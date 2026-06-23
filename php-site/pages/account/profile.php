<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/companies.php';
require_once __DIR__ . '/../../config/profile_data.php';
require_login();
$page_title = 'My Profile'; $sp = 'profile';
$u = current_user();
$uid = (int) $u['id'];

// ── change profile photo / cover (POST → redirect → GET) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_media') {
    $photo = member_upload_image('photo');
    $cover = member_upload_image('cover');
    if ($photo !== null || $cover !== null) {
        user_set_media($uid, $photo, $cover);   // also refreshes the session snapshot
        member_provider_sync($uid);             // keep the public listing photo in sync
        $_SESSION['pf_flash'] = ['ok', ($photo && $cover) ? 'Photo and cover updated.' : ($photo ? 'Profile photo updated.' : 'Cover photo updated.')];
    } else {
        $_SESSION['pf_flash'] = ['err', 'Choose a JPG, PNG, WebP or GIF image.'];
    }
    header('Location: /pages/account/profile.php'); exit;
}

// ── save service packages (POST → redirect → GET) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_packages') {
    $titles = $_POST['pkg_title'] ?? [];
    $feat   = (string) ($_POST['pkg_featured'] ?? '');   // index of the "Most popular" package
    $rows = [];
    foreach ($titles as $i => $t) {
        $rows[] = [
            'tier'        => $_POST['pkg_tier'][$i]      ?? '',
            'title'       => $t,
            'price'       => $_POST['pkg_price'][$i]     ?? '',
            'price_unit'  => $_POST['pkg_unit'][$i]      ?? '',
            'description' => $_POST['pkg_desc'][$i]      ?? '',
            'delivery'    => $_POST['pkg_delivery'][$i]  ?? '',
            'revisions'   => $_POST['pkg_revisions'][$i] ?? '',
            'features'    => $_POST['pkg_features'][$i]  ?? '',
            'is_featured' => ($feat === (string) $i) ? 1 : 0,
        ];
    }
    user_save_packages($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Service packages saved.'];
    header('Location: /pages/account/profile.php#packages'); exit;
}

// ── save portfolio / past projects (supports photo uploads + links) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_portfolio') {
    $rows = [];
    foreach (($_POST['folio_title'] ?? []) as $i => $t) {
        $img = trim($_POST['folio_img'][$i] ?? '');     // existing image / pasted link
        $up  = member_upload_file_at('folio_file', $i, 'portfolio');
        if ($up) $img = $up;                             // a freshly uploaded photo wins
        $rows[] = ['title' => $t, 'category' => $_POST['folio_cat'][$i] ?? '',
                   'year' => $_POST['folio_year'][$i] ?? '', 'image_url' => $img];
    }
    user_save_portfolio($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Portfolio updated.'];
    header('Location: /pages/account/profile.php#portfolio'); exit;
}

// ── save other services & rates ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_services') {
    $rows = [];
    foreach (($_POST['svc_name'] ?? []) as $i => $n) {
        $rows[] = ['name' => $n, 'description' => $_POST['svc_desc'][$i] ?? '',
                   'rate' => $_POST['svc_rate'][$i] ?? '', 'rate_unit' => $_POST['svc_unit'][$i] ?? ''];
    }
    user_save_services($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Services updated.'];
    header('Location: /pages/account/profile.php#services'); exit;
}

// ── save languages ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_languages') {
    $rows = [];
    foreach (($_POST['lang_name'] ?? []) as $i => $n) {
        $rows[] = ['language' => $n, 'level' => $_POST['lang_level'][$i] ?? ''];
    }
    user_save_languages($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Languages updated.'];
    header('Location: /pages/account/profile.php#languages'); exit;
}

// ── save certifications ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_certifications') {
    $rows = [];
    foreach (($_POST['cert_name'] ?? []) as $i => $n) {
        $rows[] = ['name' => $n, 'issuer' => $_POST['cert_issuer'][$i] ?? '', 'status' => $_POST['cert_status'][$i] ?? ''];
    }
    user_save_certifications($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Certifications updated.'];
    header('Location: /pages/account/profile.php#certifications'); exit;
}

// ── save business info ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_business') {
    user_save_business($uid, [
        'years_in_business' => $_POST['biz_years']   ?? '',
        'team_size'         => $_POST['biz_team']    ?? '',
        'working_hours'     => $_POST['biz_hours']   ?? '',
        'serving_area'      => $_POST['biz_serving'] ?? '',
        'website'           => $_POST['biz_website'] ?? '',
        'public_phone'      => $_POST['biz_phone']   ?? '',
    ]);
    $_SESSION['pf_flash'] = ['ok', 'Business info updated.'];
    header('Location: /pages/account/profile.php#business'); exit;
}

// ── save skills ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_skills') {
    user_set_skills($uid, explode(',', $_POST['skills'] ?? ''));
    member_provider_sync($uid);   // mirror specialisations to the public listing
    $_SESSION['pf_flash'] = ['ok', 'Skills updated.'];
    header('Location: /pages/account/profile.php#skills'); exit;
}

// ── save work history ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_work') {
    $rows = [];
    foreach (($_POST['wh_role'] ?? []) as $i => $r) {
        $rows[] = ['role' => $r, 'organization' => $_POST['wh_org'][$i] ?? '',
                   'period' => $_POST['wh_when'][$i] ?? '', 'description' => $_POST['wh_desc'][$i] ?? ''];
    }
    user_save_work_history($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Work history updated.'];
    header('Location: /pages/account/profile.php#work'); exit;
}

// ── save education ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_education') {
    $rows = [];
    foreach (($_POST['edu_title'] ?? []) as $i => $t) {
        $rows[] = ['title' => $t, 'institution' => $_POST['edu_sub'][$i] ?? ''];
    }
    user_save_education($uid, $rows);
    $_SESSION['pf_flash'] = ['ok', 'Education updated.'];
    header('Location: /pages/account/profile.php#education'); exit;
}

// ── save performance highlights ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_performance') {
    user_save_performance($uid, [
        'jobs_completed'    => $_POST['perf_jobs']     ?? '',
        'on_time_pct'       => $_POST['perf_ontime']   ?? '',
        'repeat_pct'        => $_POST['perf_repeat']   ?? '',
        'response_time'     => $_POST['perf_response'] ?? '',
        'availability_note' => $_POST['perf_avail']    ?? '',
    ]);
    $_SESSION['pf_flash'] = ['ok', 'Performance highlights updated.'];
    header('Location: /pages/account/profile.php'); exit;
}

// ── save core profile details (name · headline · phone · bio) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_details') {
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    if ($name !== '') db_stmt("UPDATE users SET name=?, phone=? WHERE id=?", [$name, ($phone ?: null), $uid]);
    else              db_stmt("UPDATE users SET phone=? WHERE id=?", [($phone ?: null), $uid]);
    db_stmt("INSERT IGNORE INTO user_professions (user_id) VALUES (?)", [$uid]);
    db_stmt("UPDATE user_professions SET title=?, bio=? WHERE user_id=?", [($headline ?: null), ($bio ?: null), $uid]);
    user_reload($uid);            // refresh the session snapshot (name shows in the navbar)
    member_provider_sync($uid);   // propagate name / headline / bio to the public listing
    $_SESSION['pf_flash'] = ['ok', 'Profile details saved.'];
    header('Location: /pages/account/profile.php#editSection'); exit;
}
$pf_flash = $_SESSION['pf_flash'] ?? null; unset($_SESSION['pf_flash']);

