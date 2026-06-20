<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_once __DIR__ . '/../../config/projects.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$type = 'invoice';
$msg  = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_invoice') {
        $items = [];
        $descs  = $_POST['item_desc']  ?? [];
        $qtys   = $_POST['item_qty']   ?? [];
        $prices = $_POST['item_price'] ?? [];
        foreach ($descs as $i => $desc) {
            if (trim($desc) === '') continue;
            $items[] = ['description' => $desc, 'quantity' => $qtys[$i] ?? 1, 'unit_price' => $prices[$i] ?? 0];
        }
        $id = (int)($_POST['doc_id'] ?? 0);
        try {
            $newId = doc_save($uid, $type, $_POST, $items, $id > 0 ? $id : null);
            $_SESSION['flash'] = $id > 0 ? 'Invoice updated.' : 'Invoice created.';
        } catch (Throwable $e) {
            $_SESSION['flash'] = 'Error saving invoice: ' . $e->getMessage();
        }
        header('Location: /pages/dashboard/invoices.php');
        exit;
    }

    if ($action === 'set_status') {
        $id     = (int)($_POST['doc_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        doc_set_status($uid, $id, $status);
        $_SESSION['flash'] = 'Invoice status updated.';
        header('Location: /pages/dashboard/invoices.php');
        exit;
    }

    if ($action === 'delete_invoice') {
        $id = (int)($_POST['doc_id'] ?? 0);
        doc_delete($uid, $id);
        $_SESSION['flash'] = 'Invoice deleted.';
        header('Location: /pages/dashboard/invoices.php');
        exit;
    }
}

// Flash
if (!empty($_SESSION['flash'])) { $msg = $_SESSION['flash']; unset($_SESSION['flash']); }

// Filters
$q_filter  = trim($_GET['q'] ?? '');
$st_filter = trim($_GET['status'] ?? '');

$invoices   = doc_all($uid, $type, $q_filter, $st_filter);
$stats      = doc_stats($uid, $type);
$statuses   = doc_statuses($type);
$clients    = client_all($uid);
$projects_l = doc_user_projects($uid);

function inv_badge_html2(string $s): string {
    $map = ['paid'=>['Paid','#166534','#dcfce7'],'sent'=>['Sent','#1e40af','#eff6ff'],'draft'=>['Draft','#6b6b6b','#f4f4f2'],'overdue'=>['Overdue','#b91c1c','#fef2f2'],'void'=>['Void','#9b9b9b','#f4f4f2']];
    [$l,$c,$b] = $map[$s] ?? [ucfirst($s),'#6b6b6b','#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:'.$c.';background:'.$b.';">'.$l.'</span>';
}

$sp           = 'invoices';
$topbar_title = 'Invoices';
$page_title   = 'Invoices';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
.kpi-tile{background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px;}
.kpi-val{font-size:1.45rem;font-weight:800;color:#0d0d0d;letter-spacing:-.04em;line-height:1.1;}
.kpi-lbl{font-size:.72rem;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em;margin-top:4px;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <?php if ($msg): ?>
    <div class="alert alert-success alert-sm py-2 px-3 mb-3" style="font-size:.85rem;">
      <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- KPI row -->
  <div class="kpi-grid">
    <div class="kpi-tile">
      <div class="kpi-val"><?= $stats['cnt'] ?></div>
      <div class="kpi-lbl">Total Invoices</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#16a34a;">KES <?= number_format((float)$stats['paid_value']) ?></div>
      <div class="kpi-lbl">Paid</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#1e40af;">KES <?= number_format((float)$stats['outstanding_value']) ?></div>
      <div class="kpi-lbl">Outstanding</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#b91c1c;">KES <?= number_format((float)$stats['overdue_value']) ?></div>
      <div class="kpi-lbl">Overdue</div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
      <input type="text" name="q" class="form-control form-control-sm"
             placeholder="Search invoices..." value="<?= htmlspecialchars($q_filter) ?>" style="width:200px;">
      <select name="status" class="form-select form-select-sm" style="width:140px;">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= $s ?>" <?= $st_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;">Filter</button>
      <?php if ($q_filter || $st_filter): ?>
        <a href="/pages/dashboard/invoices.php" class="btn btn-sm btn-light">Clear</a>
      <?php endif; ?>
    </form>
    <button class="bf-btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#invoiceModal">
      <i class="bi bi-plus-lg me-1"></i>New Invoice
    </button>
  </div>

  <!-- Invoices table -->
  <div class="bf-pf-card">
    <?php if (empty($invoices)): ?>
      <div class="text-center py-5">
        <i class="bi bi-receipt" style="font-size:2.5rem;color:var(--line);display:block;margin-bottom:12px;"></i>
        <p style="color:var(--ink-3);font-size:.9rem;margin:0;">No invoices yet.</p>
        <button class="bf-btn-dark mt-3" data-bs-toggle="modal" data-bs-target="#invoiceModal">
          <i class="bi bi-plus-lg me-1"></i>Create your first invoice
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--surface);border-bottom:1px solid var(--line);">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:10px 16px;">Number</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Client</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Subject</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Amount</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Status</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Date</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr>
              <td style="padding:12px 16px;font-weight:600;color:#1e3a5f;">
                <?= htmlspecialchars($inv['number']) ?>
              </td>
              <td>
                <div style="font-weight:500;"><?= htmlspecialchars($inv['client_name']) ?></div>
                <?php if ($inv['client_email']): ?>
                  <div style="font-size:.75rem;color:var(--ink-3);"><?= htmlspecialchars($inv['client_email']) ?></div>
                <?php endif; ?>
              </td>
              <td style="color:var(--ink-2);"><?= htmlspecialchars($inv['subject'] ?? '—') ?></td>
              <td style="font-weight:700;">
                <?= htmlspecialchars($inv['currency']) ?> <?= number_format((float)$inv['total'], 2) ?>
              </td>
              <td><?= inv_badge_html2($inv['status']) ?></td>
              <td style="color:var(--ink-3);"><?= $inv['issue_date'] ? date('d M Y', strtotime($inv['issue_date'])) : '—' ?></td>
              <td>
                <div class="d-flex gap-1">
                  <button class="btn btn-xs btn-light edit-inv-btn" style="font-size:.75rem;padding:3px 8px;"
                          data-inv='<?= htmlspecialchars(json_encode($inv), ENT_QUOTES) ?>'
                          data-bs-toggle="modal" data-bs-target="#invoiceModal">
                    Edit
                  </button>
                  <div class="dropdown">
                    <button class="btn btn-xs btn-light dropdown-toggle" style="font-size:.75rem;padding:3px 8px;" data-bs-toggle="dropdown">
                      Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="font-size:.8rem;">
                      <?php foreach ($statuses as $s): ?>
                        <li>
                          <form method="POST">
                            <input type="hidden" name="action" value="set_status">
                            <input type="hidden" name="doc_id" value="<?= (int)$inv['id'] ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($s) ?>">
                            <button type="submit" class="dropdown-item"><?= ucfirst($s) ?></button>
                          </form>
                        </li>
                      <?php endforeach; ?>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <form method="POST" onsubmit="return confirm('Delete this invoice?');">
                          <input type="hidden" name="action" value="delete_invoice">
                          <input type="hidden" name="doc_id" value="<?= (int)$inv['id'] ?>">
                          <button type="submit" class="dropdown-item text-danger">Delete</button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div><!-- /.bf-body -->
</div><!-- /.bf-main -->
</div><!-- /.bf-layout -->

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="invoiceForm">
        <input type="hidden" name="action" value="save_invoice">
        <input type="hidden" name="doc_id" id="modal_doc_id" value="">
        <input type="hidden" name="doc_type" value="invoice">

        <div class="modal-header" style="background:#0d0d0d;color:#fff;">
          <h5 class="modal-title" id="invoiceModalTitle" style="font-size:1rem;font-weight:700;">New Invoice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" style="background:#f8f8f6;">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Name *</label>
              <input type="text" name="client_name" id="m_client_name" class="form-control" placeholder="Jane Kamau" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Email</label>
              <input type="email" name="client_email" id="m_client_email" class="form-control" placeholder="client@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Phone</label>
              <input type="text" name="client_phone" id="m_client_phone" class="form-control" placeholder="+254...">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Subject</label>
              <input type="text" name="subject" id="m_subject" class="form-control" placeholder="Invoice for...">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Issue Date</label>
              <input type="date" name="issue_date" id="m_issue_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Due Date</label>
              <input type="date" name="due_date" id="m_due_date" class="form-control">
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency" id="m_currency" class="form-select">
                <?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $cur): ?>
                  <option value="<?= $cur ?>"><?= $cur ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Tax %</label>
              <input type="number" name="tax_rate" id="m_tax_rate" class="form-control" value="0" min="0" max="100" step="0.5">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Status</label>
              <select name="status" id="m_status" class="form-select">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if ($projects_l): ?>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Linked Project</label>
              <select name="project_id" id="m_project_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($projects_l as $p): ?>
                  <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <!-- Line items -->
          <div class="mt-4">
            <label class="form-label" style="font-size:.8rem;font-weight:700;">Line Items</label>
            <div id="lineItems">
              <div class="row g-2 mb-2 line-item-row align-items-end">
                <div class="col-5"><input type="text" name="item_desc[]" class="form-control form-control-sm" placeholder="Description *" required></div>
                <div class="col-2"><input type="number" name="item_qty[]" class="form-control form-control-sm" placeholder="Qty" value="1" min="0.001" step="any"></div>
                <div class="col-3"><input type="number" name="item_price[]" class="form-control form-control-sm" placeholder="Unit price" min="0" step="any"></div>
                <div class="col-2"><button type="button" class="btn btn-sm btn-light remove-line w-100" style="font-size:.75rem;">Remove</button></div>
              </div>
            </div>
            <button type="button" id="addLineItem" class="btn btn-sm" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;font-weight:600;margin-top:6px;">
              <i class="bi bi-plus me-1"></i>Add line
            </button>
          </div>

          <div class="mt-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Notes</label>
            <textarea name="notes" id="m_notes" class="form-control" rows="2" placeholder="Payment terms, bank details..."></textarea>
          </div>
        </div>

        <div class="modal-footer" style="background:#fff;">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-dark">Save Invoice</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extra_js = <<<'JS'
<script>
// Add line item
document.getElementById('addLineItem').addEventListener('click', function(){
  var tpl = `<div class="row g-2 mb-2 line-item-row align-items-end">
    <div class="col-5"><input type="text" name="item_desc[]" class="form-control form-control-sm" placeholder="Description *" required></div>
    <div class="col-2"><input type="number" name="item_qty[]" class="form-control form-control-sm" placeholder="Qty" value="1" min="0.001" step="any"></div>
    <div class="col-3"><input type="number" name="item_price[]" class="form-control form-control-sm" placeholder="Unit price" min="0" step="any"></div>
    <div class="col-2"><button type="button" class="btn btn-sm btn-light remove-line w-100" style="font-size:.75rem;">Remove</button></div>
  </div>`;
  document.getElementById('lineItems').insertAdjacentHTML('beforeend', tpl);
});
document.addEventListener('click', function(e){
  if(e.target.classList.contains('remove-line')){
    var row = e.target.closest('.line-item-row');
    if(document.querySelectorAll('.line-item-row').length > 1) row.remove();
  }
});
// Edit invoice button — populate modal
document.querySelectorAll('.edit-inv-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var inv = JSON.parse(this.dataset.inv);
    document.getElementById('modal_doc_id').value = inv.id;
    document.getElementById('invoiceModalTitle').textContent = 'Edit ' + inv.number;
    document.getElementById('m_client_name').value  = inv.client_name || '';
    document.getElementById('m_client_email').value = inv.client_email || '';
    document.getElementById('m_client_phone').value = inv.client_phone || '';
    document.getElementById('m_subject').value      = inv.subject || '';
    document.getElementById('m_issue_date').value   = inv.issue_date || '';
    document.getElementById('m_due_date').value     = inv.due_date || '';
    document.getElementById('m_currency').value     = inv.currency || 'KES';
    document.getElementById('m_tax_rate').value     = inv.tax_rate || '0';
    document.getElementById('m_status').value       = inv.status || 'draft';
    document.getElementById('m_notes').value        = inv.notes || '';
    var pid = document.getElementById('m_project_id');
    if(pid) pid.value = inv.project_id || '';
  });
});
// Reset modal on new invoice open
document.getElementById('invoiceModal').addEventListener('show.bs.modal', function(e){
  if(!e.relatedTarget || !e.relatedTarget.classList.contains('edit-inv-btn')){
    document.getElementById('modal_doc_id').value = '';
    document.getElementById('invoiceModalTitle').textContent = 'New Invoice';
    document.getElementById('invoiceForm').reset();
    document.getElementById('m_issue_date').value = new Date().toISOString().substr(0,10);
  }
});
</script>
JS;
?>
<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
