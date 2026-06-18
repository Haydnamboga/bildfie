<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$u = current_user();
$page_title = 'My profile';
$saved = false; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $full_name = trim($_POST['full_name'] ?? '');
    $headline  = trim($_POST['headline'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    $skills    = trim($_POST['skills'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $category  = $_POST['category'] ?? '';
    $is_pro    = isset($_POST['is_pro']) ? 1 : 0;
    $rate_raw  = trim($_POST['hourly_rate'] ?? '');
    $hourly    = $rate_raw === '' ? null : (float)$rate_raw;
    if ($category !== '' && !in_array($category, trade_categories(), true)) $category = '';
    if ($full_name === '') $errors[] = 'Full name cannot be empty.';

    if (!$errors) {
        $cat = $category === '' ? null : $category;
        $stmt = $mysqli->prepare(
          'UPDATE users SET full_name=?, headline=?, bio=?, skills=?, location=?, category=?, hourly_rate=?, is_pro=? WHERE id=?'
        );
        $stmt->bind_param('ssssssdii', $full_name, $headline, $bio, $skills, $location, $cat, $hourly, $is_pro, $u['id']);
        $stmt->execute(); $stmt->close();
        flash('Profile updated.');
        redirect('profile.php');
    }
}
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= e(url('dashboard.php')) ?>">← Back to dashboard</a>
<div class="form-card form-wide">
  <h1 class="auth-title">Edit profile</h1>
  <p class="auth-sub">This is what clients see when they view your profile.</p>
  <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" action="<?= e(url('profile.php')) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label>Full name</label>
      <input class="input" name="full_name" value="<?= e($u['full_name']) ?>" required>
    </div>
    <div class="field">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="is_pro" value="1" <?= $u['is_pro']?'checked':'' ?>>
        I offer services as a trade professional (show me in the marketplace)
      </label>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Trade / category</label>
        <select name="category" class="select">
          <option value="">— none —</option>
          <?php foreach (trade_categories() as $c): ?>
            <option value="<?= e($c) ?>" <?= $u['category']===$c?'selected':'' ?>><?= e(nice($c)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Location</label>
        <input class="input" name="location" value="<?= e($u['location']) ?>" placeholder="e.g. Nairobi">
      </div>
    </div>
    <div class="field">
      <label>Headline</label>
      <input class="input" name="headline" value="<?= e($u['headline']) ?>" placeholder="e.g. Licensed electrician, 10+ yrs">
    </div>
    <div class="field">
      <label>About / bio</label>
      <textarea class="textarea" name="bio" placeholder="Tell clients about your experience…"><?= e($u['bio']) ?></textarea>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Skills (comma separated)</label>
        <input class="input" name="skills" value="<?= e($u['skills']) ?>" placeholder="Wiring, Solar, Inspection">
      </div>
      <div class="field">
        <label>Hourly rate (KES)</label>
        <input class="input" type="number" step="0.01" name="hourly_rate" value="<?= e($u['hourly_rate']) ?>">
      </div>
    </div>
    <button class="btn btn-primary" type="submit">Save profile</button>
    <a class="btn btn-ghost" href="<?= e(url('professional.php?id=' . $u['id'])) ?>">View public profile</a>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