$first = explode(' ', $u['name'])[0];
$me = db_one("SELECT u.*, r.name AS region_name FROM users u LEFT JOIN regions r ON r.id = u.region_id WHERE u.id = ?", [(int)$u['id']]);
$employment = user_employment((int)$u['id']);
$prof = user_profession($uid);

// ── Identity & reputation — all from the database ──
$headline   = $prof['title'] ?: 'Construction Professional';
$location   = $me['region_name'] ?: 'Kenya';
$memberSince= $me['created_at'] ? date('Y', strtotime($me['created_at'])) : date('Y');
$revStats   = user_review_stats($uid);
$reviews    = (int) $revStats['count'];
$rating     = $reviews ? number_format($revStats['avg'], 1) : 'New';
$avatar     = !empty($prof['photo_url']) ? $prof['photo_url'] : user_avatar($u, 240);
$cover      = $prof['cover_url'] ?? '';

// ── Performance stat strip — rating is live; the rest are owner-stated (editable) ──
$stats = [['num' => $rating, 'small' => ($reviews ? '★' : ''), 'lbl' => 'Overall rating']];
if (($prof['jobs_completed']    ?? '') !== '') $stats[] = ['num' => $prof['jobs_completed'],    'small' => '',  'lbl' => 'Jobs completed'];
if (($prof['on_time_pct']       ?? '') !== '') $stats[] = ['num' => $prof['on_time_pct'],       'small' => '%', 'lbl' => 'On-time delivery'];
if (($prof['repeat_pct']        ?? '') !== '') $stats[] = ['num' => $prof['repeat_pct'],        'small' => '%', 'lbl' => 'Repeat clients'];
if (($prof['response_time']     ?? '') !== '') $stats[] = ['num' => $prof['response_time'],     'small' => '',  'lbl' => 'Avg response'];
if (($prof['years_in_business'] ?? '') !== '') $stats[] = ['num' => $prof['years_in_business'], 'small' => '',  'lbl' => 'In business'];

$services       = user_services($uid);          // editable à-la-carte services
$skills         = user_skills($uid);            // real specialisations (user_skills)
$folio          = user_portfolio($uid);         // editable portfolio
$certifications = user_certifications($uid);    // editable certs & licences
$reviewList     = user_reviews($uid, 8);        // client-created reviews
$packages       = user_packages($uid);          // editable service packages
$workHistory    = user_work_history($uid);      // editable work history
$education       = user_education($uid);          // editable education & training
$languages      = user_languages($uid);         // editable languages
$completion     = profile_completion($uid, $prof);

