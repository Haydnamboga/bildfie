<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Construction Facilities';
$nav = 'facilities';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="bf-mkt-hero">
  <div class="container">

    <!-- Utility bar -->
    <div class="bf-mkt-utility">
      <span class="bf-mkt-utility-item"><i class="bi bi-building"></i><b>3,400+</b> Facilities Available</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-patch-check"></i><b>820+</b> Verified Providers</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-box-seam"></i> Portable &amp; Modular Units</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-calendar-range"></i> Short &amp; Long-Term Hire</span>
      <span class="bf-mkt-ticker">
        <span class="dot"></span>
        <span class="bf-mkt-ticker-item"><span class="val">48hr</span> <span class="lbl">avg. delivery</span></span>
      </span>
    </div>

    <div style="padding:28px 0 26px;">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <div class="bf-section-eyebrow mb-2"><i class="bi bi-building" style="color:#c0392b;"></i> Site Facilities</div>
          <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">3,400+ site offices, storage &amp; welfare units</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Portable and modular facilities — site offices, yards, storage and welfare. Ready to occupy, short or long-term hire.</p>
        </div>
        <a href="/pages/projects/create.php" class="bf-btn-accent">List your facility</a>
      </div>

      <!-- Trust badges -->
      <div class="bf-mkt-badges">
        <span class="bf-mkt-badge"><i class="bi bi-box-seam-fill"></i> Portable &amp; modular units</span>
        <span class="bf-mkt-badge"><i class="bi bi-calendar-range-fill"></i> Short &amp; long-term hire</span>
        <span class="bf-mkt-badge"><i class="bi bi-truck"></i> 48hr average delivery</span>
      </div>

      <!-- Advanced search -->
      <div class="bf-adv-search">
        <div class="bf-adv-search-main">
          <div class="bf-adv-search-label"><i class="bi bi-search"></i> Search Facilities</div>
          <input type="text" placeholder="Facility type, size or location…">
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Type</div>
          <select><option>All Types</option><option>Site Offices</option><option>Portable Cabins</option><option>Storage Containers</option><option>Warehouses</option><option>Storage Yards</option><option>Batching Yards</option><option>Workshops</option><option>Welfare Units</option><option>Labour Camps</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt"></i> Location</div>
          <select><option>Any Location</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Kampala</option><option>Dar es Salaam</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Duration</div>
          <select><option>Any Duration</option><option>Weekly</option><option>Monthly</option><option>3–6 Months</option><option>6–12 Months</option></select>
        </div>
        <button class="bf-adv-search-btn"><i class="bi bi-search"></i> Search</button>
      </div>

      <!-- Popular -->
      <div class="bf-mkt-popular">
        <span class="bf-mkt-popular-label">Popular:</span>
        <?php foreach (['Site Office','Portable Cabin','Storage Container','Warehouse','Welfare Unit'] as $p): ?>
        <button class="bf-search-tag"><?= $p ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Big stats -->
      <div class="bf-mkt-stats">
        <?php foreach ([['3,400+','Facilities'],['820+','Providers'],['4.7★','Avg. Rating'],['48hr','Avg. Move-In'],['47','Counties Covered']] as [$n,$l]): ?>
        <div>
          <div class="bf-mkt-stat-num"><?= preg_replace('/([+★])/u','<span>$1</span>',$n) ?></div>
          <div class="bf-mkt-stat-label"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Category nav -->
    <div class="bf-mkt-catnav">
      <?php foreach ([['All Facilities','3,400'],['Site Offices','820'],['Portable Cabins','640'],['Storage Containers','960'],['Warehouses','410'],['Storage Yards','380'],['Workshops','290'],['Welfare Units','480'],['Labour Camps','140']] as $i=>[$c,$ct]): ?>
      <div class="bf-mkt-catnav-item <?= $i===0?'active':'' ?>">
        <div class="bf-mkt-catnav-name"><?= $c ?></div>
        <div class="bf-mkt-catnav-count"><?= $ct ?></div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<div class="container" style="padding-top:32px;padding-bottom:60px;">

  <!-- Results bar -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid var(--line);">
    <div style="font-size:12.5px;color:var(--ink-3);">Showing <strong style="color:var(--ink);">3,400+</strong> facilities &amp; yards</div>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>All regions</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option>
      </select>
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>Sort: Featured</option><option>Price: Low→High</option><option>Highest rated</option><option>Move-in soonest</option>
      </select>
    </div>
  </div>

  <div class="row g-3">
    <?php
    // Live from the database (listings · facilities)
    $facilities = [];
    foreach (db_all("SELECT * FROM listings WHERE kind='facilities' AND status='active' ORDER BY sort_order, id") as $r) {
      $tags = $r['tags'] ? explode(',', $r['tags']) : [];
      $facilities[] = [$r['title'], $r['description'], $r['location'], $r['price'], $r['image_url'], $r['category'], $r['avail_label'], $r['icon'], $tags];
    }
    shuffle($facilities); // random order on each load (like the homepage)
    foreach ($facilities as [$name,$desc,$loc,$price,$img,$type,$avail,$icon,$tags]): ?>
    <div class="col-md-6">
      <div class="bf-facility-large h-100">
        <div class="bf-facility-large-img">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($name) ?>" style="filter:brightness(0.85) saturate(0.8);">
        </div>
        <div class="bf-facility-large-body">
          <div>
            <div class="bf-facility-large-type">
              <i class="bi <?= $icon ?>"></i><?= $type ?>
              <span class="bf-facility-large-status avl"><?= strtoupper($avail) ?></span>
            </div>
            <div class="bf-facility-large-name"><?= $name ?></div>
            <div class="bf-facility-large-desc"><?= $desc ?></div>
            <div class="d-flex flex-wrap gap-1 mt-2">
              <?php foreach ($tags as $t): ?>
              <span style="font-size:10px;font-weight:600;background:var(--surface);border:1px solid var(--line);padding:2px 8px;border-radius:4px;color:var(--ink-3);"><?= $t ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="bf-facility-large-meta">
            <div class="bf-facility-large-loc"><i class="bi bi-geo-alt" style="font-size:10px;color:#c0392b;"></i><?= $loc ?></div>
            <div class="d-flex align-items-center gap-2">
              <div class="bf-facility-large-price"><?= $price ?></div>
              <button class="bf-action-dark" data-engage="quote" data-ctx="<?= htmlspecialchars($name, ENT_QUOTES) ?>" style="font-size:11px;padding:5px 14px;border:none;cursor:pointer;">Enquire</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
