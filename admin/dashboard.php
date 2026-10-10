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

$recentItems = db_connect()->query(
    'SELECT items.id, items.title, items.status, users.full_name AS owner_name'
    . ' FROM items INNER JOIN users ON users.id = items.owner_id'
    . ' ORDER BY items.created_at DESC, items.id DESC LIMIT 5'
)->fetchAll();

$recentUsers = db_connect()->query(
    'SELECT id, full_name, email, role FROM users'
    . ' ORDER BY id DESC LIMIT 5'
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
    <div class="admin-layout">
        <?php require __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="admin-content">
            <div class="page-head">
                <h1>ภาพรวมระบบ</h1>
                <p class="page-sub">สรุปข้อมูลผู้ใช้และประกาศทั้งหมดบน MSU Share &amp; Care</p>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['total_users']; ?></p>
                    <p class="stat-label">ผู้ใช้ทั้งหมด</p>
                </div>
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['total_items']; ?></p>
                    <p class="stat-label">ประกาศทั้งหมด</p>
                </div>
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['available_items']; ?></p>
                    <p class="stat-label">Available</p>
                </div>
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['completed_items']; ?></p>
                    <p class="stat-label">Completed</p>
                </div>
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['donate_items']; ?></p>
                    <p class="stat-label">Donate</p>
                </div>
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                    </span>
                    <p class="stat-number"><?php echo (int) $stats['exchange_items']; ?></p>
                    <p class="stat-label">Exchange</p>
                </div>
            </div>

            <div class="admin-links">
                <a class="btn btn-outline" href="<?php echo app_url('admin/users.php'); ?>">จัดการผู้ใช้</a>
                <a class="btn btn-outline" href="<?php echo app_url('admin/items.php'); ?>">จัดการประกาศ</a>
            </div>

            <?php if (!empty($recentItems)) { ?>
                <div class="section-head">
                    <h2>ประกาศล่าสุด</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อสิ่งของ</th>
                                <th>เจ้าของ</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentItems as $item) { ?>
                                <tr>
                                    <td><?php echo (int) $item['id']; ?></td>
                                    <td>
                                        <a href="<?php echo app_url('item_detail.php?id=' . (int) $item['id']); ?>">
                                            <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $item['status'] === 'available' ? 'badge-available' : 'badge-completed'; ?>">
                                            <?php echo $item['status'] === 'available' ? 'Available' : 'Completed'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <?php if (!empty($recentUsers)) { ?>
                <div class="section-head">
                    <h2>ผู้ใช้ล่าสุด</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th>บทบาท</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user) { ?>
                                <tr>
                                    <td><?php echo (int) $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>