<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        header('Location: items.php');
        exit;
    }

    $deleteId = isset($_POST['delete_id']) ? (int) $_POST['delete_id'] : 0;

    if ($deleteId > 0) {
        $statement = db_connect()->prepare('DELETE FROM items WHERE id = ?');
        $statement->execute([$deleteId]);
    }

    header('Location: items.php');
    exit;
}

$pageTitle = 'จัดการประกาศ';

$statement = db_connect()->query(
    'SELECT items.id, items.title, items.type, items.status, items.created_at,'
    . ' users.full_name AS owner_name'
    . ' FROM items'
    . ' INNER JOIN users ON users.id = items.owner_id'
    . ' ORDER BY items.created_at DESC, items.id DESC'
);
$items = $statement->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
    <h1>จัดการประกาศ</h1>
    <p>ประกาศทั้งหมด <?php echo count($items); ?> รายการ</p>

    <?php if (empty($items)) { ?>
        <div class="empty-state">
            <p><strong>ยังไม่มีประกาศ</strong></p>
        </div>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อสิ่งของ</th>
                        <th>เจ้าของ</th>
                        <th>ประเภท</th>
                        <th>สถานะ</th>
                        <th>ประกาศเมื่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td><?php echo (int) $item['id']; ?></td>
                            <td>
                                <a href="<?php echo app_url('item_detail.php?id=' . (int) $item['id']); ?>">
                                    <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?php echo $item['type'] === 'donate' ? 'badge-donate' : 'badge-exchange'; ?>">
                                    <?php echo $item['type'] === 'donate' ? 'Donate' : 'Exchange'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $item['status'] === 'available' ? 'badge-available' : 'badge-completed'; ?>">
                                    <?php echo $item['status'] === 'available' ? 'Available' : 'Completed'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></td>
                            <td>
                                <form class="inline-form" method="post" action="items.php"
                                      data-confirm="ต้องการลบประกาศนี้จริงหรือไม่?">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $item['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
