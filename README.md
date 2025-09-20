## 📚 Library Management System

A secure, MVC-style PHP library system with Spanish UI and English backend/domain model. Includes catalog, loans/returns, holds (waitlist), users, reporting, dashboards, authentication, and role-based access control.

## ✨ Features
- **Catalog (Libros)**: search/filter (título, autor, ISBN, código), pagination, create/edit, detail, archive (soft-delete) for librarians, hard-delete for admins, ISBN check with modal.
- **Loans (Préstamos)**: create via book ID/ISBN and user Cédula/llave, extend deadlines, overdue tracking, “due soon” lists.
- **Returns (Devoluciones)**: confirmation modal, auto-assign next user from holds queue on return.
- **Holds/Waitlist (Lista de espera)**: add/cancel/fulfill holds; only when book unavailable and not archived.
- **Users (Usuarios)**: create with strict validation (English keys), list/search/sort/paginate, sanction/unsanction.
- **Reporting (Reportes)**: CSV exports for loans, overdue, due soon, users, books (role-restricted).
- **AuthN/AuthZ**: unified login (librarians via email/password; patrons via id_number/user_key), roles via `ADMIN_EMAILS`, CSRF protection, secure sessions.
- **Dashboard**: KPIs, recent loans/returns, overdue and due-soon sections.

## 🧰 Tech Stack
- PHP 7.4+ with MySQLi prepared statements
- Bootstrap 4/5, Font Awesome
- PSR-4 autoloading, simple MVC structure

## 🗂️ Project Structure
```text
library/
├── app/
│   ├── Controllers/
│   │   └── AuthController.php
│   ├── Helpers/
│   │   ├── Session.php
│   │   ├── Validator.php
│   │   └── Notifier.php
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   └── Models/
│       ├── Model.php
│       ├── Book.php
│       ├── User.php
│       ├── Loan.php
│       ├── Hold.php
│       └── Librarian.php
├── config/
│   ├── autoload.php
│   ├── config.php
│   └── Database.php
├── api/
│   ├── books_check_isbn.php
│   ├── books_lookup.php
│   └── users_lookup.php
├── logs/ (writable)
├── public/
├── resources/views/
└── main pages: login.php, dashboard.php, users.php, books*.php, loans.php, returns.php, reports.php, holds.php, profile.php, settings.php, forgot-password.php, reset-password.php
```

## ⚙️ Configuration (.env)
Create `library/.env` with:
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=Library
DB_USERNAME=root
DB_PASSWORD=

APP_NAME="Library Management System"
APP_URL=http://localhost
APP_ENV=production
APP_DEBUG=false
APP_KEY=change_me_32_chars_hex

CSRF_ENABLED=true
SESSION_LIFETIME=120
ADMIN_EMAILS=admin@example.com,other-admin@example.com
```
Notes:
- Admin role is granted when the logged-in librarian email is in `ADMIN_EMAILS`.
- Ensure `logs/` is writable by the web server.

## 🗄️ Database Overview
- Core tables: `books`, `users`, `loans`, `librarians`, `holds` plus lookups: `classifications`, `origins`, `labels`, `rooms`.
- Keys: English-only table/column names. Primary keys: `books.id` (INT), `users.id_number` (BIGINT), `loans.loan_id` (INT), `librarians.id` (INT), `holds.id` (INT).
- Foreign keys: `loans.book_id -> books.id`, `loans.user_id -> users.id_number`, etc.
- Passwords: bcrypt.
- If you don’t have SQL files, create schema accordingly; see model properties for fields and types.

## 🚀 Setup & Run
1. Create MySQL database and schema.
2. Create `.env` (see above).
3. Set folder permissions: `logs/` (and `public/uploads` if used) writable by web server.
4. Configure Apache (Debian) VirtualHost (see deploy section) or place in any subdirectory.
5. Visit `APP_URL` and log in at `login.php`.
   - Seed a librarian in `librarians` table manually (email must match `ADMIN_EMAILS` for admin actions).

## 🔐 Security
- CSRF tokens on all forms (`Session::csrfField()` / `Session::verifyCsrfToken()`).
- Prepared statements everywhere (`Config\Database` wrapper).
- Secure sessions: HTTPOnly, SameSite, optional Secure flag, ID regeneration, inactivity timeout.
- Output escaping via `Validator::escape` / `htmlspecialchars`.
- Role checks via `AuthMiddleware::hasRole('admin')`.
- Activity logging to `logs/activity.log` (JSON lines). Password reset link is not logged here.

## 🔎 API Endpoints (AJAX)
- `api/books_lookup.php?q=...` → `{ data: [ { id, isbn, title, author } ] }`
- `api/users_lookup.php?q=...` → `{ data: [ { id_number, user_key, first_name, last_name } ] }`
- `api/books_check_isbn.php?isbn=...` → `{ found: bool, book?: {...} }`

## 🧭 Key Pages
- `login.php` (librarians) and patron subform (Cédula/Llave)
- `forgot-password.php`, `reset-password.php` (tokens stored in `librarians.reset_token*`)
- `dashboard.php` (KPIs, overdue, due soon)
- `books.php`, `books_create.php`, `books_edit.php`, `books_detail.php`, `books_delete.php`
- `loans.php`, `returns.php` (auto-assign from holds upon return)
- `holds.php` (queue management)
- `users.php` (create, list, search, sort, paginate)
- `reports.php` (CSV exports; some admin-only)
- `profile.php`, `settings.php` (profile and password change)

## 📝 Logging
- `logs/error.log`: PHP errors
- `logs/activity.log`: JSON activity entries (user, action, metadata)
- `logs/login_attempts.log`: login attempts
- `logs/emails.log`: optional email-like messages written via `App\Helpers\Notifier` (placeholder SMTP)

## 🌐 Language Policy
- UI copy: Spanish (labels, buttons, messages)
- Backend: English (table/column names, code identifiers, API keys)

## 📤 Reporting & Exports
- Loans: `reports.php?export=loans`
- Overdue: `reports.php?export=overdue`
- Due soon (N days): `reports.php?export=due_soon&days=3`
- Users (admin): `reports.php?export=users`
- Books (admin): `reports.php?export=books`

## 🚢 Deploy (Apache2 on Debian)
```bash
sudo apt update && sudo apt install -y apache2 php php-mysqli
sudo a2enmod rewrite
```
VirtualHost (example):
```apache
<VirtualHost *:80>
    ServerName your-domain
    DocumentRoot /var/www/html
    <Directory /var/www/html/your-folder>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Restart Apache:
```bash
sudo systemctl restart apache2
```
