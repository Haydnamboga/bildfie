<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/auth.php';
$page_title = 'Find Verified Construction Professionals';
$nav = 'home';
$extra_css = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<?php include __DIR__ . '/includes/navbar.php'; ?>


<!-- ═══════════════════════════════════════════
     HERO
═══════════════════════════════════════════ -->
<section class="bf-hero" style="padding-top:0;">

  <!-- Live strip -->
  <div class="bf-livestrip">
    <div class="container">
      <div class="bf-livestrip-inner" style="flex-wrap:nowrap;overflow:hidden;">
        <div class="bf-livestrip-item" style="flex-shrink:0;">
          <span class="bf-live-dot"></span>
          <span class="bf-livestrip-val">Kisii, Kenya</span>
        </div>
        <div class="bf-livestrip-item d-none d-md-flex" style="flex-shrink:0;">
          <i class="bi bi-thermometer-half" style="color:#f59e0b;font-size:12px;"></i>
          <span class="bf-livestrip-val">24 °C</span>
          <span style="font-size:9px;background:#f0fdf4;color:#166534;font-weight:800;padding:1px 7px;border-radius:4px;letter-spacing:.05em;">GOOD SITE DAY</span>
        </div>
        <div class="bf-livestrip-item d-none d-lg-flex" style="flex-shrink:0;">
          <i class="bi bi-currency-exchange" style="color:var(--ink-4);font-size:11px;"></i>
          <span>1 USD</span><span class="bf-livestrip-val">=&thinsp;KES 129.4</span>
        </div>
        <div style="flex:1;min-width:0;display:flex;align-items:center;gap:10px;padding:0 16px;overflow:hidden;" class="d-none d-lg-flex">
          <span id="newsLabel" style="font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;background:#fef2f2;color:#c0392b;padding:2px 8px;border-radius:4px;white-space:nowrap;flex-shrink:0;">BREAKING</span>
          <div style="overflow:hidden;height:1.4em;flex:1;min-width:0;position:relative;">
            <div id="newsTicker" style="position:absolute;width:100%;">
              <?php foreach ([
                'NCA mandates structural audits for all high-rise buildings — effective July 2025',
                'Cement prices stabilise after Q3 global supply chain disruptions',
                'New affordable housing policy targets 100,000 units by 2026',
                'Equity Bank launches KES 5B construction finance product for SMEs',
              ] as $h): ?>
              <div style="font-size:11.5px;font-weight:600;height:1.4em;line-height:1.4em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $h ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Hero content -->
  <div class="container" style="padding-top:68px;padding-bottom:72px;">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="bf-section-eyebrow mb-3"><span class="bf-section-eyebrow-num">01</span> Africa's #1 Construction Network</div>
        <h1 class="bf-hero-headline mb-4">
          Find & hire<br><em>Africa's finest</em><br>construction talent.
        </h1>
        <p class="bf-hero-sub mb-5">Verified architects, engineers, contractors and suppliers — all in one place. Post a project, invite professionals, and build with confidence.</p>

        <!-- Search bar v2 -->
        <form class="bf-search3 mb-4" action="/pages/search.php" method="get">
          <i class="bi bi-search bf-search3-ic"></i>
          <input type="text" name="q" placeholder="Search professionals, materials, equipment…">
          <div class="bf-search3-cat">
            <i class="bi bi-grid-fill"></i>
            <select name="cat" aria-label="Category">
              <option value="">All</option>
              <option>Professionals</option>
              <option>Materials</option>
              <option>Equipment</option>
              <option>Facilities</option>
            </select>
            <i class="bi bi-chevron-down bf-search3-chev"></i>
          </div>
          <button type="submit" class="bf-search3-btn" aria-label="Search"><i class="bi bi-arrow-right"></i></button>
        </form>

        <!-- Quick-access tags -->
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ([
            ['bi-hard-hat','Architects'],
            ['bi-rulers','Structural Eng.'],
            ['bi-lightning','MEP Engineers'],
            ['bi-hammer','Contractors'],
            ['bi-calculator','QS / BOQ'],
            ['bi-palette2','Interior Design'],
          ] as [$ic,$c]): ?>
          <a href="/pages/marketplace/professionals.php" class="bf-search-tag">
            <i class="bi <?= $ic ?>"></i><?= $c ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Stats panel -->
      <div class="col-lg-6 d-none d-lg-block">
        <div class="bf-hero-panel">
          <div class="bf-hero-panel-header">
            <div class="bf-hero-panel-title">Platform overview</div>
            <div class="bf-hero-panel-live">Live data</div>
          </div>
          <div class="bf-stats-grid">
            <div class="bf-stat-block">
              <div class="bf-stat-num" id="statProfs">48,621<span style="font-size:16px;color:var(--ink-4);">+</span></div>
              <div class="bf-stat-label">Verified Professionals <span class="bf-stat-change up">↑ 12%</span></div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num" style="color:#c0392b;" id="statBid">KES 2.4B</div>
              <div class="bf-stat-label">Active Bid Value <span class="bf-stat-change up">↑ 8%</span></div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num">4.8<span style="font-size:18px;color:#f59e0b;"> ★</span></div>
              <div class="bf-stat-label">Platform Rating <span class="bf-stat-change up">↑ 0.2</span></div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num">47</div>
              <div class="bf-stat-label">Counties · 6 Countries</div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num" id="statEscrow">$84M</div>
              <div class="bf-stat-label">Escrow Active <span class="bf-stat-change up">↑ 19%</span></div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num">98%</div>
              <div class="bf-stat-label">Dispute Resolution Rate</div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num" id="statToday" style="color:#22c55e;">48</div>
              <div class="bf-stat-label">New Projects Today</div>
            </div>
            <div class="bf-stat-block">
              <div class="bf-stat-num" id="statReg">186</div>
              <div class="bf-stat-label">Registrations Today</div>
            </div>
          </div>
          <div class="bf-hero-activity">
            <div class="bf-activity-title" style="display:flex;align-items:center;justify-content:space-between;">
              <span>Recent activity</span>
              <span style="font-size:10px;color:#22c55e;font-weight:700;display:flex;align-items:center;gap:4px;"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 1.5s infinite;"></span> <span id="onlineCount">1,247</span> online now</span>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt=""></div>
              <div class="bf-activity-text"><strong>David K.</strong> placed a bid on Westlands Office Block <span style="color:#c0392b;font-size:10px;font-weight:700;margin-left:4px;">KES 4.2M</span></div>
              <div class="bf-activity-time">2m ago</div>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt=""></div>
              <div class="bf-activity-text"><strong>Fatuma H.</strong> earned <span style="color:#1e3a5f;font-weight:700;">NCA Verified</span> badge · Civil Engineer</div>
              <div class="bf-activity-time">6m ago</div>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av" style="background:#f0fdf4;display:flex;align-items:center;justify-content:center;"><i class="bi bi-building" style="font-size:13px;color:#166534;"></i></div>
              <div class="bf-activity-text">Project posted: <strong>12km Murram Road, Nakuru</strong> · KES 18M</div>
              <div class="bf-activity-time">11m ago</div>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av"><img src="https://randomuser.me/api/portraits/men/7.jpg" alt=""></div>
              <div class="bf-activity-text"><strong>Samuel O.</strong> marked Karen Luxury Apts MEP phase complete</div>
              <div class="bf-activity-time">18m ago</div>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av"><img src="https://randomuser.me/api/portraits/women/22.jpg" alt=""></div>
              <div class="bf-activity-text"><strong>Priya M.</strong> hired <strong>Kofi Tetteh</strong> for Accra retail fit-out</div>
              <div class="bf-activity-time">22m ago</div>
            </div>
            <div class="bf-activity-item">
              <div class="bf-activity-av" style="background:#fef2f2;display:flex;align-items:center;justify-content:center;"><i class="bi bi-clock" style="font-size:12px;color:#c0392b;"></i></div>
              <div class="bf-activity-text"><strong>3 bids closing</strong> in next 24 hrs — <a href="/pages/bids/index.php" style="color:#c0392b;font-weight:700;">view now</a></div>
              <div class="bf-activity-time">now</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     TRADE MARQUEE
═══════════════════════════════════════════ -->
<div class="bf-trade-marquee" aria-hidden="true">
  <div class="bf-marquee-track">
    <?php
    $trades = [
      ['bi-hammer','Masons'],['bi-lightning-charge','Electricians'],['bi-brush','Painters'],
      ['bi-droplet','Plumbers'],['bi-fire','Welders'],['bi-tools','Carpenters'],
      ['bi-rulers','Architects'],['bi-diagram-3','Civil Engineers'],
      ['bi-calculator','Quantity Surveyors'],['bi-bricks','Structural Engineers'],
      ['bi-lightning','MEP Engineers'],['bi-palette','Interior Designers'],
      ['bi-tree','Landscapers'],['bi-kanban','Project Managers'],['bi-geo-alt','Land Surveyors'],
    ];
    for ($r = 0; $r < 2; $r++): foreach ($trades as $i => [$icon,$label]): ?>
    <span class="bf-mq-item"><i class="bi <?= $icon ?>"></i> <?= $label ?></span>
    <?php if ($i < count($trades)-1): ?><span class="bf-mq-sep">·</span><?php endif; ?>
    <?php endforeach; endfor; ?>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     01 — TOP RATED PROFESSIONALS
═══════════════════════════════════════════ -->
<?php /* Facebook-style feed: capture content sections below, then shuffle their order on every load */ ob_start(); ?>

