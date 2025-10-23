<?php
// products/delete_images.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body(); // expects application/json

/* ========= INPUTS ========= */
$token       = trim($in['token'] ?? '');
$product_id  = isset($in['product_id']) ? (int)$in['product_id'] : 0;
$upload_ids_in = $in['upload_ids'] ?? []; // can be array or comma-separated string

if ($token === '')     json_out(422, ['success' => false, 'message' => 'token is required']);
if ($product_id <= 0)  json_out(422, ['success' => false, 'message' => 'product_id is required (>0)']);

// Normalize upload_ids to int array
$upload_ids = [];
if (is_string($upload_ids_in)) {
    $upload_ids_in = preg_split('/\s*,\s*/', trim($upload_ids_in));
}
if (is_array($upload_ids_in)) {
    foreach ($upload_ids_in as $id) {
        if (is_numeric($id) && (int)$id > 0) $upload_ids[] = (int)$id;
    }
}
$upload_ids = array_values(array_unique($upload_ids));
if (empty($upload_ids)) {
    json_out(422, ['success' => false, 'message' => 'upload_ids is required (array or CSV of integers)']);
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
    json_out(403, ['success' => false, 'message' => 'Only admin can delete product images']);
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

/* ========= PARSE PRODUCT'S CURRENT upload_id CSV ========= */
$currentCsv = trim((string)($product['upload_id'] ?? ''));
$currentIds = [];
if ($currentCsv !== '') {
    $currentCsv = preg_replace('/[^0-9,]/', '', $currentCsv);
    foreach (array_filter(explode(',', $currentCsv)) as $cid) {
        if ($cid !== '' && ctype_digit($cid)) $currentIds[] = (int)$cid;
    }
}
$currentIds = array_values(array_unique($currentIds));

/* ========= Determine which requested IDs actually belong to this product ========= */
$belongs = array_values(array_intersect($currentIds, $upload_ids));
$skipped = array_values(array_diff($upload_ids, $belongs));

if (empty($belongs)) {
    json_out(422, [
        'success' => false,
        'message' => 'None of the provided upload_ids belong to this product',
        'data' => [
            'product_id' => $product_id,
            'requested_upload_ids' => $upload_ids,
            'current_upload_ids' => $currentIds,
            'skipped_not_attached' => $skipped
        ]
    ]);
}

/* ========= FETCH ROWS FOR DELETION ========= */
$uploads = [];
$placeholders = implode(',', array_fill(0, count($belongs), '?'));
$types = str_repeat('i', count($belongs));
$stmt = $mysqli->prepare("SELECT id, file_path FROM t_uploads WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$belongs);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $uploads[] = $row; // ['id'=>..., 'file_path'=>'/uploads/products/xxx.png']
}
$stmt->close();

/* ========= DELETE FILES FROM DISK FIRST ========= */
$baseUploadsDir = realpath(dirname(__DIR__) . '/uploads'); // ../uploads
$deletedFiles = [];
$missingFiles = [];
$foundIdsFromDisk = [];

foreach ($uploads as $up) {
    $relPath = (string)$up['file_path']; // e.g. '/uploads/products/abc.png'
    $absPath = dirname(__DIR__) . $relPath;
    $absReal = realpath($absPath);

    if ($absReal !== false && $baseUploadsDir !== false && strpos($absReal, $baseUploadsDir) === 0) {
        if (@unlink($absReal)) {
            $deletedFiles[] = $relPath;
            $foundIdsFromDisk[] = (int)$up['id'];
        } else {
            $missingFiles[] = $relPath . ' (unlink failed)';
            $foundIdsFromDisk[] = (int)$up['id']; // still attempt DB delete
        }
    } else {
        $missingFiles[] = $relPath . ' (not found)';
        $foundIdsFromDisk[] = (int)$up['id']; // still attempt DB delete
    }
}

/* ========= DB DELETE + UPDATE PRODUCT (transaction) ========= */
$mysqli->begin_transaction();
try {
    if (!empty($foundIdsFromDisk)) {
        $ph = implode(',', array_fill(0, count($foundIdsFromDisk), '?'));
        $ty = str_repeat('i', count($foundIdsFromDisk));
        $delU = $mysqli->prepare("DELETE FROM t_uploads WHERE id IN ($ph)");
        if (!$delU) throw new RuntimeException('Prepare failed for t_uploads delete: ' . $mysqli->error);
        $delU->bind_param($ty, ...$foundIdsFromDisk);
        if (!$delU->execute()) {
            $msg = $delU->error ?: 'Delete failed for t_uploads';
            $delU->close();
            throw new RuntimeException($msg);
        }
        $delU->close();
    }

    // Compute new CSV for product (remove the deleted ids)
    $remaining = array_values(array_diff($currentIds, $foundIdsFromDisk));
    $newCsv = implode(',', $remaining);

    $up = $mysqli->prepare("UPDATE t_products SET upload_id = ? WHERE id = ?");
    $up->bind_param('si', $newCsv, $product_id);
    if (!$up->execute()) {
        $msg = $up->error ?: 'Failed to update product upload_id';
        $up->close();
        throw new RuntimeException($msg);
    }
    $up->close();

    $mysqli->commit();

    json_out(200, [
        'success' => true,
        'message' => 'Selected images deleted from product',
        'data' => [
            'product_id' => $product_id,
            'deleted_upload_ids' => $foundIdsFromDisk,
            'remaining_upload_ids' => $remaining,
            'deleted_files' => $deletedFiles,
            'missing_or_failed_files' => $missingFiles,
            'skipped_not_attached' => $skipped
        ]
    ]);
} catch (Throwable $e) {
    $mysqli->rollback();
    json_out(500, [
        'success' => false,
        'message' => 'Deletion failed',
        'error'   => $e->getMessage(),
        'data' => [
            'product_id' => $product_id,
            'attempted_upload_ids' => $foundIdsFromDisk,
            'deleted_files' => $deletedFiles,
            'missing_or_failed_files' => $missingFiles,
            'skipped_not_attached' => $skipped
        ]
    ]);
}
