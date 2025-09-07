<?php
// create_brand.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

/* ------------------- Helpers ------------------- */
function ensure_dir(string $dir): void {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}
function safe_ext(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return preg_replace('/[^a-z0-9]+/i', '', $ext);
}
function mk_filename(string $ext): string {
    return time() . '_' . bin2hex(random_bytes(4)) . ($ext ? ('.' . $ext) : '');
}

/**
 * Move upload to disk and record in t_uploads
 * $allowedMimes: array of accepted MIME strings
 * $maxSizeBytes: optional max size (null = no limit)
 */
function move_and_record_upload(mysqli $mysqli, array $file, string $purpose, string $destDir, array $allowedMimes, ?int $maxSizeBytes = null): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
    }

    $tmp  = $file['tmp_name'];
    $orig = $file['name'];
    $size = (int)$file['size'];

    if ($maxSizeBytes !== null && $size > $maxSizeBytes) {
        json_out(422, ['success' => false, 'message' => 'File too large', 'max_bytes' => $maxSizeBytes]);
    }

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

    // Portable path with forward slashes for DB
    $relPath = str_replace('\\', '/', $targetPath);

    // Save record in t_uploads
    $stmt = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
    $extForDb = $ext ?: '';
    $stmt->bind_param('sssds', $purpose, $orig, $relPath, $size, $extForDb);
    if (!$stmt->execute()) {
        json_out(500, ['success' => false, 'message' => 'Failed to insert upload record', 'error' => $stmt->error]);
    }
    $uploadId = (int)$stmt->insert_id;
    $stmt->close();

    return ['id' => $uploadId, 'path' => $relPath];
}

/* ------------------- Inputs (FORM-DATA only) ------------------- */

// ✅ Token must come from form-data (or x-www-form-urlencoded)
if (!isset($_POST['token']) || trim($_POST['token']) === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
$token = trim($_POST['token']);

// Name (also from form-data)
$name = trim((string)($_POST['name'] ?? ''));
if ($name === '') {
    json_out(422, ['success' => false, 'message' => 'name is required']);
}

/* ------------------- Auth (admin only) ------------------- */
$stmt = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$actor = $res->fetch_assoc();
$stmt->close();

if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

/* ------------------- Optional uploads ------------------- */
$logoId = null;
$logoPath = null;
$catalogueId = null;
$cataloguePath = null;

/* brand_logo: OPTIONAL image */
if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $logoDir     = '../uploads/brands/logo';
    $logoAllowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $logo        = move_and_record_upload($mysqli, $_FILES['brand_logo'], 'brands', $logoDir, $logoAllowed);
    $logoId   = $logo['id'];
    $logoPath = $logo['path'];
}

/* catalouge: OPTIONAL PDF (max 50 MB) */
if (isset($_FILES['catalouge']) && $_FILES['catalouge']['error'] !== UPLOAD_ERR_NO_FILE) {
    $catDir     = '../uploads/brands/catalouge';
    $catAllowed = ['application/pdf'];
    $maxPdf     = 50 * 1024 * 1024; // 50 MB
    $catalogue  = move_and_record_upload($mysqli, $_FILES['catalouge'], 'brands', $catDir, $catAllowed, $maxPdf);
    $catalogueId   = $catalogue['id'];
    $cataloguePath = $catalogue['path'];
}

/* ------------------- Insert brand ------------------- */
/**
 * Make sure t_brands.brand_logo and t_brands.catalouge_id are NULLable:
 * ALTER TABLE t_brands MODIFY brand_logo INT NULL, MODIFY catalouge_id INT NULL;
 */
$stmt = $mysqli->prepare("INSERT INTO t_brands (name, brand_logo, catalouge_id) VALUES (?,?,?)");
$stmt->bind_param('sii', $name, $logoId, $catalogueId);
if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to create brand', 'error' => $stmt->error]);
}
$brandId = (int)$stmt->insert_id;
$stmt->close();

/* ------------------- Success ------------------- */
json_out(201, [
    'success' => true,
    'message' => 'Brand created',
    'data' => [
        'token'                => $token,
        'name'                 => $name,
        'brand_logo_id'        => $logoId,
        'brand_logo_path'      => $logoPath,
        'brand_catalouge_id'   => $catalogueId,
        'brand_catalouge_path' => $cataloguePath,
        'brand_id'             => $brandId
    ]
]);
