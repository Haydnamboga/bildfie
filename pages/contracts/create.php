<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];
$id  = (int)($_REQUEST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid = contract_save($uid, [
        'title'        => $_POST['title'] ?? '', 'counterparty' => $_POST['counterparty'] ?? '',
        'value'        => $_POST['value'] ?? '', 'start_date'   => $_POST['start_date'] ?? '',
        'end_date'     => $_POST['end_date'] ?? '', 'body'       => $_POST['body'] ?? '',
        'status'       => $_POST['status'] ?? 'draft',
    ], $id ?: null);
    $_SESSION['ct_flash'] = ['ok', 'Contract saved.'];
    header('Location: /pages/contracts/view.php?id=' . $cid); exit;
}

$c = $id ? contract_get($uid, $id) : null;
if ($id && !$c) { header('Location: /pages/contracts/index.php'); exit; }

$page_title = $id ? 'Edit Contract' : 'Create Contract'; $sp = 'contracts';
$topbar_crumb = 'Contracts';
$cur = defined('CURRENCY') ? CURRENCY : 'KES';
$topbar_action = '<a href="/pages/contracts/index.php" class="bf-btn-ghost" style="text-decoration:none;border:1px solid var(--line);background:#fff;">Cancel</a>';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
     <div style="max-width:820px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Contracts</div>
        <h1 class="bf-dash-title"><?= $id ? 'Edit ' . htmlspecialchars($c['number']) : 'Create a new contract' ?></h1>
        <p class="bf-dash-sub">Draft an agreement, attach terms and track it through to signature.</p>
      </div></div>

      <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-file-earmark-ruled"></i> Contract details</div></div>
          <div class="bf-pf-card-b">
            <div class="row g-3">
              <div class="col-md-8"><label class="bf-f-lbl">Contract title *</label><input name="title" class="bf-f-input" value="<?= htmlspecialchars($c['title'] ?? '') ?>" placeholder="e.g. Main works contract — Westlands Office Block" required></div>
              <div class="col-md-4"><label class="bf-f-lbl">Status</label>
                <select name="status" class="bf-f-input">
                  <?php foreach (['draft'=>'Draft','sent'=>'Awaiting signature','signed'=>'Signed','active'=>'Active','expired'=>'Expired'] as $k=>$v): ?>
                  <option value="<?= $k ?>" <?= ($c['status'] ?? 'draft')===$k?'selected':'' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-8"><label class="bf-f-lbl">Client / counterparty *</label><input name="counterparty" class="bf-f-input" value="<?= htmlspecialchars($c['counterparty'] ?? '') ?>" placeholder="e.g. Skyline Developers" required></div>
              <div class="col-md-4"><label class="bf-f-lbl">Contract value (<?= $cur ?>)</label><input name="value" class="bf-f-input" value="<?= $c && $c['value']!==null ? htmlspecialchars((string)(0+$c['value'])) : '' ?>" placeholder="45000000"></div>
              <div class="col-md-4"><label class="bf-f-lbl">Start date</label><input name="start_date" type="date" class="bf-f-input" value="<?= htmlspecialchars($c['start_date'] ?? '') ?>"></div>
              <div class="col-md-4"><label class="bf-f-lbl">End date</label><input name="end_date" type="date" class="bf-f-input" value="<?= htmlspecialchars($c['end_date'] ?? '') ?>"></div>
              <div class="col-12"><label class="bf-f-lbl">Scope &amp; terms</label><textarea name="body" class="bf-f-input" rows="7" placeholder="Describe the scope of works, payment schedule, milestones, retention, defects liability period…"><?= htmlspecialchars($c['body'] ?? '') ?></textarea></div>
            </div>
          </div>
          <div class="bf-pf-card-b" style="padding-top:0;display:flex;gap:10px;justify-content:flex-end;">
            <a href="/pages/contracts/index.php" class="bf-btn-ghost" style="text-decoration:none;border:1px solid var(--line);background:#fff;">Cancel</a>
            <button type="submit" class="bf-btn-accent" style="border:none;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Save contract</button>
          </div>
        </div>
      </form>
     </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
