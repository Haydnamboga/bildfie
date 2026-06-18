<?php
/* MySQL connection (mysqli) — shared by every page. */
require_once __DIR__ . '/../config.php';

mysqli_report(MYSQLI_REPORT_OFF); // we handle errors ourselves

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_errno) {
    http_response_code(500);
    if (defined('DEBUG') && DEBUG) {
        die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error)
            . '<br><br>Check your settings in <strong>config.php</strong>.');
    }
    die('The site is temporarily unavailable. Please try again later.');
}
$mysqli->set_charset('utf8mb4');
