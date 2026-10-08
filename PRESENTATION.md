# MSU Share & Care — Presentation Guide (Phase 9)

เอกสารเตรียมตัวสำหรับนำเสนอ: Demo flow, Architecture, Security, และ Q&A

## 1. เตรียมก่อนสาธิต

- XAMPP (Apache + MySQL) ทำงานอยู่ และเปิดแอปตามขั้นตอนใน `README.md`
- **สาธิตบน Apache/XAMPP เท่านั้น** (ไม่ใช่ `php -S`) เพราะไฟล์ `.htaccess`
  ที่ใช้บล็อก `/.env` จะมีผลเฉพาะ Apache
- รีเซ็ตข้อมูลตัวอย่าง: วาง SQL ชุดนี้ใน phpMyAdmin แล้ว Execute
  (รันผ่าน phpMyAdmin เท่านั้น — ถ้า pipe SQL ไทยผ่าน PowerShell ตัวอักษรจะพัง)

```sql
DELETE FROM items;
DELETE FROM users;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE items AUTO_INCREMENT = 1;

INSERT INTO users (full_name, email, password_hash, role, contact_info) VALUES
('นาย สมชาย ใจดี', 'studenta@example.com', '$2y$10$c/RvfoS7ue7SsM3ihpE14.Go9gaeSv.c1rRA8nlN4n2t1tddrB.jm', 'admin', '081-111-2222'),
('นางสาว สมหญิง รักเรียน', 'studentb@example.com', '$2y$10$c/RvfoS7ue7SsM3ihpE14.Go9gaeSv.c1rRA8nlN4n2t1tddrB.jm', 'user', '082-333-4444');

INSERT INTO items (owner_id, title, description, type, status, contact) VALUES
(1, 'กระเป๋าเป้นักเรียน สภาพดี', 'กระเป๋าเป้ ใช้งานมาประมาณ 1 ปี ซีดเล็กน้อย ซิปทำงานปกติ', 'donate', 'available', '081-111-2222'),
(1, 'หนังสือคณิตศาสตร์ ปี 1', 'หนังสือเรียนสภาพดี ไม่มีลายเขียน', 'exchange', 'available', '081-111-2222'),
(2, 'โต๊ะพับไม้ยางนา', 'โต๊ะพับสภาพใช้ได้ ขาไม่โยก', 'donate', 'available', '082-333-4444'),
(2, 'เครื่องคิดเลข Casio fx-991ES', 'ใช้ได้ปกติ มีคู่มือ', 'exchange', 'available', '082-333-4444');
```

บัญชีสาธิต:

| อีเมล | รหัสผ่าน | บทบาท |
|---|---|---|
| studenta@example.com | TestPass123 | Admin |
| studentb@example.com | TestPass123 | User |

## 2. Demo Flow

เปิด 2 บราวเซอร์: บราวเซอร์หลัก + อีกบราวเซอร์เป็น InPrivate/Incognito
(ใช้แทน "ผู้ใช้อีกคน" ได้)

### บทที่ 1 — ผู้ใช้ทั่วไป

1. **หน้าแรก (guest)** — เห็นประกาศตัวอย่าง 4 รายการ เมนูมีแค่
   สมัครสมาชิก / เข้าสู่ระบบ ยังไม่มีปุ่มแก้ไข/ลบ
   - คาดหวัง: แสดง badge Donate/Exchange และ Available ถูกต้อง
2. **สมัครสมาชิก — สาธิต validation 3 ชั้น** (แยกย่อย ก–ค)
   - **(ก) ฟอร์มเปล่า / อีเมลผิดรูปแบบ → กดปุ่มสมัครสมาชิก**
     - คาดหวัง: บราวเซอร์เตือนทันที **ไม่ต้องรอ server**
       (client-side: HTML5 `required` / `type=email` / `minlength`)
   - **(ข) พิมพ์ `studenta@example.com` แล้วกดออกจากช่องอีเมล**
     - คาดหวัง: ใต้ช่องขึ้น "อีเมลนี้ถูกใช้งานแล้ว" ทันที **โดยไม่กด submit**
       (AJAX: jQuery `$.getJSON` → `check_email.php` → JSON) —
       ลองเปลี่ยนเป็นเมลใหม่ เช่น `new.user@example.com` ขึ้น "อีเมลนี้ใช้ได้"
   - **(ค) กรอกครบแต่รหัสผ่านยืนยันไม่ตรงกัน → กดสมัคร**
     - คาดหวัง: server ปฏิเสธเป็นข้อความไทย ("รหัสผ่านยืนยันไม่ตรงกัน")
       — บราวเซอร์เช็คเงื่อนไขนี้ไม่ได้ จึงพิสูจน์ว่า
       **server-side validation ยังเป็นหลักเสมอ** แม้ปิด JavaScript
