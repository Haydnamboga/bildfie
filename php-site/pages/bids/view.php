<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$title     = trim($_GET['title'] ?? '4-Storey Residential Apartment — Structural & Finishing Works');
$owner     = trim($_GET['owner'] ?? 'Daniel Otieno');
$ownerType = trim($_GET['ownerType'] ?? 'Verified Owner');
$loc       = trim($_GET['loc'] ?? 'Kilimani, Nairobi');
$budget    = trim($_GET['budget'] ?? 'KES 48,000–65,000');
$dur       = trim($_GET['dur'] ?? '8 months');
$deadline  = trim($_GET['deadline'] ?? 'May 2025');
$nbids     = (int)($_GET['bids'] ?? 14);
$type      = trim($_GET['type'] ?? 'commercial');
$remaining = trim($_GET['remaining'] ?? '4d 8h');

$page_title = $title;
$nav = 'bids';

$typeColors = ['commercial'=>'#1e3a5f','residential'=>'#f59e0b','infrastructure'=>'#22c55e','government'=>'#ef4444'];
$dot = $typeColors[$type] ?? '#1e3a5f';

$scope = [
  'Reinforced concrete frame to G+4, including columns, beams and slabs',
  'Block work, plastering and external rendering',
  'Floor and wall tiling to wet areas and common spaces',
  'Internal and external painting (3 coats, weather-guard external)',
  'Aluminium windows and timber doors installation',
  'Snagging, making good and handover to client representative',
];
$reqs = ['NCA registration (Category G3 or higher)','Valid contractor all-risk insurance','Minimum 3 similar completed projects','Own or hired tower crane access','Ability to start within 2 weeks'];
$trades = ['Structural','Finishing','Tiling','Painting','Masonry'];
$attachments = [['Bill of Quantities (BOQ).pdf','420 KB'],['Architectural drawings.pdf','3.1 MB'],['Structural drawings.pdf','2.4 MB']];
$qa = [
  ['Is the BOQ provided or do we prepare our own?','Owner','BOQ is attached above. Bid against it; flag any discrepancies in your cover letter.','2 days ago'],
  ['Is scaffolding included or contractor-supplied?','Owner','Contractor to supply all access equipment including scaffolding and crane.','3 days ago'],
];
$similar = [
  ['MEP Installation — Commercial Office Block','Lagos, Nigeria','$35,000–52,000','commercial'],
  ['Renovation & Extension — Period Bungalow','Karen, Nairobi','KES 4,500–7,000','residential'],
  ['Road Construction — 12km Murram Access Road','Nakuru, Kenya','KES 280,000+','infrastructure'],
];
$ctx = htmlspecialchars($title, ENT_QUOTES);
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);padding:22px 0 48px;">
  <div class="container" style="max-width:1080px;">

    <!-- Breadcrumb -->
    <div style="font-size:12px;color:var(--ink-4);margin-bottom:14px;">
      <a href="/pages/bids/index.php" style="color:var(--ink-3);text-decoration:none;">Open Bids</a>
      <i class="bi bi-chevron-right" style="font-size:9px;"></i> <span style="color:var(--ink-2);"><?= htmlspecialchars($title) ?></span>
    </div>

    <div class="row g-3">
      <!-- LEFT -->
      <div class="col-lg-8">

        <!-- Header -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-b">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
              <span style="font-size:9px;font-weight:800;letter-spacing:.08em;background:#fef2f2;color:#c0392b;padding:3px 9px;border-radius:4px;">🔴 HOT · HIGH DEMAND</span>
              <span style="font-size:10px;font-weight:700;color:var(--ink-4);"><i class="bi bi-people"></i> <?= $nbids ?> bids submitted</span>
              <button class="bf-fav" data-fav="bid:<?= urlencode($title) ?>" title="Save" style="margin-left:auto;"><i class="bi bi-heart"></i></button>
            </div>
            <h1 style="font-size:clamp(19px,2.6vw,25px);font-weight:800;color:var(--ink);line-height:1.25;margin:0 0 10px;letter-spacing:-.01em;"><?= htmlspecialchars($title) ?></h1>
            <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:12.5px;color:var(--ink-3);">
              <span><span style="width:9px;height:9px;border-radius:50%;background:<?= $dot ?>;display:inline-block;margin-right:5px;"></span><?= htmlspecialchars($owner) ?> · <?= htmlspecialchars($ownerType) ?></span>
              <span><i class="bi bi-geo-alt-fill" style="color:#c0392b;"></i> <?= htmlspecialchars($loc) ?></span>
              <span><i class="bi bi-clock" style="color:#f59e0b;"></i> Closes in <?= htmlspecialchars($remaining) ?></span>
            </div>
          </div>
        </div>

        <!-- Overview -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-file-text"></i> Project overview</div></div>
          <div class="bf-pf-card-b">
            <p style="font-size:13px;color:var(--ink-2);line-height:1.7;margin:0 0 14px;">A residential apartment block of 24 units (G+4) in <?= htmlspecialchars($loc) ?>. The structural framework is partially complete and the owner is seeking an experienced contractor to deliver the remaining structural and full finishing works to a high standard, on programme and within budget.</p>
            <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:8px;">Scope of works</div>
            <?php foreach ($scope as $s): ?>
            <div style="display:flex;gap:9px;font-size:12.5px;color:var(--ink-2);padding:5px 0;"><i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:13px;margin-top:2px;flex-shrink:0;"></i><?= $s ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Requirements -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-clipboard-check"></i> Requirements</div></div>
          <div class="bf-pf-card-b">
            <?php foreach ($reqs as $r): ?>
            <div style="display:flex;gap:9px;font-size:12.5px;color:var(--ink-2);padding:5px 0;"><i class="bi bi-dot" style="font-size:18px;color:#1e3a5f;margin-top:-2px;flex-shrink:0;"></i><?= $r ?></div>
            <?php endforeach; ?>
            <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:7px;">
              <?php foreach ($trades as $t): ?><span class="bf-pf-chip core"><?= $t ?></span><?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Attachments -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-paperclip"></i> Documents</div></div>
          <div class="bf-pf-card-b" style="padding-top:8px;padding-bottom:8px;">
            <?php foreach ($attachments as [$fn,$sz]): ?>
            <a href="#" style="display:flex;align-items:center;gap:12px;padding:11px 0;border-top:1px solid var(--line-2);text-decoration:none;">
              <span style="width:36px;height:36px;border-radius:8px;background:#fef2f2;color:#c0392b;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-file-earmark-pdf"></i></span>
              <span style="flex:1;font-size:12.5px;font-weight:600;color:var(--ink);"><?= $fn ?></span>
              <span style="font-size:11px;color:var(--ink-4);"><?= $sz ?></span>
              <i class="bi bi-download" style="color:#1e3a5f;"></i>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Q&A -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-chat-left-dots"></i> Questions &amp; answers</div></div>
          <div class="bf-pf-card-b">
            <?php foreach ($qa as [$q,$by,$a,$when]): ?>
            <div style="padding:12px 0;border-top:1px solid var(--line-2);">
              <div style="font-size:12.5px;font-weight:700;color:var(--ink);"><i class="bi bi-question-circle" style="color:#1e3a5f;"></i> <?= $q ?></div>
              <div style="font-size:12.5px;color:var(--ink-2);line-height:1.6;margin:6px 0 0;padding-left:18px;border-left:2px solid var(--line);margin-left:4px;"><b style="color:#1e3a5f;"><?= $by ?>:</b> <?= $a ?> <span style="color:var(--ink-4);font-size:11px;">· <?= $when ?></span></div>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;gap:8px;margin-top:12px;">
              <input class="bf-f-input" placeholder="Ask the owner a question…">
              <button class="bf-btn-navy" style="white-space:nowrap;">Ask</button>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT (sticky) -->
      <div class="col-lg-4">
        <div style="position:sticky;top:80px;">

          <!-- Bid facts -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-b" style="padding-bottom:8px;">
              <?php foreach ([['Budget',$budget,'#c0392b'],['Duration',$dur,'var(--ink)'],['Start','Immediate','var(--ink)'],['Bid deadline',$deadline,'var(--ink)']] as [$k,$v,$vc]): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-top:1px solid var(--line-2);">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-4);"><?= $k ?></span>
                <span style="font-size:13px;font-weight:800;color:<?= $vc ?>;"><?= htmlspecialchars($v) ?></span>
              </div>
              <?php endforeach; ?>
              <div style="background:#fff7ed;color:#c2410c;border-radius:8px;padding:9px 12px;font-size:11.5px;font-weight:700;text-align:center;margin-top:12px;"><i class="bi bi-clock-history"></i> Closes in <?= htmlspecialchars($remaining) ?></div>
            </div>
          </div>

          <!-- Submit bid -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-send"></i> Submit your bid</div></div>
            <form class="js-engage-form">
              <div class="bf-pf-card-b">
                <label class="bf-f-lbl">Your bid amount (KES)</label>
                <input class="bf-f-input" placeholder="e.g. 4,200,000" required>
                <label class="bf-f-lbl mt-2">Delivery time</label>
                <select class="bf-f-input"><option>Within budget timeline</option><option>Faster — I can beat it</option><option>I'll propose a schedule</option></select>
                <label class="bf-f-lbl mt-2">Cover letter</label>
                <textarea class="bf-f-input" rows="4" placeholder="Why you're the right contractor — relevant experience, approach, team…" required></textarea>
                <div style="font-size:10.5px;color:var(--ink-4);margin-top:8px;"><i class="bi bi-shield-fill-check" style="color:#16a34a;"></i> Payments are escrow-protected and released on milestone approval.</div>
              </div>
              <div class="bf-pf-card-b" style="padding-top:0;">
                <button type="submit" class="bf-btn-accent" style="width:100%;border:none;cursor:pointer;"><i class="bi bi-send me-1"></i>Submit bid</button>
              </div>
            </form>
            <div class="bf-modal-success js-engage-success">
              <div class="bf-modal-success-ic"><i class="bi bi-check-lg"></i></div>
              <div class="bf-modal-success-t">Bid submitted</div>
              <div class="bf-modal-success-s">The owner has been notified. Track this bid under your dashboard — you'll be alerted if you're shortlisted.</div>
              <a href="/pages/dashboard/index.php" class="bf-btn-navy" style="text-decoration:none;">Go to dashboard</a>
            </div>
          </div>

          <!-- Client info -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-person-badge"></i> About the client</div></div>
            <div class="bf-pf-card-b" style="padding-top:14px;">
              <div style="display:flex;align-items:center;gap:11px;margin-bottom:12px;">
                <div style="width:42px;height:42px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;"><?= strtoupper(substr($owner,0,1)) ?></div>
                <div><div style="font-size:13px;font-weight:800;color:var(--ink);"><?= htmlspecialchars($owner) ?></div><div style="font-size:11px;color:#166534;font-weight:700;"><i class="bi bi-patch-check-fill"></i> <?= htmlspecialchars($ownerType) ?></div></div>
              </div>
              <?php foreach ([['Member since','2021'],['Projects posted','12'],['Hire rate','83%'],['Avg. budget','KES 32M']] as [$k,$v]): ?>
              <div class="bf-pf-contact" style="justify-content:space-between;padding:7px 0;"><span style="color:var(--ink-3);"><?= $k ?></span><span style="font-weight:700;color:var(--ink);"><?= $v ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Similar -->
    <div style="margin-top:28px;">
      <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:12px;">Similar open projects</div>
      <div class="row g-3">
        <?php foreach ($similar as [$st,$sl,$sb,$sty]): ?>
        <div class="col-md-4">
          <a href="/pages/bids/view.php?title=<?= urlencode($st) ?>&loc=<?= urlencode($sl) ?>&budget=<?= urlencode($sb) ?>&type=<?= urlencode($sty) ?>" style="text-decoration:none;display:block;background:var(--white);border:1px solid var(--line);border-radius:12px;padding:16px;height:100%;">
            <div style="font-size:13px;font-weight:800;color:var(--ink);line-height:1.35;margin-bottom:8px;"><?= $st ?></div>
            <div style="font-size:11.5px;color:var(--ink-4);"><i class="bi bi-geo-alt-fill" style="color:#c0392b;"></i> <?= $sl ?></div>
            <div style="font-size:13px;font-weight:800;color:#c0392b;margin-top:8px;"><?= $sb ?></div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
