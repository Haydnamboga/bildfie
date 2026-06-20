<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];
$type = 'estimate';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $items = [];
        foreach ($_POST['item_desc'] ?? [] as $i => $desc) {
            if (trim($desc) === '') continue;
            $items[] = ['description' => $desc, 'quantity' => $_POST['item_qty'][$i] ?? 1, 'unit_price' => $_POST['item_price'][$i] ?? 0];
        }
        $id = (int)($_POST['doc_id'] ?? 0);
        try {
            doc_save($uid, $type, $_POST, $items, $id > 0 ? $id : null);
            $_SESSION['flash'] = $id > 0 ? 'Estimate updated.' : 'Estimate created.';
        } catch (Throwable $e) {
            $_SESSION['flash'] = 'Error: ' . $e->getMessage();
        }
        header('Location: /pages/sales/estimates.php');
        exit;
    }
    if ($action === 'set_status') {
        doc_set_status($uid, (int)$_POST['doc_id'], $_POST['status'] ?? '');
        $_SESSION['flash'] = 'Status updated.';
        header('Location: /pages/sales/estimates.php'); exit;
    }
    if ($action === 'convert') {
        $newId = doc_convert($uid, (int)$_POST['doc_id'], 'invoice');
        $_SESSION['flash'] = $newId ? 'Converted to invoice!' : 'Could not convert.';
        header('Location: /pages/dashboard/invoices.php'); exit;
    }
    if ($action === 'delete') {
        doc_delete($uid, (int)$_POST['doc_id']);
        $_SESSION['flash'] = 'Estimate deleted.';
        header('Location: /pages/sales/estimates.php'); exit;
    }
}

if (!empty($_SESSION['flash'])) { $msg = $_SESSION['flash']; unset($_SESSION['flash']); }

$q_filter  = trim($_GET['q'] ?? '');
$st_filter = trim($_GET['status'] ?? '');
$docs      = doc_all($uid, $type, $q_filter, $st_filter);
$stats     = doc_stats($uid, $type);
$statuses  = doc_statuses($type);
$projects  = doc_user_projects($uid);

$sp           = 'estimates';
$topbar_title = 'Estimates';
$page_title   = 'Estimates';

