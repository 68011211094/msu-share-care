<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db_connect.php';

$pageTitle = 'หน้าแรก';

$statement = db_connect()->query(
    'SELECT items.id, items.title, items.type, items.status, items.created_at, items.image,'
    . ' users.full_name AS owner_name'
    . ' FROM items'
    . ' INNER JOIN users ON users.id = items.owner_id'
    . ' ORDER BY items.created_at DESC, items.id DESC'
);
$items = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
    <h1>MSU Share &amp; Care</h1>
    <p>กระดานแจ้งแบ่งปันของสำหรับนักศึกษามหาวิทยาลัยมหาสารคาม</p>

    <?php if (empty($items)) { ?>
        <div class="empty-state">
            <p><strong>ยังไม่มีประกาศ</strong></p>
            <p>รายการประกาศจะแสดงที่นี่</p>
        </div>
    <?php } else { ?>
        <div class="item-list">
            <?php foreach ($items as $item) { ?>
                <div class="item-card">
                    <?php if (!empty($item['image'])) { ?>
                        <a class="item-thumb" href="item_detail.php?id=<?php echo (int) $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        </a>
                    <?php } ?>
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
                        โดย <?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?>
                        &middot; <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
