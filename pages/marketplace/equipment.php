<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Equipment Hire';
$nav = 'equipment';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="bf-mkt-hero">
  <div class="container">

    <!-- Utility bar -->
    <div class="bf-mkt-utility">
      <span class="bf-mkt-utility-item"><i class="bi bi-gear-wide-connected"></i><b>6,400+</b> Equipment Listed</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-patch-check"></i><b>1,800+</b> Verified Owners</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-clock-history"></i> Hourly, Daily &amp; Weekly Rates</span>
      <span class="bf-mkt-utility-item"><i class="bi bi-person-gear"></i> Operator Included Options</span>
      <span class="bf-mkt-ticker">
        <span class="dot"></span>
        <span class="bf-mkt-ticker-item"><span class="val">24hr</span> <span class="lbl">avg. delivery</span></span>
      </span>
    </div>

    <div style="padding:28px 0 26px;">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <div class="bf-section-eyebrow mb-2"><i class="bi bi-gear" style="color:#c0392b;"></i> Equipment Hire</div>
          <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">6,400+ machines ready when you are</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Excavators, cranes, generators and more — hourly, daily, weekly or monthly hire with delivery and operator options.</p>
        </div>
        <a href="/pages/projects/create.php" class="bf-btn-accent">List your equipment</a>
      </div>

      <!-- Trust badges -->
      <div class="bf-mkt-badges">
        <span class="bf-mkt-badge"><i class="bi bi-shield-fill-check"></i> Fully insured equipment</span>
        <span class="bf-mkt-badge"><i class="bi bi-person-gear"></i> Operator-included options</span>
        <span class="bf-mkt-badge"><i class="bi bi-truck"></i> 24hr average delivery</span>
      </div>

      <!-- Advanced search -->
      <div class="bf-adv-search">
        <div class="bf-adv-search-main">
          <div class="bf-adv-search-label"><i class="bi bi-search"></i> Search Equipment</div>
          <input type="text" placeholder="Machine type, make or model…">
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Category</div>
          <select><option>All Equipment</option><option>Excavators</option><option>Cranes &amp; Hoists</option><option>Concrete Equipment</option><option>Compaction</option><option>Scaffolding</option><option>Generators</option><option>Compressors</option><option>Welding Equipment</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt"></i> Location</div>
          <select><option>Any Location</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Kampala</option><option>Dar es Salaam</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Duration</div>
          <select><option>Any Duration</option><option>Hourly</option><option>Daily</option><option>Weekly</option><option>Monthly</option></select>
        </div>
        <button class="bf-adv-search-btn"><i class="bi bi-search"></i> Search</button>
      </div>

      <!-- Popular -->
      <div class="bf-mkt-popular">
        <span class="bf-mkt-popular-label">Popular:</span>
        <?php foreach (['Excavator','Tower Crane','Concrete Mixer','Generator','Scaffolding'] as $p): ?>
        <button class="bf-search-tag"><?= $p ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Big stats -->
      <div class="bf-mkt-stats">
        <?php foreach ([['6,400+','Equipment Listed'],['1,800+','Verified Owners'],['4.7★','Average Rating'],['24hr','Avg. Delivery'],['12+','Countries Covered']] as [$n,$l]): ?>
        <div>
          <div class="bf-mkt-stat-num"><?= preg_replace('/([+★])/u','<span>$1</span>',$n) ?></div>
          <div class="bf-mkt-stat-label"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Category nav -->
    <div class="bf-mkt-catnav">
      <?php foreach ([['All Equipment','6,400'],['Excavators','840'],['Cranes & Hoists','620'],['Concrete','980'],['Compaction','440'],['Scaffolding','1,200'],['Generators','760'],['Compressors','380'],['Welding','520'],['Water Pumps','340'],['Site Lighting','280']] as $i=>[$c,$ct]): ?>
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
    <div style="font-size:12.5px;color:var(--ink-3);">Showing <strong style="color:var(--ink);">6,400+</strong> machines available for hire</div>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>All regions</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option>
      </select>
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>Sort: Featured</option><option>Rate: Low→High</option><option>Highest rated</option>
      </select>
    </div>
  </div>

  <div class="row g-3">
    <?php
    // Live from the database (listings · equipment)
    $equipment = [];
    foreach (db_all("SELECT * FROM listings WHERE kind='equipment' AND status='active' ORDER BY sort_order, id") as $r) {
      $sp = json_decode($r['specs'] ?: '{}', true) ?: []; $kv = [];
      foreach ($sp as $k => $v) { $kv[] = $k; $kv[] = $v; }
      $kv = array_pad($kv, 8, '');
      $equipment[] = [$r['title'], $r['category'], $r['image_url'], $r['badge'], $kv[0],$kv[1],$kv[2],$kv[3],$kv[4],$kv[5],$kv[6],$kv[7], $r['price'], $r['price_secondary'], $r['rating'], $r['usage_count'], $r['location'], $r['icon']];
    }
    shuffle($equipment); // random order on each load (like the homepage)
    foreach ($equipment as [$name,$cat,$img,$badge,$k1,$v1,$k2,$v2,$k3,$v3,$k4,$v4,$day,$week,$rat,$hires,$loc,$icon]): ?>
    <div class="col-md-6 col-xl-4">
      <div class="bf-equip-card h-100">
        <div class="bf-equip-cover">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($name) ?>" style="filter:brightness(0.75) saturate(0.7);">
          <div class="bf-equip-cover-overlay"></div>
          <div class="bf-equip-cover-name"><?= $name ?></div>
          <span style="position:absolute;top:12px;left:12px;font-size:8.5px;font-weight:800;letter-spacing:.1em;background:rgba(0,0,0,.45);color:#fff;padding:3px 8px;border-radius:4px;"><?= $badge ?></span>
          <span style="position:absolute;top:12px;right:12px;font-size:8.5px;font-weight:800;background:#dcfce7;color:#166534;padding:3px 8px;border-radius:4px;">AVAILABLE</span>
        </div>
        <div style="padding:16px;flex:1;display:flex;flex-direction:column;">
          <div style="font-size:9.5px;font-weight:700;color:#c0392b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;"><?= $cat ?></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px 14px;margin-bottom:14px;">
            <?php foreach ([[$k1,$v1],[$k2,$v2],[$k3,$v3],[$k4,$v4]] as [$k,$v]): ?>
            <div>
              <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);"><?= $k ?></div>
              <div style="font-size:12px;font-weight:700;color:var(--ink);"><?= $v ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="background:var(--surface);border-radius:8px;padding:10px 12px;margin-bottom:14px;">
            <div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-4);margin-bottom:6px;">Hire rates</div>
            <div style="display:flex;justify-content:space-between;font-size:12px;">
              <span style="color:var(--ink-3);">Daily</span><span style="font-weight:800;color:var(--ink);"><?= $day ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-top:3px;">
              <span style="color:var(--ink-3);">Weekly (5 days)</span><span style="font-weight:800;color:var(--ink);"><?= $week ?></span>
            </div>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line-2);margin-top:auto;">
            <div>
              <div style="font-size:10.5px;color:var(--ink-3);"><i class="bi bi-geo-alt" style="color:#c0392b;font-size:9px;"></i> <?= $loc ?></div>
              <div style="font-size:10px;color:var(--ink-4);margin-top:2px;"><i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i> <?= $rat ?> · <?= $hires ?> hires</div>
            </div>
            <?php $eqUrl = '/pages/marketplace/equipment-view.php?name='.urlencode($name).'&cat='.urlencode($cat).'&img='.urlencode($img).'&day='.urlencode($day).'&week='.urlencode($week).'&loc='.urlencode($loc).'&rat='.urlencode($rat).'&hires='.urlencode($hires).'&k1='.urlencode($k1).'&v1='.urlencode($v1).'&k2='.urlencode($k2).'&v2='.urlencode($v2).'&k3='.urlencode($k3).'&v3='.urlencode($v3).'&k4='.urlencode($k4).'&v4='.urlencode($v4); ?>
            <div class="d-flex gap-2">
              <a href="<?= $eqUrl ?>" class="bf-action-outline" style="font-size:11px;padding:6px 14px;text-decoration:none;">Details</a>
              <a href="<?= $eqUrl ?>" class="bf-action-dark" style="font-size:11px;padding:6px 14px;background:#c0392b;text-decoration:none;">Book Now</a>
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
