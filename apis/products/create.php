<?php
// products/create.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body(); // decode application/json

/* ========= AUTH VALIDATION ========= */
$token = trim($in['token'] ?? '');
if ($token === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}

// Check token and role
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$u->close();

if (!$user) {
    json_out(401, ['success' => false, 'message' => 'Invalid token']);
}
if (strtolower($user['role']) !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Only admin can add products']);
}

// ===== Extract =====
$name        = isset($in['name']) ? trim((string)$in['name']) : '';
$price_raw   = $in['price'] ?? null;
$unit        = isset($in['unit']) ? trim((string)$in['unit']) : '';
$category_id = isset($in['category_id']) ? (int)$in['category_id'] : 0;
$brand_id    = isset($in['brand_id']) ? (int)$in['brand_id'] : 0;

$featuresIn        = $in['features'] ?? null;              // string | array | object | html
$descriptionIn     = isset($in['description']) ? $in['description'] : null; // string | html
$short_description = isset($in['short_description']) ? (string)$in['short_description'] : null;

// ===== Validate =====
$errors = [];
if ($name === '')                        $errors[] = 'name is required';
if ($unit === '')                        $errors[] = 'unit is required';
if (!is_numeric($price_raw))             $errors[] = 'price must be numeric';
$price = $errors ? null : (float)$price_raw;
if ($category_id <= 0)                   $errors[] = 'category_id is required (>0)';
if ($brand_id <= 0)                      $errors[] = 'brand_id is required (>0)';
if ($errors) {
    json_out(422, ['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
}

// ===== Helpers =====
function is_html_like($s) {
    return is_string($s) && preg_match('/^\s*</', $s) === 1;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function prettify_key($k) {
    $k = trim((string)$k);
    $k = str_replace(['_', '-'], ' ', $k);
    $k = preg_replace('/\s+/', ' ', $k);
    return strtolower($k); // you showed lowercase labels
}
function build_features_html($features) {
    // Already HTML? store as-is
    if (is_html_like($features)) return (string)$features;

    $rows = [];

    if (is_array($features)) {
        // Associative (label => value) or list of pairs
        $isAssoc = array_keys($features) !== range(0, count($features) - 1);
        if ($isAssoc) {
            foreach ($features as $k => $v) {
                $label = prettify_key($k);
                $val   = is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $rows[] = '        <tr>' .
                          '            <td class="fw6">' . e($label) . '</td>' .
                          '            <td>' . e($val) . '</td>' .
                          '        </tr>';
            }
        } else {
            // Expect list like: [ ["color","Matte Black"], ["blades",3], ... ]
            foreach ($features as $pair) {
                if (is_array($pair) && count($pair) >= 2) {
                    $label = prettify_key($pair[0]);
                    $val   = is_scalar($pair[1]) ? (string)$pair[1] : json_encode($pair[1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $rows[] = '        <tr>' .
                              '            <td class="fw6">' . e($label) . '</td>' .
                              '            <td>' . e($val) . '</td>' .
                              '        </tr>';
                }
            }
        }
    } elseif (is_string($features) && trim($features) !== '') {
        // Plain string: store as a single row with "feature" label
        $rows[] = '        <tr><td class="fw6">feature</td><td>' . e($features) . '</td></tr>';
    }

    // Empty? store minimal table (or null). We'll keep a minimal table for consistency.
    if (empty($rows)) {
        $rows[] = '        <tr><td class="fw6">info</td><td>-</td></tr>';
    }

    return "<table class=\" tbl tble1\">\n    <tbody>\n" . implode("\n", $rows) . "\n    </tbody>\n</table>";
}

function build_description_html($desc) {
    if ($desc === null || $desc === '') return null;
    if (is_html_like($desc)) return (string)$desc;

    // Plain text → wrap in <div class="pro_desc"><p>…</p></div>
    // Split paragraphs on blank lines
    $text = (string)$desc;
    $paras = preg_split("/\R{2,}/", $text);
    $paras = array_map(function($p) {
        // Preserve single line breaks within paragraph as <br>
        $p = e($p);
        $p = preg_replace("/\R/", "<br>", $p);
        return "<p>{$p}</p>";
    }, $paras);

    return "<div class=\"pro_desc\">\n    " . implode("\n    ", $paras) . "\n</div>";
}

// ===== Build HTML payloads to store =====
$features_html    = build_features_html($featuresIn);
$description_html = build_description_html($descriptionIn);
$short_description_db = (isset($short_description) && trim($short_description) !== '') ? $short_description : null;

// ===== (Optional) FK checks =====
$chk = $mysqli->prepare("SELECT id FROM t_categories WHERE id = ? LIMIT 1");
$chk->bind_param('i', $category_id);
$chk->execute(); $catExists = $chk->get_result()->fetch_column(); $chk->close();
if (!$catExists) json_out(422, ['success' => false, 'message' => 'Invalid category_id']);

$chk = $mysqli->prepare("SELECT id FROM t_brands WHERE id = ? LIMIT 1");
$chk->bind_param('i', $brand_id);
$chk->execute(); $brandExists = $chk->get_result()->fetch_column(); $chk->close();
if (!$brandExists) json_out(422, ['success' => false, 'message' => 'Invalid brand_id']);

// ===== Insert =====
$sql = "INSERT INTO t_products
        (name, price, unit, category_id, brand_id, features, description, short_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$ins = $mysqli->prepare($sql);
if (!$ins) {
    json_out(500, ['success' => false, 'message' => 'Prepare failed', 'error' => $mysqli->error]);
}
$ins->bind_param(
    'sdsiisss', // s d s i i s s s
    $name,
    $price,
    $unit,
    $category_id,
    $brand_id,
    $features_html,
    $description_html,
    $short_description_db
);
if (!$ins->execute()) {
    $msg = $ins->error ?: 'Insert failed';
    $ins->close();
    json_out(500, ['success' => false, 'message' => 'Failed to create product', 'error' => $msg]);
}
$productId = (int)$ins->insert_id;
$ins->close();

// ===== Response =====
json_out(201, [
    'success' => true,
    'message' => 'Product created',
    'data' => [
        'product' => [
            'id' => $productId,
            'name' => $name,
            'price' => (float)$price,
            'unit' => $unit,
            'category_id' => $category_id,
            'brand_id' => $brand_id,
            'features' => $features_html,
            'description' => $description_html,
            'short_description' => $short_description_db
        ]
    ]
]);
