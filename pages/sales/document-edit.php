<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];

$type = doc_type_norm($_REQUEST['type'] ?? 'invoice');
$id   = (int)($_REQUEST['id'] ?? 0);

// ── save (POST → redirect to the view) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descs = $_POST['desc'] ?? []; $qtys = $_POST['qty'] ?? []; $prices = $_POST['price'] ?? [];
    $items = [];
    foreach ($descs as $i => $d) {
        $items[] = ['description' => $d, 'quantity' => $qtys[$i] ?? 1, 'unit_price' => $prices[$i] ?? 0];
    }
    $clientId = client_find_or_create($uid, $_POST['client_name'] ?? '', $_POST['client_email'] ?? null, $_POST['client_phone'] ?? null);
    $docId = doc_save($uid, $type, [
        'client_name' => $_POST['client_name'] ?? '', 'client_id' => $clientId,
        'client_email' => $_POST['client_email'] ?? '', 'client_phone'=> $_POST['client_phone'] ?? '',
        'subject' => $_POST['subject'] ?? '', 'project_id' => $_POST['project_id'] ?? '',
        'issue_date'  => $_POST['issue_date'] ?? '', 'due_date' => $_POST['due_date'] ?? '',
        'tax_rate'    => $_POST['tax_rate'] ?? 0, 'notes' => $_POST['notes'] ?? '',
        'status'      => $_POST['status'] ?? 'draft', 'currency' => 'KES',
    ], $items, $id ?: null);
    $_SESSION['doc_flash'] = ['ok', doc_label($type) . ' saved.'];
    header('Location: /pages/sales/document-view.php?id=' . $docId); exit;
}

$doc = $id ? doc_get($uid, $id) : null;
if ($id && !$doc) { header('Location: ' . doc_list_url($type)); exit; }
if ($doc) $type = $doc['doc_type'];
$items = $id ? doc_items($id) : [];
$projects = doc_user_projects($uid);
$clients  = client_all($uid);
$clientEmails = []; foreach ($clients as $cl) $clientEmails[$cl['name']] = $cl['email'];

$label    = doc_label($type);
$statuses = doc_statuses($type);
$sp = doc_sp($type);
$cur = defined('CURRENCY') ? CURRENCY : 'KES';
$page_title   = ($id ? 'Edit ' : 'New ') . $label;
$topbar_crumb = 'Sales';
$topbar_action = '<button form="docForm" type="submit" class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Save ' . $label . '</button>';

function li_row(array $it = ['description'=>'','quantity'=>1,'unit_price'=>0]): void { ?>
  <tr class="li-row">
    <td><input name="desc[]" class="form-control bf-inp li-desc" value="<?= htmlspecialchars($it['description']) ?>" placeholder="Describe the work or item"></td>
    <td style="width:88px;"><input name="qty[]" type="number" step="any" class="form-control bf-inp li-qty" value="<?= htmlspecialchars((string)$it['quantity']) ?>" oninput="recompute()"></td>
    <td style="width:132px;"><input name="price[]" type="number" step="0.01" class="form-control bf-inp li-price" value="<?= htmlspecialchars((string)$it['unit_price']) ?>" oninput="recompute()"></td>
    <td style="width:120px;text-align:right;font-weight:700;color:var(--ink);" class="li-amt">0</td>
    <td style="width:34px;"><button type="button" class="btn btn-sm text-danger p-0" onclick="delRow(this)" title="Remove"><i class="bi bi-x-lg"></i></button></td>
  </tr>
<?php }
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;max-width:920px;">

      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> <a href="<?= doc_list_url($type) ?>" style="color:inherit;text-decoration:none;"><?= $label ?>s</a></div>
        <h1 class="bf-dash-title"><?= $id ? 'Edit ' . htmlspecialchars($doc['number']) : 'New ' . $label ?></h1>
      </div></div>

      <form id="docForm" method="post">
        <input type="hidden" name="type" value="<?= $type ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px;">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="bf-lbl">Client / company *</label>
              <input name="client_name" required list="clientList" autocomplete="off" class="form-control bf-inp" value="<?= htmlspecialchars($doc['client_name'] ?? '') ?>" placeholder="Start typing — past clients suggest" oninput="clientPick(this.value)">
              <datalist id="clientList"><?php foreach ($clients as $cl): ?><option value="<?= htmlspecialchars($cl['name'], ENT_QUOTES) ?>"></option><?php endforeach; ?></datalist>
              <?php if ($clients): ?><div style="font-size:10.5px;color:var(--ink-4);margin-top:3px;"><i class="bi bi-people"></i> <?= count($clients) ?> saved client<?= count($clients)>1?'s':'' ?> — new names are saved automatically.</div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="bf-lbl">Client email</label>
              <input name="client_email" id="clientEmail" type="email" class="form-control bf-inp" value="<?= htmlspecialchars($doc['client_email'] ?? '') ?>" placeholder="billing@client.com">
            </div>
            <div class="col-md-6">
              <label class="bf-lbl">Subject</label>
              <input name="subject" class="form-control bf-inp" value="<?= htmlspecialchars($doc['subject'] ?? '') ?>" placeholder="What is this <?= strtolower($label) ?> for?">
            </div>
            <div class="col-md-6">
              <label class="bf-lbl">Linked project</label>
              <select name="project_id" class="form-select bf-inp">
                <option value="">— None —</option>
                <?php foreach ($projects as $pr): ?>
                <option value="<?= (int)$pr['id'] ?>" <?= (int)($doc['project_id'] ?? 0) === (int)$pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="bf-lbl">Issue date</label>
              <input name="issue_date" type="date" class="form-control bf-inp" value="<?= htmlspecialchars($doc['issue_date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3">
              <label class="bf-lbl"><?= $type==='invoice'?'Due date':'Valid until' ?></label>
              <input name="due_date" type="date" class="form-control bf-inp" value="<?= htmlspecialchars($doc['due_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="bf-lbl">Status</label>
              <select name="status" class="form-select bf-inp">
                <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= ($doc['status'] ?? 'draft')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="bf-lbl">Tax / VAT (%)</label>
              <input name="tax_rate" id="taxRate" type="number" step="0.01" min="0" class="form-control bf-inp" value="<?= htmlspecialchars((string)($doc['tax_rate'] ?? 0)) ?>" oninput="recompute()">
            </div>
          </div>
        </div>

        <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px;">
          <div style="font-size:12px;font-weight:800;color:var(--ink);margin-bottom:10px;">Line items</div>
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead><tr style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);text-align:left;">
              <th style="padding:4px 6px;">Description</th><th style="padding:4px 6px;">Qty</th>
              <th style="padding:4px 6px;">Unit price</th><th style="padding:4px 6px;text-align:right;">Amount</th><th></th>
            </tr></thead>
            <tbody id="liBody">
              <?php if ($items) foreach ($items as $it) li_row($it); else li_row(); ?>
            </tbody>
          </table>
          <button type="button" class="bf-btn-ghost mt-2" onclick="addRow()" style="border:1px dashed var(--line);background:#fff;font-size:12px;"><i class="bi bi-plus-lg me-1"></i>Add line</button>

          <div style="display:flex;justify-content:flex-end;margin-top:16px;">
            <div style="width:260px;font-size:13px;">
              <div style="display:flex;justify-content:space-between;padding:5px 0;color:var(--ink-3);"><span>Subtotal</span><span id="sumSub">0</span></div>
              <div style="display:flex;justify-content:space-between;padding:5px 0;color:var(--ink-3);"><span>Tax</span><span id="sumTax">0</span></div>
              <div style="display:flex;justify-content:space-between;padding:9px 0;border-top:1px solid var(--line);font-weight:800;font-size:15px;color:var(--ink);"><span>Total (<?= $cur ?>)</span><span id="sumTot">0</span></div>
            </div>
          </div>
        </div>

        <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px;">
          <label class="bf-lbl">Notes / terms</label>
          <textarea name="notes" rows="3" class="form-control bf-inp" placeholder="Payment terms, bank details, thank-you note…"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Save <?= $label ?></button>
          <a href="<?= doc_list_url($type) ?>" class="bf-btn-ghost" style="border:1px solid var(--line);background:#fff;text-decoration:none;">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.bf-lbl{font-size:11.5px;font-weight:700;color:var(--ink-2);display:block;margin-bottom:5px;}
