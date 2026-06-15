<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];

// ── handle saves (POST → redirect → GET) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'save') {
        $id = (int)($_POST['id'] ?? 0) ?: null;
        inv_save($uid, $_POST, $id);
        $_SESSION['inv_flash'] = ['ok', $id ? 'Item updated.' : 'Item added to inventory.'];
    } elseif ($act === 'delete') {
        inv_delete($uid, (int)($_POST['id'] ?? 0));
        $_SESSION['inv_flash'] = ['ok', 'Item removed.'];
    }
    header('Location: /pages/inventory/index.php'); exit;
}

$page_title = 'Inventory'; $sp = 'inventory';
$topbar_crumb = 'Catalog';
$topbar_action = '<button class="bf-topbar-new" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="invNew()" style="border:none;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Add Item</button>';

$flash = $_SESSION['inv_flash'] ?? null; unset($_SESSION['inv_flash']);
$q    = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');

$stats = inv_stats($uid);
$items = inv_all($uid, $q, $type);

$typeBadge = ['hardware'=>['Hardware','navy'],'software'=>['Software','blue'],'material'=>['Material','amber'],'service'=>['Service','green']];
$stBadge   = ['in'=>['In stock','green'],'low'=>['Low','amber'],'out'=>['Out of stock','red']];
$typeIcon  = ['software'=>'bi-cpu','hardware'=>'bi-tools','service'=>'bi-wrench-adjustable','material'=>'bi-bricks'];
$cur = defined('CURRENCY') ? CURRENCY : 'KES';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Catalog</div>
        <h1 class="bf-dash-title">Inventory &amp; items</h1>
        <p class="bf-dash-sub">Everything you sell or stock — materials, hardware, software licences and services.</p>
      </div></div>

      <?php if ($flash): ?>
      <div data-ms="5000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Total items', number_format((int)$stats['cnt']), 'bi-box-seam','#1e3a5f','#eaf0f6'],
          ['Low stock',   number_format((int)$stats['low_cnt']), 'bi-exclamation-triangle','#b45309','#fffbeb'],
          ['Out of stock',number_format((int)$stats['out_cnt']), 'bi-x-octagon','#c0392b','#fef2f2'],
          ['Stock value', $cur.' '.number_format((float)$stats['stock_value']), 'bi-cash-stack','#166534','#f0fdf4'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?= $bg ?>;color:<?= $c ?>;"><i class="bi <?= $ic ?>"></i></div></div><div class="bf-kpi-v"><?= $v ?></div><div class="bf-kpi-l"><?= $l ?></div></div>
        <?php endforeach; ?>
      </div>

      <form method="get" class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div class="bf-topbar-search" style="max-width:300px;height:38px;"><i class="bi bi-search"></i><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search items, SKU…"></div>
        <?php foreach (['All'=>'','Hardware'=>'hardware','Software'=>'software','Materials'=>'material','Services'=>'service'] as $lbl=>$val): ?>
        <a href="?<?= http_build_query(array_filter(['q'=>$q,'type'=>$val])) ?>" class="bf-badge <?= $type===$val?'navy':'grey' ?>" style="text-decoration:none;padding:7px 13px;font-size:11px;"><?= $lbl ?></a>
        <?php endforeach; ?>
      </form>

      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="flex:1;">Item</div>
          <div style="width:150px;" class="d-none d-lg-block">SKU</div>
          <div style="width:110px;" class="d-none d-md-block">Type</div>
          <div style="width:120px;">Unit price</div>
          <div style="width:100px;" class="d-none d-md-block">In stock</div>
          <div style="width:100px;">Status</div>
          <div style="width:40px;"></div>
        </div>

        <?php if (!$items): ?>
        <div style="padding:48px 20px;text-align:center;color:var(--ink-4);">
          <i class="bi bi-box-seam" style="font-size:34px;opacity:.4;"></i>
          <p style="margin:12px 0 16px;font-size:13px;">No items yet<?= $q||$type?' match your filter.':'. Add your first stock item or service.' ?></p>
          <button class="bf-topbar-new" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="invNew()" style="border:none;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
        </div>
        <?php endif; ?>

        <?php foreach ($items as $it):
          $type_k = $it['item_type'];
          [$tl,$tb] = $typeBadge[$type_k] ?? ['Item','grey'];
          if ($type_k === 'service') { $sl = 'Available'; $sb = 'green'; }
          else { [$sl,$sb] = $stBadge[inv_stock_status((int)$it['quantity'], (int)$it['reorder_level'])]; }
          $qtyLabel = $type_k === 'service' ? '—' : ((int)$it['quantity']) . ($it['unit'] ? ' '.$it['unit'] : '');
          $j = htmlspecialchars(json_encode($it), ENT_QUOTES); ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;border-radius:8px;background:var(--surface);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:#1e3a5f;flex-shrink:0;"><i class="bi <?= $typeIcon[$type_k] ?? 'bi-box' ?>"></i></span>
            <span class="pri"><?= htmlspecialchars($it['name']) ?></span>
          </div>
          <div style="width:150px;font-size:11.5px;color:var(--ink-4);font-family:monospace;" class="d-none d-lg-block"><?= htmlspecialchars($it['sku'] ?: '—') ?></div>
          <div style="width:110px;" class="d-none d-md-block"><span class="bf-badge <?= $tb ?>"><?= $tl ?></span></div>
          <div style="width:120px;font-weight:800;color:var(--ink);"><?= $cur ?> <?= number_format((float)$it['unit_price']) ?></div>
          <div style="width:100px;" class="d-none d-md-block"><?= htmlspecialchars($qtyLabel) ?></div>
          <div style="width:100px;"><span class="bf-badge <?= $sb ?>"><?= $sl ?></span></div>
          <div style="width:40px;text-align:right;" class="dropdown">
            <button class="btn btn-sm border-0 p-1" data-bs-toggle="dropdown" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:10px;">
              <li><a class="dropdown-item" href="#" data-item="<?= $j ?>" onclick="invEdit(this);return false;"><i class="bi bi-pencil me-2 text-muted"></i>Edit</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="post" onsubmit="return confirm('Remove this item from inventory?');" style="margin:0;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                  <button type="submit" class="dropdown-item text-danger" style="border:none;background:none;cursor:pointer;"><i class="bi bi-trash me-2"></i>Delete</button>
                </form>
              </li>
            </ul>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Add / Edit item modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" style="border:none;border-radius:16px;">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="im_id">
      <div class="modal-header" style="border-bottom:1px solid var(--line);">
        <h5 class="modal-title" id="im_title" style="font-size:15px;font-weight:800;color:var(--ink);">Add item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding:18px 20px;">
        <div class="row g-3">
          <div class="col-12">
            <label class="bf-lbl">Item name *</label>
            <input name="name" id="im_name" required class="form-control bf-inp" placeholder="e.g. Portland Cement 50kg">
          </div>
          <div class="col-md-6">
            <label class="bf-lbl">SKU / code</label>
            <input name="sku" id="im_sku" class="form-control bf-inp" placeholder="CEM-OPC-50">
          </div>
          <div class="col-md-6">
            <label class="bf-lbl">Type</label>
            <select name="item_type" id="im_type" class="form-select bf-inp">
              <option value="material">Material</option>
              <option value="hardware">Hardware</option>
              <option value="software">Software</option>
              <option value="service">Service</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="bf-lbl">Unit price (<?= $cur ?>)</label>
            <input name="unit_price" id="im_price" type="number" step="0.01" min="0" class="form-control bf-inp" placeholder="1050">
          </div>
          <div class="col-md-4">
            <label class="bf-lbl">Quantity</label>
            <input name="quantity" id="im_qty" type="number" step="1" class="form-control bf-inp" placeholder="420">
          </div>
          <div class="col-md-4">
            <label class="bf-lbl">Unit</label>
            <input name="unit" id="im_unit" class="form-control bf-inp" placeholder="bags / units / seats">
          </div>
          <div class="col-md-6">
            <label class="bf-lbl">Reorder level</label>
            <input name="reorder_level" id="im_reorder" type="number" step="1" min="0" class="form-control bf-inp" placeholder="50">
          </div>
          <div class="col-md-6">
            <label class="bf-lbl">Location / store</label>
            <input name="location" id="im_location" class="form-control bf-inp" placeholder="Nairobi yard">
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--line);">
        <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal" style="border:1px solid var(--line);background:#fff;">Cancel</button>
        <button type="submit" class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Save item</button>
      </div>
    </form>
  </div>
