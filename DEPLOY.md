# Deploying bildfie to cPanel

bildfie is a plain PHP 8 + MySQL app (no build step, no framework) — ideal for cPanel.
It serves `.php` files directly, so it must live at the **domain root** (`public_html`).

---

## 0. One-time prep (already done in the repo)
- `config/config.local.php` holds real credentials and is **git-ignored** (see `config/config.sample.php`).
- `.htaccess` files force HTTPS, block `config/` & `database/` from the web, and stop scripts running from `uploads/`.
- Errors are hidden automatically when `APP_ENV = production`.

---

## 1. Create the database (cPanel → MySQL® Databases)
1. **Create New Database** → e.g. `bildfie` → cPanel makes it `cpuser_bildfie`.
2. **Add New User** → e.g. `bildfie` → `cpuser_bildfie`, with a **strong password**.
3. **Add User To Database** → grant **ALL PRIVILEGES**.
4. Note the final names: host `localhost`, db `cpuser_bildfie`, user `cpuser_bildfie`.

## 2. Export your local data, import to cPanel
Locally (Windows PowerShell), dump the database:
```
& "C:\xampp\mysql\bin\mysqldump.exe" -u root bildfie > bildfie.sql
```
Then in cPanel → **phpMyAdmin** → select `cpuser_bildfie` → **Import** → upload `bildfie.sql`.
> This carries your schema **and** data (incl. your owner login). For a clean launch instead,
> import nothing and run migrations (step 5) + `php database/seed.php` to recreate only the owner.

## 3. Upload the files
1. Locally, zip the project folder (exclude `config/config.local.php`, `*.log`).
2. cPanel → **File Manager** → `public_html` → **Upload** the zip → **Extract**.
   (Or use FTP/SFTP, or Git — see "Keep enhancing" below.)

## 4. Create `config/config.local.php` on the server
Copy `config/config.sample.php` → `config/config.local.php` and fill in:
```php
<?php
define('APP_ENV', 'production');
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpuser_bildfie');
define('DB_USER', 'cpuser_bildfie');
define('DB_PASS', 'your-strong-db-password');
define('DB_PORT', 3306);
define('BASE_URL', '');
```

## 5. PHP version, extensions, permissions
- **MultiPHP Manager** → set the domain to **PHP 8.1+**.
- **Select PHP Version → Extensions** → ensure `mysqli` and `mbstring` are on.
- File Manager → set `uploads/` (and `uploads/staff`, `uploads/members`) to **0755** and writable.

## 6. SSL / HTTPS
- cPanel → **SSL/TLS Status** → run **AutoSSL** (Let's Encrypt).
- The root `.htaccess` then auto-redirects http → https.

## 7. Run / update migrations (CLI — not web)
`database/` is blocked from the browser on purpose. Run migrations from **cPanel → Terminal**
(or a one-off **Cron Job**):
```
cd ~/public_html && php database/migrate.php
```
If your host has no CLI/SSH, paste each new `database/migrations/00xx_*.sql` into phpMyAdmin → SQL.

## 8. Go-live checklist
- [ ] Home page loads over **https**.
- [ ] `/admin` login works (then **change the owner password**).
- [ ] A test member sign-up writes to the DB.
- [ ] Visiting `/config/db.php` or `/database/migrate.php` returns **403** (blocked).
- [ ] No PHP errors shown to visitors (check cPanel → **Errors** / `error_log`).

---

## Keep enhancing after it's live

**Use Git as the single source of truth.**
1. `git init` locally, push to a private GitHub repo (`config.local.php` is ignored).
2. On cPanel use **Git™ Version Control** → clone the repo into `public_html` (or a folder),
   then deploy future changes with **Pull or Deploy** (or `git pull` in Terminal).
3. Each feature = code + a **new numbered migration** (`0009_*.sql`, `0010_*.sql`).

**Safe release routine for every update:**
1. Build & test **locally** on XAMPP.
2. **Back up the live DB** (cPanel → phpMyAdmin → Export, or a nightly cron `mysqldump`).
3. Deploy code (`git pull` / upload changed files).
4. Run `php database/migrate.php` on the server (additive `IF NOT EXISTS` migrations are safe).
5. Smoke-test the affected pages on the live site.

**Good habits:**
- Keep a **staging subdomain** (e.g. `staging.yourdomain.com`) with its own DB to test before production.
- **Never run `seed_*.php` on production** — those are demo data. Only `migrate.php`.
- Bump `APP_VERSION` in `config/app.php` each release; schedule **automated backups**.
- Watch `error_log` after each deploy.
