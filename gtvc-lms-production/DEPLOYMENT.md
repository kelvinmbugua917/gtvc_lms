# Gilgil Technical and Vocational College (GTVC) LMS - Deployment Guide

This guide provides step-by-step instructions for deploying the GTVC LMS production PHP/MySQL application on **XAMPP (Local Development)**, **InfinityFree (Free Shared Hosting)**, and **Hostinger (Premium Shared Hosting)**.

---

## 1. Local Deployment (XAMPP / WAMP / MAMP)

### Prerequisites
- XAMPP with PHP 8.0 or higher and MySQL/MariaDB enabled.

### Steps
1. **Copy Files to htdocs**:
   - Extract `gtvc-lms-production` into your XAMPP directory:
     `C:\xampp\htdocs\gtvc-lms` (Windows) or `/opt/lampp/htdocs/gtvc-lms` (Linux).
2. **Start Services**:
   - Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Import Database**:
   - Open browser and navigate to `http://localhost/phpmyadmin`.
   - Create a new database named `gilgil_lms` with Collation `utf8mb4_unicode_ci`.
   - Click **Import** tab -> Choose `database/schema.sql` -> Click **Go**.
   - Click **Import** tab -> Choose `database/seeds.sql` -> Click **Go**.
4. **Configure Environment File**:
   - Copy `.env.example` to `.env` in the root folder (`htdocs/gtvc-lms/.env`).
   - Ensure the database configuration matches:
     ```env
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_NAME=gilgil_lms
     DB_USER=root
     DB_PASS=
     ```
5. **Access Application**:
   - Open `http://localhost/gtvc-lms/public/` in your web browser.
   - Login with `admin@gilgiltvc.ac.ke` / `password123` or `student@gilgiltvc.ac.ke` / `password123`.

---

## 2. InfinityFree Deployment (Free Shared Hosting)

### Prerequisites
- Free InfinityFree Account & Domain/Subdomain created.

### Steps
1. **Create MySQL Database**:
   - Log into InfinityFree Control Panel (VistaPanel).
   - Go to **MySQL Databases**.
   - Create a new database (e.g., `if0_12345678_lms`). Note the database name, host name (e.g. `sql123.epizy.com`), and username.
2. **Import Database via phpMyAdmin**:
   - In VistaPanel, click **phpMyAdmin** next to your created database.
   - Select your database and click **Import**.
   - Upload and execute `database/schema.sql`, then upload and execute `database/seeds.sql`.
3. **Upload Application Files**:
   - Open InfinityFree **File Manager** or connect via FTP (FileZilla).
   - Navigate to the `htdocs` directory of your domain.
   - Upload all contents of `gtvc-lms-production` into `htdocs`.
4. **Configure Environment File**:
   - Create or edit `.env` in `htdocs`:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=http://yourdomain.infinityfreeapp.com

     DB_HOST=sql123.epizy.com
     DB_PORT=3306
     DB_NAME=if0_12345678_lms
     DB_USER=if0_12345678
     DB_PASS=YourAccountPassword
     ```
5. **Set Root Redirection (Public Directory)**:
   - If InfinityFree points directly to `htdocs`, place the contents of `public/` into `htdocs/` or update `.htaccess` in `htdocs` to route traffic into `public/`:
     ```apache
     RewriteEngine On
     RewriteRule ^$ public/ [L]
     RewriteRule (.*) public/$1 [L]
     ```
6. **Test Access**:
   - Visit your domain in your browser.

---

## 3. Hostinger Deployment (cPanel / hPanel)

### Prerequisites
- Hostinger Shared Hosting Plan with Domain & SSL enabled.

### Steps
1. **Create Database in hPanel**:
   - Log into Hostinger hPanel -> Go to **Databases** -> **Management**.
   - Create a new MySQL database and user. Grant all privileges.
2. **Import Database**:
   - Enter **phpMyAdmin** from hPanel.
   - Select your database and import `database/schema.sql`, followed by `database/seeds.sql`.
3. **Upload Files via File Manager or FTP**:
   - Open **File Manager** in hPanel -> Go to `public_html`.
   - Upload all files from `gtvc-lms-production`.
4. **Set Up Domain Root or .htaccess**:
   - In Hostinger hPanel, change your Domain Target Directory to `public_html/public` OR place `.htaccess` in `public_html`:
     ```apache
     <IfModule mod_rewrite.c>
         RewriteEngine On
         RewriteRule ^$ public/ [L]
         RewriteRule (.*) public/$1 [L]
     </IfModule>
     ```
5. **Configure Environment Variables**:
   - Create a `.env` file in `public_html`:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://yourdomain.com

     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_NAME=u123456789_lms
     DB_USER=u123456789_lmsuser
     DB_PASS=StrongDatabasePassword
     ```
6. **Set Permissions**:
   - Ensure `storage/` and `public/uploads/` have write permissions (`755` or `775`).

---

## 4. Security Checklist & Verification

- [x] **No Node.js/npm Required**: Application executes 100% on standard PHP 8.x + MySQL.
- [x] **SQL Injection Protection**: All database queries use PDO Prepared Statements.
- [x] **XSS Sanitization**: All HTML view outputs use `View::e()` escaping.
- [x] **CSRF Protection**: All POST forms include anti-CSRF token verification.
- [x] **Password Hashing**: Passwords stored using standard BCRYPT algorithm.
- [x] **Protected Uploads**: File uploads sanitized with extension whitelisting and randomized filenames.
