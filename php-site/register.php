<?php
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) redirect('dashboard.php');
$page_title = 'Sign up';
$errors = [];
$old = ['full_name'=>'','email'=>'','account_type'=>'client','category'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = strtolower(trim($_POST['email'] ?? ''));
    $old['account_type'] = $_POST['account_type'] ?? 'client';
    $old['category']  = $_POST['category'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($old['full_name'] === '')            $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 8)               $errors[] = 'Password must be at least 8 characters.';
    $is_pro = ($old['account_type'] === 'pro') ? 1 : 0;
    if ($is_pro && !in_array($old['category'], trade_categories(), true)) $errors[] = 'Please choose your trade.';

    if (!$errors) {
        $chk = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $chk->bind_param('s', $old['email']);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            $errors[] = 'That email is already registered.';
        }
        $chk->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $cat = $is_pro ? $old['category'] : null;
        $stmt = $mysqli->prepare(
          'INSERT INTO users (email, password_hash, full_name, is_pro, category, email_verified)
           VALUES (?,?,?,?,?,1)'
        );
        $stmt->bind_param('sssis', $old['email'], $hash, $old['full_name'], $is_pro, $cat);
        if ($stmt->execute()) {
            $_SESSION['uid'] = $stmt->insert_id;
            flash('Welcome to bildfie, ' . $old['full_name'] . '!');
            redirect('dashboard.php');
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
  <h1 class="auth-title">Create your account</h1>
  <p class="auth-sub">Join bildfie as a client or a trade professional.</p>
  <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" action="<?= e(url('register.php')) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label>I want to…</label>
      <select name="account_type" class="select" id="acctType" onchange="document.getElementById('catField').style.display = this.value==='pro' ? 'block':'none'">
        <option value="client" <?= $old['account_type']==='client'?'selected':'' ?>>Hire professionals (Client)</option>
        <option value="pro" <?= $old['account_type']==='pro'?'selected':'' ?>>Offer my services (Professional)</option>
      </select>
    </div>
    <div class="field" id="catField" style="display:<?= $old['account_type']==='pro'?'block':'none' ?>">
      <label>Your trade</label>
      <select name="category" class="select">
        <option value="">Select a trade…</option>
        <?php foreach (trade_categories() as $c): ?>
          <option value="<?= e($c) ?>" <?= $old['category']===$c?'selected':'' ?>><?= e(nice($c)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Full name</label>
      <input class="input" name="full_name" value="<?= e($old['full_name']) ?>" required>
    </div>
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" value="<?= e($old['email']) ?>" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required>
      <div class="help">At least 8 characters.</div>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Create account</button>
  </form>
  <p class="auth-sub" style="margin:18px 0 0">Already have an account? <a href="<?= e(url('login.php')) ?>">Log in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
