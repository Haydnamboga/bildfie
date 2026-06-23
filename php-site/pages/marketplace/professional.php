<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/profile_data.php';

// Resolve the provider + the member (user_id) whose real, edited data we render.
$prov = null; $puid = 0;
if (!empty($_GET['id'])) {
    $prov = db_one("SELECT * FROM providers WHERE id=? AND status<>'suspended' LIMIT 1", [(int)$_GET['id']]);
    $puid = (int) ($prov['user_id'] ?? 0);
} elseif (!empty($_GET['u'])) {                         // owner "Preview public" (by member public_id)
    $uu0 = db_one("SELECT id FROM users WHERE public_id=? LIMIT 1", [$_GET['u']]);
    if ($uu0) { $puid = (int) $uu0['id']; $prov = db_one("SELECT * FROM providers WHERE user_id=? AND status<>'suspended' LIMIT 1", [$puid]); }
}
// Ensure any real member has a provider row so reviews / invites / quotes can attach to them.
if (!$prov && $puid) { provider_ensure($puid); $prov = db_one("SELECT * FROM providers WHERE user_id=? AND status<>'suspended' LIMIT 1", [$puid]); }

$pprof  = $puid ? user_profession($puid) : [];
$rstats = $puid ? user_review_stats($puid) : ['count'=>0,'avg'=>0.0,'breakdown'=>[5=>0,4=>0,3=>0,2=>0,1=>0]];

if ($prov) {
    $name  = $prov['name'];
    $role  = $prov['headline'] ?: ($pprof['title'] ?? 'Service Provider');
    $loc   = $prov['location'] ?: 'Kenya';
    $rate  = $prov['day_rate'] ?: 'On request';
    $photo = $prov['photo_url'] ?: (($pprof['photo_url'] ?? '') ?: user_avatar(['name'=>$prov['name']], 200));
    $cover = $prov['cover_url'] ?: ($pprof['cover_url'] ?? '');
    $badge = $prov['badge'] ?: '';
    $bio   = $prov['bio'] ?: ($pprof['bio'] ?? '');
    $createdAt = $prov['created_at'];
} elseif ($puid) {                                     // member preview without a public listing yet
    $uu = db_one("SELECT u.*, r.name AS region_name FROM users u LEFT JOIN regions r ON r.id=u.region_id WHERE u.id=? LIMIT 1", [$puid]);
    $name  = $uu['name'];
    $role  = $pprof['title'] ?: 'Construction Professional';
    $loc   = $uu['region_name'] ?: 'Kenya';
    $rate  = !empty($pprof['day_rate']) ? (($pprof['currency_code'] ?? 'KES').' '.number_format((float)$pprof['day_rate']).'/'.($pprof['rate_unit'] ?? 'day')) : 'On request';
    $photo = ($pprof['photo_url'] ?? '') ?: user_avatar($uu, 200);
    $cover = $pprof['cover_url'] ?? '';
    $badge = '';
    $bio   = $pprof['bio'] ?? '';
    $createdAt = $uu['created_at'];
} else {                                               // legacy ?name= preview (no real member)
    $name  = trim($_GET['name'] ?? 'John Mwangi');
    $role  = trim($_GET['role'] ?? 'Structural Engineer');
    $loc   = trim($_GET['loc'] ?? 'Nairobi, Kenya');
    $rate  = trim($_GET['rate'] ?? 'KES 8,500/day');
    $photo = filter_var($_GET['photo'] ?? '', FILTER_VALIDATE_URL) ? $_GET['photo'] : 'https://randomuser.me/api/portraits/men/32.jpg';
    $cover = ''; $badge = ''; $bio = ''; $createdAt = date('Y-m-d');
}
$verified = (int) ($prov['is_verified'] ?? 0);

// Reputation — real, from client reviews (provider_reviews)
$reviews  = (int) $rstats['count'];
$rating   = $reviews ? number_format($rstats['avg'], 1) : '';
$jobsDone = (int) ($pprof['jobs_completed'] ?? 0);
$isNew    = ($reviews === 0);

