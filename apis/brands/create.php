<?php
// create_brand.php
require '../configs/db_connect.php';

/**
 * Early guard: if the body exceeded post_max_size, PHP discards POST/FILES.
 * Return a clear 413 so you don’t get a misleading “token is required”.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
    $postMax = ini_get('post_max_size') ?: 'unknown';
    json_out(413, [
        'success' => false,
        'message' => 'Request entity too large. Increase post_max_size and upload_max_filesize.',
        'details' => [
            'CONTENT_LENGTH' => $contentLength,
            'post_max_size'  => $postMax
        ]
    ]);
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
 * Save an uploaded file and record it in t_uploads
 * - $allowedMimes: list of allowed MIME types
 * - $maxSizeBytes: null for no limit; integer to enforce max
 */
function move_and_record_upload(mysqli $mysqli, array $file, string $purpose, string $destDir, array $allowedMimes, ?int $maxSizeBytes = null): array {
    // Standard PHP upload errors (other than NO_FILE) are rejected
    if (!isset($file['error'])) {
        json_out(422, ['success' => false, 'message' => 'Upload error: missing error code']);
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // UPLOAD_ERR_NO_FILE is handled by caller; anything else is an error
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error']]);
    }

    $tmp  = $file['tmp_name'];
    $orig = $file['name'] ?? 'unknown';
    $size = (int)($file['size'] ?? 0);

    if ($maxSizeBytes !== null && $size > $maxSizeBytes) {
        json_out(422, ['success' => false, 'message' => 'File too large', 'max_bytes' => $maxSizeBytes]);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
    if (!in_array($mime, $allowedMimes, true)) {
        json_out(422, ['success' => false, 'message' => 'Invalid file type', 'mime' => $mime]);
    }

    $ext      = safe_ext($orig);
    $filename = mk_filename($ext);

    ensure_dir($destDir);
    $targetPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $targetPath)) {
        json_out(500, ['success' => false, 'message' => 'Failed to move uploaded file']);
    }

    // Normalize to forward slashes
    $relPath = str_replace('\\', '/', $targetPath);

    // Record upload
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

/* ------------------- Inputs (FORM-DATA ONLY) ------------------- */
if (!isset($_POST['token']) || trim($_POST['token']) === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
$token = trim($_POST['token']);

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

/** brand_logo: OPTIONAL image */
if (isset($_FILES['brand_logo'])) {
    if ($_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
        $logoDir     = '../uploads/brands/logo';
        $logoAllowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $logo        = move_and_record_upload($mysqli, $_FILES['brand_logo'], 'brands', $logoDir, $logoAllowed);
        $logoId   = $logo['id'];
        $logoPath = $logo['path'];
    } elseif ($_FILES['brand_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        json_out(422, ['success' => false, 'message' => 'brand_logo upload failed', 'php_upload_error' => $_FILES['brand_logo']['error']]);
    }
}

/** catalouge: OPTIONAL PDF (≤ 50 MB) */
if (isset($_FILES['catalouge'])) {
    if ($_FILES['catalouge']['error'] === UPLOAD_ERR_OK) {
        $catDir     = '../uploads/brands/catalouge';
        $catAllowed = ['application/pdf'];
        $maxPdf     = 50 * 1024 * 1024; // 50 MB
        $catalogue  = move_and_record_upload($mysqli, $_FILES['catalouge'], 'brands', $catDir, $catAllowed, $maxPdf);
        $catalogueId   = $catalogue['id'];
        $cataloguePath = $catalogue['path'];
    } elseif ($_FILES['catalouge']['error'] !== UPLOAD_ERR_NO_FILE) {
        json_out(422, ['success' => false, 'message' => 'catalouge upload failed', 'php_upload_error' => $_FILES['catalouge']['error']]);
    }
}

/* ------------------- Insert brand ------------------- */
/**
 * NOTE: Passing NULL to bind_param is OK; ensure table columns are NULLable.
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
