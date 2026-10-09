<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_items.php');
    exit;
}

require_login();

if (!csrf_verify()) {
    header('Location: my_items.php');
    exit;
}

$currentUser = current_user();

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($itemId <= 0) {
    show_error_page(404, 'ไม่พบประกาศ', 'ประกาศนี้อาจถูกลบไปแล้ว');
}

$item = require_owned_item($itemId);

if ($currentUser['role'] === 'admin') {
    $statement = db_connect()->prepare(
        "UPDATE items SET status = 'completed' WHERE id = ? AND status = 'available'"
    );
    $statement->execute([$itemId]);
} else {
    $statement = db_connect()->prepare(
        "UPDATE items SET status = 'completed' WHERE id = ? AND owner_id = ? AND status = 'available'"
    );
    $statement->execute([$itemId, $currentUser['id']]);
}

header('Location: ' . ($currentUser['role'] === 'admin' ? app_url('admin/items.php') : 'item_detail.php?id=' . $itemId));
exit;
