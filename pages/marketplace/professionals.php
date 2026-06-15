<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/companies.php';
$page_title = 'Find Professionals';
$nav = 'professionals';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="bf-mkt-hero">
  <div class="container">

    <!-- Utility bar -->
    <div class="bf-mkt-utility">
      <span class="bf-mkt-utility-item"><i class="bi bi-patch-check-fill"></i> Verified construction professionals across Kenya</span>
    </div>

    <div style="padding:28px 0 26px;">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
          <div class="bf-section-eyebrow mb-2"><i class="bi bi-people" style="color:#c0392b;"></i> Professionals</div>
          <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">Verified construction professionals</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Architects, engineers, contractors and specialists — licence verified, rated and ready to work.</p>
        </div>
        <a href="/pages/bids/index.php" class="bf-btn-accent">Post a project</a>
      </div>

      <!-- Trust badges -->
      <div class="bf-mkt-badges">
        <span class="bf-mkt-badge"><i class="bi bi-patch-check-fill"></i> ID &amp; licence verified</span>
        <span class="bf-mkt-badge"><i class="bi bi-clock-fill"></i> Available-now filtering</span>
        <span class="bf-mkt-badge"><i class="bi bi-shield-lock-fill"></i> Escrow-protected payments</span>
      </div>

      <!-- Advanced search -->
      <div class="bf-adv-search">
        <div class="bf-adv-search-main">
          <div class="bf-adv-search-label"><i class="bi bi-search"></i> Search Professionals</div>
          <input type="text" placeholder="Name, trade, skill or company…">
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Trade</div>
          <select><option>All Trades</option><option>Architecture</option><option>Civil Engineering</option><option>Structural Engineering</option><option>MEP Engineering</option><option>Quantity Surveying</option><option>Project Management</option><option>Interior Design</option><option>Landscape Architecture</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label"><i class="bi bi-geo-alt"></i> Location</div>
          <select><option>Any Location</option><option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Kampala</option><option>Dar es Salaam</option><option>Kigali</option></select>
        </div>
        <div class="bf-adv-search-field">
          <div class="bf-adv-search-label">Rate (KES)</div>
          <select><option>Any Rate</option><option>Under KES 2,000</option><option>KES 2,000–5,000</option><option>KES 5,000–10,000</option><option>KES 10,000+</option></select>
        </div>
        <button class="bf-adv-search-btn"><i class="bi bi-search"></i> Search</button>
      </div>

      <!-- Popular -->
      <div class="bf-mkt-popular">
        <span class="bf-mkt-popular-label">Popular:</span>
        <?php foreach (['Architects','Civil Engineers','Structural','Quantity Surveyors','MEP Engineers','Project Managers','Site Foreman'] as $p): ?>
        <button class="bf-search-tag"><?= $p ?></button>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Category nav -->
    <div class="bf-mkt-catnav">
      <?php foreach (['All Trades','Architecture','Civil Engineering','Structural','MEP Engineering','Quantity Surveying','Project Management','Interior Design','Landscape','Site Foreman'] as $i=>$c): ?>
      <div class="bf-mkt-catnav-item <?= $i===0?'active':'' ?>">
        <div class="bf-mkt-catnav-name"><?= $c ?></div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<div class="container" style="padding-top:32px;padding-bottom:60px;">

  <!-- Results bar -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid var(--line);">
    <div style="font-size:12.5px;color:var(--ink-3);">Browse professionals</div>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>All countries</option><option>Kenya</option><option>Nigeria</option><option>Ghana</option><option>UAE</option>
      </select>
      <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
        <option>Sort: Top Rated</option><option>Most Reviews</option><option>Rate: Low→High</option><option>Recently Active</option>
      </select>
    </div>
  </div>

  <?php
  // ── data ──
  $skillsByPro = [];
  foreach (db_all("SELECT provider_id, skill FROM provider_skills ORDER BY sort_order") as $r) { $skillsByPro[(int)$r['provider_id']][] = $r['skill']; }
  $featured = db_all("SELECT * FROM providers WHERE status='active' AND is_featured=1 ORDER BY sort_order, name");

  // ── One shuffled feed for the main grid: professionals + companies + sponsored ads, mixed randomly on each load ──
  $feed = [];
  foreach (db_all("SELECT * FROM providers WHERE status='active' AND is_featured=0 ORDER BY sort_order, name") as $pp) $feed[] = ['type'=>'pro','data'=>$pp];
  foreach (company_all() as $cc) $feed[] = ['type'=>'co','data'=>$cc];
  shuffle($feed);
  // sprinkle sponsored ads into random slots (different spots each refresh)
  foreach (['buildmart','escrow'] as $adk) { if (count($feed) < 3) break; array_splice($feed, random_int(1, count($feed)-1), 0, [['type'=>'ad','data'=>$adk]]); }

  // shared resolvers
  function provider_tier(array $p): array {
    $new = ((int)$p['reviews_count']===0 && (int)$p['jobs_completed']===0);
    if (!empty($p['is_elite']))     return ['Elite Pro','#1e3a5f','#ffffff','#1e3a5f','bi-gem'];
    if (!empty($p['is_preferred'])) return ['bildfie Choice','#eef2ff','#4338ca','#4338ca','bi-hand-thumbs-up-fill'];
    if (!empty($p['is_top_rated'])) return ['Top Rated','#fdf6e3','#9a7d27','#c9a227','bi-award-fill'];
    if (!empty($p['is_featured']))  return ['Premium','#fff7ed','#c2410c','#f59e0b','bi-star-fill'];
    if (!empty($p['is_verified']))  return ['Verified Pro','#f0fdf4','#166534','#16a34a','bi-patch-check-fill'];
    if ($new) return ['New','#eff6ff','#1e40af','#2563eb','bi-stars'];
    return ['Member','#f4f4f2','#6b6b6b','#cbd5e1','bi-person-fill'];
  }
  // Detail chips on the card = the active trust badges (tier + availability are shown separately).
  function provider_approvals(array $p): array {
    $out = [];
    foreach (provider_badges($p, ['is_available','is_elite','is_preferred','is_top_rated','is_featured']) as [$lbl,$ic]) $out[] = [$lbl,$ic];
    return $out;
  }
  ?>

  <!-- ═══ Featured professionals + sponsored ad (hidden when there are no featured providers) ═══ -->
  <?php if ($featured): ?>
  <div class="row g-3" style="margin-top:16px;margin-bottom:28px;">
    <?php foreach ($featured as $p):
      $pid=(int)$p['id']; $appr=provider_approvals($p);
      $apprShow=array_slice($appr,0,5); $apprMore=count($appr)-count($apprShow);
      $av=(int)$p['is_available']; $jobs=(int)$p['jobs_completed']; $reviews=(int)$p['reviews_count'];
    ?>
    <div class="col-xl-6 col-lg-6 col-12">
      <div class="bf-fpc">
        <div class="bf-fpc-ribbon"><span><i class="bi bi-gem"></i> Featured Elite</span><span style="opacity:.9;"><i class="bi bi-trophy-fill"></i> Top-ranked &middot; <?= htmlspecialchars(explode(',', $p['location'])[0] ?: 'Kenya') ?></span></div>
        <div class="bf-fpc-body">
          <div class="bf-fpc-photo">
            <img src="<?= $p['photo_url'] ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <span style="position:absolute;bottom:8px;left:8px;display:inline-flex;align-items:center;gap:4px;font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;background:rgba(13,31,54,.82);color:#fff;padding:3px 8px;border-radius:20px;"><i class="bi bi-circle-fill" style="font-size:6px;color:<?= $av?'#22c55e':'#f59e0b' ?>;"></i><?= $av?'Available':'Busy' ?></span>
          </div>
          <div class="bf-fpc-info">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
              <div style="min-width:0;">
                <div style="font-size:16px;font-weight:800;color:var(--ink);line-height:1.15;"><?= htmlspecialchars($p['name']) ?></div>
                <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['headline']) ?></div>
              </div>
              <span style="flex-shrink:0;display:inline-flex;align-items:center;gap:3px;font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:#1e3a5f;color:#fff;padding:4px 8px;border-radius:5px;"><i class="bi bi-gem" style="font-size:8px;"></i>Elite</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;margin-top:9px;flex-wrap:wrap;">
              <span style="display:inline-flex;align-items:baseline;gap:4px;"><span style="font-size:16px;font-weight:900;color:var(--ink);"><?= $p['rating'] ?></span><span style="color:#f59e0b;font-size:9px;">★★★★★</span><span style="font-size:10px;color:var(--ink-4);">(<?= number_format($reviews) ?>)</span></span>
              <span style="font-size:10.5px;color:var(--ink-4);"><i class="bi bi-briefcase-fill" style="font-size:9px;"></i> <?= number_format($jobs) ?> jobs</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:11px;">
              <?php foreach ($apprShow as [$al,$ai]): ?><span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:600;background:#f6faf7;color:#166534;border:1px solid #e3efe7;padding:2px 7px;border-radius:5px;"><i class="bi <?= $ai ?>" style="font-size:8px;"></i><?= $al ?></span><?php endforeach; ?>
              <?php if ($apprMore>0): ?><span style="display:inline-flex;align-items:center;font-size:9px;font-weight:700;color:#1e3a5f;background:#eaf0f6;padding:2px 7px;border-radius:5px;">+<?= $apprMore ?> more</span><?php endif; ?>
            </div>
            <div style="margin-top:auto;display:flex;align-items:flex-end;justify-content:space-between;gap:8px;padding-top:13px;">
              <div><div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);">From</div><div style="font-size:14px;font-weight:900;color:#c0392b;letter-spacing:-.01em;"><?= htmlspecialchars($p['day_rate'] ?: 'On request') ?></div></div>
              <div style="display:flex;gap:6px;align-items:center;">
                <button class="bf-action-dark" data-engage="hire" data-ctx="<?= htmlspecialchars($p['name'],ENT_QUOTES) ?>" data-pid="<?= $pid ?>" style="height:32px;padding:0 12px;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;background:#c0392b;border:none;border-radius:8px;cursor:pointer;color:#fff;"><i class="bi bi-send-fill"></i>Invite</button>
                <a href="/pages/marketplace/professional.php?id=<?= $pid ?>" class="bf-action-outline" style="height:32px;padding:0 12px;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;text-decoration:none;"><i class="bi bi-eye"></i>View</a>
                <button class="bf-fav" data-fav="pro:<?= urlencode($p['name']) ?>" title="Save" style="width:32px;height:32px;flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:0;"><i class="bi bi-bookmark"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Sponsored display ad -->
    <div class="col-xl-6 col-lg-6 col-12">
      <a href="#" style="display:block;height:100%;min-height:228px;border-radius:14px;overflow:hidden;position:relative;text-decoration:none;background:linear-gradient(125deg,#0d1f36,#1e3a5f 58%,#2a4d78);">
        <span style="position:absolute;top:10px;right:10px;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;background:rgba(255,255,255,.18);color:#fff;padding:3px 8px;border-radius:4px;">Ad &middot; Sponsored</span>
        <div style="padding:24px;color:#fff;display:flex;flex-direction:column;height:100%;justify-content:center;">
          <div style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#ffd76a;margin-bottom:9px;"><i class="bi bi-bag-check-fill"></i> Buildmart Pro</div>
          <div style="font-size:22px;font-weight:900;line-height:1.18;letter-spacing:-.02em;max-width:340px;">Premium building materials, delivered to your site.</div>
          <div style="font-size:12.5px;opacity:.82;margin-top:9px;max-width:350px;line-height:1.55;">Cement, steel, finishes &amp; tools at trade prices for bildfie pros. Free delivery over KES 50,000.</div>
          <span style="margin-top:18px;display:inline-flex;align-items:center;gap:7px;align-self:flex-start;background:#c0392b;color:#fff;font-size:12px;font-weight:700;padding:9px 18px;border-radius:9px;">Shop now <i class="bi bi-arrow-right"></i></span>
        </div>
      </a>
    </div>
  </div>

  <?php endif; /* /featured row */ ?>

  <!-- ═══ Mixed, shuffled feed — professionals + companies + ads, all together ═══ -->
  <div class="row g-3">
    <?php foreach ($feed as $item):
      if ($item['type']==='pro'): $p=$item['data'];
      $pid=(int)$p['id']; $name=$p['name']; $role=$p['headline']; $loc=$p['location'];
      $rating=$p['rating']; $reviews=(int)$p['reviews_count']; $rate=$p['day_rate'];
      $available=(int)$p['is_available']; $verified=(int)$p['is_verified']; $photo=$p['photo_url'];
      $isNew = ($reviews===0 && (int)$p['jobs_completed']===0);
      [$tierL,$tierBg,$tierC,$tierAc,$tierI] = provider_tier($p);
      $appr = provider_approvals($p); $apprShow = array_slice($appr,0,8); $apprMore = count($appr)-count($apprShow);
    ?>
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="bf-pro-card h-100" style="background:var(--white);border:1px solid var(--line);border-top:3px solid <?= $tierAc ?>;border-radius:14px;padding:16px;display:flex;flex-direction:column;">

        <!-- header -->
        <div style="display:flex;align-items:flex-start;gap:11px;margin-bottom:12px;">
          <div style="position:relative;flex-shrink:0;">
            <img src="<?= $photo ?>" alt="<?= htmlspecialchars($name) ?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid var(--line);">
            <span style="position:absolute;bottom:0;right:0;width:12px;height:12px;border-radius:50%;background:<?= $available?'#22c55e':'#f59e0b' ?>;border:2px solid var(--white);"></span>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:800;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($name) ?></div>
            <div style="font-size:10.5px;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($role) ?></div>
            <div style="font-size:10px;color:var(--ink-4);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i class="bi bi-geo-alt-fill" style="font-size:9px;color:#c0392b;"></i> <?= htmlspecialchars($loc) ?></div>
          </div>
          <span style="flex-shrink:0;font-size:8px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;background:<?= $tierBg ?>;color:<?= $tierC ?>;padding:4px 8px;border-radius:5px;display:inline-flex;align-items:center;gap:3px;line-height:1;white-space:nowrap;"><i class="bi <?= $tierI ?>" style="font-size:8px;"></i><?= $tierL ?></span>
        </div>

        <!-- ratings strip -->
        <div style="display:flex;align-items:center;gap:10px;background:var(--surface);border-radius:10px;padding:9px 12px;margin-bottom:10px;">
          <?php if ($isNew): ?>
            <div style="display:flex;align-items:center;gap:6px;color:#1e40af;font-weight:700;font-size:12px;"><i class="bi bi-stars"></i> New provider</div>
            <span style="margin-left:auto;font-size:10px;color:var(--ink-4);">No reviews yet</span>
          <?php else: ?>
            <div style="display:flex;align-items:baseline;gap:5px;">
              <span style="font-size:19px;font-weight:900;color:var(--ink);letter-spacing:-.02em;"><?= $rating ?></span>
              <div style="line-height:1;">
                <div style="color:#f59e0b;font-size:9px;letter-spacing:.5px;">★★★★★</div>
                <div style="font-size:9.5px;color:var(--ink-4);margin-top:2px;"><?= number_format($reviews) ?> reviews</div>
              </div>
            </div>
            <div style="margin-left:auto;text-align:right;">
              <div style="font-size:14px;font-weight:800;color:#1e3a5f;line-height:1;"><?= number_format((int)$p['jobs_completed']) ?></div>
              <div style="font-size:8.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.04em;">Jobs done</div>
            </div>
          <?php endif; ?>
        </div>

        <!-- approvals (minimalized — holds 8-12) -->
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px;flex:1;align-content:flex-start;">
          <?php foreach ($apprShow as [$al,$ai]): ?>
          <span style="display:inline-flex;align-items:center;gap:4px;font-size:9px;font-weight:600;background:#f6faf7;color:#166534;border:1px solid #e3efe7;padding:3px 7px;border-radius:5px;"><i class="bi <?= $ai ?>" style="font-size:9px;"></i><?= $al ?></span>
          <?php endforeach; ?>
          <?php if ($apprMore>0): ?><span style="display:inline-flex;align-items:center;font-size:9px;font-weight:700;color:#1e3a5f;background:#eaf0f6;padding:3px 7px;border-radius:5px;">+<?= $apprMore ?></span><?php endif; ?>
        </div>

        <!-- rate -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:11px;padding:8px 11px;background:var(--surface);border-radius:8px;">
          <div><div style="font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);">Day rate</div><div style="font-size:12.5px;font-weight:800;color:#c0392b;"><?= htmlspecialchars($rate ?: 'On request') ?></div></div>
          <div style="text-align:right;"><div style="font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);">Status</div><div style="font-size:11px;font-weight:700;color:<?= $available?'#22c55e':'#f59e0b' ?>;"><?= $available?'Available':'Busy' ?></div></div>
        </div>

        <!-- actions -->
        <div style="display:flex;gap:7px;align-items:stretch;">
          <a href="/pages/marketplace/professional.php?id=<?= $pid ?>" class="bf-action-outline" style="flex:1;height:36px;display:flex;align-items:center;justify-content:center;gap:5px;font-size:11.5px;font-weight:700;text-decoration:none;"><i class="bi bi-eye"></i> View</a>
          <button class="bf-action-dark" data-engage="hire" data-ctx="<?= htmlspecialchars($name, ENT_QUOTES) ?>" data-pid="<?= $pid ?>" style="flex:1;height:36px;display:flex;align-items:center;justify-content:center;gap:5px;font-size:11.5px;font-weight:700;background:#1e3a5f;border:none;cursor:pointer;color:#fff;"><i class="bi bi-send"></i> Invite</button>
          <button class="bf-fav" data-fav="pro:<?= urlencode($name) ?>" title="Save to shortlist" style="width:36px;height:36px;flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:0;"><i class="bi bi-bookmark"></i></button>
        </div>
      </div>
    </div>
    <?php elseif ($item['type']==='co'):
      $c=$item['data']; $cid=(int)$c['id']; $cspecs=array_slice(company_specialties($cid),0,3);
    ?>
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="bf-co-card" data-href="/pages/companies/view.php?slug=<?= urlencode($c['slug']) ?>">
          <div class="bf-co-cover" style="<?= !empty($c['cover_url']) ? "background:url('".htmlspecialchars($c['cover_url'], ENT_QUOTES)."') center/cover" : 'background:linear-gradient(120deg,#1e3a5f,#2a4d78)' ?>;">
            <span class="bf-co-type"><i class="bi bi-buildings-fill"></i> Organization</span>
          </div>
          <div class="bf-co-body">
            <img class="bf-co-logo" src="<?= htmlspecialchars(company_logo($c), ENT_QUOTES) ?>" alt="<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>">
            <div class="bf-co-name"><?= htmlspecialchars($c['name']) ?><?php if ((int)$c['is_verified']): ?><i class="bi bi-patch-check-fill" title="Verified organization"></i><?php endif; ?></div>
            <div class="bf-co-tagline"><?= htmlspecialchars($c['tagline'] ?? $c['industry'] ?? '') ?></div>
            <div class="bf-co-meta">
              <span><i class="bi bi-briefcase"></i> <?= htmlspecialchars($c['industry'] ?? '—') ?></span>
              <span><i class="bi bi-people"></i> <?= htmlspecialchars($c['company_size'] ?? '—') ?></span>
            </div>
            <?php if ($cspecs): ?>
            <div class="bf-co-chips">
              <?php foreach ($cspecs as $s): ?><span><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="bf-co-foot">
              <span class="bf-co-emp"><i class="bi bi-person-badge"></i> <?= (int)$c['emp_count'] ?> on bildfie</span>
            </div>
            <div class="bf-co-actions">
              <a href="/pages/companies/view.php?slug=<?= urlencode($c['slug']) ?>" class="bf-action-outline"><i class="bi bi-eye"></i> View</a>
              <button class="bf-follow sm" type="button" data-follow="co:<?= htmlspecialchars($c['slug'], ENT_QUOTES) ?>">
                <span class="bf-follow-off"><i class="bi bi-bookmark"></i>Save</span>
                <span class="bf-follow-on"><i class="bi bi-bookmark-check-fill"></i>Saved</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php else: $ad=$item['data']; ?>
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <?php if ($ad==='buildmart'): ?>
      <a href="#" class="bf-ad-grid" style="background:linear-gradient(140deg,#0d1f36,#1e3a5f 60%,#2a4d78);">
        <span class="ad-tag">Ad · Sponsored</span>
        <div style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#ffd76a;margin-bottom:8px;"><i class="bi bi-bag-check-fill"></i> Buildmart Pro</div>
        <div style="font-size:17px;font-weight:900;line-height:1.2;color:#fff;">Premium building materials, delivered to site.</div>
        <div style="font-size:11.5px;color:rgba(255,255,255,.8);margin-top:8px;line-height:1.5;">Cement, steel &amp; finishes at trade prices. Free delivery over KES 50,000.</div>
        <span style="margin-top:14px;display:inline-flex;align-items:center;gap:6px;align-self:flex-start;background:#c0392b;color:#fff;font-size:11.5px;font-weight:700;padding:8px 16px;border-radius:8px;">Shop now <i class="bi bi-arrow-right"></i></span>
      </a>
      <?php else: ?>
      <a href="#" class="bf-ad-grid" style="background:linear-gradient(140deg,#14321f,#166534 68%,#1e7a45);">
        <span class="ad-tag">Ad</span>
        <i class="bi bi-shield-fill-check" style="font-size:28px;color:#ffd76a;"></i>
        <div style="font-size:17px;font-weight:900;line-height:1.2;color:#fff;margin-top:10px;">Protect every hire with bildfie Escrow</div>
        <div style="font-size:11.5px;color:rgba(255,255,255,.82);margin-top:8px;line-height:1.5;">Funds release only when milestones are met — zero fees on your first contract.</div>
        <span style="margin-top:14px;display:inline-flex;align-items:center;gap:6px;align-self:flex-start;background:#c0392b;color:#fff;font-size:11.5px;font-weight:700;padding:8px 16px;border-radius:8px;">Enable escrow <i class="bi bi-arrow-right"></i></span>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; endforeach; ?>
  </div>

  <!-- Load more -->
  <div style="text-align:center;margin-top:36px;">
    <button class="bf-btn-outline" style="padding:12px 36px;font-size:13px;">Load more professionals</button>
  </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
