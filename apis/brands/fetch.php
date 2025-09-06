<?php
// fetch_brands.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

// Optional: simple pagination
$in = read_json_body();
$limit  = isset($in['limit'])  ? max(1, (int)$in['limit'])  : 100;
$offset = isset($in['offset']) ? max(0, (int)$in['offset']) : 0;

$sql = "
  SELECT 
    b.id, b.name, b.brand_logo, b.catalouge_id,
    ul.file_path AS brand_logo_path,
    uc.file_path AS brand_catalouge_path
  FROM t_brands b
  LEFT JOIN t_uploads ul ON ul.id = b.brand_logo
  LEFT JOIN t_uploads uc ON uc.id = b.catalouge_id
  ORDER BY b.id DESC
  LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();

$brands = [];
while ($row = $res->fetch_assoc()) {
    $brands[] = [
        'id'                   => (int)$row['id'],
        'name'                 => $row['name'],
        'brand_logo_id'        => $row['brand_logo'] ? (int)$row['brand_logo'] : null,
        'brand_logo_path'      => $row['brand_logo_path'] ?? null,
        'brand_catalouge_id'   => $row['catalouge_id'] ? (int)$row['catalouge_id'] : null,
        'brand_catalouge_path' => $row['brand_catalouge_path'] ?? null
    ];
}
$stmt->close();

json_out(200, [
    'success' => true,
    'message' => 'Brands fetched',
    'data'    => [
        'count'  => count($brands),
        'brands' => $brands
    ]
]);
