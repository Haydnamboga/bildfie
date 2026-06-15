<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];

// ── actions (status / archive / delete) → redirect back ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $back = $_POST['back'] ?? '/pages/contracts/index.php';
    if ($id && contract_get($uid, $id)) {
        switch ($_POST['action'] ?? '') {
            case 'status':
                $v = $_POST['value'] ?? 'draft';
                if (in_array($v, ['draft','sent','signed','active','expired'], true)) {
                    contract_set_status($uid, $id, $v);
                    $_SESSION['ct_flash'] = ['ok', 'Contract marked as ' . $v . '.'];
                }
                break;
            case 'archive':
                contract_set_status($uid, $id, 'archived');
                $_SESSION['ct_flash'] = ['ok', 'Contract archived.'];
                $back = '/pages/contracts/index.php';
                break;
            case 'delete':
                contract_delete($uid, $id);
                $_SESSION['ct_flash'] = ['ok', 'Contract deleted.'];
                $back = '/pages/contracts/index.php';
                break;
        }
    }
    header('Location: ' . $back); exit;
}

$page_title = 'Contracts'; $sp = 'contracts';
$topbar_crumb = 'Billing';
$topbar_action = '<a href="/pages/contracts/create.php" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>Create Contract</a>';

$flash = $_SESSION['ct_flash'] ?? null; unset($_SESSION['ct_flash']);
$q  = trim($_GET['q'] ?? '');
$fs = trim($_GET['status'] ?? '');
$stats = contract_stats($uid);
$rows  = contract_all($uid, $q, $fs);
$cur = defined('CURRENCY') ? CURRENCY : 'KES';

$st = ['signed'=>['Signed','green'],'active'=>['Active','green'],'sent'=>['Awaiting sign','navy'],'draft'=>['Draft','grey'],'archived'=>['Archived','grey'],'expired'=>['Expired','red']];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Billing</div>
        <h1 class="bf-dash-title">Contracts</h1>
        <p class="bf-dash-sub">All client and subcontractor agreements — drafted, e-signed and tracked to expiry.</p>
      </div></div>

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Total contracts', number_format((int)$stats['cnt']),'bi-file-earmark-ruled','#1e3a5f','#eaf0f6'],
          ['Active',          number_format((int)$stats['active_cnt']),'bi-check2-circle','#166534','#f0fdf4'],
          ['Awaiting sign',   number_format((int)$stats['pending_cnt']),'bi-hourglass-split','#b45309','#fffbeb'],
          ['Active value',    $cur.' '.number_format((float)$stats['active_value']),'bi-cash-stack','#9a7d27','#fdf6e3'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?= $bg ?>;color:<?= $c ?>;"><i class="bi <?= $ic ?>"></i></div></div><div class="bf-kpi-v"><?= $v ?></div><div class="bf-kpi-l"><?= $l ?></div></div>
        <?php endforeach; ?>
      </div>

      <form method="get" class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div class="bf-topbar-search" style="max-width:320px;height:38px;"><i class="bi bi-search"></i><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search contracts…"></div>
        <select name="status" class="form-select" onchange="this.form.submit()" style="width:auto;font-size:12.5px;border-color:var(--line);border-radius:9px;">
          <option value="">All status</option>
          <?php foreach (['draft'=>'Draft','sent'=>'Awaiting sign','signed'=>'Signed','active'=>'Active','archived'=>'Archived','expired'=>'Expired'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $fs===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <a href="/pages/contracts/create.php" class="bf-btn-navy ms-auto" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New contract</a>
      </form>

      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="width:130px;">Contract</div><div style="flex:1;">Client / Title</div>
          <div style="width:110px;" class="d-none d-md-block">Value</div>
          <div style="width:170px;" class="d-none d-lg-block">Period</div>
          <div style="width:120px;">Status</div><div style="width:40px;"></div>
        </div>

        <?php if (!$rows): ?>
        <div style="padding:48px 20px;text-align:center;color:var(--ink-4);">
          <i class="bi bi-file-earmark-ruled" style="font-size:34px;opacity:.4;"></i>
          <p style="margin:12px 0 16px;font-size:13px;"><?= $q||$fs ? 'No contracts match your filter.' : 'No contracts yet. Draft your first agreement.' ?></p>
          <a href="/pages/contracts/create.php" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>Create Contract</a>
        </div>
        <?php endif; ?>

        <?php foreach ($rows as $r): [$sl,$bc] = $st[$r['status']] ?? ['—','grey'];
          $start = $r['start_date'] ? date('d M Y', strtotime($r['start_date'])) : '—';
          $end   = $r['end_date']   ? date('d M Y', strtotime($r['end_date']))   : '—'; ?>
        <div class="bf-tbl-row" data-href="/pages/contracts/view.php?id=<?= (int)$r['id'] ?>" style="cursor:pointer;">
          <div style="width:130px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($r['number']) ?></div>
          <div style="flex:1;min-width:0;"><div class="pri"><?= htmlspecialchars($r['counterparty']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($r['title']) ?></div></div>
          <div style="width:110px;font-weight:800;color:var(--ink);" class="d-none d-md-block"><?= $r['value']!==null ? $cur.' '.number_format((float)$r['value']) : '—' ?></div>
          <div style="width:170px;font-size:11.5px;" class="d-none d-lg-block"><?= $start ?> → <?= $end ?></div>
          <div style="width:120px;"><span class="bf-badge <?= $bc ?>"><?= $sl ?></span></div>
          <div style="width:40px;text-align:right;" class="dropdown">
            <button class="btn btn-sm border-0 p-1" data-bs-toggle="dropdown" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:10px;">
              <li><a class="dropdown-item" href="/pages/contracts/view.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-eye me-2 text-muted"></i>View</a></li>
              <li><a class="dropdown-item" href="/pages/contracts/create.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil me-2 text-muted"></i>Edit</a></li>
              <li><form method="post" style="margin:0;"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="value" value="sent"><button class="dropdown-item" type="submit"><i class="bi bi-vector-pen me-2 text-muted"></i>Send for e-sign</button></form></li>
              <li><form method="post" style="margin:0;"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="value" value="signed"><button class="dropdown-item" type="submit"><i class="bi bi-check2-circle me-2 text-muted"></i>Mark signed</button></form></li>
              <li><hr class="dropdown-divider"></li>
              <li><form method="post" style="margin:0;"><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-archive me-2"></i>Archive</button></form></li>
            </ul>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
