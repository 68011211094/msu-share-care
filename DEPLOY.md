# DEPLOY.md — ขึ้นเว็บเวอร์ชันฟรี (Free Hosting)

ขั้นตอนนี้ทำให้ได้ **ลิงก์ HTTPS สาธารณะฟรี** เพื่อส่งให้อาจารย์เปิดดูได้จริง
โดยไม่ต้องเสียเงินและไม่ต้องใช้บัตรเครดิต

## 1. ตรวจก่อนเริ่ม

- [ ] โค้ดรันได้บนเครื่องแล้ว (ตาม `README.md — Setup`)
- [ ] มีไฟล์ `sql/schema.sql` พร้อม
- [ ] มีอีเมลจริงสำหรับสมัครโฮสต์ฟรี
- [ ] ตั้งใจให้เว็บพร้อมใช้งานจริง (`APP_DEBUG` จะถูกปิดในขั้นตอนที่ 6)

## 2. เลือกโฮสต์

| โฮสต์ | ฟรีถาวร? | ต้องใช้บัตร? | PHP | หมายเหตุ |
|---|---|---|---|---|
| **InfinityFree** (แนะนำ) | ✅ | ❌ | 8.4 | PHP + MySQL + Apache + `.htaccess` + SSL ฟรี ไม่มีโฆษณาในเว็บ |
| Byet.host | ✅ | ❌ | 8.3 | คล้าย InfinityFree ให้ subdomain `*.byet.org` |
| TinkerHost | ✅ | ❌ | 8.x | พื้นที่เล็ก (1GB) อัปโหลดทาง FTP |
| Oracle Cloud Always Free | ✅ | ✅ (ยืนยันตัวตน) | ตั้งเอง | ทรงพลังสุดแต่ต้องตั้ง server เอง — เกินจำเป็นถ้าแค่ส่งงาน |

> ❌ **ห้ามใช้** GitHub Pages / Netlify / Vercel — บริการเหล่านั้นรัน **HTML/CSS/JS อย่างเดียว** รัน PHP ไม่ได้

ขั้นตอนต่อไปใช้ **InfinityFree** เป็นหลัก

## 3. สมัครและสร้างเว็บ

1. ไปที่ <https://www.infinityfree.com/> → **Sign Up**
2. ยืนยันอีเมล → ลงชื่อเข้าใช้ → **Create Account**
3. สร้าง hosting account → ตั้งชื่อเว็บ เช่น `msu-share-care`
4. เลือก subdomain ฟรี เช่น `msu-share-care.infinityfreeapp.com`
   (ขั้นต่อไปจะเข้า control panel = `VistaPanel`)

## 4. สร้างฐานข้อมูล

1. ใน control panel เลือก **MySQL Databases** → **Create Database**
2. จดค่าที่ได้มาใส่ไฟล์ `.env` (ขั้นตอนที่ 6) ค่าเหล่านี้ขึ้นอยู่กับ host ไม่ควรใช้ค่ารันบนเครื่องตัวเอง:

   | ตัวแปร | ตัวอย่าง |
   |---|---|
   | DB_HOST | `sqlXXX.infinityfree.com` (ใช้ค่าจาก panel ไม่ใช่ `127.0.0.1`) |
   | DB_NAME | `if0_xxxx_msu` (ค่าจาก panel) |
   | DB_USER | `if0_xxxx` (ค่าจาก panel) |
   | DB_PASS | รหัสที่ panel ให้มา |

3. เปิด **phpMyAdmin** (ใน panel) → เลือก database → แท็บ **Import** → เลือกไฟล์ `sql/schema.sql` → **Go**
4. ตรวจว่ามีตาราง `users` และ `items` ปรากฏ

## 5. อัปโหลดโค้ด

มี 2 วิธี:

**วิธี ก — File Manager (ง่ายสุด):**
1. สร้าง zip ของโปรเจกต์โดย**ไม่รวม** `.env`, `.git`, `tests`
   (zip พร้อมใช้แล้วอยู่ที่ `C:\Users\phets\AppData\Local\Temp\opencode\msu-share-care-deploy.zip`)
2. เปิด **File Manager** → เข้าโฟลเดอร์ `htdocs/`
3. อัปโหลด zip → คลิกขวา → **Extract** → เนื้อหาควรอยู่ที่ `htdocs/` ตรง ๆ
   (เช่น เห็น `index.php`, `.htaccess`, โฟลเดอร์ `config/`, `includes/`)

**วิธี ข — FTP:**
- ใช้รายการ FTP จาก control panel (host/username/password) ต่อด้วย FileZilla แล้วอัปโหลดเนื้อหาลง `htdocs/`

## 6. สร้างไฟล์ `.env`

1. ใน File Manager เปิดโฟลเดอร์ `htdocs/`
2. คัดลอกไฟล์ `.env.example` → ตั้งชื่อใหม่เป็น `.env`
3. แก้ไขเนื้อหาดังนี้ (หมายเหตุ: `.env` จะถูกบล็อกไม่ให้เข้าผ่านเว็บโดย `.htaccess`):

   ```text
   APP_DEBUG=false
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_NAME=if0_xxxx_msu
   DB_USER=if0_xxxx
   DB_PASS=รหัสจาก panel
   ```

   - `APP_DEBUG=false` สำคัญ: ถ้าเกิด error ผู้เข้าชมจะไม่เห็นข้อมูลภายใน server
   - **อย่า** commit `.env` นี้ลง Git

