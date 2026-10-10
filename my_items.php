<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();

$currentUser = current_user();

$pageTitle = 'ประกาศของฉัน';

$statement = db_connect()->prepare(
    'SELECT id, title, type, status, created_at, image FROM items WHERE owner_id = ?'
    . ' ORDER BY created_at DESC, id DESC'
);
$statement->execute([$currentUser['id']]);
$items = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
    <div class="page-head">
        <h1>ประกาศของฉัน</h1>
        <p class="page-sub">จัดการประกาศที่คุณลงไว้ เพื่อบริจาคหรือแลกเปลี่ยนสิ่งของ</p>
    </div>

    <div class="page-actions">
        <a class="btn" href="item_create.php">+ สร้างประกาศใหม่</a>
    </div>

    <?php if (empty($items)) { ?>
        <div class="empty-state">
            <span class="empty-icon" aria-hidden="true">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </span>
            <p><strong>คุณยังไม่มีประกาศ</strong></p>
            <p>กด "สร้างประกาศใหม่" เพื่อเริ่มต้นแบ่งปันสิ่งของชิ้นแรกของคุณ</p>
            <a class="btn" href="item_create.php">สร้างประกาศใหม่</a>
        </div>
    <?php } else { ?>
        <div class="item-list">
            <?php foreach ($items as $item) { ?>
                <div class="item-card">
                    <a class="item-thumb" href="item_detail.php?id=<?php echo (int) $item['id']; ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($item['image'])) { ?>
                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <?php } else { ?>
                            <span class="item-thumb-placeholder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </span>
                        <?php } ?>
                    </a>
                    <div class="item-card-body">
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
                            <a class="btn btn-outline btn-small" href="item_edit.php?id=<?php echo (int) $item['id']; ?>">แก้ไข</a>

                            <?php if ($item['status'] === 'available') { ?>
                                <form class="inline-form" method="post"
                                      action="item_complete.php?id=<?php echo (int) $item['id']; ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-secondary btn-small">ทำเครื่องหมาย Completed</button>
                                </form>
                            <?php } ?>

                            <form class="inline-form" method="post"
                                  action="item_delete.php?id=<?php echo (int) $item['id']; ?>"
                                  data-confirm="ต้องการลบประกาศนี้จริงหรือไม่?">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-danger btn-small">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
<?php require __DIR__ . '/includes/footer.php'; ?>