$ratingBreak = [];
foreach ([5,4,3,2,1] as $st) {
  $cnt = (int) $revStats['breakdown'][$st];
  $ratingBreak[] = [$st, $cnt, $reviews ? round($cnt * 100 / $reviews) : 0];
}
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="container" style="padding:24px 0 56px;">
  <div class="bf-pf-wrap">

    <!-- profile action bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <div class="bf-section-eyebrow" style="margin-bottom:2px;"><i class="bi bi-person-badge" style="color:#c0392b;"></i> My profile</div>
        <div style="font-size:12.5px;color:var(--ink-3);">Your public profile, packages, portfolio &amp; account — all editable here.</div>
      </div>
      <div class="d-flex gap-2">
        <a href="/pages/marketplace/professional.php?u=<?= urlencode($u['public_id']) ?>" class="bf-btn-outline" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;"><i class="bi bi-eye"></i> Preview public</a>
        <button type="submit" form="profileForm" class="bf-btn-accent" style="display:inline-flex;align-items:center;gap:6px;"><i class="bi bi-check-lg"></i> Save changes</button>
      </div>
    </div>

      <?php if ($pf_flash): ?>
      <div class="d-flex align-items-center gap-2 mb-3" data-ms="5000" style="background:<?= $pf_flash[0]==='ok'?'#f0fdf4':'#fef2f2' ?>;border:1px solid <?= $pf_flash[0]==='ok'?'#bbf7d0':'#fecaca' ?>;color:<?= $pf_flash[0]==='ok'?'#166534':'#b91c1c' ?>;border-radius:12px;padding:11px 15px;font-size:13px;">
        <i class="bi bi-<?= $pf_flash[0]==='ok'?'check-circle-fill':'exclamation-triangle-fill' ?>"></i>
        <div><?= htmlspecialchars($pf_flash[1]) ?></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($_GET['welcome'])): ?>
      <div class="d-flex align-items-center gap-2 mb-3" data-ms="6000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:12px 16px;font-size:13px;">
        <i class="bi bi-check-circle-fill"></i>
        <div>Welcome to bildfie, <?= htmlspecialchars($first) ?>! Build out your profile below — a strong profile wins up to <strong>5× more work</strong>.</div>
      </div>
      <?php endif; ?>

      <!-- ═══ Identity hero ═══ -->
      <form id="mediaForm" method="post" enctype="multipart/form-data" class="d-none">
        <input type="hidden" name="action" value="save_media">
        <input type="file" id="coverInput" name="cover" accept="image/jpeg,image/png,image/webp,image/gif">
        <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
      </form>
      <div class="bf-pf-hero">
        <div class="bf-pf-cover" style="<?= $cover ? "background-image:url('" . htmlspecialchars($cover, ENT_QUOTES) . "');" : "background:linear-gradient(120deg,#0d1f36,#1e3a5f);" ?>">
          <button type="button" class="bf-pf-cover-edit" onclick="document.getElementById('coverInput').click()"><i class="bi bi-camera"></i> Edit cover</button>
        </div>
        <div class="bf-pf-headrow">
          <div class="bf-pf-avatar-wrap">
            <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($u['name']) ?>" class="bf-pf-avatar">
            <span class="bf-pf-avatar-cam" onclick="document.getElementById('photoInput').click()" style="cursor:pointer;" title="Change profile photo"><i class="bi bi-camera-fill"></i></span>
          </div>

          <div class="bf-pf-id">
            <div class="bf-pf-name"><?= htmlspecialchars($u['name']) ?></div>
            <div class="bf-pf-headline"><?= $headline ?></div>
            <?php
              $availMap = ['available'=>['#16a34a','Available now'],'busy'=>['#b45309','Busy'],'arrangement'=>['#1e3a5f','By arrangement'],'unavailable'=>['#6b6b6b','Unavailable']];
              [$avCol,$avTxt] = $availMap[$prof['availability'] ?? 'available'] ?? ['#16a34a','Available now'];
            ?>
            <div class="bf-pf-meta">
              <span><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars($location) ?></span>
              <span><i class="bi bi-calendar3"></i>Member since <?= $memberSince ?></span>
              <?php if (!empty($prof['response_time'])): ?><span><i class="bi bi-clock"></i>Replies in <?= htmlspecialchars($prof['response_time']) ?></span><?php endif; ?>
              <span style="color:<?= $avCol ?>;font-weight:700;"><i class="bi bi-circle-fill" style="font-size:8px;color:<?= $avCol ?>;"></i><?= $avTxt ?></span>
            </div>
            <div class="bf-pf-verify-pills">
              <?php if ($reviews > 0 && $revStats['avg'] >= 4.8): ?><span class="bf-pf-pill gold"><i class="bi bi-award-fill"></i> Top Rated</span><?php endif; ?>
              <?php if (!empty($me['email_verified_at'])): ?><span class="bf-pf-pill green"><i class="bi bi-patch-check-fill"></i> Email Verified</span><?php endif; ?>
              <?php if (!empty($prof['nca_number'])): ?><span class="bf-pf-pill navy"><i class="bi bi-file-earmark-check-fill"></i> NCA Registered<?= !empty($prof['nca_category']) ? ' · '.htmlspecialchars($prof['nca_category']) : '' ?></span><?php endif; ?>
            </div>
          </div>

          <div class="bf-pf-head-cta">
            <div class="bf-pf-rating-big">
              <b><?= $rating ?></b>
              <div>
                <div class="stars"><?php for($i=0;$i<5;$i++):?><i class="bi <?= $i<round($revStats['avg'])?'bi-star-fill':'bi-star' ?>"></i><?php endfor;?></div>
                <div style="font-size:11px;color:var(--ink-4);"><?= $reviews ? $reviews.' reviews' : 'No reviews yet' ?></div>
              </div>
            </div>
            <a href="/pages/marketplace/professional.php?u=<?= urlencode($u['public_id']) ?>" style="font-size:11.5px;font-weight:700;color:#1e3a5f;text-decoration:none;text-align:center;border:1px solid #d6e2ee;background:#eaf0f6;padding:8px 16px;border-radius:9px;"><i class="bi bi-share me-1"></i>Share profile</a>
          </div>
        </div>
      </div>

      <!-- ═══ Reputation stat strip (rating live, performance owner-stated) ═══ -->
      <div class="bf-pf-stats" style="position:relative;">
        <button type="button" class="bf-pf-stat-edit" data-bs-toggle="modal" data-bs-target="#perfModal" title="Edit performance highlights"><i class="bi bi-pencil"></i></button>
        <?php foreach ($stats as $s): ?>
        <div class="bf-pf-stat">
          <div class="bf-pf-stat-num"><?= htmlspecialchars((string)$s['num']) ?><small><?= $s['small'] ?></small></div>
          <div class="bf-pf-stat-lbl"><?= $s['lbl'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3" style="margin-top:2px;">

        <!-- ═══ LEFT (main) ═══ -->
        <div class="col-lg-8">

          <!-- About -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-person-lines-fill"></i> About</div>
              <a href="/pages/account/settings.php#professional" class="bf-pf-card-edit"><i class="bi bi-pencil"></i> Edit</a>
            </div>
            <div class="bf-pf-card-b">
              <p style="font-size:13px;color:var(--ink-2);line-height:1.7;margin:0;">
                NCA-registered civil contractor with <strong>12+ years</strong> delivering commercial, residential and institutional builds across Kenya. I lead site teams from groundwork to handover with a focus on programme certainty, watertight cost control and a finish my clients are proud of. Whether you need a full turnkey contractor or specialist structural works, I bring a verified track record and a network of trusted subcontractors.
              </p>
            </div>
          </div>

          <!-- Experience (affirmed employment) -->
          <?php if (!empty($employment)): ?>
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-buildings"></i> Experience</div>
              <a href="/pages/companies/index.php" class="bf-pf-card-edit"><i class="bi bi-search"></i> Find company</a>
            </div>
            <div class="bf-pf-card-b">
              <?php foreach ($employment as $job):
                $logo = !empty($job['logo_url']) ? $job['logo_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($job['company_name']) . '&background=1e3a5f&color=fff&size=80&bold=true';
              ?>
              <div style="display:flex;gap:13px;padding:11px 0;border-top:1px solid var(--line-2);">
                <img src="<?= htmlspecialchars($logo, ENT_QUOTES) ?>" style="width:46px;height:46px;border-radius:11px;object-fit:cover;border:1px solid var(--line);background:#fff;flex-shrink:0;">
                <div style="flex:1;min-width:0;">
                  <div style="font-size:13.5px;font-weight:700;color:var(--ink);"><?= htmlspecialchars($job['position'] ?: 'Team member') ?></div>
                  <div style="font-size:12px;color:var(--ink-2);">
                    <a href="/pages/companies/view.php?slug=<?= urlencode($job['slug']) ?>" style="color:#1e3a5f;text-decoration:none;font-weight:600;"><?= htmlspecialchars($job['company_name']) ?></a><?php if ((int)$job['company_verified']): ?> <i class="bi bi-patch-check-fill" style="color:#1e3a5f;font-size:11px;"></i><?php endif; ?><?php if (!empty($job['employment_type'])): ?> <span style="color:var(--ink-4);">· <?= htmlspecialchars($job['employment_type']) ?></span><?php endif; ?>
                  </div>
                  <div style="margin-top:5px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:700;background:#f0fdf4;color:#166534;padding:3px 8px;border-radius:5px;"><i class="bi bi-patch-check-fill" style="font-size:9px;"></i> Affirmed by <?= htmlspecialchars($job['company_name']) ?></span>
                    <?php if ((int)$job['is_current']): ?><span style="font-size:9.5px;color:var(--ink-4);margin-left:6px;">Current</span><?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Service packages (editable, DB-backed) -->
          <div class="bf-pf-card" id="packages">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-box-seam"></i> Service packages</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#pkgModal"><i class="bi bi-pencil"></i> <?= $packages ? 'Edit packages' : 'Add packages' ?></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$packages): ?>
                <div class="bf-pf-empty">
                  <i class="bi bi-box-seam"></i>
                  <div class="t">No packages yet</div>
                  <p>Bundle your services into clear, fixed-scope tiers. Clients are far more likely to hire when they can see exactly what they get — and what it costs — at a glance.</p>
                  <button type="button" class="bf-btn-accent" data-bs-toggle="modal" data-bs-target="#pkgModal"><i class="bi bi-plus-lg me-1"></i>Add your first package</button>
                </div>
              <?php else: ?>
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
                    <?php if ($pk['delivery'] || $pk['revisions']): ?>
                    <div class="bf-pkg-meta">
                      <?php if ($pk['delivery']): ?><span><i class="bi bi-clock"></i><?= htmlspecialchars($pk['delivery']) ?></span><?php endif; ?>
                      <?php if ($pk['revisions']): ?><span><i class="bi bi-arrow-repeat"></i><?= htmlspecialchars($pk['revisions']) ?></span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($feats as $f): ?>
                    <div class="bf-pkg-feat"><i class="bi bi-check-lg"></i><?= htmlspecialchars($f) ?></div>
                    <?php endforeach; ?>
                    <a href="/pages/marketplace/professional.php?u=<?= urlencode($u['public_id']) ?>" class="bf-pkg-btn <?= $pk['is_featured']?'solid':'' ?>">Request <?= htmlspecialchars($pk['tier'] ?: $pk['title']) ?></a>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Other services & rates (editable, DB-backed) -->
          <div class="bf-pf-card" id="services">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-briefcase-fill"></i> Other services &amp; rates</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#svcModal"><i class="bi bi-<?= $services ? 'pencil' : 'plus-lg' ?>"></i> <?= $services ? 'Edit' : 'Add service' ?></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$services): ?>
                <div class="bf-pf-mini-empty" style="padding:18px 4px;">No individual services yet — <a href="#" data-bs-toggle="modal" data-bs-target="#svcModal">add à-la-carte services &amp; rates</a> to sit alongside your packages.</div>
              <?php else: foreach ($services as $sv): ?>
              <div class="bf-pf-service">
                <div class="bf-pf-service-ic"><i class="bi <?= htmlspecialchars($sv['icon'] ?: 'bi-wrench-adjustable') ?>"></i></div>
                <div style="flex:1;min-width:0;">
                  <div class="bf-pf-service-name"><?= htmlspecialchars($sv['name']) ?></div>
                  <?php if ($sv['description']): ?><div class="bf-pf-service-desc"><?= htmlspecialchars($sv['description']) ?></div><?php endif; ?>
                </div>
                <?php if ($sv['rate']): ?><div class="bf-pf-service-rate"><?= htmlspecialchars($sv['rate']) ?><small><?= htmlspecialchars((string)$sv['rate_unit']) ?></small></div><?php endif; ?>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Portfolio (editable, DB-backed) -->
          <div class="bf-pf-card" id="portfolio">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-images"></i> Portfolio &amp; past projects</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#folioModal"><i class="bi bi-<?= $folio ? 'pencil' : 'plus-lg' ?>"></i> <?= $folio ? 'Edit' : 'Add project' ?></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$folio): ?>
                <div class="bf-pf-empty">
                  <i class="bi bi-images"></i>
                  <div class="t">No projects yet</div>
                  <p>Show off your best completed work — photos of finished projects are the single most persuasive thing a client sees on your profile.</p>
                  <button type="button" class="bf-btn-accent" data-bs-toggle="modal" data-bs-target="#folioModal"><i class="bi bi-plus-lg me-1"></i>Add a project</button>
                </div>
              <?php else: ?>
              <div class="bf-pf-folio-grid">
                <?php foreach ($folio as $pj):
                  $img = $pj['image_url'] ?: ('https://ui-avatars.com/api/?name=' . urlencode($pj['title']) . '&background=0d1f36&color=fff&size=400&bold=true');
                ?>
                <div class="bf-pf-folio">
                  <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($pj['title']) ?>">
                  <div class="bf-pf-folio-ov">
                    <?php if ($pj['category']): ?><div class="bf-pf-folio-cat"><?= htmlspecialchars($pj['category']) ?></div><?php endif; ?>
                    <div class="bf-pf-folio-name"><?= htmlspecialchars($pj['title']) ?></div>
                    <?php if ($pj['year']): ?><div class="bf-pf-folio-yr">Completed <?= htmlspecialchars($pj['year']) ?></div><?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Skills (real, DB-backed) -->
          <div class="bf-pf-card" id="skills">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-stars"></i> Skills &amp; specialisations</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#skillModal"><i class="bi bi-pencil"></i> Edit</button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$skills): ?>
                <div class="bf-pf-mini-empty">No skills yet — <a href="#" data-bs-toggle="modal" data-bs-target="#skillModal">add your specialisations</a> so clients find you in search.</div>
              <?php else: ?>
              <div class="bf-pf-chips">
                <?php foreach ($skills as $i => $sk): ?><span class="bf-pf-chip <?= $i < 4 ? 'core' : '' ?>"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Work history (editable, DB-backed) -->
          <div class="bf-pf-card" id="work">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-clock-history"></i> Work history</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#workModal"><i class="bi bi-<?= $workHistory ? 'pencil' : 'plus-lg' ?>"></i> <?= $workHistory ? 'Edit' : 'Add' ?></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$workHistory): ?>
                <div class="bf-pf-mini-empty" style="padding:8px 4px;">No work history yet — <a href="#" data-bs-toggle="modal" data-bs-target="#workModal">add the roles &amp; projects you've delivered</a>.</div>
              <?php else: foreach ($workHistory as $w): ?>
              <div class="bf-work">
                <div class="bf-work-ic"><i class="bi bi-building-fill"></i></div>
                <div style="flex:1;min-width:0;">
                  <div class="bf-work-role"><?= htmlspecialchars($w['role']) ?></div>
                  <?php if ($w['organization']): ?><div class="bf-work-org"><?= htmlspecialchars($w['organization']) ?></div><?php endif; ?>
                  <?php if ($w['period']): ?><div class="bf-work-when"><?= htmlspecialchars($w['period']) ?></div><?php endif; ?>
                  <?php if ($w['description']): ?><div class="bf-work-desc"><?= htmlspecialchars($w['description']) ?></div><?php endif; ?>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Education & training (editable, DB-backed) -->
          <div class="bf-pf-card" id="education">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-mortarboard-fill"></i> Education &amp; training</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#eduModal"><i class="bi bi-<?= $education ? 'pencil' : 'plus-lg' ?>"></i> <?= $education ? 'Edit' : 'Add' ?></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$education): ?>
                <div class="bf-pf-mini-empty" style="padding:8px 4px;">No education added — <a href="#" data-bs-toggle="modal" data-bs-target="#eduModal">add your qualifications &amp; training</a>.</div>
              <?php else: foreach ($education as $ed): ?>
              <div class="bf-pf-cert">
                <div class="bf-pf-cert-ic" style="background:#eaf0f6;color:#1e3a5f;"><i class="bi bi-mortarboard-fill"></i></div>
                <div style="flex:1;min-width:0;">
                  <div class="bf-pf-cert-name"><?= htmlspecialchars($ed['title']) ?></div>
                  <?php if ($ed['institution']): ?><div class="bf-pf-cert-sub"><?= htmlspecialchars($ed['institution']) ?></div><?php endif; ?>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Reviews -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-chat-quote-fill"></i> Client reviews</div>
              <span style="font-size:11.5px;color:var(--ink-4);"><i class="bi bi-star-fill" style="color:#f59e0b;"></i> <strong style="color:var(--ink);"><?= $rating ?></strong> · <?= $reviews ?> reviews</span>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$reviews): ?>
                <div class="bf-pf-empty">
                  <i class="bi bi-chat-quote"></i>
                  <div class="t">No reviews yet</div>
                  <p>Reviews are written by your clients after a completed job — you can't add them yourself. They'll appear here automatically and build the trust that wins you more work.</p>
                </div>
              <?php else: ?>
              <!-- Rating breakdown (live from client reviews) -->
              <div class="bf-rate-break">
                <div class="bf-rate-big">
                  <b><?= $rating ?></b>
                  <div class="stars"><?php for($i=0;$i<5;$i++):?><i class="bi <?= $i<round($revStats['avg'])?'bi-star-fill':'bi-star' ?>"></i><?php endfor;?></div>
                  <div class="cnt"><?= $reviews ?> review<?= $reviews==1?'':'s' ?></div>
                </div>
                <div class="bf-rate-bars">
                  <?php foreach ($ratingBreak as [$st,$cnt,$pct]): ?>
                  <div class="bf-rate-row">
                    <span><?= $st ?> <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i></span>
                    <span class="bf-rate-track"><span class="bf-rate-fill" style="width:<?= $pct ?>%;"></span></span>
                    <span><?= $cnt ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php foreach ($reviewList as $rv):
                $rname  = $rv['reviewer_name']; $rstars = (int) $rv['rating'];
                $rav    = 'https://ui-avatars.com/api/?name=' . urlencode($rname) . '&background=eaf0f6&color=1e3a5f&size=80&bold=true';
              ?>
              <div class="bf-pf-review">
                <div class="bf-pf-review-head">
                  <img src="<?= htmlspecialchars($rav, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($rname) ?>" class="bf-pf-review-av">
                  <div>
                    <div class="bf-pf-review-name"><?= htmlspecialchars($rname) ?></div>
                    <div class="bf-pf-review-meta"><?= $rv['created_at'] ? date('M j, Y', strtotime($rv['created_at'])) : '' ?></div>
                  </div>
                  <div class="bf-pf-review-stars">
                    <?php for ($i=0;$i<5;$i++): ?><i class="bi <?= $i<$rstars?'bi-star-fill':'bi-star' ?>"></i><?php endfor; ?>
                  </div>
                </div>
                <?php if (!empty($rv['body'])): ?><div class="bf-pf-review-text">"<?= htmlspecialchars($rv['body']) ?>"</div><?php endif; ?>
                <?php if (!empty($rv['project'])): ?><div class="bf-pf-review-proj"><i class="bi bi-bookmark-fill" style="color:#1e3a5f;font-size:10px;"></i> Project: <b><?= htmlspecialchars($rv['project']) ?></b></div><?php endif; ?>
              </div>
              <?php endforeach; ?>
              <?php if ($reviews > count($reviewList)): ?>
              <div style="text-align:center;padding-top:16px;border-top:1px solid var(--line-2);margin-top:4px;">
                <a href="/pages/marketplace/professional.php?u=<?= urlencode($u['public_id']) ?>" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">Read all <?= $reviews ?> reviews →</a>
              </div>
              <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- ═══ RIGHT (sidebar) ═══ -->
        <div class="col-lg-4">

          <!-- Profile strength (live — based on what you've actually filled) -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-graph-up-arrow"></i> Profile strength</div>
              <span style="font-size:15px;font-weight:900;color:#1e3a5f;"><?= $completion['pct'] ?>%</span>
            </div>
            <div class="bf-pf-card-b">
              <div class="bf-pf-meter-track mb-2"><div class="bf-pf-meter-fill" style="width:<?= $completion['pct'] ?>%;"></div></div>
              <p style="font-size:11.5px;color:var(--ink-3);margin:0 0 10px;line-height:1.5;">A complete profile ranks higher in search and earns client trust.</p>
              <?php foreach ($completion['items'] as [$l,$done]): ?>
              <div class="bf-pf-check <?= $done?'done':'todo' ?>">
                <i class="bi <?= $done?'bi-check-circle-fill':'bi-circle' ?>"></i><span><?= $l ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Verification (real signals only) -->
          <?php
            $emailOk  = !empty($me['email_verified_at']);
            $ncaVal   = trim((string) ($prof['nca_number'] ?? ''));
            $phoneVal = trim((string) (($prof['public_phone'] ?? '') ?: ($me['phone'] ?? '')));
            $verItems = [
              ['bi-envelope-check-fill',    'Email address',    $emailOk,        $emailOk        ? 'VERIFIED' : 'PENDING'],
              ['bi-file-earmark-text-fill', 'NCA registration', $ncaVal !== '',  $ncaVal !== ''  ? 'ON FILE'  : 'ADD'],
              ['bi-telephone-fill',         'Phone number',     $phoneVal !== '',$phoneVal !== ''? 'ADDED'    : 'ADD'],
            ];
          ?>
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-shield-lock-fill"></i> Verification</div>
              <?php if ($emailOk): ?><span class="bf-pf-pill green" style="margin:0;"><i class="bi bi-patch-check-fill"></i> Trusted</span><?php endif; ?>
            </div>
            <div class="bf-pf-card-b">
              <?php foreach ($verItems as [$ic,$name,$ok,$tag]):
                $col = $ok ? '#166534' : '#b45309'; $bg = $ok ? '#f0fdf4' : '#fffbeb';
              ?>
              <div class="bf-pf-verify-item">
                <div class="bf-pf-verify-ic" style="background:<?= $bg ?>;color:<?= $col ?>;"><i class="bi <?= $ic ?>"></i></div>
                <div class="bf-pf-verify-name"><?= $name ?></div>
                <span class="bf-pf-verify-tag" style="color:<?= $col ?>;background:<?= $bg ?>;"><?= $tag ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Certifications (editable, DB-backed) -->
          <div class="bf-pf-card" id="certifications">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-patch-check-fill"></i> Certifications</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#certModal"><i class="bi bi-<?= $certifications ? 'pencil' : 'plus-lg' ?>"></i></button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$certifications): ?>
                <div class="bf-pf-mini-empty" style="padding:16px;">No certifications yet — <a href="#" data-bs-toggle="modal" data-bs-target="#certModal">add your licences &amp; certs</a> to build client trust.</div>
              <?php else: foreach ($certifications as $ct):
                $stU = strtoupper(trim((string) $ct['status']));
                $stMap = ['VERIFIED' => ['#166534','#f0fdf4'], 'CURRENT' => ['#1e3a5f','#eaf0f6'], 'PENDING' => ['#b45309','#fffbeb']];
                [$stCol,$stBg] = $stMap[$stU] ?? ['#6b6b6b','#f4f4f2'];
              ?>
              <div class="bf-pf-cert">
                <div class="bf-pf-cert-ic" style="background:#eaf0f6;color:#1e3a5f;"><i class="bi bi-patch-check-fill"></i></div>
                <div style="flex:1;min-width:0;">
                  <div class="bf-pf-cert-name"><?= htmlspecialchars($ct['name']) ?></div>
                  <?php if ($ct['issuer']): ?><div class="bf-pf-cert-sub"><?= htmlspecialchars($ct['issuer']) ?></div><?php endif; ?>
                </div>
                <?php if ($stU): ?><span class="bf-pf-cert-stat" style="color:<?= $stCol ?>;background:<?= $stBg ?>;"><?= htmlspecialchars($stU) ?></span><?php endif; ?>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Languages (editable, DB-backed) -->
          <div class="bf-pf-card" id="languages">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-translate"></i> Languages</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#langModal"><i class="bi bi-pencil"></i></button>
            </div>
            <div class="bf-pf-card-b" style="padding-top:14px;padding-bottom:14px;">
              <?php if (!$languages): ?>
                <div class="bf-pf-mini-empty">No languages added — <a href="#" data-bs-toggle="modal" data-bs-target="#langModal">add the languages you work in</a>.</div>
              <?php else: foreach ($languages as $lg): ?>
              <div class="bf-lang-row"><span><?= htmlspecialchars($lg['language']) ?></span><span class="bf-lang-lvl"><?= htmlspecialchars((string)$lg['level']) ?></span></div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Business info (editable, DB-backed) -->
          <?php
            $bizRows = array_values(array_filter([
              ['bi-calendar-check','Years in business', $prof['years_in_business'] ?? ''],
              ['bi-people',        'Team size',         $prof['team_size'] ?? ''],
              ['bi-clock',         'Working hours',     $prof['working_hours'] ?? ''],
              ['bi-geo-alt-fill',  'Serving',           $prof['serving_area'] ?? ''],
            ], fn($r) => trim((string) $r[2]) !== ''));
          ?>
          <div class="bf-pf-card" id="business">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-shop"></i> Business info</div>
              <button type="button" class="bf-pf-card-edit" data-bs-toggle="modal" data-bs-target="#bizModal"><i class="bi bi-pencil"></i></button>
            </div>
            <div class="bf-pf-card-b" style="padding-top:8px;padding-bottom:8px;">
              <?php if (!$bizRows): ?>
                <div class="bf-pf-mini-empty" style="padding:10px 0;">No business details yet — <a href="#" data-bs-toggle="modal" data-bs-target="#bizModal">add them</a> so clients know who they're hiring.</div>
              <?php else: foreach ($bizRows as [$ic,$k,$v]): ?>
              <div class="bf-pf-contact" style="justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:11px;"><i class="bi <?= $ic ?>"></i><?= $k ?></span>
                <span style="font-weight:700;color:var(--ink);"><?= htmlspecialchars((string) $v) ?></span>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <!-- Location & contact (on-platform only) -->
          <div class="bf-pf-card">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-geo-alt-fill"></i> Location</div>
            </div>
            <div class="bf-pf-card-b" style="padding-top:14px;padding-bottom:14px;">
              <div class="bf-pf-contact"><i class="bi bi-geo-alt"></i><?= htmlspecialchars($location) ?></div>
              <div class="bf-pf-contact"><i class="bi bi-calendar3"></i>Member since <?= $memberSince ?></div>
              <div style="font-size:11px;color:var(--ink-4);line-height:1.5;margin-top:8px;padding-top:10px;border-top:1px solid var(--line-2);">
                <i class="bi bi-shield-lock-fill" style="color:#166534;"></i> For your protection, all messaging, quotes and payments stay on bildfie — no phone numbers or emails are shared.
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ═══ Edit details (collapsible form area) ═══ -->
      <div class="bf-pf-card" id="editSection" style="margin-top:6px;">
        <div class="bf-pf-card-h">
          <div class="bf-pf-card-t"><i class="bi bi-sliders"></i> Edit profile details</div>
        </div>
        <div class="bf-pf-card-b">
          <form id="profileForm" method="post">
            <input type="hidden" name="action" value="save_details">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Full name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($u['name']) ?>" style="font-size:13px;border-color:var(--line);border-radius:9px;padding:10px 13px;">
              </div>
              <div class="col-md-6">
                <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Professional headline</label>
                <input type="text" name="headline" class="form-control" value="<?= htmlspecialchars($prof['title'] ?? '') ?>" placeholder="e.g. Civil Contractor &amp; Project Manager" style="font-size:13px;border-color:var(--line);border-radius:9px;padding:10px 13px;">
              </div>
              <div class="col-md-6">
                <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($me['phone'] ?? '') ?>" placeholder="e.g. +254 712 345 678" style="font-size:13px;border-color:var(--line);border-radius:9px;padding:10px 13px;">
              </div>
              <div class="col-md-6">
                <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Location</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($location) ?>" disabled style="font-size:13px;border-color:var(--line);border-radius:9px;padding:10px 13px;background:#f7f7f5;color:var(--ink-3);">
                <div style="font-size:10.5px;color:var(--ink-4);margin-top:4px;">Set from your region in <a href="/pages/account/settings.php#account" style="color:#1e3a5f;">Settings</a>.</div>
              </div>
            </div>
            <div class="mb-3">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Professional bio</label>
              <textarea name="bio" class="form-control" rows="4" placeholder="Tell clients about your experience, specialisms and what sets you apart — this is your public 'About'." style="font-size:13px;border-color:var(--line);border-radius:9px;padding:10px 13px;resize:vertical;"><?= htmlspecialchars($prof['bio'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="bf-login-btn" style="width:auto;padding:11px 28px;"><i class="bi bi-check-lg"></i>Save Changes</button>
          </form>
        </div>
      </div>

  </div>
</div>
<script>
// auto-submit the photo/cover picker the moment a file is chosen
['photoInput','coverInput'].forEach(function(id){
  var el = document.getElementById(id);
  if (el) el.addEventListener('change', function(){ if (el.files && el.files.length) document.getElementById('mediaForm').submit(); });
});
</script>
<!-- ═══ Edit packages modal ═══ -->
<div class="modal fade" id="pkgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal" id="pkgForm">
      <input type="hidden" name="action" value="save_packages">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-box-seam me-2" style="color:#c0392b;"></i>Service packages</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="bf-modal-hint">Offer up to three clear tiers. Tick <b>Most popular</b> on the one you want to highlight. Blank packages are skipped when you save.</p>
        <div id="pkgRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="pkgAdd"><i class="bi bi-plus-lg me-1"></i>Add a package</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save packages</button>
      </div>
    </form>
  </div>
</div>
<template id="pkgTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h">
      <span class="bf-edit-row-n">Package</span>
      <label class="bf-edit-pop"><input type="radio" name="pkg_featured" value=""> Most popular</label>
      <button type="button" class="bf-edit-del" title="Remove package"><i class="bi bi-trash"></i></button>
    </div>
    <div class="row g-2">
      <div class="col-4"><input name="pkg_tier[]" class="form-control form-control-sm" placeholder="Tier — e.g. Basic"></div>
      <div class="col-8"><input name="pkg_title[]" class="form-control form-control-sm" placeholder="Package title — e.g. Structural works"></div>
      <div class="col-7"><input name="pkg_price[]" class="form-control form-control-sm" placeholder="Price — e.g. from KES 850K"></div>
      <div class="col-5"><input name="pkg_unit[]" class="form-control form-control-sm" placeholder="Unit — e.g. /project"></div>
      <div class="col-12"><textarea name="pkg_desc[]" rows="2" class="form-control form-control-sm" placeholder="One line on what this package delivers"></textarea></div>
      <div class="col-6"><input name="pkg_delivery[]" class="form-control form-control-sm" placeholder="Delivery — e.g. 8-week delivery"></div>
      <div class="col-6"><input name="pkg_revisions[]" class="form-control form-control-sm" placeholder="Revisions — e.g. Unlimited"></div>
      <div class="col-12"><textarea name="pkg_features[]" rows="4" class="form-control form-control-sm" placeholder="What's included — one item per line:&#10;Detailed BOQ &amp; costing&#10;Foundation + RC frame&#10;Weekly progress reports"></textarea></div>
    </div>
  </div>
</template>
<script>
(function(){
  var data = <?= json_encode(array_map(function($p){ return [
    'tier'=>$p['tier'],'title'=>$p['title'],'price'=>$p['price'],'unit'=>$p['price_unit'],
    'desc'=>$p['description'],'delivery'=>$p['delivery'],'revisions'=>$p['revisions'],
    'features'=>$p['features'],'featured'=>(int)$p['is_featured'],
  ]; }, $packages), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  var wrap=document.getElementById('pkgRows'), tpl=document.getElementById('pkgTpl'), addBtn=document.getElementById('pkgAdd');
  if(!wrap||!tpl) return;
  function rows(){ return wrap.querySelectorAll('.bf-edit-row'); }
  function renumber(){
    rows().forEach(function(row,i){
      row.querySelector('.bf-edit-row-n').textContent='Package '+(i+1);
      row.querySelector('input[name="pkg_featured"]').value=String(i);
    });
    addBtn.style.display = rows().length>=3 ? 'none' : '';
  }
  function set(row,sel,val){ var el=row.querySelector(sel); if(el) el.value=val||''; }
  function addRow(d){
    if(!d && rows().length>=3) return;
    var node=tpl.content.cloneNode(true), row=node.querySelector('.bf-edit-row');
    if(d){
      set(row,'[name="pkg_tier[]"]',d.tier); set(row,'[name="pkg_title[]"]',d.title);
      set(row,'[name="pkg_price[]"]',d.price); set(row,'[name="pkg_unit[]"]',d.unit);
      set(row,'[name="pkg_desc[]"]',d.desc); set(row,'[name="pkg_delivery[]"]',d.delivery);
      set(row,'[name="pkg_revisions[]"]',d.revisions); set(row,'[name="pkg_features[]"]',d.features);
      if(d.featured) row.querySelector('input[name="pkg_featured"]').checked=true;
    }
    row.querySelector('.bf-edit-del').addEventListener('click',function(){ row.remove(); renumber(); });
    wrap.appendChild(node);
    renumber();
  }
  if(data.length){ data.forEach(addRow); } else { addRow(); }
  addBtn.addEventListener('click',function(){ addRow(); });
})();
</script>

<!-- ═══ Edit portfolio modal ═══ -->
<div class="modal fade" id="folioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" enctype="multipart/form-data" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_portfolio">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-images me-2" style="color:#c0392b;"></i>Portfolio &amp; past projects</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">Add your completed projects. Paste an image link for each — direct photo uploads are coming next. Blank rows are skipped.</p>
        <div id="folioRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="folioAdd"><i class="bi bi-plus-lg me-1"></i>Add a project</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save portfolio</button></div>
    </form>
  </div>
</div>
<template id="folioTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Project">Project</span><button type="button" class="bf-edit-del" title="Remove project"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-12"><input name="folio_title[]" class="form-control form-control-sm" placeholder="Project name — e.g. Westlands Office Block"></div>
      <div class="col-7"><input name="folio_cat[]" class="form-control form-control-sm" placeholder="Category — e.g. Commercial"></div>
      <div class="col-5"><input name="folio_year[]" class="form-control form-control-sm" placeholder="Year — e.g. 2024"></div>
      <div class="col-12">
        <div class="bf-folio-up">
          <img class="bf-folio-thumb" alt="" style="display:none;">
          <div style="flex:1;min-width:0;">
            <label class="bf-edit-lbl">Project photo</label>
            <input type="file" name="folio_file[]" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control form-control-sm">
            <input name="folio_img[]" class="form-control form-control-sm mt-1" placeholder="…or paste an image link (https://…)">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- ═══ Edit services modal ═══ -->
<div class="modal fade" id="svcModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_services">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-briefcase-fill me-2" style="color:#c0392b;"></i>Other services &amp; rates</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">Individual services clients can request à la carte, each with its own rate.</p>
        <div id="svcRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="svcAdd"><i class="bi bi-plus-lg me-1"></i>Add a service</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save services</button></div>
    </form>
  </div>
</div>
<template id="svcTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Service">Service</span><button type="button" class="bf-edit-del" title="Remove service"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-12"><input name="svc_name[]" class="form-control form-control-sm" placeholder="Service — e.g. Site Supervision"></div>
      <div class="col-12"><input name="svc_desc[]" class="form-control form-control-sm" placeholder="Short description"></div>
      <div class="col-7"><input name="svc_rate[]" class="form-control form-control-sm" placeholder="Rate — e.g. KES 6,000"></div>
      <div class="col-5"><input name="svc_unit[]" class="form-control form-control-sm" placeholder="Unit — e.g. /day"></div>
    </div>
  </div>
</template>

<!-- ═══ Edit languages modal ═══ -->
<div class="modal fade" id="langModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_languages">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-translate me-2" style="color:#c0392b;"></i>Languages</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">The languages you can work and communicate in.</p>
        <div id="langRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="langAdd"><i class="bi bi-plus-lg me-1"></i>Add a language</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<template id="langTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Language">Language</span><button type="button" class="bf-edit-del" title="Remove language"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-6"><input name="lang_name[]" class="form-control form-control-sm" placeholder="Language — e.g. English"></div>
      <div class="col-6"><input name="lang_level[]" class="form-control form-control-sm" placeholder="Level — e.g. Fluent"></div>
    </div>
  </div>
</template>

<!-- ═══ Edit certifications modal ═══ -->
<div class="modal fade" id="certModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_certifications">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-patch-check-fill me-2" style="color:#c0392b;"></i>Certifications &amp; licences</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">Your registrations, licences and certifications. Set a status badge so clients can see at a glance.</p>
        <div id="certRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="certAdd"><i class="bi bi-plus-lg me-1"></i>Add a certification</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<template id="certTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Certification">Certification</span><button type="button" class="bf-edit-del" title="Remove certification"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-12"><input name="cert_name[]" class="form-control form-control-sm" placeholder="Certificate / licence — e.g. NCA Registration G3"></div>
      <div class="col-7"><input name="cert_issuer[]" class="form-control form-control-sm" placeholder="Issuer — e.g. National Construction Authority"></div>
      <div class="col-5"><select name="cert_status[]" class="form-select form-select-sm"><option value="">Status…</option><option>VERIFIED</option><option>CURRENT</option><option>PENDING</option></select></div>
    </div>
  </div>
</template>

<!-- ═══ Edit business info modal ═══ -->
<div class="modal fade" id="bizModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_business">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-shop me-2" style="color:#c0392b;"></i>Business info</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">Help clients understand who they're hiring. Leave anything blank to hide it.</p>
        <div class="row g-2">
          <div class="col-6"><label class="bf-edit-lbl">Years in business</label><input name="biz_years" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['years_in_business'] ?? '') ?>" placeholder="e.g. 12 years"></div>
          <div class="col-6"><label class="bf-edit-lbl">Team size</label><input name="biz_team" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['team_size'] ?? '') ?>" placeholder="e.g. 8 – 15 crew"></div>
          <div class="col-12"><label class="bf-edit-lbl">Working hours</label><input name="biz_hours" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['working_hours'] ?? '') ?>" placeholder="e.g. Mon – Sat · 7:00–18:00"></div>
          <div class="col-12"><label class="bf-edit-lbl">Serving (areas)</label><input name="biz_serving" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['serving_area'] ?? '') ?>" placeholder="e.g. Nairobi metro + 3 counties"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save business info</button></div>
    </form>
  </div>
