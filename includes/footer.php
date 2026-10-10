<?php
require_once __DIR__ . '/auth.php';

$currentUser = current_user();
?>
</main>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="footer-brand" href="<?php echo app_url('index.php'); ?>">
                    <img src="<?php echo app_url('assets/favicon.svg'); ?>" alt="โลโก้ MSU Share &amp; Care">
                    MSU Share &amp; Care
                </a>
                <p>กระดานแจ้งแบ่งปันของสำหรับนักศึกษามหาวิทยาลัยมหาสารคาม<br>ส่งต่อสิ่งของที่ยังมีคุณค่า ผ่านการบริจาคและแลกเปลี่ยน</p>
            </div>
            <div class="footer-column">
                <h3>เมนู</h3>
                <ul>
                    <li><a href="<?php echo app_url('index.php'); ?>">หน้าแรก</a></li>
                    <?php if ($currentUser !== null) { ?>
                        <li><a href="<?php echo app_url('my_items.php'); ?>">ประกาศของฉัน</a></li>
                        <li><a href="<?php echo app_url('item_create.php'); ?>">ลงประกาศสิ่งของ</a></li>
                        <?php if ($currentUser['role'] === 'admin') { ?>
                            <li><a href="<?php echo app_url('admin/dashboard.php'); ?>">หน้าระบบ (Admin)</a></li>
                        <?php } ?>
                    <?php } else { ?>
                        <li><a href="<?php echo app_url('login.php'); ?>">เข้าสู่ระบบ</a></li>
                        <li><a href="<?php echo app_url('register.php'); ?>">สมัครสมาชิก</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="footer-column">
                <h3>แนวคิด</h3>
                <p>แบ่งปันสิ่งดี ๆ<br>ลดของเหลือใช้<br>สร้างชุมชน MSU ที่น่าอยู่ยิ่งขึ้น</p>
            </div>
        </div>
        <div class="footer-message">
            &ldquo;Share more. Waste less. Care together.&rdquo; &mdash; แบ่งปันสิ่งดี ๆ เพื่อชุมชน MSU ที่น่าอยู่ยิ่งขึ้น
        </div>
        <div class="footer-bottom">
            <p>MSU Share &amp; Care &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</footer>
<script src="<?php echo app_url('assets/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo app_url('assets/js/main.js'); ?>"></script>
</body>
</html>