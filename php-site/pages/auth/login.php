<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

// Already logged in
if (is_logged_in()) {
    header('Location: ' . ($_GET['redirect'] ?? '/pages/dashboard/'));
    exit;
}

$error      = '';
$unverified = false;
$unver_email = '';
$redirect   = $_GET['redirect'] ?? '/pages/dashboard/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $result   = user_login($email, $password);

    if ($result['ok']) {
        session_regenerate_id(true);
        header('Location: ' . $redirect);
        exit;
    } elseif (!empty($result['unverified'])) {
        $unverified  = true;
        $unver_email = $result['email'] ?? $email;
        $error       = $result['error'];
    } else {
        $error = $result['error'];
    }
}

// Resend verification
$resent = false;
if (isset($_GET['resend']) && !empty($_GET['email'])) {
    resend_verification(trim($_GET['email']));
    $resent = true;
}

$page_title = 'Sign in';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--surface); padding: 32px 16px; }
.bf-auth-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 40px 40px 36px; width: 100%; max-width: 420px; }
@media (max-width: 480px) { .bf-auth-card { padding: 28px 20px 24px; } }
</style>

<div class="bf-auth-wrap">
  <div class="bf-auth-card">
    <!-- Logo -->
    <div class="text-center mb-4">
      <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-building-fill-up" style="font-size:24px;color:#1e3a5f;"></i>
        <span style="font-weight:800;font-size:1.2rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
      </a>
      <h1 class="mt-3 mb-1" style="font-size:1.4rem;font-weight:700;">Welcome back</h1>
      <p class="text-muted" style="font-size:.875rem;">Sign in to your bildfie account</p>
    </div>

    <?php if ($resent): ?>
      <div class="alert alert-success alert-sm py-2 px-3" style="font-size:.85rem;">
        <i class="bi bi-check-circle me-2"></i>Verification email resent — check your inbox.
      </div>
    <?php endif; ?>

    <?php if ($unverified): ?>
      <div class="alert alert-warning py-2 px-3" style="font-size:.85rem;">
        <i class="bi bi-envelope-exclamation me-2"></i>
        <?= htmlspecialchars($error) ?>
        <div class="mt-2">
          <a href="?resend=1&email=<?= urlencode($unver_email) ?>" class="alert-link">
            Resend verification email
          </a>
        </div>
      </div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger py-2 px-3" style="font-size:.85rem;">
        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

      <div class="mb-3">
        <label for="email" class="form-label fw-600" style="font-size:.875rem;">Email address</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars($unver_email ?: ($_POST['email'] ?? '')) ?>"
               placeholder="you@example.com" required autocomplete="email" autofocus>
      </div>

      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="password" class="form-label fw-600 mb-0" style="font-size:.875rem;">Password</label>
          <a href="/pages/auth/forgot-password.php" style="font-size:.8rem;color:#1e3a5f;">Forgot password?</a>
        </div>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" required autocomplete="current-password">
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember" style="font-size:.875rem;">Keep me signed in</label>
      </div>

      <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;padding:12px;">
        Sign in
      </button>
    </form>

    <p class="text-center mt-4 mb-0" style="font-size:.875rem;color:#6b6b6b;">
      Don't have an account?
      <a href="/pages/auth/register.php" style="color:#1e3a5f;font-weight:600;">Create one free</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
