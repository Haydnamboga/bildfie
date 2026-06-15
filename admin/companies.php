<?php
require_once __DIR__ . '/config/admin.php';
require_once __DIR__ . '/../config/companies.php';
require_admin();
$page_title = 'Companies';
$ap = 'companies';
$topbar_crumb = 'CRM';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $adminId = (int)(current_admin()['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'toggle_verified':
            db_stmt("UPDATE companies SET is_verified = 1 - is_verified WHERE id=?", [$id]);
            admin_audit('company.verify.toggle', 'company', $id); break;
        case 'toggle_status':
            db_stmt("UPDATE companies SET status = IF(status='active','suspended','active') WHERE id=?", [$id]);
            admin_audit('company.status.toggle', 'company', $id); break;
        case 'affirm_member':
            company_member_affirm($id, $adminId);
            admin_audit('company.member.affirm', 'company_member', $id); break;
        case 'reject_member':
            company_member_reject($id);
            admin_audit('company.member.reject', 'company_member', $id); break;
    }
    $_SESSION['co_admin_flash'] = 'Saved.';
    header('Location: /admin/companies.php'); exit;
}
$msg = $_SESSION['co_admin_flash'] ?? ''; unset($_SESSION['co_admin_flash']);

$companies = db_all("SELECT c.*, (SELECT COUNT(*) FROM company_members m WHERE m.company_id=c.id AND m.status='affirmed' AND m.is_current=1) AS emp FROM companies c ORDER BY c.is_verified DESC, c.name");
$pending = company_pending_claims();
$cTotal    = (int) db_value("SELECT COUNT(*) FROM companies");
$cVerified = (int) db_value("SELECT COUNT(*) FROM companies WHERE is_verified=1");
$cEmp      = (int) db_value("SELECT COUNT(*) FROM company_members WHERE status='affirmed'");
$cPending  = count($pending);
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <div class="bf-dash-h">
        <div>
          <div class="bf-crumb"><a href="/admin/index.php">Dashboard</a> <i class="bi bi-chevron-right"></i> <span>CRM</span> <i class="bi bi-chevron-right"></i> <span style="color:var(--ink-2);">Companies</span></div>
          <h1 class="bf-dash-title">Companies &amp; organizations</h1>
          <p class="bf-dash-sub">Verify organizations and affirm employment claims — affirmed roles show on both profiles.</p>
        </div>
      </div>

      <?php if ($msg): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:12px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Companies',$cTotal,'bi-buildings','#1e3a5f','#eaf0f6'],
          ['Verified',$cVerified,'bi-patch-check','#166534','#f0fdf4'],
          ['Affirmed staff',$cEmp,'bi-person-check','#1e40af','#eff6ff'],
          ['Pending claims',$cPending,'bi-hourglass-split','#b45309','#fffbeb'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <!-- Pending employment claims -->
      <?php if ($pending): ?>
      <div style="font-size:13px;font-weight:800;color:var(--ink);margin:6px 0 8px;"><i class="bi bi-hourglass-split me-1" style="color:#b45309;"></i> Pending employment claims</div>
      <div class="bf-tbl-wrap compact mb-3">
        <div class="bf-tbl-head"><div style="flex:1;">Member</div><div style="width:200px;">Claims to be</div><div style="width:200px;" class="d-none d-md-block">At company</div><div style="width:150px;text-align:right;">Action</div></div>
        <?php foreach ($pending as $m): ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;"><div class="pri"><?= htmlspecialchars($m['user_name']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($m['user_email']) ?></div></div>
          <div style="width:200px;font-size:12px;"><?= htmlspecialchars($m['position'] ?: '—') ?></div>
          <div style="width:200px;font-size:12px;" class="d-none d-md-block"><?= htmlspecialchars($m['company_name']) ?></div>
          <div style="width:150px;text-align:right;display:flex;gap:5px;justify-content:flex-end;">
            <form method="post" style="display:inline;"><input type="hidden" name="action" value="affirm_member"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="bf-btn-s xs solid" style="background:#166534;"><i class="bi bi-check-lg"></i> Affirm</button></form>
            <form method="post" style="display:inline;" onsubmit="return confirm('Reject this employment claim?');"><input type="hidden" name="action" value="reject_member"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="bf-btn-s xs danger"><i class="bi bi-x-lg"></i></button></form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Companies -->
      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="flex:1;">Company</div>
          <div style="width:140px;" class="d-none d-lg-block">Industry</div>
          <div style="width:90px;text-align:center;" class="d-none d-md-block">Staff</div>
          <div style="width:74px;text-align:center;">Verified</div>
          <div style="width:74px;text-align:center;">Status</div>
          <div style="width:150px;text-align:right;">Actions</div>
        </div>
        <?php foreach ($companies as $c): ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;display:flex;align-items:center;gap:11px;">
            <img src="<?= htmlspecialchars(company_logo($c), ENT_QUOTES) ?>" style="width:34px;height:34px;border-radius:8px;object-fit:cover;border:1px solid var(--line);">
            <div style="min-width:0;"><div class="pri" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['name']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($c['hq_location'] ?? '') ?></div></div>
          </div>
          <div style="width:140px;font-size:11.5px;" class="d-none d-lg-block"><?= htmlspecialchars($c['industry'] ?? '—') ?></div>
          <div style="width:90px;text-align:center;font-weight:700;color:#1e3a5f;" class="d-none d-md-block"><?= (int)$c['emp'] ?></div>
          <div style="width:74px;text-align:center;">
            <form method="post"><input type="hidden" name="action" value="toggle_verified"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><label class="bf-switch sm is-green"><input type="checkbox" onchange="this.form.submit()" <?= (int)$c['is_verified']?'checked':'' ?>><span class="sl"></span></label></form>
          </div>
          <div style="width:74px;text-align:center;">
            <form method="post"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><label class="bf-switch sm"><input type="checkbox" onchange="this.form.submit()" <?= $c['status']==='active'?'checked':'' ?>><span class="sl"></span></label></form>
          </div>
          <div style="width:150px;text-align:right;">
            <a href="/pages/companies/view.php?slug=<?= urlencode($c['slug']) ?>" target="_blank" class="bf-btn-s xs solid" style="text-decoration:none;"><i class="bi bi-eye"></i> View</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
