<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($itemId <= 0) {
    show_error_page(404, 'ไม่พบประกาศ', 'ประกาศนี้อาจถูกลบไปแล้ว');
}

$statement = db_connect()->prepare(
    'SELECT items.*, users.full_name AS owner_name'
    . ' FROM items'
    . ' INNER JOIN users ON users.id = items.owner_id'
    . ' WHERE items.id = ?'
);
$statement->execute([$itemId]);
$item = $statement->fetch();

if ($item === false) {
    show_error_page(404, 'ไม่พบประกาศ', 'ประกาศนี้อาจถูกลบไปแล้ว');
}

$currentUser = current_user();
$isOwner = $currentUser !== null
    && (int) $item['owner_id'] === (int) $currentUser['id'];

$pageTitle = $item['title'];

require __DIR__ . '/includes/header.php';
?>
    <div class="item-badges">
        <span class="badge <?php echo $item['type'] === 'donate' ? 'badge-donate' : 'badge-exchange'; ?>">
            <?php echo $item['type'] === 'donate' ? 'Donate' : 'Exchange'; ?>
        </span>
        <span class="badge <?php echo $item['status'] === 'available' ? 'badge-available' : 'badge-completed'; ?>">
            <?php echo $item['status'] === 'available' ? 'Available' : 'Completed'; ?>
        </span>
    </div>

    <div class="detail-box">
        <h1><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h1>

        <p class="item-meta">
            โดย <?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?>
            &middot; ประกาศเมื่อ <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
            &middot; แก้ไขล่าสุด <?php echo date('d/m/Y H:i', strtotime($item['updated_at'])); ?>
        </p>

        <h2>รายละเอียด</h2>
        <p class="item-description"><?php echo nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')); ?></p>

        <h2>ช่องทางติดต่อ</h2>
        <p class="item-contact"><?php echo htmlspecialchars($item['contact'], ENT_QUOTES, 'UTF-8'); ?></p>

        <?php if ($isOwner) { ?>
            <div class="detail-actions">
                <a class="btn btn-secondary" href="item_edit.php?id=<?php echo $itemId; ?>">แก้ไข</a>

                <?php if ($item['status'] === 'available') { ?>
                    <form class="inline-form" method="post" action="item_complete.php?id=<?php echo $itemId; ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn">ทำเครื่องหมาย Completed</button>
                    </form>
                <?php } ?>

                <form class="inline-form" method="post" action="item_delete.php?id=<?php echo $itemId; ?>"
                      data-confirm="ต้องการลบประกาศนี้จริงหรือไม่?">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-danger">ลบ</button>
                </form>
            </div>
        <?php } ?>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
