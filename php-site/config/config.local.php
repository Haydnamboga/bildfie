<?php
// bildfie — server configuration (this file is NOT in git).
// Fill in DB_PASS below, then save. Everything else is ready for bildfie.com.

define('APP_ENV', 'local');

define('DB_HOST', 'localhost');
define('DB_NAME', 'remissio_bildfie2');
define('DB_USER', 'remissio_bildfie2');
define('DB_PASS', 'Haydn1990.');   // <-- your MySQL password
define('DB_PORT', 3306);

define('BASE_URL', '');   // served from the domain root

// ── Email (optional now; fill when ready) ──
define('MAIL_TRANSPORT', 'smtp');
define('SMTP_HOST',   'mail.bildfie.com');
define('SMTP_PORT',   465);
define('SMTP_SECURE', 'ssl');
define('SMTP_USER',   'noreply@bildfie.com');
define('SMTP_PASS',   'Haydn1990.');
define('MAIL_FROM',      'noreply@bildfie.com');
define('MAIL_FROM_NAME', 'Bildfie');