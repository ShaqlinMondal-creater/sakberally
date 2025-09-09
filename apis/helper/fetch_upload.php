<?php
// fetch_uploads.php
require __DIR__ . 'configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in        = read_json_body();
$purpose   = isset($in['purpose'])   ? trim((string)$in['purpose'])   : '';
$name      = isset($in['name'])      ? trim((string)$in['name'])      : '';
$extension = isset($in['extension']) ? trim((string)$in['extension']) : '';
$limit     = isset($in['limit'])     ? max(1, (int)$in['limit'])      : 10;
$offset    = isset($in['offset'])    ? max(0, (int)$in['offset'])     : 0;

// Helper: bind params by reference for dynamic prepared statements
function bind_params_by_ref(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$sql = "SELECT id, purpose, file_original_name, file_path, size, extension, created_at, updated_at FROM t_uploads";
$conds  = [];
$params = [];
$types  = '';

if ($purpose !== '') {
    $conds[]  = "purpose = ?";
    $params[] = $purpose;
    $types   .= 's';
}
if ($name !== '') {
    $conds[]  = "file_original_name LIKE ?";
    $params[] = '%' . $name . '%';
    $types   .= 's';
}
if ($extension !== '') {
    $conds[]  = "extension = ?";
    $params[] = $extension;
    $types   .= 's';
}

if ($conds) {
    $sql .= " WHERE " . implode(' AND ', $conds);
}
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);
bind_params_by_ref($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

$uploads = [];
while ($r = $res->fetch_assoc()) {
    $uploads[] = [
        'id'                 => (int)$r['id'],
        'purpose'            => $r['purpose'],
        'file_original_name' => $r['file_original_name'],
        'file_path'          => $r['file_path'],
        'size'               => (int)$r['size'],
        'extension'          => $r['extension'],
        'created_at'         => $r['created_at'],
        'updated_at'         => $r['updated_at'],
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Uploads fetched',
    'data'    => [
        'count'    => count($uploads),
        'uploads'  => $uploads
    ]
]);
