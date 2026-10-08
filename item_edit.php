<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/image_upload.php';

require_login();

$currentUser = current_user();

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($itemId <= 0) {
    show_error_page(404, 'ไม่พบประกาศ', 'ประกาศนี้อาจถูกลบไปแล้ว');
}

$item = require_owned_item($itemId);

$pageTitle = 'แก้ไขประกาศ';

$errors = [];
$oldValues = [
    'title' => $item['title'],
    'description' => $item['description'],
    'type' => $item['type'],
    'contact' => $item['contact'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldValues['title'] = isset($_POST['title']) ? trim($_POST['title']) : '';
    $oldValues['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
    $oldValues['type'] = isset($_POST['type']) ? $_POST['type'] : '';
    $oldValues['contact'] = isset($_POST['contact']) ? trim($_POST['contact']) : '';

    if (!csrf_verify()) {
        $errors[] = 'เซสชันหมดอายุ กรุณาลองใหม่อีกครั้ง';
    }

    $error = validate_required($oldValues['title'], 'ชื่อสิ่งของ');
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_max_length($oldValues['title'], 'ชื่อสิ่งของ', 120);
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_required($oldValues['description'], 'รายละเอียด');
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_max_length($oldValues['description'], 'รายละเอียด', 5000);
    if ($error !== '') {
        $errors[] = $error;
    }

    if (!in_array($oldValues['type'], ['donate', 'exchange'], true)) {
        $errors[] = 'ประเภทประกาศไม่ถูกต้อง';
    }

    $error = validate_required($oldValues['contact'], 'ช่องทางติดต่อ');
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_max_length($oldValues['contact'], 'ช่องทางติดต่อ', 150);
    if ($error !== '') {
        $errors[] = $error;
    }

    $oldImage = null;

    if (empty($errors)) {
        $newUpload = handle_image_upload($errors);
    }

    if (empty($errors)) {
        $imagePath = $item['image'];

        if ($newUpload !== null) {
            $oldImage = $item['image'];
            $imagePath = $newUpload;
        } elseif (isset($_POST['remove_image']) && $item['image'] !== null) {
            $oldImage = $item['image'];
            $imagePath = null;
        }

        $statement = db_connect()->prepare(
            'UPDATE items SET title = ?, description = ?, type = ?, contact = ?, image = ?'
            . ' WHERE id = ? AND owner_id = ?'
        );
        $statement->execute([
            $oldValues['title'],
            $oldValues['description'],
            $oldValues['type'],
            $oldValues['contact'],
            $imagePath,
            $itemId,
            $currentUser['id'],
        ]);

        if ($oldImage !== null) {
            delete_uploaded_image($oldImage);
        }

        header('Location: item_detail.php?id=' . $itemId);
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>
    <h1>แก้ไขประกาศ</h1>

    <div class="form-box">
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="post" action="item_edit.php?id=<?php echo $itemId; ?>"
              enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <label for="title">ชื่อสิ่งของ *</label>
            <input type="text" id="title" name="title" maxlength="120" required
                   value="<?php echo htmlspecialchars($oldValues['title'], ENT_QUOTES, 'UTF-8'); ?>">

            <label>ประเภทประกาศ *</label>
            <label class="radio-label">
                <input type="radio" name="type" value="donate"
                    <?php echo $oldValues['type'] === 'donate' ? 'checked' : ''; ?>>
                Donate (บริจาค)
            </label>
            <label class="radio-label">
                <input type="radio" name="type" value="exchange"
                    <?php echo $oldValues['type'] === 'exchange' ? 'checked' : ''; ?>>
                Exchange (แลกเปลี่ยน)
            </label>

            <label for="description">รายละเอียด *</label>
            <textarea id="description" name="description" maxlength="5000" required
                      ><?php echo htmlspecialchars($oldValues['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="contact">ช่องทางติดต่อ *</label>
            <input type="text" id="contact" name="contact" maxlength="150" required
                   value="<?php echo htmlspecialchars($oldValues['contact'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="image">รูปภาพ (ไม่บังคับ — ขนาดไม่เกิน 2 MB, รองรับ JPG/PNG/GIF)</label>
            <?php if (!empty($item['image'])) { ?>
                <p>
                    <img class="detail-image" src="<?php echo htmlspecialchars(app_url($item['image']), ENT_QUOTES, 'UTF-8'); ?>"
                         alt="รูปภาพปัจจุบัน">
                </p>
            <?php } ?>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
            <?php if (!empty($item['image'])) { ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="remove_image" value="1"> ลบรูปภาพนี้
                </label>
            <?php } ?>

            <div class="form-actions">
                <button type="submit" class="btn">บันทึกการแก้ไข</button>
                <a class="btn btn-secondary" href="item_detail.php?id=<?php echo $itemId; ?>">ยกเลิก</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