function doc_status_badge(string $s): string {
    $map = ['draft'=>['Draft','#6b6b6b','#f4f4f2'],'sent'=>['Sent','#1e40af','#eff6ff'],'accepted'=>['Accepted','#166534','#dcfce7'],'declined'=>['Declined','#b91c1c','#fef2f2'],'expired'=>['Expired','#b45309','#fffbeb'],'invoiced'=>['Invoiced','#7c3aed','#f5f3ff'],'open'=>['Open','#1e40af','#eff6ff']];
    [$l,$c,$b] = $map[$s] ?? [ucfirst($s),'#6b6b6b','#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:'.$c.';background:'.$b.';">'.$l.'</span>';
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px;}
.kpi-tile{background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px;}
.kpi-val{font-size:1.35rem;font-weight:800;color:#0d0d0d;letter-spacing:-.04em;line-height:1.1;}
.kpi-lbl{font-size:.72rem;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em;margin-top:4px;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <?php if ($msg): ?><div class="alert alert-success py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <!-- KPI -->
  <div class="kpi-grid">
    <div class="kpi-tile"><div class="kpi-val"><?= $stats['cnt'] ?></div><div class="kpi-lbl">Total</div></div>
    <div class="kpi-tile"><div class="kpi-val" style="color:#16a34a;"><?= $stats['won_cnt'] ?></div><div class="kpi-lbl">Accepted</div></div>
    <div class="kpi-tile"><div class="kpi-val" style="color:#1e40af;"><?= $stats['open_cnt'] ?></div><div class="kpi-lbl">Pending</div></div>
    <div class="kpi-tile"><div class="kpi-val" style="font-size:1rem;">KES <?= number_format((float)$stats['pipeline_value']) ?></div><div class="kpi-lbl">Pipeline</div></div>
  </div>

  <!-- Toolbar -->
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..." value="<?= htmlspecialchars($q_filter) ?>" style="width:200px;">
      <select name="status" class="form-select form-select-sm" style="width:140px;">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= $s ?>" <?= $st_filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;">Filter</button>
      <?php if ($q_filter||$st_filter): ?><a href="/pages/sales/estimates.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
    <button class="bf-btn-dark" data-bs-toggle="modal" data-bs-target="#estModal"><i class="bi bi-plus-lg me-1"></i>New Estimate</button>
  </div>

  <div class="bf-pf-card">
    <?php if (empty($docs)): ?>
      <div class="text-center py-5">
        <i class="bi bi-calculator" style="font-size:2.5rem;color:var(--line);display:block;margin-bottom:12px;"></i>
        <p style="color:var(--ink-3);">No estimates yet.</p>
        <button class="bf-btn-dark" data-bs-toggle="modal" data-bs-target="#estModal"><i class="bi bi-plus-lg me-1"></i>Create first estimate</button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--surface);">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:10px 16px;">Number</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Client</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Amount</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Status</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Date</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($docs as $doc): ?>
            <tr>
              <td style="padding:11px 16px;font-weight:600;color:#1e3a5f;"><?= htmlspecialchars($doc['number']) ?></td>
              <td><?= htmlspecialchars($doc['client_name']) ?></td>
              <td style="font-weight:700;"><?= htmlspecialchars($doc['currency']) ?> <?= number_format((float)$doc['total'], 2) ?></td>
              <td><?= doc_status_badge($doc['status']) ?></td>
              <td style="color:var(--ink-3);"><?= $doc['issue_date'] ? date('d M Y', strtotime($doc['issue_date'])) : '—' ?></td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-xs btn-light dropdown-toggle" style="font-size:.75rem;padding:3px 8px;" data-bs-toggle="dropdown">Actions</button>
                  <ul class="dropdown-menu dropdown-menu-end" style="font-size:.8rem;">
                    <?php foreach ($statuses as $s): ?>
                      <li><form method="POST" style="margin:0;"><input type="hidden" name="action" value="set_status"><input type="hidden" name="doc_id" value="<?= (int)$doc['id'] ?>"><input type="hidden" name="status" value="<?= $s ?>"><button type="submit" class="dropdown-item">Mark <?= ucfirst($s) ?></button></form></li>
                    <?php endforeach; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" style="margin:0;"><input type="hidden" name="action" value="convert"><input type="hidden" name="doc_id" value="<?= (int)$doc['id'] ?>"><button type="submit" class="dropdown-item"><i class="bi bi-arrow-right me-2"></i>Convert to Invoice</button></form></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" onsubmit="return confirm('Delete?');" style="margin:0;"><input type="hidden" name="action" value="delete"><input type="hidden" name="doc_id" value="<?= (int)$doc['id'] ?>"><button type="submit" class="dropdown-item text-danger">Delete</button></form></li>
                  </ul>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="estModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="doc_id" value="">
        <div class="modal-header" style="background:#0d0d0d;color:#fff;">
          <h5 class="modal-title" style="font-size:1rem;font-weight:700;">New Estimate</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="background:#f8f8f6;">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label" style="font-size:.8rem;font-weight:600;">Client Name *</label><input type="text" name="client_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label" style="font-size:.8rem;font-weight:600;">Client Email</label><input type="email" name="client_email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label" style="font-size:.8rem;font-weight:600;">Subject</label><input type="text" name="subject" class="form-control"></div>
            <div class="col-md-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Issue Date</label><input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Valid Until</label><input type="date" name="due_date" class="form-control"></div>
            <div class="col-md-2"><label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency" class="form-select"><?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $c): ?><option><?= $c ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label" style="font-size:.8rem;font-weight:600;">Tax %</label><input type="number" name="tax_rate" class="form-control" value="0" min="0" max="100"></div>
            <div class="col-md-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Status</label>
              <select name="status" class="form-select"><?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
          </div>
          <div class="mt-4">
            <label class="form-label" style="font-size:.8rem;font-weight:700;">Line Items</label>
            <div id="estLineItems">
              <div class="row g-2 mb-2 line-item-row align-items-end">
                <div class="col-5"><input type="text" name="item_desc[]" class="form-control form-control-sm" placeholder="Description *" required></div>
                <div class="col-2"><input type="number" name="item_qty[]" class="form-control form-control-sm" value="1" min="0.001" step="any"></div>
                <div class="col-3"><input type="number" name="item_price[]" class="form-control form-control-sm" placeholder="Unit price" min="0" step="any"></div>
                <div class="col-2"><button type="button" class="btn btn-sm btn-light remove-line w-100" style="font-size:.75rem;">Remove</button></div>
              </div>
            </div>
            <button type="button" class="btn btn-sm" id="addEstLine" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;margin-top:6px;"><i class="bi bi-plus me-1"></i>Add line</button>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-dark">Save Estimate</button></div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('addEstLine').addEventListener('click', function(){
  document.getElementById('estLineItems').insertAdjacentHTML('beforeend',
    '<div class="row g-2 mb-2 line-item-row align-items-end"><div class="col-5"><input type="text" name="item_desc[]" class="form-control form-control-sm" placeholder="Description *" required></div><div class="col-2"><input type="number" name="item_qty[]" class="form-control form-control-sm" value="1" min="0.001" step="any"></div><div class="col-3"><input type="number" name="item_price[]" class="form-control form-control-sm" placeholder="Unit price" min="0" step="any"></div><div class="col-2"><button type="button" class="btn btn-sm btn-light remove-line w-100" style="font-size:.75rem;">Remove</button></div></div>');
});
document.addEventListener('click', function(e){ if(e.target.classList.contains('remove-line')){ var row=e.target.closest('.line-item-row'); if(document.querySelectorAll('.line-item-row').length>1) row.remove(); } });
</script>
<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
