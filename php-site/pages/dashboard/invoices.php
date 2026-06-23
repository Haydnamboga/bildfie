<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];
$page_title = 'Invoices'; $sp = 'invoices';

$flash = $_SESSION['doc_flash'] ?? null; unset($_SESSION['doc_flash']);
$q  = trim($_GET['q'] ?? '');
$st = trim($_GET['status'] ?? '');
$stats    = doc_stats($uid, 'invoice');
$invoices = doc_all($uid, 'invoice', $q, $st);
$cur = defined('CURRENCY') ? CURRENCY : 'KES';

$statusMap = [
  'paid'    => ['Paid','#166534','#f0fdf4'], 'sent'=>['Sent','#1e3a5f','#eaf0f6'],
  'overdue' => ['Overdue','#c0392b','#fef2f2'], 'draft'=>['Draft','#6b6b6b','#f4f4f2'],
  'void'    => ['Void','#c0392b','#fef2f2'],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">

    <?php $topbar_crumb='Sales'; $topbar_action='<a href="/pages/sales/document-edit.php?type=invoice" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>'; include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:28px;">

      <div class="mb-4">
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Billing</div>
        <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">Invoices</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Issue, track and reconcile payments across all your projects.</p>
      </div>

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <!-- stat cards -->
      <div class="row g-3 mb-4">
        <?php foreach ([
          ['Total billed', $cur.' '.number_format((float)$stats['total_value']),'bi-receipt-cutoff','#1e3a5f','#eaf0f6'],
          ['Outstanding',  $cur.' '.number_format((float)$stats['outstanding_value']),'bi-hourglass-split','#b45309','#fffbeb'],
          ['Paid',         $cur.' '.number_format((float)$stats['paid_value']),'bi-check2-circle','#166534','#f0fdf4'],
          ['Overdue',      $cur.' '.number_format((float)$stats['overdue_value']),'bi-exclamation-octagon','#c0392b','#fef2f2'],
        ] as [$label,$value,$icon,$iconCol,$iconBg]): ?>
        <div class="col-6 col-lg-3">
          <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;height:100%;">
            <div style="width:38px;height:38px;border-radius:10px;background:<?=$iconBg?>;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
              <i class="bi <?=$icon?>" style="font-size:17px;color:<?=$iconCol?>;"></i>
            </div>
            <div style="font-size:19px;font-weight:900;color:var(--ink);line-height:1;letter-spacing:-.02em;"><?=$value?></div>
            <div style="font-size:11px;color:var(--ink-3);margin-top:6px;"><?=$label?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- filters -->
      <form method="get" class="d-flex flex-wrap gap-2 mb-3">
        <div class="bf-login-field" style="flex:1;min-width:220px;max-width:340px;padding:0 12px;">
          <i class="bi bi-search"></i>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search invoices, clients…" style="padding:9px 0;font-size:13px;">
        </div>
        <select name="status" class="form-select" onchange="this.form.submit()" style="width:auto;font-size:12.5px;border-color:var(--line);border-radius:10px;color:var(--ink-2);">
          <option value="">All Status</option>
          <?php foreach (['paid'=>'Paid','sent'=>'Sent','overdue'=>'Overdue','draft'=>'Draft','void'=>'Void'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $st===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <!-- invoices table -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;">
        <div class="d-none d-lg-flex align-items-center" style="padding:11px 20px;background:var(--surface);font-size:9.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-4);">
          <div style="width:130px;">Invoice</div>
          <div style="flex:1;">Client / Project</div>
          <div style="width:130px;">Amount</div>
          <div style="width:110px;">Issued</div>
          <div style="width:110px;">Due</div>
          <div style="width:90px;">Status</div>
          <div style="width:40px;"></div>
        </div>

        <?php if (!$invoices): ?>
        <div style="padding:48px 20px;text-align:center;color:var(--ink-4);">
          <i class="bi bi-receipt" style="font-size:34px;opacity:.4;"></i>
          <p style="margin:12px 0 16px;font-size:13px;"><?= $q||$st ? 'No invoices match your filter.' : 'No invoices yet. Create your first one.' ?></p>
          <a href="/pages/sales/document-edit.php?type=invoice" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>
        </div>
        <?php endif; ?>

        <?php foreach ($invoices as $inv):
          [$sLabel,$sCol,$sBg] = $statusMap[$inv['status']] ?? ['—','#6b6b6b','#f4f4f2'];
          $issued = $inv['issue_date'] ? date('d M Y', strtotime($inv['issue_date'])) : '—';
          $due    = $inv['due_date']   ? date('d M Y', strtotime($inv['due_date']))   : '—'; ?>
        <div class="d-flex align-items-center gap-3" data-href="/pages/sales/document-view.php?id=<?= (int)$inv['id'] ?>" style="padding:14px 20px;border-top:1px solid var(--line-2);cursor:pointer;transition:background .15s;" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='transparent'">
          <div style="width:130px;font-size:12.5px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($inv['number']) ?></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($inv['client_name']) ?></div>
            <div style="font-size:11px;color:var(--ink-4);margin-top:2px;"><?= htmlspecialchars($inv['subject'] ?: '—') ?></div>
          </div>
          <div style="width:130px;font-size:13px;font-weight:800;color:var(--ink);" class="d-none d-lg-block"><?= $cur ?> <?= number_format((float)$inv['total']) ?></div>
          <div style="width:110px;font-size:11.5px;color:var(--ink-3);" class="d-none d-lg-block"><?= $issued ?></div>
          <div style="width:110px;font-size:11.5px;color:<?= $inv['status']==='overdue'?'#c0392b':'var(--ink-3)' ?>;font-weight:<?= $inv['status']==='overdue'?'700':'400' ?>;" class="d-none d-lg-block"><?= $due ?></div>
          <div style="width:90px;">
            <span style="font-size:10px;font-weight:700;color:<?=$sCol?>;background:<?=$sBg?>;padding:4px 10px;border-radius:6px;"><?=$sLabel?></span>
          </div>
          <div style="width:40px;text-align:right;" class="dropdown">
            <button class="btn btn-sm border-0 p-1" data-bs-toggle="dropdown" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:10px;">
              <li><a class="dropdown-item" href="/pages/sales/document-view.php?id=<?= (int)$inv['id'] ?>"><i class="bi bi-eye me-2 text-muted"></i>View</a></li>
              <li><a class="dropdown-item" href="/pages/sales/document-edit.php?id=<?= (int)$inv['id'] ?>"><i class="bi bi-pencil me-2 text-muted"></i>Edit</a></li>
              <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$inv['id'] ?>"><input type="hidden" name="value" value="sent"><button class="dropdown-item" type="submit"><i class="bi bi-send me-2 text-muted"></i>Mark as sent</button></form></li>
              <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$inv['id'] ?>"><input type="hidden" name="value" value="paid"><button class="dropdown-item" type="submit"><i class="bi bi-check2-circle me-2 text-muted"></i>Mark as paid</button></form></li>
              <li><hr class="dropdown-divider"></li>
              <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;" onsubmit="return confirm('Delete <?= htmlspecialchars($inv['number']) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$inv['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
            </ul>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