<!-- Sponsored ad bands (shuffle into random slots each refresh) -->
<section class="bf-section bf-ad-band" style="padding-top:26px;padding-bottom:26px;">
  <div class="container">
    <a href="#" class="bf-ad-banner" style="background:linear-gradient(120deg,#0d1f36,#1e3a5f 60%,#2a4d78);">
      <span class="ad-tag">Ad · Sponsored</span>
      <i class="bi bi-bag-check-fill" style="font-size:34px;color:#ffd76a;flex-shrink:0;"></i>
      <div style="flex:1;min-width:200px;">
        <div style="font-size:17px;font-weight:900;line-height:1.2;">Buildmart Pro — materials at trade prices.</div>
        <div style="font-size:12.5px;opacity:.82;margin-top:4px;">Cement, steel, finishes &amp; tools delivered to your site. Free delivery over KES 50,000.</div>
      </div>
      <span style="background:#c0392b;color:#fff;font-size:12.5px;font-weight:700;padding:10px 22px;border-radius:9px;white-space:nowrap;">Shop now <i class="bi bi-arrow-right"></i></span>
    </a>
  </div>
</section>
<section class="bf-section bf-ad-band" style="padding-top:26px;padding-bottom:26px;">
  <div class="container">
    <a href="#" class="bf-ad-banner" style="background:linear-gradient(120deg,#14321f,#166534 65%,#1e7a45);">
      <span class="ad-tag">Ad</span>
      <i class="bi bi-shield-fill-check" style="font-size:34px;color:#ffd76a;flex-shrink:0;"></i>
      <div style="flex:1;min-width:200px;">
        <div style="font-size:17px;font-weight:900;line-height:1.2;">Protect every hire with bildfie Escrow.</div>
        <div style="font-size:12.5px;opacity:.82;margin-top:4px;">Funds release only when milestones are met — zero fees on your first contract.</div>
      </div>
      <span style="background:#c0392b;color:#fff;font-size:12.5px;font-weight:700;padding:10px 22px;border-radius:9px;white-space:nowrap;">Enable escrow <i class="bi bi-arrow-right"></i></span>
    </a>
  </div>
</section>

<?php
// Top-rated professionals from the providers table — the whole section is hidden until real providers exist.
$trColors = [['#dcfce7','#166534'],['#dbeafe','#1e40af'],['#fce7f3','#9d174d'],['#ede9fe','#5b21b6'],['#fef9c3','#854d0e'],['#ffedd5','#9a3412'],['#ccfbf1','#134e4a'],['#fee2e2','#991b1b'],['#fef3c7','#92400e'],['#e0e7ff','#1e1b4b']];
$topRated = [];
foreach (db_all("SELECT id,name,headline,location,rating,reviews_count,day_rate,photo_url,badge FROM providers WHERE status='active' AND photo_url IS NOT NULL ORDER BY is_featured DESC, rating DESC, reviews_count DESC LIMIT 10") as $i=>$p) {
  [$bg,$dark] = $trColors[$i % count($trColors)];
  $topRated[] = [(int)$p['id'], $p['photo_url'], $p['name'], $p['headline'] ?: 'Professional', $p['rating'], (int)$p['reviews_count'], $p['day_rate'] ?: 'On request', $p['location'] ?: 'Kenya', $p['badge'] ?: 'Verified Pro', $bg, $dark];
}
if ($topRated): ?>
<section class="bf-section" style="background:var(--surface);border-top:1px solid var(--line);">
  <div class="container">

    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">01</span> Top Rated</div>
        <h2 class="bf-section-title mb-1">Highest-rated professionals</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Sorted by verified project completions, client reviews and trust score</p>
      </div>
      <a href="/pages/marketplace/professionals.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">View all 84,200+ →</a>
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3 mb-5">
      <?php foreach ($topRated as [$pid,$portrait,$name,$role,$rat,$projs,$rate,$loc,$badge,$bg,$dark]):
        $href = '/pages/marketplace/professional.php?id=' . $pid; ?>
      <div class="col">
        <div class="bf-toprated-card" data-href="<?= htmlspecialchars($href, ENT_QUOTES) ?>" style="cursor:pointer;">
          <span class="bf-toprated-badge" style="background:<?= $bg ?>;color:<?= $dark ?>;"><?= $badge ?></span>
          <div class="bf-toprated-photo">
            <img src="<?= htmlspecialchars($portrait, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name) ?>">
            <div class="bf-toprated-verify"><i class="bi bi-patch-check-fill"></i></div>
          </div>
          <div class="bf-toprated-name"><?= $name ?></div>
          <div class="bf-toprated-role"><?= $role ?></div>
          <div class="bf-toprated-rating">
            <i class="bi bi-star-fill"></i> <?= $rat ?>
            <span style="font-size:9.5px;color:var(--ink-4);font-weight:500;">(<?= $projs ?>)</span>
          </div>
          <div class="bf-toprated-rate"><?= $rate ?></div>
          <div class="bf-toprated-loc"><i class="bi bi-geo-alt-fill" style="font-size:9px;color:#c0392b;"></i> <?= $loc ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>


    <!-- 02 — Service Providers -->
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">02</span> Service Providers</div>
        <h2 class="bf-section-title mb-0">Browse all professionals by trade</h2>
      </div>
      <a href="/pages/marketplace/professionals.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All professionals →</a>
    </div>

    <div class="bf-tabs mb-4">
      <?php foreach (['Contractors','Skilled Workers','Engineers','Architects','Specialists'] as $t): ?>
      <button class="bf-tab <?= $t==='Contractors'?'active':'' ?>"><?= $t ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-3">
      <?php
      $providers = []; // dummy professionals removed — wired to real providers later via onboarding
      foreach ($providers as [$portrait,$name,$role,$yrs,$loc,$rat,$projs,$rate,$tagsStr,$activeLabel,$status,$verify,$bio]):
        $href = '/pages/marketplace/professional.php?' . http_build_query(['name'=>$name,'role'=>$role,'loc'=>$loc,'rate'=>$rate.'/hr','rating'=>$rat,'reviews'=>$projs,'photo'=>"https://randomuser.me/api/portraits/{$portrait}.jpg",'badge'=>'Verified Pro']); ?>
      <div class="col-md-6 col-xl-3">
        <div class="bf-prov-card" data-href="<?= htmlspecialchars($href, ENT_QUOTES) ?>" style="display:flex;flex-direction:column;height:100%;cursor:pointer;">

          <!-- Header with photo -->
          <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;">
            <div class="bf-prov-photo">
              <img src="https://randomuser.me/api/portraits/<?= $portrait ?>.jpg" alt="<?= htmlspecialchars($name) ?>">
            </div>
            <div style="flex:1;min-width:0;">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                <div class="bf-prov-name"><?= $name ?></div>
                <i class="bi bi-patch-check-fill" style="color:#1e3a5f;font-size:13px;flex-shrink:0;" title="Verified"></i>
              </div>
              <div class="bf-prov-meta"><?= $role ?> · <?= $yrs ?></div>
              <div style="font-size:10.5px;color:var(--ink-3);display:flex;align-items:center;gap:3px;margin-top:2px;">
                <i class="bi bi-geo-alt" style="font-size:9px;color:#c0392b;"></i><?= $loc ?>
              </div>
            </div>
            <span class="bf-prov-avail-badge <?= $status==='available'?'avl':'bsy' ?>"><?= $status==='available'?'AVAIL.':'BUSY' ?></span>
          </div>

          <!-- Verify row -->
          <div class="bf-prov-verify-row mb-2">
            <i class="bi bi-shield-fill-check" style="color:#1e3a5f;"></i> <?= $verify ?>
          </div>

          <!-- Stats -->
          <div class="bf-prov-stats mb-3" style="background:var(--surface);border-radius:8px;padding:8px 12px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;text-align:center;">
            <div>
              <div style="font-size:14px;font-weight:800;color:var(--ink);"><?= $rat ?><i class="bi bi-star-fill" style="color:#f59e0b;font-size:10px;margin-left:2px;"></i></div>
              <div style="font-size:9px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.06em;">Rating</div>
            </div>
            <div>
              <div style="font-size:14px;font-weight:800;color:var(--ink);"><?= $projs ?></div>
              <div style="font-size:9px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.06em;">Projects</div>
            </div>
            <div>
              <div style="font-size:14px;font-weight:800;color:#1e3a5f;"><?= $rate ?><span style="font-size:9px;color:var(--ink-4);font-weight:500;">/hr</span></div>
              <div style="font-size:9px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.06em;">Rate</div>
            </div>
          </div>

          <!-- Bio -->
          <p style="font-size:11.5px;color:var(--ink-3);line-height:1.6;margin-bottom:10px;"><?= $bio ?></p>

          <!-- Tags -->
          <div class="d-flex flex-wrap gap-1 mb-3">
            <?php foreach (explode(' ', $tagsStr) as $tag): ?>
            <span class="bf-prov-tag"><?= $tag ?></span>
            <?php endforeach; ?>
          </div>

          <!-- Footer -->
          <div class="d-flex justify-content-between align-items-center mt-auto" style="padding-top:12px;border-top:1px solid var(--line-2);">
            <div style="font-size:11px;color:var(--ink-3);"><i class="bi bi-kanban" style="font-size:10px;margin-right:3px;"></i><?= $activeLabel ?></div>
            <div class="d-flex gap-2">
              <button class="bf-action-outline" style="font-size:11px;padding:6px 14px;">View</button>
              <button class="bf-action-dark" style="font-size:11px;padding:6px 14px;"><i class="bi bi-send me-1" style="font-size:10px;"></i>Invite</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     OPEN BIDS
