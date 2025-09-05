<?php
// logout.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body();
$token = trim((string)($in['token'] ?? ''));

if ($token === '') {
    json_out(422, ['success' => false, 'message' => 'token is required']);
}
// Optional: enforce 16-char alphanumeric format
if (!preg_match('/^[A-Za-z0-9]{16}$/', $token)) {
    json_out(422, ['success' => false, 'message' => 'Invalid token format']);
}

// Find user by token
$stmt = $mysqli->prepare("SELECT id, name, email, mobile, role FROM t_users WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    json_out(401, ['success' => false, 'message' => 'Invalid token']);
}

// Clear token
$upd = $mysqli->prepare("UPDATE t_users SET token = NULL, updated_at = NOW() WHERE id = ?");
$upd->bind_param('i', $user['id']);
if (!$upd->execute()) {
    json_out(500, ['success' => false, 'message' => 'Logout failed', 'error' => $upd->error]);
}
$upd->close();

// Respond (exclude created_at / updated_at)
json_out(200, [
    'success' => true,
    'message' => 'Logged out successfully',
    'data' => [
        'id'     => (int)$user['id'],
        'name'   => $user['name'],
        'email'  => $user['email'],
        'mobile' => $user['mobile'],
        'role'   => $user['role'],
        'token'  => null
    ]
]);
