<?php
// create_brand.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

if (!isset($_POST['token']) || trim($_POST['token']) === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
$token = trim($_POST['token']);

$name = trim((string)($_POST['name'] ?? ''));
if ($name === '') {
    json_out(422, ['success' => false, 'message' => 'name is required']);
}

// Check if 'brand_logo' is uploaded and handle error
if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_NO_FILE) {
    json_out(422, ['success' => false, 'message' => 'brand_logo file is required']);
}

// Check if 'catalouge' is uploaded and handle error
if (isset($_FILES['catalouge']) && $_FILES['catalouge']['error'] === UPLOAD_ERR_NO_FILE) {
    json_out(422, ['success' => false, 'message' => 'catalouge file is required']);
}


// --- Step 1: validate admin token ---
$stmt = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$actor = $res->fetch_assoc();
$stmt->close();

if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

// --- Helpers ---
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

function move_and_record_upload(mysqli $mysqli, array $file, string $purpose, string $destDir, array $allowedMimes): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
    }

    $tmp  = $file['tmp_name'];
    $orig = $file['name'];
    $size = (int)$file['size'];

    // Basic MIME validation
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

    // build a portable file_path (forward slashes)
    $relPath = str_replace('\\', '/', $targetPath);

    // Insert into t_uploads
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

// --- Step 2: save brand_logo ---
$logoDir = '../uploads/brands/logo';
$logoAllowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$logo = move_and_record_upload($mysqli, $_FILES['brand_logo'], 'brands', $logoDir, $logoAllowed);

// --- Step 3: save catalouge (pdf or image) ---
$catDir = '../uploads/brands/catalouge';
$catAllowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
$catalogue = move_and_record_upload($mysqli, $_FILES['catalouge'], 'brands', $catDir, $catAllowed);

// --- Step 4: insert brand ---
$stmt = $mysqli->prepare("INSERT INTO t_brands (name, brand_logo, catalouge_id) VALUES (?,?,?)");
$stmt->bind_param('sii', $name, $logo['id'], $catalogue['id']);
if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to create brand', 'error' => $stmt->error]);
}
$brandId = (int)$stmt->insert_id;
$stmt->close();

// --- Success response (201) ---
json_out(201, [
    'success' => true,
    'message' => 'Brand created',
    'data' => [
        'token'                => $token,
        'name'                 => $name,
        'brand_logo_id'        => $logo['id'],
        'brand_logo_path'      => $logo['path'],
        'brand_catalouge_id'   => $catalogue['id'],
        'brand_catalouge_path' => $catalogue['path'],
        'brand_id'             => $brandId
    ]
]);









// create_brand.php
// require '../configs/db_connect.php';

// // Only POST allowed
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
// }

// // --- Get input ---
// $token = trim($_POST['token'] ?? '');
// $name  = trim($_POST['name'] ?? '');

// // If token or name is missing in POST, fallback to JSON body
// if ($token === '' || $name === '') {
//     $input = json_decode(file_get_contents('php://input'), true);
//     if ($token === '') $token = trim($input['token'] ?? '');
//     if ($name === '')  $name  = trim($input['name'] ?? '');
// }

// // Validate mandatory fields
// if ($token === '') json_out(422, ['success' => false, 'message' => 'token is required']);
// if ($name === '')  json_out(422, ['success' => false, 'message' => 'name is required']);

// // --- Step 1: validate admin token ---
// $stmt = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
// $stmt->bind_param('s', $token);
// $stmt->execute();
// $res = $stmt->get_result();
// $actor = $res->fetch_assoc();
// $stmt->close();

// if (!$actor || $actor['role'] !== 'admin') {
//     json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
// }

// // --- Helpers ---
// function ensure_dir(string $dir): void {
//     if (!is_dir($dir)) @mkdir($dir, 0777, true);
// }

// function safe_ext(string $filename): string {
//     $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
//     return preg_replace('/[^a-z0-9]+/i', '', $ext);
// }

// function mk_filename(string $ext): string {
//     return time() . '_' . bin2hex(random_bytes(4)) . ($ext ? ('.' . $ext) : '');
// }

// function move_and_record_upload(mysqli $mysqli, array $file, string $purpose, string $destDir, array $allowedMimes): array {
//     if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
//         json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
//     }

//     $tmp  = $file['tmp_name'];
//     $orig = $file['name'];
//     $size = (int)$file['size'];

//     // Validate MIME type
//     $finfo = new finfo(FILEINFO_MIME_TYPE);
//     $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
//     if (!in_array($mime, $allowedMimes, true)) {
//         json_out(422, ['success' => false, 'message' => 'Invalid file type', 'mime' => $mime]);
//     }

//     $ext = safe_ext($orig);
//     $filename = mk_filename($ext);

//     ensure_dir($destDir);
//     $targetPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

//     if (!move_uploaded_file($tmp, $targetPath)) {
//         json_out(500, ['success' => false, 'message' => 'Failed to move uploaded file']);
//     }

//     $relPath = str_replace('\\', '/', $targetPath);

//     // Insert into t_uploads
//     $stmt = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
//     $extForDb = $ext ?: '';
//     $stmt->bind_param('sssds', $purpose, $orig, $relPath, $size, $extForDb);
//     if (!$stmt->execute()) {
//         json_out(500, ['success' => false, 'message' => 'Failed to insert upload record', 'error' => $stmt->error]);
//     }
//     $uploadId = (int)$stmt->insert_id;
//     $stmt->close();

//     return ['id' => $uploadId, 'path' => $relPath];
// }

// // --- Step 2: handle brand_logo (optional) ---
// $logo = null;
// if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
//     $logoDir = '../uploads/brands/logo';
//     $logoAllowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
//     $logo = move_and_record_upload($mysqli, $_FILES['brand_logo'], 'brands', $logoDir, $logoAllowed);
// }

// // --- Step 3: handle catalouge (optional) ---
// $catalogue = null;
// if (isset($_FILES['catalouge']) && $_FILES['catalouge']['error'] !== UPLOAD_ERR_NO_FILE) {
//     $catDir = '../uploads/brands/catalouge';
//     $catAllowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
//     $catalogue = move_and_record_upload($mysqli, $_FILES['catalouge'], 'brands', $catDir, $catAllowed);
// }

// // --- Step 4: insert brand ---
// $stmt = $mysqli->prepare("INSERT INTO t_brands (name, brand_logo, catalouge_id) VALUES (?,?,?)");
// $logoId = $logo['id'] ?? null;
// $catalogueId = $catalogue['id'] ?? null;
// $stmt->bind_param('sii', $name, $logoId, $catalogueId);
// if (!$stmt->execute()) {
//     json_out(500, ['success' => false, 'message' => 'Failed to create brand', 'error' => $stmt->error]);
// }
// $brandId = (int)$stmt->insert_id;
// $stmt->close();

// // --- Success response ---
// json_out(201, [
//     'success' => true,
//     'message' => 'Brand created',
//     'data' => [
//         'token'                => $token,
//         'name'                 => $name,
//         'brand_logo_id'        => $logoId,
//         'brand_logo_path'      => $logo['path'] ?? null,
//         'brand_catalouge_id'   => $catalogueId,
//         'brand_catalouge_path' => $catalogue['path'] ?? null,
//         'brand_id'             => $brandId
//     ]
// ]);



