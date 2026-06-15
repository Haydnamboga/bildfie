<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$name     = trim($_GET['name'] ?? 'Portland Cement 50kg');
$price    = trim($_GET['price'] ?? 'KES 1,050');
$supplier = trim($_GET['supplier'] ?? 'Nairobi Builders Hub');
$area     = trim($_GET['area'] ?? 'Industrial Area, Nairobi');
$cat      = trim($_GET['cat'] ?? 'Cement & Concrete');

$page_title = $name;
$nav = 'materials';
$img = 'https://picsum.photos/seed/mat-' . urlencode($name) . '/700/520';

$specs = [
  ['Grade','OPC 42.5N'],['Pack size','50 kg bag'],['Standard','KEBS / EN 197-1'],
  ['Min. order','10 bags'],['Lead time','Same-day (Nairobi)'],['Bulk discount','Yes — 100+ bags'],
];
$otherSuppliers = [
  ['Global Build Supplies','Victoria Island','KES 1,040','4.8'],
  ['Premium Build Mart','Accra Road','KES 1,075','4.9'],
  ['BuildCorp Distributors','Mombasa Road','KES 1,030','4.6'],
];
$ctx = htmlspecialchars($name, ENT_QUOTES);
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);padding:22px 0 48px;">
  <div class="container" style="max-width:1080px;">

    <div style="font-size:12px;color:var(--ink-4);margin-bottom:14px;">
      <a href="/pages/marketplace/materials.php" style="color:var(--ink-3);text-decoration:none;">Materials</a>
      <i class="bi bi-chevron-right" style="font-size:9px;"></i> <span style="color:var(--ink-2);"><?= htmlspecialchars($name) ?></span>
    </div>

    <div class="row g-3">
      <!-- Image -->
      <div class="col-lg-5">
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;overflow:hidden;position:relative;">
          <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name) ?>" style="width:100%;height:360px;object-fit:cover;display:block;">
          <button class="bf-fav" data-fav="mat:<?= urlencode($name) ?>" title="Save" style="position:absolute;top:14px;right:14px;"><i class="bi bi-heart"></i></button>
          <span style="position:absolute;top:14px;left:14px;font-size:10px;font-weight:800;background:#f0fdf4;color:#166534;padding:4px 10px;border-radius:6px;"><i class="bi bi-check-circle-fill"></i> In stock</span>
        </div>
      </div>

      <!-- Detail -->
      <div class="col-lg-7">
        <div class="bf-pf-card" style="margin-bottom:12px;">
          <div class="bf-pf-card-b">
            <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#1e3a5f;"><?= htmlspecialchars($cat) ?></div>
            <h1 style="font-size:24px;font-weight:900;color:var(--ink);margin:6px 0 8px;letter-spacing:-.02em;"><?= htmlspecialchars($name) ?></h1>
            <div style="display:flex;align-items:baseline;gap:10px;margin-bottom:6px;">
              <span style="font-size:26px;font-weight:900;color:#c0392b;"><?= htmlspecialchars($price) ?></span>
              <span style="font-size:12px;color:#166534;font-weight:700;"><i class="bi bi-arrow-down-right"></i> 2% this week</span>
            </div>
            <div style="font-size:12px;color:var(--ink-3);margin-bottom:16px;"><i class="bi bi-shop" style="color:var(--ink-4);"></i> Sold by <b style="color:var(--ink);"><?= htmlspecialchars($supplier) ?></b> · <?= htmlspecialchars($area) ?></div>

            <!-- order row -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
              <div style="display:flex;align-items:center;border:1px solid var(--line);border-radius:9px;overflow:hidden;">
                <button style="border:none;background:var(--surface);padding:9px 13px;cursor:pointer;font-size:14px;color:var(--ink-2);">−</button>
                <input value="10" style="width:48px;border:none;text-align:center;font-size:13px;font-weight:700;outline:none;">
                <button style="border:none;background:var(--surface);padding:9px 13px;cursor:pointer;font-size:14px;color:var(--ink-2);">+</button>
              </div>
              <span style="font-size:11.5px;color:var(--ink-4);">bags · min. order 10</span>
              <button class="bf-btn-accent" data-engage="quote" data-ctx="<?= $ctx ?>" style="border:none;cursor:pointer;flex:1;min-width:160px;"><i class="bi bi-receipt me-1"></i>Request quote</button>
            </div>
            <div style="font-size:11px;color:var(--ink-4);margin-top:12px;"><i class="bi bi-truck"></i> Same-day delivery in Nairobi · <i class="bi bi-shield-fill-check" style="color:#16a34a;"></i> Escrow-protected</div>
          </div>
        </div>

        <!-- specs -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-list-check"></i> Specifications</div></div>
          <div class="bf-pf-card-b" style="padding-top:6px;padding-bottom:6px;">
            <?php foreach ($specs as [$k,$v]): ?>
            <div class="bf-pf-contact" style="justify-content:space-between;"><span style="color:var(--ink-3);"><?= $k ?></span><span style="font-weight:700;color:var(--ink);"><?= $v ?></span></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Compare suppliers -->
    <div class="bf-pf-card" style="margin-top:14px;">
      <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-shop-window"></i> Compare suppliers for this item</div></div>
      <div class="bf-pf-card-b" style="padding-top:6px;padding-bottom:6px;">
        <?php foreach ($otherSuppliers as [$sn,$sa,$sp,$sr]): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:13px 0;border-top:1px solid var(--line-2);">
          <div style="width:38px;height:38px;border-radius:9px;background:#eaf0f6;color:#1e3a5f;display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;"><?= strtoupper(substr($sn,0,2)) ?></div>
          <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;color:var(--ink);"><?= $sn ?></div><div style="font-size:11px;color:var(--ink-4);"><i class="bi bi-geo-alt" style="color:#c0392b;font-size:9px;"></i> <?= $sa ?> · <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i> <?= $sr ?></div></div>
          <div style="font-size:14px;font-weight:800;color:#c0392b;"><?= $sp ?></div>
          <button class="bf-btn-ghost" data-engage="quote" data-ctx="<?= htmlspecialchars($sn, ENT_QUOTES) ?>">Quote</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
