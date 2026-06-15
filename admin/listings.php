<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Listings';
$ap = 'listings';
$topbar_crumb = 'Marketplace';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'toggle_verified': db_stmt("UPDATE listings SET is_verified = 1 - is_verified WHERE id=?", [$id]); admin_audit('listing.verify.toggle','listing',$id); break;
        case 'toggle_featured': db_stmt("UPDATE listings SET is_featured = 1 - is_featured WHERE id=?", [$id]); admin_audit('listing.feature.toggle','listing',$id); break;
        case 'toggle_status':   db_stmt("UPDATE listings SET status = IF(status='active','suspended','active') WHERE id=?", [$id]); admin_audit('listing.status.toggle','listing',$id); break;
    }
    $_SESSION['lst_flash'] = 'Listing updated.';
    header('Location: /admin/listings.php' . (!empty($_POST['kind']) ? '?kind=' . urlencode($_POST['kind']) : '')); exit;
}
$msg = $_SESSION['lst_flash'] ?? ''; unset($_SESSION['lst_flash']);

$kinds = ['materials'=>['Materials','#fdf2ee','#c0392b'],'equipment'=>['Equipment','#eff6ff','#1e40af'],'transport'=>['Transport','#f0fdf4','#166534'],'facilities'=>['Facilities','#fdf6e3','#9a7d27']];
$kind = $_GET['kind'] ?? '';
$where = "l.status<>'deleted'"; $params = [];
if (isset($kinds[$kind])) { $where .= " AND l.kind=?"; $params[] = $kind; }
$rows = db_all("SELECT l.*, s.name AS supplier_name FROM listings l LEFT JOIN suppliers s ON s.id=l.supplier_id WHERE $where ORDER BY l.kind, l.sort_order, l.id", $params);
$cTotal = (int) db_value("SELECT COUNT(*) FROM listings");
$cVer   = (int) db_value("SELECT COUNT(*) FROM listings WHERE is_verified=1");
$cFeat  = (int) db_value("SELECT COUNT(*) FROM listings WHERE is_featured=1");
$cSup   = (int) db_value("SELECT COUNT(*) FROM suppliers");
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <div class="adm-body">
      <div class="bf-dash-h">
        <div>
          <div class="bf-crumb"><a href="/admin/index.php">Dashboard</a> <i class="bi bi-chevron-right"></i> <span>Marketplace</span> <i class="bi bi-chevron-right"></i> <span style="color:var(--ink-2);">Listings</span></div>
          <h1 class="bf-dash-title">Marketplace listings</h1>
          <p class="bf-dash-sub">Materials, equipment, transport &amp; facilities — verify, feature or suspend supply.</p>
        </div>
      </div>

      <?php if ($msg): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:12px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([['Listings',$cTotal,'bi-box-seam','#1e3a5f','#eaf0f6'],['Verified',$cVer,'bi-patch-check','#166534','#f0fdf4'],['Featured',$cFeat,'bi-star','#9a7d27','#fdf6e3'],['Suppliers',$cSup,'bi-shop','#1e40af','#eff6ff']] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-2">
        <a href="/admin/listings.php" class="bf-btn-s <?= $kind===''?'solid':'ghost' ?>" style="text-decoration:none;">All</a>
        <?php foreach ($kinds as $k=>[$lbl,$bg,$c]): ?><a href="/admin/listings.php?kind=<?=$k?>" class="bf-btn-s <?= $kind===$k?'solid':'ghost' ?>" style="text-decoration:none;"><?=$lbl?></a><?php endforeach; ?>
      </div>

      <div class="bf-tbl-wrap compact">
        <div class="bf-tbl-head">
          <div style="flex:1;">Listing</div>
          <div style="width:90px;" class="d-none d-md-block">Kind</div>
          <div style="width:120px;" class="d-none d-lg-block">Price</div>
          <div style="width:62px;text-align:center;">Verified</div>
          <div style="width:62px;text-align:center;">Featured</div>
          <div style="width:62px;text-align:center;">Active</div>
        </div>
        <?php foreach ($rows as $r): [$klbl,$kbg,$kc] = $kinds[$r['kind']] ?? [$r['kind'],'#f4f4f2','#6b6b6b']; ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;"><div class="pri" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['title']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($r['supplier_name'] ?: $r['vendor_name'] ?: $r['category'] ?: '') ?></div></div>
          <div style="width:90px;" class="d-none d-md-block"><span class="bf-badge" style="background:<?=$kbg?>;color:<?=$kc?>;"><?=$klbl?></span></div>
          <div style="width:120px;font-size:11.5px;color:#c0392b;font-weight:700;" class="d-none d-lg-block"><?= htmlspecialchars($r['price'] ?? '—') ?></div>
          <div style="width:62px;text-align:center;"><form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_verified"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="kind" value="<?=$kind?>"><label class="bf-switch sm is-green"><input type="checkbox" onchange="this.form.submit()" <?= $r['is_verified']?'checked':'' ?>><span class="sl"></span></label></form></div>
          <div style="width:62px;text-align:center;"><form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_featured"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="kind" value="<?=$kind?>"><label class="bf-switch sm"><input type="checkbox" onchange="this.form.submit()" <?= $r['is_featured']?'checked':'' ?>><span class="sl"></span></label></form></div>
          <div style="width:62px;text-align:center;"><form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="kind" value="<?=$kind?>"><label class="bf-switch sm"><input type="checkbox" onchange="this.form.submit()" <?= $r['status']==='active'?'checked':'' ?>><span class="sl"></span></label></form></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