</div>

<!-- ═══ Edit skills modal ═══ -->
<div class="modal fade" id="skillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_skills">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-stars me-2" style="color:#c0392b;"></i>Skills &amp; specialisations</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">List your skills separated by commas. The first four show as highlighted specialisations.</p>
        <textarea name="skills" rows="4" class="form-control form-control-sm" placeholder="e.g. Reinforced Concrete, Foundation Engineering, Project Planning, Site Safety, AutoCAD"><?= htmlspecialchars(implode(', ', $skills)) ?></textarea>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
  </div>
</div>

<!-- ═══ Edit work history modal ═══ -->
<div class="modal fade" id="workModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_work">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-clock-history me-2" style="color:#c0392b;"></i>Work history</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">The key roles and projects you've delivered. Blank rows are skipped.</p>
        <div id="workRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="workAdd"><i class="bi bi-plus-lg me-1"></i>Add a role</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save work history</button></div>
    </form>
  </div>
</div>
<template id="workTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Role">Role</span><button type="button" class="bf-edit-del" title="Remove role"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-12"><input name="wh_role[]" class="form-control form-control-sm" placeholder="Role — e.g. Lead Contractor"></div>
      <div class="col-7"><input name="wh_org[]" class="form-control form-control-sm" placeholder="Project / organisation"></div>
      <div class="col-5"><input name="wh_when[]" class="form-control form-control-sm" placeholder="When — e.g. 2023 – 2024"></div>
      <div class="col-12"><textarea name="wh_desc[]" rows="2" class="form-control form-control-sm" placeholder="What you delivered"></textarea></div>
    </div>
  </div>
