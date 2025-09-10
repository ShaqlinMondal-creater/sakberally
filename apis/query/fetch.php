<?php
// fetch_inquiries.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body();
$name    = isset($in['name'])    ? trim((string)$in['name'])    : '';
$email   = isset($in['email'])   ? trim((string)$in['email'])   : '';
$mobile  = isset($in['mobile'])  ? trim((string)$in['mobile'])  : '';
$subject = isset($in['subject']) ? trim((string)$in['subject']) : '';
$limit   = isset($in['limit'])   ? max(1, (int)$in['limit'])    : 10;
$offset  = isset($in['offset'])  ? max(0, (int)$in['offset'])   : 0;

// helper to bind params by reference for dynamic prepared statements
function bind_params_by_ref(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$sql = "
  SELECT 
    i.id, i.name, i.mobile, i.email, i.subject, i.messege, i.upload_id, i.create_at,
    u.file_path AS upload_path
  FROM t_inquiry_contact i
  LEFT JOIN t_uploads u ON u.id = i.upload_id
";

$conds  = [];
$params = [];
$types  = '';

if ($name !== '')   { $conds[] = "i.name LIKE ?";    $params[] = '%'.$name.'%';   $types .= 's'; }
if ($email !== '')  { $conds[] = "i.email LIKE ?";   $params[] = '%'.$email.'%';  $types .= 's'; }
if ($mobile !== '') { $conds[] = "i.mobile LIKE ?";  $params[] = '%'.$mobile.'%'; $types .= 's'; }
if ($subject !== '') {
    // subject is enum('contact','inquiry'); if user passes other text, do LIKE
    if (in_array(strtolower($subject), ['contact','inquiry'], true)) {
        $conds[] = "i.subject = ?";
        $params[] = strtolower($subject);
        $types .= 's';
    } else {
        $conds[] = "i.subject LIKE ?";
        $params[] = '%'.$subject.'%';
        $types .= 's';
    }
}

if ($conds) {
    $sql .= " WHERE " . implode(' AND ', $conds);
}
$sql .= " ORDER BY i.id DESC LIMIT ? OFFSET ?";

$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);
bind_params_by_ref($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id'         => (int)$r['id'],
        'name'       => $r['name'],
        'mobile'     => $r['mobile'],
        'email'      => $r['email'],
        'subject'    => $r['subject'],
        'messege'    => $r['messege'],
        'upload_id'  => $r['upload_id'] ? (int)$r['upload_id'] : null,
        'upload_path'=> $r['upload_path'] ?? null,
        'date'       => $r['create_at'] ?? null
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Inquiries fetched',
    'data'    => [
        'count'     => count($rows),
        'inquiries' => $rows
    ]
]);
