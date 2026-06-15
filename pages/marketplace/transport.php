<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Transport & Logistics';
$nav = 'logistics';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="bf-mkt-hero">
  <div class="container">

    <!-- Utility bar -->
    <div class="bf-mkt-utility">
      <span class="bf-mkt-utility-item"><i class="bi bi-truck"></i><b>2,800+</b> Vehicles Available</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-patch-check"></i><b>640+</b> Verified Operators</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-broadcast-pin"></i> GPS-tracked Real-time</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-shield-check"></i> Fully Insured Fleets</span>
      <span class="bf-mkt-ticker">
        <span class="dot"></span>
        <span class="bf-mkt-ticker-item"><span class="val">24hr</span> <span class="lbl">avg. availability</span></span>
      </span>
    </div>

    <div style="padding:28px 0 26px;">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <div class="bf-section-eyebrow mb-2"><i class="bi bi-truck" style="color:#c0392b;"></i> Transport & Logistics</div>
          <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">2,800+ vehicles ready to move your load</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Flatbeds, low-loaders and tippers — GPS-tracked and fully insured. Book per trip, daily, weekly or on contract.</p>
        </div>
        <a href="/pages/projects/create.php" class="bf-btn-accent">Post a haulage request</a>
      </div>

      <!-- Trust badges -->
      <div class="bf-mkt-badges">
        <span class="bf-mkt-badge"><i class="bi bi-broadcast-pin"></i> GPS-tracked, real-time</span>
        <span class="bf-mkt-badge"><i class="bi bi-shield-fill-check"></i> Fully insured fleets</span>
        <span class="bf-mkt-badge"><i class="bi bi-truck-flatbed"></i> Flatbeds, low-loaders &amp; tippers</span>
      </div>

      <!-- Advanced search -->
      <div class="bf-adv-search">
        <div class="bf-adv-search-main">
          <div class="bf-adv-search-label"><i class="bi bi-search"></i> Search Transport</div>
          <input type="text" placeholder="Vehicle, operator or cargo type…">
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Vehicle Type</div>
          <select><option>All Vehicles</option><option>Flatbed Truck</option><option>Low-Loader</option><option>Tipper / Dump Truck</option><option>Crane Truck</option><option>Curtainsider</option><option>Tanker</option><option>Pick-up / Light</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt"></i> From</div>
          <select><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Kampala</option><option>Dar es Salaam</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt-fill"></i> To</div>
          <select><option>Any Destination</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Kampala</option><option>Dar es Salaam</option></select>
        </div>
        <button class="bf-adv-search-btn"><i class="bi bi-search"></i> Search</button>
      </div>

      <!-- Popular -->
      <div class="bf-mkt-popular">
        <span class="bf-mkt-popular-label">Popular:</span>
        <?php foreach (['Flatbed','Low-Loader','Tipper','Concrete Mixer','Crane Truck','Pickup'] as $p): ?>
        <button class="bf-search-tag"><?= $p ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Big stats -->
      <div class="bf-mkt-stats">
        <?php foreach ([['2,800+','Vehicles'],['640+','Operators'],['4.8★','Avg. Rating'],['24hr','Availability'],['12+','Countries Covered']] as [$n,$l]): ?>
        <div>
          <div class="bf-mkt-stat-num"><?= preg_replace('/([+★])/u','<span>$1</span>',$n) ?></div>
          <div class="bf-mkt-stat-label"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Category nav -->
    <div class="bf-mkt-catnav">
      <?php foreach ([['All Vehicles','2,800'],['Flatbed Trucks','680'],['Low-Loaders','320'],['Tippers / Dump','840'],['Tankers','240'],['Cargo Vans','360'],['Light Trucks','200'],['Crane Trucks','160']] as $i=>[$c,$ct]): ?>
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
    <div style="font-size:12.5px;color:var(--ink-3);">Showing <strong style="color:var(--ink);">2,800+</strong> vehicles &amp; operators</div>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>Per Trip</option><option>Daily Rate</option><option>Weekly</option><option>Contract</option>
      </select>
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>Sort: Featured</option><option>Rate: Low→High</option><option>Highest rated</option><option>Nearest</option>
      </select>
    </div>
  </div>

  <div class="row g-3">
    <?php
    // Live from the database (listings · transport)
    $vehicles = [];
    foreach (db_all("SELECT * FROM listings WHERE kind='transport' AND status='active' ORDER BY sort_order, id") as $r) {
      $sp = json_decode($r['specs'] ?: '{}', true) ?: [];
      $vehicles[] = [$r['title'], $r['vendor_name'], $r['icon'], $r['category'], $r['price'], $r['price_secondary'], $r['avail_label'], $r['description'], $r['rating'], $r['usage_count'], $sp['portrait'] ?? 'men/11'];
    }
    shuffle($vehicles); // random order on each load (like the homepage)
    foreach ($vehicles as [$name,$owner,$icon,$type,$tripRate,$dayRate,$status,$desc,$rat,$trips,$portrait]): ?>
    <div class="col-md-6 col-xl-3">
      <div class="bf-transport-v2 h-100">
        <div class="bf-transport-v2-header">
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--white);display:flex;align-items:center;justify-content:center;border:1px solid var(--line);">
              <i class="bi <?= $icon ?>" style="font-size:16px;color:#1e3a5f;"></i>
            </div>
            <div style="min-width:0;">
              <div style="font-size:12.5px;font-weight:800;color:var(--ink);line-height:1.2;"><?= $name ?></div>
              <div style="font-size:10px;color:var(--ink-4);"><?= $type ?></div>
            </div>
          </div>
          <span style="font-size:9px;font-weight:800;letter-spacing:.06em;padding:3px 8px;border-radius:4px;
            background:<?= $status==='AVAIL.'?'#f0fdf4':($status==='FEW'?'#fff7ed':'#fef2f2') ?>;
            color:<?= $status==='AVAIL.'?'#166534':($status==='FEW'?'#92400e':'#c0392b') ?>;"><?= $status ?></span>
        </div>

        <div style="padding:14px 16px;flex:1;display:flex;flex-direction:column;">
          <div style="font-size:11px;color:var(--ink-3);line-height:1.6;margin-bottom:12px;flex:1;"><?= $desc ?></div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
            <div style="background:var(--surface);border-radius:8px;padding:8px 10px;text-align:center;">
              <div style="font-size:9px;color:var(--ink-4);font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Per Trip</div>
              <div style="font-size:13px;font-weight:800;color:#c0392b;"><?= $tripRate ?></div>
            </div>
            <div style="background:var(--surface);border-radius:8px;padding:8px 10px;text-align:center;">
              <div style="font-size:9px;color:var(--ink-4);font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Per Day</div>
              <div style="font-size:13px;font-weight:800;color:var(--ink);"><?= $dayRate ?></div>
            </div>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line-2);">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:28px;height:28px;border-radius:50%;overflow:hidden;border:2px solid var(--line);">
                <img src="https://randomuser.me/api/portraits/<?= $portrait ?>.jpg" style="width:100%;height:100%;object-fit:cover;">
              </div>
              <div>
                <div style="font-size:10.5px;font-weight:700;color:var(--ink);"><?= explode(' ·', $owner)[0] ?></div>
                <div style="font-size:9.5px;color:var(--ink-4);"><i class="bi bi-star-fill" style="color:#f59e0b;font-size:8px;"></i> <?= $rat ?> · <?= $trips ?> trips</div>
              </div>
            </div>
            <button class="bf-action-dark" data-engage="quote" data-ctx="<?= htmlspecialchars($name, ENT_QUOTES) ?>" style="font-size:11px;padding:6px 14px;border:none;cursor:pointer;">Book</button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