3. **กรอกข้อมูลครบ → สมัครสมาชิก** (เช่น `test@example.com`)
   - คาดหวัง: เข้าสู่ระบบอัตโนมัติ เมนูขึ้น "สวัสดี, ..." (แสดงชื่อที่ escape แล้ว)
4. **สร้างประกาศ (Donate)** — ตั้งชื่อ/รายละเอียด/ช่องทางติดต่อ
   - คาดหวัง: ไปหน้ารายละเอียด และเห็นรายการตัวเองบนหน้าแรก
5. **แก้ไขประกาศของตัวเอง** — เปลี่ยนชื่อ แล้วบันทึก
   - คาดหวัง: ชื่อใหม่แสดงทันที แสดง "แก้ไขล่าสุด" ด้วย
6. **เปลี่ยนสถานะเป็น Completed**
   - คาดหวัง: badge เปลี่ยนเป็น Completed ทุกหน้าที่แสดงรายการนี้
7. **เปิดบราวเซอร์ InPrivate → login เป็น studentb →
   เปิดประกาศของ user คนแรก (id=1 หรือ 2)**
   - คาดหวัง: ดูรายละเอียดได้ **แต่ไม่มีปุ่มแก้ไข/ลบ/เปลี่ยนสถานะ**
     (Case B — หน้าสาธารณะดูได้ แต่ UI ไม่สิทธิ์)
8. **ID manipulation:** ยัง login เป็น studentb → พิมพ์ URL
   `item_edit.php?id=1` (ประกาศของคนอื่น) โดยตรง
   - คาดหวัง: **HTTP 403 "ไม่มีสิทธิ์"** — ตรงนี้คือหลักฐานว่า server
     ตรวจ ownership ไม่ใช่แค่ซ่อนปุ่ม (Case C)
9. **Logout → เปิด `my_items.php` อีกครั้ง**
   - คาดหวัง: ถูก redirect ไปหน้า login (เซสชันถูกทำลายจริง)

### บทที่ 2 — Admin

10. **login เป็น studenta**
    - คาดหวัง: เมนูเพิ่ม "Admin"
11. **Dashboard** — สถิติ 6 ช่อง (ผู้ใช้, ประกาศ, Available, Completed,
    Donate, Exchange)
12. **รายการผู้ใช้** — เห็น 2 บัญชี มี badge Admin/User
13. **รายการประกาศ → ลบ item ใดก็ได้**
    - คาดหวัง: รายการหายทันที (คำขอมี CSRF token + บันทึกผ่าน POST เท่านั้น)
14. **(เสริม)** ยัง login เป็น admin → พิมพ์ `item_edit.php?id=2`
    (ประกาศที่ admin ไม่ได้เป็นเจ้าของ)
    - คาดหวัง: **403** — ตามขอบเขตที่กำหนด: admin ดู+ลบได้เท่านั้น
      ไม่แก้ไขเนื้อหาของผู้ใช้

### บทที่ 3 — Security

15. **XSS:** สร้างประกาศชื่อ `<script>alert('xss')</script>`
    (รายละเอียดใส่ `<b>bold</b>` ก็ได้)
    - คาดหวัง: แสดงเป็นข้อความธรรมดา **ไม่มี popup** ทั้งหน้าแรก
      และหน้ารายละเอียด (ข้อมูลเก็บจริง แต่ escape ก่อนแสดง)
16. **SQL Injection:** ลองเข้าสู่ระบบด้วย
    email = `' OR '1'='1` รหัสผ่านอะไรก็ได้
    - คาดหวัง: "อีเมลหรือรหัสผ่านไม่ถูกต้อง" ไม่มีการ bypass
      (prepared statements อ่านค่านี้เป็นข้อความธรรมดา)
17. **ไฟล์ configuration:** เปิด URL
    `http://localhost/msu-share-care/.env`
    - คาดหวัง: **403 Forbidden** (.htaccess บล็อก) — ลองต่อด้วย
      `/sql/schema.sql`, `/config/config.php`, `/includes/auth.php` ก็ 403
18. **จบ demo:** logout กลับเป็น guest

## 3. Architecture Explanation

```text
Browser → Apache (อ่าน .htaccess กรองไฟล์ไว้ก่อนเข้า PHP)
       → หน้าเว็บ PHP แต่ละไฟล์ ทำงานตามลำดับ:
         config/config.php      (โหลด .env + เปิด session + ตั้งค่า security)
         includes/auth|csrf|validation
         ตรรกะของหน้า           (รับ input → validate → prepared statement)
         includes/header|nav    (แสดงผลด้วย htmlspecialchars)
         includes/footer
       → MySQL (msu_share_care)
```

โครงสร้างไฟล์:

