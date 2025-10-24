<?php
// products/update.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body(); // expects application/json

/* ========= REQUIRED ========= */
$token      = trim($in['token'] ?? '');
$product_id = isset($in['product_id']) ? (int)$in['product_id'] : 0;

/* ========= OPTIONAL FIELDS ========= */
$hasName        = array_key_exists('name', $in);
$hasCategoryId  = array_key_exists('category_id', $in);
$hasBrandId     = array_key_exists('brand_id', $in);
$hasPrice       = array_key_exists('price', $in);
$hasUnit        = array_key_exists('unit', $in);
$hasFeatures    = array_key_exists('features', $in);
$hasDesc        = array_key_exists('description', $in);
$hasShortDesc   = array_key_exists('short_description', $in);

$name        = $hasName ? trim((string)$in['name']) : null;
$category_id = $hasCategoryId ? (int)$in['category_id'] : null;
$brand_id    = $hasBrandId ? (int)$in['brand_id'] : null;
$price_raw   = $hasPrice ? $in['price'] : null;
$unit        = $hasUnit ? trim((string)$in['unit']) : null;

$featuresIn        = $hasFeatures ? $in['features'] : null; // string|array|object|html
$descriptionIn     = $hasDesc ? $in['description'] : null;  // string|html
$short_description = $hasShortDesc ? (string)$in['short_description'] : null;

/* ========= VALIDATION ========= */
$errors = [];
if ($token === '')                     $errors[] = 'token is required';
if ($product_id <= 0)                  $errors[] = 'product_id is required (>0)';
if (!$hasName && !$hasCategoryId && !$hasBrandId && !$hasPrice && !$hasUnit && !$hasFeatures && !$hasDesc && !$hasShortDesc) {
    $errors[] = 'At least one updatable field must be provided';
}
if ($hasName && $name === '')                      $errors[] = 'name cannot be empty when provided';
if ($hasCategoryId && $category_id <= 0)           $errors[] = 'category_id must be > 0 when provided';
if ($hasBrandId && $brand_id <= 0)                 $errors[] = 'brand_id must be > 0 when provided';
if ($hasPrice && !is_numeric($price_raw))          $errors[] = 'price must be numeric when provided';
if ($hasUnit && $unit === '')                      $errors[] = 'unit cannot be empty when provided';

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

/* ========= VERIFY PRODUCT ========= */
$chk = $mysqli->prepare("SELECT id FROM t_products WHERE id = ? LIMIT 1");
$chk->bind_param('i', $product_id);
$chk->execute();
$exists = $chk->get_result()->fetch_column();
$chk->close();
if (!$exists) json_out(404, ['success' => false, 'message' => 'Product not found']);

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

/* ========= HELPERS FOR HTML FIELDS ========= */
function is_html_like($s) {
    return is_string($s) && preg_match('/^\s*</', $s) === 1;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function prettify_key($k) {
    $k = str_replace(['_', '-'], ' ', trim((string)$k));
    $k = preg_replace('/\s+/', ' ', $k);
    return strtolower($k);
}
function build_features_html($features) {
    if ($features === null) return null;
    if (is_html_like($features)) return (string)$features;

    $rows = [];
    if (is_array($features)) {
        $isAssoc = array_keys($features) !== range(0, count($features) - 1);
        if ($isAssoc) {
            foreach ($features as $k => $v) {
                $label = prettify_key($k);
                $val   = is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $rows[] = '        <tr><td class="fw6">'.e($label).'</td><td>'.e($val).'</td></tr>';
            }
        } else {
            foreach ($features as $pair) {
                if (is_array($pair) && count($pair) >= 2) {
                    $label = prettify_key($pair[0]);
                    $val   = is_scalar($pair[1]) ? (string)$pair[1] : json_encode($pair[1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $rows[] = '        <tr><td class="fw6">'.e($label).'</td><td>'.e($val).'</td></tr>';
                }
            }
        }
    } elseif (is_string($features) && trim($features) !== '') {
        $rows[] = '        <tr><td class="fw6">feature</td><td>'.e($features).'</td></tr>';
    } else {
        $rows[] = '        <tr><td class="fw6">info</td><td>-</td></tr>';
    }

    return "<table class=\" tbl tble1\">\n    <tbody>\n" . implode("\n", $rows) . "\n    </tbody>\n</table>";
}
function build_description_html($desc) {
    if ($desc === null || $desc === '') return null;
    if (is_html_like($desc)) return (string)$desc;

    // Plain text → wrap in pro_desc with <p>
    $text = (string)$desc;
    // Split on blank lines; keep single linebreaks as <br>
    $paras = preg_split("/\R{2,}/", $text);
    $paras = array_map(function($p){
        $p = e($p);
        $p = preg_replace("/\R/", "<br>", $p);
        return "<p>{$p}</p>";
    }, $paras);

    return "<div class=\"pro_desc\">\n    " . implode("\n    ", $paras) . "\n</div>";
}

/* ========= PREPARE OPTIONAL HTML FIELDS ========= */
$features_html    = $hasFeatures ? build_features_html($featuresIn) : null;
$description_html = $hasDesc     ? build_description_html($descriptionIn) : null;
$short_desc_db    = $hasShortDesc ? (trim($short_description) !== '' ? $short_description : null) : null;

/* ========= DYNAMIC UPDATE ========= */
$setParts = [];
$types    = '';
$params   = [];

if ($hasName)        { $setParts[] = 'name = ?';             $types .= 's'; $params[] = $name; }
if ($hasCategoryId)  { $setParts[] = 'category_id = ?';      $types .= 'i'; $params[] = $category_id; }
if ($hasBrandId)     { $setParts[] = 'brand_id = ?';         $types .= 'i'; $params[] = $brand_id; }
if ($hasPrice)       { $setParts[] = 'price = ?';            $types .= 'd'; $params[] = $price; }
if ($hasUnit)        { $setParts[] = 'unit = ?';             $types .= 's'; $params[] = $unit; }
if ($hasFeatures)    { $setParts[] = 'features = ?';         $types .= 's'; $params[] = $features_html; }
if ($hasDesc)        { $setParts[] = 'description = ?';      $types .= 's'; $params[] = $description_html; }
if ($hasShortDesc)   { $setParts[] = 'short_description = ?';$types .= 's'; $params[] = $short_desc_db; }

$setSql = implode(', ', $setParts) . ', updated_at = NOW()';
$sql = "UPDATE t_products SET $setSql WHERE id = ?";

$types .= 'i';
$params[] = $product_id;

$st = $mysqli->prepare($sql);
if (!$st) {
    json_out(500, ['success' => false, 'message' => 'Prepare failed', 'error' => $mysqli->error]);
}
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
           p.features, p.description, p.short_description,
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
            'id'                 => (int)$row['id'],
            'name'               => $row['name'],
            'price'              => (float)$row['price'],
            'unit'               => $row['unit'] ?? null,
            'category_id'        => isset($row['category_id']) ? (int)$row['category_id'] : null,
            'category_name'      => $row['category_name'] ?? null,
            'brand_id'           => isset($row['brand_id']) ? (int)$row['brand_id'] : null,
            'brand_name'         => $row['brand_name'] ?? null,
            'features'           => $row['features'],          // HTML saved
            'description'        => $row['description'],       // HTML saved
            'short_description'  => $row['short_description']
        ]
    ]
]);
