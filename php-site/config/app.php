<?php
// Machine-specific overrides (DB creds, BASE_URL, APP_ENV) — never committed.
if (is_file(__DIR__ . '/config.local.php')) require_once __DIR__ . '/config.local.php';

define('APP_NAME',    'bildfie');
define('APP_TAG',     'Build Smarter. Connect Better.');
define('APP_VERSION', '1.0.0');
if (!defined('BASE_URL'))   define('BASE_URL',   '');          // empty = served from the domain root
if (!defined('ASSETS_URL')) define('ASSETS_URL', '/assets');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', '/uploads');
define('UPLOAD_PATH', __DIR__ . '/../uploads');
// Absolute site URL (used to build links in emails). Auto-derived from the request; override in config.local.php if needed.
if (!defined('APP_URL')) {
    $bf_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    define('APP_URL', rtrim((defined('BASE_URL') && BASE_URL) ? BASE_URL : (($bf_https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/'));
}
if (!defined('CURRENCY')) define('CURRENCY', 'KES');
if (!defined('TIMEZONE')) define('TIMEZONE', 'Africa/Nairobi');

date_default_timezone_set(TIMEZONE);

// Harden the session cookie automatically when served over HTTPS.
$bf_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('bildfie_session');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $bf_secure]);
    session_start();
}

// Performance: defer off-screen images so pages paint faster (adds lazy-load + async decode
// to any <img> that doesn't already set them). One filter covers the whole public site.
ob_start(function ($html) {
    return preg_replace('/<img (?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async" ', $html);
});
