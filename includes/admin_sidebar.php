<?php
if (!function_exists('app_url')) {
    require_once __DIR__ . '/auth.php';
}

$adminScript = basename($_SERVER['SCRIPT_NAME']);
$activeKey = $adminScript === 'user_edit.php' ? 'users' : $adminScript;
$adminLinks = [
    ['dashboard.php', 'dashboard', 'ภาพรวมระบบ', '&#9678;'],
    ['users.php', 'users', 'จัดการผู้ใช้', '&#9787;'],
    ['items.php', 'items', 'จัดการประกาศ', '&#9776;'],
    ['user_create.php', 'user_create', 'เพิ่มผู้ใช้', '&#10133;'],
];
?>
<aside class="admin-sidebar" aria-label="เมนู Admin">
    <ul>
        <?php foreach ($adminLinks as $link) { ?>
            <li>
                <a href="<?php echo app_url('admin/' . $link[0]); ?>"
                   <?php if ($link[1] === $activeKey) { echo 'class="active" aria-current="page"'; } ?>>
                    <span class="side-icon"><?php echo $link[3]; ?></span>
                    <?php echo $link[2]; ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</aside>