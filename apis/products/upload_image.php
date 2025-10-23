<?php
// products/upload_images.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

/* ========= CONFIG ========= */
$BASE_URL = 'https://sakberally.com/api';  // change this if domain differs
$REL_DIR  = '/uploads/products';
$ABS_DIR  = dirname(__DIR__) . $REL_DIR;

/* ========= HELPERS ========= */
function json_bad_request($msg, $errors = []) {
    json_out(422, ['success' => false, 'message' => $msg, 'errors' => $errors]);
}
function ensure_dir($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Failed to create upload directory');
        }
    }
}
function safe_filename($orig) {
    $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $base = bin2hex(random_bytes(10));
    return $ext ? ($base . '.' . $ext) : $base;
}
function now_ts() { return date('Y-m-d H:i:s'); }
function normalize_files_array($filesField) {
    $out = [];
    if (!isset($filesField['name'])) return $out;
    if (is_array($filesField['name'])) {
        $count = count($filesField['name']);
        for ($i = 0; $i < $count; $i++) {
            $out[] = [
                'name'     => $filesField['name'][$i],
                'type'     => $filesField['type'][$i] ?? '',
                'tmp_name' => $filesField['tmp_name'][$i] ?? '',
                'error'    => $filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $filesField['size'][$i] ?? 0,
            ];
        }
    } else {
        $out[] = $filesField;
    }
    return $out;
}

/* ========= INPUTS ========= */
$token      = trim($_POST['token'] ?? '');
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($token === '') json_bad_request('token is required');
if ($product_id <= 0) json_bad_request('product_id is required (>0)');
if (!isset($_FILES['uploads'])) json_bad_request('uploads[] files are required');

/* ========= AUTH: only admin ========= */
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$u->close();

if (!$user) json_out(401, ['success' => false, 'message' => 'Invalid token']);
if (strtolower($user['role']) !== 'admin')
    json_out(403, ['success' => false, 'message' => 'Only admin can upload product images']);

/* ========= Ensure product exists ========= */
$chk = $mysqli->prepare("SELECT id, upload_id FROM t_products WHERE id = ? LIMIT 1");
$chk->bind_param('i', $product_id);
$chk->execute();
$prod = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$prod) json_bad_request('Invalid product_id');

/* ========= Prepare directory ========= */
ensure_dir($ABS_DIR);
$allowedExts = ['jpg','jpeg','png','webp','gif','svg','bmp','tiff','pdf'];
$maxBytes = 10 * 1024 * 1024; // 10 MB

$files = normalize_files_array($_FILES['uploads']);
$validFiles = [];
$errors = [];

foreach ($files as $idx => $f) {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = "File #$idx upload error: " . $f['error'];
        continue;
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        $errors[] = "File #$idx invalid";
        continue;
    }
    $origName = $f['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        $errors[] = "File #$idx invalid extension: $ext";
        continue;
    }
    if ($f['size'] > $maxBytes) {
        $errors[] = "File #$idx too large";
        continue;
    }
    $validFiles[] = $f;
}
if (empty($validFiles)) json_bad_request('No valid files to upload', $errors);

/* ========= Process upload ========= */
$mysqli->begin_transaction();

try {
    $uploadedInfo = [];

    foreach ($validFiles as $f) {
        $origName = $f['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $size     = (int)$f['size'];
        $newName  = safe_filename($origName);
        $absPath  = $ABS_DIR . '/' . $newName;
        $relPath  = $REL_DIR . '/' . $newName;

        if (!move_uploaded_file($f['tmp_name'], $absPath)) {
            throw new RuntimeException('Failed to move ' . $origName);
        }

        // Insert into t_uploads
        $purpose = 'products';
        $now     = now_ts();
        $ins = $mysqli->prepare("
            INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param('sssisss', $purpose, $origName, $relPath, $size, $ext, $now, $now);
        if (!$ins->execute()) {
            throw new RuntimeException($ins->error ?: 'Insert failed');
        }
        $uploadId = (int)$ins->insert_id;
        $ins->close();

        $uploadedInfo[] = [
            'id'  => (string)$uploadId,
            'url' => $BASE_URL . $relPath
        ];
    }

    // Update product.upload_id
    $existing = trim((string)($prod['upload_id'] ?? ''));
    $merged = [];

    if ($existing !== '') {
        $existing = preg_replace('/[^0-9,]/', '', $existing);
        $existingIds = array_filter(explode(',', $existing));
        foreach ($existingIds as $id) $merged[(int)$id] = true;
    }
    foreach ($uploadedInfo as $info) {
        $merged[(int)$info['id']] = true;
    }
    $finalList = implode(',', array_keys($merged));

    $up = $mysqli->prepare("UPDATE t_products SET upload_id = ? WHERE id = ?");
    $up->bind_param('si', $finalList, $product_id);
    if (!$up->execute()) throw new RuntimeException($up->error ?: 'Update failed');
    $up->close();

    $mysqli->commit();

    json_out(201, [
        'success' => true,
        'message' => 'Images uploaded successfully',
        'data' => [
            'product_id' => $product_id,
            'uploaded_ids' => $uploadedInfo,
            'upload_id_string' => $finalList
        ],
        'errors' => $errors
    ]);
} catch (Throwable $e) {
    $mysqli->rollback();
    json_out(500, [
        'success' => false,
        'message' => 'Upload failed',
        'error'   => $e->getMessage()
    ]);
}
