<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();

$currentUser = current_user();

$pageTitle = 'ประกาศของฉัน';

$statement = db_connect()->prepare(
    'SELECT id, title, type, status, created_at FROM items WHERE owner_id = ?'
    . ' ORDER BY created_at DESC, id DESC'
);
$statement->execute([$currentUser['id']]);
$items = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
    <h1>ประกาศของฉัน</h1>

    <div class="page-actions">
        <a class="btn" href="item_create.php">สร้างประกาศใหม่</a>
    </div>

    <?php if (empty($items)) { ?>
        <div class="empty-state">
            <p><strong>คุณยังไม่มีประกาศ</strong></p>
            <p>กด "สร้างประกาศใหม่" เพื่อเริ่มต้น</p>
        </div>
    <?php } else { ?>
        <div class="item-list">
            <?php foreach ($items as $item) { ?>
                <div class="item-card">
                    <div class="item-badges">
                        <span class="badge <?php echo $item['type'] === 'donate' ? 'badge-donate' : 'badge-exchange'; ?>">
                            <?php echo $item['type'] === 'donate' ? 'Donate' : 'Exchange'; ?>
                        </span>
                        <span class="badge <?php echo $item['status'] === 'available' ? 'badge-available' : 'badge-completed'; ?>">
                            <?php echo $item['status'] === 'available' ? 'Available' : 'Completed'; ?>
                        </span>
                    </div>
                    <h2><a href="item_detail.php?id=<?php echo (int) $item['id']; ?>">
                        <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a></h2>
                    <p class="item-meta">
                        ประกาศเมื่อ <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                    </p>
                    <div class="item-actions">
                        <a class="btn btn-secondary" href="item_edit.php?id=<?php echo (int) $item['id']; ?>">แก้ไข</a>

                        <?php if ($item['status'] === 'available') { ?>
                            <form class="inline-form" method="post"
                                  action="item_complete.php?id=<?php echo (int) $item['id']; ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn">ทำเครื่องหมาย Completed</button>
                            </form>
                        <?php } ?>

                        <form class="inline-form" method="post"
                              action="item_delete.php?id=<?php echo (int) $item['id']; ?>"
                              data-confirm="ต้องการลบประกาศนี้จริงหรือไม่?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger">ลบ</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
