<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

$currentUser = current_user();

$currentScript = basename($_SERVER['SCRIPT_NAME']);
$isAdminArea = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
?>
<a class="skip-link" href="#main-content">ข้ามไปยังเนื้อหา</a>
<header class="site-header">
    <div class="container nav-inner">
        <a class="brand" href="<?php echo app_url('index.php'); ?>">
            <img class="brand-logo" src="<?php echo app_url('assets/favicon.svg'); ?>" alt="โลโก้ MSU Share &amp; Care">
            <span class="brand-name">
                <span>MSU Share &amp; Care</span>
                <em>แชร์สิ่งดี ๆ&middot;สร้างสังคม MSU</em>
            </span>
        </a>
        <button class="nav-toggle" type="button" aria-label="เปิดเมนู" aria-expanded="false">&#9776;</button>
        <nav class="site-nav" aria-label="เมนูหลัก">
            <a href="<?php echo app_url('index.php'); ?>"
               <?php if ($currentScript === 'index.php') { echo 'class="active" aria-current="page"'; } ?>>หน้าแรก</a>
            <?php if ($currentUser !== null) { ?>
                <a href="<?php echo app_url('my_items.php'); ?>"
                   <?php if ($currentScript === 'my_items.php') { echo 'class="active" aria-current="page"'; } ?>>ประกาศของฉัน</a>
                <?php if ($currentUser['role'] === 'admin') { ?>
                    <a href="<?php echo app_url('admin/dashboard.php'); ?>"
                       <?php if ($isAdminArea) { echo 'class="active" aria-current="page"'; } ?>>Admin</a>
                <?php } ?>
            <?php } ?>

            <div class="nav-actions">
                <?php if ($currentUser !== null) { ?>
                    <span class="nav-user">สวัสดี, <strong><?php echo htmlspecialchars($currentUser['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <a class="btn" href="<?php echo app_url('item_create.php'); ?>">+ ลงประกาศสิ่งของ</a>
                    <form class="nav-logout" method="post" action="<?php echo app_url('logout.php'); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="nav-logout-btn">ออกจากระบบ</button>
                    </form>
                <?php } else { ?>
                    <a class="btn btn-outline" href="<?php echo app_url('login.php'); ?>">เข้าสู่ระบบ</a>
                    <a class="btn" href="<?php echo app_url('register.php'); ?>">สมัครสมาชิก</a>
                <?php } ?>
            </div>
        </nav>
    </div>
</header>