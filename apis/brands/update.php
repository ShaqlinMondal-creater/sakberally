<?php
// update_brand.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

/**
 * Expected multipart/form-data:
 * token          (string)  - admin token (required)
 * brand_id       (int)     - which brand to update (required) [fallback: id]
 * name           (string)  - optional
 * brand_logo     (file)    - optional (image)
 * catalouge      (file)    - optional (pdf/image)
 */

$token    = trim($_POST['token'] ?? '');
$brandId  = (int)($_POST['brand_id'] ?? ($_POST['id'] ?? 0));
$nameIn   = isset($_POST['name']) ? trim((string)$_POST['name']) : null;

if ($token === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
if ($brandId <= 0) {
    json_out(422, ['success' => false, 'message' => 'brand_id is required']);
}

// ---- Validate admin token ----
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$actor = $u->get_result()->fetch_assoc();
$u->close();

if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

// ---- Fetch current brand ----
$b = $mysqli->prepare("SELECT id, name, brand_logo, catalouge_id FROM t_brands WHERE id = ? LIMIT 1");
$b->bind_param('i', $brandId);
$b->execute();
$brand = $b->get_result()->fetch_assoc();
$b->close();

if (!$brand) {
    json_out(404, ['success' => false, 'message' => 'Brand not found']);
}

// ---- Helpers ----
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
    // find file path
    $q = $mysqli->prepare("SELECT file_path FROM t_uploads WHERE id = ? LIMIT 1");
    $q->bind_param('i', $uploadId);
    $q->execute();
    $res = $q->get_result();
    $row = $res->fetch_assoc();
    $q->close();

    if ($row && !empty($row['file_path'])) {
        $p = $row['file_path'];
        // Try unlink stored path
        @unlink($p);
        // If older rows saved without '../', try prefixing one level up
        if (file_exists($p) && strpos($p, '../') !== 0) {
            @unlink('../' . ltrim($p, '/\\'));
        }
    }

    // delete upload row
    $d = $mysqli->prepare("DELETE FROM t_uploads WHERE id = ? LIMIT 1");
    $d->bind_param('i', $uploadId);
    $d->execute();
    $d->close();
}

function move_and_record_upload(mysqli $mysqli, array $file, string $purpose, string $destDir, array $allowedMimes): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
    }

    $tmp  = $file['tmp_name'];
    $orig = $file['name'];
    $size = (int)$file['size'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
    if (!in_array($mime, $allowedMimes, true)) {
        json_out(422, ['success' => false, 'message' => 'Invalid file type', 'mime' => $mime]);
    }

    $ext = safe_ext($orig);
    $filename = mk_filename($ext);

    ensure_dir($destDir);
    $targetPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $targetPath)) {
        json_out(500, ['success' => false, 'message' => 'Failed to move uploaded file']);
    }

    $relPath = str_replace('\\', '/', $targetPath);

    // insert upload record
    $ins = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
    $extForDb = $ext ?: '';
    $ins->bind_param('sssis', $purpose, $orig, $relPath, $size, $extForDb);
    if (!$ins->execute()) {
        json_out(500, ['success' => false, 'message' => 'Failed to insert upload record', 'error' => $ins->error]);
    }
    $uploadId = (int)$ins->insert_id;
    $ins->close();

    return ['id' => $uploadId, 'path' => $relPath];
}

// ---- Directories (UPDATED with ../) ----
$logoDir = '../uploads/brands/logo';
$catDir  = '../uploads/brands/catalouge';

$logoAllowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$catAllowed  = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

// ---- Track what will change ----
$newName        = $nameIn !== null ? $nameIn : $brand['name'];
$newLogoId      = (int)($brand['brand_logo'] ?? 0);
$newCatalogueId = (int)($brand['catalouge_id'] ?? 0);
$newLogoPath    = null;
$newCatPath     = null;

// ---- If new logo uploaded: delete old, save new ----
if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    // delete old file + upload row (if any)
    if (!empty($brand['brand_logo'])) {
        delete_upload_and_file($mysqli, (int)$brand['brand_logo']);
    }
    // save new
    $logo = move_and_record_upload($mysqli, $_FILES['brand_logo'], 'brands', $logoDir, $logoAllowed);
    $newLogoId   = $logo['id'];
    $newLogoPath = $logo['path'];
}

// ---- If new catalouge uploaded: delete old, save new ----
if (isset($_FILES['catalouge']) && $_FILES['catalouge']['error'] !== UPLOAD_ERR_NO_FILE) {

    if (!empty($brand['catalouge_id'])) {
        delete_upload_and_file($mysqli, (int)$brand['catalouge_id']);
    }
    $catalogue = move_and_record_upload($mysqli, $_FILES['catalouge'], 'brands', $catDir, $catAllowed);
    $newCatalogueId = $catalogue['id'];
    $newCatPath     = $catalogue['path'];
}

// ---- Update brand row ----
$fields = [];
$params = [];
$types  = '';

if ($nameIn !== null) { $fields[] = "name = ?";         $params[] = $newName;        $types .= 's'; }
if ($newLogoId !== (int)($brand['brand_logo'] ?? 0)) { 
    $fields[] = "brand_logo = ?";  $params[] = $newLogoId;  $types .= 'i'; 
}
if ($newCatalogueId !== (int)($brand['catalouge_id'] ?? 0)) { 
    $fields[] = "catalouge_id = ?"; $params[] = $newCatalogueId; $types .= 'i'; 
}

if (!empty($fields)) {
    $sql = "UPDATE t_brands SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
    $params[] = $brandId;
    $types   .= 'i';
    $up = $mysqli->prepare($sql);
    $up->bind_param($types, ...$params);
    if (!$up->execute()) {
        json_out(500, ['success' => false, 'message' => 'Failed to update brand', 'error' => $up->error]);
    }
    $up->close();
}

// ---- Get latest paths (if not replaced in this request) ----
$g = $mysqli->prepare("
    SELECT 
        b.id, b.name, b.brand_logo, b.catalouge_id,
        ul.file_path AS logo_path,
        uc.file_path AS cat_path
    FROM t_brands b
    LEFT JOIN t_uploads ul ON ul.id = b.brand_logo
    LEFT JOIN t_uploads uc ON uc.id = b.catalouge_id
    WHERE b.id = ?
    LIMIT 1
");
$g->bind_param('i', $brandId);
$g->execute();
$row = $g->get_result()->fetch_assoc();
$g->close();

json_out(200, [
    'success' => true,
    'message' => 'Brand updated',
    'data' => [
        'token'                  => $token,
        'name'                   => $row['name'],
        'brand_logo_id'          => $row['brand_logo'] ? (int)$row['brand_logo'] : null,
        'brand_logo_path'        => $row['logo_path'] ?? $newLogoPath,
        'brand_catalouge_id'     => $row['catalouge_id'] ? (int)$row['catalouge_id'] : null,
        'brand_catalouge_path'   => $row['cat_path'] ?? $newCatPath,
        'brand_id'               => (int)$row['id']
    ]
]);
