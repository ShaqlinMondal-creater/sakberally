<?php
// login.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body();
// Allow login by email OR mobile; client can send one of them
$login = trim((string)($in['email'] ?? $in['mobile'] ?? ''));
$pass  = (string)($in['password'] ?? '');

if ($login === '' || $pass === '') {
    json_out(422, ['success'=>false, 'message'=>'email/mobile and password are required']);
}

// Find user
if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
    $stmt = $mysqli->prepare("SELECT id, name, email, mobile, password, role, token FROM t_users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $login);
} else {
    $stmt = $mysqli->prepare("SELECT id, name, email, mobile, password, role, token FROM t_users WHERE mobile = ? LIMIT 1");
    $stmt->bind_param('s', $login);
}

$stmt->execute();
$res  = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($pass, $user['password'])) {
    json_out(401, ['success'=>false,'message'=>'Invalid credentials']);
}

// Generate and save fresh token
$newToken = make_token16();
$upd = $mysqli->prepare("UPDATE t_users SET token = ?, updated_at = NOW() WHERE id = ?");
$upd->bind_param('si', $newToken, $user['id']);
if (!$upd->execute()) {
    json_out(500, ['success'=>false,'message'=>'Could not update token','error'=>$upd->error]);
}
$upd->close();

// Respond (exclude created_at, updated_at)
json_out(200, [
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'id'     => (int)$user['id'],
        'name'   => $user['name'],
        'email'  => $user['email'],
        'mobile' => $user['mobile'],
        'role'   => $user['role'],
        'token'  => $newToken
    ]
]);
