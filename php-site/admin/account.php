<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'My Account';
$ap = '';
$topbar_crumb = 'Account';

$id  = (int) current_admin()['id'];
$msg = ''; $err = '';

/** Save an uploaded image to /uploads/<subdir>/ and return its public path, or null. */
function save_upload(string $field, string $subdir): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) return null;
    $f = $_FILES[$field];
    if ($f['size'] > 4 * 1024 * 1024) return null;                       // 4 MB cap
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], "$dir/$name") ? "/uploads/$subdir/$name" : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'photos') {
        $photo = save_upload('photo_file', 'staff') ?: trim($_POST['photo_url'] ?? '');
        $cover = save_upload('cover_file', 'staff') ?: trim($_POST['cover_url'] ?? '');
        if ($photo !== '') { db_stmt("UPDATE staff SET photo_url=? WHERE id=?", [$photo, $id]); $_SESSION['admin']['photo'] = $photo; }
        if ($cover !== '') { db_stmt("UPDATE staff SET cover_url=? WHERE id=?", [$cover, $id]); $_SESSION['admin']['cover'] = $cover; }
        admin_audit('account.photos.update', 'staff', $id);
        $msg = 'Your photos were updated.';

    } elseif ($action === 'password') {
        $cur = (string) ($_POST['current'] ?? '');
        $new = (string) ($_POST['new'] ?? '');
        $conf = (string) ($_POST['confirm'] ?? '');
        $row = db_one("SELECT password_hash FROM staff WHERE id=?", [$id]);
        if (!$row || !password_verify($cur, $row['password_hash'])) {
            $err = 'Your current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $err = 'New password must be at least 6 characters.';
        } elseif ($new !== $conf) {
            $err = 'New passwords do not match.';
        } else {
            db_stmt("UPDATE staff SET password_hash=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $id]);
            admin_audit('account.password.change', 'staff', $id);
            $msg = 'Your password has been changed.';
        }
    }
}

$me = db_one(
    "SELECT s.*, r.name AS role_name, r.level, d.name AS dept_name
     FROM staff s LEFT JOIN roles r ON r.id=s.role_id LEFT JOIN departments d ON d.id=s.department_id
     WHERE s.id=?", [$id]
);
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body" style="max-width:920px;">

      <div class="bf-dash-h" style="margin-bottom:12px;">
        <div>
          <div class="bf-eyebrow2"><span>—</span> Account</div>
          <h1 class="bf-dash-title">My account</h1>
          <p class="bf-dash-sub">Update your photo, cover and password. Identity &amp; security details are managed in the database.</p>
        </div>
      </div>

      <?php if ($msg): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:11px 15px;font-size:13px;margin-bottom:14px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div style="background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;border-radius:10px;padding:11px 15px;font-size:13px;margin-bottom:14px;"><i class="bi bi-exclamation-circle-fill me-1"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

      <!-- cover + avatar + identity -->
      <div class="bf-pf-card" style="overflow:hidden;">
        <div style="height:130px;background:#0d1f36 center/cover no-repeat;<?= $me['cover_url'] ? "background-image:url('".htmlspecialchars($me['cover_url'],ENT_QUOTES)."');" : "background:linear-gradient(135deg,#1e3a5f,#0d1f36);" ?>"></div>
        <div class="bf-pf-card-b" style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;margin-top:-44px;">
          <img src="<?= htmlspecialchars($me['photo_url'] ?: 'https://randomuser.me/api/portraits/men/45.jpg', ENT_QUOTES) ?>" alt="" style="width:84px;height:84px;border-radius:50%;object-fit:cover;border:4px solid var(--white);background:var(--surface);">
          <div style="flex:1;min-width:200px;padding-top:44px;">
            <div style="font-size:18px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($me['name']) ?></div>
            <div style="font-size:12.5px;color:var(--ink-2);"><?= htmlspecialchars($me['role_name'] ?? 'Staff') ?> · <?= htmlspecialchars($me['dept_name'] ?? '') ?></div>
            <div style="margin-top:7px;display:flex;gap:7px;flex-wrap:wrap;">
              <span class="bf-badge red"><i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($me['level'] ?? 'L0') ?> · Full access</span>
              <?php if ((int)$me['is_protected'] === 1): ?><span class="bf-badge navy"><i class="bi bi-lock-fill"></i> Protected owner account</span><?php endif; ?>
            </div>
          </div>
        </div>
        <!-- read-only identity & security -->
        <div class="bf-pf-card-b" style="padding-top:0;">
          <div style="background:var(--surface);border:1px solid var(--line);border-radius:11px;padding:14px 16px;">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);margin-bottom:8px;"><i class="bi bi-database-lock"></i> Managed in the database — read only</div>
            <div class="row g-2" style="font-size:12.5px;">
              <?php foreach ([
                ['Full name', $me['name']],
                ['Email', $me['email']],
                ['Role / position', $me['role_name']],
                ['Department', $me['dept_name']],
                ['Account ID', $me['public_id']],
                ['Member since', $me['onboarded_year']],
              ] as [$k,$v]): ?>
              <div class="col-md-6" style="display:flex;justify-content:space-between;gap:12px;padding:5px 0;border-bottom:1px solid var(--line-2);"><span style="color:var(--ink-3);"><?= $k ?></span><span style="font-weight:700;color:var(--ink);text-align:right;"><?= htmlspecialchars((string)$v) ?></span></div>
              <?php endforeach; ?>
            </div>
            <div style="font-size:11px;color:var(--ink-4);margin-top:10px;"><i class="bi bi-info-circle"></i> To change your name, email or role, a database administrator must update the record directly. This account is protected and cannot be deleted.</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <!-- photos -->
        <div class="col-lg-6">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-image"></i> Photo &amp; cover</div></div>
            <div class="bf-pf-card-b">
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="photos">
                <label class="bf-f-lbl">Profile photo</label>
                <input type="file" name="photo_file" accept="image/*" class="bf-f-input" style="padding:8px;">
                <input type="url" name="photo_url" placeholder="…or paste an image URL" class="bf-f-input" style="margin-top:8px;">
                <label class="bf-f-lbl mt-3">Cover photo</label>
                <input type="file" name="cover_file" accept="image/*" class="bf-f-input" style="padding:8px;">
                <input type="url" name="cover_url" placeholder="…or paste an image URL" class="bf-f-input" style="margin-top:8px;">
                <button type="submit" class="bf-btn-navy" style="margin-top:14px;"><i class="bi bi-upload me-1"></i>Save photos</button>
              </form>
            </div>
          </div>
        </div>
        <!-- password -->
        <div class="col-lg-6">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-shield-lock"></i> Change password</div></div>
            <div class="bf-pf-card-b">
              <form method="POST">
                <input type="hidden" name="action" value="password">
                <label class="bf-f-lbl">Current password</label>
                <input type="password" name="current" class="bf-f-input" required>
                <label class="bf-f-lbl mt-2">New password</label>
                <input type="password" name="new" class="bf-f-input" minlength="6" required>
                <label class="bf-f-lbl mt-2">Confirm new password</label>
                <input type="password" name="confirm" class="bf-f-input" minlength="6" required>
                <button type="submit" class="bf-btn-navy" style="margin-top:14px;"><i class="bi bi-check-lg me-1"></i>Update password</button>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
