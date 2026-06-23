<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$err   = '';
$done  = false;
$valid = $token !== '' ? auth_token_user($token, 'reset') : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = (string) ($_POST['password'] ?? '');
    $p2 = (string) ($_POST['password2'] ?? '');
    if ($p1 !== $p2) {
        $err = 'The two passwords do not match.';
    } else {
        $r = password_reset($token, $p1);
        if ($r['ok']) { $done = true; } else { $err = $r['error']; $valid = null; }
    }
}
$page_title = 'Reset password';
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
      <?php if ($done): ?>
        <div style="text-align:center;">
          <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;color:#16a34a;font-size:28px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><i class="bi bi-check-lg"></i></div>
          <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 6px;">Password updated</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0 0 20px;">You can now sign in with your new password.</p>
          <a href="/pages/auth/login.php?reset=1" class="bf-login-btn" style="text-decoration:none;"><i class="bi bi-box-arrow-in-right"></i>Sign in</a>
        </div>
      <?php elseif (!$valid): ?>
        <div class="bf-section-eyebrow mb-2">Reset</div>
        <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 5px;">Link expired or invalid</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0 0 18px;"><?= htmlspecialchars($err ?: 'This password reset link is no longer valid. Reset links expire after 1 hour.') ?></p>
        <a href="/pages/auth/forgot-password.php" class="bf-login-btn" style="text-decoration:none;"><i class="bi bi-arrow-repeat"></i>Request a new link</a>
      <?php else: ?>
        <div class="bf-section-eyebrow mb-2">Reset</div>
        <h1 style="font-size:23px;font-weight:800;color:var(--ink);margin:0 0 5px;">Choose a new password</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0 0 18px;">for <strong style="color:var(--ink);"><?= htmlspecialchars($valid['email']) ?></strong></p>
        <?php if ($err): ?>
        <div class="d-flex align-items-center gap-2 mb-3" style="background:#fef2f2;border:1px solid #fecaca;color:#c0392b;border-radius:10px;padding:10px 14px;font-size:12.5px;"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
        <form method="post">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">New password</label>
            <div class="bf-login-field"><i class="bi bi-lock"></i><input type="password" name="password" id="rp1" minlength="6" placeholder="At least 6 characters" required autofocus>
              <i class="bi bi-eye" style="cursor:pointer;" onclick="(function(){var p=document.getElementById('rp1');p.type=p.type==='password'?'text':'password';})()"></i></div>
          </div>
          <div class="mb-2">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Confirm new password</label>
            <div class="bf-login-field"><i class="bi bi-lock-fill"></i><input type="password" name="password2" minlength="6" placeholder="Re-enter password" required></div>
          </div>
          <button type="submit" class="bf-login-btn" style="margin-top:14px;"><i class="bi bi-check-lg"></i>Update password</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
