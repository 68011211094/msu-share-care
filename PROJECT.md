# MSU Share & Care — Project Specification

## 1. ภาพรวมโครงการ

**ชื่อโครงการ:** MSU Share & Care  
**ประเภท:** Web Application  
**กลุ่มเป้าหมาย:** นักศึกษามหาวิทยาลัยมหาสารคาม (MSU)

MSU Share & Care เป็นเว็บชุมชนแบบ **กระดานประกาศ (Bulletin Board)** สำหรับนักศึกษา MSU ที่ต้องการแบ่งปันของที่ไม่ได้ใช้แล้ว โดยมี 2 รูปแบบ:

- Donate — บริจาค
- Exchange — แลกเปลี่ยน

ระบบนี้ **ไม่ใช่ Marketplace** และไม่จัดการการซื้อขาย การชำระเงิน การจัดส่ง หรือ workflow คำขอรับของ/คำขอแลกเปลี่ยนภายในระบบ

### หลักการทำงาน

1. เจ้าของสมัครสมาชิก / เข้าสู่ระบบ
2. เจ้าของสร้างประกาศสิ่งของ
3. เลือก Donate หรือ Exchange
4. ผู้สนใจดูรายละเอียด
5. ผู้สนใจติดต่อเจ้าของผ่านช่องทางที่เจ้าของระบุ
6. เมื่อตกลงกันสำเร็จ เจ้าของเปลี่ยนสถานะเป็น Completed

## 2. เป้าหมาย

สร้าง Web Application ที่ตอบ requirement ของรายวิชา Web Programming โดยเน้น:

- ความถูกต้อง
- ความปลอดภัย
- ความเรียบง่าย
- อ่านและอธิบายได้
- Debug ง่าย
- Deploy ได้
- ไม่เพิ่ม feature ที่ไม่จำเป็น

**เป้าหมายคือระบบเล็ก แต่ถูกต้อง ปลอดภัย อธิบายได้ และส่งงานทัน**

## 3. Core Requirements

ระบบต้องมีอย่างน้อย:

- Register
- Login
- Logout
- User / Admin roles
- Session / Authentication
- Password hashing
- Item CRUD
- Owner-based authorization
- Donate / Exchange
- Available / Completed
- MySQL database
- อย่างน้อย 2 forms ที่บันทึกข้อมูลลง database
- User pages
- Admin dashboard
- Validation
- SQL Injection protection
- XSS protection
- Authorization protection
- Session security
- Deploy ได้

## 4. User Role

User สามารถ:

- Register
- Login
- Logout
- ดูรายการ
- ดูรายละเอียด
- สร้างรายการ
- แก้ไขรายการของตนเอง
- ลบรายการของตนเอง
- เปลี่ยนสถานะรายการของตนเองเป็น Completed

User ไม่สามารถ:

- แก้ไขรายการของผู้อื่น
- ลบรายการของผู้อื่น
- เข้าถึงหน้าของ Admin โดยไม่ได้รับสิทธิ์

## 5. Admin Role

Admin สามารถเข้าถึง:

- Admin dashboard
- รายการผู้ใช้
- รายการสิ่งของ
- การจัดการที่จำเป็น
- สถิติพื้นฐาน

Admin ไม่ควรมีสิทธิ์เกินความจำเป็นของ requirement

## 6. Ownership / Authorization

ทุก Item ต้องมี owner/user identifier

ก่อนแก้ไข ลบ เปลี่ยนสถานะ หรือ operation ที่แก้ข้อมูล ต้องตรวจสอบฝั่ง Server ว่า:

```text
current_user.id == item.owner_id
```

ห้ามพึ่งเพียงการซ่อนปุ่มในหน้าเว็บ

ต้องป้องกันกรณีผู้ใช้เปลี่ยน ID ใน URL หรือ request โดยตรง

### Ownership Test Cases

**Case A — Owner**

User A สร้าง Item

- A แก้ไขได้
- A ลบได้

**Case B — Other User**

User B เปิด Item ของ A

- B ดูได้ตามสิทธิ์
- B แก้ไขไม่ได้
- B ลบไม่ได้

**Case C — ID Manipulation**

B เปลี่ยน Item ID ใน URL/request เพื่อพยายามแก้ข้อมูลของ A

- Server ต้องปฏิเสธ

**Case D — Admin**

Admin เข้า Admin page ได้ตามสิทธิ์ที่กำหนด

## 7. Item Entity

ใช้ Entity เดียวสำหรับทั้ง Donate และ Exchange

ฟิลด์หลักที่ต้องพิจารณา:

- owner/user identifier
- title
- description
- type
- category (ถ้าใช้)
- status
- contact
- created_at
- updated_at

ค่าหลัก:

```text
type:
- Donate
- Exchange

status:
- Available
- Completed
```

Category และ image เป็น feature ที่เพิ่มได้หลังจาก core system ทำงานแล้ว

## 8. Forms

อย่างน้อยต้องมี:

1. Register form
2. Create/Edit Item form

Form ต้องมี:

- Client-side validation ตามความเหมาะสม
- Server-side validation

ห้ามพึ่ง client-side validation เพียงอย่างเดียว

## 9. Security

ต้องพิจารณา:

### Password

ใช้:

```php
password_hash()
password_verify()
```

ห้ามเก็บ plaintext password

### SQL Injection

ใช้:

- Prepared statements
- Parameterized queries

### XSS

Escape output เช่น:

```php
htmlspecialchars()
```

### CSRF

ใช้ CSRF token สำหรับ state-changing requests ตามความเหมาะสม

### Session

ต้องมีการจัดการ session อย่างปลอดภัย รวมถึงพิจารณา session fixation

