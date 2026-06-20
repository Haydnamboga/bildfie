<?php
require_once __DIR__ . '/config/admin.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$me = db_one(
    "SELECT s.*, r.name AS role_name, r.level, r.id AS role_id, r.scope AS role_scope, d.name AS dept_name
     FROM staff s
     LEFT JOIN roles r       ON r.id = s.role_id
     LEFT JOIN departments d ON d.id = s.department_id
     WHERE s.id = ?", [$id]
);
if (!$me) { header('Location: /admin/roles.php'); exit; }

$perms = db_all(
    "SELECT p.name, p.module FROM role_permissions rp
     JOIN permissions p ON p.id = rp.permission_id
     WHERE rp.role_id = ? ORDER BY p.module, p.name", [(int) $me['role_id']]
);
$activity = db_all(
    "SELECT action, entity_type, entity_id, created_at FROM audit_logs
     WHERE actor_type='staff' AND actor_id = ? ORDER BY id DESC LIMIT 12", [$id]
);

$page_title = $me['name'];
$ap = 'roles_users';
$topbar_crumb = 'Roles & Users';
$lvlColor = ['L0'=>['#c0392b','#fef2f2'],'L1'=>['#c0392b','#fef2f2'],'L2'=>['#1e3a5f','#eaf0f6'],'L3'=>['#9a7d27','#fdf6e3'],'L4'=>['#6b6b6b','#f4f4f2']];
[$lc,$lbg] = $lvlColor[$me['level']] ?? ['#6b6b6b','#f4f4f2'];
$yrs = $me['onboarded_year'] ? max(1, (int)date('Y') - (int)$me['onboarded_year']) : null;
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body" style="max-width:1040px;">
      <div style="font-size:12px;color:var(--ink-4);margin-bottom:12px;">
        <a href="/admin/roles.php" style="color:var(--ink-3);text-decoration:none;">Roles &amp; Users</a>
        <i class="bi bi-chevron-right" style="font-size:9px;"></i> <span style="color:var(--ink-2);"><?= htmlspecialchars($me['name']) ?></span>
      </div>

      <!-- header -->
      <div class="bf-pf-card" style="overflow:hidden;">
        <div style="height:90px;<?= $me['cover_url'] ? "background:center/cover no-repeat url('".htmlspecialchars($me['cover_url'],ENT_QUOTES)."');" : "background:linear-gradient(135deg,#1e3a5f,#0d1f36);" ?>"></div>
        <div class="bf-pf-card-b" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin-top:-34px;">
          <img src="<?= htmlspecialchars($me['photo_url'] ?: 'https://randomuser.me/api/portraits/men/45.jpg', ENT_QUOTES) ?>" alt="" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:4px solid var(--white);background:var(--surface);">
          <div style="flex:1;min-width:220px;padding-top:34px;">
            <div style="font-size:18px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($me['name']) ?></div>
            <div style="font-size:13px;color:var(--ink-2);"><?= htmlspecialchars($me['role_name'] ?? 'Staff') ?> · <?= htmlspecialchars($me['dept_name'] ?? '') ?></div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:9px;">
              <span class="bf-badge" style="background:<?=$lbg?>;color:<?=$lc?>;"><?= htmlspecialchars($me['level'] ?? '—') ?></span>
              <span class="bf-badge <?= $me['status']==='active'?'green':'grey' ?>"><?= ucfirst($me['status']) ?></span>
              <?php if ($me['onboarded_year']): ?><span class="bf-badge navy"><i class="bi bi-calendar-check"></i> Joined <?= $me['onboarded_year'] ?><?= $yrs?" · {$yrs}y":'' ?></span><?php endif; ?>
              <?php if ((int)$me['is_protected']===1): ?><span class="bf-badge red"><i class="bi bi-lock-fill"></i> Protected owner</span><?php endif; ?>
            </div>
          </div>
          <div style="display:flex;gap:8px;">
            <a href="#" class="bf-btn-ghost" style="text-decoration:none;"><i class="bi bi-chat-dots me-1"></i>Message</a>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-7">
          <!-- real activity -->
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-activity"></i> Recent activity</div><span class="bf-badge green" style="font-size:9px;"><i class="bi bi-database"></i> Audit log</span></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php if (!$activity): ?>
              <div style="font-size:12.5px;color:var(--ink-4);padding:10px 0;">No recorded activity yet.</div>
              <?php else: foreach ($activity as $a): ?>
              <div class="bf-li">
                <span class="bf-li-ic" style="background:var(--surface);color:#1e3a5f;border:1px solid var(--line);"><i class="bi bi-dot" style="font-size:20px;"></i></span>
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;color:var(--ink-2);"><code style="font-size:11.5px;color:#1e3a5f;"><?= htmlspecialchars($a['action']) ?></code><?php if($a['entity_type']):?> <span style="color:var(--ink-4);">· <?= htmlspecialchars($a['entity_type']) ?> #<?= htmlspecialchars((string)$a['entity_id']) ?></span><?php endif;?></div></div>
                <span style="font-size:10.5px;color:var(--ink-4);white-space:nowrap;"><?= date('d M · H:i', strtotime($a['created_at'])) ?></span>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <!-- role & permissions (real) -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-shield-lock"></i> Role &amp; permissions</div><span class="bf-badge navy"><?= count($perms) ?></span></div>
            <div class="bf-pf-card-b" style="padding-top:10px;">
              <div style="font-size:12px;color:var(--ink-3);margin-bottom:10px;"><?= htmlspecialchars($me['role_scope'] ?? '') ?></div>
              <?php if (!$perms): ?>
              <div style="font-size:12px;color:var(--ink-4);">No explicit permissions on this role.</div>
              <?php else: foreach ($perms as $p): ?>
              <div style="display:flex;gap:9px;align-items:center;font-size:12px;color:var(--ink-2);padding:5px 0;border-top:1px solid var(--line-2);"><i class="bi bi-check-circle-fill" style="color:#1e3a5f;font-size:12px;"></i><span><?= htmlspecialchars($p['name']) ?></span><span class="bf-badge grey" style="margin-left:auto;font-size:8.5px;"><?= htmlspecialchars($p['module']) ?></span></div>
              <?php endforeach; endif; ?>
            </div>
          </div>
          <!-- quick facts (real) -->
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-info-circle"></i> Account</div></div>
            <div class="bf-pf-card-b" style="padding-top:6px;padding-bottom:6px;">
              <?php foreach ([
                ['Email',$me['email']],['Account ID',$me['public_id']],
                ['Last login', $me['last_login_at'] ? date('d M Y · H:i', strtotime($me['last_login_at'])) : 'Never'],
                ['Created', date('d M Y', strtotime($me['created_at']))],
              ] as [$k,$v]): ?>
              <div style="display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-top:1px solid var(--line-2);font-size:12px;"><span style="color:var(--ink-3);"><?= $k ?></span><span style="font-weight:700;color:var(--ink);text-align:right;word-break:break-all;"><?= htmlspecialchars((string)$v) ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
