<?php
require_once __DIR__ . '/config/admin.php';

// Already logged in
if (admin_logged_in()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Enter your admin email and password.';
    } elseif (admin_attempt($email, $password)) {
        session_regenerate_id(true);
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = 'Invalid credentials or account is inactive.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login · bildfie</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= ASSETS_URL ?>/css/app.css" rel="stylesheet">
<link href="<?= ADMIN_ASSETS ?>/admin.css" rel="stylesheet">
<style>
  body { background: #0d0d0d; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
  .adm-login-card { background: #161616; border: 1px solid #2a2a2a; border-radius: 14px; padding: 44px 44px 40px; width: 100%; max-width: 400px; }
  .adm-login-card .form-control { background: #1e1e1e; border-color: #2e2e2e; color: #e8e8e8; }
  .adm-login-card .form-control:focus { background: #222; border-color: #c9a84c; box-shadow: 0 0 0 3px rgba(201,168,76,.12); color: #fff; }
  .adm-login-card .form-control::placeholder { color: #555; }
  .adm-login-card label { color: #9b9b9b; font-size: .8rem; font-weight: 600; letter-spacing: .03em; }
  .adm-login-btn { background: #1e3a5f; color: #fff; border: none; border-radius: 8px; padding: 11px; font-weight: 700; font-size: .9rem; width: 100%; letter-spacing: .02em; transition: background .15s; }
  .adm-login-btn:hover { background: #16305a; }
  .adm-alert { background: #2d1b1b; border: 1px solid #5a1e1e; color: #f87171; border-radius: 8px; padding: 10px 14px; font-size: .83rem; margin-bottom: 20px; }
  @media (max-width: 480px) { .adm-login-card { padding: 28px 20px 24px; } }
</style>
</head>
<body>

<div class="adm-login-card">
  <!-- Brand -->
  <div class="text-center mb-5">
    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
      <i class="bi bi-building-fill-up" style="font-size:22px;color:#c9a84c;"></i>
      <span style="font-weight:800;font-size:1.2rem;color:#fff;letter-spacing:-.5px;">bildfie</span>
    </div>
    <div style="font-size:.72rem;color:#555;letter-spacing:.1em;text-transform:uppercase;">Administration Panel</div>
    <div style="width:32px;height:2px;background:#c9a84c;margin:10px auto 0;border-radius:2px;"></div>
  </div>

  <?php if ($error): ?>
    <div class="adm-alert"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" novalidate>
    <div class="mb-3">
      <label for="email" class="form-label text-uppercase mb-1">Email</label>
      <input type="email" id="email" name="email" class="form-control"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="admin@bildfie.com" required autocomplete="email" autofocus>
    </div>

    <div class="mb-4">
      <label for="password" class="form-label text-uppercase mb-1">Password</label>
      <input type="password" id="password" name="password" class="form-control"
             placeholder="••••••••" required autocomplete="current-password">
    </div>

    <button type="submit" class="adm-login-btn">
      Sign in to Admin
    </button>
  </form>

  <p class="text-center mt-4 mb-0" style="font-size:.75rem;color:#444;">
    Not an admin?
    <a href="/" style="color:#c9a84c;text-decoration:none;">Go to site</a>
  </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
