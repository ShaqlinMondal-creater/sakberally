<?php
// fetch_sheets.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in     = read_json_body();
$name   = isset($in['name'])   ? trim((string)$in['name'])   : '';
$limit  = isset($in['limit'])  ? max(1, (int)$in['limit'])  : 10;
$offset = isset($in['offset']) ? max(0, (int)$in['offset']) : 0;

$sql = "SELECT id, name, path, status, created_at, updated_at FROM t_sheets";
$params = [];
$types  = '';

if ($name !== '') {
    $sql .= " WHERE name LIKE ?";
    $params[] = '%'.$name.'%';
    $types   .= 's';
}

$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);

// Bind params (supports both cases)
if ($types !== '') {
    // bind by reference for mysqli
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id'         => (int)$r['id'],
        'name'       => $r['name'],
        'path'       => $r['path'],
        'status'     => (int)$r['status'],
        'created_at' => $r['created_at'],
        'updated_at' => $r['updated_at']
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Sheets fetched',
    'data' => [
        'count'  => count($rows),
        'sheets' => $rows
    ]
]);
