<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

if (is_logged_in()) { header('Location: /pages/dashboard/'); exit; }

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = false;

// Validate token before showing form
$valid_user = $token !== '' ? auth_token_user($token, 'reset') : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = password_reset($token, $password);
        if ($result['ok']) {
            $success = true;
        } else {
            $error = $result['error'];
        }
    }
}

$page_title = 'Reset Password';
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
      <h1 class="mt-3 mb-1" style="font-size:1.4rem;font-weight:700;">Choose a new password</h1>
    </div>

    <?php if ($success): ?>
      <div class="text-center">
        <div style="width:52px;height:52px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
          <i class="bi bi-shield-check" style="font-size:1.5rem;color:#16a34a;"></i>
        </div>
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Password updated</h2>
        <p style="font-size:.875rem;color:#6b6b6b;margin-bottom:24px;">
          Your password has been reset. You can now sign in with your new password.
        </p>
        <a href="/pages/auth/login.php" class="bf-btn-dark" style="justify-content:center;min-width:160px;">Sign in</a>
      </div>

    <?php elseif (!$valid_user && !$_POST): ?>
      <div class="text-center">
        <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
          <i class="bi bi-clock-history" style="font-size:1.5rem;color:#c0392b;"></i>
        </div>
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Link expired or invalid</h2>
        <p style="font-size:.875rem;color:#6b6b6b;margin-bottom:20px;">
          This reset link has expired or already been used. Request a fresh one.
        </p>
        <a href="/pages/auth/forgot-password.php" class="bf-btn-dark" style="justify-content:center;min-width:160px;">
          Request new link
        </a>
      </div>

    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
          <label for="password" class="form-label" style="font-size:.875rem;font-weight:600;">New password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min. 6 characters" required autocomplete="new-password" autofocus>
        </div>
        <div class="mb-4">
          <label for="password_confirm" class="form-label" style="font-size:.875rem;font-weight:600;">Confirm new password</label>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                 placeholder="Repeat password" required autocomplete="new-password">
        </div>
        <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;padding:12px;">
          Update password
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
