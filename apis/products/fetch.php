

<!-- // fetch_products.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in         = read_json_body();
$name       = isset($in['name'])     ? trim((string)$in['name'])     : '';
$category   = isset($in['category']) ? trim((string)$in['category']) : '';
$productId  = isset($in['id'])       ? (int)$in['id']                : 0;
$limit      = isset($in['limit'])    ? max(1, (int)$in['limit'])     : 10;
$offset     = isset($in['offset'])   ? max(0, (int)$in['offset'])    : 0;

/** ========== CONFIG: base URL for file links ========== */
const BASE_URL = 'https://sakberally.com/apis';

/** Helper: bind params by reference for dynamic prepared statements */
function bind_params_by_ref(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

/** Normalize stored file_path (which might be "../uploads/..." or "/uploads/...") to absolute URL */
function file_path_to_url(?string $file_path): ?string {
    if (!$file_path) return null;
    $p = str_replace('\\', '/', $file_path);

    // If already absolute URL, return as-is
    if (preg_match('~^https?://~i', $p)) return $p;

    // Strip any leading "../"
    $p = preg_replace('#^(\.\./)+#', '/', $p);

    // Ensure leading slash
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;

    return rtrim(BASE_URL, '/') . $p;
}

$sql = "
  SELECT 
    p.id, p.name, p.price, p.unit, p.category_id, p.brand_id, p.upd_link, p.upload_id,
    p.features, p.description, p.short_description,
    c.name AS category_name
  FROM t_products p
  LEFT JOIN t_categories c ON c.id = p.category_id
";

$conds  = [];
$params = [];
$types  = '';

if ($productId > 0) {
    $conds[]  = "p.id = ?";
    $params[] = $productId;
    $types   .= 'i';
}
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

$sql .= " ORDER BY p.id ASC LIMIT ? OFFSET ?";
$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);
bind_params_by_ref($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

/**
 * First pass: collect products and all upload IDs across them (for one batched lookup).
 */
$rows = [];
$allUploadIds = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;

    $csv = trim((string)($r['upload_id'] ?? ''));
    if ($csv !== '') {
        $csv = preg_replace('/[^0-9,]/', '', $csv);
        foreach (array_filter(explode(',', $csv)) as $idStr) {
            if ($idStr !== '' && ctype_digit($idStr)) {
                $allUploadIds[(int)$idStr] = true;
            }
        }
    }
}
$stmt->close();

/** Build map upload_id => file_path (one query) */
$uploadMap = []; // [id => file_path]
if (!empty($allUploadIds)) {
    $ids = array_keys($allUploadIds);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $typ = str_repeat('i', count($ids));
    $stU = $mysqli->prepare("SELECT id, file_path FROM t_uploads WHERE id IN ($ph)");
    $stU->bind_param($typ, ...$ids);
    $stU->execute();
    $ru = $stU->get_result();
    while ($u = $ru->fetch_assoc()) {
        $uploadMap[(int)$u['id']] = $u['file_path'];
    }
    $stU->close();
}

/** Build final payload */
$products = [];
foreach ($rows as $r) {
    // Build uploads array from CSV in p.upload_id
    $uploadsArr = [];
    $csv = trim((string)($r['upload_id'] ?? ''));
    if ($csv !== '') {
        $csv = preg_replace('/[^0-9,]/', '', $csv);
        $ids = array_values(array_unique(array_filter(array_map('trim', explode(',', $csv)))));
        foreach ($ids as $idStr) {
            if ($idStr === '' || !ctype_digit($idStr)) continue;
            $uid = (int)$idStr;
            $path = $uploadMap[$uid] ?? null;
            if ($path) {
                $uploadsArr[] = [
                    'upload_id'   => $uid,
                    'upload_path' => file_path_to_url($path),
                ];
            } else {
                // If upload row missing, still reflect ID without URL
                $uploadsArr[] = [
                    'upload_id'   => $uid,
                    'upload_path' => null,
                ];
            }
        }
    }

    $products[] = [
        'id'                => (int)$r['id'],
        'name'              => $r['name'],
        'price'             => (float)$r['price'],
        'unit'              => $r['unit'],
        'category_id'       => $r['category_id'] ? (int)$r['category_id'] : null,
        'brand_id'          => isset($r['brand_id']) ? ((string)$r['brand_id'] !== '' ? (int)$r['brand_id'] : null) : null,
        'category_name'     => $r['category_name'] ?? null,
        'uploads'           => $uploadsArr,                 // << desired structure
        'features'          => $r['features'],
        'description'       => $r['description'],
        'short_description' => $r['short_description']
    ];
}

json_out(200, [
    'success' => true,
    'message' => 'Products fetched',
    'data'    => [
        'count'    => count($products),
        'products' => $products
    ]
]);
 -->


