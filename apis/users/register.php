<?php
// register.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in = read_json_body();
$name   = trim((string)($in['name'] ?? ''));
$email  = strtolower(trim((string)($in['email'] ?? '')));
$mobile = trim((string)($in['mobile'] ?? ''));
$pass   = (string)($in['password'] ?? '');
$role   = trim((string)($in['role'] ?? 'user'));

if ($email === '' || $pass === '') {
    json_out(422, ['success'=>false,'message'=>'email and password are required']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(422, ['success'=>false,'message'=>'Invalid email']);
}
if (strlen($pass) < 6) {
    json_out(422, ['success'=>false,'message'=>'Password must be at least 6 characters']);
}

// If you want to allow empty mobile in DB, make the column nullable or set a default.
if ($mobile === '') {
    $mobile = ''; // keep empty string (OK if column is NOT NULL UNIQUE? If unique, multiple empty strings will collide)
}

// Check duplicates (email or mobile when provided)
if ($mobile !== '') {
    $chk = $mysqli->prepare("SELECT id FROM t_users WHERE email = ? OR mobile = ? LIMIT 1");
    $chk->bind_param('ss', $email, $mobile);
} else {
    $chk = $mysqli->prepare("SELECT id FROM t_users WHERE email = ? LIMIT 1");
    $chk->bind_param('s', $email);
}
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    $chk->close();
    json_out(409, ['success'=>false,'message'=>'Email or mobile already exists']);
}
$chk->close();

$hash = password_hash($pass, PASSWORD_BCRYPT);

// Insert (token left NULL until login)
if ($mobile !== '') {
    $stmt = $mysqli->prepare("INSERT INTO t_users (name, email, mobile, password, role) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssss', $name, $email, $mobile, $hash, $role);
} else {
    // when mobile empty, insert as NULL to avoid unique collisions if column is UNIQUE+NULLable
    $stmt = $mysqli->prepare("INSERT INTO t_users (name, email, mobile, password, role) VALUES (?,?,?,?,?)");
    $nullMobile = '';
    $stmt->bind_param('sssss', $name, $email, $nullMobile, $hash, $role);
}

if (!$stmt->execute()) {
    json_out(500, ['success'=>false, 'message'=>'Registration failed', 'error'=>$stmt->error]);
}
$userId = (int)$stmt->insert_id;
$stmt->close();

// Build response WITHOUT created_at / updated_at
json_out(201, [
    'success' => true,
    'message' => 'Registered',
    'data' => [
        'id'     => $userId,
        'name'   => $name,
        'email'  => $email,
        'mobile' => $mobile,
        'role'   => $role,
        'token'  => null  // not set yet (will be set on login)
    ]
]);