═══════════════════════════════════════════ -->
<?php endif; /* /top-rated professionals gate */ ?>

<section class="bf-section" style="border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">03</span> Open Bids</div>
        <h2 class="bf-section-title mb-1">Projects seeking your skills now</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Live project postings from verified owners. Submit your bid directly — no middlemen.</p>
      </div>
      <a href="/pages/bids/index.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All open projects →</a>
    </div>
    <div class="row g-3">
      <?php
      $bids = [
        ['4-Storey Residential Apartment — Structural & Finishing Works','Daniel Otieno','Verified Owner · Kilimani, NBI','$48,000–$65,000','8 months','May 2025',['Structural','Finishing','Tiling','Painting'],'14','0d 18h','closing'],
        ['Architectural Design + BOQ — 5-Bedroom Villa, Dubai','Aisha Al-Rashid','Verified Owner · Dubai, UAE','$22,000–$30,000','3 months','Immediate',['Architecture','BOQ','3D Rendering'],'3','5d 2h','new-bid'],
        ['Road Construction — 12km Murram Access Road, Nakuru County','County Roads Authority','Government Entity · Nakuru, KE','$280,000+','14 months','June 2025',['Civil Eng.','Earthmoving','Compaction','Drainage'],'27','3d 14h','hot'],
        ['MEP Installation — Commercial Office Block, Lagos Island','Landline Properties Ltd','Verified Owner · Lagos, NG','$35,000–$52,000','5 months','Immediate',['MEP Engineer','Electrician','Plumber'],'8','3d 8h','new-bid'],
      ];
      foreach ($bids as [$title,$client,$meta,$budget,$dur,$start,$trades,$count,$timer,$urgency]): ?>
      <div class="col-md-6 col-xl-3">
        <div class="bf-bid-v2 <?= $urgency ?>">
          <div class="bf-bid-v2-badge <?= $urgency ?>">
            <?php if ($urgency==='closing'): ?>
            <i class="bi bi-hourglass-split"></i> Closing Soon
            <?php elseif ($urgency==='hot'): ?>
            <i class="bi bi-fire"></i> High Budget
            <?php else: ?>
            <i class="bi bi-patch-check"></i> Just Posted
            <?php endif; ?>
          </div>
          <div class="bf-bid-v2-title"><?= $title ?></div>
          <div class="bf-bid-v2-client">
            <i class="bi bi-person-badge"></i>
            <div>
              <div style="font-weight:600;color:var(--ink);"><?= $client ?></div>
              <div style="font-size:10px;color:var(--ink-4);"><?= $meta ?></div>
            </div>
          </div>
          <div class="bf-bid-v2-grid">
            <div>
              <div class="bf-bid-v2-stat-label">Budget</div>
              <div class="bf-bid-v2-stat-val accent"><?= $budget ?></div>
            </div>
            <div>
              <div class="bf-bid-v2-stat-label">Duration</div>
              <div class="bf-bid-v2-stat-val"><?= $dur ?></div>
            </div>
            <div>
              <div class="bf-bid-v2-stat-label">Start</div>
              <div class="bf-bid-v2-stat-val"><?= $start ?></div>
            </div>
            <div>
              <div class="bf-bid-v2-stat-label">Bids</div>
              <div class="bf-bid-v2-stat-val"><?= $count ?> submitted</div>
            </div>
          </div>
          <div class="bf-bid-v2-trades">
            <?php foreach ($trades as $t): ?><span class="bf-bid-v2-trade"><?= $t ?></span><?php endforeach; ?>
          </div>
          <div class="bf-bid-v2-footer">
            <div class="bf-bid-v2-timer">
              <i class="bi bi-clock"></i>
              <strong><?= $timer ?></strong> remaining
            </div>
            <?php if (is_logged_in()): ?>
            <button class="bf-action-dark" style="font-size:11px;padding:6px 16px;">Place Bid</button>
            <?php else: ?>
            <a href="/pages/auth/login.php" class="bf-action-dark" style="font-size:11px;padding:6px 16px;">Sign In to Bid</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     LIVE PROJECT MAP
═══════════════════════════════════════════ -->
<section class="bf-section bf-map-section" style="background:var(--surface);border-top:1px solid var(--line);padding-top:40px;padding-bottom:40px;">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3">
      <div>
        <div class="bf-section-eyebrow mb-1"><i class="bi bi-geo-alt-fill" style="color:#c0392b;"></i> Projects Near Your Location</div>
        <p style="font-size:12px;color:var(--ink-3);margin:0;">Showing within 50km of Nairobi CBD · hover a pin for project details</p>
      </div>
      <a href="/pages/projects/index.php" class="bf-action-dark" style="font-size:12px;">Open Full Map</a>
    </div>
    <div class="bf-map-wrap mb-3">
      <div id="bildfie-map"></div>
    </div>
    <div class="bf-map-legend">
      <div class="bf-map-legend-item"><div class="bf-map-dot" style="background:#1e3a5f;"></div> Commercial</div>
      <div class="bf-map-legend-item"><div class="bf-map-dot" style="background:#f59e0b;"></div> Residential</div>
      <div class="bf-map-legend-item"><div class="bf-map-dot" style="background:#22c55e;"></div> Infrastructure</div>
      <div class="bf-map-legend-item"><div class="bf-map-dot" style="background:#a855f7;"></div> Industrial</div>
      <div class="bf-map-legend-item"><div class="bf-map-dot" style="background:#ef4444;"></div> Government</div>
      <div class="ms-auto" style="font-size:13px;color:var(--ink-3);"><strong style="color:#1e3a5f;font-size:18px;">26</strong> open projects nearby</div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     03 — MATERIALS & HARDWARE
     (inline ad floated below header)
═══════════════════════════════════════════ -->
<section class="bf-section" style="border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-2">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">04</span> Materials & Hardware</div>
        <h2 class="bf-section-title mb-0">Verified suppliers with live pricing</h2>
      </div>
      <a href="/pages/marketplace/materials.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All suppliers →</a>
    </div>

    <!-- Inline ad -->
    <div class="bf-inline-ad-inner mb-4" style="padding-bottom:14px;border-bottom:1px solid var(--line-2);">
      <span class="bf-inline-ad-badge">⏳ Limited Time</span>
      <span class="bf-inline-ad-text"><strong>First project escrow fee — waived.</strong> Zero platform fee on escrow up to KES 8,000 for your first project.</span>
      <span class="bf-inline-ad-sponsor">Sponsored · Bamburi Cement</span>
      <button class="bf-inline-ad-btn red">Claim offer</button>
    </div>

    <div class="row g-3">
      <?php
      // Live: top materials suppliers + their products (2 queries, grouped — no N+1).
      $homeProd = [];
      foreach (db_all("SELECT supplier_id, title, price, price_change FROM listings WHERE kind='materials' AND status='active' AND supplier_id IS NOT NULL ORDER BY sort_order, id") as $pr) {
        $sid = (int)$pr['supplier_id'];
        if (count($homeProd[$sid] ?? []) < 6) $homeProd[$sid][] = [$pr['title'], $pr['price'], $pr['price_change'] ?: null];
      }
      $suppliers = [];
      foreach (db_all("SELECT * FROM suppliers WHERE status='active' ORDER BY sort_order, name LIMIT 3") as $s) {
        $suppliers[] = [$s['code'], $s['name'], $s['location'], $s['rating'], $s['reviews_count'], $homeProd[(int)$s['id']] ?? [], $s['delivery_info'], $s['logo_bg'] ?: '#dcfce7', $s['logo_color'] ?: '#166534', 'open'];
      }
      foreach ($suppliers as [$code,$name,$area,$rat,$reviews,$products,$delivery,$bg,$color,$status]): ?>
      <div class="col-md-4">
        <div class="bf-supplier-card" data-href="/pages/marketplace/materials.php" style="cursor:pointer;">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="bf-supplier-logo" style="background:<?= $bg ?>;color:<?= $color ?>;"><?= $code ?></div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:13px;font-weight:700;color:var(--ink);"><?= $name ?></div>
              <div style="font-size:10.5px;color:var(--ink-3);display:flex;align-items:center;gap:4px;">
                <i class="bi bi-geo-alt" style="font-size:9px;color:#c0392b;"></i><?= $area ?>
              </div>
              <div style="font-size:10.5px;color:var(--ink-3);margin-top:1px;">
                <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i> <?= $rat ?> &nbsp;·&nbsp; <?= $reviews ?> reviews
              </div>
            </div>
            <span class="bf-supplier-open"><?= strtoupper($status) ?></span>
          </div>
          <div class="mb-3">
            <?php foreach ($products as [$pn,$pp,$chg]): ?>
            <div class="bf-product-row">
              <span class="bf-product-name"><?= $pn ?></span>
              <span class="bf-product-price d-flex align-items-center gap-1"><?= $pp ?>
                <?php if ($chg): $isUp = str_starts_with($chg,'up'); ?>
                <span class="bf-price-chg <?= $isUp?'up':'down' ?>"><?= $isUp?'+'.substr($chg,3):substr($chg,0) ?></span>
                <?php endif; ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="d-flex align-items-center justify-content-between" style="padding-top:10px;border-top:1px solid var(--line-2);">
            <div style="font-size:10.5px;color:var(--ink-3);"><i class="bi bi-truck" style="margin-right:4px;"></i><?= $delivery ?></div>
            <button class="bf-action-dark" style="font-size:11.5px;padding:7px 18px;background:#c0392b;">Order Now</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     04 — MATERIAL COSTS BY REGION
