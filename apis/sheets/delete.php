<?php
// delete_sheet.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in   = read_json_body();
$id   = (int)($in['id'] ?? 0);

if ($id <= 0) {
    json_out(422, ['success' => false, 'message' => 'id is required']);
}

// Fetch existing record (to return details after delete)
$g = $mysqli->prepare("SELECT id, name, path, status, created_at, updated_at FROM t_sheets WHERE id = ? LIMIT 1");
$g->bind_param('i', $id);
$g->execute();
$row = $g->get_result()->fetch_assoc();
$g->close();

if (!$row) {
    json_out(404, ['success' => false, 'message' => 'Sheet not found']);
}

// Delete
$d = $mysqli->prepare("DELETE FROM t_sheets WHERE id = ? LIMIT 1");
$d->bind_param('i', $id);
if (!$d->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to delete sheet', 'error' => $d->error]);
}
$d->close();

json_out(200, [
    'success' => true,
    'message' => 'Sheet deleted',
    'data' => [
        'id'         => (int)$row['id'],
        'name'       => $row['name'],
        'path'       => $row['path'],
        'status'     => (int)$row['status'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ]
]);