### Authorization

ตรวจสอบ role และ ownership ฝั่ง Server ทุกครั้ง

### Secrets

เก็บ secret/configuration ผ่าน environment หรือ configuration ที่ไม่ commit secret ลง Git

## 10. Nice-to-Have

ทำหลัง Core Requirements สำเร็จเท่านั้น:

- Search
- Filter
- Category
- รูปภาพ 1 รูปต่อ Item
- Dashboard statistics เพิ่มเติม

## 11. Features ที่ไม่ควรทำ

ห้ามเพิ่มโดยไม่มีเหตุผล/การอนุมัติ:

- Chat
- Notifications
- Payments
- Delivery
- Auction
- Matching
- Recommendation
- Social feed
- Likes
- Follows
- Comments
- Google Login / OAuth
- Mobile application
- Complex student-ID verification
- Request / Approve / Reject workflow

## 12. Technology Direction

Stack ที่สอดคล้องกับรายวิชา:

- HTML5
- CSS3
- JavaScript
- jQuery
- AJAX / Fetch
- PHP
- MySQL
- Apache / XAMPP
- VS Code
- Git / GitHub

หลักการทำงาน:

```text
Browser
   ↓ HTTP/HTTPS
Apache
   ↓
PHP
   ↓
MySQL
```

## 13. Code Quality

Code ต้อง:

- Beginner-readable
- Meaningful names
- Simple
- Easy to debug
- ไม่ over-engineer
- ใช้ abstraction เท่าที่จำเป็น

ห้าม rewrite project ทั้งหมดเพียงเพื่อเพิ่ม feature เล็ก ๆ

## 14. AI Development Rules

ก่อน implementation ต้องตอบ:

- งานนี้แก้ requirement ข้อใด
- เกี่ยวข้องกับส่วนไหน
- มี dependency อะไร
- มีผลต่อ database หรือไม่
- มี security concern หรือไม่

ก่อนแก้ code ต้องระบุ:

```text
สร้าง:
- path/file.ext

แก้:
- path/file.ext

ไม่แก้:
- path/file.ext
```

ทำงานเป็น Step เล็ก ๆ

หลัง implementation ต้องรายงาน:

- แก้ไฟล์อะไร
- เพิ่มอะไร
- แก้ logic อะไร
- security ที่เกี่ยวข้อง
- วิธีทดสอบ
- สิ่งที่ยังไม่ได้ทำ

AI ห้าม:

- เดา requirement สำคัญ
- เพิ่ม feature เอง
- เปลี่ยน architecture เองโดยไม่อธิบาย
- rewrite project โดยไม่จำเป็น
- อ้างว่าทดสอบแล้วถ้ายังไม่ได้ทดสอบจริง
- อ้างว่า security ปลอดภัย 100%
- สร้าง secret/API key ปลอม
- ลบไฟล์สำคัญโดยไม่แจ้ง

หากไม่แน่ใจ ให้ถามก่อน

## 15. Git / Backup

ใช้ Git เป็น checkpoint:

- commit เป็นช่วงเล็ก ๆ
- ใช้ข้อความ commit ที่อธิบายงาน
- ก่อนงานใหญ่ควรมีจุดย้อนกลับ
- ไม่ commit secret
- ไม่ commit `.env` หากมี secret

## 16. Definition of Done

Feature ถือว่าเสร็จเมื่อ:

- requirement ถูกตอบ
- code ทำงาน
- validation ถูกต้อง
- authorization ถูกต้อง
- security ที่เกี่ยวข้องถูกตรวจ
- ทดสอบ happy path
- ทดสอบ invalid input
- ทดสอบ unauthorized access
- ไม่มี error สำคัญ
- ไม่ทำ feature อื่นเสีย
- สามารถอธิบายให้อาจารย์ฟังได้

## 17. Development Phases

### Phase 0 — Requirement
- อ่านโจทย์
- สรุปข้อกำหนด
- Freeze scope

### Phase 1 — Architecture
- เลือก stack
- วาง project structure
- ออกแบบ database
- ออกแบบ authorization

### Phase 2 — UI/UX
- วางหน้า
- Navigation
- Forms
- Responsive layout

### Phase 3 — Authentication
- Register
- Login
- Logout
- Password hashing
- Session

### Phase 4 — Item CRUD
- Create
- Read
- Update
- Delete
- Ownership

### Phase 5 — Admin
- Admin authentication/authorization
- Dashboard
- User/item management

### Phase 6 — Security
- Validation
- SQL Injection
- XSS
- CSRF
- Session
- Access control

### Phase 7 — Testing
- Functional tests
- Security tests
- Ownership tests
- Error tests

### Phase 8 — Deployment
- Production configuration
- Environment variables
- Database
- Hosting
- Final smoke test

### Phase 9 — Presentation
- Demo flow
- Professor Q&A
- Security explanation
- Architecture explanation

## 18. Decision Priority

เมื่อมีปัญหา ให้เลือกตามลำดับ:

1. Requirement
2. Security
3. Simplicity
4. Readability
5. Maintainability
6. Deployability
7. Aesthetics
8. Extra features

หาก feature ไม่ช่วย requirement และเพิ่มความซับซ้อน ให้ไม่ทำ

## 19. Final Goal

ระบบสุดท้ายต้อง:

- ใช้งานได้จริง
- มี database จริง
- มี authentication
- มี authorization
- มี User/Admin roles
- มี CRUD
- มี ownership protection
- มีอย่างน้อย 2 forms
- มี admin page
- มี security
- deploy ได้
- อธิบายได้

**ระบบเล็ก แต่ถูกต้อง ปลอดภัย อธิบายได้ และส่งงานทัน**
