<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

$currentUser = current_user();
?>
<header class="site-header">
    <div class="container nav-inner">
        <a class="brand" href="<?php echo app_url('index.php'); ?>">MSU Share &amp; Care</a>
        <button class="nav-toggle" type="button" aria-label="เปิดเมนู" aria-expanded="false">&#9776;</button>
        <nav class="site-nav">
            <a href="<?php echo app_url('index.php'); ?>">หน้าแรก</a>
            <?php if ($currentUser !== null) { ?>
                <a href="<?php echo app_url('my_items.php'); ?>">ประกาศของฉัน</a>
                <?php if ($currentUser['role'] === 'admin') { ?>
                    <a href="<?php echo app_url('admin/dashboard.php'); ?>">Admin</a>
                <?php } ?>
                <span class="nav-user">สวัสดี, <?php echo htmlspecialchars($currentUser['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <form class="nav-logout" method="post" action="<?php echo app_url('logout.php'); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="nav-logout-btn">ออกจากระบบ</button>
                </form>
            <?php } else { ?>
                <a href="<?php echo app_url('register.php'); ?>">สมัครสมาชิก</a>
                <a href="<?php echo app_url('login.php'); ?>">เข้าสู่ระบบ</a>
            <?php } ?>
        </nav>
    </div>
</header>
