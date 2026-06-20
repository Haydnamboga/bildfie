<?php
/**
 * COPY this file to  config/config.local.php  on each machine/server and fill in
 * real values. config.local.php is git-ignored and must NEVER be committed.
 *
 * Locally (XAMPP) you usually don't need this file — the defaults in db.php
 * (127.0.0.1 / root / no password / bildfie) already work.
 */

define('APP_ENV', 'production');          // 'production' on a live server, 'local' for dev

// ── cPanel MySQL (names are prefixed with your cPanel username) ──
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpuser_bildfie');
define('DB_USER', 'cpuser_bildfie');
define('DB_PASS', 'your-strong-db-password');
define('DB_PORT', 3306);

// Public site URL, no trailing slash. Leave '' when served from the domain root.
define('BASE_URL', '');

// ── Email sending (SMTP) ─────────────────────────────────────────
// In cPanel: Email Accounts → create e.g. noreply@bildfie.com, then use:
//   Host  = mail.yourdomain.com   (cPanel → "Connect Devices" shows the exact host)
//   Port  = 465 (SSL)  or  587 (TLS)
//   User  = the FULL email address      Pass = that email account's password
// Leave these commented out to use plain PHP mail() instead.
define('MAIL_TRANSPORT', 'smtp');
define('SMTP_HOST',   'mail.bildfie.com');
define('SMTP_PORT',   465);
define('SMTP_SECURE', 'ssl');            // 'ssl' for 465, 'tls' for 587
define('SMTP_USER',   'noreply@bildfie.com');
define('SMTP_PASS',   'the-email-account-password');
define('MAIL_FROM',      'noreply@bildfie.com');
define('MAIL_FROM_NAME', 'bildfie');
