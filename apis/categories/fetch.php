<?php
// fetch_categories.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in     = read_json_body();
$name   = isset($in['name'])   ? trim((string)$in['name'])   : '';
$limit  = isset($in['limit'])  ? max(1, (int)$in['limit'])  : 10;
$offset = isset($in['offset']) ? max(0, (int)$in['offset']) : 0;

$base = "
  SELECT 
    c.id, c.name, c.sort_no, c.category_image_id,
    u.file_path AS category_image_path
  FROM t_categories c
  LEFT JOIN t_uploads u ON u.id = c.category_image_id
";

if ($name !== '') {
    $sql = $base . " WHERE c.name LIKE ? ORDER BY c.sort_no ASC, c.id DESC LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $like = '%' . $name . '%';
    $stmt->bind_param('sii', $like, $limit, $offset);
} else {
    $sql = $base . " ORDER BY c.sort_no ASC, c.id DESC LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id'                 => (int)$r['id'],
        'name'               => $r['name'],
        'sort_no'            => (int)$r['sort_no'],
        'category_image_id'  => $r['category_image_id'] ? (int)$r['category_image_id'] : null,
        'category_image_path'=> $r['category_image_path'] ?? null
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Categories fetched',
    'data' => [
        'count'      => count($rows),
        'categories' => $rows
    ]
]);
