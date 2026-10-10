<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/validation.php';

$pageTitle = 'เข้าสู่ระบบ';

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/csrf.php';
    require_once __DIR__ . '/includes/db_connect.php';

    $oldEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!csrf_verify()) {
        $errors[] = 'เซสชันหมดอายุ กรุณาลองใหม่อีกครั้ง';
    }

    $error = validate_required($oldEmail, 'อีเมล');
    if ($error !== '') {
        $errors[] = $error;
    }

    $error = validate_required($password, 'รหัสผ่าน');
    if ($error !== '') {
        $errors[] = $error;
    }

    if (empty($errors)) {
        $statement = db_connect()->prepare(
            'SELECT id, password_hash FROM users WHERE email = ?'
        );
        $statement->execute([$oldEmail]);
        $user = $statement->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];

            header('Location: index.php');
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
    <div class="auth-wrap">
        <div class="auth-brand">
            <img src="<?php echo app_url('assets/favicon.svg'); ?>" alt="โลโก้ MSU Share &amp; Care">
            <h1>ยินดีต้อนรับกลับเข้าสู่ MSU Share &amp; Care</h1>
            <p>ส่งต่อสิ่งของที่ยังมีคุณค่าให้เพื่อนนักศึกษา และสร้างชุมชน MSU ที่น่าอยู่ด้วยกัน</p>
            <p class="auth-quote">&ldquo;แชร์สิ่งดี ๆ สร้างสังคม MSU ให้น่าอยู่&rdquo;</p>
        </div>
        <div class="auth-form">
            <div class="form-box">
                <?php if (!empty($errors)) { ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error) { ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>

                <form method="post" action="login.php">
                    <?php echo csrf_field(); ?>

                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" maxlength="150" required
                           value="<?php echo htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" required>

                    <div class="form-actions">
                        <button type="submit" class="btn">เข้าสู่ระบบ</button>
                    </div>

                    <p class="form-hint">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
                </form>
            </div>
        </div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>