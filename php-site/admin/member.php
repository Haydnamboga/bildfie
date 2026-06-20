<?php
require_once __DIR__ . '/config/admin.php';
require_admin();

if (!function_exists('bf_avatar')) {
    function bf_avatar(string $name): string {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1e3a5f&color=fff&size=140&bold=true';
    }
}

$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'save':
            $name   = trim($_POST['name'] ?? '');
            $phone  = trim($_POST['phone'] ?? '');
            $region = ($_POST['region_id'] ?? '') !== '' ? (int)$_POST['region_id'] : null;
            $isProv = isset($_POST['is_provider']) ? 1 : 0;
            $status = in_array($_POST['status'] ?? '', ['active','pending','suspended']) ? $_POST['status'] : 'active';
            $ver    = isset($_POST['verified']);
            if ($name !== '') {
                db_stmt("UPDATE users SET name=?, phone=?, region_id=?, is_provider=?, status=?,
                         email_verified_at = " . ($ver ? "COALESCE(email_verified_at, NOW())" : "NULL") . "
                         WHERE id=?", [$name, $phone ?: null, $region, $isProv, $status, $id]);
                admin_audit('user.update', 'user', $id);
                $_SESSION['m_flash'] = 'Member saved.';
            }
            header('Location: /admin/member.php?id=' . $id); exit;
        case 'soft_delete':
            db_stmt("UPDATE users SET status='deleted' WHERE id=?", [$id]);
            admin_audit('user.soft_delete', 'user', $id);
            $_SESSION['users_flash'] = 'Member moved to Deleted.';
            header('Location: /admin/users.php'); exit;
        case 'restore':
            db_stmt("UPDATE users SET status='active' WHERE id=?", [$id]);
            admin_audit('user.restore', 'user', $id);
            $_SESSION['m_flash'] = 'Member restored.';
            header('Location: /admin/member.php?id=' . $id); exit;
    }
}

