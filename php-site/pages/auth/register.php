<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

if (is_logged_in()) { header('Location: /pages/dashboard/'); exit; }

$error     = '';
$success   = false;
$suc_email = '';
$fields    = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $fields   = compact('name', 'email', 'phone');

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $result = user_register($name, $email, $password, $phone ?: null);
        if ($result['ok']) {
            $success   = true;
            $suc_email = $result['email'];
        } else {
            $error = $result['error'];
        }
    }
}

$page_title = 'Create account';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-auth-wrap { min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--surface);padding:32px 16px; }
.bf-auth-card { background:#fff;border:1px solid var(--line);border-radius:14px;padding:40px 40px 36px;width:100%;max-width:480px; }
@media (max-width:480px) { .bf-auth-card { padding:28px 20px 24px; } }
.role-card { border:2px solid var(--line);border-radius:10px;padding:16px;cursor:pointer;transition:border-color .15s,background .15s; }
.role-card.selected,.role-card:has(input:checked) { border-color:#1e3a5f;background:#eaf0f6; }
</style>

<div class="bf-auth-wrap">
  <div class="bf-auth-card">
    <div class="text-center mb-4">
      <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-building-fill-up" style="font-size:24px;color:#1e3a5f;"></i>
        <span style="font-weight:800;font-size:1.2rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
      </a>
      <h1 class="mt-3 mb-1" style="font-size:1.4rem;font-weight:700;">Create your account</h1>
      <p class="text-muted" style="font-size:.875rem;">Join the construction professional network</p>
    </div>

    <?php if ($success): ?>
      <div class="text-center py-4">
        <div style="width:56px;height:56px;background:#eaf0f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
          <i class="bi bi-envelope-check" style="font-size:1.6rem;color:#1e3a5f;"></i>
        </div>
        <h2 style="font-size:1.15rem;font-weight:700;margin-bottom:8px;">Check your inbox</h2>
        <p style="font-size:.875rem;color:#6b6b6b;max-width:320px;margin:0 auto 20px;">
          We sent a verification link to <strong><?= htmlspecialchars($suc_email) ?></strong>.
          Click it to activate your account and sign in.
        </p>
        <a href="/pages/auth/login.php" class="bf-btn-dark" style="justify-content:center;">Go to sign in</a>
      </div>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>

        <!-- Role choice -->
        <div class="row g-2 mb-4">
          <div class="col-6">
            <label class="role-card d-block text-center">
              <input type="radio" name="role" value="client" class="d-none" checked>
              <i class="bi bi-person-bounding-box d-block mb-2" style="font-size:1.4rem;color:#1e3a5f;"></i>
              <div style="font-size:.8rem;font-weight:600;">I want to hire</div>
              <div style="font-size:.73rem;color:#6b6b6b;">Post projects &amp; find pros</div>
            </label>
          </div>
          <div class="col-6">
            <label class="role-card d-block text-center">
              <input type="radio" name="role" value="provider" class="d-none">
              <i class="bi bi-tools d-block mb-2" style="font-size:1.4rem;color:#1e3a5f;"></i>
              <div style="font-size:.8rem;font-weight:600;">I'm a professional</div>
              <div style="font-size:.73rem;color:#6b6b6b;">Offer services &amp; get hired</div>
            </label>
          </div>
        </div>

        <div class="mb-3">
          <label for="name" class="form-label" style="font-size:.875rem;font-weight:600;">Full name</label>
          <input type="text" id="name" name="name" class="form-control"
                 value="<?= htmlspecialchars($fields['name']) ?>"
                 placeholder="Jane Kamau" required autocomplete="name" autofocus>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label" style="font-size:.875rem;font-weight:600;">Email address</label>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($fields['email']) ?>"
                 placeholder="you@example.com" required autocomplete="email">
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label" style="font-size:.875rem;font-weight:600;">
            Phone number <span style="font-weight:400;color:#6b6b6b;">(optional)</span>
          </label>
          <input type="tel" id="phone" name="phone" class="form-control"
                 value="<?= htmlspecialchars($fields['phone']) ?>"
                 placeholder="+254 700 000 000" autocomplete="tel">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <label for="password" class="form-label" style="font-size:.875rem;font-weight:600;">Password</label>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="Min. 6 characters" required autocomplete="new-password">
          </div>
          <div class="col-6">
            <label for="password_confirm" class="form-label" style="font-size:.875rem;font-weight:600;">Confirm</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                   placeholder="Repeat password" required autocomplete="new-password">
          </div>
        </div>

        <p class="text-muted mb-3" style="font-size:.78rem;">
          By creating an account you agree to our
          <a href="#" style="color:#1e3a5f;">Terms of Service</a> and
          <a href="#" style="color:#1e3a5f;">Privacy Policy</a>.
        </p>

        <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;padding:12px;">
          Create account
        </button>
      </form>

      <p class="text-center mt-4 mb-0" style="font-size:.875rem;color:#6b6b6b;">
        Already have an account?
        <a href="/pages/auth/login.php" style="color:#1e3a5f;font-weight:600;">Sign in</a>
      </p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Role card visual toggle
document.querySelectorAll('.role-card').forEach(function(card){
  card.querySelector('input').addEventListener('change', function(){
    document.querySelectorAll('.role-card').forEach(function(c){ c.classList.remove('selected'); });
    card.classList.add('selected');
  });
});
// Pre-select first
var first = document.querySelector('.role-card');
if(first) first.classList.add('selected');
</script>
</body>
</html>
