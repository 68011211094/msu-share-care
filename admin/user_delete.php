<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/image_upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

require_admin();

if (!csrf_verify()) {
    header('Location: users.php');
    exit;
}

$currentAdmin = current_user();

$deleteId = isset($_POST['delete_id']) ? (int) $_POST['delete_id'] : 0;

if ($deleteId <= 0) {
    header('Location: users.php');
    exit;
}

$statement = db_connect()->prepare('SELECT * FROM users WHERE id = ?');
$statement->execute([$deleteId]);
$user = $statement->fetch();

if ($user === false) {
    header('Location: users.php');
    exit;
}

if ((int) $user['id'] === (int) $currentAdmin['id']) {
    show_error_page(400, 'ไม่สามารถลบได้', 'ไม่สามารถลบบัญชีของตัวเองได้');
}

if ($user['role'] === 'admin') {
    $adminCount = (int) db_connect()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    if ($adminCount <= 1) {
        show_error_page(400, 'ไม่สามารถลบได้', 'ไม่สามารถลบแอดมินคนสุดท้ายได้');
    }
}

$statement = db_connect()->prepare('SELECT image FROM items WHERE owner_id = ?');
$statement->execute([$deleteId]);

foreach ($statement->fetchAll() as $itemImageRow) {
    if ($itemImageRow['image'] !== null && $itemImageRow['image'] !== '') {
        delete_uploaded_image($itemImageRow['image']);
    }
}

$statement = db_connect()->prepare('DELETE FROM users WHERE id = ?');
$statement->execute([$deleteId]);

header('Location: users.php');
exit;