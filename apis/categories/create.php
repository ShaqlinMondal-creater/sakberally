<?php
// create_category.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$token    = trim($_POST['token'] ?? '');
$name     = trim((string)($_POST['name'] ?? ''));
$sortNo   = isset($_POST['sort_no']) ? (int)$_POST['sort_no'] : 0;

// === parent_id logic ===
$parentId = 0; // default to 0 (means top-level category)
if (isset($_POST['parent_id']) && $_POST['parent_id'] !== '' && $_POST['parent_id'] !== null) {
    $tmpPid = (int)$_POST['parent_id'];
    if ($tmpPid < 0) {
        json_out(422, ['success' => false, 'message' => 'Invalid parent_id']);
    }
    // verify parent exists (ignore if 0)
    if ($tmpPid > 0) {
        $chk = $mysqli->prepare("SELECT id FROM t_categories WHERE id = ? LIMIT 1");
        $chk->bind_param('i', $tmpPid);
        $chk->execute();
        $exists = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$exists) {
            json_out(422, ['success' => false, 'message' => 'parent_id does not reference an existing category']);
        }
    }
    $parentId = $tmpPid;
}

if ($token === '')  json_out(422, ['success' => false, 'message' => 'token is required']);
if ($name === '')   json_out(422, ['success' => false, 'message' => 'name is required']);

// --- Verify admin token ---
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$actor = $u->get_result()->fetch_assoc();
$u->close();

if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

// --- Helpers ---
function ensure_dir(string $dir): void {
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
}
function safe_ext(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return preg_replace('/[^a-z0-9]+/i', '', $ext);
}
function mk_filename(string $ext): string {
    return time() . '_' . bin2hex(random_bytes(4)) . ($ext ? ('.' . $ext) : '');
}
function save_upload(mysqli $mysqli, array $file, string $destDir): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
    }

    $tmp  = $file['tmp_name'];
    $orig = $file['name'];
    $size = (int)$file['size'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($mime, $allowed, true)) {
        json_out(422, ['success' => false, 'message' => 'Invalid image type', 'mime' => $mime]);
    }

    ensure_dir($destDir);
    $ext = safe_ext($orig);
    $filename  = mk_filename($ext);
    $targetAbs = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $targetAbs)) {
        json_out(500, ['success' => false, 'message' => 'Failed to move uploaded file']);
    }

    $filePath = str_replace('\\', '/', $targetAbs);

    $ins = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
    $purpose = 'category';
    $extDb = $ext ?: '';
    $ins->bind_param('sssds', $purpose, $orig, $filePath, $size, $extDb);
    if (!$ins->execute()) {
        json_out(500, ['success' => false, 'message' => 'Failed to insert upload record', 'error' => $ins->error]);
    }
    $uploadId = (int)$ins->insert_id;
    $ins->close();

    return ['id' => $uploadId, 'path' => $filePath];
}

// --- Optional category image ---
$imgId = null;
$imgPath = null;
if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $dest = '../uploads/categories';
    $saved = save_upload($mysqli, $_FILES['category_image'], $dest);
    $imgId   = $saved['id'];
    $imgPath = $saved['path'];
}

// --- Insert category ---
$stmt = $mysqli->prepare("
    INSERT INTO t_categories (name, parent_id, category_image_id, sort_no)
    VALUES (?,?,?,?)
");
$stmt->bind_param('siii', $name, $parentId, $imgId, $sortNo);

if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to create category', 'error' => $stmt->error]);
}
$catId = (int)$stmt->insert_id;
$stmt->close();

json_out(201, [
    'success' => true,
    'message' => 'Category created',
    'data' => [
        'token'               => $token,
        'category_id'         => $catId,
        'name'                => $name,
        'parent_id'           => $parentId, // always integer (0 for parent)
        'sort_no'             => $sortNo,
        'category_image_id'   => $imgId,
        'category_image_path' => $imgPath
    ]
]);
