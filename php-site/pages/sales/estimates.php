<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];
$page_title = 'Estimates'; $sp = 'estimates';
$topbar_crumb = 'Sales';
$topbar_action = '<a href="/pages/sales/document-edit.php?type=estimate" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New Estimate</a>';

$flash = $_SESSION['doc_flash'] ?? null; unset($_SESSION['doc_flash']);
$q  = trim($_GET['q'] ?? '');
$fs = trim($_GET['status'] ?? '');
$stats = doc_stats($uid, 'estimate');
$rows  = doc_all($uid, 'estimate', $q, $fs);
$cur = defined('CURRENCY') ? CURRENCY : 'KES';

$st = ['draft'=>['Draft','grey'],'sent'=>['Sent','navy'],'accepted'=>['Accepted','green'],'declined'=>['Declined','red'],'expired'=>['Expired','amber'],'invoiced'=>['Invoiced','blue']];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Sales</div>
        <h1 class="bf-dash-title">Estimates</h1>
        <p class="bf-dash-sub">Cost estimates and quotations — convert accepted estimates to invoices in one click.</p>
      </div></div>

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Total estimates', number_format((int)$stats['cnt']),'bi-calculator','#1e3a5f','#eaf0f6'],
          ['Awaiting reply',  number_format((int)$stats['open_cnt']),'bi-hourglass-split','#b45309','#fffbeb'],
          ['Accepted',        number_format((int)$stats['won_cnt']),'bi-check2-circle','#166534','#f0fdf4'],
          ['Pipeline value',  $cur.' '.number_format((float)$stats['pipeline_value']),'bi-cash-stack','#9a7d27','#fdf6e3'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?= $bg ?>;color:<?= $c ?>;"><i class="bi <?= $ic ?>"></i></div></div><div class="bf-kpi-v"><?= $v ?></div><div class="bf-kpi-l"><?= $l ?></div></div>
        <?php endforeach; ?>
      </div>

      <form method="get" class="d-flex flex-wrap gap-2 mb-3">
        <div class="bf-topbar-search" style="max-width:320px;height:38px;"><i class="bi bi-search"></i><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search estimates…"></div>
        <select name="status" class="form-select" onchange="this.form.submit()" style="width:auto;font-size:12.5px;border-color:var(--line);border-radius:9px;">
          <option value="">All status</option>
          <?php foreach (['draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted','declined'=>'Declined','expired'=>'Expired','invoiced'=>'Invoiced'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $fs===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="width:110px;">Estimate</div><div style="flex:1;">Client / Subject</div>
          <div style="width:120px;" class="d-none d-md-block">Amount</div><div style="width:110px;" class="d-none d-md-block">Date</div>
          <div style="width:100px;">Status</div><div style="width:40px;"></div>
        </div>

        <?php if (!$rows): ?>
        <div style="padding:48px 20px;text-align:center;color:var(--ink-4);">
          <i class="bi bi-calculator" style="font-size:34px;opacity:.4;"></i>
          <p style="margin:12px 0 16px;font-size:13px;"><?= $q||$fs ? 'No estimates match your filter.' : 'No estimates yet. Create your first one.' ?></p>
          <a href="/pages/sales/document-edit.php?type=estimate" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New Estimate</a>
        </div>
        <?php endif; ?>

        <?php foreach ($rows as $r): [$sl,$bc] = $st[$r['status']] ?? ['—','grey'];
          $date = $r['issue_date'] ? date('d M Y', strtotime($r['issue_date'])) : '—'; ?>
        <div class="bf-tbl-row" data-href="/pages/sales/document-view.php?id=<?= (int)$r['id'] ?>" style="cursor:pointer;">
          <div style="width:110px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($r['number']) ?></div>
          <div style="flex:1;min-width:0;"><div class="pri"><?= htmlspecialchars($r['client_name']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($r['subject'] ?: '—') ?></div></div>
          <div style="width:120px;font-weight:800;color:var(--ink);" class="d-none d-md-block"><?= $cur ?> <?= number_format((float)$r['total']) ?></div>
          <div style="width:110px;" class="d-none d-md-block"><?= $date ?></div>
          <div style="width:100px;"><span class="bf-badge <?= $bc ?>"><?= $sl ?></span></div>
          <div style="width:40px;text-align:right;" class="dropdown">
            <button class="btn btn-sm border-0 p-1" data-bs-toggle="dropdown" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:10px;">
              <li><a class="dropdown-item" href="/pages/sales/document-view.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-eye me-2 text-muted"></i>View</a></li>
              <li><a class="dropdown-item" href="/pages/sales/document-edit.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil me-2 text-muted"></i>Edit</a></li>
              <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;" onsubmit="return confirm('Create an invoice from this estimate?');"><input type="hidden" name="action" value="convert"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="dropdown-item" type="submit"><i class="bi bi-receipt me-2 text-muted"></i>Convert to invoice</button></form></li>
              <li><hr class="dropdown-divider"></li>
              <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;" onsubmit="return confirm('Delete <?= htmlspecialchars($r['number']) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
            </ul>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
