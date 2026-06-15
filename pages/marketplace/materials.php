<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Materials & Hardware';
$nav = 'materials';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="bf-mkt-hero">
  <div class="container">

    <!-- Utility bar: stats + live ticker -->
    <div class="bf-mkt-utility">
      <span class="bf-mkt-utility-item"><i class="bi bi-box-seam"></i><b>24,800+</b> Products Listed</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-patch-check"></i><b>3,200+</b> Verified Suppliers</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-truck"></i><b>4,400+</b> Same-Day Items</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-globe"></i>Ships to <b>180+</b> Countries</span>
      <span class="bf-mkt-ticker">
        <span class="dot"></span>
        <span class="bf-mkt-ticker-item"><span class="lbl">OPC Cement</span> <span class="val">KES 980</span>/bag <span class="up">↑2.1%</span></span>
        <span class="bf-mkt-ticker-item"><span class="lbl">Steel Rebar</span> <span class="val">KES 128</span>/kg <span class="down">↓0.8%</span></span>
        <span class="bf-mkt-ticker-item"><i class="bi bi-clock" style="font-size:10px;"></i> EAT</span>
      </span>
    </div>

    <div style="padding:28px 0 26px;">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <div class="bf-section-eyebrow mb-2"><i class="bi bi-bricks" style="color:#c0392b;"></i> Materials & Hardware</div>
          <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">24,800+ products from verified suppliers</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Source cement, steel, timber and finishes — compare live prices across vetted local suppliers and order in bulk.</p>
        </div>
        <a href="/pages/auth/register.php" class="bf-btn-accent">List your products</a>
      </div>

      <!-- Trust badges -->
      <div class="bf-mkt-badges">
        <span class="bf-mkt-badge"><i class="bi bi-lightning-charge-fill"></i> Same-day dispatch on 4,400+ items</span>
        <span class="bf-mkt-badge"><i class="bi bi-shield-check"></i> All suppliers identity verified</span>
        <span class="bf-mkt-badge"><i class="bi bi-tags-fill"></i> Bulk & project pricing available</span>
      </div>

      <!-- Advanced search -->
      <div class="bf-adv-search">
        <div class="bf-adv-search-main">
          <div class="bf-adv-search-label"><i class="bi bi-search"></i> Search Materials</div>
          <input type="text" placeholder="Product name, brand, specification…">
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Category</div>
          <select><option>All Categories</option><option>Cement &amp; Concrete</option><option>Steel &amp; Rebar</option><option>Timber &amp; Lumber</option><option>Blocks &amp; Bricks</option><option>Roofing</option><option>Electrical</option><option>Plumbing &amp; Pipes</option><option>Tiles &amp; Flooring</option><option>Paints &amp; Finishes</option><option>Aggregates</option><option>Waterproofing</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt"></i> Deliver To</div>
          <select><option>Nairobi, Kenya</option><option>Mombasa</option><option>Kisumu</option><option>Kampala, Uganda</option><option>Dar es Salaam</option><option>Any Location</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Quantity</div>
          <select><option>Any Qty.</option><option>Sample / &lt;10</option><option>Small (10–100)</option><option>Bulk (1000+)</option></select>
        </div>
        <button class="bf-adv-search-btn"><i class="bi bi-search"></i> Search</button>
      </div>

      <!-- Popular -->
      <div class="bf-mkt-popular">
        <span class="bf-mkt-popular-label">Popular:</span>
        <?php foreach (['OPC Cement','Y12 Rebar','Timber & Planks','Roofing Sheets','Electrical Cable','Ceramic Tiles','Waterproofing'] as $p): ?>
        <button class="bf-search-tag"><?= $p ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Big stats -->
      <div class="bf-mkt-stats">
        <?php foreach ([['24,800+','Products Listed'],['3,200+','Verified Suppliers'],['180+','Countries'],['4.7★','Avg. Supplier Rating'],['4,400+','Same-Day Items']] as [$n,$l]): ?>
        <div>
          <div class="bf-mkt-stat-num"><?= preg_replace('/([+★])/u','<span>$1</span>',$n) ?></div>
          <div class="bf-mkt-stat-label"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Category nav -->
    <div class="bf-mkt-catnav">
      <?php foreach ([['All Materials','24,800'],['Cement','2,400'],['Steel & Rebar','3,100'],['Timber','1,800'],['Blocks & Bricks','980'],['Roofing','1,640'],['Electrical','4,200'],['Plumbing','2,800'],['Tiles & Flooring','3,400'],['Paints','1,200'],['Aggregates','420'],['Waterproofing','560'],['Tools','1,920']] as $i=>[$c,$ct]): ?>
      <div class="bf-mkt-catnav-item <?= $i===0?'active':'' ?>">
        <div class="bf-mkt-catnav-name"><?= $c ?></div>
        <div class="bf-mkt-catnav-count"><?= $ct ?></div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<div class="container" style="padding-top:36px;padding-bottom:60px;">

  <!-- Live price ticker -->
  <div style="background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px 20px;margin-bottom:28px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
      <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pulse 1.5s infinite;display:inline-block;"></span>
      <span style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);">Live Prices</span>
    </div>
    <?php foreach ([
      ['Cement 50kg','KES 1,050','up'],['Steel Rod 12mm','KES 850','down'],['River Sand (tonne)','KES 3,200','up'],
      ['Roofing Sheet','KES 1,890','up'],['Concrete Block 6"','KES 62','down'],['Timber 2×4 (5m)','KES 620','up'],
    ] as [$item,$price,$dir]): ?>
    <div style="display:flex;align-items:center;gap:6px;font-size:12px;">
      <span style="color:var(--ink-3);"><?= $item ?></span>
      <span style="font-weight:700;color:var(--ink);"><?= $price ?></span>
      <span style="font-size:10px;font-weight:700;color:<?= $dir==='up'?'#c0392b':'#22c55e' ?>;"><?= $dir==='up'?'↑':'↓' ?></span>
    </div>
    <?php endforeach; ?>
    <a href="#" style="font-size:11px;font-weight:700;color:#1e3a5f;margin-left:auto;white-space:nowrap;">Full price list →</a>
  </div>

  <!-- Suppliers grid -->
  <div class="row g-3">
    <?php
    // Live from the database (suppliers + their materials listings)
    // 2 queries instead of N+1: pull all materials listings once, group by supplier in PHP.
    $prodBySupplier = [];
    foreach (db_all("SELECT supplier_id, title, price, price_change FROM listings WHERE kind='materials' AND status='active' AND supplier_id IS NOT NULL ORDER BY sort_order, id") as $pr) {
      $prodBySupplier[(int)$pr['supplier_id']][] = [$pr['title'], $pr['price'], $pr['price_change'] ?: null];
    }
    $suppliers = [];
    foreach (db_all("SELECT * FROM suppliers WHERE status='active' ORDER BY sort_order, name") as $s) {
      $suppliers[] = [$s['code'], $s['name'], $s['location'], $s['rating'], $s['reviews_count'], $s['phone'], $prodBySupplier[(int)$s['id']] ?? [], $s['delivery_info'], $s['logo_bg'], $s['logo_color'], 'OPEN'];
    }
    shuffle($suppliers); // random order on each load (like the homepage)
    foreach ($suppliers as [$code,$name,$area,$rat,$reviews,$tel,$products,$delivery,$bg,$color,$status]): ?>
    <div class="col-md-6 col-xl-4">
      <div class="bf-supplier-card h-100">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="bf-supplier-logo" style="background:<?= $bg ?>;color:<?= $color ?>;"><?= $code ?></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:700;color:var(--ink);"><?= $name ?></div>
            <div style="font-size:10.5px;color:var(--ink-3);"><i class="bi bi-geo-alt" style="font-size:9px;color:#c0392b;"></i> <?= $area ?></div>
            <div style="font-size:10.5px;color:var(--ink-3);margin-top:1px;">
              <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i> <?= $rat ?> · <?= $reviews ?> reviews
              <span style="margin-left:6px;"><i class="bi bi-telephone" style="font-size:9px;"></i> <?= $tel ?></span>
            </div>
          </div>
          <span class="bf-supplier-open"><?= $status ?></span>
        </div>
        <div class="mb-3">
          <?php foreach ($products as [$pn,$pp,$chg]): ?>
          <a class="bf-product-row" href="/pages/marketplace/material.php?name=<?= urlencode($pn) ?>&price=<?= urlencode($pp) ?>&supplier=<?= urlencode($name) ?>&area=<?= urlencode($area) ?>" style="text-decoration:none;">
            <span class="bf-product-name"><?= $pn ?></span>
            <span class="bf-product-price d-flex align-items-center gap-1"><?= $pp ?>
              <?php if ($chg): $isUp = str_starts_with($chg,'up'); ?>
              <span class="bf-price-chg <?= $isUp?'up':'down' ?>"><?= $isUp?'+'.substr($chg,3):substr($chg,0) ?></span>
              <?php endif; ?>
            </span>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="d-flex align-items-center justify-content-between" style="padding-top:10px;border-top:1px solid var(--line-2);margin-top:auto;">
          <div style="font-size:10.5px;color:var(--ink-3);"><i class="bi bi-truck me-1"></i><?= $delivery ?></div>
          <button class="bf-action-dark" data-engage="quote" data-ctx="<?= htmlspecialchars($name, ENT_QUOTES) ?>" style="font-size:11.5px;padding:7px 18px;background:#c0392b;border:none;cursor:pointer;">Order Now</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
