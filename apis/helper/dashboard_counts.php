<?php
// dashboard_counts.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

// Helper to count rows in a table
function table_count(mysqli $mysqli, string $table): int {
    $sql = "SELECT COUNT(*) AS cnt FROM {$table}";
    $res = $mysqli->query($sql);
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    return (int)($row['cnt'] ?? 0);
}

$counts = [
    'users'      => table_count($mysqli, 't_users'),
    'brands'     => table_count($mysqli, 't_brands'),
    'categories' => table_count($mysqli, 't_categories'),
    'products'   => table_count($mysqli, 't_products'),
    'uploads'    => table_count($mysqli, 't_uploads'),
    'inquiries'  => table_count($mysqli, 't_inquiry_contact'),
    'sheets'     => table_count($mysqli, 't_sheets'),
];

json_out(200, [
    'success' => true,
    'message' => 'Dashboard counts',
    'data'    => $counts
]);
