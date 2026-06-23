<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];
$me  = current_user();

$id = (int)($_GET['id'] ?? 0);
$c  = contract_get($uid, $id);
if (!$c) { header('Location: /pages/contracts/index.php'); exit; }

$page_title = $c['number']; $sp = 'contracts';
$topbar_crumb = 'Contracts';
$cur = $c['currency'] ?: 'KES';
$flash = $_SESSION['ct_flash'] ?? null; unset($_SESSION['ct_flash']);
$self = '/pages/contracts/view.php?id=' . $id;

$st = ['signed'=>['Signed','#166534','#f0fdf4'],'active'=>['Active','#166534','#f0fdf4'],'sent'=>['Awaiting signature','#1e3a5f','#eaf0f6'],'draft'=>['Draft','#6b6b6b','#f4f4f2'],'archived'=>['Archived','#6b6b6b','#f4f4f2'],'expired'=>['Expired','#c0392b','#fef2f2']];
[$sl,$scol,$sbg] = $st[$c['status']] ?? ['—','#6b6b6b','#f4f4f2'];

$topbar_action = '<a href="/pages/contracts/create.php?id=' . $id . '" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-pencil me-1"></i>Edit</a>';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;max-width:860px;">

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2 align-items-center mb-3" style="padding-bottom:14px;border-bottom:1px solid var(--line);">
        <a href="/pages/contracts/index.php" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;text-decoration:none;"><i class="bi bi-arrow-left"></i></a>
        <a href="/pages/contracts/create.php?id=<?= $id ?>" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;text-decoration:none;"><i class="bi bi-pencil me-1"></i>Edit</a>
        <button onclick="window.print()" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-download me-1"></i>Download / Print</button>
        <form method="post" action="/pages/contracts/index.php" style="margin:0;">
          <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="value" value="sent"><input type="hidden" name="back" value="<?= $self ?>">
          <button class="bf-btn-ghost" type="submit" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-vector-pen me-1"></i>Send for e-sign</button>
        </form>
        <form method="post" action="/pages/contracts/index.php" style="margin:0;">
          <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="value" value="signed"><input type="hidden" name="back" value="<?= $self ?>">
          <button class="bf-btn-ghost" type="submit" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-check2-circle me-1"></i>Mark signed</button>
        </form>
        <form method="post" action="/pages/contracts/index.php" style="margin:0 0 0 auto;" onsubmit="return confirm('Archive this contract?');">
          <input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= $id ?>">
          <button class="bf-btn-ghost text-danger" type="submit" style="border:1px solid #f3c9c4;background:#fff;cursor:pointer;"><i class="bi bi-archive me-1"></i>Archive</button>
        </form>
      </div>

      <div id="docPaper" style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:34px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
          <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;">Contract</div>
            <div style="font-size:20px;font-weight:900;color:var(--ink);margin-top:2px;"><?= htmlspecialchars($c['title']) ?></div>
            <div style="font-size:13px;font-weight:800;color:#1e3a5f;margin-top:3px;"><?= htmlspecialchars($c['number']) ?></div>
          </div>
          <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:<?= $scol ?>;background:<?= $sbg ?>;padding:5px 12px;border-radius:6px;"><?= $sl ?></span>
        </div>

        <div class="row g-3 mb-4" style="font-size:12.5px;">
          <div class="col-sm-6">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:3px;">Parties</div>
            <div style="color:var(--ink);"><b><?= htmlspecialchars($me['name'] ?? '') ?></b> (you)</div>
            <div style="color:var(--ink);"><b><?= htmlspecialchars($c['counterparty']) ?></b> (counterparty)</div>
          </div>
          <div class="col-sm-3">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:3px;">Value</div>
            <div style="color:var(--ink);font-weight:800;font-size:15px;"><?= $c['value']!==null ? $cur.' '.number_format((float)$c['value']) : '—' ?></div>
          </div>
          <div class="col-sm-3">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:3px;">Period</div>
            <div style="color:var(--ink);"><?= $c['start_date'] ? date('d M Y', strtotime($c['start_date'])) : '—' ?></div>
            <div style="color:var(--ink-3);">to <?= $c['end_date'] ? date('d M Y', strtotime($c['end_date'])) : '—' ?></div>
          </div>
        </div>

        <div style="border-top:1px solid var(--line-2);padding-top:16px;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:8px;">Scope &amp; terms</div>
          <div style="font-size:13px;color:var(--ink-2);line-height:1.7;"><?= $c['body'] ? nl2br(htmlspecialchars($c['body'])) : '<span style="color:var(--ink-4);">No terms recorded yet — use Edit to add the scope of works.</span>' ?></div>
        </div>

        <?php if ($c['signed_at']): ?>
        <div style="margin-top:20px;padding:12px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:12px;color:#166534;">
          <i class="bi bi-patch-check-fill"></i> Signed on <?= date('d M Y \a\t H:i', strtotime($c['signed_at'])) ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<style>@media print{.bf-sidebar,.bf-topbar,.bf-body>.d-flex,.bf-body>div[data-ms]{display:none!important;}.bf-main,.bf-body{margin:0!important;padding:0!important;}#docPaper{border:none!important;}}</style>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