═══════════════════════════════════════════ -->
<section class="bf-section" style="background:var(--surface);border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">05</span> Material Costs by Region</div>
        <h2 class="bf-section-title mb-1">Cheapest prices near your site</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Live pricing from verified suppliers. Updated daily from 22 countries.</p>
      </div>
      <a href="/pages/marketplace/materials.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">Full global index →</a>
    </div>
    <div class="row g-3">
      <?php
      $regions = [
        ['🇰🇪','KE','Nairobi, Kenya',   'KES · USD equiv.',[['Cement 50kg bag','$8.20',null],['Steel Rod 12mm (pc)','$6.50','up +3%'],['Building Sand (tonne)','$32','down -2%'],['Iron Sheet 32G','$5.60',null],['Concrete Block 6"','$0.42',null],['Ready-mix Concrete m³','$82','up +4%']]],
        ['🇳🇬','NG','Lagos, Nigeria',   'NGN · USD equiv.',[['Cement 50kg bag','$6.00','up +6%'],['Steel Rod 12mm (pc)','$3.20','up +4%'],['Sharp Sand (tonne)','$21',null],['Iron Sheet 32G','$4.80','down -1%'],['9-inch Block','$0.48',null],['Ready-mix Concrete m³','$74','up +2%']]],
        ['🇬🇭','GH','Accra, Ghana',     'GHS · USD equiv.',[['Cement 50kg bag','$6.80',null],['Steel Rod 12mm (pc)','$4.20','up +2%'],['River Sand (tonne)','$28','down -4%'],['Iron Sheet 32G','$5.20',null],['6-inch Block','$0.66',null],['Ready-mix Concrete m³','$78','down -3%']]],
        ['🇮🇳','IN','Mumbai, India',    'INR · USD equiv.',[['Cement 50kg bag','$4.80',null],['TMT Steel Bar 12mm','$3.40','up +5%'],['M-sand (tonne)','$18',null],['Clay Brick (1000)','$96','down -2%'],['AAC Block 200mm','$0.72',null],['Ready-mix Concrete m³','$62',null]]],
        ['🇧🇷','BR','São Paulo, Brazil','BRL · USD equiv.',[['Cement 50kg bag','$9.40','down -1%'],['CA-60 Rebar 10mm','$5.80','up +3%'],['Areia (tonne)','$24',null],['Ceramic Block','$0.58',null],['Steel Sheet 4mm','$38','up +7%'],['Ready-mix Concrete m³','$88',null]]],
        ['🇦🇪','AE','Dubai, UAE',       'AED · USD equiv.',[['Cement 50kg bag','$11.20',null],['Steel Rod 16mm','$8.80','up +2%'],['Washed Sand (tonne)','$44',null],['Hollow Block 200mm','$1.20','down -1%'],['Structural Steel','$420/t','up +4%'],['Ready-mix Concrete m³','$118',null]]],
      ];
      foreach ($regions as [$flag,$code,$country,$currency,$prices]): ?>
      <div class="col-md-6 col-xl-4">
        <div class="bf-price-card" data-href="/pages/services/index.php" style="cursor:pointer;">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="bf-price-flag"><?= $flag ?></div>
              <div>
                <div style="font-size:10px;font-weight:800;color:var(--ink-3);letter-spacing:.05em;"><?= $code ?></div>
                <div class="bf-price-country"><?= $country ?></div>
                <div class="bf-price-currency"><?= $currency ?></div>
              </div>
            </div>
            <span class="bf-price-updated"><i class="bi bi-circle-fill" style="font-size:6px;margin-right:3px;"></i>Updated today</span>
          </div>
          <?php foreach ($prices as [$mat,$val,$chg]): ?>
          <div class="bf-price-row">
            <span class="bf-price-mat"><?= $mat ?></span>
            <span class="bf-price-val">
              <?= $val ?>
              <?php if ($chg): $isUp = str_starts_with($chg,'up'); ?>
              <span class="bf-price-chg <?= $isUp?'up':'down' ?>"><?= $isUp?'↑ +'.substr($chg,3):'↓ '.substr($chg,5) ?></span>
              <?php endif; ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     05 — NEARBY FACILITIES (Quarries, Sand Bays etc.)
═══════════════════════════════════════════ -->
<section class="bf-section" style="border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">06</span> Nearby Facilities</div>
        <h2 class="bf-section-title mb-1">Quarries, sand bays, rivers &amp; site services</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Raw material access points and site-support facilities near your location</p>
      </div>
      <a href="/pages/marketplace/facilities.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">View on map →</a>
    </div>
    <div class="row g-3">
      <?php
      $facilities = [
        ['bi-gem','#fff7ed','#9a3412','Kabete Stone Quarry','Granite Quarry','2.4 km','Large granite quarry supplying crushed stone, ballast and hardcore. 8 active trucks daily. Mon–Sat 6am–6pm.',['Granite','Ballast','Hardcore','Delivery'],'From $8/tonne'],
        ['bi-water','#f0f9ff','#1d4ed8','Athi River Sand Bay','River Sand Bay','5.8 km','Natural river sand extraction. Fine and coarse grades available. Self-loading possible. Transport partners on site.',['River Sand','Coarse','Fine Grade'],'From $18/tonne'],
        ['bi-droplet-half','#eff6ff','#1e40af','Ruiru Water Point','Water Access','3.1 km','Licensed county water for construction. Tanker loading station. 24-hour access for commercial contractors.',['Tanker Fill','Licensed','24 hr'],'$0.04/litre'],
        ['bi-tools','#f0fdf4','#166534','Westlands Tool Hire','Equipment Hire','1.2 km','Scaffolding, mixers, compactors, vibrators, generators. Daily and weekly rates. Site delivery available.',['Daily Hire','Weekly','Delivery'],'From $12/day'],
        ['bi-layers','#fef9c3','#854d0e','Juja Murram Pit','Murram & Fill','12 km','Murram and fill material for roads and compacted fills. Sub-base material. Lorry-load pricing. Open Tue–Sat.',['Murram','Fill Soil','Sub-base'],'From $6/m³'],
        ['bi-building-up','#f5f3ff','#5b21b6','Ind. Area Concrete Plant','Ready-Mix Plant','4.5 km','Ready-mix concrete plant. Grades C15–C50. Min 2m³ order. Pump trucks available for high-rise pours.',['Ready Mix','C15–C50','Pump Trucks'],'From $74/m³'],
        ['bi-fuel-pump','#fff7ed','#c2410c','Ngong Rd Fuel Depot','Fuel Depot','0.9 km','Bulk diesel for generators and machinery. Drum purchase and on-site delivery. Track compliance certs.',['Diesel','Bulk','On-site Delivery'],'Market price'],
        ['bi-tree','#f0fdf4','#166534','Ruaka Timber Yard','Timber Yard','7.3 km','Structural and finish timber. Treated hardwood and softwood. Cut-to-size service. Wholesale for large orders.',['Hardwood','Softwood','Treated'],'From $2.80/m'],
      ];
      foreach ($facilities as [$icon,$bg,$iconColor,$name,$type,$dist,$desc,$tags,$price]): ?>
      <div class="col-md-6 col-xl-3">
        <div class="bf-facility-card" data-href="/pages/marketplace/facilities.php" style="cursor:pointer;">
          <div class="bf-facility-card-header">
            <div class="bf-facility-icon" style="background:<?= $bg ?>;color:<?= $iconColor ?>;">
              <i class="bi <?= $icon ?>"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="bf-facility-dist"><i class="bi bi-geo-alt" style="font-size:9px;"></i> <?= $dist ?> away</div>
              <div class="bf-facility-name"><?= $name ?></div>
              <div style="font-size:10px;color:var(--ink-4);font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $type ?></div>
            </div>
          </div>
          <div class="bf-facility-card-body">
            <div class="bf-facility-desc"><?= $desc ?></div>
            <div class="d-flex flex-wrap gap-1 mb-0">
              <?php foreach ($tags as $tag): ?><span class="bf-facility-tag"><?= $tag ?></span><?php endforeach; ?>
            </div>
          </div>
          <div class="bf-facility-card-footer">
            <div class="bf-facility-price"><?= $price ?></div>
            <button class="bf-action-dark" style="font-size:11px;padding:5px 14px;">Directions</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     06 — TRANSPORT & LOGISTICS
