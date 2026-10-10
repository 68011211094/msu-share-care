<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'หน้าแรก';

$currentUser = current_user();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$allowedTypes = ['donate', 'exchange'];
$allowedStatuses = ['available', 'completed'];

if (!in_array($type, $allowedTypes, true)) {
    $type = '';
}
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

$sql = 'SELECT items.id, items.title, items.type, items.status, items.created_at, items.image,'
    . ' users.full_name AS owner_name'
    . ' FROM items'
    . ' INNER JOIN users ON users.id = items.owner_id'
    . ' WHERE 1 = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (items.title LIKE ? OR items.description LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}

if ($type !== '') {
    $sql .= ' AND items.type = ?';
    $params[] = $type;
}

if ($status !== '') {
    $sql .= ' AND items.status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY items.created_at DESC, items.id DESC';

$statement = db_connect()->prepare($sql);
$statement->execute($params);
$items = $statement->fetchAll();

$hasFilter = $q !== '' || $type !== '' || $status !== '';

require __DIR__ . '/includes/header.php';
?>
    <section class="hero">
        <div>
            <span class="hero-tagline">&#10022; ชุมชนนักศึกษา มหาวิทยาลัยมหาสารคาม</span>
            <h1>ของที่คุณไม่ใช้ อาจเป็นโอกาสของใครอีกคน</h1>
            <p>ส่งต่อสิ่งของที่ยังมีคุณค่าให้เพื่อนนักศึกษา ผ่านการบริจาคและแลกเปลี่ยนภายในชุมชน MSU</p>
            <div class="hero-cta">
                <a class="btn btn-lg" href="#items">ดูสิ่งของที่แบ่งปัน</a>
                <?php if ($currentUser !== null) { ?>
                    <a class="btn btn-secondary btn-lg" href="item_create.php">ลงประกาศสิ่งของ</a>
                <?php } else { ?>
                    <a class="btn btn-outline btn-lg" href="register.php">สมัครสมาชิกเพื่อลงประกาศ</a>
                <?php } ?>
            </div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <svg viewBox="0 0 420 340" xmlns="http://www.w3.org/2000/svg" role="img">
                <ellipse cx="210" cy="290" rx="170" ry="24" fill="#D7E5E7" opacity="0.6"/>
                <circle cx="90" cy="80" r="42" fill="#F4C430" opacity="0.25"/>
                <circle cx="350" cy="110" r="30" fill="#4CAF97" opacity="0.22"/>
                <rect x="60" y="150" width="96" height="72" rx="12" fill="#FFFFFF" stroke="#D7E5E7" stroke-width="2"/>
                <rect x="76" y="166" width="64" height="6" rx="3" fill="#BFD4D8"/>
                <rect x="76" y="182" width="44" height="6" rx="3" fill="#E8F2F1"/>
                <rect x="264" y="120" width="96" height="72" rx="12" fill="#FFFFFF" stroke="#D7E5E7" stroke-width="2"/>
                <path d="M276 150l16 14 10-10 16 14" fill="none" stroke="#4CAF97" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M210 210v90" stroke="#BFD4D8" stroke-width="3" stroke-dasharray="6 6"/>
                <path d="M150 252c6-8 18-8 24 0l36 46-48 60-48-60z" fill="#E7F5F0" stroke="#BFD4D8" stroke-width="2" stroke-linejoin="round"/>
                <path d="M246 252c6-8 18-8 24 0l0 0" fill="none" stroke="#BFD4D8" stroke-width="2"/>
                <path d="M210 300c-14-16-38-30-38-44 0-9 7-16 16-16 5.5 0 10.4 2.7 13 6.8 2.6-4.1 7.5-6.8 13-6.8 9 0 16 7 16 16 0 14-24 28-38 44z" fill="#F4C430"/>
            </svg>
        </div>
    </section>

    <section class="benefits" aria-label="แนวคิดของ MSU Share และ Care">
        <div class="benefit-card">
            <span class="benefit-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <h3>แบ่งปันสิ่งของ</h3>
                <p>ให้ของที่ยังใช้ได้มีชีวิตที่สอง ผ่านการบริจาคและแลกเปลี่ยน</p>
            </div>
        </div>
        <div class="benefit-card">
            <span class="benefit-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <h3>ลดของเหลือใช้</h3>
                <p>ลดขยะและของเหลือทิ้งในรั้วมหาวิทยาลัย อย่างยั่งยืน</p>
            </div>
        </div>
        <div class="benefit-card">
            <span class="benefit-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <h3>สร้างชุมชน MSU</h3>
                <p>ช่วยเหลือเพื่อนนักศึกษาและสร้างสังคมที่น่าอยู่ร่วมกัน</p>
            </div>
        </div>
    </section>

    <section class="search-panel" aria-label="ค้นหาและกรองประกาศ">
        <form class="search-form" method="get" action="index.php" role="search">
            <label for="search-q" class="skip-link">ค้นหาสิ่งของ</label>
            <input type="search" id="search-q" name="q" placeholder="ค้นหาสิ่งของที่ต้องการแบ่งปัน..." autocomplete="off"
                   value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">

            <label for="search-type" class="skip-link">ประเภท</label>
            <select id="search-type" name="type">
                <option value="">ทุกประเภท</option>
                <option value="donate" <?php echo $type === 'donate' ? 'selected' : ''; ?>>Donate (บริจาค)</option>
                <option value="exchange" <?php echo $type === 'exchange' ? 'selected' : ''; ?>>Exchange (แลกเปลี่ยน)</option>
            </select>

            <label for="search-status" class="skip-link">สถานะ</label>
            <select id="search-status" name="status">
                <option value="">ทุกสถานะ</option>
                <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit" class="btn">ค้นหา</button>
        </form>
    </section>

    <div id="items" class="section-head">
        <h2>สิ่งของล่าสุด</h2>
        <span class="section-count">พบ <?php echo count($items); ?> รายการ</span>
    </div>

    <?php if (empty($items)) { ?>
        <div class="empty-state">
            <span class="empty-icon" aria-hidden="true">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </span>
            <p><strong><?php echo $hasFilter ? 'ไม่พบประกาศที่ตรงกับที่ค้นหา' : 'ยังไม่มีประกาศ'; ?></strong></p>
            <p><?php echo $hasFilter ? 'ลองเปลี่ยนคำค้นหาหรือตัวกรองใหม่อีกครั้ง' : 'รายการประกาศจะแสดงที่นี่'; ?></p>
            <?php if ($hasFilter) { ?>
                <a class="btn btn-secondary" href="index.php">ล้างตัวกรอง</a>
            <?php } elseif ($currentUser !== null) { ?>
                <a class="btn" href="item_create.php">สร้างประกาศแรกของคุณ</a>
            <?php } else { ?>
                <a class="btn btn-secondary" href="login.php">เข้าสู่ระบบเพื่อลงประกาศ</a>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="item-list">
            <?php foreach ($items as $item) { ?>
                <div class="item-card">
                    <a class="item-thumb" href="item_detail.php?id=<?php echo (int) $item['id']; ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($item['image'])) { ?>
                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <?php } else { ?>
                            <span class="item-thumb-placeholder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </span>
                        <?php } ?>
                    </a>
                    <div class="item-card-body">
                        <div class="item-badges">
                            <span class="badge <?php echo $item['type'] === 'donate' ? 'badge-donate' : 'badge-exchange'; ?>">
                                <?php echo $item['type'] === 'donate' ? 'Donate' : 'Exchange'; ?>
                            </span>
                            <span class="badge <?php echo $item['status'] === 'available' ? 'badge-available' : 'badge-completed'; ?>">
                                <?php echo $item['status'] === 'available' ? 'Available' : 'Completed'; ?>
                            </span>
                        </div>
                        <h2><a href="item_detail.php?id=<?php echo (int) $item['id']; ?>">
                            <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a></h2>
                        <p class="item-meta">
                            โดย <?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?>
                            &middot; <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
<?php require __DIR__ . '/includes/footer.php'; ?>