<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
if (is_logged_in()) { header('Location: /'); exit; }
$page_title = 'Sign In';
$err         = htmlspecialchars($_GET['error'] ?? '');
$unverified  = !empty($_GET['unverified']);
$verifyEmail = htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES);
$verifySent  = !empty($_GET['verify_sent']);
$verified    = !empty($_GET['verified']);
$resetOk     = !empty($_GET['reset']);
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>

<div class="bf-login">
  <span class="bf-login-blob navy"></span>
  <span class="bf-login-blob red"></span>
  <span class="bf-login-blob amber"></span>

  <div class="bf-login-card">

    <!-- Brand -->
    <div class="text-center mb-4">
      <a href="/" class="bf-brand text-decoration-none d-inline-flex align-items-center mb-1" style="font-size:24px;">
        <i class="bi bi-building-fill-up"></i>bildfie
      </a>
      <div style="font-size:12px;color:var(--ink-3);"><?= APP_TAG ?></div>
    </div>

    <!-- Panel -->
    <div class="bf-login-panel">
      <div class="bf-section-eyebrow mb-2">Sign In</div>
      <h1 style="font-size:24px;font-weight:800;color:var(--ink);letter-spacing:-.02em;margin:0 0 5px;">Welcome back</h1>
      <p style="font-size:13px;color:var(--ink-3);margin:0 0 22px;">Sign in to your account to continue.</p>

      <?php if ($err): ?>
      <div class="d-flex align-items-center gap-2 mb-3"
           style="background:#fef2f2;border:1px solid #fecaca;color:#c0392b;border-radius:10px;padding:10px 14px;font-size:12.5px;">
        <i class="bi bi-exclamation-circle-fill"></i><?= $err ?>
      </div>
      <?php endif; ?>

      <?php if ($unverified): ?>
      <form method="post" action="/pages/auth/verify-email.php" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:10px;padding:11px 14px;font-size:12.5px;margin-bottom:14px;line-height:1.5;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><i class="bi bi-envelope-exclamation"></i> Your email isn't verified yet.</div>
        <input type="hidden" name="action" value="resend">
        <input type="hidden" name="email" value="<?= $verifyEmail ?>">
        <button type="submit" style="background:none;border:none;padding:0;color:#92400e;font-weight:800;text-decoration:underline;cursor:pointer;font-size:12.5px;">Resend verification link</button>
      </form>
      <?php elseif ($verifySent): ?>
      <div style="background:#eaf0f6;border:1px solid #d6e2ee;color:#1e3a5f;border-radius:10px;padding:11px 14px;font-size:12.5px;margin-bottom:14px;line-height:1.55;">
        <i class="bi bi-envelope-check"></i> Account created! We've emailed a verification link<?= $verifyEmail ? ' to <strong>'.$verifyEmail.'</strong>' : '' ?> — click it to activate your account, then sign in.
      </div>
      <?php elseif ($verified): ?>
      <div class="d-flex align-items-center gap-2 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;"><i class="bi bi-check-circle-fill"></i> Email verified — you can sign in now.</div>
      <?php elseif ($resetOk): ?>
      <div class="d-flex align-items-center gap-2 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;"><i class="bi bi-check-circle-fill"></i> Password updated — sign in with your new password.</div>
      <?php endif; ?>

      <form action="/api/auth/login.php" method="POST">
        <div class="mb-3">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Email address</label>
          <div class="bf-login-field">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" placeholder="you@email.com" required autofocus>
          </div>
        </div>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '/', ENT_QUOTES) ?>">

        <div class="mb-3">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Password</label>
          <div class="bf-login-field">
            <i class="bi bi-lock"></i>
            <input type="password" name="password" id="loginPassword" placeholder="••••••••" required>
            <i class="bi bi-eye" id="togglePw" style="cursor:pointer;" onclick="(function(){var p=document.getElementById('loginPassword');var i=document.getElementById('togglePw');var s=p.type==='password';p.type=s?'text':'password';i.className=s?'bi bi-eye-slash':'bi bi-eye';})()"></i>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <label class="bf-login-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
          <a href="/pages/auth/forgot-password.php" class="bf-login-link">Forgot password?</a>
        </div>

        <button type="submit" class="bf-login-btn">
          <i class="bi bi-box-arrow-in-right"></i>Sign In
        </button>
      </form>

      <div class="bf-login-divider"><span>or continue with</span></div>

      <div class="d-flex gap-2">
        <a href="#" class="bf-login-social"><i class="bi bi-google" style="color:#ea4335;"></i> Google</a>
        <a href="#" class="bf-login-social"><i class="bi bi-apple" style="color:var(--ink);"></i> Apple</a>
      </div>

      <div style="border-top:1px solid var(--line);margin:22px 0 0;padding-top:18px;text-align:center;">
        <span style="font-size:13px;color:var(--ink-3);">New to bildfie? </span>
        <a href="/pages/auth/register.php" class="bf-login-link" style="font-size:13px;">Create an account</a>
      </div>
    </div>

    <p class="text-center mt-4 mb-0" style="font-size:11.5px;color:var(--ink-4);">
      &copy; <?= date('Y') ?> bildfie · <a href="#" style="color:var(--ink-4);">Privacy</a> · <a href="#" style="color:var(--ink-4);">Terms</a>
    </p>

  </div>
</div>

<?php include __DIR__ . '/../../includes/scripts.php'; ?>