═══════════════════════════════════════════ -->
<section class="bf-section" style="background:var(--surface);border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">07</span> Transport & Logistics</div>
        <h2 class="bf-section-title mb-1">Pickups, lorries &amp; delivery fleets</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Available per-trip, daily, weekly or full-contract — GPS-tracked &amp; insured</p>
      </div>
      <a href="/pages/marketplace/transport.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All transport →</a>
    </div>
    <div class="row g-3">
      <?php
      $transports = [
        ['bi-truck','#f0f9ff','7-Tonne Lorry','Moses Kariuki · Nairobi',     '$45/trip · $180/day','now',  ['Available Now','Per Trip','On Contract'],'Specialises in sand, ballast and construction material haulage. 80km radius. Licensed, insured, GPS tracked.','4.8','312 trips','https://picsum.photos/seed/bf-lorry/440/240'],
        ['bi-truck-flatbed','#f0fdf4','½-Tonne Pickup','Peter Ouma · Nairobi', '$25/trip · $80/day', 'now',  ['Available Now','Per Trip'],             'Light materials pickup and site runs. Ideal for hardware-to-site. Half-tonne. Flexible same-day booking.',        '4.9','486 trips','https://picsum.photos/seed/bf-pickup/440/240'],
        ['bi-box-seam','#fff7ed','3-Truck Fleet · Regional','Sunrise Haulage Ltd', 'From $120/day',  'avl',  ['On Contract','Per Trip'],              'Commercial fleet: 10-tonne, 7-tonne, pickup. GPS tracked. Long-distance available. Min 3-day contract.',       '4.7','1,240 trips','https://picsum.photos/seed/bf-fleet/440/240'],
        ['bi-arrow-down-up','#fef9c3','Low Loader · Heavy Haul','SteelMove Express','$220/trip',     'soon', ['Per Trip','On Contract'],              'Heavy machinery and structural steel transport. Low loader with permit handling. Crane-assist on request.','4.8','84 trips','https://picsum.photos/seed/bf-lowloader/440/240'],
      ];
      foreach ($transports as [$icon,$bg,$type,$name,$rate,$status,$tags,$desc,$rat,$trips,$img]): ?>
      <div class="col-md-6 col-xl-3">
        <div class="bf-transport-v2">
          <div class="bf-transport-v2-cover" style="background-image:url('<?= $img ?>');">
            <span class="bf-transport-badge <?= $status ?>"><?= strtoupper($status==='now'?'NOW':($status==='avl'?'AVAIL.':'FRI +')) ?></span>
            <div class="bf-transport-v2-cover-cap">
              <div class="t"><?= $type ?></div>
              <div class="n"><?= $name ?></div>
            </div>
          </div>
          <div class="bf-transport-v2-body">
            <div style="font-size:14px;font-weight:800;color:#c0392b;margin-bottom:10px;"><?= $rate ?></div>
            <div class="d-flex flex-wrap gap-1 mb-10">
              <?php foreach ($tags as $tag): ?><span class="bf-transport-tag"><?= $tag ?></span><?php endforeach; ?>
            </div>
            <p style="font-size:11.5px;color:var(--ink-3);line-height:1.6;margin:10px 0;"><?= $desc ?></p>
            <div class="d-flex justify-content-between align-items-center mt-auto" style="padding-top:10px;border-top:1px solid var(--line-2);">
              <div style="font-size:11px;color:var(--ink-3);">
                <i class="bi bi-star-fill" style="color:#f59e0b;font-size:10px;"></i> <?= $rat ?> &nbsp;·&nbsp; <?= $trips ?>
              </div>
              <div class="d-flex gap-2">
                <button class="bf-action-outline" style="font-size:11px;padding:5px 12px;">View</button>
                <button class="bf-action-dark" style="font-size:11px;padding:5px 12px;background:#1e3a5f;">Book</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     07 — EQUIPMENT HIRE
═══════════════════════════════════════════ -->
<section class="bf-section" style="border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-2">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">08</span> Equipment Hire</div>
        <h2 class="bf-section-title mb-1">Construction equipment,<br>ready when you are.</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Hire by the day, week or project. Verified operators available.</p>
      </div>
      <a href="/pages/marketplace/equipment.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All equipment →</a>
    </div>

    <!-- Stats bar -->
    <div class="row g-0 mb-4 mt-3" style="border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);overflow:hidden;">
      <?php foreach ([['1,240+','Units Available Globally'],['$180','Starting / Day'],['48 hrs','Avg. Delivery to Site'],['98%','Maintenance Compliance']] as $i => [$n,$l]): ?>
      <div class="col-6 col-md-3 text-center py-3 px-2" style="<?= $i<3?'border-right:1px solid var(--line);':'' ?>">
        <div style="font-size:22px;font-weight:900;color:#1e3a5f;letter-spacing:-.03em;"><?= $n ?></div>
        <div style="font-size:10px;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;"><?= $l ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Category pills -->
    <div class="bf-cat-pills mb-4">
      <?php foreach (['bi-grid','bi-truck','bi-building-up','bi-fan','bi-lightning-charge','bi-ladder','bi-moisture'] as $i => $icon): ?>
      <?php $labels = ['All Equipment','Earthmoving','Lifting & Cranes','Compaction','Power & Generators','Access & Scaffolding','Concrete & Mixing']; ?>
      <button class="bf-cat-pill <?= $i===0?'active':'' ?>"><i class="bi <?= $icon ?>"></i><?= $labels[$i] ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-3">
      <?php
      $equipment = [
        ['https://picsum.photos/id/96/800/400', 'EARTHMOVING · 20 TONNE',   'CAT 320 Excavator',         [['BUCKET CAP','0.9 m³'],['DIG DEPTH','6.5 m'],['REACH','9.8 m'],['ENGINE','104 kW']],[['Daily','$480'],['Weekly (5 days)','$2,100'],['Monthly','$7,200']],'4 active · Nairobi · Operator optional','4.9','84 hires','avl'],
        ['https://picsum.photos/id/376/800/400','LIFTING · 16T CAPACITY',  'Liebherr 280 EC-H Crane',   [['MAX LOAD','16 t'],['JIB LENGTH','65 m'],['HOOK HEIGHT','79 m'],['TIP LOAD','4.4 t']],[['Weekly','$4,800'],['Monthly','$16,500'],['Project rate','POA']],'Available Fri · Lagos · Operator incl.','5.0','31 hires','avl'],
        ['https://picsum.photos/id/209/800/400','COMPACTION · VIBRATORY',  'Dynapac CA2500D Compactor',[['DRUM WIDTH','1.67 m'],['OP. WEIGHT','12,500 kg'],['FREQUENCY','28/33 Hz'],['ENGINE','93 kW']],[['Daily','$280'],['Weekly (5 days)','$1,200'],['Monthly','$4,000']],'Available · Accra · Operator optional','4.8','62 hires','avl'],
        ['https://picsum.photos/id/111/800/400','POWER · DIESEL SILENT',   'Perkins 200 kVA Generator', [['OUTPUT','200 kVA'],['FUEL TANK','530 L'],['LOAD','160 kVA'],['ENGINE','6 Cyl']],[['Daily','$120'],['Weekly (5 days)','$520'],['Monthly','$1,800']],'Available · Nairobi · Delivery incl.','4.7','128 hires','avl'],
        ['https://picsum.photos/id/149/800/400','CONCRETE · 36M BOOM',     'Putzmeister BSF 36Z',       [['BOOM REACH','36 m'],['OUTPUT','160 m³/hr'],['FOLDS','5 section'],['PIPE','DN125']],[['Daily','$650'],['Weekly','$2,800'],['Monthly','POA']],'Available · Mombasa · Op. incl.','4.9','21 hires','avl'],
        ['https://picsum.photos/id/177/800/400','ACCESS · ELECTRIC 10M',   'JLG 3246ES Scissor Lift',   [['PLATFORM HT.','9.92 m'],['CAPACITY','450 kg'],['WIDTH','0.81 m'],['DRIVE','Electric']],[['Daily','$95'],['Weekly (5 days)','$420'],['Monthly','$1,400']],'Available · Nairobi · Self-drive','4.6','73 hires','avl'],
      ];
      foreach ($equipment as [$coverImg,$cat,$name,$specs,$rates,$avail,$rat,$hires,$availStatus]): ?>
      <div class="col-md-6 col-xl-4">
        <div class="bf-equip-card" data-href="/pages/marketplace/equipment.php" style="cursor:pointer;">
          <!-- Cover photo -->
          <div class="bf-equip-cover">
            <img src="<?= $coverImg ?>" alt="<?= htmlspecialchars($name) ?>">
            <div class="bf-equip-cover-overlay"></div>
            <div class="bf-equip-cover-cat"><?= $cat ?></div>
            <div class="bf-equip-cover-avail <?= $availStatus ?>"><?= $availStatus==='avl'?'AVAILABLE':'BUSY' ?></div>
            <div class="bf-equip-cover-name"><?= $name ?></div>
          </div>
          <!-- Body -->
          <div class="bf-equip-body">
            <!-- Spec grid -->
            <div class="row g-2 mb-2" style="padding-bottom:10px;border-bottom:1px solid var(--line-2);">
              <?php foreach ($specs as [$sl,$sv]): ?>
              <div class="col-6">
                <div class="bf-spec-label"><?= $sl ?></div>
                <div class="bf-spec-val"><?= $sv ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- Hire rates -->
            <div class="bf-hire-label">Hire Rates</div>
            <?php foreach ($rates as [$period,$price]): ?>
            <div class="bf-hire-row">
              <span class="bf-hire-period"><?= $period ?></span>
              <span class="bf-hire-price"><?= $price ?></span>
            </div>
            <?php endforeach; ?>
            <!-- Footer -->
            <div class="mt-auto" style="padding-top:12px;">
              <div style="font-size:11px;color:var(--ink-3);margin-bottom:4px;">
                <i class="bi bi-geo-alt" style="font-size:10px;color:#c0392b;"></i> <?= $avail ?>
              </div>
              <div style="font-size:11px;color:var(--ink-3);margin-bottom:12px;">
                <i class="bi bi-star-fill" style="color:#f59e0b;font-size:10px;"></i> <?= $rat ?> &nbsp;·&nbsp; <?= $hires ?>
              </div>
              <div class="d-flex gap-2">
                <button class="bf-action-outline" style="flex:1;justify-content:center;font-size:11px;">Details</button>
                <button class="bf-action-dark" style="flex:1;justify-content:center;font-size:11px;background:#c0392b;">Book Now</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     PROJECT CRM — compact feature section
     (replaces old dark CTA)
