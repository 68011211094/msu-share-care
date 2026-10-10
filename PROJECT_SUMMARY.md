# บทสรุปโปรเจกต์ MSU Share & Care

> เอกสารนี้สรุปว่าโปรเจกต์นี้คืออะไร มีอะไรบ้าง และเราทำอะไรไปแล้วตั้งแต่ต้นจนถึงปัจจุบัน
> (อัปเดตล่าสุด: หลังเสร็จฟีเจอร์จัดการผู้ใช้/ประกาศของแอดมิน + favicon และ Deploy ขึ้น InfinityFree)

---

## สารบัญ

1. [โปรเจกต์นี้คืออะไร](#1-โปรเจกต์นี้คืออะไร)
2. [เป้าหมายและขอบเขต](#2-เป้าหมายและขอบเขต)
3. [เทคโนโลยีที่ใช้](#3-เทคโนโลยีที่ใช้)
4. [โครงสร้างไฟล์ทั้งโปรเจกต์](#4-โครงสร้างไฟล์ทั้งโปรเจกต์)
5. [ฐานข้อมูล](#5-ฐานข้อมูล)
6. [ฟีเจอร์ทั้งหมดที่มีตอนนี้](#6-ฟีเจอร์ทั้งหมดที่มีตอนนี้)
7. [ความปลอดภัยที่ทำไว้](#7-ความปลอดภัยที่ทำไว้)
8. [บัญชีทดลองใช้งาน](#8-บัญชีทดลองใช้งาน)
9. [วิธีรันบนเครื่อง (Local / XAMPP)](#9-วิธีรันบนเครื่อง-local--xampp)
10. [การ Deploy จริงบน InfinityFree](#10-การ-deploy-จริงบน-infinityfree)
11. [ปัญหาระหว่าง Deploy และวิธีแก้](#11-ปัญหาระหว่าง-deploy-และวิธีแก้)
12. [ประวัติการทำงาน (Phase + Commit)](#12-ประวัติการทำงาน-phase--commit)
13. [ประวัติงานช่วงท้าย (Admin Features + Deploy)](#13-ประวัติงานช่วงท้าย-admin-features--deploy)
14. [สถานะปัจจุบัน](#14-สถานะปัจจุบัน)
15. [สิ่งที่ยังไม่ได้ทำ / ยังค้างอยู่](#15-สิ่งที่ยังไม่ได้ทำ--ยังค้างอยู่)
16. [ไฟล์สำหรับนำไปส่ง / นำเสนอ](#16-ไฟล์สำหรับนำไปส่ง--นำเสนอ)

---

## 1. โปรเจกต์นี้คืออะไร

**MSU Share & Care** คือเว็บแอปพลิเคชันแบบ **กระดานประกาศชุมชน (Community Bulletin Board)**
สำหรับนักศึกษามหาวิทยาลัยมหาสารคาม (MSU) ให้นักศึกษาโพสต์ของที่ไม่ได้ใช้แล้ว
เพื่อ **บริจาค (Donate)** หรือ **แลกเปลี่ยน (Exchange)** ให้เพื่อนนักศึกษา

**ไม่ใช่ร้านค้าออนไลน์** — ไม่มีการชำระเงิน ค่าจัดส่ง หรือดีลจบในระบบ
ผู้สนใจดูประกาศแล้วติดต่อเจ้าของผ่านช่องทางที่เจ้าของเขียนไว้เอง

**หลักการทำงาน:**

1. เจ้าของสมัครสมาชิก / เข้าสู่ระบบ
2. เจ้าของสร้างประกาศ (เลือก Donate หรือ Exchange)
3. ผู้สนใจเข้าดูรายละเอียด
4. ผู้สนใจติดต่อเจ้าของตามช่องทางที่ระบุ
5. เมื่อตกลงกันได้ เจ้าของกดเปลี่ยนสถานะเป็น Completed

---

## 2. เป้าหมายและขอบเขต

เป้าหมาย: ระบบเล็ก แต่ **ถูกต้อง ปลอดภัย อ่านง่าย อธิบายอาจารย์ได้ และ Deploy ได้จริง**

**สิ่งที่ทำ (ตาม requirement รายวิชา):**

- Register / Login / Logout
- User / Admin roles
- Session / Authentication + Password hashing
- Item CRUD + Owner-based authorization
- Donate / Exchange + Available / Completed
- MySQL database + อย่างน้อย 2 ฟอร์มที่บันทึกข้อมูล
- Admin dashboard + จัดการผู้ใช้/ประกาศ
- Validation, SQL Injection protection, XSS protection, CSRF protection

**สิ่งที่ "ไม่ทำ" (กันขอบเขตบานปลาย):** Chat, Notifications, Payments, Delivery,
Auction, Matching/Recommendation, Social feed, Likes/Follows, Comments,
Google Login/OAuth, Mobile App — ทั้งหมดนี้ไม่อยู่ในโปรเจกต์

---

## 3. เทคโนโลยีที่ใช้

| ส่วน | เทคโนโลยี |
|---|---|
| โครงหน้าเว็บ | HTML5 |
| ตกแต่ง | CSS3 (responsive รองรับมือถือ/แท็บเล็ต/พีซี) |
| ฝั่งไคลเอนต์ | JavaScript + **jQuery 3.7.1** (เมนูมือถือ, confirm ก่อนลบ, พรีวิวรูป) |
| AJAX | ตรวจอีเมลซ้ำตอนสมัคร (`check_email.php` คืน JSON) |
| ฝั่งเซิร์ฟเวอร์ | **PHP 8.x** เขียนแบบ procedural ไม่ใช้ framework |
| ฐานข้อมูล | **MySQL / MariaDB** ผ่าน PDO (prepared statements) |
| เว็บเซิร์ฟเวอร์ | Apache / XAMPP (ตอนพัฒนา) |
| โฮสต์จริง | **InfinityFree** (ฟรี, PHP 8.4, MySQL, SSL) |
| จัดเวอร์ชัน | Git / GitHub |

---

## 4. โครงสร้างไฟล์ทั้งโปรเจกต์

```text
Term_Project_002/
├── index.php               # หน้าแรก แสดงประกาศทั้งหมด (เรียงใหม่ล่าสุดก่อน)
├── register.php            # สมัครสมาชิก
├── login.php               # เข้าสู่ระบบ
├── logout.php              # ออกจากระบบ (POST + CSRF)
├── my_items.php            # ประกาศของฉัน
├── item_create.php         # สร้างประกาศ
├── item_edit.php           # แก้ไขประกาศ (เจ้าของ หรือ แอดมิน)
├── item_detail.php         # ดูรายละเอียดประกาศ
├── item_delete.php         # ลบประกาศ (POST + CSRF, เจ้าของ หรือ แอดมิน)
├── item_complete.php       # ทำเครื่องหมาย Completed (POST + CSRF)
├── check_email.php         # AJAX ตรวจอีเมลซ้ำ (คืน JSON)
│
├── admin/
│   ├── dashboard.php       # แดชบอร์ดแอดมิน (สถิติ)
│   ├── users.php           # จัดการผู้ใช้ (ดูรายชื่อ + ปุ่มแก้ไข/ลบ + เพิ่มผู้ใช้)
│   ├── user_create.php     # [ใหม่] สร้างผู้ใช้ (แอดมิน)
│   ├── user_edit.php       # [ใหม่] แก้ไขผู้ใช้ (ชื่อ/อีเมล/บทบาท/ติดต่อ/รหัสผ่านใหม่)
│   ├── user_delete.php     # [ใหม่] ลบผู้ใช้ (ลบประกาศ + รูปทั้งหมดของคนนั้นด้วย)
│   └── items.php           # จัดการประกาศทั้งหมด (เพิ่ม/แก้ไข/ลบ)
│
├── includes/               # โค้ดใช้ร่วมกัน
│   ├── db_connect.php      # เชื่อมต่อฐานข้อมูล PDO
│   ├── auth.php            # current_user / require_login / require_admin / require_owned_item
│   ├── csrf.php            # สร้าง + ตรวจ CSRF token
│   ├── validation.php      # ฟังก์ชัน validate
│   ├── image_upload.php    # บันทึก/ลบไฟล์รูป (uploads/)
│   ├── header.php          # เปิด HTML + favicon + CSS
│   ├── nav.php             # แถบเมนูบนสุด
│   └── footer.php          # ปิด HTML + โหลด jQuery
│
├── config/
│   ├── config.php          # ตั้งค่า session
│   └── db.php              # อ่านค่า .env -> get_db_config()  ← แก้บั๊กสำคัญตอน Deploy
│
├── sql/schema.sql          # สคริปต์สร้างตาราง users + items
├── assets/
│   ├── css/style.css       # สไตล์ทั้งหมด
│   ├── favicon.svg         # [ใหม่] โลโก้บนแท็บเบราว์เซอร์
│   └── js/
│       ├── jquery-3.7.1.min.js
│       └── main.js         # เมนูมือถือ / confirm / พรีวิวรูป / AJAX
│
├── uploads/                # เก็บไฟล์รูป (.htaccess ห้ามรัน PHP)
├── tests/                  # ชุดทดสอบอัตโนมัติ (PowerShell)
├── .env.example            # ตัวอย่างค่าตั้ง (คัดลอกเป็น .env)
├── .htaccess               # ป้องกันไฟล์อ่อนไหว + ปิด directory listing
├── .gitignore              # ไม่เอา .env และไฟล์รูปขึ้น Git
├── PROJECT.md              # ข้อกำหนด/ขอบเขตฉบับเต็ม
├── README.md               # คู่มือติดตั้ง + ใช้งาน (ภาษาไทย)
├── DEPLOY.md               # คู่มือ Deploy ฟรี (InfinityFree)
├── PRESENTATION.md         # คู่มือนำเสนอ + Q&A
├── AGENTS.md               # กฎการพัฒนา
└── PROJECT_SUMMARY.md      # ← เอกสารฉบับนี้
```

---

## 5. ฐานข้อมูล

ใช้ฐานข้อมูลชื่อ **`msu_share_care`** (local) / **`if0_xxxx_msu`** (บนโฮสต์จริง) มี 2 ตาราง:

### ตาราง `users`

| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| id | INT AI PK | รหัสผู้ใช้ |
| full_name | VARCHAR(100) | ชื่อ-นามสกุล |
| email | VARCHAR(150) UNIQUE | อีเมล (ใช้ login) |
| password_hash | VARCHAR(255) | รหัสผ่านแบบ hash (bcrypt) |
| role | ENUM('user','admin') | บทบาท (ค่าเริ่มต้น user) |
| contact_info | VARCHAR(150) NULL | ช่องทางติดต่อ |
| created_at | DATETIME | วันสมัคร |

### ตาราง `items`

| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| id | INT AI PK | รหัสประกาศ |
| owner_id | INT FK → users.id | **เจ้าของประกาศ** (ON DELETE CASCADE) |
| title | VARCHAR(120) | ชื่อสิ่งของ |
| description | TEXT | รายละเอียด |
| type | ENUM('donate','exchange') | ประเภท |
| status | ENUM('available','completed') | สถานะ (ค่าเริ่มต้น available) |
| contact | VARCHAR(150) | ช่องทางติดต่อ |
| image | VARCHAR(255) NULL | path รูป เช่น `uploads/xxxx.png` |
| created_at | DATETIME | วันสร้าง |
| updated_at | DATETIME (ON UPDATE) | วันแก้ไขล่าสุด |

**จุดสำคัญ:** `items.owner_id` เป็น FOREIGN KEY → `users.id` แบบ **ON DELETE CASCADE**
แปลว่าลบผู้ใช้แล้วประกาศทั้งหมดของคนนั้นถูกลบตามอัตโนมัติ

---

## 6. ฟีเจอร์ทั้งหมดที่มีตอนนี้

### 6.1 ฝั่งผู้ใช้ทั่วไป (User)

| ฟีเจอร์ | รายละเอียด |
|---|---|
| สมัครสมาชิก | อีเมล + รหัสผ่าน (≥ 8 ตัว) + ตรวจอีเมลซ้ำแบบเรียลไทม์ (AJAX) |
| เข้าสู่ระบบ / ออก | PHP Session + `password_verify()` |
| ดูประกาศ | หน้าแรกแสดงทุกประกาศ เรียงใหม่ล่าสุดก่อน |
| สร้างประกาศ | เลือก Donate/Exchange + รายละเอียด + ช่องทางติดต่อ |
| อัปโหลดรูป | ไม่บังคับ, JPG/PNG/GIF ≤ 20 MB, แสดง 1:1 เท่ากันทุกการ์ด, มีพรีวิวทันที |
| แก้ไขประกาศของตัวเอง | เปลี่ยนข้อมูล/รูป/ลบรูป (ไฟล์เก่าลบอัตโนมัติ) |
| ลบประกาศของตัวเอง | ลบพร้อมไฟล์รูป |
| ทำเครื่องหมาย Completed | เจ้าของจัดการเองเมื่อแบ่งปันสำเร็จ |

### 6.2 ฝั่งแอดมิน (Admin) — **อัปเกรดใหม่ล่าสุด**

| ฟีเจอร์ | รายละเอียด |
|---|---|
| Dashboard | สถิติ: ผู้ใช้ทั้งหมด, ประกาศทั้งหมด, Available, Completed, Donate, Exchange |
| จัดการผู้ใช้ (CRUD) | **ดู / เพิ่ม / แก้ไข / ลบ** ผู้ใช้ได้ (ใหม่) |
| — เพิ่มผู้ใช้ | ตั้งชื่อ/อีเมล/รหัสผ่าน/บทบาท/ช่องทางติดต่อเอง |
| — แก้ไขผู้ใช้ | แก้ชื่อ/อีเมล/บทบาท/ติดต่อ + เปลี่ยนรหัสผ่าน (ถ้าปล่อยว่าง = คงรหัสเดิม) |
| — ลบผู้ใช้ | ลบผู้ใช้ + ประกาศทั้งหมด + ไฟล์รูปของผู้นั้น |
| จัดการประกาศ | **เพิ่ม / แก้ไข / ลบ / ทำ Completed** ประกาศของใครก็ได้ (ใหม่) |
| ปุ่มบนหน้าประกาศ | แอดมินเห็นปุ่ม แก้ไข / Completed / ลบ บนประกาศของคนอื่นด้วย (ใหม่) |

**ข้อจำกัด/การป้องกันความปลอดภัยที่ใส่ไว้:**

- แอดมินลบตัวเองไม่ได้ / ลดสิทธิ์ตัวเองไม่ได้
- ลดสิทธิ์หรือลบแอดมินคนสุดท้ายไม่ได้ (ต้องมีแอดมินเหลืออย่างน้อย 1 คน)
- บทบาทที่ตั้งได้มีแค่ `user` หรือ `admin` เท่านั้น
- ตรวจอีเมลซ้ำฝั่งเซิร์ฟเวอร์
- ทุกการกระทำที่เปลี่ยนข้อมูล = POST + CSRF + `require_admin()` ฝั่งเซิร์ฟเวอร์

---

## 7. ความปลอดภัยที่ทำไว้

| ความเสี่ยง | วิธีป้องกัน |
|---|---|
| SQL Injection | ใช้ PDO **prepared statements** ทุกคำสั่งที่รับข้อมูลจากผู้ใช้ |
| XSS | ใช้ `htmlspecialchars()` หนีอักขระทุกจุดที่แสดงข้อมูล |
| CSRF | ทุกฟอร์ม POST มี CSRF token ตรวจด้วย `hash_equals()` |
| Session fixation | เรียก `session_regenerate_id(true)` หลัง login/สมัครสำเร็จ |
| Session hijack | คุกกี้ session ตั้ง `httponly` + `SameSite=Lax` |
| รหัสผ่านรั่ว | เก็บด้วย `password_hash()` (bcrypt) ไม่มี plaintext |
| แก้ข้อมูลคนอื่น | ตรวจ `owner_id` ทั้งฝั่ง PHP (`require_owned_item()`) และใน SQL (`WHERE id = ? AND owner_id = ?`) |
| อัปโหลดรูปอันตราย | ตรวจชนิดไฟล์จริงด้วย `getimagesize()` + เปลี่ยนชื่อสุ่ม + โฟลเดอร์ `uploads/` ห้ามรัน PHP |
| Config รั่ว | `.env` อยู่ใน `.gitignore` + `.htaccess` บล็อก `sql/`, `config/`, `includes/`, ไฟล์ dotfile |

> หมายเหตุ: ระบบนี้ป้องกันช่องโหว่พื้นฐานครบตามมาตรฐานโครงงาน
> แต่ **ไม่ควรอ้างว่าปลอดภัย 100%** — ยังมีจุดที่ต้องพิจารณาเพิ่มเมื่อใช้งานจริง (เช่น เปิด HTTPS + cookie secure)

---

## 8. บัญชีทดลองใช้งาน

| บทบาท | อีเมล | รหัสผ่าน |
|---|---|---|
| Admin | `studenta@example.com` | `TestPass123` |
| User | `studentb@example.com` | (ตามที่ตั้งใน seed) |
| User | `68011211094@msu.ac.th` | (ตามที่ตั้งใน seed) |

(บัญชีเหล่านี้ถูก seed เข้าฐานข้อมูลทั้งเครื่อง local และบนโฮสต์จริง)

---

## 9. วิธีรันบนเครื่อง (Local / XAMPP)

1. คัดลอกโปรเจกต์ไปไว้ใน `C:\xampp\htdocs\`
2. เปิด Apache + MySQL ใน XAMPP Control Panel
3. สร้างฐานข้อมูล `msu_share_care` (utf8mb4) แล้ว import `sql/schema.sql`
4. คัดลอก `.env.example` → `.env` แล้วกรอกค่า:

   ```text
   APP_DEBUG=true
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=msu_share_care
   DB_USER=root
   DB_PASS=
   ```

5. เปิดเบราว์เซอร์ไปที่ `http://localhost/msu-share-care/`

> ระหว่างพัฒนาเคยรันทดสอบที่พอร์ตเฉพาะ (Apache config `httpd-msu-proj.conf`, Listen `8087`)
> โดยตั้ง DocumentRoot ชี้ตรงที่โฟลเดอร์โปรเจกต์

### ชุดทดสอบอัตโนมัติ

ในโฟลเดอร์ `tests/` มีสคริปต์ PowerShell รวม **223 assertions**:

| ชุดทดสอบ | ครอบคลุม |
|---|---|
| `tests/phase4_tests.ps1` | item CRUD, validation, ownership (Case A/B/C/D) |
| `tests/phase5_tests.ps1` | admin dashboard, user list, item list |
| `tests/phase7_tests.ps1` | auth, CSRF, XSS/SQLi, roles, session, AJAX email, image upload |

> คำเตือน: ทุกชุดทดสอบจะล้างฐานข้อมูลก่อนรัน หลังรันให้ seed ข้อมูล demo กลับคืน

---

## 10. การ Deploy จริงบน InfinityFree

**ข้อมูลโฮสต์ปัจจุบัน**

| รายการ | ค่า |
|---|---|
| URL เว็บ | `https://msu-share-care.freedev.app/` |
| บัญชี | `if0_xxxx` |
| Web root | `/htdocs` (โฟลเดอร์ `htdocs/` ตรง ๆ) |
| MySQL host | `sqlXXX.infinityfree.com` |
| MySQL user | `if0_xxxx` |
| ชื่อฐานข้อมูล | `if0_xxxx_msu` |
| FTP host | `ftpupload.net` (ดู IP จาก panel) |

**ขั้นตอนที่ทำไปแล้ว**

1. สร้างโฮสต์ฟรี + ฐานข้อมูลบน InfinityFree
2. Import `sql/schema.sql` ผ่าน phpMyAdmin
3. สร้างไฟล์ `.env` บนเซิร์ฟเวอร์ด้วยค่า Deploy:

   ```text
   APP_DEBUG=false
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_NAME=if0_xxxx_msu
   DB_USER=if0_xxxx
   DB_PASS=<รหัสจาก panel>
   ```

4. อัปโหลดโค้ดขึ้น `htdocs/` (ผ่าน FTP)
5. Seed ข้อมูล demo → เว็บใช้งานได้

> **หมายเหตุความปลอดภัย:** รหัสผ่านที่เคยใช้ระหว่างทำ Deploy หลุดในแชท
> และรหัส FTP = รหัส MySQL (เป็นค่าเดียวกัน) — **ผู้ใช้ได้เข้าไปเปลี่ยนรหัสเองแล้ว**

---

## 11. ปัญหาระหว่าง Deploy และวิธีแก้

### ปัญหาหลัก: หน้าเว็บขึ้น "ไม่สามารถเชื่อมต่อฐานข้อมูลได้" ทั้งที่ค่าใน `.env` ถูกต้อง

**สาเหตุที่พบ:** บนโฮสต์ InfinityFree ฟังก์ชัน `putenv()` ทำงาน (คืนค่า `true`)
แต่ค่า **ไม่ส่งต่อไปให้ `getenv()`** — โค้ดเดิมที่รely พึ่ง `putenv()`/`getenv()`
จึงอ่านค่าไม่ได้เลย → ทุกค่า `DB_*` กลายเป็น `<not-set>` → ระบบ fallback ไปที่ `127.0.0.1`
→ connection refused

**วิธีแก้:** แก้ `config/db.php` ให้เก็บค่าที่อ่านจากไฟล์ `.env` ไว้ในตัวแปร global `$ENV`
แล้วสร้างฟังก์ชัน `env_value()` ที่ลองอ่านตามลำดับ:

1. ค่าจริงจาก `getenv()` (ถ้ามี)
2. ค่าที่อ่านจากไฟล์ `.env` ใน `$ENV`
3. ค่า default

→ commit `7d203bf fix: read .env values on hosts where putenv() is a no-op`

**ผล:** หลังอัปไฟล์ `config/db.php` ที่แก้แล้วขึ้นเซิร์ฟเวอร์ เว็บเชื่อมต่อฐานข้อมูลได้ปกติ

### ปัญหาย่อยอื่น ๆ ที่เจอระหว่างแก้

- ระหว่างทดสอบ local สับสนเพราะโฟลเดอร์ชั่วคราว (Temp) มีไฟล์ `config/db.php` และ `.env`
  เวอร์ชันเก่าตกค้างอยู่ → ลบไฟล์ตกค้างออกเพื่อให้ทดสอบสะอาด
- ไฟล์ `.env` ในซอร์สถูกเผลอใส่ค่า Deploy → แก้กลับเป็นค่า local
- Apache เครื่อง local เดิมพอร์ต :80 ชี้ไปที่ htdocs กลาง ไม่ใช่โปรเจกต์
  → สร้าง config เฉพาะ (`httpd-msu-proj.conf`) ให้ชี้ที่โปรเจกต์ พอร์ต 8087

---

## 12. ประวัติการทำงาน (Phase + Commit)

เราทำตามลำดับ Phase ใน `PROJECT.md` (Phase 0 → 9) โดย commit เป็นช่วงเล็ก ๆ:

| # | Commit | สรุป |
|---|---|---|
| 1 | `6924e1c` | first commit |
| 2 | `59587de` | docs: เพิ่มข้อกำหนดโปรเจกต์ + กฎการพัฒนา |
| 3 | `7698b8c` | chore: โครงโปรเจกต์ + config ฐานข้อมูล + schema |
| 4 | `08c90d1` | feat: โครง UI + เมนู responsive + หน้าเพลสโฮลเดอร์ |
| 5 | `48a6379` | feat: ระบบ authentication + CSRF + validation |
| 6 | `1269f40` | feat: item CRUD + owner-based authorization |
| 7 | `57e877c` | feat: admin dashboard + จัดการผู้ใช้/ประกาศ |
| 8 | `197e2e5` | security: `.htaccess`, APP_DEBUG, DB สิทธิ์ least-privilege |
| 9 | `ac276f9` | docs: คู่มือ setup + deploy, ปรับ schema ให้ portable |
| 10 | `7ba22e8` | docs: คู่มือนำเสนอ + demo flow + Q&A |
| 11 | `0ac24e6` | test: ชุดทดสอบอัตโนมัติ |
| 12 | `0d39828` | feat: jQuery + AJAX ตรวจอีเมล + HTML5 validation |
| 13 | `4a78813` | test: ครอบคลุม AJAX email check |
| 14 | `23a3d36` | docs: อัปเดต demo flow + จำนวน test |
| 15 | `e30311d` | docs: คู่มือ Deploy ฟรี (InfinityFree) |
| 16 | `d72d675` | feat: อัปโหลดรูปสิ่งของ + validation + cleanup |
| 17 | `00ca52f` | test: ครอบคลุม image upload (224 assertions) |
| 18 | `b409526` | feat: เพิ่มลิมิตเป็น 20MB + พรีวิว 1:1 + file picker |
| 19 | `ff321a3` | docs: อัปเดตขนาดรูป + พรีวิว |
| 20 | `5d4a33f` | style: responsive desktop/tablet/mobile |
| 21 | `2eca0a3` | ui: ปรับดีไซน์ + ขยายตัวหนังสือบนมือถือ |
| 22 | `71c2aeb` | docs: เขียน README ภาษาไทยละเอียด |

---

## 13. ประวัติงานช่วงท้าย (Admin Features + Deploy)

งานช่วงหลังสุด (ที่ยังไม่ได้อยู่ใน README เดิม) มี 5 commit:

| Commit | สรุป |
|---|---|
| `7d203bf` | **fix:** อ่านค่า `.env` บนโฮสต์ที่ `putenv()` ไม่ทำงาน (root cause ของปัญหาเชื่อม DB ไม่ได้) |
| `430d352` | **feat:** ให้แอดมินแก้ไข / ทำ Completed / ลบ ประกาศของใครก็ได้ |
| `0b03c22` | **feat:** ให้แอดมินจัดการผู้ใช้ได้ (เพิ่ม / แก้ไข / ลบ) |
| `f094a4b` | **feat:** ใส่ favicon + โลโก้บนแท็บเบราว์เซอร์ |
| `13ff75a` | **docs:** เพิ่มเอกสารสรุปโปรเจกต์ภาษาไทย (`PROJECT_SUMMARY.md`) |

**รายละเอียดสิ่งที่แก้/เพิ่ม**

- `includes/auth.php` — `require_owned_item()` อนุญาตให้แอดมินผ่านได้
- `item_edit.php`, `item_delete.php`, `item_complete.php` — แอดมินข้ามการเช็ค `owner_id`
  และเปลี่ยนเส้นทางกลับไปที่หน้าจัดการประกาศของแอดมิน
- `item_detail.php` — เพิ่มตัวแปร `$isAdmin` / `$canManage` ให้ปุ่มแสดงตามสิทธิ์
- `admin/users.php` — เพิ่มปุ่ม "เพิ่มผู้ใช้" + ปุ่มแก้ไข/ลบแต่ละแถว
- `admin/user_create.php`, `admin/user_edit.php`, `admin/user_delete.php` — ไฟล์ใหม่ (CRUD ผู้ใช้)
- `admin/items.php` — เพิ่มปุ่ม "เพิ่มประกาศ" + ปุ่มแก้ไขแต่ละแถว
- `assets/favicon.svg` + `includes/header.php` — โลโก้ SC บนแท็บเบราว์เซอร์

**การทดสอบ/Deploy ที่ทำ**

- PHP lint ผ่านทุกไฟล์ที่แก้
- รันชุดทดสอบ local แบบ end-to-end ครบ (admin item edit/complete/delete, 403 สำหรับ non-admin,
  user CRUD, guard ต่าง ๆ, ทดสอบ CSRF ทางลบ, favicon) — **ผ่าน 28/28**
- Push GitHub แล้ว: `origin/main` = `13ff75a`
- อัปไฟล์ใหม่ 12 ไฟล์ขึ้นโฮสต์ผ่าน FTP สำเร็จ 12/12
- ตรวจขนาดไฟล์บนโฮสต์เทียบกับ local — **ตรงกันทุกไฟล์**
- ลบไฟล์วินิจฉัยชั่วคราวบนโฮสต์ (`dbcheck.php`, `probe_7f3k2.txt`) ออกแล้ว

---

## 14. สถานะปัจจุบัน

| ด้าน | สถานะ |
|---|---|
| โค้ดในเครื่อง | ✅ ทำงานได้ครบ (commit ล่าสุด `13ff75a`) |
| GitHub | ✅ push ล่าสุดแล้ว (`origin/main` = `13ff75a`) |
| เว็บบนโฮสต์จริง | ✅ ใช้งานได้ที่ `https://msu-share-care.freedev.app/` |
| ฟีเจอร์แอดมินใหม่ | ✅ อัปขึ้นเซิร์ฟเวอร์แล้ว (12 ไฟล์) |
| Favicon | ✅ ขึ้นเซิร์ฟเวอร์แล้ว |
| รหัสผ่านที่หลุดในแชท | ✅ ผู้ใช้เปลี่ยนเองแล้ว |

> **working tree ณ เวลาตรวจสอบ (ยังไม่ commit):** การออกแบบ UI ใหม่
> (`assets/css/style.css`, `includes/header.php`, `assets/favicon.svg`)
> และงานแก้เอกสาร (`README.md`, `PRESENTATION.md`, `PROJECT_SUMMARY.md`)
> ยังอยู่เฉพาะในเครื่อง ยังไม่ commit/push

---

## 15. สิ่งที่ยังไม่ได้ทำ / ยังค้างอยู่

1. **ทดสอบบนเว็บจริงแบบ manual ให้ครบ** — ต้องกด Ctrl+F5 แล้วเช็ค:
   - หน้า `admin/users.php` เห็นปุ่ม เพิ่มผู้ใช้ + แก้ไข/ลบ
   - เปิดประกาศของคนอื่นในฐานะแอดมิน เห็นปุ่ม แก้ไข / Completed / ลบ (ไม่ขึ้น 403 แล้ว)
   - แท็บเบราว์เซอร์ขึ้นโลโก้ SC
2. **เปิด HTTPS/SSL** — ควรยืนยันว่าลิงก์ `https://` ใช้งานได้ และพิจารณาตั้ง
   cookie session เป็น `secure` เมื่อรันบน HTTPS จริง
3. **อัปเดตเอกสารส่วนที่เหลือให้ตรงของจริง** — ปรับคำอธิบายสิทธิ์ Admin ใน README.md
   ให้ตรงกับโค้ดแล้ว (งาน documentation fix) เหลือส่วนอื่นที่ควรตามต่อ เช่น
   จำนวน test, ตารางไฟล์เอกสาร และชุดทดสอบ `tests/phase7_tests.ps1`
   ที่ยัง assert พฤติกรรม admin แบบเก่า (admin แก้/ลบของคนอื่น -> 403)
4. **เก็บกวาดไฟล์ Deploy ชั่วคราว** — ไฟล์ zip/สคริปต์ทดสอบชั่วคราวในโฟลเดอร์ Temp (ไม่กระทบตัวเว็บ)

---

## 16. ไฟล์สำหรับนำไปส่ง / นำเสนอ

| ไฟล์ | ใช้ทำอะไร |
|---|---|
| `PROJECT.md` | ข้อกำหนด/ขอบเขตฉบับเต็ม |
| `README.md` | คู่มือติดตั้ง + ใช้งาน |
| `DEPLOY.md` | คู่มือ Deploy ฟรีทีละขั้น |
| `PRESENTATION.md` | คู่มือนำเสนอ + demo flow + Q&A |
| `PROJECT_SUMMARY.md` | เอกสารฉบับนี้ — สรุปทำอะไรมาบ้าง |
| `sql/schema.sql` | สคริปต์สร้างฐานข้อมูล |
| `tests/` | ชุดทดสอบอัตโนมัติ (223 assertions) |

**ลิงก์เว็บจริง:** <https://msu-share-care.freedev.app/>

---

*MSU Share & Care — Web Programming term project*
