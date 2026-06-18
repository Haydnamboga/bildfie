<?php
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) redirect('dashboard.php');
$page_title = 'Log in';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Enter your email and password.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $row['id'];
            flash('Welcome back!');
            redirect('dashboard.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
  <h1 class="auth-title">Welcome back</h1>
  <p class="auth-sub">Log in to your bildfie account.</p>
  <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" action="<?= e(url('login.php')) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" value="<?= e($email) ?>" required autofocus>
    </div>
    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Log in</button>
  </form>
  <p class="auth-sub" style="margin:18px 0 0">New here? <a href="<?= e(url('register.php')) ?>">Create an account</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
