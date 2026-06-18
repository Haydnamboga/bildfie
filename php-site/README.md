# bildfie — PHP + MySQL website

A complete construction/trade marketplace that runs on **standard Apache + PHP + MySQL**
shared hosting (cPanel, Hostinger, Namecheap, etc.). No Node.js, no build step.

## Features
- Sign up / log in (clients **and** trade professionals), secure password hashing
- Professional profiles + marketplace with search & filters (trade, location)
- Job board: post jobs, browse, filter
- Proposals: pros bid on jobs; clients shortlist / accept / reject
- Dashboard, profile editor, portfolio & reviews display
- Admin panel (super admin): stats, manage users & roles
- CSRF protection, prepared statements (no SQL injection), output escaping (no XSS)

---

## Deploy in 5 steps (cPanel)

### 1. Upload the files
Upload **everything inside this folder** into your `public_html`
(or a subfolder like `public_html/bildfie`). You can upload the `.zip`
via cPanel **File Manager → Upload**, then **Extract**.

### 2. Create the database
In cPanel → **MySQL Databases**:
- Create a database (e.g. `youruser_bildfie`)
- Create a user, set a password, and **add the user to the database** with **All Privileges**

### 3. Import the tables
cPanel → **phpMyAdmin** → select your database → **Import** tab →
choose **`database.sql`** → **Go**.
This creates all tables and a super-admin account.

### 4. Configure
Edit **`config.php`** and fill in your database details:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'youruser_bildfie');
define('DB_USER', 'youruser_bildfie');
define('DB_PASS', 'your-password');
```
Also change `APP_SECRET` to any long random string, and set `DEBUG` to `false`.

### 5. Open your site
Visit your domain. Log in as the admin:
- **Email:** `admin@bildfie.com`
- **Password:** `Admin@Bildfie2026`  ← change it after logging in!

---

## Local testing (optional)
```bash
php -S localhost:8080      # from inside this folder
# then open http://localhost:8080
```
(Requires a local MySQL/MariaDB and matching settings in config.php.)

## File overview
```
config.php            <- your DB settings (edit this)
database.sql          <- import into phpMyAdmin
index.php             <- homepage
register/login/logout <- authentication
dashboard.php         <- logged-in home
marketplace.php       <- find professionals
professional.php      <- public pro profile
jobs.php / job.php     <- job board + detail/proposals
post-job.php          <- create a job
proposals.php         <- pro's submitted proposals
profile.php           <- edit your profile
admin/                <- admin panel (stats, users)
includes/             <- shared code (db, helpers, header, footer)
assets/css/style.css  <- styling
```
