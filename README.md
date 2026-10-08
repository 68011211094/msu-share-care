# MSU Share & Care

Web Application for Mahasarakham University students to share unused items through **Donate** or **Exchange**.

## Project Type

Web Programming term project.

## Main Concept

MSU Share & Care is a community bulletin board.

Users can:

- Register
- Login / Logout
- View item announcements
- Create item announcements
- Choose Donate or Exchange
- Edit/delete their own announcements
- Mark their own item as Completed
- Contact the owner using the contact information provided

The system does not handle payments, delivery, or internal request/approval workflows.

## Planned Technology

- HTML5
- CSS3
- JavaScript
- jQuery
- AJAX / Fetch
- PHP
- MySQL
- Apache / XAMPP
- Git / GitHub

## Development Principle

Build a small, correct, secure, readable, explainable, and deployable application.

See `PROJECT.md` for the full project specification.

See `AGENTS.md` for AI/development rules.

## Requirements

- XAMPP (Apache, MySQL/MariaDB, PHP 8.x)
- Apache must allow `.htaccess` (`AllowOverride All` — the default in XAMPP)

## Setup (XAMPP)

1. Copy this folder into `C:\xampp\htdocs\msu-share-care`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Create the database, then import `sql/schema.sql` into it:

   - **phpMyAdmin:** New → database name `msu_share_care` → Import → choose `sql/schema.sql`.
   - **CLI:**

     ```bash
     mysql -u root -e "CREATE DATABASE msu_share_care CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
     mysql -u root msu_share_care < sql/schema.sql
     ```
4. Create a least-privilege database user (replace `your_password` with your own password):

   ```sql
   CREATE USER 'msu_app'@'localhost' IDENTIFIED BY 'your_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON msu_share_care.* TO 'msu_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

5. Copy `.env.example` to `.env` and fill in your values:

   ```text
   DB_NAME=msu_share_care
   DB_USER=msu_app
   DB_PASS=your_password
   ```

6. Open `http://localhost/msu-share-care/`.
7. Register an account, then promote it to admin:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your_email@example.com';
   ```

## Production Configuration

- Set `APP_DEBUG=false` in `.env` on the server. Error details are then hidden from
  visitors and only written to the PHP error log.
- `.env` contains secrets and is gitignored. Never commit it. Deploy by copying
  `.env.example` and filling in real values on the server only.
- `.htaccess` blocks HTTP access to `sql/`, `config/`, `includes/`, dotfiles,
  `.sql` and `.md` files, and disables directory listing.
- The application logs in with a database user that only has
  SELECT / INSERT / UPDATE / DELETE (no schema changes).
- Passwords are stored with `password_hash()`; all SQL uses prepared statements;
  all output is escaped; every state-changing POST requires a CSRF token.

## Hosting Notes

- Any Apache + PHP 8.x + MySQL host works. The app runs from the document root
  or from a subdirectory (all links are relative).
- `mod_rewrite` must be enabled for `.htaccess` rules to apply.
- Keep `APP_DEBUG=false` and the database credentials private in production.

## Manual Smoke Test

1. Register → auto login → nav shows user name.
2. Create an item (Donate and Exchange) → appears on the home page.
3. Edit own item → change saved. Mark as Completed → status changes.
4. Open the item in a second browser (different account) → visible, but no
   edit/delete controls; POST-ing the edit URL directly returns 403.
5. Delete own item → removed.
6. Log in as admin → dashboard stats, user list, item list; admin delete works.
7. Log out → session ends (revisiting my page redirects to login).
8. Visit `/.env`, `/sql/schema.sql`, `/config/`, `/includes/` → 403.

## Automated Tests

The `tests/` folder contains PowerShell test suites (212 assertions total):

- `tests/phase4_tests.ps1` — item CRUD, validation, ownership (56 assertions)
- `tests/phase5_tests.ps1` — admin dashboard, user list, item list (32 assertions)
- `tests/phase7_tests.ps1` — auth, CSRF, XSS/SQLi, roles, session, AJAX email check (124 assertions)

Prerequisites:

- The app is served at `http://127.0.0.1:8080` (change `$base` at the top of
  the script if your URL differs, e.g. `http://localhost/msu-share-care`).
- MySQL CLI is at `C:\xampp\mysql\bin\mysql.exe` (change `$mysql` if needed).
- Windows PowerShell.

Run from the project folder:

```powershell
powershell -ExecutionPolicy Bypass -File tests\phase7_tests.ps1
```

**Warning:** every suite resets the database first (deletes all users and
items) so its results are independent. After running the tests, restore demo
data with the seed SQL in `PRESENTATION.md` or by re-importing
`sql/schema.sql`.
