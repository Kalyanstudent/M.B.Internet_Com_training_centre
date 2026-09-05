# MB Internet And Digital Studio | Mobile & Gift House

**Proprietor & Founder:** Mukesh Bhattacharya ("MB")  
**Facebook:** [https://www.facebook.com/MBIDS/photos_by](https://www.facebook.com/MBIDS/photos_by)  
**Production Stack:** XAMPP (Apache 2.4+, PHP 8.2+, MariaDB/MySQL 8.0+), Bootstrap 5.3.3, Vanilla JavaScript, Chart.js, Razorpay

---

## 🎯 Quick XAMPP Setup & Launch Instructions

### Step 1: Start XAMPP Control Panel
1. Open **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.

### Step 2: Project Deployment Location
The project is deployed in your XAMPP web root directory:
- Primary Path: `C:\xampp\htdocs\skillpoint-portal\` and `E:\xampp\htdocs\skillpoint-portal\`
- Subfolder Path: `E:\xampp\htdocs\Mukesh_da_project\skillpoint-portal\`

### Step 3: MySQL Database Configuration & Schema
1. Database Name: `skillpoint_portal`
2. Host: `localhost` (Port: `3306`)
3. User: `root`
4. Password: ` ` (Empty by default in XAMPP)
5. If creating manually via phpMyAdmin or MySQL CLI:
   ```sql
   CREATE DATABASE skillpoint_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE skillpoint_portal;
   SOURCE database/schema.sql;
   ```

### Step 4: Configure Database & Payment Keys
All configuration settings are centralized in `config/config.php`:
```php
defined('DB_HOST')     or define('DB_HOST', 'localhost');
defined('DB_PORT')     or define('DB_PORT', '3306');
defined('DB_NAME')     or define('DB_NAME', 'skillpoint_portal');
defined('DB_USER')     or define('DB_USER', 'root');
defined('DB_PASSWORD') or define('DB_PASSWORD', '');

// Razorpay API Credentials (Test / Live)
defined('RAZORPAY_KEY_ID')     or define('RAZORPAY_KEY_ID', 'rzp_test_demo12345678');
defined('RAZORPAY_KEY_SECRET') or define('RAZORPAY_KEY_SECRET', 'test_secret_demo12345678');
```

### Step 5: Administrator Account Setup
Run the CLI / Web script to initialize or reset administrator credentials:
```bash
php database/create-admin.php
```
- **Admin Email**: `admin@mbinternet.com`
- **Admin Password**: `admin123`
- **Demo Student Account**: `user@mbinternet.com` / `student123`

### Step 6: Access the Website in Your Browser
Open:
- **[http://localhost/skillpoint-portal/](http://localhost/skillpoint-portal/)**
or:
- **[http://localhost/Mukesh_da_project/skillpoint-portal/](http://localhost/Mukesh_da_project/skillpoint-portal/)**

---

## 🛡️ Role-Based Security & Permissions Verification

1. **Normal Student / User**:
   - Can browse public courses, tax services, digital services, and catalog.
   - Can register and sign in to access Student Dashboard (`dashboard.html` / `/dashboard`).
   - Access to Admin Dashboard (`/admin/index.html` or `/admin/dashboard`) or Admin APIs (`/api/admin/*`) returns **HTTP 403 Forbidden**.
2. **Administrator (Mukesh Bhattacharya)**:
   - Signs in to access the Admin Console (`admin/index.html`).
   - Access to the normal student dashboard returns **HTTP 403 Forbidden** (Admin is isolated and never treated as a normal student).
   - Manages courses, admissions, payment transactions, student accounts, tax requests with internal notes, and document vault.
3. **Protected Document Storage**:
   - Files uploaded during tax requests are saved in `storage/documents/`.
   - Direct browser access to `storage/documents/file.pdf` is blocked with **HTTP 403 Forbidden** via `.htaccess`.
   - Files are served securely only through authenticated PHP endpoints: `api/documents/view.php` and `api/documents/download.php`.

---

## 🧭 Tested URLs & Apache Routes

| Route | Destination File | Status |
| :--- | :--- | :--- |
| `http://localhost/skillpoint-portal/` | `index.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/index.php` | `index.php` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/courses` | `courses.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/login` | `login.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/register` | `login.html?tab=register` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/dashboard` | `dashboard.html` | ✅ HTTP 200 OK (Auth Guard) |
| `http://localhost/skillpoint-portal/admin/dashboard` | `admin/index.html` | ✅ HTTP 200 OK (Admin Guard) |
| `http://localhost/skillpoint-portal/tax-services` | `tax-services.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/digital-services` | `digital-services.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/mobile-gift-house` | `mobile-gift-house.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/important-links` | `important-links.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/about` | `about.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/contact` | `contact.html` | ✅ HTTP 200 OK |
| `http://localhost/skillpoint-portal/api/courses/list.php` | REST API | ✅ HTTP 200 JSON |
| `http://localhost/skillpoint-portal/api/admin/dashboard.php` | Admin REST API | ✅ HTTP 200 (Admin) / 403 (User) |
