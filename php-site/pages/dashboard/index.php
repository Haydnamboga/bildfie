<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';
require_once __DIR__ . '/../../config/sales.php';

require_login();
$u  = current_user();
$uid = (int) $u['id'];

// KPI data
$projects     = user_projects($uid);
$proj_count   = count($projects);
$recent_projs = array_slice($projects, 0, 5);

$inv_stats    = doc_stats($uid, 'invoice');
$inv_count    = (int) $inv_stats['cnt'];

$wallet       = user_wallet($uid);
$wallet_bal   = number_format((float) $wallet['balance'], 2);
$wallet_cur   = $wallet['currency_code'] ?? 'KES';

$bids_count   = (int) db_value("SELECT COUNT(*) FROM provider_engagements pe JOIN providers p ON p.id=pe.provider_id WHERE p.user_id=?", [$uid]);

$recent_invs  = doc_all($uid, 'invoice', '', '');
$recent_invs  = array_slice($recent_invs, 0, 5);

// Greeting
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$first_name = explode(' ', $u['name'])[0];

$sp            = 'dashboard';
$topbar_title  = 'Dashboard';
$page_title    = 'Dashboard';

function status_badge_html(string $s): string {
    $defs = project_status_defs();
    $key  = project_status_key($s);
    [$label, $color, $bg] = $defs[$key] ?? ['Unknown', '#6b6b6b', '#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;color:' . $color . ';background:' . $bg . ';">' . htmlspecialchars($label) . '</span>';
}
function inv_badge_html(string $s): string {
    $map = ['paid'=>['Paid','#166534','#dcfce7'],'sent'=>['Sent','#1e40af','#eff6ff'],'draft'=>['Draft','#6b6b6b','#f4f4f2'],'overdue'=>['Overdue','#b91c1c','#fef2f2'],'void'=>['Void','#9b9b9b','#f4f4f2']];
    [$l,$c,$b] = $map[$s] ?? [ucfirst($s),'#6b6b6b','#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;color:'.$c.';background:'.$b.';">'.$l.'</span>';
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout { display:flex;min-height:100vh; }
.bf-main { flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w); }
@media(max-width:991px){ .bf-main { margin-left:0; } }
.bf-body { flex:1;padding:28px 24px;background:var(--surface); }
.kpi-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px; }
.kpi-tile { background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:6px; }
.kpi-val { font-size:1.7rem;font-weight:800;color:#0d0d0d;letter-spacing:-.04em;line-height:1; }
.kpi-lbl { font-size:.75rem;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em; }
.kpi-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:4px; }
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <!-- Header -->
  <div class="mb-4">
    <h2 style="font-size:1.4rem;font-weight:800;color:#0d0d0d;margin-bottom:2px;">
      <?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($first_name) ?> 👋
    </h2>
    <p style="font-size:.875rem;color:var(--ink-3);margin:0;">Here's what's happening on your account today.</p>
  </div>

  <!-- KPI row -->
  <div class="kpi-grid">
    <div class="kpi-tile">
      <div class="kpi-icon" style="background:#eaf0f6;"><i class="bi bi-kanban" style="color:#1e3a5f;font-size:1rem;"></i></div>
      <div class="kpi-val"><?= $proj_count ?></div>
      <div class="kpi-lbl">My Projects</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-icon" style="background:#f0fdf4;"><i class="bi bi-receipt" style="color:#16a34a;font-size:1rem;"></i></div>
      <div class="kpi-val"><?= $inv_count ?></div>
      <div class="kpi-lbl">Invoices</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-icon" style="background:#fffbeb;"><i class="bi bi-wallet2" style="color:#b45309;font-size:1rem;"></i></div>
      <div class="kpi-val" style="font-size:1.35rem;"><?= $wallet_cur ?> <?= $wallet_bal ?></div>
      <div class="kpi-lbl">Wallet Balance</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-icon" style="background:#faf5ff;"><i class="bi bi-inbox" style="color:#7c3aed;font-size:1rem;"></i></div>
      <div class="kpi-val"><?= $bids_count ?></div>
      <div class="kpi-lbl">Bids Received</div>
    </div>
  </div>

  <!-- Quick actions -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a href="/pages/projects/create.php" class="bf-btn-dark">
      <i class="bi bi-plus-lg me-1"></i>Post a Project
    </a>
    <a href="/pages/professionals/" class="bf-btn-outline">
      <i class="bi bi-search me-1"></i>Find Professionals
    </a>
    <a href="/pages/dashboard/invoices.php" class="bf-btn-outline">
      <i class="bi bi-receipt me-1"></i>Create Invoice
    </a>
  </div>

  <div class="row g-4">
    <!-- Recent Projects -->
    <div class="col-12 col-lg-6">
      <div class="bf-pf-card h-100">
        <div class="bf-pf-card-h d-flex align-items-center justify-content-between">
          <span class="bf-pf-card-t">Recent Projects</span>
          <a href="/pages/projects/" style="font-size:.8rem;color:#1e3a5f;">View all</a>
        </div>
        <?php if (empty($recent_projs)): ?>
          <div class="bf-pf-card-b text-center py-5">
            <i class="bi bi-kanban" style="font-size:2rem;color:var(--line);display:block;margin-bottom:10px;"></i>
            <p style="font-size:.875rem;color:var(--ink-3);margin:0;">No projects yet.</p>
            <a href="/pages/projects/create.php" class="bf-btn-dark mt-3 d-inline-flex">Post your first project</a>
          </div>
        <?php else: ?>
          <div class="bf-pf-card-b p-0">
            <?php foreach ($recent_projs as $p): ?>
              <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--line-2);">
                <div style="width:32px;height:32px;border-radius:8px;background:#eaf0f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="bi bi-kanban" style="color:#1e3a5f;font-size:.85rem;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:.875rem;font-weight:600;color:#0d0d0d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <a href="/pages/projects/view.php?id=<?= urlencode($p['public_id']) ?>" style="color:inherit;text-decoration:none;">
                      <?= htmlspecialchars($p['name']) ?>
                    </a>
                  </div>
                  <div style="font-size:.75rem;color:var(--ink-3);">
                    <?= htmlspecialchars($p['type'] ?? 'Project') ?>
                    <?php if ($p['location']): ?> &middot; <?= htmlspecialchars($p['location']) ?><?php endif; ?>
                  </div>
                </div>
                <div><?= status_badge_html($p['status'] ?? 'draft') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Invoices -->
    <div class="col-12 col-lg-6">
      <div class="bf-pf-card h-100">
        <div class="bf-pf-card-h d-flex align-items-center justify-content-between">
          <span class="bf-pf-card-t">Recent Invoices</span>
          <a href="/pages/dashboard/invoices.php" style="font-size:.8rem;color:#1e3a5f;">View all</a>
        </div>
        <?php if (empty($recent_invs)): ?>
          <div class="bf-pf-card-b text-center py-5">
            <i class="bi bi-receipt" style="font-size:2rem;color:var(--line);display:block;margin-bottom:10px;"></i>
            <p style="font-size:.875rem;color:var(--ink-3);margin:0;">No invoices yet.</p>
          </div>
        <?php else: ?>
          <div class="bf-pf-card-b p-0">
            <?php foreach ($recent_invs as $inv): ?>
              <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--line-2);">
                <div style="flex:1;min-width:0;">
                  <div style="font-size:.875rem;font-weight:600;color:#0d0d0d;"><?= htmlspecialchars($inv['number']) ?></div>
                  <div style="font-size:.75rem;color:var(--ink-3);"><?= htmlspecialchars($inv['client_name']) ?></div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                  <div style="font-size:.875rem;font-weight:700;color:#0d0d0d;">
                    <?= htmlspecialchars($inv['currency']) ?> <?= number_format((float)$inv['total'], 2) ?>
                  </div>
                  <div><?= inv_badge_html($inv['status']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div><!-- /.bf-body -->
</div><!-- /.bf-main -->
</div><!-- /.bf-layout -->

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
