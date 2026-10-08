<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db_connect.php';

$pageTitle = 'หน้าแรก';
db_connect();

require __DIR__ . '/includes/header.php';
?>
    <h1>MSU Share &amp; Care</h1>
    <p>กระดานแจ้งแบ่งปันของสำหรับนักศึกษามหาวิทยาลัยมหาสารคาม</p>

    <div class="empty-state">
        <p><strong>ยังไม่มีประกาศ</strong></p>
        <p>รายการประกาศจะแสดงที่นี่</p>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
