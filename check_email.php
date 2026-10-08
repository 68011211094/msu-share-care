<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if ($email === '' || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['valid' => false, 'available' => false]);
    exit;
}

$statement = db_connect()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$statement->execute([$email]);
$exists = $statement->fetch() !== false;

echo json_encode(['valid' => true, 'available' => !$exists]);
