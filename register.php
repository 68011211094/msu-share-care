<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/validation.php';

$pageTitle = 'สมัครสมาชิก';

$errors = [];
$oldValues = [
    'full_name' => '',
    'email' => '',
    'contact_info' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/csrf.php';
    require_once __DIR__ . '/includes/db_connect.php';

    $oldValues['full_name'] = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $oldValues['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';
    $oldValues['contact_info'] = isset($_POST['contact_info']) ? trim($_POST['contact_info']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $passwordConfirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

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

    if ($passwordConfirm === '' || $password !== $passwordConfirm) {
        $errors[] = 'รหัสผ่านยืนยันไม่ตรงกัน';
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
            'INSERT INTO users (full_name, email, password_hash, contact_info) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([
            $oldValues['full_name'],
            $oldValues['email'],
            $passwordHash,
            $contactInfo,
        ]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) db_connect()->lastInsertId();

        header('Location: index.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>
    <h1>สมัครสมาชิก</h1>

    <div class="form-box">
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="post" action="register.php" novalidate>
            <?php echo csrf_field(); ?>

            <label for="full_name">ชื่อ-นามสกุล *</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" required
                   value="<?php echo htmlspecialchars($oldValues['full_name'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="email">อีเมล *</label>
            <input type="email" id="email" name="email" maxlength="150" required
                   value="<?php echo htmlspecialchars($oldValues['email'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="password">รหัสผ่าน * (อย่างน้อย 8 ตัวอักษร)</label>
            <input type="password" id="password" name="password" minlength="8" required>

            <label for="password_confirm">ยืนยันรหัสผ่าน *</label>
            <input type="password" id="password_confirm" name="password_confirm" minlength="8" required>

            <label for="contact_info">ช่องทางติดต่อ (ไม่บังคับ เช่น เบอร์โทร)</label>
            <input type="text" id="contact_info" name="contact_info" maxlength="150"
                   value="<?php echo htmlspecialchars($oldValues['contact_info'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-actions">
                <button type="submit" class="btn">สมัครสมาชิก</button>
            </div>

            <p class="form-hint">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </form>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
