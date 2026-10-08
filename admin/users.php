<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'จัดการผู้ใช้';

$statement = db_connect()->query(
    'SELECT users.id, users.full_name, users.email, users.role, users.created_at,'
    . ' (SELECT COUNT(*) FROM items WHERE items.owner_id = users.id) AS item_count'
    . ' FROM users'
    . ' ORDER BY users.id'
);
$users = $statement->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
    <h1>จัดการผู้ใช้</h1>
    <p>ผู้ใช้ทั้งหมด <?php echo count($users); ?> คน</p>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>อีเมล</th>
                    <th>บทบาท</th>
                    <th>ประกาศ</th>
                    <th>สมัครเมื่อ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <td><?php echo (int) $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                            </span>
                        </td>
                        <td><?php echo (int) $user['item_count']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
