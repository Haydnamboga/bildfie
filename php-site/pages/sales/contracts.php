<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['contract_id'] ?? 0);
        try {
            contract_save($uid, $_POST, $id > 0 ? $id : null);
            $_SESSION['flash'] = $id > 0 ? 'Contract updated.' : 'Contract created.';
        } catch (Throwable $e) {
            $_SESSION['flash'] = 'Error: ' . $e->getMessage();
        }
        header('Location: /pages/sales/contracts.php'); exit;
    }

    if ($action === 'set_status') {
        contract_set_status($uid, (int)$_POST['contract_id'], $_POST['status'] ?? '');
        $_SESSION['flash'] = 'Status updated.';
        header('Location: /pages/sales/contracts.php'); exit;
    }

    if ($action === 'delete') {
        contract_delete($uid, (int)$_POST['contract_id']);
        $_SESSION['flash'] = 'Contract deleted.';
        header('Location: /pages/sales/contracts.php'); exit;
    }
}

if (!empty($_SESSION['flash'])) { $msg = $_SESSION['flash']; unset($_SESSION['flash']); }

$q_filter  = trim($_GET['q'] ?? '');
$st_filter = trim($_GET['status'] ?? '');
$contracts = contract_all($uid, $q_filter, $st_filter);
$stats     = contract_stats($uid);

$statuses = ['draft', 'sent', 'under_review', 'signed', 'active', 'expired', 'terminated', 'void'];

$sp           = 'contracts';
$topbar_title = 'Contracts';
$page_title   = 'Contracts';

