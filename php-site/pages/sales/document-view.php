<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];
$me  = current_user();

$id  = (int)($_GET['id'] ?? 0);
$doc = doc_get($uid, $id);
if (!$doc) { header('Location: /pages/dashboard/index.php'); exit; }
$type  = $doc['doc_type'];
$items = doc_items($id);
$project = $doc['project_id'] ? db_one("SELECT name FROM projects WHERE id=? AND owner_user_id=?", [(int)$doc['project_id'], $uid]) : null;
$label = doc_label($type);
$sp = doc_sp($type);
$cur = $doc['currency'] ?: 'KES';
$page_title   = $doc['number'];
$topbar_crumb = $label . 's';

$flash = $_SESSION['doc_flash'] ?? null; unset($_SESSION['doc_flash']);

$statusColor = [
  'paid'=>['#166534','#f0fdf4'],'accepted'=>['#166534','#f0fdf4'],'applied'=>['#166534','#f0fdf4'],
  'sent'=>['#1e3a5f','#eaf0f6'],'open'=>['#1e40af','#eff6ff'],'invoiced'=>['#1e40af','#eff6ff'],'issued'=>['#1e40af','#eff6ff'],
  'overdue'=>['#c0392b','#fef2f2'],'declined'=>['#c0392b','#fef2f2'],'void'=>['#c0392b','#fef2f2'],'expired'=>['#b45309','#fffbeb'],
  'draft'=>['#6b6b6b','#f4f4f2'],
];
[$scol,$sbg] = $statusColor[$doc['status']] ?? ['#6b6b6b','#f4f4f2'];

$quickStatus = [
  'invoice'     => ['sent'=>'Mark as sent','paid'=>'Mark as paid','overdue'=>'Mark overdue','void'=>'Void'],
  'estimate'    => ['sent'=>'Mark as sent','accepted'=>'Mark accepted','declined'=>'Mark declined','expired'=>'Mark expired'],
  'proposal'    => ['sent'=>'Mark as sent','accepted'=>'Mark accepted','declined'=>'Mark declined'],
  'credit_note' => ['issued'=>'Mark issued','applied'=>'Mark applied','void'=>'Void'],
][$type] ?? [];
$convertTo = ['estimate'=>'invoice','proposal'=>'estimate'][$type] ?? null;