## 7. เปิด SSL (HTTPS)

- ใน control panel เลือกเว็บ → **SSL Certificates** → เปิดใช้งาน
  (ยุคปัจจุบันเปิด SSL แล้ว ลิงก์ควรเป็น `https://...infinityfreeapp.com`)
- ทดสอบว่าเปิดด้วย `https://` ได้ ไม่ติดเตือน

## 8. สร้างบัญชี Admin

1. เปิดลิงก์เว็บ → สมัครสมาชิกด้วยอีเมลคุณ
2. เข้า **phpMyAdmin** → database → แท็บ **SQL** รัน:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'อีเมลที่สมัครไป';
   ```

3. เข้าสู่ระบบอีกครั้ง → ควรเห็นเมนู **Admin**

## 9. ตรวจหลังติดตั้ง (Smoke Test บนเว็บจริง)

| # | ทดสอบ | ผลที่ควรเห็น |
|---|---|---|
| 1 | เปิด URL หน้าแรก | มีรายการประกาศตัวอย่าง (ถ้า import ข้อมูลมา) หรือหน้าว่างตาม schema ใหม่ |
| 2 | สมัครสมาชิก | เข้าสู่ระบบอัตโนมัติ, เมนูขึ้นชื่อ |
| 3 | สร้าง/แก้ไข/ลบ item | ทำงานเหมือนเครื่องตัวเอง |
| 4 | ลอง URL `/.env` | **403** (ห้ามเห็นไฟล์) |
| 5 | ลองเข้าหน้า admin ในชื่อ user ปกติ | **403** |
| 6 | Login/Logout | พฤติกรรมปกติ |
| 7 | รีเฟรช 2-3 ครั้ง | ไม่มีเตือน HTTP/HTTPS ปะปน |

## 10. ปัญหาที่พบบ่อย (แก้ยังไง)

| อาการ | สาเหตุ / วิธีแก้ |
|---|---|
| เว็บ 500 ทั้งหน้า (ทั้งเว็บพัง) | มักมาจาก `.htaccess` → host บางรายไม่อนุญาต `Options -Indexes` → เปิด `.htaccess` แล้วลบ/คอมเมนต์บรรทัดนั้น |
| เชื่อมฐานข้อมูลไม่ได้ | `.env` ผิด: ใช้ค่า DB ตอน localhost มาใส่ → ใช้ค่าจาก phpMyAdmin ของ host, และ `DB_HOST` ต้องเป็นค่าจาก panel ไม่ใช่ `127.0.0.1` |
| รูป/ลิงก์ขาด | ลิงก์ในระบบใช้ relative ทั้งหมด ดังนั้นต้องอัปโหลดโค้ดไว้ที่ `htdocs/` ตรง ๆ ไม่ใช่ใต้โฟลเดอร์ย่อย |
| ฟอร์มยาวเกิน (ถ้าเจอ error ไฟล์ใหญ่เกิน) | Host ฟรีจำกัดขนาดไฟล์; โปรเจกต์นี้ไฟล์เล็ก ไม่ควรเจออันนี้ |
| PHP 8.4 error/notice | แจ้งไว้ใน issue — โค้ดใช้ API มาตรฐาน (PDO, `password_hash`, `htmlspecialchars`) ความเสี่ยงต่ำ |

## 11. แผนสำรอง (ถ้า host ล่มหรือลิงก์พังตอนนำเสนอ)

ใช้เครื่องตัวเองสาธิตชั่วคราว:

1. เปิด XAMPP (Apache + MySQL) และโหลดเว็บตาม `README.md`
2. เปิด tunnel ฟรีเพื่อให้ได้ลิงก์ชั่วคราว:
   - **Cloudflare Tunnel** (แนะนำ): ติดตั้ง `cloudflared` แล้วรัน
     `cloudflared tunnel --url http://localhost` → ได้ URL `https://...trycloudflare.com`
   - หรือ **ngrok**: `ngrok http 8080`
3. หมายเหตุ: URL เปลี่ยนทุกครั้งที่ปิด/เปิดใหม่ และเครื่องต้องเปิดค้างไว้ระหว่างนำเสนอ

## 12. ก่อนส่งไฟล์/ส่งลิงก์ให้อาจารย์

- [ ] เว็บใช้งานได้จริงเป็นเวลา 2–3 วันติดต่อกัน (อัปโหลดล่วงหน้า)
- [ ] `APP_DEBUG=false` ใน `.env`
- [ ] ลิงก์ https เปิดแล้วเหมือนเครื่องตัวเอง (ตามข้อ 9)
- [ ] มีบัญชี demo ส่งให้อาจารย์ (เช่น `studenta@example.com` / รหัส `TestPass123` ที่เป็น admin)
- [ ] เก็บลิงก์โฮสต์ + ข้อมูล DB ไว้ที่ตัวเองด้วย (ถ้าต้องย้าย host)