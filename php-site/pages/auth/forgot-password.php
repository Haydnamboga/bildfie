<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
if (is_logged_in()) { header('Location: /'); exit; }

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    password_reset_request(trim($_POST['email'] ?? ''));
    $sent = true;   // always show the same message (no account enumeration)
}
$page_title = 'Forgot password';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-login">
  <span class="bf-login-blob navy"></span>
  <span class="bf-login-blob red"></span>
  <span class="bf-login-blob amber"></span>
  <div class="bf-login-card">
    <div class="text-center mb-4">
      <a href="/" class="bf-brand text-decoration-none d-inline-flex align-items-center mb-1" style="font-size:24px;"><i class="bi bi-building-fill-up"></i>bildfie</a>
      <div style="font-size:12px;color:var(--ink-3);"><?= APP_TAG ?></div>
    </div>
    <div class="bf-login-panel">
      <?php if ($sent): ?>
        <div style="text-align:center;">
          <div style="width:56px;height:56px;border-radius:50%;background:#eaf0f6;color:#1e3a5f;font-size:26px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><i class="bi bi-envelope-check"></i></div>
          <h1 style="font-size:21px;font-weight:800;color:var(--ink);margin:0 0 6px;">Check your email</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0 0 18px;line-height:1.6;">If an account exists for that address, we've sent a link to reset your password. It expires in 1 hour — check your spam folder too.</p>
          <a href="/pages/auth/login.php" class="bf-login-link">Back to sign in</a>
        </div>
      <?php else: ?>
        <div class="bf-section-eyebrow mb-2">Reset</div>
        <h1 style="font-size:23px;font-weight:800;color:var(--ink);margin:0 0 5px;">Forgot your password?</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0 0 20px;">Enter the email you registered with and we'll send you a reset link.</p>
        <form method="post">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Email address</label>
          <div class="bf-login-field"><i class="bi bi-envelope"></i><input type="email" name="email" placeholder="you@email.com" required autofocus></div>
          <button type="submit" class="bf-login-btn" style="margin-top:16px;"><i class="bi bi-send"></i>Send reset link</button>
        </form>
        <div style="border-top:1px solid var(--line);margin:20px 0 0;padding-top:18px;text-align:center;">
          <span style="font-size:13px;color:var(--ink-3);">Remembered it? </span>
          <a href="/pages/auth/login.php" class="bf-login-link" style="font-size:13px;">Sign in</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
