<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$token  = $_GET['token'] ?? '';
$resent = false;
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend') {
    resend_verification(trim($_POST['email'] ?? ''));
    $resent = true;
} elseif ($token !== '') {
    $result = email_verify($token);
}
$page_title = 'Verify email';
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
      <?php if ($result && $result['ok']): ?>
        <div style="text-align:center;">
          <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;color:#16a34a;font-size:28px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><i class="bi bi-check-lg"></i></div>
          <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 6px;">Email verified!</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0 0 20px;">Your account is now active. You can sign in.</p>
          <a href="/pages/auth/login.php?verified=1" class="bf-login-btn" style="text-decoration:none;"><i class="bi bi-box-arrow-in-right"></i>Sign in</a>
        </div>
      <?php elseif ($resent): ?>
        <div style="text-align:center;">
          <div style="width:56px;height:56px;border-radius:50%;background:#eaf0f6;color:#1e3a5f;font-size:26px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><i class="bi bi-envelope-check"></i></div>
          <h1 style="font-size:21px;font-weight:800;color:var(--ink);margin:0 0 6px;">Check your inbox</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0 0 18px;">If that email is registered and not yet verified, a fresh verification link is on its way (check spam too).</p>
          <a href="/pages/auth/login.php" class="bf-login-link">Back to sign in</a>
        </div>
      <?php else: ?>
        <div class="bf-section-eyebrow mb-2">Verify email</div>
        <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 5px;">Link didn't work</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0 0 16px;"><?= htmlspecialchars($result['error'] ?? 'No verification token was provided.') ?></p>
        <form method="post">
          <input type="hidden" name="action" value="resend">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Your email</label>
          <div class="bf-login-field"><i class="bi bi-envelope"></i><input type="email" name="email" placeholder="you@email.com" required autofocus></div>
          <button type="submit" class="bf-login-btn" style="margin-top:14px;"><i class="bi bi-arrow-repeat"></i>Resend verification link</button>
        </form>
        <div style="border-top:1px solid var(--line);margin:18px 0 0;padding-top:16px;text-align:center;">
          <a href="/pages/auth/login.php" class="bf-login-link" style="font-size:13px;">Back to sign in</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
