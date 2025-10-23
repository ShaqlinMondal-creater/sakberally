<?php
// products/delete.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body(); // expects application/json: { "token": "...", "product_id": 123 }

/* ========= INPUTS ========= */
$token      = trim($in['token'] ?? '');
$product_id = isset($in['product_id']) ? (int)$in['product_id'] : 0;

if ($token === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
if ($product_id <= 0) {
    json_out(422, ['success' => false, 'message' => 'product_id is required (>0)']);
}

/* ========= AUTH (admin only) ========= */
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$u->close();

if (!$user) {
    json_out(401, ['success' => false, 'message' => 'Invalid token']);
}
if (strtolower($user['role']) !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Only admin can delete products']);
}

/* ========= FETCH PRODUCT ========= */
$ps = $mysqli->prepare("SELECT id, upload_id FROM t_products WHERE id = ? LIMIT 1");
$ps->bind_param('i', $product_id);
$ps->execute();
$product = $ps->get_result()->fetch_assoc();
$ps->close();

if (!$product) {
    json_out(404, ['success' => false, 'message' => 'Product not found']);
}

/* ========= PARSE UPLOAD IDS ========= */
$uploadIds = [];
$uploadIdRaw = trim((string)($product['upload_id'] ?? ''));
if ($uploadIdRaw !== '') {
    $clean = preg_replace('/[^0-9,]/', '', $uploadIdRaw);
    foreach (array_filter(explode(',', $clean)) as $idStr) {
        if ($idStr !== '' && ctype_digit($idStr)) {
            $uploadIds[] = (int)$idStr;
        }
    }
    // de-dup
    $uploadIds = array_values(array_unique($uploadIds));
}

/* ========= FETCH UPLOAD ROWS ========= */
$uploads = [];
if (!empty($uploadIds)) {
    $placeholders = implode(',', array_fill(0, count($uploadIds), '?'));
    $types = str_repeat('i', count($uploadIds));
    $stmt = $mysqli->prepare("SELECT id, file_path FROM t_uploads WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$uploadIds);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $uploads[] = $row; // ['id' => ..., 'file_path' => '/uploads/products/xxx.png']
    }
    $stmt->close();
}

/* ========= DELETE FILES FROM DISK (FIRST) ========= */
// We only allow deleting inside the /uploads directory for safety.
$baseUploadsDir = realpath(dirname(__DIR__) . '/uploads'); // ../uploads
$deletedFiles = [];
$missingFiles = [];

foreach ($uploads as $up) {
    $relPath = (string)$up['file_path']; // e.g., '/uploads/products/abc.png'
    // Build absolute path
    $absPath = dirname(__DIR__) . $relPath;
    $absReal = realpath($absPath);

    // If file exists, ensure it's inside uploads dir, then unlink
    if ($absReal !== false && $baseUploadsDir !== false && strpos($absReal, $baseUploadsDir) === 0) {
        if (@unlink($absReal)) {
            $deletedFiles[] = $relPath;
        } else {
            // If unlink fails, still proceed; we'll try DB cleanup after
            $missingFiles[] = $relPath . ' (unlink failed)';
        }
    } else {
        // File missing or outside allowed dir
        $missingFiles[] = $relPath . ' (not found)';
    }
}

/* ========= DB DELETE (THEN) ========= */
$mysqli->begin_transaction();
try {
    if (!empty($uploadIds)) {
        $placeholders = implode(',', array_fill(0, count($uploadIds), '?'));
        $types = str_repeat('i', count($uploadIds));
        $delU = $mysqli->prepare("DELETE FROM t_uploads WHERE id IN ($placeholders)");
        if (!$delU) {
            throw new RuntimeException('Prepare failed for t_uploads delete: ' . $mysqli->error);
        }
        $delU->bind_param($types, ...$uploadIds);
        if (!$delU->execute()) {
            $msg = $delU->error ?: 'Delete failed for t_uploads';
            $delU->close();
            throw new RuntimeException($msg);
        }
        $delU->close();
    }

    $delP = $mysqli->prepare("DELETE FROM t_products WHERE id = ? LIMIT 1");
    if (!$delP) {
        throw new RuntimeException('Prepare failed for t_products delete: ' . $mysqli->error);
    }
    $delP->bind_param('i', $product_id);
    if (!$delP->execute()) {
        $msg = $delP->error ?: 'Delete failed for product';
        $delP->close();
        throw new RuntimeException($msg);
    }
    $delP->close();

    $mysqli->commit();

    json_out(200, [
        'success' => true,
        'message' => 'Product and associated images deleted',
        'data' => [
            'product_id' => $product_id,
            'deleted_upload_ids' => $uploadIds,
            'deleted_files' => $deletedFiles,
            'missing_or_failed_files' => $missingFiles
        ]
    ]);
} catch (Throwable $e) {
    $mysqli->rollback();
    json_out(500, [
        'success' => false,
        'message' => 'Deletion failed',
        'error'   => $e->getMessage(),
        'data'    => [
            'product_id' => $product_id,
            'attempted_upload_ids' => $uploadIds,
            'deleted_files' => $deletedFiles,
            'missing_or_failed_files' => $missingFiles
        ]
    ]);
}