```text
index.php, register.php, login.php, logout.php   → หน้าหลัก / auth
item_*.php, my_items.php                         → CRUD ประกาศ (มี ownership check)
admin/                                           → หน้าแอดมิน (require_admin + basePath='../')
includes/  auth, csrf, validation, db_connect, image_upload,
           header, nav, footer                   → ส่วนที่ใช้ร่วมกัน
config/    config.php (env+session), db.php      → ค่าตั้งค่า + โหลด .env
sql/schema.sql                                   → โครงสร้างฐานข้อมูล
uploads/   โฟลเดอร์เก็บภาพที่อัปโหลด (.gitkeep +
           .htaccess กัน PHP รันในนี้)
.htaccess, .env.example, .gitignore              → deploy + security
assets/css, assets/js                            → สไตล์ + jQuery + main.js
                                                  (nav toggle, confirm ลบ, AJAX เช็ค email)
```

ฐานข้อมูล 2 ตาราง:

```text
users (id, full_name, email UNIQUE, password_hash, role ENUM(user,admin),
       contact_info, created_at)
1 ─────── N items (id, owner_id FK→users ON DELETE CASCADE, title,
                     description, type ENUM(donate,exchange),
                     status ENUM(available,completed), contact,
                     image VARCHAR(255) NULL,
                     created_at, updated_at)
```

จุดออกแบบที่ควรอธิบาย:

- **ทำไมเขียน PHP ตรง ๆ ไม่ใช้ framework** — ตามโจทย์รายวิชา
  ให้อ่านออก ดีบักง่าย ระดับนักศึกษาปี 2 ไม่ซ่อน logic ไว้ใน framework
- **Relative URL ทั้งหมด + `app_url()`** — deploy ได้ทั้ง document root
  และ subdirectory (หน้า admin ตั้ง `$basePath = '../'`)
- **Form ตาม requirement:** ฟอร์มสมัครสมาชิก + ฟอร์มสร้าง/แก้ไขประกาศ
  ทั้งคู่ validate ฝั่ง server และบันทึกลง database จริง
- **CSRF + session อยู่ฝั่ง server** — ไม่มี token คงที่ในโค้ด

## 4. Security Explanation (ภัยคุกคาม → กลไก → โค้ด)