</template>

<!-- ═══ Edit education modal ═══ -->
<div class="modal fade" id="eduModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_education">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-mortarboard-fill me-2" style="color:#c0392b;"></i>Education &amp; training</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">Your qualifications, courses and training.</p>
        <div id="eduRows"></div>
        <button type="button" class="bf-btn-outline w-100" id="eduAdd"><i class="bi bi-plus-lg me-1"></i>Add a qualification</button>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<template id="eduTpl">
  <div class="bf-edit-row">
    <div class="bf-edit-row-h"><span class="bf-edit-row-n" data-label="Qualification">Qualification</span><button type="button" class="bf-edit-del" title="Remove"><i class="bi bi-trash"></i></button></div>
    <div class="row g-2">
      <div class="col-12"><input name="edu_title[]" class="form-control form-control-sm" placeholder="Qualification — e.g. BSc Civil Engineering"></div>
      <div class="col-12"><input name="edu_sub[]" class="form-control form-control-sm" placeholder="Institution &amp; year — e.g. University of Nairobi · 2008 – 2012"></div>
    </div>
  </div>
</template>

<!-- ═══ Edit performance highlights modal ═══ -->
<div class="modal fade" id="perfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="save_performance">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-graph-up-arrow me-2" style="color:#c0392b;"></i>Performance highlights</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="bf-modal-hint">These appear in your stats band. Your <b>overall rating</b> is calculated automatically from real client reviews — the rest are yours to state. Leave any blank to hide it.</p>
        <div class="row g-2">
          <div class="col-6"><label class="bf-edit-lbl">Jobs completed</label><input name="perf_jobs" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['jobs_completed'] ?? '') ?>" placeholder="e.g. 184"></div>
          <div class="col-6"><label class="bf-edit-lbl">On-time delivery (%)</label><input name="perf_ontime" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['on_time_pct'] ?? '') ?>" placeholder="e.g. 96"></div>
          <div class="col-6"><label class="bf-edit-lbl">Repeat clients (%)</label><input name="perf_repeat" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['repeat_pct'] ?? '') ?>" placeholder="e.g. 71"></div>
          <div class="col-6"><label class="bf-edit-lbl">Avg response time</label><input name="perf_response" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['response_time'] ?? '') ?>" placeholder="e.g. ~2 hours"></div>
          <div class="col-12"><label class="bf-edit-lbl">Availability note</label><input name="perf_avail" class="form-control form-control-sm" value="<?= htmlspecialchars($prof['availability_note'] ?? '') ?>" placeholder="e.g. 1 slot open this month"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save highlights</button></div>
    </form>
  </div>
