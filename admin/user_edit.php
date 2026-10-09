<?php

$basePath = '../';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';

require_admin();

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($userId <= 0) {
    show_error_page(404, 'ไม่พบผู้ใช้', 'ไม่พบผู้ใช้ดังกล่าว');
}

$statement = db_connect()->prepare('SELECT * FROM users WHERE id = ?');
$statement->execute([$userId]);
$user = $statement->fetch();

if ($user === false) {
    show_error_page(404, 'ไม่พบผู้ใช้', 'ไม่พบผู้ใช้ดังกล่าว');
}

$currentAdmin = current_user();

$errors = [];
$oldValues = [
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'contact_info' => $user['contact_info'],
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

    if ($password !== '') {
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
        $statement = db_connect()->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $statement->execute([$oldValues['email'], $userId]);

        if ($statement->fetch() !== false) {
            $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
        }
    }

    $isSelf = (int) $user['id'] === (int) $currentAdmin['id'];

    if ($isSelf && $oldValues['role'] !== 'admin') {
        $errors[] = 'ไม่สามารถเปลี่ยนบทบาทของตัวเองจาก Admin เป็น User ได้';
    }

    if (empty($errors) && $user['role'] === 'admin' && $oldValues['role'] !== 'admin') {
        $adminCount = (int) db_connect()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

        if ($adminCount <= 1) {
            $errors[] = 'ไม่สามารถถอดบทบาทของแอดมินคนสุดท้ายได้';
        }
    }

    if (empty($errors)) {
        $contactInfo = $oldValues['contact_info'] === '' ? null : $oldValues['contact_info'];

        if ($password === '') {
            $statement = db_connect()->prepare(
                'UPDATE users SET full_name = ?, email = ?, role = ?, contact_info = ? WHERE id = ?'
            );
            $statement->execute([
                $oldValues['full_name'],
                $oldValues['email'],
                $oldValues['role'],
                $contactInfo,
                $userId,
            ]);
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $statement = db_connect()->prepare(
                'UPDATE users SET full_name = ?, email = ?, role = ?, contact_info = ?, password_hash = ? WHERE id = ?'
            );
            $statement->execute([
                $oldValues['full_name'],
                $oldValues['email'],
                $oldValues['role'],
                $contactInfo,
                $passwordHash,
                $userId,
            ]);
        }

        header('Location: users.php');
        exit;
    }
}

$pageTitle = 'แก้ไขผู้ใช้';

require __DIR__ . '/../includes/header.php';
?>
    <h1>แก้ไขผู้ใช้</h1>

    <div class="form-box">
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="post" action="user_edit.php?id=<?php echo (int) $userId; ?>">
            <?php echo csrf_field(); ?>

            <label for="full_name">ชื่อ-นามสกุล *</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" required
                   value="<?php echo htmlspecialchars($oldValues['full_name'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="email">อีเมล *</label>
            <input type="email" id="email" name="email" maxlength="150" required
                   value="<?php echo htmlspecialchars($oldValues['email'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="password">รหัสผ่านใหม่ (เว้นว่างไว้ = คงรหัสเดิม)</label>
            <input type="password" id="password" name="password" minlength="8">

            <label for="role">บทบาท *</label>
            <select id="role" name="role">
                <option value="user" <?php echo $oldValues['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo $oldValues['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>

            <label for="contact_info">ช่องทางติดต่อ (ไม่บังคับ เช่น เบอร์โทร)</label>
            <input type="text" id="contact_info" name="contact_info" maxlength="150"
                   value="<?php echo htmlspecialchars($oldValues['contact_info'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-actions">
                <button type="submit" class="btn">บันทึกการแก้ไข</button>
                <a class="btn btn-secondary" href="users.php">ยกเลิก</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>