<?php
// delete_inquiry.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in      = read_json_body();
$token   = trim((string)($in['token'] ?? ''));
$inqId   = (int)($in['inquiry_id'] ?? ($in['id'] ?? 0));

if ($token === '' || $inqId <= 0) {
    json_out(422, ['success' => false, 'message' => 'token and inquiry_id are required']);
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

// fetch inquiry + upload path
$q = $mysqli->prepare("
  SELECT i.id, i.name, i.mobile, i.email, i.subject, i.messege, i.upload_id, u.file_path AS upload_path
  FROM t_inquiry_contact i
  LEFT JOIN t_uploads u ON u.id = i.upload_id
  WHERE i.id = ? LIMIT 1
");
$q->bind_param('i', $inqId);
$q->execute();
$inq = $q->get_result()->fetch_assoc();
$q->close();

if (!$inq) {
    json_out(404, ['success' => false, 'message' => 'Inquiry not found']);
}

// helper: delete upload row + physical file
function delete_upload_and_file(mysqli $mysqli, ?int $uploadId): void {
    if (!$uploadId) return;

    $s = $mysqli->prepare("SELECT file_path FROM t_uploads WHERE id = ? LIMIT 1");
    $s->bind_param('i', $uploadId);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();

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

// delete attachment if exists
if (!empty($inq['upload_id'])) {
    delete_upload_and_file($mysqli, (int)$inq['upload_id']);
}

// delete inquiry
$del = $mysqli->prepare("DELETE FROM t_inquiry_contact WHERE id = ? LIMIT 1");
$del->bind_param('i', $inqId);
if (!$del->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to delete inquiry', 'error' => $del->error]);
}
$del->close();

json_out(200, [
    'success' => true,
    'message' => 'Inquiry deleted',
    'data' => [
        'token'       => $token,
        'id'          => (int)$inq['id'],
        'name'        => $inq['name'],
        'mobile'      => $inq['mobile'],
        'email'       => $inq['email'],
        'subject'     => $inq['subject'],
        'messege'     => $inq['messege'],
        'upload_id'   => $inq['upload_id'] ? (int)$inq['upload_id'] : null,
        'upload_path' => $inq['upload_path'] ?? null
    ]
]);