// Owner-edited content (keyed to the member behind the listing)
$packages       = $puid ? user_packages($puid) : [];
$folio          = $puid ? user_portfolio($puid) : [];
$languages      = $puid ? user_languages($puid) : [];
$certifications = $puid ? user_certifications($puid) : [];
$workHistory    = $puid ? user_work_history($puid) : [];
$education      = $puid ? user_education($puid) : [];
$skills         = $puid ? user_skills($puid) : [];
if (!$skills && $prov) $skills = array_column(db_all("SELECT skill FROM provider_skills WHERE provider_id=? ORDER BY sort_order", [(int)$prov['id']]), 'skill');
$reviewList     = $puid ? user_reviews($puid, 12) : [];

$ratingBreak = [];
foreach ([5,4,3,2,1] as $st) { $c = (int) $rstats['breakdown'][$st]; $ratingBreak[] = [$st, $c, $reviews ? round($c * 100 / $reviews) : 0]; }

$page_title = $name . ' — ' . $role;
$nav = 'professionals';
$ctx = htmlspecialchars($name, ENT_QUOTES);
$provId = (int) ($prov['id'] ?? 0);   // real provider target for engage actions (0 = legacy preview)
$slug = $prov['public_id'] ?? ('u' . $puid);
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);padding:22px 0 40px;">
  <div class="container" style="max-width:1080px;">

    <!-- Breadcrumb -->
    <div style="font-size:12px;color:var(--ink-4);margin-bottom:14px;">
      <a href="/pages/marketplace/professionals.php" style="color:var(--ink-3);text-decoration:none;">Professionals</a>
      <i class="bi bi-chevron-right" style="font-size:9px;"></i> <span style="color:var(--ink-2);"><?= htmlspecialchars($name) ?></span>
    </div>

    <!-- ═══ Identity hero ═══ -->
    <div class="bf-pf-hero">
      <div class="bf-pf-cover" style="<?= $cover ? "background-image:url('".htmlspecialchars($cover, ENT_QUOTES)."');" : "background:linear-gradient(120deg,#0d1f36,#1e3a5f);" ?>"></div>
      <div class="bf-pf-headrow">
        <div class="bf-pf-avatar-wrap">
          <img src="<?= htmlspecialchars($photo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name) ?>" class="bf-pf-avatar">
        </div>
        <div class="bf-pf-id">
          <div class="bf-pf-name"><?= htmlspecialchars($name) ?></div>
          <div class="bf-pf-headline"><?= htmlspecialchars($role) ?></div>
          <div class="bf-pf-meta">
            <span><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars($loc) ?></span>
            <span><i class="bi bi-clock"></i>Replies in ~2 hours</span>
            <?php $pfAvail = $prov ? (int)($prov['is_available'] ?? 1) : 1; ?>
            <?php if ($pfAvail): ?><span style="color:#16a34a;font-weight:700;"><i class="bi bi-circle-fill" style="font-size:8px;color:#16a34a;"></i>Available now</span><?php else: ?><span style="color:#c2410c;font-weight:700;"><i class="bi bi-circle-fill" style="font-size:8px;color:#c2410c;"></i>Busy / by arrangement</span><?php endif; ?>
          </div>
          <div class="bf-pf-verify-pills">
            <?php
            if ($prov):
              $pfBadges = provider_badges($prov, ['is_available']);
              if ($pfBadges):
                foreach ($pfBadges as [$bl,$bi,$bbg,$bc]): ?>
                  <span class="bf-pf-pill" style="background:<?= $bbg ?>;color:<?= $bc ?>;"><i class="bi <?= $bi ?>"></i> <?= htmlspecialchars($bl) ?></span>
              <?php endforeach;
              else: ?>
                <span class="bf-pf-pill" style="background:#eff6ff;color:#1e40af;"><i class="bi bi-stars"></i> New provider</span>
                <span class="bf-pf-pill" style="background:#f4f4f2;color:#6b6b6b;"><i class="bi bi-hourglass-split"></i> No badges awarded yet</span>
              <?php endif;
            else: ?>
              <?php if ($badge): ?><span class="bf-pf-pill gold"><i class="bi bi-award-fill"></i> <?= htmlspecialchars($badge) ?></span><?php endif; ?>
              <span class="bf-pf-pill green"><i class="bi bi-patch-check-fill"></i> NCA Verified</span>
              <span class="bf-pf-pill navy"><i class="bi bi-person-badge-fill"></i> ID Verified</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="bf-pf-head-cta">
          <?php if ($isNew): ?>
          <div class="bf-pf-rating-big"><b style="color:#1e40af;">New</b><div><div style="font-size:11px;color:var(--ink-4);">No reviews yet</div></div></div>
          <?php else: ?>
          <div class="bf-pf-rating-big">
            <b><?= htmlspecialchars($rating) ?></b>
            <div>
              <div class="stars"><?php for($i=0;$i<5;$i++):?><i class="bi <?= $i<round($rstats['avg'])?'bi-star-fill':'bi-star' ?>"></i><?php endfor;?></div>
              <div style="font-size:11px;color:var(--ink-4);"><?= $reviews ?> review<?= $reviews==1?'':'s' ?></div>
            </div>
          </div>
          <?php endif; ?>
          <div class="bf-pf-rating-big" style="margin-top:4px;"></div>
        </div>
      </div>
    </div>

    <!-- ═══ Action bar ═══ -->
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;background:var(--white);border:1px solid var(--line);border-radius:14px;padding:14px 18px;margin-top:12px;">
      <div style="margin-right:auto;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-4);">Pricing</div>
        <div style="font-size:15px;font-weight:800;color:var(--ink);">Fixed quote per project</div>
        <div style="font-size:11px;color:var(--ink-4);">Request a quote or post a project to get a fixed price.</div>
      </div>
      <button class="bf-fav" data-fav="pro:<?= htmlspecialchars($slug, ENT_QUOTES) ?>" title="Save"><i class="bi bi-heart"></i></button>
      <a href="/pages/dashboard/messages.php" class="bf-btn-ghost"><i class="bi bi-chat-dots me-1"></i>Message</a>
      <button class="bf-btn-ghost" data-engage="quote" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>"><i class="bi bi-receipt me-1"></i>Request quote</button>
      <button class="bf-btn-accent" data-engage="hire" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>" style="border:none;cursor:pointer;"><i class="bi bi-person-plus me-1"></i>Hire / Invite</button>
    </div>

    <!-- ═══ Trust strip ═══ -->
    <?php if (!$isNew):
      $memberSince = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
      $respLbl = !empty($pprof['response_time']) ? $pprof['response_time'] : 'Fast';
    ?>
    <div class="bf-pf-trust">
      <div class="bf-pf-trust-item"><div class="bf-pf-trust-ic" style="background:#fdf6e3;color:#9a7d27;"><i class="bi bi-star-fill"></i></div><div><div class="bf-pf-trust-v"><?= htmlspecialchars($rating ?: '—') ?></div><div class="bf-pf-trust-l">Avg rating</div></div></div>
      <div class="bf-pf-trust-item"><div class="bf-pf-trust-ic" style="background:#f0fdf4;color:#166534;"><i class="bi bi-chat-square-text-fill"></i></div><div><div class="bf-pf-trust-v"><?= number_format($reviews) ?></div><div class="bf-pf-trust-l">Reviews</div></div></div>
      <div class="bf-pf-trust-item"><div class="bf-pf-trust-ic" style="background:#eaf0f6;color:#1e3a5f;"><i class="bi bi-briefcase-fill"></i></div><div><div class="bf-pf-trust-v"><?= number_format($jobsDone) ?></div><div class="bf-pf-trust-l">Jobs completed</div></div></div>
      <div class="bf-pf-trust-item"><div class="bf-pf-trust-ic" style="background:#eff6ff;color:#1e40af;"><i class="bi bi-lightning-charge-fill"></i></div><div><div class="bf-pf-trust-v"><?= $respLbl ?></div><div class="bf-pf-trust-l">Response time</div></div></div>
      <div class="bf-pf-trust-item"><div class="bf-pf-trust-ic" style="background:#f0fdf4;color:#166534;"><i class="bi bi-calendar-check-fill"></i></div><div><div class="bf-pf-trust-v"><?= $memberSince ?></div><div class="bf-pf-trust-l">On bildfie since</div></div></div>
    </div>
    <?php endif; ?>

    <div class="row g-3" style="margin-top:2px;">
      <!-- LEFT -->
      <div class="col-lg-8">

        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-person-lines-fill"></i> About</div></div>
          <div class="bf-pf-card-b"><p style="font-size:13px;color:var(--ink-2);line-height:1.7;margin:0;"><?php if (!empty($bio)): ?><?= nl2br(htmlspecialchars($bio)) ?><?php else: ?><?= htmlspecialchars($name) ?> is a <?= htmlspecialchars(strtolower($role)) ?> based in <?= htmlspecialchars(explode(',',$loc)[0]) ?><?= $isNew ? ', newly listed on bildfie.' : '.' ?><?php endif; ?></p></div>
        </div>

        <!-- Service packages -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-box-seam"></i> Service packages</div></div>
          <div class="bf-pf-card-b">
            <?php if (!$packages): ?><div style="text-align:center;color:var(--ink-4);font-size:12.5px;padding:18px;">No service packages published yet.</div><?php else: ?>
            <div class="bf-pkg-grid">
              <?php foreach ($packages as $pk): $feats = pkg_features($pk['features']); ?>
              <div class="bf-pkg <?= $pk['is_featured']?'feat':'' ?>">
                <?php if ($pk['is_featured']): ?><div class="bf-pkg-rib">MOST POPULAR</div><?php endif; ?>
                <div class="bf-pkg-top">
                  <div class="bf-pkg-name"><?= htmlspecialchars(trim(($pk['tier'] ? $pk['tier'].' · ' : '').$pk['title'])) ?></div>
                  <div class="bf-pkg-price"><?= htmlspecialchars($pk['price'] ?: '—') ?><small><?= htmlspecialchars((string)$pk['price_unit']) ?></small></div>
                  <?php if ($pk['description']): ?><div class="bf-pkg-desc"><?= htmlspecialchars($pk['description']) ?></div><?php endif; ?>
                </div>
                <div class="bf-pkg-body">
                  <?php if ($pk['delivery'] || $pk['revisions']): ?><div class="bf-pkg-meta"><?php if($pk['delivery']):?><span><i class="bi bi-clock"></i><?= htmlspecialchars($pk['delivery']) ?></span><?php endif;?><?php if($pk['revisions']):?><span><i class="bi bi-arrow-repeat"></i><?= htmlspecialchars($pk['revisions']) ?></span><?php endif;?></div><?php endif; ?>
                  <?php foreach ($feats as $f): ?><div class="bf-pkg-feat"><i class="bi bi-check-lg"></i><?= htmlspecialchars($f) ?></div><?php endforeach; ?>
                  <button class="bf-pkg-btn <?= $pk['is_featured']?'solid':'' ?>" data-engage="quote" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>" style="cursor:pointer;width:100%;">Request <?= htmlspecialchars($pk['tier'] ?: $pk['title']) ?></button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Portfolio -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-images"></i> Portfolio</div></div>
          <div class="bf-pf-card-b">
            <?php if (!$folio): ?><div style="text-align:center;color:var(--ink-4);font-size:12.5px;padding:18px;">No portfolio projects yet.</div><?php else: ?>
            <div class="bf-pf-folio-grid">
              <?php foreach ($folio as $pj): $img = $pj['image_url'] ?: ('https://ui-avatars.com/api/?name='.urlencode($pj['title']).'&background=0d1f36&color=fff&size=400&bold=true'); ?>
              <div class="bf-pf-folio"><img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($pj['title']) ?>"><div class="bf-pf-folio-ov"><?php if($pj['category']):?><div class="bf-pf-folio-cat"><?= htmlspecialchars($pj['category']) ?></div><?php endif;?><div class="bf-pf-folio-name"><?= htmlspecialchars($pj['title']) ?></div><?php if($pj['year']):?><div class="bf-pf-folio-yr">Completed <?= htmlspecialchars($pj['year']) ?></div><?php endif;?></div></div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Skills -->
        <?php if ($skills): ?>
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-stars"></i> Skills &amp; specialisations</div></div>
          <div class="bf-pf-card-b"><div class="bf-pf-chips"><?php foreach ($skills as $i=>$sk): ?><span class="bf-pf-chip <?= $i<3?'core':'' ?>"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?></div></div>
        </div>
        <?php endif; ?>

        <!-- Work history -->
        <?php if ($workHistory): ?>
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-clock-history"></i> Work history</div></div>
          <div class="bf-pf-card-b">
            <?php foreach ($workHistory as $w): ?>
            <div class="bf-work">
              <div class="bf-work-ic"><i class="bi bi-building-fill"></i></div>
              <div style="flex:1;min-width:0;">
                <div class="bf-work-role"><?= htmlspecialchars($w['role']) ?></div>
                <?php if ($w['organization']): ?><div class="bf-work-org"><?= htmlspecialchars($w['organization']) ?></div><?php endif; ?>
                <?php if ($w['period']): ?><div class="bf-work-when"><?= htmlspecialchars($w['period']) ?></div><?php endif; ?>
                <?php if ($w['description']): ?><div class="bf-work-desc"><?= htmlspecialchars($w['description']) ?></div><?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Education -->
        <?php if ($education): ?>
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-mortarboard-fill"></i> Education &amp; training</div></div>
          <div class="bf-pf-card-b">
            <?php foreach ($education as $ed): ?>
            <div class="bf-pf-cert">
              <div class="bf-pf-cert-ic" style="background:#eaf0f6;color:#1e3a5f;"><i class="bi bi-mortarboard-fill"></i></div>
              <div style="flex:1;min-width:0;"><div class="bf-pf-cert-name"><?= htmlspecialchars($ed['title']) ?></div><?php if ($ed['institution']): ?><div class="bf-pf-cert-sub"><?= htmlspecialchars($ed['institution']) ?></div><?php endif; ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h">
            <div class="bf-pf-card-t"><i class="bi bi-chat-quote-fill"></i> Client reviews</div>
            <button class="bf-pf-card-edit" data-engage="review" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>" style="background:none;border:none;cursor:pointer;"><i class="bi bi-pencil-square"></i> Leave a review</button>
          </div>
          <div class="bf-pf-card-b">
            <?php if (!$reviewList): ?><div style="text-align:center;color:var(--ink-4);font-size:12.5px;padding:22px;">No reviews yet — be the first to work with <?= htmlspecialchars($name) ?>.</div><?php else: ?>
            <div class="bf-rate-break">
              <div class="bf-rate-big"><b><?= htmlspecialchars($rating) ?></b><div class="stars"><?php for($i=0;$i<5;$i++):?><i class="bi <?= $i<round($rstats['avg'])?'bi-star-fill':'bi-star' ?>"></i><?php endfor;?></div><div class="cnt"><?= $reviews ?> review<?= $reviews==1?'':'s' ?></div></div>
              <div class="bf-rate-bars">
                <?php foreach ($ratingBreak as [$st,$cnt,$pct]): ?>
                <div class="bf-rate-row"><span><?= $st ?> <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i></span><span class="bf-rate-track"><span class="bf-rate-fill" style="width:<?= $pct ?>%;"></span></span><span><?= $cnt ?></span></div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php foreach ($reviewList as $rv):
              $rn = $rv['reviewer_name']; $rs = (int) $rv['rating'];
              $rav = 'https://ui-avatars.com/api/?name='.urlencode($rn).'&background=eaf0f6&color=1e3a5f&size=80&bold=true';
            ?>
            <div class="bf-pf-review">
              <div class="bf-pf-review-head">
                <img src="<?= htmlspecialchars($rav, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($rn) ?>" class="bf-pf-review-av">
                <div><div class="bf-pf-review-name"><?= htmlspecialchars($rn) ?></div><div class="bf-pf-review-meta"><?= $rv['created_at'] ? date('M j, Y', strtotime($rv['created_at'])) : '' ?></div></div>
                <div class="bf-pf-review-stars"><?php for($i=0;$i<5;$i++):?><i class="bi <?= $i<$rs?'bi-star-fill':'bi-star' ?>"></i><?php endfor;?></div>
              </div>
              <?php if (!empty($rv['body'])): ?><div class="bf-pf-review-text">"<?= htmlspecialchars($rv['body']) ?>"</div><?php endif; ?>
              <?php if (!empty($rv['project'])): ?><div class="bf-pf-review-proj"><i class="bi bi-bookmark-fill" style="color:#1e3a5f;font-size:10px;"></i> Project: <b><?= htmlspecialchars($rv['project']) ?></b></div><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-lg-4">
        <!-- Hire box -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-b" style="text-align:center;">
            <img src="<?= htmlspecialchars($photo, ENT_QUOTES) ?>" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--line);margin-bottom:10px;">
            <div style="font-size:14px;font-weight:800;color:var(--ink);"><?= htmlspecialchars($name) ?></div>
            <div style="font-size:11.5px;color:var(--ink-3);margin-bottom:14px;"><?= htmlspecialchars($role) ?></div>
            <button class="bf-btn-accent" data-engage="hire" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>" style="border:none;cursor:pointer;width:100%;margin-bottom:8px;"><i class="bi bi-person-plus me-1"></i>Hire / Invite</button>
            <button class="bf-btn-ghost" data-engage="quote" data-ctx="<?= $ctx ?>" data-pid="<?= $provId ?>" style="width:100%;"><i class="bi bi-receipt me-1"></i>Request a quote</button>
            <div style="font-size:11px;color:var(--ink-4);margin-top:12px;"><i class="bi bi-shield-fill-check" style="color:#16a34a;"></i> Escrow-protected payments</div>
          </div>
        </div>

        <!-- Languages -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-translate"></i> Languages</div></div>
          <div class="bf-pf-card-b" style="padding-top:14px;padding-bottom:14px;">
            <?php if (!$languages): ?><div style="font-size:12px;color:var(--ink-4);">Not specified.</div><?php else: foreach ($languages as $lg): ?>
            <div class="bf-lang-row"><span><?= htmlspecialchars($lg['language']) ?></span><span class="bf-lang-lvl"><?= htmlspecialchars((string)$lg['level']) ?></span></div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Business info -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-shop"></i> Business info</div></div>
          <div class="bf-pf-card-b" style="padding-top:8px;padding-bottom:8px;">
            <?php
              $bizRows = array_values(array_filter([
                ['bi-calendar-check','Years in business', $pprof['years_in_business'] ?? ''],
                ['bi-people',        'Team size',         $pprof['team_size'] ?? ''],
                ['bi-clock',         'Working hours',     $pprof['working_hours'] ?? ''],
                ['bi-reply-all-fill','On-time delivery',  ($pprof['on_time_pct'] ?? '') !== '' ? $pprof['on_time_pct'].'%' : ''],
                ['bi-geo-alt-fill',  'Serving',           $pprof['serving_area'] ?? ''],
              ], fn($r) => trim((string)$r[2]) !== ''));
            ?>
            <?php if (!$bizRows): ?><div style="font-size:12px;color:var(--ink-4);padding:6px 0;">Not specified.</div><?php else: foreach ($bizRows as [$ic,$k,$v]): ?>
            <div class="bf-pf-contact" style="justify-content:space-between;"><span style="display:flex;align-items:center;gap:11px;"><i class="bi <?= $ic ?>"></i><?= $k ?></span><span style="font-weight:700;color:var(--ink);"><?= htmlspecialchars((string)$v) ?></span></div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Certifications -->
        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-patch-check-fill"></i> Certifications</div></div>
          <div class="bf-pf-card-b">
            <?php if (!$certifications): ?><div style="font-size:12px;color:var(--ink-4);padding:14px;">No certifications listed.</div><?php else: foreach ($certifications as $ct):
              $stU = strtoupper(trim((string)$ct['status']));
              $stMap = ['VERIFIED'=>['#166534','#f0fdf4'],'CURRENT'=>['#1e3a5f','#eaf0f6'],'PENDING'=>['#b45309','#fffbeb']];
              [$cc,$cbg] = $stMap[$stU] ?? ['#1e3a5f','#eaf0f6'];
            ?>
            <div class="bf-pf-cert"><div class="bf-pf-cert-ic" style="background:<?= $cbg ?>;color:<?= $cc ?>;"><i class="bi bi-patch-check-fill"></i></div><div style="flex:1;min-width:0;"><div class="bf-pf-cert-name"><?= htmlspecialchars($ct['name']) ?></div><?php if($ct['issuer']):?><div class="bf-pf-cert-sub"><?= htmlspecialchars($ct['issuer']) ?></div><?php endif;?></div><?php if($stU):?><span class="bf-pf-cert-stat" style="color:<?= $cc ?>;background:<?= $cbg ?>;"><?= htmlspecialchars($stU) ?></span><?php endif;?></div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
