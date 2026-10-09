<?php

require_once __DIR__ . '/includes/init.php';
header('Content-Type: text/plain; charset=utf-8');


$allowed = ['127.0.0.1', '::1', 'localhost'];
$remote = $_SERVER['REMOTE_ADDR'] ?? '';

echo "HerMart Debug\n";
echo "============\n";
echo "DB ready: " . (db_ready() ? 'yes' : 'no') . "\n";
echo "DB error: " . db_error() . "\n\n";

$email = trim((string)($_GET['email'] ?? 'seller@hermart.com'));
$pw    = array_key_exists('pw', $_GET) ? $_GET['pw'] : null;

echo "Lookup: $email\n";
$user = db_select_one("SELECT id, name, email, phone, password_hash, role, status FROM users WHERE email = ? LIMIT 1", 's', [$email]);
if (!$user) {
    echo "User not found.\n";
    exit;
}

echo "User ID: " . (int)$user['id'] . "\n";
echo "Name: " . ($user['name'] ?? '') . "\n";
echo "Email: " . ($user['email'] ?? '') . "\n";
echo "Role: " . ($user['role'] ?? '') . "\n";
echo "Status: " . ($user['status'] ?? '') . "\n";
$hash = $user['password_hash'] ?? '';
echo "Password hash present: " . ($hash !== '' ? 'yes' : 'no') . " (len=" . strlen($hash) . ")\n";

if ($pw !== null) {
    $res = password_verify($pw, $hash) ? 'MATCH' : 'NO MATCH';
    echo "password_verify('$pw') => $res\n";
} else {
    echo "Provide ?pw= to run password_verify against the stored hash.\n";
}


$cnt = db_select_one("SELECT COUNT(*) AS c FROM products", '', []);
echo "\nProducts table count: " . ($cnt['c'] ?? 'n/a') . "\n";

echo "\nDone.\n";
