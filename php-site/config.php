<?php
/* ============================================================
 *  bildfie — configuration
 *  EDIT the database settings below to match your hosting.
 *  (In cPanel: see "MySQL Databases" for the exact names.)
 * ============================================================ */

// ---- Database (MySQL) ----
define('DB_HOST', 'localhost');          // usually 'localhost' on cPanel
define('DB_NAME', 'bildfie');            // your database name (e.g. cpaneluser_bildfie)
define('DB_USER', 'root');               // your database user (e.g. cpaneluser_bildfie)
define('DB_PASS', '');                   // your database password

// ---- Site ----
define('SITE_NAME', 'bildfie');
define('SITE_TAGLINE', 'Hire trusted construction & trade professionals');

// ---- Security ----
// Change this to any long random string (used for CSRF / sessions).
define('APP_SECRET', 'change-this-to-a-long-random-string-please');

// Show DB errors on screen? Set to false on the live site.
define('DEBUG', true);
