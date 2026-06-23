<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

// Unified membership — no client/professional split. Every member can both
// seek services and offer them; reputation is built on the profile.
$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name']  ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$pass  = (string) ($_POST['password'] ?? '');
$name  = trim("$first $last");

$r = user_register($name, $email, $pass, $phone ?: null);
if ($r['ok']) {
    // account created but not yet verified — send them to sign-in with a "check your email" notice
    header('Location: /pages/auth/login.php?verify_sent=1&email=' . urlencode($r['email'] ?? $email));
    exit;
}
header('Location: /pages/auth/register.php?error=' . urlencode($r['error']) . '&first_name=' . urlencode($first) . '&last_name=' . urlencode($last) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone));
exit;