═══════════════════════════════════════════ -->
<section class="bf-crm-section">
  <div class="container">
    <div class="row align-items-center g-5">

      <!-- Left: Project CRM dashboard mockup -->
      <div class="col-lg-5">
        <div class="bf-crm-mockup">
          <!-- Header bar -->
          <div class="bf-crm-mock-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:8px;height:8px;border-radius:50%;background:#ef4444;"></div>
              <div style="width:8px;height:8px;border-radius:50%;background:#f59e0b;"></div>
              <div style="width:8px;height:8px;border-radius:50%;background:#22c55e;"></div>
            </div>
            <div style="flex:1;text-align:center;font-size:11px;font-weight:700;color:rgba(255,255,255,.6);">Karen Luxury Villas — Phase 2</div>
            <div style="font-size:9px;font-weight:700;background:#22c55e;color:#fff;padding:2px 8px;border-radius:4px;">ON TRACK</div>
          </div>
          <!-- Progress -->
          <div class="bf-crm-mock-body">
            <div style="margin-bottom:14px;">
              <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:5px;">
                <span style="font-weight:700;color:var(--ink);">Overall progress</span>
                <span style="font-weight:800;color:#1e3a5f;">68%</span>
              </div>
              <div style="height:7px;border-radius:999px;background:var(--line);overflow:hidden;">
                <div style="width:68%;height:100%;background:linear-gradient(90deg,#1e3a5f,#3b6cb7);border-radius:999px;"></div>
              </div>
            </div>
            <!-- Mini kanban tasks -->
            <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-4);margin-bottom:8px;">Active tasks</div>
            <?php foreach ([
              ['bi-check-circle-fill','#22c55e','Foundation works','James Kamau','Done'],
              ['bi-arrow-right-circle-fill','#f59e0b','Superstructure — Floor 3','Vikram Reddy','In Progress'],
              ['bi-circle','#cbd5e1','MEP rough-in','Pending assignment','Upcoming'],
              ['bi-circle','#cbd5e1','Roofing & waterproofing','Pending','Upcoming'],
            ] as [$ic,$col,$task,$asn,$status]): ?>
            <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--line-2);">
              <i class="bi <?= $ic ?>" style="color:<?= $col ?>;font-size:13px;flex-shrink:0;"></i>
              <div style="flex:1;min-width:0;">
                <div style="font-size:11.5px;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $task ?></div>
                <div style="font-size:10px;color:var(--ink-4);"><?= $asn ?></div>
              </div>
              <span style="font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;white-space:nowrap;
                background:<?= $status==='Done'?'#f0fdf4':($status==='In Progress'?'#fff7ed':'#f8fafc') ?>;
                color:<?= $status==='Done'?'#166534':($status==='In Progress'?'#92400e':'#64748b') ?>;"><?= $status ?></span>
            </div>
            <?php endforeach; ?>
            <!-- Budget mini bar -->
            <div style="margin-top:12px;">
              <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:5px;">
                <span style="color:var(--ink-3);">Budget spent</span>
                <span style="font-weight:700;color:var(--ink);">KES 122M <span style="color:var(--ink-4);font-weight:400;">/ KES 180M</span></span>
              </div>
              <div style="height:6px;border-radius:999px;background:var(--line);overflow:hidden;">
                <div style="width:68%;height:100%;background:linear-gradient(90deg,#22c55e,#86efac);border-radius:999px;"></div>
              </div>
            </div>
            <!-- Team avatars -->
            <div style="margin-top:12px;display:flex;align-items:center;gap:8px;">
              <div style="display:flex;">
                <?php foreach (['men/32','men/49','women/44','men/7','women/22'] as $i => $av): ?>
                <div style="width:26px;height:26px;border-radius:50%;overflow:hidden;border:2px solid #fff;margin-left:<?= $i>0?'-8px':'0' ?>;position:relative;z-index:<?= 10-$i ?>;">
                  <img src="https://randomuser.me/api/portraits/<?= $av ?>.jpg" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <?php endforeach; ?>
              </div>
              <span style="font-size:11px;color:var(--ink-3);">+3 more · <strong style="color:var(--ink);">8 team members</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: features -->
      <div class="col-lg-7">
        <div class="bf-crm-eyebrow"><i class="bi bi-kanban" style="color:#1e3a5f;"></i> Project CRM</div>
        <h2 class="bf-crm-headline">Manage your entire project<br>in one place</h2>
        <p class="bf-crm-sub">From the first site survey to final handover, bildfie's built-in CRM keeps your team, timeline, budget and documents in sync — no spreadsheets required.</p>

        <div class="bf-crm-feature">
          <div class="bf-crm-feature-icon"><i class="bi bi-kanban"></i></div>
          <div>
            <div class="bf-crm-feature-title">Tasks &amp; milestones</div>
            <div class="bf-crm-feature-desc">Assign work, set deadlines and track progress visually with Gantt and Kanban views.</div>
          </div>
        </div>
        <div class="bf-crm-feature">
          <div class="bf-crm-feature-icon"><i class="bi bi-people"></i></div>
          <div>
            <div class="bf-crm-feature-title">Team management</div>
            <div class="bf-crm-feature-desc">Invite contractors, assign roles and manage access levels across your entire project team.</div>
          </div>
        </div>
        <div class="bf-crm-feature">
          <div class="bf-crm-feature-icon"><i class="bi bi-file-earmark-text"></i></div>
          <div>
            <div class="bf-crm-feature-title">Document hub</div>
            <div class="bf-crm-feature-desc">Upload drawings, BOQs, contracts and reports in one place — accessible to all stakeholders.</div>
          </div>
        </div>
        <div class="bf-crm-feature">
          <div class="bf-crm-feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
          <div>
            <div class="bf-crm-feature-title">Budget tracking</div>
            <div class="bf-crm-feature-desc">Monitor spend vs. budget with real-time updates and automated milestone payment releases.</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
          <a href="/pages/auth/register.php" class="bf-btn-dark" style="padding:12px 28px;font-size:13.5px;">Create a project free</a>
          <a href="#" style="font-size:13px;font-weight:600;color:var(--ink-3);">Watch demo <i class="bi bi-play-circle ms-1"></i></a>
        </div>

        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--line);display:flex;flex-wrap:wrap;gap:20px;">
          <?php foreach ([['48,600+','Professionals'],['KES 2.4B','Bid Value'],['4.8★','Rating'],['180+','Countries']] as [$n,$l]): ?>
          <div>
            <div style="font-size:18px;font-weight:900;color:var(--ink);letter-spacing:-.03em;"><?= $n ?></div>
            <div style="font-size:10px;color:var(--ink-3);"><?= $l ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     08 — CONSTRUCTION FACILITIES
     (Site Offices, Warehouses, Yards)
═══════════════════════════════════════════ -->
<section class="bf-section" style="background:#EBFFF8;border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">09</span> Site Offices · Warehouses · Yards</div>
        <h2 class="bf-section-title mb-0">Construction Facilities</h2>
      </div>
      <a href="/pages/marketplace/facilities.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">Browse all →</a>
    </div>

    <!-- Inline ad -->
    <div class="bf-inline-ad-inner mb-4" style="padding-bottom:14px;border-bottom:1px solid var(--line-2);">
      <span class="bf-inline-ad-badge blue"><i class="bi bi-shield-check me-1"></i> NCA Registration</span>
      <span class="bf-inline-ad-text"><strong>Get NCA Verified — Boost your profile.</strong> Fast-track NCA registration online. Approval in 5 days.</span>
      <span class="bf-inline-ad-sponsor">Sponsored · NCA Kenya</span>
      <button class="bf-inline-ad-btn dark">Register</button>
    </div>

    <div class="row g-3">
      <?php foreach ([
        ['Westlands Site Office',  'Modular office block, 8 furnished offices, boardroom, kitchenette and ablutions. Fibre internet. 240m² total.','Nairobi · Westlands','KES 95,000/mo','https://picsum.photos/id/260/800/500','Site Office','available','3 units avail.'],
        ['Athi River Industrial Warehouse','2,400m² bonded warehouse with 6 loading bays, 24-hr security, CCTV and cold-room annex. Crane gantry included.','Machakos · Athi River','KES 320,000/mo','https://picsum.photos/id/329/800/500','Warehouse','available','1 unit avail.'],
        ['Ruiru Batching Plant Yard','Serviced 1.5-acre yard with installed batching plant, mixer bay, weigh bridge and admin office. Power and water on-site.','Kiambu · Ruiru','KES 240,000/mo','https://picsum.photos/id/366/800/500','Batching Yard','avl','2 units avail.'],
        ['Miritini Container Storage','Port-adjacent 3-acre bonded yard. Container stackers, hardstand parking and customs clearing agents on site.','Mombasa · Miritini','KES 180,000/mo','https://picsum.photos/id/119/800/500','Storage Yard','available','Available now'],
      ] as [$name,$desc,$loc,$price,$img,$type,$status,$units]): ?>
      <div class="col-md-6">
        <div class="bf-facility-large">
          <div class="bf-facility-large-img">
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($name) ?>">
          </div>
          <div class="bf-facility-large-body">
            <div>
              <div class="bf-facility-large-type">
                <i class="bi bi-building"></i><?= $type ?>
                <span class="bf-facility-large-status <?= $status==='available'||$status==='avl'?'avl':'bsy' ?>"><?= strtoupper($units) ?></span>
              </div>
              <div class="bf-facility-large-name"><?= $name ?></div>
              <div class="bf-facility-large-desc"><?= $desc ?></div>
            </div>
            <div class="bf-facility-large-meta">
              <div class="bf-facility-large-loc">
                <i class="bi bi-geo-alt" style="font-size:10px;color:#c0392b;"></i><?= $loc ?>
              </div>
              <div class="d-flex align-items-center gap-2">
                <div class="bf-facility-large-price"><?= $price ?></div>
                <button class="bf-action-dark" style="font-size:11px;padding:5px 14px;">Enquire</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     09 — INDUSTRY NEWS & SITE CONDITIONS
