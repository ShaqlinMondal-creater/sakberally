<?php
// delete_category.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in      = read_json_body();
$token   = trim((string)($in['token'] ?? ''));
$catId   = (int)($in['category_id'] ?? ($in['id'] ?? 0));

if ($token === '' || $catId <= 0) {
    json_out(422, ['success' => false, 'message' => 'token and category_id are required']);
}

// verify admin token
$u = $mysqli->prepare("SELECT id, role FROM t_users WHERE token = ? LIMIT 1");
$u->bind_param('s', $token);
$u->execute();
$actor = $u->get_result()->fetch_assoc();
$u->close();
if (!$actor || $actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied (invalid token or not admin)']);
}

// fetch category + image path
$c = $mysqli->prepare("
  SELECT c.id, c.name, c.sort_no, c.category_image_id, u.file_path AS category_image_path
  FROM t_categories c
  LEFT JOIN t_uploads u ON u.id = c.category_image_id
  WHERE c.id = ? LIMIT 1
");
$c->bind_param('i', $catId);
$c->execute();
$cat = $c->get_result()->fetch_assoc();
$c->close();

if (!$cat) {
    json_out(404, ['success' => false, 'message' => 'Category not found']);
}

// helper: delete upload row + physical file
function delete_upload_and_file(mysqli $mysqli, ?int $uploadId): void {
    if (!$uploadId) return;
    $q = $mysqli->prepare("SELECT file_path FROM t_uploads WHERE id = ? LIMIT 1");
    $q->bind_param('i', $uploadId);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    $q->close();

    if (!empty($row['file_path'])) {
        $p = $row['file_path'];
        if (file_exists($p)) @unlink($p);
        $alt = '../' . ltrim($p, '/\\');
        if (file_exists($alt)) @unlink($alt);
    }

    $d = $mysqli->prepare("DELETE FROM t_uploads WHERE id = ? LIMIT 1");
    $d->bind_param('i', $uploadId);
    $d->execute();
    $d->close();
}

// delete image (if any)
if (!empty($cat['category_image_id'])) {
    delete_upload_and_file($mysqli, (int)$cat['category_image_id']);
}

// delete category
$del = $mysqli->prepare("DELETE FROM t_categories WHERE id = ? LIMIT 1");
$del->bind_param('i', $catId);
if (!$del->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to delete category', 'error' => $del->error]);
}
$del->close();

json_out(200, [
    'success' => true,
    'message' => 'Category deleted',
    'data' => [
        'token'               => $token,
        'category_id'         => (int)$cat['id'],
        'name'                => $cat['name'],
        'sort_no'             => (int)$cat['sort_no'],
        'category_image_id'   => $cat['category_image_id'] ? (int)$cat['category_image_id'] : null,
        'category_image_path' => $cat['category_image_path'] ?? null
    ]
]);
