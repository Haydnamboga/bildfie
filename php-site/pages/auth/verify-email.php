<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$token   = trim($_GET['token'] ?? '');
$result  = ['ok' => false, 'error' => 'No token provided.'];
$resent  = false;

if ($token !== '') {
    $result = email_verify($token);
}

// Resend form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resend_email'])) {
    resend_verification(trim($_POST['resend_email']));
    $resent = true;
}

$page_title = 'Verify Email';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-auth-wrap { min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--surface);padding:32px 16px; }
.bf-auth-card { background:#fff;border:1px solid var(--line);border-radius:14px;padding:40px 40px 36px;width:100%;max-width:420px;text-align:center; }
</style>

<div class="bf-auth-wrap">
  <div class="bf-auth-card">
    <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-4">
      <i class="bi bi-building-fill-up" style="font-size:24px;color:#1e3a5f;"></i>
      <span style="font-weight:800;font-size:1.2rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
    </a>

    <?php if ($result['ok']): ?>
      <div style="width:56px;height:56px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <i class="bi bi-check-lg" style="font-size:1.8rem;color:#16a34a;"></i>
      </div>
      <h1 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;">Email verified!</h1>
      <p style="font-size:.875rem;color:#6b6b6b;margin-bottom:24px;">
        Your email address has been confirmed. You can now sign in to your bildfie account.
      </p>
      <a href="/pages/auth/login.php" class="bf-btn-dark" style="justify-content:center;width:100%;">Sign in now</a>

    <?php else: ?>
      <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <i class="bi bi-x-lg" style="font-size:1.6rem;color:#c0392b;"></i>
      </div>
      <h1 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;">Link expired or invalid</h1>
      <p style="font-size:.875rem;color:#6b6b6b;margin-bottom:24px;">
        <?= htmlspecialchars($result['error'] ?? 'This verification link is no longer valid.') ?>
        Enter your email below to request a new one.
      </p>

      <?php if ($resent): ?>
        <div class="alert alert-success py-2 px-3 text-start" style="font-size:.85rem;">
          <i class="bi bi-check-circle me-2"></i>If that email is registered and unverified, we've sent a new link.
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="text-start">
        <div class="mb-3">
          <label for="resend_email" class="form-label" style="font-size:.875rem;font-weight:600;">Your email address</label>
          <input type="email" id="resend_email" name="resend_email" class="form-control"
                 placeholder="you@example.com" required>
        </div>
        <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;">
          Resend verification link
        </button>
      </form>

      <p class="mt-3 mb-0" style="font-size:.85rem;color:#6b6b6b;">
        Already verified? <a href="/pages/auth/login.php" style="color:#1e3a5f;font-weight:600;">Sign in</a>
      </p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
