<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
if (is_logged_in()) { header('Location: /'); exit; }
$page_title = 'Create Your Account';
$err = htmlspecialchars($_GET['error'] ?? '');
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>

<div class="bf-login">
  <span class="bf-login-blob navy"></span>
  <span class="bf-login-blob red"></span>
  <span class="bf-login-blob amber"></span>

  <div class="bf-login-card" style="max-width:480px;">

    <!-- Brand -->
    <div class="text-center mb-4">
      <a href="/" class="bf-brand text-decoration-none d-inline-flex align-items-center mb-1" style="font-size:24px;">
        <i class="bi bi-building-fill-up"></i>bildfie
      </a>
      <div style="font-size:12px;color:var(--ink-3);">Join Africa's largest construction network.</div>
    </div>

    <!-- Panel -->
    <div class="bf-login-panel">
      <div class="bf-section-eyebrow mb-2">Get Started</div>
      <h1 style="font-size:24px;font-weight:800;color:var(--ink);letter-spacing:-.02em;margin:0 0 5px;">Create your account</h1>
      <p style="font-size:13px;color:var(--ink-3);margin:0 0 18px;">Free to join — no credit card required.</p>

      <!-- One account, both ways -->
      <div style="display:flex;gap:12px;align-items:flex-start;background:#eaf0f6;border:1px solid #d6e2ee;border-radius:12px;padding:13px 15px;margin-bottom:22px;">
        <i class="bi bi-arrow-left-right" style="font-size:17px;color:#1e3a5f;margin-top:1px;flex-shrink:0;"></i>
        <div>
          <div style="font-size:12.5px;font-weight:800;color:#1e3a5f;line-height:1.3;">One account — hire and get hired.</div>
          <div style="font-size:11.5px;color:var(--ink-3);line-height:1.5;margin-top:2px;">Post projects and find talent, or offer your own services. Build your reputation on your profile to win more work.</div>
        </div>
      </div>

      <?php if ($err): ?>
      <div class="d-flex align-items-center gap-2 mb-3" style="background:#fef2f2;border:1px solid #fecaca;color:#c0392b;border-radius:10px;padding:10px 14px;font-size:12.5px;">
        <i class="bi bi-exclamation-circle-fill"></i><?= $err ?>
      </div>
      <?php endif; ?>

      <form action="/api/auth/register.php" method="POST">

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">First name</label>
            <div class="bf-login-field">
              <i class="bi bi-person"></i>
              <input type="text" name="first_name" placeholder="John" required autofocus>
            </div>
          </div>
          <div class="col-6">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Last name</label>
            <div class="bf-login-field">
              <input type="text" name="last_name" placeholder="Mwangi" required>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Email address</label>
          <div class="bf-login-field">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" placeholder="you@email.com" required>
          </div>
        </div>

        <div class="mb-3">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Phone</label>
          <div class="bf-login-field">
            <span style="font-size:13px;color:var(--ink-3);font-weight:600;flex-shrink:0;">+254</span>
            <input type="tel" name="phone" placeholder="7XX XXX XXX">
          </div>
        </div>

        <div class="mb-4">
          <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:7px;">Password</label>
          <div class="bf-login-field">
            <i class="bi bi-lock"></i>
            <input type="password" name="password" id="regPassword" minlength="8" placeholder="Min. 8 characters" required>
            <i class="bi bi-eye" id="regTogglePw" style="cursor:pointer;" onclick="(function(){var p=document.getElementById('regPassword');var i=document.getElementById('regTogglePw');var s=p.type==='password';p.type=s?'text':'password';i.className=s?'bi bi-eye-slash':'bi bi-eye';})()"></i>
          </div>
        </div>

        <button type="submit" class="bf-login-btn">
          <i class="bi bi-person-plus"></i>Create Account
        </button>
      </form>

      <div class="bf-login-divider"><span>or sign up with</span></div>

      <div class="d-flex gap-2">
        <a href="#" class="bf-login-social"><i class="bi bi-google" style="color:#ea4335;"></i> Google</a>
        <a href="#" class="bf-login-social"><i class="bi bi-apple" style="color:var(--ink);"></i> Apple</a>
      </div>

      <p style="font-size:11px;color:var(--ink-4);text-align:center;margin:16px 0 0;line-height:1.6;">
        By creating an account you agree to bildfie's <a href="#" style="color:var(--ink-3);">Terms</a> &amp; <a href="#" style="color:var(--ink-3);">Privacy Policy</a>.
      </p>

      <div style="border-top:1px solid var(--line);margin:18px 0 0;padding-top:18px;text-align:center;">
        <span style="font-size:13px;color:var(--ink-3);">Already have an account? </span>
        <a href="/pages/auth/login.php" class="bf-login-link" style="font-size:13px;">Sign in</a>
      </div>
    </div>

    <p class="text-center mt-4 mb-0" style="font-size:11.5px;color:var(--ink-4);">&copy; <?= date('Y') ?> bildfie</p>

  </div>
</div>

<?php include __DIR__ . '/../../includes/scripts.php'; ?>