═══════════════════════════════════════════ -->
<section class="bf-section" style="border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">10</span> News, Weather & Resources</div>
        <h2 class="bf-section-title mb-0">Industry news &amp; site conditions</h2>
      </div>
      <a href="/pages/learn/index.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">All articles →</a>
    </div>
    <div class="row g-3">

      <!-- Featured article -->
      <div class="col-lg-5">
        <div class="bf-news-featured-v2 h-100">
          <div class="bf-news-img">
            <img src="https://picsum.photos/id/48/800/400" alt="News" style="filter:brightness(0.88) saturate(0.85);">
          </div>
          <div class="bf-news-body">
            <div class="bf-news-source">
              <div class="bf-news-source-dot" style="background:#16a34a;"></div>
              <div class="bf-news-source-label">Global Market</div>
              <div style="font-size:10px;color:var(--ink-4);">Reuters · Today</div>
            </div>
            <div class="bf-news-headline-v2">Global cement prices set to stabilise after Q3 supply chain disruptions</div>
            <p class="bf-news-excerpt-v2">Analysts forecast a return to pre-disruption pricing as supply normalises across Asia and West Africa producing regions, reducing pressure on construction budgets.</p>
            <div class="bf-news-meta-v2">
              <i class="bi bi-clock" style="font-size:11px;"></i> 3 min read
              <span style="color:var(--line);">·</span>
              <a href="/pages/learn/index.php" style="color:#1e3a5f;font-weight:600;font-size:11px;">Read more →</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Stacked articles -->
      <div class="col-lg-4 d-flex flex-column gap-3">
        <?php foreach ([
          ['bi-shield-check','#eff6ff','#1e40af','reg',    'Regulation','New NCA residential foundation standards — what builders must know','Yesterday · 4 min'],
          ['bi-cash-coin',   '#f0fdf4','#16a34a','finance','Finance',   'Construction financing rates drop to lowest in 18 months across SSA markets','2 days ago · 3 min'],
          ['bi-graph-up',    '#fff7ed','#c2410c','demand', 'Market',    'West Africa construction demand surges 34% — new PWC survey results breakdown','3 days ago · 6 min'],
          ['bi-building',    '#f5f3ff','#7c3aed','reg',    'Compliance','Kenya building code updates: new seismic zone requirements take effect Q2 2025','4 days ago · 5 min'],
        ] as [$icon,$ibg,$icolor,$cat,$catLabel,$headline,$time]): ?>
        <a href="/pages/learn/index.php" class="bf-news-item-v2" style="text-decoration:none;">
          <div class="bf-news-item-icon" style="background:<?= $ibg ?>;color:<?= $icolor ?>;"><i class="bi <?= $icon ?>"></i></div>
          <div>
            <div class="bf-news-item-category bf-news-cat <?= $cat ?>"><?= $catLabel ?></div>
            <div class="bf-news-item-headline"><?= $headline ?></div>
            <div class="bf-news-item-time"><i class="bi bi-clock" style="font-size:9px;"></i> <?= $time ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Weather + Quick Stats -->
      <div class="col-lg-3">
        <div class="bf-weather mb-3">
          <div style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px;">
            <i class="bi bi-geo-alt-fill me-1"></i>Your Site
          </div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-cloud-sun-fill" style="font-size:32px;color:#fbbf24;"></i>
            <div>
              <div class="bf-weather-temp">22°C</div>
              <div class="bf-weather-cond">Partly Cloudy · Good for site work</div>
            </div>
          </div>
          <div class="bf-weather-forecast">
            <?php foreach (['TUE'=>'26°','WED'=>'18°','THU'=>'21°','FRI'=>'24°'] as $d=>$t): ?>
            <div class="bf-weather-day"><?= $d ?><span><?= $t ?></span></div>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-4);margin-bottom:8px;">Platform Today</div>
        <div class="row g-2 mb-3">
          <?php foreach ([['48','New Projects'],['186','Registrations'],['$84M','Escrow Active'],['98%','Resolution']] as [$n,$l]): ?>
          <div class="col-6"><div class="bf-qstat"><div class="bf-qstat-num"><?= $n ?></div><div class="bf-qstat-label"><?= $l ?></div></div></div>
          <?php endforeach; ?>
        </div>

        <div style="border:1px solid var(--line);border-radius:var(--radius);padding:14px 16px;background:var(--white);">
          <div style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;">💱 FX Converter</div>
          <div class="d-flex align-items-center gap-2 mt-1">
            <select style="flex:1;border:1px solid var(--line);border-radius:6px;padding:7px 10px;font-family:inherit;font-size:12px;outline:none;"><option>USD $</option><option>EUR €</option><option>GBP £</option><option>KES</option></select>
            <i class="bi bi-arrow-left-right" style="color:var(--ink-4);flex-shrink:0;font-size:12px;"></i>
            <select style="flex:1;border:1px solid var(--line);border-radius:6px;padding:7px 10px;font-family:inherit;font-size:12px;outline:none;"><option>KES</option><option>USD $</option><option>NGN</option><option>GHS</option></select>
          </div>
          <div style="font-size:11.5px;color:var(--ink-3);margin-top:9px;padding-top:9px;border-top:1px solid var(--line-2);">1 USD = <strong style="color:var(--ink);">129.4 KES</strong> <span style="color:#16a34a;font-size:10px;font-weight:700;">Live</span></div>
        </div>
      </div>
    </div>

    <!-- Did you know row -->
    <div class="row mt-3 g-3">
      <div class="col-md-6">
        <div class="bf-didyouknow">
          <div class="bf-dyk-label">💡 Did you know?</div>
          <div class="bf-dyk-text">Verified professionals on bildfie complete projects <strong>34% faster</strong> on average, thanks to milestone accountability and escrow payment incentives.</div>
        </div>
      </div>
      <div class="col-md-6">
        <div style="border:1px solid var(--line);border-radius:var(--radius);padding:16px 18px;background:var(--white);display:flex;align-items:center;gap:16px;height:100%;">
          <i class="bi bi-megaphone" style="font-size:28px;color:#1e3a5f;flex-shrink:0;"></i>
          <div>
            <div style="font-size:12px;font-weight:700;color:var(--ink);margin-bottom:3px;">Contractor All Risk Insurance</div>
            <div style="font-size:11.5px;color:var(--ink-3);">Protect your project from day one. Get a quote in 10 minutes. <a href="#" style="color:#c0392b;font-weight:600;">Get Cover →</a></div>
          </div>
          <span style="font-size:9px;color:var(--ink-4);white-space:nowrap;flex-shrink:0;">Sponsored · CIC</span>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     10 — PROJECT SHOWCASE
═══════════════════════════════════════════ -->
<section class="bf-section" style="background:var(--surface);border-top:1px solid var(--line);">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <div class="bf-section-eyebrow mb-1"><span class="bf-section-eyebrow-num">11</span> Project Showcase</div>
        <h2 class="bf-section-title mb-1">Recently completed on bildfie</h2>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Real projects, real teams, real results — across 180+ countries.</p>
      </div>
      <a href="/pages/projects/index.php" style="font-size:13px;font-weight:700;color:#c0392b;white-space:nowrap;">Browse all →</a>
    </div>
    <div class="row g-3">
      <?php
      $showcases = [
        ['https://picsum.photos/id/164/800/500', 'Residential',    '14-Unit Apartment Complex, Kilimani',   'Nairobi, Kenya',  'KES 420,000','16 mo','James Kamau',      'men/32','4.9','General Contractor'],
        ['https://picsum.photos/id/1029/800/500','Commercial',     '3-Storey Office Block, Victoria Island', 'Lagos, Nigeria',  'KES 290,000','14 mo','Olumide Lawal',     'men/49','4.8','Civil Engineer'],
        ['https://picsum.photos/id/239/800/500', 'Infrastructure', '8km Community Access Road, Tema',        'Accra, Ghana',    'KES 185,000','10 mo','Kofi Tetteh',       'men/85','4.8','Structural Engineer'],
        ['https://picsum.photos/id/225/800/500', 'Luxury',         '6-Bedroom Villa with Pool, Palm Jumeiral','Dubai, UAE',     'KES 1.2M',  '24 mo','Hideaki Tanaka',    'men/62','5.0','Architect'],
      ];
      foreach ($showcases as [$img,$badge,$title,$loc,$budget,$dur,$contractor,$portrait,$rat,$role]): ?>
      <div class="col-md-6 col-xl-3">
        <div class="bf-showcase-v2">
          <!-- Thumbnail with badge overlay -->
          <div class="bf-showcase-v2-thumb">
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($title) ?>">
            <div class="bf-showcase-v2-thumb-overlay"></div>
            <div class="bf-showcase-v2-badge"><?= $badge ?></div>
          </div>
          <!-- Card body -->
          <div class="bf-showcase-v2-content">
            <div class="bf-showcase-v2-title"><?= $title ?></div>
            <div class="bf-showcase-v2-loc">
              <i class="bi bi-geo-alt-fill" style="font-size:9px;"></i><?= $loc ?>
            </div>
            <div class="bf-showcase-v2-stats">
              <div class="bf-showcase-v2-stat">
                <label>Budget</label>
                <span><?= $budget ?></span>
              </div>
              <div class="bf-showcase-v2-stat">
                <label>Duration</label>
                <span><?= $dur ?></span>
              </div>
            </div>
            <div class="bf-showcase-v2-footer">
              <div class="bf-showcase-v2-contractor">
                <div class="bf-showcase-v2-avatar">
                  <img src="https://randomuser.me/api/portraits/<?= $portrait ?>.jpg" alt="<?= htmlspecialchars($contractor) ?>">
                </div>
                <div>
                  <div class="bf-showcase-v2-cname"><?= $contractor ?></div>
                  <div class="bf-showcase-v2-role"><?= $role ?></div>
                </div>
              </div>
              <div class="bf-showcase-v2-rating">
                <i class="bi bi-star-fill"></i> <?= $rat ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<?php
