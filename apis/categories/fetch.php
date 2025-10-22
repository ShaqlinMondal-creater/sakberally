<?php
// fetch_categories.php
require  '../configs/db_connect.php';
// Base URL for images (no trailing slash)
$BASE_URL = 'https://sakberally.com/apis';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in      = read_json_body();
$name    = isset($in['name'])   ? trim((string)$in['name'])   : '';
$limit   = isset($in['limit'])  ? max(1, (int)$in['limit'])  : 10;
$offset  = isset($in['offset']) ? max(0, (int)$in['offset']) : 0;
$wise    = isset($in['wise'])   ? strtolower(trim((string)$in['wise'])) : 'simple';
// allowed: simple | category | sub_category | all
if (!in_array($wise, ['simple','category','sub_category','all'], true)) {
    $wise = 'simple';
}

$base = "
  SELECT 
    c.id, c.name, c.parent_id, c.sort_no, c.category_image_id,
    u.file_path AS category_image_path
  FROM t_categories c
  LEFT JOIN t_uploads u ON u.id = c.category_image_id
";

if ($wise !== 'all') {
    // ===== Flat responses (simple/category/sub_category) =====
    $where = [];
    $params = [];
    $types = '';

    if ($name !== '') {
        $where[] = "c.name LIKE ?";
        $params[] = '%' . $name . '%';
        $types   .= 's';
    }

    if ($wise === 'category') {
        $where[] = "c.parent_id = 0";
    } elseif ($wise === 'sub_category') {
        $where[] = "c.parent_id <> 0";
    } // else simple: no parent filter

    $sql = $base
         . (count($where) ? " WHERE " . implode(" AND ", $where) : "")
         . " ORDER BY c.sort_no ASC, c.id DESC LIMIT ? OFFSET ?";

    $stmt = $mysqli->prepare($sql);
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'id'                  => (int)$r['id'],
            'name'                => $r['name'],
            'parent_id'           => (int)$r['parent_id'],
            'sort_no'             => (int)$r['sort_no'],
            'category_image_id'   => $r['category_image_id'] ? (int)$r['category_image_id'] : null,
            'category_image_path' => $r['category_image_path'] 
                ? preg_replace('#^\.\./#', $BASE_URL . '/', $r['category_image_path'])
                : null
        ];
    }
    $stmt->close();

    json_out(200, [
        'success' => true,
        'message' => 'Categories fetched',
        'data' => [
            'wise'       => $wise,
            'count'      => count($rows),
            'categories' => $rows
        ]
    ]);
    exit;
}

// ===== wise = 'all' (parents with their children) =====

// 1) Fetch parent categories (paged)
$whereParents = ["c.parent_id = 0"];
$paramsP = [];
$typesP  = '';

if ($name !== '') {
    // Name filter on parent names (simple & fast)
    $whereParents[] = "c.name LIKE ?";
    $paramsP[] = '%' . $name . '%';
    $typesP   .= 's';
}

$sqlParents = $base
            . " WHERE " . implode(" AND ", $whereParents)
            . " ORDER BY c.sort_no ASC, c.id DESC LIMIT ? OFFSET ?";

$paramsP[] = $limit;
$paramsP[] = $offset;
$typesP   .= 'ii';

$stmtP = $mysqli->prepare($sqlParents);
$stmtP->bind_param($typesP, ...$paramsP);
$stmtP->execute();
$resP = $stmtP->get_result();

$parents = [];
$parentIds = [];
while ($r = $resP->fetch_assoc()) {
    $pid = (int)$r['id'];
    $parentIds[] = $pid;
    $parents[$pid] = [
        'id'                  => $pid,
        'name'                => $r['name'],
        'parent_id'           => (int)$r['parent_id'], // should be 0
        'sort_no'             => (int)$r['sort_no'],
        'category_image_id'   => $r['category_image_id'] ? (int)$r['category_image_id'] : null,
        'category_image_path' => $r['category_image_path'] 
            ? preg_replace('#^\.\./#', $BASE_URL . '/', $r['category_image_path'])
            : null,
        'children'            => [] // will fill next
    ];
}
$stmtP->close();

if (empty($parentIds)) {
    // No parents in this page
    json_out(200, [
        'success' => true,
        'message' => 'Categories fetched',
        'data' => [
            'wise'        => 'all',
            'count'       => 0,
            'parents'     => [],
            'total_child' => 0
        ]
    ]);
    exit;
}

// 2) Fetch all children for those parent IDs
// Build IN (?, ?, ...)
$placeholders = implode(',', array_fill(0, count($parentIds), '?'));
$sqlChildren = $base
             . " WHERE c.parent_id IN ($placeholders)"
             . " ORDER BY c.parent_id ASC, c.sort_no ASC, c.id DESC";

$stmtC = $mysqli->prepare($sqlChildren);

// bind dynamic IN
$typesC = str_repeat('i', count($parentIds));
$stmtC->bind_param($typesC, ...$parentIds);

$stmtC->execute();
$resC = $stmtC->get_result();

$totalChildren = 0;
while ($r = $resC->fetch_assoc()) {
    $pId = (int)$r['parent_id'];
    if (!isset($parents[$pId])) continue; // safety

    $child = [
        'id'                  => (int)$r['id'],
        'name'                => $r['name'],
        'parent_id'           => $pId,
        'sort_no'             => (int)$r['sort_no'],
        'category_image_id'   => $r['category_image_id'] ? (int)$r['category_image_id'] : null,
        'category_image_path' => $r['category_image_path'] 
            ? preg_replace('#^\.\./#', $BASE_URL . '/', $r['category_image_path'])
            : null
    ];
    $parents[$pId]['children'][] = $child;
    $totalChildren++;
}
$stmtC->close();

// Re-index parents as a list
$parentsList = array_values($parents);

json_out(200, [
    'success' => true,
    'message' => 'Categories fetched',
    'data' => [
        'wise'        => 'all',
        // count = number of parent rows returned in this page
        'count'       => count($parentsList),
        'parents'     => $parentsList,
        // convenience: total number of children included for these parents
        'total_child' => $totalChildren
    ]
]);