$topbar_action = '<a href="/pages/sales/document-edit.php?id=' . $id . '" class="bf-topbar-new" style="text-decoration:none;"><i class="bi bi-pencil me-1"></i>Edit</a>';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;max-width:900px;">

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <!-- action bar -->
      <div class="d-flex flex-wrap gap-2 align-items-center mb-3" style="padding-bottom:14px;border-bottom:1px solid var(--line);">
        <a href="<?= doc_list_url($type) ?>" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;text-decoration:none;"><i class="bi bi-arrow-left"></i></a>
        <a href="/pages/sales/document-edit.php?id=<?= $id ?>" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;text-decoration:none;"><i class="bi bi-pencil me-1"></i>Edit</a>
        <button onclick="window.print()" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-download me-1"></i>Download / Print</button>

        <div class="dropdown">
          <button class="bf-btn-ghost dropdown-toggle" data-bs-toggle="dropdown" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-flag me-1"></i>Status</button>
          <ul class="dropdown-menu shadow border-0" style="font-size:13px;border-radius:10px;">
            <?php foreach ($quickStatus as $val=>$lbl): ?>
            <li><form method="post" action="/pages/sales/document-action.php" style="margin:0;">
              <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="value" value="<?= $val ?>">
              <button type="submit" class="dropdown-item"><?= $lbl ?></button>
            </form></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <?php if ($convertTo): ?>
        <form method="post" action="/pages/sales/document-action.php" style="margin:0;" onsubmit="return confirm('Create a new <?= $convertTo ?> from this <?= strtolower($label) ?>?');">
          <input type="hidden" name="action" value="convert"><input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;cursor:pointer;"><i class="bi bi-arrow-right-circle me-1"></i>Convert to <?= $convertTo ?></button>
        </form>
        <?php endif; ?>

        <form method="post" action="/pages/sales/document-action.php" style="margin:0 0 0 auto;" onsubmit="return confirm('Delete <?= $doc['number'] ?>? This cannot be undone.');">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="bf-btn-ghost text-danger" style="border:1px solid #f3c9c4;background:#fff;cursor:pointer;"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
      </div>

      <!-- the document -->
      <div id="docPaper" style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:34px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
          <div>
            <div style="font-size:22px;font-weight:900;color:#1e3a5f;letter-spacing:-.02em;">bildfie</div>
            <div style="font-size:12px;color:var(--ink-3);margin-top:2px;"><?= htmlspecialchars($me['name'] ?? '') ?></div>
            <div style="font-size:12px;color:var(--ink-4);"><?= htmlspecialchars($me['email'] ?? '') ?></div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:18px;font-weight:800;color:var(--ink);text-transform:uppercase;letter-spacing:.04em;"><?= $label ?></div>
            <div style="font-size:14px;font-weight:800;color:#1e3a5f;"><?= htmlspecialchars($doc['number']) ?></div>
            <span style="display:inline-block;margin-top:6px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:<?= $scol ?>;background:<?= $sbg ?>;padding:4px 10px;border-radius:6px;"><?= ucfirst($doc['status']) ?></span>
          </div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-3 mb-4" style="font-size:12.5px;">
          <div>
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:4px;">Bill to</div>
            <div style="font-weight:800;color:var(--ink);font-size:14px;"><?= htmlspecialchars($doc['client_name']) ?></div>
            <?php if ($doc['client_email']): ?><div style="color:var(--ink-3);"><?= htmlspecialchars($doc['client_email']) ?></div><?php endif; ?>
            <?php if ($doc['client_phone']): ?><div style="color:var(--ink-3);"><?= htmlspecialchars($doc['client_phone']) ?></div><?php endif; ?>
            <?php if ($doc['subject']): ?><div style="color:var(--ink-3);margin-top:3px;"><?= htmlspecialchars($doc['subject']) ?></div><?php endif; ?>
            <?php if ($project): ?><div style="color:var(--ink-3);margin-top:3px;"><i class="bi bi-folder2-open" style="font-size:11px;color:#1e3a5f;"></i> Project: <b style="color:var(--ink);"><?= htmlspecialchars($project['name']) ?></b></div><?php endif; ?>
          </div>
          <div style="text-align:right;color:var(--ink-3);">
            <?php if ($doc['issue_date']): ?><div>Issued: <b style="color:var(--ink);"><?= date('d M Y', strtotime($doc['issue_date'])) ?></b></div><?php endif; ?>
            <?php if ($doc['due_date']): ?><div><?= $type==='invoice'?'Due':'Valid until' ?>: <b style="color:var(--ink);"><?= date('d M Y', strtotime($doc['due_date'])) ?></b></div><?php endif; ?>
          </div>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:18px;">
          <thead><tr style="background:var(--surface);font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);text-align:left;">
            <th style="padding:9px 10px;border-radius:7px 0 0 7px;">Description</th>
            <th style="padding:9px 10px;text-align:right;">Qty</th>
            <th style="padding:9px 10px;text-align:right;">Unit price</th>
            <th style="padding:9px 10px;text-align:right;border-radius:0 7px 7px 0;">Amount</th>
          </tr></thead>
          <tbody>
            <?php if (!$items): ?>
            <tr><td colspan="4" style="padding:16px 10px;color:var(--ink-4);text-align:center;">No line items.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $it): ?>
            <tr style="border-bottom:1px solid var(--line-2);">
              <td style="padding:10px;color:var(--ink);"><?= htmlspecialchars($it['description']) ?></td>
              <td style="padding:10px;text-align:right;color:var(--ink-3);"><?= rtrim(rtrim(number_format((float)$it['quantity'],2),'0'),'.') ?></td>
              <td style="padding:10px;text-align:right;color:var(--ink-3);"><?= number_format((float)$it['unit_price']) ?></td>
              <td style="padding:10px;text-align:right;font-weight:700;color:var(--ink);"><?= number_format((float)$it['line_total']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="display:flex;justify-content:flex-end;">
          <div style="width:280px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;padding:5px 0;color:var(--ink-3);"><span>Subtotal</span><span><?= $cur ?> <?= number_format((float)$doc['subtotal']) ?></span></div>
            <?php if ((float)$doc['tax_rate'] > 0): ?>
            <div style="display:flex;justify-content:space-between;padding:5px 0;color:var(--ink-3);"><span>Tax (<?= rtrim(rtrim(number_format((float)$doc['tax_rate'],2),'0'),'.') ?>%)</span><span><?= $cur ?> <?= number_format((float)$doc['tax_amount']) ?></span></div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:2px solid var(--ink);font-weight:900;font-size:16px;color:var(--ink);"><span>Total</span><span><?= $cur ?> <?= number_format((float)$doc['total']) ?></span></div>
          </div>
        </div>

        <?php if ($doc['notes']): ?>
        <div style="margin-top:22px;padding-top:16px;border-top:1px solid var(--line-2);font-size:12px;color:var(--ink-3);line-height:1.6;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);font-weight:700;margin-bottom:5px;">Notes</div>
          <?= nl2br(htmlspecialchars($doc['notes'])) ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<style>@media print{.bf-sidebar,.bf-topbar,.bf-body>.d-flex.mb-3,.bf-body>div[data-ms]{display:none!important;}.bf-main,.bf-body{margin:0!important;padding:0!important;}#docPaper{border:none!important;}}</style>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
