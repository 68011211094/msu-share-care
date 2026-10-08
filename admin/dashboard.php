<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Admin Dashboard';

$statement = db_connect()->query(
    'SELECT'
    . ' (SELECT COUNT(*) FROM users) AS total_users,'
    . ' (SELECT COUNT(*) FROM items) AS total_items,'
    . " (SELECT COUNT(*) FROM items WHERE status = 'available') AS available_items,"
    . " (SELECT COUNT(*) FROM items WHERE status = 'completed') AS completed_items,"
    . " (SELECT COUNT(*) FROM items WHERE type = 'donate') AS donate_items,"
    . " (SELECT COUNT(*) FROM items WHERE type = 'exchange') AS exchange_items"
);
$stats = $statement->fetch();

require __DIR__ . '/../includes/header.php';
?>
    <h1>Admin Dashboard</h1>

    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['total_users']; ?></p>
            <p class="stat-label">ผู้ใช้ทั้งหมด</p>
        </div>
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['total_items']; ?></p>
            <p class="stat-label">ประกาศทั้งหมด</p>
        </div>
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['available_items']; ?></p>
            <p class="stat-label">Available</p>
        </div>
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['completed_items']; ?></p>
            <p class="stat-label">Completed</p>
        </div>
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['donate_items']; ?></p>
            <p class="stat-label">Donate</p>
        </div>
        <div class="stat-card">
            <p class="stat-number"><?php echo (int) $stats['exchange_items']; ?></p>
            <p class="stat-label">Exchange</p>
        </div>
    </div>

    <div class="admin-links">
        <a class="btn btn-secondary" href="<?php echo app_url('admin/users.php'); ?>">จัดการผู้ใช้</a>
        <a class="btn btn-secondary" href="<?php echo app_url('admin/items.php'); ?>">จัดการประกาศ</a>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
