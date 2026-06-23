<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$email = trim($_POST['email'] ?? '');
$pass  = (string) ($_POST['password'] ?? '');
$to    = $_POST['redirect'] ?? '/';
if ($to === '' || $to[0] !== '/') $to = '/';   // index is the homepage, not the dashboard; internal redirects only

$r = user_login($email, $pass);
if ($r['ok']) {
    header('Location: ' . $to);
    exit;
}
$qs = 'error=' . urlencode($r['error']) . '&redirect=' . urlencode($to);
if (!empty($r['unverified'])) $qs .= '&unverified=1&email=' . urlencode($r['email'] ?? $email);
header('Location: /pages/auth/login.php?' . $qs);
exit;
