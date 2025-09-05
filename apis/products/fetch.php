<?php
// fetch_products.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in       = read_json_body();
$name     = isset($in['name'])     ? trim((string)$in['name'])     : '';
$category = isset($in['category']) ? trim((string)$in['category']) : '';
$limit    = isset($in['limit'])    ? max(1, (int)$in['limit'])     : 10;
$offset   = isset($in['offset'])   ? max(0, (int)$in['offset'])    : 0;

// Helper: bind params by reference for dynamic prepared statements
function bind_params_by_ref(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$sql = "
  SELECT 
    p.id, p.name, p.price, p.unit, p.category_id, p.upd_link, p.upload_id,
    p.features, p.description, p.short_description,
    c.name AS category_name,
    u.file_path AS upload_path
  FROM t_products p
  LEFT JOIN t_categories c ON c.id = p.category_id
  LEFT JOIN t_uploads   u ON u.id = p.upload_id
";

$conds  = [];
$params = [];
$types  = '';

if ($name !== '') {
    $conds[]  = "p.name LIKE ?";
    $params[] = '%' . $name . '%';
    $types   .= 's';
}
if ($category !== '') {
    $conds[]  = "c.name LIKE ?";
    $params[] = '%' . $category . '%';
    $types   .= 's';
}

if ($conds) {
    $sql .= " WHERE " . implode(' AND ', $conds);
}

$sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);
bind_params_by_ref($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

$products = [];
while ($r = $res->fetch_assoc()) {
    $products[] = [
        'id'                => (int)$r['id'],
        'name'              => $r['name'],
        'price'             => (float)$r['price'],
        'unit'              => $r['unit'],
        'category_id'       => $r['category_id'] ? (int)$r['category_id'] : null,
        'category_name'     => $r['category_name'] ?? null,
        'upd_link'          => $r['upd_link'],
        'upload_id'         => $r['upload_id'] ? (int)$r['upload_id'] : null,
        'upload_path'       => $r['upload_path'] ?? null,
        'features'          => $r['features'],          // as stored (e.g., HTML/table)
        'description'       => $r['description'],
        'short_description' => $r['short_description']
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Products fetched',
    'data'    => [
        'count'    => count($products),
        'products' => $products
    ]
]);
