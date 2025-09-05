<?php
// add_sheet.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in     = read_json_body();
$name   = trim((string)($in['name'] ?? ''));
$path   = trim((string)($in['path'] ?? ''));
$status = isset($in['status']) ? (int)$in['status'] : 0; // default 0

if ($name === '' || $path === '') {
    json_out(422, ['success' => false, 'message' => 'name and path are required']);
}

// (Optional) constrain status to 0/1
if (!in_array($status, [0,1], true)) {
    $status = 0;
}

$stmt = $mysqli->prepare("INSERT INTO t_sheets (name, path, status) VALUES (?,?,?)");
$stmt->bind_param('ssi', $name, $path, $status);

if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to add sheet', 'error' => $stmt->error]);
}

$id = (int)$stmt->insert_id;
$stmt->close();

// Response (exclude created_at / updated_at)
json_out(201, [
    'success' => true,
    'message' => 'Sheet added',
    'data'    => [
        'id'     => $id,
        'name'   => $name,
        'path'   => $path,
        'status' => $status
    ]
]);
