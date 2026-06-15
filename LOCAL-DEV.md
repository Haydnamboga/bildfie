# Running bildfie locally (Windows + XAMPP)

A local copy of the site on your PC for safe testing before publishing to
bildfie.com. You only set this up once.

## One-time setup

1. **Get the code** (run once, in Git Bash or Command Prompt):
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/Haydnamboga/bildfie.git
   cd bildfie
   git checkout claude/bildfie-multi-device-hosting-m477od
   ```

2. **Add your data file:** copy `remissio_bildfie.sql` into this project folder
   (`C:\xampp\htdocs\bildfie`).

3. **Start MySQL:** open the **XAMPP Control Panel** and click **Start** on **MySQL**.

4. **Import the database:** double-click **`import-db.bat`**. It creates a local
   `bildfie` database and loads your data.

## Every time you want to work locally

1. XAMPP Control Panel → **Start** MySQL (Apache too if you want phpMyAdmin).
2. Double-click **`start-local.bat`** → it opens **http://localhost:8000**.
3. Close that window to stop the server.

> Local uses XAMPP defaults automatically: database `bildfie`, user `root`, no
> password. No config file needed. (Errors show on screen locally, which is what
> you want while developing.)

## The update cycle (local → GitHub → live)

- **Claude** pushes changes to GitHub.
- **You** pull them to your PC and preview locally:
  ```bash
  cd C:/xampp/htdocs/bildfie
  git pull
  ```
  then refresh http://localhost:8000
- When happy, **deploy to bildfie.com** (one-click cPanel Git pull — set up separately).

## Notes
- `config/config.local.php` is only for the **live server** and is never committed.
- The PHP built-in server ignores `.htaccess` — that's expected and fine locally.