</div>

<style>
.bf-lbl{font-size:11.5px;font-weight:700;color:var(--ink-2);display:block;margin-bottom:5px;}
.bf-inp{font-size:13px;border-color:var(--line);border-radius:9px;padding:9px 12px;}
</style>
<script>
function invNew(){
  document.getElementById('im_title').textContent = 'Add item';
  ['id','name','sku','price','qty','unit','reorder','location'].forEach(k=>{var e=document.getElementById('im_'+k);if(e)e.value='';});
  document.getElementById('im_type').value='material';
}
function invEdit(el){
  var it = JSON.parse(el.getAttribute('data-item'));
  document.getElementById('im_title').textContent = 'Edit item';
  document.getElementById('im_id').value      = it.id || '';
  document.getElementById('im_name').value    = it.name || '';
  document.getElementById('im_sku').value     = it.sku || '';
  document.getElementById('im_type').value    = it.item_type || 'material';
  document.getElementById('im_price').value   = it.unit_price || '';
  document.getElementById('im_qty').value     = it.quantity || '';
  document.getElementById('im_unit').value    = it.unit || '';
  document.getElementById('im_reorder').value = it.reorder_level || '';
  document.getElementById('im_location').value= it.location || '';
  new bootstrap.Modal(document.getElementById('itemModal')).show();
}
</script>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