function con_badge(string $s): string {
    $map = [
        'draft'        => ['Draft',        '#6b6b6b', '#f4f4f2'],
        'sent'         => ['Sent',          '#1e40af', '#eff6ff'],
        'under_review' => ['Under Review',  '#b45309', '#fffbeb'],
        'signed'       => ['Signed',        '#166534', '#dcfce7'],
        'active'       => ['Active',        '#15803d', '#f0fdf4'],
        'expired'      => ['Expired',       '#9b9b9b', '#f4f4f2'],
        'terminated'   => ['Terminated',    '#b91c1c', '#fef2f2'],
        'void'         => ['Void',          '#9b9b9b', '#f4f4f2'],
    ];
    [$l, $c, $b] = $map[$s] ?? [ucfirst(str_replace('_', ' ', $s)), '#6b6b6b', '#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:'.$c.';background:'.$b.';">'.htmlspecialchars($l).'</span>';
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
.kpi-val{font-size:1.3rem;font-weight:800;color:#0d0d0d;letter-spacing:-.04em;line-height:1.1;}
.kpi-lbl{font-size:.72rem;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em;margin-top:4px;}
.bf-pf-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <?php if ($msg): ?>
    <div class="alert alert-success py-2 px-3 mb-3" style="font-size:.85rem;">
      <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- KPI Row -->
  <div class="kpi-grid">
    <div class="kpi-tile">
      <div class="kpi-val"><?= (int)$stats['cnt'] ?></div>
      <div class="kpi-lbl">Total</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#16a34a;"><?= (int)$stats['active_cnt'] ?></div>
      <div class="kpi-lbl">Active / Signed</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#b45309;"><?= (int)$stats['pending_cnt'] ?></div>
      <div class="kpi-lbl">Pending</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="font-size:1rem;">KES <?= number_format((float)$stats['active_value']) ?></div>
      <div class="kpi-lbl">Active Value</div>
    </div>
  </div>

  <!-- Filter + New -->
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
      <input type="text" name="q" class="form-control form-control-sm"
             placeholder="Search contracts…" value="<?= htmlspecialchars($q_filter) ?>" style="width:220px;">
      <select name="status" class="form-select form-select-sm" style="width:150px;">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= htmlspecialchars($s) ?>" <?= $st_filter === $s ? 'selected' : '' ?>>
            <?= ucfirst(str_replace('_', ' ', $s)) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;">Filter</button>
      <?php if ($q_filter || $st_filter): ?>
        <a href="/pages/sales/contracts.php" class="btn btn-sm btn-light" style="border-radius:8px;">Clear</a>
      <?php endif; ?>
    </form>
    <button class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600;"
            data-bs-toggle="modal" data-bs-target="#conModal" id="btnNewCon">
      <i class="bi bi-plus-lg me-1"></i>New Contract
    </button>
  </div>

  <!-- Contracts Table -->
  <div class="bf-pf-card">
    <?php if (empty($contracts)): ?>
      <div class="text-center py-5">
        <i class="bi bi-file-earmark-ruled" style="font-size:2.5rem;color:var(--line);display:block;margin-bottom:12px;"></i>
        <p style="color:var(--ink-3);margin-bottom:16px;">No contracts yet. Start by creating your first contract.</p>
        <button class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;padding:6px 18px;font-weight:600;"
                data-bs-toggle="modal" data-bs-target="#conModal">
          <i class="bi bi-plus-lg me-1"></i>Create Contract
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--surface);">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:10px 16px;">Number</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Title</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Counterparty</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Value</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Status</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Start</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">End</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contracts as $con): ?>
            <tr>
              <td style="padding:11px 16px;font-weight:600;color:#1e3a5f;"><?= htmlspecialchars($con['number']) ?></td>
              <td>
                <button class="btn btn-link p-0 text-start" style="font-size:.875rem;color:#0d0d0d;font-weight:500;"
                        data-bs-toggle="modal" data-bs-target="#conModal"
                        data-con="<?= htmlspecialchars(json_encode($con), ENT_QUOTES) ?>">
                  <?= htmlspecialchars($con['title']) ?>
                </button>
              </td>
              <td style="color:var(--ink-2);"><?= htmlspecialchars($con['counterparty']) ?></td>
              <td style="font-weight:700;">
                <?php if ($con['value'] !== null): ?>
                  <?= htmlspecialchars($con['currency'] ?? 'KES') ?> <?= number_format((float)$con['value'], 2) ?>
                <?php else: ?>
                  <span style="color:var(--ink-4);">—</span>
                <?php endif; ?>
              </td>
              <td><?= con_badge($con['status'] ?? 'draft') ?></td>
              <td style="color:var(--ink-3);"><?= $con['start_date'] ? date('d M Y', strtotime($con['start_date'])) : '—' ?></td>
              <td style="color:var(--ink-3);"><?= $con['end_date'] ? date('d M Y', strtotime($con['end_date'])) : '—' ?></td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-xs btn-light dropdown-toggle" style="font-size:.75rem;padding:3px 8px;" data-bs-toggle="dropdown">Actions</button>
                  <ul class="dropdown-menu dropdown-menu-end" style="font-size:.8rem;">
                    <li>
                      <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#conModal"
                              data-con="<?= htmlspecialchars(json_encode($con), ENT_QUOTES) ?>">
                        <i class="bi bi-pencil me-2"></i>Edit
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <?php foreach ($statuses as $s): ?>
                    <li>
                      <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="contract_id" value="<?= (int)$con['id'] ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($s) ?>">
                        <button type="submit" class="dropdown-item">Mark <?= ucfirst(str_replace('_', ' ', $s)) ?></button>
                      </form>
                    </li>
                    <?php endforeach; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" onsubmit="return confirm('Delete this contract permanently?');" style="margin:0;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="contract_id" value="<?= (int)$con['id'] ?>">
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                      </form>
                    </li>
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

</div></div></div>

<!-- Contract Create / Edit Modal -->
<div class="modal fade" id="conModal" tabindex="-1" aria-labelledby="conModalLabel">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="conForm">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="contract_id" value="" id="conId">
        <div class="modal-header" style="background:#0d0d0d;color:#fff;">
          <h5 class="modal-title" id="conModalLabel" style="font-size:1rem;font-weight:700;">New Contract</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="background:#f8f8f6;">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Contract Title *</label>
              <input type="text" name="title" id="conTitle" class="form-control" required placeholder="e.g. Construction Services Agreement">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Status</label>
              <select name="status" id="conStatus" class="form-select">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= htmlspecialchars($s) ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Counterparty / Client *</label>
              <input type="text" name="counterparty" id="conCounterparty" class="form-control" required placeholder="Company or individual name">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Contract Value</label>
              <input type="number" name="value" id="conValue" class="form-control" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency" id="conCurrency" class="form-select">
                <?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $c): ?>
                  <option value="<?= $c ?>"><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Start Date</label>
              <input type="date" name="start_date" id="conStart" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">End Date</label>
              <input type="date" name="end_date" id="conEnd" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Contract Body / Terms</label>
              <textarea name="body" id="conBody" class="form-control" rows="10"
                        placeholder="Paste or type the contract terms, scope of work, payment schedule, and any special conditions here…"
                        style="font-size:.875rem;font-family:monospace;"></textarea>
              <div class="form-text" style="font-size:.75rem;">Plain text or basic HTML. This will be included when printing or sharing the contract.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" style="background:#1e3a5f;color:#fff;font-weight:600;border-radius:8px;">Save Contract</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $extra_js = <<<'JS'
<script>
(function () {
  var modal = document.getElementById('conModal');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    var raw = btn ? btn.getAttribute('data-con') : null;
    var label = document.getElementById('conModalLabel');
    var idEl  = document.getElementById('conId');
    var title = document.getElementById('conTitle');
    var status= document.getElementById('conStatus');
    var party = document.getElementById('conCounterparty');
    var val   = document.getElementById('conValue');
    var cur   = document.getElementById('conCurrency');
    var start = document.getElementById('conStart');
    var end   = document.getElementById('conEnd');
    var body  = document.getElementById('conBody');
    if (raw) {
      try {
        var c = JSON.parse(raw);
        label.textContent = 'Edit Contract';
        idEl.value   = c.id    || '';
        title.value  = c.title || '';
        party.value  = c.counterparty || '';
        val.value    = c.value !== null && c.value !== undefined ? c.value : '';
        cur.value    = c.currency || 'KES';
        start.value  = c.start_date || '';
        end.value    = c.end_date   || '';
        body.value   = c.body       || '';
        // set status
        var opts = status.options;
        for (var i = 0; i < opts.length; i++) {
          opts[i].selected = (opts[i].value === c.status);
        }
      } catch (ex) { console.error(ex); }
    } else {
      label.textContent = 'New Contract';
      idEl.value  = '';
      title.value = party.value = val.value = start.value = end.value = body.value = '';
      status.value = 'draft';
      cur.value    = 'KES';
    }
  });
})();
</script>
JS;
?>
<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
