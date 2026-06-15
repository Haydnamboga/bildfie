<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Open Bids';
$nav = 'bids';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);border-bottom:1px solid var(--line);padding:36px 0 28px;">
  <div class="container">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div class="bf-section-eyebrow mb-2"><i class="bi bi-megaphone" style="color:#c0392b;"></i> Open Bids</div>
        <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 6px;">Projects seeking your skills now</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Live project postings from verified owners — submit your bid directly, no middlemen.</p>
      </div>
      <a href="/pages/projects/create.php" class="bf-btn-accent">Post a project</a>
    </div>

    <!-- Search + filters -->
    <div class="bf-searchbar-v2 mt-4" style="max-width:600px;">
      <span class="bf-searchbar-v2-icon"><i class="bi bi-search"></i></span>
      <input type="text" placeholder="Search projects by trade, location, keyword…">
      <div class="bf-searchbar-v2-divider"></div>
      <select><option>All Trades</option><option>Architecture</option><option>Civil</option><option>MEP</option><option>Structural</option><option>QS</option><option>Interior</option></select>
      <button class="bf-searchbar-v2-btn">Search</button>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3 align-items-center">
      <?php foreach (['All','🔴 Hot','New Today','Closing Soon','High Budget','Government','Residential','Commercial','Infrastructure'] as $i=>$f): ?>
      <button class="bf-search-tag" style="<?= $i===0?'background:#1e3a5f;color:#fff;border-color:#1e3a5f;':'' ?>"><?= $f ?></button>
      <?php endforeach; ?>
      <div class="ms-auto d-flex gap-2">
        <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
          <option>Sort: Newest</option><option>Closing soonest</option><option>Budget: High→Low</option><option>Most bids</option>
        </select>
        <select class="form-select form-select-sm" style="font-size:12px;width:auto;border-color:var(--line);">
          <option>All countries</option><option>Kenya</option><option>Nigeria</option><option>Ghana</option><option>UAE</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:36px;padding-bottom:60px;">

  <!-- Stats bar -->
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--line);">
    <?php foreach ([['186','Open projects','bi-kanban'],['47','Closing today','bi-clock text-warning'],['KES 2.4B','Total bid value','bi-currency-exchange'],['4,200+','Professionals bidding','bi-people']] as [$n,$l,$ic]): ?>
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:36px;height:36px;border-radius:10px;background:var(--surface);display:flex;align-items:center;justify-content:center;border:1px solid var(--line);">
        <i class="bi <?= $ic ?>" style="font-size:15px;color:#1e3a5f;"></i>
      </div>
      <div>
        <div style="font-size:16px;font-weight:900;color:var(--ink);"><?= $n ?></div>
        <div style="font-size:10.5px;color:var(--ink-4);"><?= $l ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Bids grid -->
  <div class="row g-3">
    <?php
    // Live from the database (public open projects)
    $bids = [];
    foreach (db_all("SELECT * FROM projects WHERE visibility='public' AND status='open' ORDER BY created_at DESC, id DESC") as $r) {
      $bids[] = [$r['segment'] ?: 'commercial', $r['name'], $r['urgency'], $r['owner_name'], $r['owner_label'], $r['location'],
        $r['budget_display'], $r['duration'], $r['start_label'], ((int)$r['bids_count'] . ' submitted'), $r['deadline_label'], $r['remaining'],
        ($r['trades'] ? explode(',', $r['trades']) : []), $r['description']];
    }

    $typeColors = ['commercial'=>'#1e3a5f','residential'=>'#f59e0b','infrastructure'=>'#22c55e','government'=>'#ef4444'];
    $urgencyClasses = ['hot'=>'hot','closing'=>'closing','new'=>'new-bid'];

    foreach ($bids as [$type,$title,$urgency,$owner,$ownerType,$loc,$budget,$dur,$start,$bidsSubmit,$deadline,$remaining,$tags,$desc]): ?>
    <div class="col-md-6 col-xl-4">
      <div class="bf-bid-v2 <?= $urgencyClasses[$urgency] ?> h-100">
        <!-- Urgency badge -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
          <?php if ($urgency==='hot'): ?>
          <span style="font-size:9px;font-weight:800;letter-spacing:.08em;background:#fef2f2;color:#c0392b;padding:3px 9px;border-radius:4px;">🔴 HOT · HIGH DEMAND</span>
          <?php elseif ($urgency==='closing'): ?>
          <span style="font-size:9px;font-weight:800;letter-spacing:.08em;background:#fff7ed;color:#c2410c;padding:3px 9px;border-radius:4px;">⏰ CLOSING SOON</span>
          <?php else: ?>
          <span style="font-size:9px;font-weight:800;letter-spacing:.08em;background:#f0fdf4;color:#166534;padding:3px 9px;border-radius:4px;">✨ JUST POSTED</span>
          <?php endif; ?>
          <span style="font-size:10px;font-weight:700;color:var(--ink-4);"><?= $bidsSubmit ?></span>
        </div>

        <!-- Title -->
        <?php $bidUrl = '/pages/bids/view.php?title='.urlencode($title).'&owner='.urlencode($owner).'&ownerType='.urlencode($ownerType).'&loc='.urlencode($loc).'&budget='.urlencode($budget).'&dur='.urlencode($dur).'&deadline='.urlencode($deadline).'&bids='.urlencode($bidsSubmit).'&type='.urlencode($type).'&remaining='.urlencode($remaining); ?>
        <a href="<?= $bidUrl ?>" style="font-size:13.5px;font-weight:800;color:var(--ink);line-height:1.3;margin-bottom:8px;display:block;text-decoration:none;"><?= $title ?></a>
        <div style="font-size:11px;color:var(--ink-3);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
          <span style="width:8px;height:8px;border-radius:50%;background:<?= $typeColors[$type] ?>;display:inline-block;flex-shrink:0;"></span>
          <?= $owner ?> · <?= $ownerType ?>
          <i class="bi bi-geo-alt-fill" style="font-size:9px;color:#c0392b;margin-left:4px;"></i><?= $loc ?>
        </div>

        <!-- Description -->
        <div style="font-size:11.5px;color:var(--ink-3);line-height:1.6;margin-bottom:14px;flex:1;"><?= $desc ?></div>

        <!-- Stats 2×2 -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
          <?php foreach ([['Budget',$budget,'#c0392b'],['Duration',$dur,'var(--ink)'],['Start',$start,'var(--ink)'],['Bid Deadline',$deadline,'var(--ink)']] as [$k,$v,$vc]): ?>
          <div style="background:var(--surface);border-radius:8px;padding:8px 10px;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);margin-bottom:2px;"><?= $k ?></div>
            <div style="font-size:12px;font-weight:700;color:<?= $vc ?>;"><?= $v ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Tags -->
        <div class="d-flex flex-wrap gap-1 mb-3">
          <?php foreach ($tags as $t): ?>
          <span style="font-size:10px;font-weight:600;background:var(--surface);border:1px solid var(--line);padding:2px 8px;border-radius:4px;color:var(--ink-3);"><?= $t ?></span>
          <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line-2);">
          <div style="font-size:11px;color:var(--ink-4);"><i class="bi bi-clock" style="color:#f59e0b;"></i> <?= $remaining ?> remaining</div>
          <a href="<?= $bidUrl ?>" class="bf-action-dark" style="font-size:11px;padding:6px 18px;background:#c0392b;text-decoration:none;">View &amp; Bid →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Load more -->
  <div style="text-align:center;margin-top:36px;">
    <button class="bf-btn-outline" style="padding:12px 36px;font-size:13px;">Load more projects</button>
    <div style="font-size:12px;color:var(--ink-4);margin-top:10px;">Showing 6 of 186 open projects</div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