<?php
// fetch_products.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in         = read_json_body();
$name       = isset($in['name'])     ? trim((string)$in['name'])     : '';
$category   = isset($in['category']) ? trim((string)$in['category']) : '';
$productId  = isset($in['id'])       ? (int)$in['id']                : 0;
$limit      = isset($in['limit'])    ? max(1, (int)$in['limit'])     : 10;
$offset     = isset($in['offset'])   ? max(0, (int)$in['offset'])    : 0;
$sort       = isset($in['sort'])     ? strtolower(trim((string)$in['sort'])) : 'asc'; // NEW

/** ========== CONFIG: base URL for file links ========== */
const BASE_URL = 'https://sakberally.com/apis';

/** Helper: bind params by reference for dynamic prepared statements */
function bind_params_by_ref(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

/** Normalize stored file_path to absolute URL */
function file_path_to_url(?string $file_path): ?string {
    if (!$file_path) return null;
    $p = str_replace('\\', '/', $file_path);
    if (preg_match('~^https?://~i', $p)) return $p;           // already absolute
    $p = preg_replace('#^(\.\./)+#', '/', $p);               // strip ../
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;           // ensure leading slash
    return rtrim(BASE_URL, '/') . $p;
}

$sql = "
  SELECT 
    p.id, p.name, p.price, p.unit, p.category_id, p.brand_id, p.upd_link, p.upload_id,
    p.features, p.description, p.short_description,
    c.name AS category_name,
    b.name AS brand_name
  FROM t_products p
  LEFT JOIN t_categories c ON c.id = p.category_id
  LEFT JOIN t_brands    b ON b.id = p.brand_id
";

$conds  = [];
$params = [];
$types  = '';

if ($productId > 0) {
    $conds[]  = "p.id = ?";
    $params[] = $productId;
    $types   .= 'i';
}
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

/** ✅ Add sorting direction (asc / desc) */
$orderDir = ($sort === 'desc') ? 'DESC' : 'ASC';

$sql .= " ORDER BY p.id $orderDir LIMIT ? OFFSET ?";
$params[] = $limit;  $types .= 'i';
$params[] = $offset; $types .= 'i';

$stmt = $mysqli->prepare($sql);
bind_params_by_ref($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();

/** First pass: collect rows + all upload IDs for batched lookup */
$rows = [];
$allUploadIds = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
    $csv = trim((string)($r['upload_id'] ?? ''));
    if ($csv !== '') {
        $csv = preg_replace('/[^0-9,]/', '', $csv);
        foreach (array_filter(explode(',', $csv)) as $idStr) {
            if ($idStr !== '' && ctype_digit($idStr)) $allUploadIds[(int)$idStr] = true;
        }
    }
}
$stmt->close();

/** Build map upload_id => file_path (one query) */
$uploadMap = [];
if (!empty($allUploadIds)) {
    $ids = array_keys($allUploadIds);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $typ = str_repeat('i', count($ids));
    $stU = $mysqli->prepare("SELECT id, file_path FROM t_uploads WHERE id IN ($ph)");
    $stU->bind_param($typ, ...$ids);
    $stU->execute();
    $ru = $stU->get_result();
    while ($u = $ru->fetch_assoc()) $uploadMap[(int)$u['id']] = $u['file_path'];
    $stU->close();
}

/** Build final payload */
$products = [];
foreach ($rows as $r) {
    // Build uploads array from CSV in p.upload_id
    $uploadsArr = [];
    $csv = trim((string)($r['upload_id'] ?? ''));
    if ($csv !== '') {
        $csv = preg_replace('/[^0-9,]/', '', $csv);
        $ids = array_values(array_unique(array_filter(array_map('trim', explode(',', $csv)))));
        foreach ($ids as $idStr) {
            if ($idStr === '' || !ctype_digit($idStr)) continue;
            $uid  = (int)$idStr;
            $path = $uploadMap[$uid] ?? null;
            $uploadsArr[] = [
                'upload_id'   => $uid,
                'upload_path' => $path ? file_path_to_url($path) : null,
            ];
        }
    }

    $products[] = [
        'id'                => (int)$r['id'],
        'name'              => $r['name'],
        'price'             => (float)$r['price'],
        'unit'              => $r['unit'],
        'category_id'       => $r['category_id'] !== null ? (int)$r['category_id'] : null,
        'category_name'     => $r['category_name'] ?? null,     // << included
        'brand_id'          => $r['brand_id'] !== null ? (int)$r['brand_id'] : null,
        'brand_name'        => $r['brand_name'] ?? null,         // << included
        'uploads'           => $uploadsArr,
        'features'          => $r['features'],
        'description'       => $r['description'],
        'short_description' => $r['short_description']
    ];
}

json_out(200, [
    'success' => true,
    'message' => 'Products fetched',
    'data'    => [
        'count'    => count($products),
        'products' => $products
    ]
]);
