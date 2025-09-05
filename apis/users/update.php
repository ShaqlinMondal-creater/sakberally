<?php
// update_user.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

// --- Get acting user id from query param (?id=) ---
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    json_out(422, ['success' => false, 'message' => 'User ID (param ?id=) is required']);
}

// --- Read JSON body (must include token) ---
$in = read_json_body();
$token = trim((string)($in['token'] ?? ''));
if ($token === '') {
    json_out(401, ['success' => false, 'message' => 'Token required']);
}

// --- Verify user is admin and token matches ---
$stmt = $mysqli->prepare("SELECT id, role, token FROM t_users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$actor = $res->fetch_assoc();
$stmt->close();

if (!$actor) {
    json_out(404, ['success' => false, 'message' => 'User not found']);
}
if ($actor['role'] !== 'admin') {
    json_out(403, ['success' => false, 'message' => 'Access denied: role must be admin']);
}
if ($actor['token'] !== $token) {
    json_out(401, ['success' => false, 'message' => 'Invalid token']);
}

// --- Read update fields (excluding token) ---
$name     = array_key_exists('name', $in)     ? trim((string)$in['name']) : null;
$email    = array_key_exists('email', $in)    ? strtolower(trim((string)$in['email'])) : null;
$role     = array_key_exists('role', $in)     ? trim((string)$in['role']) : null;
$password = array_key_exists('password', $in) ? (string)$in['password'] : null;

// Build dynamic update list
$fields = [];
$params = [];
$types  = '';

if ($name !== null) {
    $fields[] = "name = ?";
    $params[] = $name;
    $types   .= 's';
}
if ($email !== null) {
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_out(422, ['success' => false, 'message' => 'Invalid email']);
    }
    $fields[] = "email = ?";
    $params[] = $email;
    $types   .= 's';
}
if ($role !== null) {
    $fields[] = "role = ?";
    $params[] = $role;
    $types   .= 's';
}
if ($password !== null) {
    if (strlen($password) < 6) {
        json_out(422, ['success' => false, 'message' => 'Password must be at least 6 characters']);
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $fields[] = "password = ?";
    $params[] = $hash;
    $types   .= 's';
}

if (empty($fields)) {
    json_out(422, ['success' => false, 'message' => 'No fields to update']);
}

// Update
$sql = "UPDATE t_users SET ".implode(', ', $fields).", updated_at = NOW() WHERE id = ?";
$params[] = $userId;
$types   .= 'i';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Update failed', 'error' => $stmt->error]);
}
$stmt->close();

// Return updated record (without password, created_at, updated_at)
$get = $mysqli->prepare("SELECT id, name, email, mobile, role, token FROM t_users WHERE id = ? LIMIT 1");
$get->bind_param('i', $userId);
$get->execute();
$res = $get->get_result();
$user = $res->fetch_assoc();
$get->close();

json_out(200, [
    'success' => true,
    'message' => 'Updated successfully',
    'data'    => $user
]);