| ภัย | กลไก | ตัวอย่างโค้ด |
|---|---|---|
| SQL Injection | Prepared statements ทุกจุด, `EMULATE_PREPARES=false` | `login.php:33`, `item_edit.php:92-104` |
| XSS | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` ทุกจุดแสดงผล | `index.php:45`, `nav.php:19`, `header.php:16` |
| CSRF | token ต่อ session + `hash_equals` ทุก POST | `csrf.php:18-28` |
| Session fixation | `session_regenerate_id(true)` ตอน login/สมัครสำเร็จ | `login.php:42`, `register.php:98` |
| Session hijack | `httponly` + `strict_mode` + `only_cookies` + `SameSite=Lax` | `config/config.php:9-12` |
| Password รั่ว | `password_hash()` / `password_verify()` ไม่มี plaintext | `register.php:85`, `login.php:39` |
| เข้าถึงข้อมูลคนอื่น | `require_admin()` (403) + `require_owned_item()` (403/404) + `WHERE owner_id` ซ้ำใน SQL | `auth.php:56-65`, `auth.php:81-98`, `item_edit.php:94` |
| ข้อมูล config รั่ว | `.env` อยู่ใน `.gitignore`; `.htaccess` บล็อก `/sql`, `/config`, `/includes`, dotfiles, `.sql`, `.md` + ปิด directory listing | `.htaccess`, `.gitignore:2-4` |
| Error รั่วตอน production | `APP_DEBUG=false` → ซ่อน stack trace (ทดสอบแล้วได้ 500 เปล่า) | `config/config.php:6-7` |
| รั่วว่า email มีในระบบ | ข้อความ error ล็อกอินอันเดียวสำหรับทุกกรณี | `login.php:39-40` |
| สมัครเป็น admin เอง | INSERT ไม่รับ field `role` จาก client (ค่า default = user) | `register.php:88-96` |

## 5. Q&A ที่คาดว่าถูกถาม

**Q: ทำไมใช้ Session ไม่ใช้ JWT?**
เว็บแอปเดียวกัน origin เดียว session ฝั่ง server ควบคุมได้ครบ
(logout = ทำลายทันที, regenerate id ได้) ไม่ต้องเก็บ token ไว้ใน client

**Q: jQuery / AJAX ใช้ตรงไหน?**
jQuery โหลดใน `includes/footer.php` ใช้เขียน `assets/js/main.js`
(nav toggle, confirm ก่อนลบ, และ AJAX) — AJAX ใช้ตอนสมัครสมาชิก:
ออกจากช่องอีเมล → `$.getJSON('check_email.php', {email: ...})`
ตรวจอีเมลซ้ำแบบไม่ reload หน้า คืน JSON `{valid, available}` —
**server-side ยังตรวจซ้ำตอน submit เสมอ** ถ้าปิด JavaScript
การสมัครยังถูก validate ครบและ reject email ซ้ำเหมือนเดิม

**Q: CSRF token เก็บตรงไหน?**
`$_SESSION['csrf_token']` ฝั่ง server ฝั่ง client ได้ค่าจากฟอร์มกลับมาตอน POST
แล้วเทียบด้วย `hash_equals` — token จึงเดาไม่ได้และใช้ซ้ำข้ามคนไม่ได้

**Q: มั่นใจยังไงว่าแก้ไขของคนอื่นไม่ได้?**
สองชั้น: (1) `require_owned_item()` ตรวจ `owner_id == current_user.id`
ก่อนเสมอ (2) SQL UPDATE/DELETE มี `WHERE id = ? AND owner_id = ?`
ซ้ำอีกชั้น — เคส A/B/C/D ผ่านในชุดทดสอบอัตโนมัติ 224 กรณี

**Q: ทำไม admin ถึงมีสิทธิ์แค่ดูและลบ?**
ตามขอบเขต requirement: จัดการประกาศที่ผิดกฎได้ แต่ไม่แทรกแซง
เนื้อหาของผู้ใช้ (ไม่แก้ไขข้อความแทนเจ้าของ) — ทดสอบแล้วว่า admin
เข้า `item_edit.php` ของคนอื่นได้ 403

**Q: ทำไมไม่ใช้ MD5/SHA กับรหัสผ่าน?**
`password_hash()` (bcrypt, `$2y$`) ออกแบบมาสำหรับรหัสผ่าน:
มี salt ต่อ record, คำนวณช้าโดยเจตนาเพื่อกัน brute force,
`password_verify()` รองรับ upgrade  알고ริทึมในอนาคต

**Q: ทดสอบอะไรมาแล้วบ้าง?**
ชุดทดสอบอัตโนมัติ 224 กรณี: ownership (Case A/B/C/D), CSRF ทุก endpoint
(รวม token ปลอม), XSS/SQLi payload, validation ข้อมูลผิดทุกฟอร์ม,
guest เข้าหน้าคุ้มครอง, logout/session, AJAX email check, image upload
(สร้าง/เปลี่ยน/ลบรูป + ไฟล์ไม่ใช่ภาพถูก reject + ลบไฟล์เมื่อลบประกาศ),
ทุกอย่าง regression ซ้ำหลังแก้โค้ด + deploy test 13 กรณี (fresh database,
production config) — ทุกเคสรันจริงบน PHP 8.2/Apache ไม่ใช่การคาดเดา

**Q: upload รูปปลอดภัยยังไง?**
(1) ตรวจขนาด ≤2 MB และชนิดจริงด้วย `getimagesize()` ไม่เชื่อนามสกุล/`Content-Type`
(2) เปลี่ยนชื่อไฟล์เป็น `bin2hex(random_bytes(16))` เก็บแค่ path ใน DB
(3) โฟลเดอร์ `uploads/` มี `.htaccess` ปิด directory listing + block PHP รัน
(4) ลบไฟล์เก่าทุกครั้งที่เปลี่ยนรูป/ลบรูป/ลบประกาศ (`includes/image_upload.php`)

**Q: ข้อจำกัดของระบบนี้?**
- ไม่มี rate limiting / lockout (ขอบเขตเล็ก)
- สาธิตด้วย HTTP บน XAMPP — ยังไม่มี HTTPS จริง
- ไม่มี email verification, ไม่มี category
  (nice-to-have ตามลำดับ ยังไม่ถึง)
- ไม่มีรายงานสแปม/ร้องเรียนประกาศ — แอดมินดูรายการและลบได้แทน

**Q: ข้อมูลสำคัญอยู่ตรงไหนใน Git?**
ไม่มี — `.env` (รหัสฐานข้อมูลจริง) ถูก gitignore มีแต่ `.env.example`
เป็น template, ไม่มี secret ในโค้ด, รหัสผ่านผู้ใช้เป็น bcrypt hash

**Q: ทำไม schema.sql ไม่มี CREATE DATABASE?**
เพื่อให้ import ไปยังฐานข้อมูลชื่อใดก็ได้ตามที่เลือกตอนรันคำสั่ง
(การ hardcode `USE` ทำให้ import ผิดฐานข้อมูลโดยไม่รู้ตัว) —
ขั้นตอนสร้างฐานข้อมูลอยู่ใน README ขั้นตอนที่ 3
