<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

if (is_logged_in()) { header('Location: /pages/dashboard/'); exit; }

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        password_reset_request($email);
    }
    $sent = true; // Always show success (anti-enumeration)
}

$page_title = 'Forgot Password';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-auth-wrap { min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--surface);padding:32px 16px; }
.bf-auth-card { background:#fff;border:1px solid var(--line);border-radius:14px;padding:40px 40px 36px;width:100%;max-width:420px; }
</style>

<div class="bf-auth-wrap">
  <div class="bf-auth-card">
    <div class="text-center mb-4">
      <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-building-fill-up" style="font-size:24px;color:#1e3a5f;"></i>
        <span style="font-weight:800;font-size:1.2rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
      </a>

      <?php if ($sent): ?>
        <div style="width:52px;height:52px;background:#eaf0f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:20px auto 12px;">
          <i class="bi bi-envelope-check" style="font-size:1.5rem;color:#1e3a5f;"></i>
        </div>
        <h1 style="font-size:1.25rem;font-weight:700;margin-bottom:8px;">Check your inbox</h1>
        <p style="font-size:.875rem;color:#6b6b6b;">
          If that email is registered, we've sent a password reset link. It expires in 1 hour.
        </p>
        <a href="/pages/auth/login.php" class="bf-btn-dark mt-3 d-inline-flex" style="justify-content:center;min-width:160px;">
          Back to sign in
        </a>
      <?php else: ?>
        <h1 class="mt-3 mb-1" style="font-size:1.4rem;font-weight:700;">Forgot your password?</h1>
        <p style="font-size:.875rem;color:#6b6b6b;">Enter your email and we'll send a reset link.</p>
      <?php endif; ?>
    </div>

    <?php if (!$sent): ?>
    <form method="POST" action="" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label" style="font-size:.875rem;font-weight:600;">Email address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required autocomplete="email" autofocus>
      </div>
      <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;padding:12px;">
        Send reset link
      </button>
    </form>
    <p class="text-center mt-4 mb-0" style="font-size:.875rem;color:#6b6b6b;">
      <a href="/pages/auth/login.php" style="color:#1e3a5f;font-weight:600;">
        <i class="bi bi-arrow-left me-1"></i>Back to sign in
      </a>
    </p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
