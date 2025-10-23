<?php
// products/update.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body(); // expects application/json

/* ========= INPUTS ========= */
$token      = trim($in['token'] ?? '');
$product_id = isset($in['product_id']) ? (int)$in['product_id'] : 0;

// Optional fields
$hasName        = array_key_exists('name', $in);
$hasCategoryId  = array_key_exists('category_id', $in);
$hasBrandId     = array_key_exists('brand_id', $in);
$hasPrice       = array_key_exists('price', $in);

$name        = $hasName ? trim((string)$in['name']) : null;
$category_id = $hasCategoryId ? (int)$in['category_id'] : null;
$brand_id    = $hasBrandId ? (int)$in['brand_id'] : null;
$price_raw   = $hasPrice ? $in['price'] : null;

/* ========= VALIDATION (basic) ========= */
$errors = [];
if ($token === '')                     $errors[] = 'token is required';
if ($product_id <= 0)                  $errors[] = 'product_id is required (>0)';
if (!$hasName && !$hasCategoryId && !$hasBrandId && !$hasPrice) {
    $errors[] = 'At least one of name, category_id, brand_id, price must be provided';
}
if ($hasName && $name === '')          $errors[] = 'name cannot be empty when provided';
if ($hasCategoryId && $category_id <= 0) $errors[] = 'category_id must be > 0 when provided';
if ($hasBrandId && $brand_id <= 0)        $errors[] = 'brand_id must be > 0 when provided';
if ($hasPrice && !is_numeric($price_raw)) $errors[] = 'price must be numeric when provided';

if ($errors) {
    json_out(422, ['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
}

$price = $hasPrice ? (float)$price_raw : null;

/* ========= AUTH (admin only) ========= */
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$u->close();

if (!$user) {
    json_out(401, ['success' => false, 'message' => 'Invalid token']);
}
if (strtolower($user['role']) !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Only admin can update products']);
}

/* ========= VERIFY PRODUCT EXISTS ========= */
$chk = $mysqli->prepare("SELECT id FROM t_products WHERE id = ? LIMIT 1");
$chk->bind_param('i', $product_id);
$chk->execute();
$exists = $chk->get_result()->fetch_column();
$chk->close();

if (!$exists) {
    json_out(404, ['success' => false, 'message' => 'Product not found']);
}

/* ========= OPTIONAL FK CHECKS ========= */
if ($hasCategoryId) {
    $c = $mysqli->prepare("SELECT id FROM t_categories WHERE id = ? LIMIT 1");
    $c->bind_param('i', $category_id);
    $c->execute(); $catExists = $c->get_result()->fetch_column(); $c->close();
    if (!$catExists) json_out(422, ['success' => false, 'message' => 'Invalid category_id']);
}

if ($hasBrandId) {
    $b = $mysqli->prepare("SELECT id FROM t_brands WHERE id = ? LIMIT 1");
    $b->bind_param('i', $brand_id);
    $b->execute(); $brandExists = $b->get_result()->fetch_column(); $b->close();
    if (!$brandExists) json_out(422, ['success' => false, 'message' => 'Invalid brand_id']);
}

/* ========= DYNAMIC UPDATE ========= */
$setParts = [];
$types    = '';
$params   = [];

// maintain order for bind_param
if ($hasName)        { $setParts[] = 'name = ?';         $types .= 's'; $params[] = $name; }
if ($hasCategoryId)  { $setParts[] = 'category_id = ?';  $types .= 'i'; $params[] = $category_id; }
if ($hasBrandId)     { $setParts[] = 'brand_id = ?';     $types .= 'i'; $params[] = $brand_id; }
if ($hasPrice)       { $setParts[] = 'price = ?';        $types .= 'd'; $params[] = $price; }

// Always update timestamp
$setSql = implode(', ', $setParts) . ', updated_at = NOW()';

$sql = "UPDATE t_products SET $setSql WHERE id = ?";
$types .= 'i';
$params[] = $product_id;

$st = $mysqli->prepare($sql);
if (!$st) {
    json_out(500, ['success' => false, 'message' => 'Prepare failed', 'error' => $mysqli->error]);
}

// Bind dynamically
$refs = [];
foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
array_unshift($refs, $types);
call_user_func_array([$st, 'bind_param'], $refs);

if (!$st->execute()) {
    $msg = $st->error ?: 'Update failed';
    $st->close();
    json_out(500, ['success' => false, 'message' => 'Failed to update product', 'error' => $msg]);
}
$st->close();

/* ========= FETCH UPDATED ROW ========= */
$q = $mysqli->prepare("
    SELECT p.id, p.name, p.price, p.unit, p.category_id, p.brand_id,
           c.name AS category_name, b.name AS brand_name
    FROM t_products p
    LEFT JOIN t_categories c ON c.id = p.category_id
    LEFT JOIN t_brands b ON b.id = p.brand_id
    WHERE p.id = ?
");
$q->bind_param('i', $product_id);
$q->execute();
$row = $q->get_result()->fetch_assoc();
$q->close();

/* ========= RESPONSE ========= */
json_out(200, [
    'success' => true,
    'message' => 'Product updated successfully',
    'data' => [
        'product' => [
            'id'            => (int)$row['id'],
            'name'          => $row['name'],
            'price'         => (float)$row['price'],
            'unit'          => $row['unit'] ?? null,
            'category_id'   => isset($row['category_id']) ? (int)$row['category_id'] : null,
            'category_name' => $row['category_name'] ?? null,
            'brand_id'      => isset($row['brand_id']) ? (int)$row['brand_id'] : null,
            'brand_name'    => $row['brand_name'] ?? null
        ]
    ]
]);
