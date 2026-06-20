<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Providers';
$ap = 'providers';
$topbar_crumb = 'Marketplace';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'toggle_verified':
            db_stmt("UPDATE providers SET is_verified = 1 - is_verified WHERE id=?", [$id]);
            admin_audit('provider.verify.toggle', 'provider', $id); break;
        case 'toggle_featured':
            db_stmt("UPDATE providers SET is_featured = 1 - is_featured WHERE id=?", [$id]);
            admin_audit('provider.feature.toggle', 'provider', $id); break;
        case 'toggle_status':
            db_stmt("UPDATE providers SET status = IF(status='active','suspended','active') WHERE id=?", [$id]);
            admin_audit('provider.status.toggle', 'provider', $id); break;
    }
    $_SESSION['prov_flash'] = 'Provider updated.';
    header('Location: /admin/providers.php'); exit;
}
$msg = $_SESSION['prov_flash'] ?? ''; unset($_SESSION['prov_flash']);

$providers = db_all("SELECT p.*, v.name AS vertical_name FROM providers p LEFT JOIN verticals v ON v.id=p.vertical_id ORDER BY p.is_featured DESC, p.sort_order, p.name");
$cTotal = (int)db_value("SELECT COUNT(*) FROM providers");
$cVer   = (int)db_value("SELECT COUNT(*) FROM providers WHERE is_verified=1");
$cFeat  = (int)db_value("SELECT COUNT(*) FROM providers WHERE is_featured=1");
$cAvail = (int)db_value("SELECT COUNT(*) FROM providers WHERE is_available=1 AND status='active'");
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <div class="bf-dash-h">
        <div>
          <div class="bf-eyebrow2"><span>—</span> Marketplace</div>
          <h1 class="bf-dash-title">Providers</h1>
          <p class="bf-dash-sub">Verify, feature or suspend providers — changes show instantly on <a href="/pages/marketplace/professionals.php" target="_blank" style="color:#c0392b;">/professionals</a>.</p>
        </div>
      </div>

      <?php if ($msg): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:11px 15px;font-size:13px;margin-bottom:14px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([['Providers',$cTotal,'bi-person-badge','#1e3a5f','#eaf0f6'],['Verified',$cVer,'bi-patch-check','#166534','#f0fdf4'],['Featured',$cFeat,'bi-star','#9a7d27','#fdf6e3'],['Available',$cAvail,'bi-broadcast','#1e40af','#eff6ff']] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="flex:1;">Provider</div>
          <div style="width:120px;" class="d-none d-lg-block">Location</div>
          <div style="width:70px;">Rating</div>
          <div style="width:80px;" class="d-none d-md-block">Verified</div>
          <div style="width:80px;" class="d-none d-md-block">Featured</div>
          <div style="width:80px;">Status</div>
          <div style="width:240px;text-align:right;">Actions</div>
        </div>
        <?php foreach ($providers as $p): ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;display:flex;align-items:center;gap:11px;">
            <img src="<?= htmlspecialchars($p['photo_url'] ?: 'https://randomuser.me/api/portraits/men/45.jpg', ENT_QUOTES) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            <div style="min-width:0;"><div class="pri" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($p['headline'] ?? '') ?></div></div>
          </div>
          <div style="width:120px;font-size:11.5px;" class="d-none d-lg-block"><?= htmlspecialchars($p['location'] ?? '—') ?></div>
          <div style="width:70px;"><i class="bi bi-star-fill" style="color:#f59e0b;font-size:10px;"></i> <?= htmlspecialchars($p['rating']) ?></div>
          <div style="width:80px;" class="d-none d-md-block"><span class="bf-badge <?= $p['is_verified']?'green':'grey' ?>"><?= $p['is_verified']?'Yes':'No' ?></span></div>
          <div style="width:80px;" class="d-none d-md-block"><span class="bf-badge <?= $p['is_featured']?'amber':'grey' ?>"><?= $p['is_featured']?'Yes':'No' ?></span></div>
          <div style="width:80px;"><span class="bf-badge <?= $p['status']==='active'?'green':'red' ?>"><?= ucfirst($p['status']) ?></span></div>
          <div style="width:240px;text-align:right;display:flex;gap:5px;justify-content:flex-end;flex-wrap:wrap;">
            <a href="/pages/marketplace/professional.php?id=<?= (int)$p['id'] ?>" target="_blank" class="bf-badge navy" style="text-decoration:none;">View</a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="toggle_verified"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="bf-badge grey" style="border:none;cursor:pointer;"><?= $p['is_verified']?'Unverify':'Verify' ?></button></form>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="toggle_featured"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="bf-badge grey" style="border:none;cursor:pointer;"><?= $p['is_featured']?'Unfeature':'Feature' ?></button></form>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="bf-badge <?= $p['status']==='active'?'red':'green' ?>" style="border:none;cursor:pointer;"><?= $p['status']==='active'?'Suspend':'Activate' ?></button></form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
