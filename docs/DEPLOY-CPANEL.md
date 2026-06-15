# Deploying bildfie to cPanel

This is the full, beginner-friendly path from GitHub to a live site you can open
on your **phone and your PC**. Do it once; after that, updates take ~2 minutes.

There are two ways to deploy. **Method A (ZIP upload)** is recommended and matches
"build a ZIP to update the site". **Method B (cPanel Git)** is an optional alternative.

---

## One-time setup

### 1. Set the PHP version
cPanel → **MultiPHP Manager** (or "Select PHP Version") → set your domain to **PHP 8.3**.
Enable these extensions: `mbstring`, `bcmath`, `pdo_mysql`, `intl`, `gd`, `zip`,
`fileinfo`, `curl`, `openssl`.

### 2. Create the database
cPanel → **MySQL® Databases**:
1. Create a database, e.g. `bildfie` (cPanel shows the full name, e.g. `cpaneluser_bildfie`).
2. Create a database user with a strong password.
3. **Add the user to the database** and grant **ALL PRIVILEGES**.
4. Note the full database name, user name, and password — you'll paste them into `.env`.

### 3. Decide where the app lives
Laravel serves from its `public/` folder. Best practice is to keep the rest of the
app **outside** the web root:

- **Recommended:** put the app in `/home/CPANEL_USER/bildfie` and point your domain's
  **Document Root** to `/home/CPANEL_USER/bildfie/public`
  (cPanel → **Domains** → your domain → edit Document Root; or set it when creating a subdomain like `app.your-domain.co.ke`).
- If you cannot change the Document Root, see "Fixed public_html" at the bottom.

---

## Method A — Deploy by ZIP (recommended)

### First deploy
1. **Get the ZIP.** In GitHub → **Actions** → the latest "Build cPanel deploy ZIP"
   run → **Artifacts** → download `bildfie-cpanel-zip`. Inside is `bildfie-cpanel.zip`.
2. **Upload.** cPanel → **File Manager** → go to `/home/CPANEL_USER` → **Upload** the ZIP.
3. **Extract** it there. You now have `/home/CPANEL_USER/bildfie`.
4. **Create the live `.env`.** In `bildfie/`, copy `.env.example` to `.env` (File Manager
   → select → Copy/Rename), then **Edit** `.env` and fill in:
   - `APP_URL=https://your-domain.co.ke`
   - the `DB_*` values from step 2
   - M-Pesa, SMS, and mail values when you have them
5. **Generate the app key + run migrations.** cPanel → **Terminal** (or SSH), then:
   ```bash
   cd ~/bildfie
   php artisan key:generate --force
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   ```
   No Terminal on your plan? See "No SSH/Terminal" below.
6. **Point the domain** to `~/bildfie/public` (step 3) if you haven't.
7. Visit `https://your-domain.co.ke/health` → you should see
   `{"status":"ok","database":"ok"}`. Then open the homepage on your phone and PC. ✅

### Every update after that
1. Push your changes (or merge to `main`). The GitHub Action rebuilds the ZIP.
2. Download the new `bildfie-cpanel.zip`, upload, and extract over `~/bildfie`
   (File Manager overwrites changed files; your `.env` is **not** in the ZIP, so it stays).
3. In Terminal: `cd ~/bildfie && php artisan migrate --force && php artisan config:cache`.

> Tip: keep a backup of `.env` somewhere safe — it is never in the ZIP or GitHub.

---

## Method B — cPanel Git Version Control (optional)

1. cPanel → **Git Version Control** → **Create** → clone URL of this GitHub repo,
   path e.g. `/home/CPANEL_USER/bildfie-src`.
2. Edit `.cpanel.yml` `DEPLOYPATH` to your app path, commit, push.
3. In cPanel click **Update from Remote** → **Deploy HEAD Commit**.
4. First time only, in **Terminal** inside the deploy path:
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env && php artisan key:generate --force
   # edit .env, then:
   php artisan migrate --force && php artisan storage:link && php artisan config:cache
   ```
Vendor libraries are not in Git, so this method needs Composer available on your host.

---

## Edge cases

### No SSH/Terminal on your plan
Run the same setup through the browser:
- **APP_KEY:** generate one locally with `php artisan key:generate --show` and paste
  the `base64:...` value into `.env` as `APP_KEY=`.
- **Migrations:** create a temporary protected route or use cPanel's "Cron Jobs" to run
  `php ~/bildfie/artisan migrate --force` once. Ask and I'll add a guarded web installer.

### Fixed public_html (cannot change Document Root)
Put the app in `/home/CPANEL_USER/bildfie` and copy the **contents** of
`bildfie/public/` into `public_html/`. Then edit `public_html/index.php` so the two
`require`/`$app` paths point to `../bildfie/...` instead of `../`. Ask me and I'll
generate that adjusted `index.php` for your exact paths.

### HTTPS / SSL
cPanel → **SSL/TLS Status** → run **AutoSSL** so `https://` works (free). Keep
`SESSION_SECURE_COOKIE=true` in `.env` once HTTPS is on.

---

## Quick verification checklist
- [ ] `https://your-domain/health` returns `database: ok`
- [ ] Homepage loads on phone **and** PC
- [ ] `APP_DEBUG=false` and `APP_ENV=production` in the live `.env`
- [ ] `.env` is **not** present in GitHub
- [ ] AutoSSL padlock is green