$u = db_one("SELECT u.*, r.name AS region_name FROM users u LEFT JOIN regions r ON r.id=u.region_id WHERE u.id=?", [$id]);
$page_title = $u ? 'Member · ' . $u['name'] : 'Member not found';
$ap = 'users';
$topbar_crumb = 'Users';
$flash = $_SESSION['m_flash'] ?? ''; unset($_SESSION['m_flash']);
$regions = $u ? db_all("SELECT id,name FROM regions WHERE is_active=1 ORDER BY name") : [];
$prov = $u ? db_one("SELECT id,headline,rating,reviews_count,status FROM providers WHERE user_id=? LIMIT 1", [$id]) : null;
$acts = $u ? db_all("SELECT action,created_at FROM audit_logs WHERE entity_type='user' AND entity_id=? ORDER BY id DESC LIMIT 6", [(string)$id]) : [];
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <?php if (!$u): ?>
        <div class="bf-tbl-wrap" style="padding:40px;text-align:center;color:var(--ink-3);">
          <i class="bi bi-person-x" style="font-size:30px;color:var(--ink-4);"></i>
          <p style="margin:12px 0 0;">That member doesn't exist. <a href="/admin/users.php" style="color:#1e3a5f;">Back to Users</a></p>
        </div>
      <?php else:
        $verified = !empty($u['email_verified_at']); $st = $u['status'];
        $stColor = ['active'=>'green','pending'=>'amber','suspended'=>'red','deleted'=>'grey'][$st] ?? 'grey';
      ?>
      <div class="bf-dash-h">
        <div>
          <div class="bf-crumb"><a href="/admin/index.php">Dashboard</a> <i class="bi bi-chevron-right"></i> <a href="/admin/users.php">Users</a> <i class="bi bi-chevron-right"></i> <span style="color:var(--ink-2);">Edit</span></div>
          <h1 class="bf-dash-title">Member profile</h1>
        </div>
        <a href="/admin/users.php" class="bf-btn-s ghost" style="text-decoration:none;"><i class="bi bi-arrow-left"></i> Back</a>
      </div>

      <?php if ($flash): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:12px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>

      <div class="row g-3">
        <!-- identity card -->
        <div class="col-lg-4">
          <div class="bf-tbl-wrap" style="padding:20px;text-align:center;">
            <img src="<?= htmlspecialchars(bf_avatar($u['name']), ENT_QUOTES) ?>" style="width:84px;height:84px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
            <div style="font-size:16px;font-weight:800;color:var(--ink);"><?= htmlspecialchars($u['name']) ?></div>
            <div style="font-size:12.5px;color:var(--ink-4);margin-top:2px;"><?= htmlspecialchars($u['email']) ?></div>
            <div style="display:flex;gap:6px;justify-content:center;margin-top:11px;flex-wrap:wrap;">
              <span class="bf-badge <?= $stColor ?>"><?= ucfirst($st) ?></span>
              <span class="bf-badge <?= $verified?'green':'grey' ?>"><?= $verified?'Verified':'Unverified' ?></span>
              <span class="bf-badge <?= (int)$u['is_provider']?'navy':'grey' ?>"><?= (int)$u['is_provider']?'Provider':'Client' ?></span>
            </div>
            <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;text-align:left;font-size:12.5px;">
              <?php foreach ([
                ['Phone', $u['phone'] ?: '—'],
                ['Region', $u['region_name'] ?: '—'],
                ['Joined', date('d M Y', strtotime($u['created_at']))],
                ['User ID', '#'.$u['id']],
              ] as [$k,$v]): ?>
              <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:var(--ink-4);"><?=$k?></span><span style="color:var(--ink-2);font-weight:600;"><?= htmlspecialchars($v) ?></span></div>
              <?php endforeach; ?>
            </div>
            <?php if ($prov): ?>
            <a href="/admin/providers.php" style="display:block;margin-top:14px;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:10px;text-decoration:none;text-align:left;">
              <div style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);">Linked provider</div>
              <div style="font-size:12.5px;font-weight:700;color:#1e3a5f;margin-top:2px;"><?= htmlspecialchars($prov['headline']) ?></div>
              <div style="font-size:11px;color:var(--ink-4);"><i class="bi bi-star-fill" style="color:#f59e0b;"></i> <?= htmlspecialchars($prov['rating']) ?> · <?= (int)$prov['reviews_count'] ?> reviews</div>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- edit form -->
        <div class="col-lg-8">
          <div class="bf-tbl-wrap" style="padding:20px;">
            <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:14px;"><i class="bi bi-pencil-square me-1" style="color:#1e3a5f;"></i> Manage member</div>
            <form method="post">
              <input type="hidden" name="action" value="save">
              <input type="hidden" name="id" value="<?= $id ?>">
              <div class="row g-3">
                <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.04em;">Full name</label><input name="name" class="bf-fld" style="width:100%;height:38px;margin-top:4px;" value="<?= htmlspecialchars($u['name']) ?>" required></div>
                <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.04em;">Email <span style="color:var(--ink-4);font-weight:500;text-transform:none;">· read-only</span></label><input class="bf-fld" style="width:100%;height:38px;margin-top:4px;background:#f4f4f2;color:var(--ink-4);" value="<?= htmlspecialchars($u['email']) ?>" disabled></div>
                <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.04em;">Phone</label><input name="phone" class="bf-fld" style="width:100%;height:38px;margin-top:4px;" value="<?= htmlspecialchars($u['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.04em;">Region</label>
                  <select name="region_id" class="bf-fld" style="width:100%;height:38px;margin-top:4px;">
                    <option value="">— none —</option>
                    <?php foreach ($regions as $r): ?><option value="<?= (int)$r['id'] ?>" <?= (int)$u['region_id']===(int)$r['id']?'selected':'' ?>><?= htmlspecialchars($r['name']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.04em;">Status</label>
                  <select name="status" class="bf-fld" style="width:100%;height:38px;margin-top:4px;">
                    <?php foreach (['active'=>'Active','pending'=>'Pending','suspended'=>'Suspended'] as $k=>$lbl): ?><option value="<?=$k?>" <?= $st===$k?'selected':'' ?>><?=$lbl?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6" style="display:flex;align-items:flex-end;gap:18px;">
                  <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ink-2);cursor:pointer;"><input type="checkbox" name="verified" class="bf-chk" <?= $verified?'checked':'' ?>> Email verified</label>
                  <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ink-2);cursor:pointer;"><input type="checkbox" name="is_provider" class="bf-chk" <?= (int)$u['is_provider']?'checked':'' ?>> Is provider</label>
                </div>
              </div>
              <div style="margin-top:18px;display:flex;gap:8px;">
                <button class="bf-btn-s solid"><i class="bi bi-check-lg"></i> Save changes</button>
              </div>
            </form>

            <div style="border-top:1px solid var(--line);margin-top:18px;padding-top:14px;">
              <div style="font-size:11px;font-weight:800;color:var(--ink-4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Recent activity</div>
              <?php if (!$acts): ?><div style="font-size:12px;color:var(--ink-4);">No actions recorded yet.</div>
              <?php else: foreach ($acts as $a): ?>
              <div style="display:flex;justify-content:space-between;font-size:12px;padding:4px 0;"><code style="color:#1e3a5f;"><?= htmlspecialchars($a['action']) ?></code><span style="color:var(--ink-4);"><?= date('d M · H:i', strtotime($a['created_at'])) ?></span></div>
              <?php endforeach; endif; ?>
            </div>

            <div style="border-top:1px solid var(--line);margin-top:14px;padding-top:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
              <div style="font-size:11.5px;color:var(--ink-4);"><i class="bi bi-shield-lock me-1"></i> Deletes are <strong>soft</strong> — data is preserved and reversible.</div>
              <?php if ($st === 'deleted'): ?>
              <form method="post"><input type="hidden" name="action" value="restore"><input type="hidden" name="id" value="<?=$id?>"><button class="bf-btn-s ghost"><i class="bi bi-arrow-counterclockwise"></i> Restore member</button></form>
              <?php else: ?>
              <form method="post" onsubmit="return confirm('Soft-delete this member?');"><input type="hidden" name="action" value="soft_delete"><input type="hidden" name="id" value="<?=$id?>"><button class="bf-btn-s danger"><i class="bi bi-trash"></i> Soft-delete</button></form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
