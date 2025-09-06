<?php
// configs/db_connect.php
// Simple mysqli connection + tiny helpers (JSON only)

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'sakberally_';

// For Server
$DB_HOST = 'localhost';
$DB_USER = 'sakberally_';
$DB_PASS = '@7Uvqi429';
$DB_NAME = 'sakberally_';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'DB connection failed',
        'error'   => $mysqli->connect_error
    ]);
    exit;
}
$mysqli->set_charset('utf8mb4');

function json_out(int $status, array $payload) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// 16-char alphanumeric token (A-Z, a-z, 0-9)
function make_token16(): string {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $len = strlen($chars);
    $out = '';
    for ($i = 0; $i < 16; $i++) {
        $out .= $chars[random_int(0, $len - 1)];
    }
    return $out;
}
