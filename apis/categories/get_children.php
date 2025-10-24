<?php
// categories/get_children.php
require '../configs/db_connect.php';

// Base URL for images (no trailing slash)
$BASE_URL = 'https://sakberally.com/apis';

// Allow GET (preferred) and POST (fallback)
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'], true)) {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

// ---- Read inputs ----
// Prefer query params first
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$name       = isset($_GET['name']) ? trim((string)$_GET['name']) : '';

// Fallback to JSON body if not present in query
if ($categoryId <= 0 && $name === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = read_json_body();
    if (isset($in['category_id'])) $categoryId = (int)$in['category_id'];
    if (isset($in['name']))        $name       = trim((string)$in['name']);
}

// Validate input
if ($categoryId <= 0 && $name === '') {
    json_out(422, ['success' => false, 'message' => 'Provide category_id or name via query params']);
}

// Small helper to convert "../uploads/..." or "/uploads/..." to absolute
function to_abs_url(?string $path, string $BASE_URL): ?string {
    if (!$path) return null;
    $p = str_replace('\\', '/', $path);
    if (preg_match('~^https?://~i', $p)) return $p;
    $p = preg_replace('#^(\.\./)+#', '/', $p);
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;
    return rtrim($BASE_URL, '/') . $p;
}

// 1) Resolve parent categories to inspect (could be 1 or many if by name)
$parents = [];     // id => parentRow
$parentIds = [];   // list of IDs to fetch children for

if ($categoryId > 0) {
    $stmt = $mysqli->prepare("
        SELECT c.id, c.name, c.parent_id, c.sort_no, c.category_image_id, u.file_path AS category_image_path
        FROM t_categories c
        LEFT JOIN t_uploads u ON u.id = c.category_image_id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pid = (int)$row['id'];
        $parents[$pid] = [
            'id'                  => $pid,
            'name'                => $row['name'],
            'parent_id'           => (int)$row['parent_id'],
            'sort_no'             => (int)$row['sort_no'],
            'category_image_id'   => $row['category_image_id'] ? (int)$row['category_image_id'] : null,
            'category_image_path' => to_abs_url($row['category_image_path'], $BASE_URL),
            'children'            => []
        ];
        $parentIds[] = $pid;
    }
    $stmt->close();
} else {
    // by name (LIKE match)
    $like = '%' . $name . '%';
    $stmt = $mysqli->prepare("
        SELECT c.id, c.name, c.parent_id, c.sort_no, c.category_image_id, u.file_path AS category_image_path
        FROM t_categories c
        LEFT JOIN t_uploads u ON u.id = c.category_image_id
        WHERE c.name LIKE ?
        ORDER BY c.sort_no ASC, c.id DESC
    ");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $pid = (int)$row['id'];
        $parents[$pid] = [
            'id'                  => $pid,
            'name'                => $row['name'],
            'parent_id'           => (int)$row['parent_id'],
            'sort_no'             => (int)$row['sort_no'],
            'category_image_id'   => $row['category_image_id'] ? (int)$row['category_image_id'] : null,
            'category_image_path' => to_abs_url($row['category_image_path'], $BASE_URL),
            'children'            => []
        ];
        $parentIds[] = $pid;
    }
    $stmt->close();
}

// If no matching parent found
if (empty($parentIds)) {
    json_out(404, [
        'success' => false,
        'message' => 'Category not found',
        'data' => [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'name'        => $name !== '' ? $name : null
        ]
    ]);
}

// 2) Fetch all direct children of the resolved parentIds
$placeholders = implode(',', array_fill(0, count($parentIds), '?'));
$types = str_repeat('i', count($parentIds));

$sqlChildren = "
    SELECT c.id, c.name, c.parent_id, c.sort_no, c.category_image_id, u.file_path AS category_image_path
    FROM t_categories c
    LEFT JOIN t_uploads u ON u.id = c.category_image_id
    WHERE c.parent_id IN ($placeholders)
    ORDER BY c.parent_id ASC, c.sort_no ASC, c.id DESC
";

$stmt = $mysqli->prepare($sqlChildren);
$stmt->bind_param($types, ...$parentIds);
$stmt->execute();
$res = $stmt->get_result();

$totalChildren = 0;
while ($row = $res->fetch_assoc()) {
    $pId = (int)$row['parent_id'];
    if (!isset($parents[$pId])) continue;

    $child = [
        'id'                  => (int)$row['id'],
        'name'                => $row['name'],
        'parent_id'           => $pId,
        'sort_no'             => (int)$row['sort_no'],
        'category_image_id'   => $row['category_image_id'] ? (int)$row['category_image_id'] : null,
        'category_image_path' => to_abs_url($row['category_image_path'], $BASE_URL)
    ];
    $parents[$pId]['children'][] = $child;
    $totalChildren++;
}
$stmt->close();

// Re-index parents for output
$outputParents = array_values($parents);

json_out(200, [
    'success' => true,
    'message' => 'Sub-categories fetched',
    'data' => [
        'count_parents'  => count($outputParents),
        'total_children' => $totalChildren,
        'parents'        => $outputParents
    ]
]);