</div>

<script>
// Shared repeatable-row editor for languages / certifications / portfolio / services.
window.BF_REPEAT = {
  lang:  <?= json_encode(array_map(fn($r) => ['lang_name'=>$r['language'],'lang_level'=>$r['level']], $languages), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
  cert:  <?= json_encode(array_map(fn($r) => ['cert_name'=>$r['name'],'cert_issuer'=>$r['issuer'],'cert_status'=>$r['status']], $certifications), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
  folio: <?= json_encode(array_map(fn($r) => ['folio_title'=>$r['title'],'folio_cat'=>$r['category'],'folio_year'=>$r['year'],'folio_img'=>$r['image_url']], $folio), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
  svc:   <?= json_encode(array_map(fn($r) => ['svc_name'=>$r['name'],'svc_desc'=>$r['description'],'svc_rate'=>$r['rate'],'svc_unit'=>$r['rate_unit']], $services), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
  work:  <?= json_encode(array_map(fn($r) => ['wh_role'=>$r['role'],'wh_org'=>$r['organization'],'wh_when'=>$r['period'],'wh_desc'=>$r['description']], $workHistory), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
  edu:   <?= json_encode(array_map(fn($r) => ['edu_title'=>$r['title'],'edu_sub'=>$r['institution']], $education), JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>
};
function bfRepeat(key){
  var wrap=document.getElementById(key+'Rows'), tpl=document.getElementById(key+'Tpl'), addBtn=document.getElementById(key+'Add');
  if(!wrap||!tpl) return;
  var data=(window.BF_REPEAT&&window.BF_REPEAT[key])||[];
  function rowsEls(){ return wrap.querySelectorAll('.bf-edit-row'); }
  function renumber(){ rowsEls().forEach(function(r,i){ var n=r.querySelector('.bf-edit-row-n'); if(n) n.textContent=n.getAttribute('data-label')+' '+(i+1); }); }
  function addRow(d){
    var node=tpl.content.cloneNode(true), row=node.querySelector('.bf-edit-row');
    if(d){ Object.keys(d).forEach(function(k){ var el=row.querySelector('[name="'+k+'[]"]'); if(el) el.value=(d[k]==null?'':d[k]); }); }
    var del=row.querySelector('.bf-edit-del'); if(del) del.addEventListener('click',function(){ row.remove(); renumber(); });
    wrap.appendChild(node); renumber();
  }
  if(data.length){ data.forEach(addRow); } else { addRow(); }
  if(addBtn) addBtn.addEventListener('click',function(){ addRow(); });
}
['lang','cert','folio','svc','work','edu'].forEach(bfRepeat);

// Portfolio: show the current photo as a thumbnail + live-preview a freshly chosen file.
(function(){
  var wrap = document.getElementById('folioRows');
  if (!wrap) return;
  function refresh(){
    wrap.querySelectorAll('.bf-edit-row').forEach(function(row){
      var hid = row.querySelector('input[name="folio_img[]"]'), t = row.querySelector('.bf-folio-thumb');
      if (t && hid && hid.value) { t.src = hid.value; t.style.display = 'block'; }
    });
  }
  wrap.addEventListener('change', function(e){
    if (e.target && e.target.name === 'folio_file[]' && e.target.files && e.target.files[0]) {
      var row = e.target.closest('.bf-edit-row'), t = row && row.querySelector('.bf-folio-thumb');
      if (t) { t.src = URL.createObjectURL(e.target.files[0]); t.style.display = 'block'; }
    }
  });
  setTimeout(refresh, 0);
  var add = document.getElementById('folioAdd');
  if (add) add.addEventListener('click', function(){ setTimeout(refresh, 0); });
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
