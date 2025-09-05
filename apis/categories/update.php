<?php
// update_category.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$token = trim($_POST['token'] ?? '');
$catId = (int)($_POST['category_id'] ?? ($_POST['id'] ?? 0));
$nameIn = isset($_POST['name']) ? trim((string)$_POST['name']) : null;
$sortIn = isset($_POST['sort_no']) ? (int)$_POST['sort_no'] : null;

if ($token === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
if ($catId <= 0) {
    json_out(422, ['success' => false, 'message' => 'category_id is required']);
}

// --- Validate admin token ---
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$actor = $u->get_result()->fetch_assoc();
$u->close();

if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

// --- Fetch current category ---
$c = $mysqli->prepare("SELECT id, name, category_image_id, sort_no FROM t_categories WHERE id = ? LIMIT 1");
$c->bind_param('i', $catId);
$c->execute();
$cat = $c->get_result()->fetch_assoc();
$c->close();

if (!$cat) {
    json_out(404, ['success' => false, 'message' => 'Category not found']);
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
function delete_upload_and_file(mysqli $mysqli, ?int $uploadId): void {
    if (!$uploadId) return;
    $q = $mysqli->prepare("SELECT file_path FROM t_uploads WHERE id = ? LIMIT 1");
    $q->bind_param('i', $uploadId);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    $q->close();

    if (!empty($row['file_path'])) {
        $p = $row['file_path'];
        if (file_exists($p)) { @unlink($p); }
        // fallback if path missing ../
        $alt = '../' . ltrim($p, '/\\');
        if (file_exists($alt)) { @unlink($alt); }
    }

    $d = $mysqli->prepare("DELETE FROM t_uploads WHERE id = ? LIMIT 1");
    $d->bind_param('i', $uploadId);
    $d->execute();
    $d->close();
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

    // record in t_uploads
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

// ---- Defaults to existing values ----
$newName   = $nameIn !== null ? $nameIn : $cat['name'];
$newSortNo = $sortIn !== null ? $sortIn : (int)$cat['sort_no'];
$newImageId = $cat['category_image_id'] ? (int)$cat['category_image_id'] : null;
$newImagePath = null;

// ---- If new image uploaded: delete old, save new ----
if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if (!empty($cat['category_image_id'])) {
        delete_upload_and_file($mysqli, (int)$cat['category_image_id']);
    }
    $dest = '../uploads/categories';
    $saved = save_upload($mysqli, $_FILES['category_image'], $dest);
    $newImageId   = $saved['id'];
    $newImagePath = $saved['path'];
}

// ---- Build dynamic update ----
$fields = [];
$params = [];
$types  = '';

if ($nameIn !== null)   { $fields[] = "name = ?";               $params[] = $newName;   $types .= 's'; }
if ($sortIn !== null)   { $fields[] = "sort_no = ?";            $params[] = $newSortNo; $types .= 'i'; }
if ($newImagePath !== null) { // image actually replaced
    $fields[] = "category_image_id = ?";
    $params[] = $newImageId;
    $types   .= 'i';
}

if (empty($fields)) {
    json_out(422, ['success' => false, 'message' => 'No fields to update']);
}

// NOTE: t_categories has column named `update` (reserved); we don't set it explicitly.
// The ON UPDATE CURRENT_TIMESTAMP in DDL will auto-update it.
$sql = "UPDATE t_categories SET " . implode(', ', $fields) . " WHERE id = ?";
$params[] = $catId;
$types   .= 'i';

$up = $mysqli->prepare($sql);
$up->bind_param($types, ...$params);
if (!$up->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to update category', 'error' => $up->error]);
}
$up->close();

// ---- Fetch updated row with image path ----
$get = $mysqli->prepare("
    SELECT c.id, c.name, c.sort_no, c.category_image_id, u.file_path AS category_image_path
    FROM t_categories c
    LEFT JOIN t_uploads u ON u.id = c.category_image_id
    WHERE c.id = ? LIMIT 1
");
$get->bind_param('i', $catId);
$get->execute();
$row = $get->get_result()->fetch_assoc();
$get->close();

json_out(200, [
    'success' => true,
    'message' => 'Category updated',
    'data' => [
        'token'               => $token,
        'category_id'         => (int)$row['id'],
        'name'                => $row['name'],
        'sort_no'             => (int)$row['sort_no'],
        'category_image_id'   => $row['category_image_id'] ? (int)$row['category_image_id'] : null,
        'category_image_path' => $row['category_image_path'] ?? $newImagePath
    ]
]);
