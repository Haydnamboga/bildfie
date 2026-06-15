<?php
require_once __DIR__ . '/config/admin.php';
if (admin_logged_in()) { header('Location: /admin/index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = (string) ($_POST['password'] ?? '');
    if (admin_attempt($email, $pass)) {
        header('Location: /admin/index.php');
        exit;
    }
    $err = 'Invalid email or password.';
}
$page_title = 'Sign in';
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-login">
  <div class="adm-login-card">

    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center gap-2 mb-2" style="font-size:22px;font-weight:800;letter-spacing:-.04em;color:var(--ink);">
        <i class="bi bi-building-fill-up" style="color:#1e3a5f;"></i> bildfie
      </div>
      <div><span style="font-size:9px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;background:#c0392b;color:#fff;padding:3px 9px;border-radius:5px;">Back-office console</span></div>
    </div>

    <h1 style="font-size:19px;font-weight:800;color:var(--ink);text-align:center;margin:0 0 4px;">Staff sign in</h1>
    <p style="font-size:12.5px;color:var(--ink-3);text-align:center;margin:0 0 18px;">Authorised personnel only. All access is logged.</p>

    <?php if ($err): ?>
    <div style="background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;border-radius:9px;padding:10px 13px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
      <i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($err) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Work email</label>
      <div class="adm-login-field">
        <i class="bi bi-envelope"></i>
        <input type="email" name="email" placeholder="you@bildfie.com" autofocus>
      </div>
      <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Password</label>
      <div class="adm-login-field">
        <i class="bi bi-lock"></i>
        <input type="password" name="password" placeholder="••••••••">
      </div>
      <div class="d-flex align-items-center justify-content-between mb-3" style="font-size:12px;">
        <label style="display:flex;align-items:center;gap:7px;color:var(--ink-3);cursor:pointer;"><input type="checkbox" style="width:14px;height:14px;accent-color:#1e3a5f;"> Trust this device</label>
        <a href="#" style="color:#c0392b;font-weight:700;text-decoration:none;">Forgot password?</a>
      </div>
      <button type="submit" class="adm-login-btn"><i class="bi bi-shield-lock me-1"></i> Sign in securely</button>
    </form>

    <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--line);text-align:center;">
      <div style="font-size:11px;color:var(--ink-4);line-height:1.6;"><i class="bi bi-shield-lock"></i> Secured by bildfie · accounts are managed in the database.</div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
