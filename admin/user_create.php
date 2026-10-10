<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';

require_admin();

$pageTitle = 'เพิ่มผู้ใช้';

$errors = [];
$oldValues = [
    'full_name' => '',
    'email' => '',
    'role' => 'user',
    'contact_info' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldValues['full_name'] = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $oldValues['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';
    $oldValues['role'] = isset($_POST['role']) ? $_POST['role'] : '';
    $oldValues['contact_info'] = isset($_POST['contact_info']) ? trim($_POST['contact_info']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!csrf_verify()) {
        $errors[] = 'เซสชันหมดอายุ กรุณาลองใหม่อีกครั้ง';
    }

    $error = validate_required($oldValues['full_name'], 'ชื่อ-นามสกุล');
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_max_length($oldValues['full_name'], 'ชื่อ-นามสกุล', 100);
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_required($oldValues['email'], 'อีเมล');
    if ($error !== '') {
        $errors[] = $error;
    } else {
        $error = validate_email_format($oldValues['email']);
        if ($error !== '') {
            $errors[] = $error;
        }

        $error = validate_max_length($oldValues['email'], 'อีเมล', 150);
        if ($error !== '') {
            $errors[] = $error;
        }
    }

    $error = validate_required($password, 'รหัสผ่าน');
    if ($error !== '') {
        $errors[] = $error;
    } else {
        $error = validate_min_length($password, 'รหัสผ่าน', 8);
        if ($error !== '') {
            $errors[] = $error;
        }
    }

    if (!in_array($oldValues['role'], ['user', 'admin'], true)) {
        $errors[] = 'บทบาทไม่ถูกต้อง';
    }

    if ($oldValues['contact_info'] !== '') {
        $error = validate_max_length($oldValues['contact_info'], 'ช่องทางติดต่อ', 150);
        if ($error !== '') {
            $errors[] = $error;
        }
    }

    if (empty($errors)) {
        $statement = db_connect()->prepare('SELECT id FROM users WHERE email = ?');
        $statement->execute([$oldValues['email']]);

        if ($statement->fetch() !== false) {
            $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $contactInfo = $oldValues['contact_info'] === '' ? null : $oldValues['contact_info'];

        $statement = db_connect()->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, contact_info) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $oldValues['full_name'],
            $oldValues['email'],
            $passwordHash,
            $oldValues['role'],
            $contactInfo,
        ]);

        header('Location: users.php');
        exit;
    }
}

require __DIR__ . '/../includes/header.php';
?>
    <div class="admin-layout">
        <?php require __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="admin-content">
            <div class="page-head">
                <h1>เพิ่มผู้ใช้</h1>
                <p class="page-sub">สร้างบัญชีผู้ใช้ใหม่ในระบบ</p>
            </div>

            <div class="form-box">
                <?php if (!empty($errors)) { ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error) { ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>

                <form method="post" action="user_create.php">
                    <?php echo csrf_field(); ?>

                    <label for="full_name">ชื่อ-นามสกุล <span class="label-required">*</span></label>
                    <input type="text" id="full_name" name="full_name" maxlength="100" required
                           value="<?php echo htmlspecialchars($oldValues['full_name'], ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="email">อีเมล <span class="label-required">*</span></label>
                    <input type="email" id="email" name="email" maxlength="150" required
                           value="<?php echo htmlspecialchars($oldValues['email'], ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="password">รหัสผ่าน <span class="label-required">*</span> (อย่างน้อย 8 ตัวอักษร)</label>
                    <input type="password" id="password" name="password" minlength="8" required>

                    <label for="role">บทบาท <span class="label-required">*</span></label>
                    <select id="role" name="role">
                        <option value="user" <?php echo $oldValues['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $oldValues['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>

                    <label for="contact_info">ช่องทางติดต่อ (ไม่บังคับ เช่น เบอร์โทร)</label>
                    <input type="text" id="contact_info" name="contact_info" maxlength="150"
                           value="<?php echo htmlspecialchars($oldValues['contact_info'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-actions">
                        <button type="submit" class="btn">บันทึกผู้ใช้</button>
                        <a class="btn btn-secondary" href="users.php">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>