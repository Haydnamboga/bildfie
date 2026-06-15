<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Roles & Users';
$ap = 'roles_users';
$topbar_crumb = 'System';
$topbar_action = '<button class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-person-plus me-1"></i>Invite staff</button>';

$staff = db_all(
    "SELECT s.id,s.name,s.email,s.photo_url,s.status,s.onboarded_year,s.last_login_at,s.is_protected,
            r.name AS role_name, r.level, d.name AS dept_name
     FROM staff s
     LEFT JOIN roles r       ON r.id = s.role_id
     LEFT JOIN departments d ON d.id = s.department_id
     ORDER BY FIELD(r.level,'L0','L1','L2','L3','L4'), s.name"
);
$cTotal  = (int) db_value("SELECT COUNT(*) FROM staff");
$cActive = (int) db_value("SELECT COUNT(*) FROM staff WHERE status='active'");
$cRoles  = (int) db_value("SELECT COUNT(*) FROM roles");
$cDepts  = (int) db_value("SELECT COUNT(*) FROM departments");

$lvlColor = ['L0'=>['#c0392b','#fef2f2'],'L1'=>['#c0392b','#fef2f2'],'L2'=>['#1e3a5f','#eaf0f6'],'L3'=>['#9a7d27','#fdf6e3'],'L4'=>['#6b6b6b','#f4f4f2']];
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <div class="bf-dash-h">
        <div>
          <div class="bf-eyebrow2"><span>—</span> System &amp; Administration</div>
          <h1 class="bf-dash-title">Roles &amp; Users</h1>
          <p class="bf-dash-sub">Live staff directory — open any member to view their role, permissions and activity.</p>
        </div>
      </div>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Staff',$cTotal,'bi-people','#1e3a5f','#eaf0f6'],
          ['Active',$cActive,'bi-broadcast','#166534','#f0fdf4'],
          ['Roles',$cRoles,'bi-shield-lock','#9a7d27','#fdf6e3'],
          ['Departments',$cDepts,'bi-diagram-3','#1e40af','#eff6ff'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
        <div class="adm-search" style="max-width:280px;height:36px;"><i class="bi bi-search"></i><input placeholder="Search staff or role…"></div>
        <span class="bf-badge green" style="font-size:9.5px;"><i class="bi bi-database"></i> Live from database</span>
      </div>

      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="flex:1;">User</div>
          <div style="width:200px;" class="d-none d-lg-block">Role</div>
          <div style="width:120px;" class="d-none d-md-block">Department</div>
          <div style="width:60px;">Level</div>
          <div style="width:130px;" class="d-none d-xl-block">Last login</div>
          <div style="width:80px;">Status</div>
          <div style="width:30px;"></div>
        </div>
        <?php foreach ($staff as $p): [$lc,$lbg] = $lvlColor[$p['level']] ?? ['#6b6b6b','#f4f4f2']; ?>
        <a href="/admin/user.php?id=<?= (int)$p['id'] ?>" class="bf-tbl-row" style="text-decoration:none;cursor:pointer;">
          <div style="flex:1;min-width:0;display:flex;align-items:center;gap:11px;">
            <img src="<?= htmlspecialchars($p['photo_url'] ?: 'https://randomuser.me/api/portraits/men/45.jpg', ENT_QUOTES) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            <div style="min-width:0;"><div class="pri" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?><?php if((int)$p['is_protected']===1):?> <i class="bi bi-lock-fill" style="color:#c0392b;font-size:10px;" title="Protected owner"></i><?php endif;?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($p['email']) ?></div></div>
          </div>
          <div style="width:200px;color:var(--ink-2);" class="d-none d-lg-block"><?= htmlspecialchars($p['role_name'] ?? '—') ?></div>
          <div style="width:120px;" class="d-none d-md-block"><span class="bf-badge grey"><?= htmlspecialchars($p['dept_name'] ?? '—') ?></span></div>
          <div style="width:60px;"><span class="bf-badge" style="background:<?=$lbg?>;color:<?=$lc?>;"><?= htmlspecialchars($p['level'] ?? '—') ?></span></div>
          <div style="width:130px;font-size:11.5px;" class="d-none d-xl-block"><?= $p['last_login_at'] ? date('d M · H:i', strtotime($p['last_login_at'])) : '<span style="color:var(--ink-4);">Never</span>' ?></div>
          <div style="width:80px;"><span class="bf-badge <?= $p['status']==='active'?'green':'grey' ?>"><?= ucfirst($p['status']) ?></span></div>
          <div style="width:30px;text-align:right;color:var(--ink-4);"><i class="bi bi-chevron-right"></i></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