.bf-inp{font-size:13px;border-color:var(--line);border-radius:9px;padding:9px 12px;}
#liBody td{padding:4px 6px;vertical-align:middle;}
</style>
<script>
var CUR = <?= json_encode($cur) ?>;
var CLIENT_EMAIL = <?= json_encode($clientEmails, JSON_UNESCAPED_UNICODE) ?>;
function clientPick(name){ var em=document.getElementById('clientEmail'); if(em && !em.value && CLIENT_EMAIL[name]) em.value = CLIENT_EMAIL[name]; }
function fmt(n){ return n.toLocaleString(undefined,{maximumFractionDigits:2}); }
function rowTpl(){
  return '<tr class="li-row">'
    + '<td><input name="desc[]" class="form-control bf-inp li-desc" placeholder="Describe the work or item"></td>'
    + '<td style="width:88px;"><input name="qty[]" type="number" step="any" class="form-control bf-inp li-qty" value="1" oninput="recompute()"></td>'
    + '<td style="width:132px;"><input name="price[]" type="number" step="0.01" class="form-control bf-inp li-price" value="0" oninput="recompute()"></td>'
    + '<td style="width:120px;text-align:right;font-weight:700;" class="li-amt">0</td>'
    + '<td style="width:34px;"><button type="button" class="btn btn-sm text-danger p-0" onclick="delRow(this)"><i class="bi bi-x-lg"></i></button></td>'
    + '</tr>';
}
function addRow(){ document.getElementById('liBody').insertAdjacentHTML('beforeend', rowTpl()); recompute(); }
function delRow(b){ var r=b.closest('.li-row'); if(document.querySelectorAll('#liBody .li-row').length>1) r.remove(); else { r.querySelector('.li-desc').value=''; r.querySelector('.li-qty').value=1; r.querySelector('.li-price').value=0; } recompute(); }
function recompute(){
  var sub=0;
  document.querySelectorAll('#liBody .li-row').forEach(function(r){
    var q=parseFloat(r.querySelector('.li-qty').value)||0;
    var p=parseFloat(r.querySelector('.li-price').value)||0;
    var amt=q*p; sub+=amt;
    r.querySelector('.li-amt').textContent=fmt(amt);
  });
  var tr=parseFloat(document.getElementById('taxRate').value)||0;
  var tax=sub*tr/100;
  document.getElementById('sumSub').textContent=fmt(sub);
  document.getElementById('sumTax').textContent=fmt(tax);
  document.getElementById('sumTot').textContent=fmt(sub+tax);
}
recompute();
</script>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