/* Shuffle the captured content sections (incl. sponsored ad bands) into a random order each load */
$bfFeed = ob_get_clean();
if (preg_match_all('/<section\b.*?<\/section>/is', $bfFeed, $bfM) && count($bfM[0]) > 1) {
    $bfSecs = $bfM[0];
    shuffle($bfSecs);
    $bfI = 0;
    $bfFeed = preg_replace_callback('/<section\b.*?<\/section>/is', function () use ($bfSecs, &$bfI) { return $bfSecs[$bfI++]; }, $bfFeed);
}
echo $bfFeed;
?>
<?php include __DIR__ . '/components/modals/invite-modal.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/scripts.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ─── Map ─── */
(function() {
  var map = L.map('bildfie-map', { center:[-1.28, 36.82], zoom:11, zoomControl:true, scrollWheelZoom:false });
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    {attribution:'© OpenStreetMap © CARTO', maxZoom:19}).addTo(map);

  var colors = {commercial:'#1e3a5f',residential:'#f59e0b',infrastructure:'#22c55e',industrial:'#a855f7',government:'#ef4444'};
  var projects = [
    {lat:-1.286,lng:36.817,type:'commercial',    name:'Westlands Office Tower',      contractor:'David Mbugua',    role:'Site Manager',        budget:'KES 45M', dur:'12 mo', img:'https://picsum.photos/id/164/260/140', bids:7},
    {lat:-1.264,lng:36.803,type:'residential',   name:'Parklands Apartments',         contractor:'James Kamau',    role:'General Contractor',   budget:'KES 92M', dur:'18 mo', img:'https://picsum.photos/id/1029/260/140',bids:12},
    {lat:-1.300,lng:36.780,type:'industrial',    name:'Kilimani Mixed-Use Complex',   contractor:'Amina Mwangi',   role:'Architect',            budget:'KES 38M', dur:'10 mo', img:'https://picsum.photos/id/239/260/140', bids:4},
    {lat:-1.316,lng:36.820,type:'government',    name:'Upper Hill Hospital Wing',     contractor:'County Health Dept', role:'Client',           budget:'KES 75M', dur:'24 mo', img:'https://picsum.photos/id/225/260/140', bids:9},
    {lat:-1.270,lng:36.860,type:'infrastructure',name:'Eastlands Road Upgrade',       contractor:'Road Works Kenya Ltd',role:'Contractor',       budget:'KES 18M', dur:'6 mo',  img:'https://picsum.photos/id/260/260/140', bids:3},
    {lat:-1.234,lng:36.797,type:'residential',   name:'Ruaraka Housing Estate',       contractor:'Samuel Otieno', role:'Contractor',           budget:'KES 60M', dur:'20 mo', img:'https://picsum.photos/id/329/260/140', bids:6},
    {lat:-1.332,lng:36.710,type:'residential',   name:'Karen Luxury Villas',          contractor:'Hideaki Tanaka', role:'Architect',           budget:'KES 180M',dur:'24 mo', img:'https://picsum.photos/id/366/260/140', bids:5},
    {lat:-1.310,lng:36.850,type:'commercial',    name:'South B Commercial Plaza',     contractor:'Vikram Reddy',  role:'Civil Contractor',     budget:'KES 55M', dur:'14 mo', img:'https://picsum.photos/id/250/260/140', bids:8},
    {lat:-1.252,lng:36.831,type:'government',    name:'Kasarani Sports Complex',      contractor:'Sports Kenya Authority',role:'Client',         budget:'KES 220M',dur:'36 mo', img:'https://picsum.photos/id/292/260/140', bids:11},
    {lat:-1.283,lng:36.755,type:'infrastructure',name:"Lang'ata Road Rehabilitation", contractor:'KENHA Roads Authority',role:'Client',          budget:'KES 35M', dur:'8 mo',  img:'https://picsum.photos/id/164/260/140', bids:2},
  ];

  projects.forEach(function(p) {
    var typeLabel = p.type.charAt(0).toUpperCase()+p.type.slice(1);
    var tooltip = [
      '<div style="font-size:0;border-radius:10px;overflow:hidden;min-width:220px;">',
        '<div style="position:relative;height:110px;overflow:hidden;">',
          '<img src="'+p.img+'" style="width:100%;height:100%;object-fit:cover;filter:brightness(0.7) saturate(0.7);">',
          '<div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.7) 0%,transparent 55%);"></div>',
          '<div style="position:absolute;bottom:8px;left:10px;right:10px;">',
            '<div style="font-size:13px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:4px;">'+p.name+'</div>',
            '<span style="font-size:8.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;background:'+colors[p.type]+';color:#fff;padding:1px 7px;border-radius:3px;">'+typeLabel+'</span>',
          '</div>',
        '</div>',
        '<div style="padding:10px 12px;background:#fff;font-size:12px;">',
          '<div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">',
            '<div style="width:24px;height:24px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#374151;">'+p.contractor.charAt(0)+'</div>',
            '<div style="line-height:1.2;">',
              '<div style="font-size:11px;font-weight:700;color:#111;">'+p.contractor+'</div>',
              '<div style="font-size:10px;color:#6b7280;">'+p.role+'</div>',
            '</div>',
          '</div>',
          '<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;">',
            '<div style="font-size:10px;color:#6b7280;">Budget</div><div style="font-size:10px;font-weight:700;color:#111;text-align:right;">'+p.budget+'</div>',
            '<div style="font-size:10px;color:#6b7280;">Duration</div><div style="font-size:10px;font-weight:700;color:#111;text-align:right;">'+p.dur+'</div>',
            '<div style="font-size:10px;color:#6b7280;">Bids</div><div style="font-size:10px;font-weight:700;color:#c0392b;text-align:right;">'+p.bids+' submitted</div>',
          '</div>',
        '</div>',
      '</div>'
    ].join('');

    var marker = L.circleMarker([p.lat,p.lng],{
      radius:9, fillColor:colors[p.type], color:'#fff', weight:2.5, opacity:1, fillOpacity:.88
    }).addTo(map);

    marker.bindTooltip(tooltip, {
      permanent:false, direction:'top', sticky:false,
      className:'bf-map-tooltip', offset:[0,-8]
    });
  });

  // You marker
  L.circleMarker([-1.293,36.820],{
    radius:8, fillColor:'#ef4444', color:'#fff', weight:2.5, opacity:1, fillOpacity:.95
  }).bindTooltip('<div style="font-size:12px;font-weight:700;padding:4px 6px;">📍 Your Location</div>',
    {permanent:false, direction:'top', className:'bf-map-tooltip'}).addTo(map);
})();

/* ─── Live stats ticker ─── */
(function() {
  var profs = 48621, today = 48, reg = 186, online = 1247;
  function rand(n,d){ return Math.floor(Math.random()*d*2-d)+n; }
  setInterval(function(){
    profs += Math.random()>.7?1:0;
    today += Math.random()>.85?1:0;
    reg   += Math.random()>.8?1:0;
    online = Math.max(1000, online + Math.floor(Math.random()*6-3));
    var pe=document.getElementById('statProfs'); if(pe) pe.childNodes[0].textContent=profs.toLocaleString();
    var te=document.getElementById('statToday'); if(te) te.textContent=today;
    var re=document.getElementById('statReg');   if(re) re.textContent=reg;
    var oe=document.getElementById('onlineCount'); if(oe) oe.textContent=online.toLocaleString();
  }, 5000);
})();

/* ─── News ticker ─── */
(function() {
  var ticker = document.getElementById('newsTicker');
  var label  = document.getElementById('newsLabel');
  if (!ticker) return;
  var items = ticker.querySelectorAll('div');
  var labels = [
    {text:'BREAKING',bg:'#fef2f2',color:'#c0392b'},
    {text:'NEWS',    bg:'#f1f5f9',color:'#334155'},
    {text:'NCA',     bg:'#f5f3ff',color:'#7c3aed'},
    {text:'MARKET',  bg:'#fff7ed',color:'#c2410c'},
  ];
  var textColors = ['#c0392b','#334155','#7c3aed','#c2410c'];
  function setLabel(i) { if (!label) return; label.textContent=labels[i].text; label.style.background=labels[i].bg; label.style.color=labels[i].color; }
  items.forEach(function(it,i) { if (i>0) it.style.display='none'; it.style.color=textColors[i]; });
  setLabel(0);
  var cur=0;
  setInterval(function() {
    items[cur].style.opacity='0'; items[cur].style.transform='translateY(-8px)';
    var next=(cur+1)%items.length;
    items.forEach(function(it,i) { it.style.display=i===next?'block':'none'; it.style.opacity=i===next?'0':''; it.style.transform=i===next?'translateY(8px)':''; });
    setTimeout(function() { items[next].style.transition='opacity .4s,transform .4s'; items[next].style.opacity='1'; items[next].style.transform='translateY(0)'; setLabel(next); },50);
    cur=next;
  },4000);
})();
</script>
