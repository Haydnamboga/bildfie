<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Settings'; $sp = 'settings';
$uid = (int) current_user()['id'];

/** Save an uploaded image to /uploads/members and return its web path (or null). */
function save_member_upload(string $field): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../../uploads/members';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fname = $field . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], "$dir/$fname")) return null;
    return '/uploads/members/' . $fname;
}

// ── handle saves (POST → redirect → GET) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action'] ?? '') {
        case 'save_account':
            $r = user_update_profile($uid, $_POST['name'] ?? '', $_POST['email'] ?? '', trim($_POST['phone'] ?? ''), $_POST['region_id'] ?? null);
            if ($r['ok']) member_provider_sync($uid);
            $_SESSION['set_flash'] = $r['ok'] ? ['ok','Account information saved.'] : ['err',$r['error']];
            header('Location: /pages/account/settings.php#account'); exit;
        case 'change_password':
            $new = (string)($_POST['new'] ?? ''); $conf = (string)($_POST['confirm'] ?? '');
            if ($new !== $conf) $_SESSION['set_flash'] = ['err','The new passwords do not match.'];
            else { $r = user_change_password($uid, (string)($_POST['current'] ?? ''), $new); $_SESSION['set_flash'] = $r['ok'] ? ['ok','Password changed.'] : ['err',$r['error']]; }
            header('Location: /pages/account/settings.php#security'); exit;
        case 'save_professional':
            $rate = trim($_POST['day_rate'] ?? '');
            $rate = $rate === '' ? null : (float) str_replace([',',' '], '', $rate);
            user_save_profession($uid, [
                'trade'            => trim($_POST['trade'] ?? '') ?: null,
                'title'            => trim($_POST['title'] ?? '') ?: null,
                'years_experience' => trim($_POST['years_experience'] ?? '') ?: null,
                'day_rate'         => $rate,
                'availability'     => in_array($_POST['availability'] ?? '', ['available','busy','arrangement','unavailable'], true) ? $_POST['availability'] : 'available',
                'company_name'     => trim($_POST['company_name'] ?? '') ?: null,
                'nca_number'       => trim($_POST['nca_number'] ?? '') ?: null,
                'nca_category'     => trim($_POST['nca_category'] ?? '') ?: null,
            ]);
            user_set_skills($uid, explode(',', $_POST['skills'] ?? ''));
            user_set_areas($uid, explode(',', $_POST['areas'] ?? ''));
            member_provider_sync($uid);
            $_SESSION['set_flash'] = ['ok','Professional details saved.'];
            header('Location: /pages/account/settings.php#professional'); exit;
        case 'save_media':
            $photo = save_member_upload('photo');
            $cover = save_member_upload('cover');
            if ($photo === null && $cover === null) {
                $_SESSION['set_flash'] = ['err','Choose an image to upload (JPG, PNG or WebP).'];
            } else {
                user_set_media($uid, $photo, $cover);
                member_provider_sync($uid);
                $_SESSION['set_flash'] = ['ok','Profile media updated.'];
            }
            header('Location: /pages/account/settings.php#professional'); exit;
        case 'save_notifications':
            user_save_notifications($uid, [
                'notif_bids'       => isset($_POST['notif_bids']) ? 1 : 0,
                'notif_messages'   => isset($_POST['notif_messages']) ? 1 : 0,
                'notif_payments'   => isset($_POST['notif_payments']) ? 1 : 0,
                'notif_milestones' => isset($_POST['notif_milestones']) ? 1 : 0,
                'notif_weekly'     => isset($_POST['notif_weekly']) ? 1 : 0,
                'notif_promos'     => isset($_POST['notif_promos']) ? 1 : 0,
            ]);
            $_SESSION['set_flash'] = ['ok','Notification preferences saved.'];
            header('Location: /pages/account/settings.php#notifications'); exit;
        case 'save_preferences':
            user_save_preferences($uid, $_POST['currency_code'] ?? 'KES', $_POST['language'] ?? 'English', $_POST['timezone'] ?? 'EAT (UTC+3) — Nairobi');
            $_SESSION['set_flash'] = ['ok','Preferences saved.'];
            header('Location: /pages/account/settings.php#preferences'); exit;
        case 'save_security':
            user_save_security($uid, isset($_POST['two_factor']) ? 1 : 0, isset($_POST['login_alerts']) ? 1 : 0);
            $_SESSION['set_flash'] = ['ok','Security preferences saved.'];
            header('Location: /pages/account/settings.php#security'); exit;
        case 'deactivate':
            user_set_status($uid, 'suspended');
            logout();
            header('Location: /pages/auth/login.php?deactivated=1'); exit;
    }
}

$flash = $_SESSION['set_flash'] ?? null; unset($_SESSION['set_flash']);
$u  = current_user();
$me = db_one("SELECT * FROM users WHERE id = ?", [$uid]);
$regions = db_all("SELECT id,name FROM regions WHERE is_active=1 ORDER BY name");

// member professional profile + preferences (real, from migration 0005)
$prof    = user_profession($uid);
$uskills = user_skills($uid);
$uareas  = user_areas($uid);
$prefs   = user_prefs($uid);

$trades    = ['Civil Contractor','Architect','Structural Engineer','MEP Engineer','Quantity Surveyor','Project Manager','Interior Designer','Electrician','Plumber','Mason','Carpenter','Painter','Welder','Landscaper'];
$yearsOpts = ['1–2 years','3–5 years','6–10 years','12+ years'];
$availOpts = ['available'=>'Available now','busy'=>'Busy — limited slots','arrangement'=>'By arrangement','unavailable'=>'Not taking work'];
$ncaOpts   = ['G1','G2','G3','G4','G5','G6','G7','G8'];
$rateDisp  = (($prof['day_rate'] ?? null) !== null && $prof['day_rate'] !== '') ? number_format((float)$prof['day_rate']) : '';
$curAvail  = $prof['availability'] ?? 'available';
$checks    = [!empty($prof['trade']),!empty($prof['title']),!empty($prof['years_experience']),!empty($prof['day_rate']),(!empty($prof['company_name'])||!empty($prof['nca_number'])),count($uskills)>0,count($uareas)>0];
$completion = (int) round(array_sum(array_map('intval',$checks))/max(1,count($checks))*100);

// public listing status
$provReady = member_provider_ready($uid);
$provRow   = db_one("SELECT id,status,is_verified FROM providers WHERE user_id=?", [$uid]);
$missing   = [];
if (empty($prof['trade']))     $missing[] = 'trade';
if (empty($prof['title']))     $missing[] = 'title';
if (($prof['day_rate'] ?? '') === '' || $prof['day_rate'] === null) $missing[] = 'rate';
if (count($uskills) < 1)       $missing[] = 'a specialisation';
if (empty($prof['photo_url'])) $missing[] = 'a profile photo';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<style>
  .bf-set-card { background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:16px; }
  .bf-set-head { padding:15px 22px;border-bottom:1px solid var(--line);font-size:13.5px;font-weight:800;color:var(--ink);display:flex;align-items:center;gap:8px; }
  .bf-set-head i { color:#1e3a5f;font-size:15px; }
  .bf-set-body { padding:22px; }
  .bf-set-lbl { font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px; }
  .bf-set-input { width:100%;font-size:13px;border:1px solid var(--line);border-radius:9px;padding:10px 13px;color:var(--ink);font-family:inherit;outline:none; }
  .bf-set-input:focus { border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,.08); }
  .bf-set-toggle-row { display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-top:1px solid var(--line-2); }
  .bf-set-toggle-row:first-child { border-top:none;padding-top:0; }
  .bf-set-toggle-row:last-child { padding-bottom:0; }
  .form-switch .form-check-input { width:2.4em;height:1.25em;cursor:pointer;border-color:var(--line); }
  .form-switch .form-check-input:checked { background-color:#1e3a5f;border-color:#1e3a5f; }
  .form-switch .form-check-input:focus { box-shadow:0 0 0 .2rem rgba(30,58,95,.12);border-color:#1e3a5f; }
  .bf-set-nav a { display:block;font-size:12.5px;font-weight:600;color:var(--ink-3);text-decoration:none;padding:8px 12px;border-radius:8px;transition:all .12s; }
  .bf-set-nav a:hover { background:var(--surface);color:var(--ink); }
</style>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="container" style="padding:24px 0 56px;">
     <div style="max-width:1000px;margin:0 auto;">

      <div class="mb-4">
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Account</div>
        <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">Settings</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Manage your account, security, notifications and preferences.</p>
      </div>

      <?php if ($flash): ?>
      <div style="border-radius:11px;padding:12px 16px;font-size:13px;margin-bottom:18px;<?= $flash[0]==='ok' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;' ?>">
        <i class="bi <?= $flash[0]==='ok'?'bi-check-circle-fill':'bi-exclamation-triangle-fill' ?> me-1"></i><?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="row g-3">

        <!-- side nav -->
        <div class="col-lg-3 d-none d-lg-block">
          <div class="bf-set-nav" style="position:sticky;top:80px;">
            <a href="#account"><i class="bi bi-person me-2"></i>Account</a>
            <a href="#professional"><i class="bi bi-briefcase me-2"></i>Professional</a>
            <a href="#security"><i class="bi bi-shield-lock me-2"></i>Security</a>
            <a href="#notifications"><i class="bi bi-bell me-2"></i>Notifications</a>
            <a href="#preferences"><i class="bi bi-sliders me-2"></i>Preferences</a>
            <a href="#danger" style="color:#c0392b;"><i class="bi bi-exclamation-triangle me-2"></i>Danger zone</a>
          </div>
        </div>

        <!-- content -->
        <div class="col-lg-9">

          <!-- Account -->
          <div class="bf-set-card" id="account">
            <div class="bf-set-head"><i class="bi bi-person-fill"></i> Account information</div>
            <div class="bf-set-body">
              <form method="post">
                <input type="hidden" name="action" value="save_account">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="bf-set-lbl">Full name</label>
                    <input name="name" class="bf-set-input" value="<?= htmlspecialchars($me['name']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="bf-set-lbl">Email address</label>
                    <input name="email" class="bf-set-input" type="email" value="<?= htmlspecialchars($me['email']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="bf-set-lbl">Phone</label>
                    <input name="phone" class="bf-set-input" value="<?= htmlspecialchars($me['phone'] ?? '') ?>" placeholder="+254 712 345 678">
                  </div>
                  <div class="col-md-6">
                    <label class="bf-set-lbl">Location</label>
                    <select name="region_id" class="bf-set-input">
                      <option value="">— Select region —</option>
                      <?php foreach ($regions as $rg): ?>
                      <option value="<?= (int)$rg['id'] ?>" <?= (int)$me['region_id']===(int)$rg['id']?'selected':'' ?>><?= htmlspecialchars($rg['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <button type="submit" style="font-size:12.5px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:10px 22px;cursor:pointer;">Update account</button>
              </form>
            </div>
          </div>

          <!-- Professional account -->
          <div class="bf-set-card" id="professional">
            <div class="bf-set-head" style="justify-content:space-between;">
              <span style="display:flex;align-items:center;gap:8px;"><i class="bi bi-briefcase-fill"></i> Service provider profile</span>
              <?php $inMkt = ($provRow && $provRow['status'] === 'active'); ?>
              <span style="font-size:9.5px;font-weight:800;letter-spacing:.05em;padding:4px 10px;border-radius:6px;<?= $inMkt ? 'background:#f0fdf4;color:#166534;' : 'background:#f4f4f2;color:#6b6b6b;' ?>"><?= $inMkt ? 'IN MARKETPLACE' : 'NOT LISTED YET' ?></span>
            </div>
            <div class="bf-set-body">

              <!-- Every account can offer services — no switch, just complete the profile -->
              <div style="display:flex;gap:12px;align-items:flex-start;background:#eaf0f6;border:1px solid #d6e2ee;border-radius:12px;padding:15px 17px;margin-bottom:22px;">
                <i class="bi bi-stars" style="font-size:18px;color:#1e3a5f;margin-top:1px;flex-shrink:0;"></i>
                <div>
                  <div style="font-size:13px;font-weight:800;color:#1e3a5f;">Offer services on bildfie</div>
                  <div style="font-size:11.5px;color:var(--ink-3);line-height:1.6;margin-top:3px;max-width:600px;">Every bildfie account both hires and gets hired — there's no switch to flip. <b>Upgrade your profile</b> by completing the professional details below, and you'll automatically appear in the marketplace as a provider clients can find and invite.</div>
                </div>
              </div>

              <!-- Professional fields -->
              <div id="proFields">

                <!-- Public listing status -->
                <?php
                  $lsBg='#f4f4f2'; $lsBd='var(--line)'; $lsC='#6b6b6b'; $lsIc='bi-eye-slash'; $lsT='Not yet listed publicly';
                  $lsS = $missing ? ('Add ' . implode(', ', $missing) . ' to go live on the marketplace.') : 'Your profile is ready.';
                  if ($provRow && $provRow['status']==='suspended') { $lsBg='#fef2f2';$lsBd='#f3c9c2';$lsC='#c0392b';$lsIc='bi-slash-circle';$lsT='Listing suspended by an admin';$lsS='Contact support to restore your listing.'; }
                  elseif ($provReady && $provRow && $provRow['status']==='active') {
                    $lsBg='#f0fdf4';$lsBd='#bbf7d0';$lsC='#166534';$lsIc='bi-broadcast';
                    $lsT = (int)$provRow['is_verified'] ? 'Live &amp; verified on the marketplace' : 'Live on the marketplace';
                    $lsS = (int)$provRow['is_verified'] ? 'Clients can find and hire you.' : 'Visible to clients now — the verified badge is pending admin review.';
                  }
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:<?=$lsBg?>;border:1px solid <?=$lsBd?>;border-radius:12px;padding:13px 16px;margin-bottom:18px;">
                  <div style="display:flex;align-items:center;gap:11px;">
                    <i class="bi <?=$lsIc?>" style="font-size:18px;color:<?=$lsC?>;"></i>
                    <div><div style="font-size:12.5px;font-weight:800;color:<?=$lsC?>;"><?=$lsT?></div><div style="font-size:11.5px;color:var(--ink-3);margin-top:1px;"><?=$lsS?></div></div>
                  </div>
                  <?php if ($provReady && $provRow && $provRow['status']==='active'): ?>
                  <a href="/pages/marketplace/professional.php?id=<?= (int)$provRow['id'] ?>" target="_blank" style="font-size:11.5px;font-weight:700;color:#fff;background:#166534;padding:8px 14px;border-radius:8px;text-decoration:none;white-space:nowrap;">View public listing →</a>
                  <?php endif; ?>
                </div>

                <!-- Profile photo & cover (photo required to list) -->
                <form method="post" enctype="multipart/form-data" style="border:1px solid var(--line);border-radius:12px;padding:14px 16px;margin-bottom:18px;">
                  <input type="hidden" name="action" value="save_media">
                  <label class="bf-set-lbl" style="margin-bottom:10px;">Profile photo &amp; cover <span style="color:#c0392b;font-weight:600;">· photo required to appear publicly</span></label>
                  <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                    <?php if (!empty($prof['photo_url'])): ?>
                      <img src="<?= htmlspecialchars($prof['photo_url']) ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--line);">
                    <?php else: ?>
                      <div style="width:64px;height:64px;border-radius:50%;background:var(--surface);border:2px dashed var(--line);display:flex;align-items:center;justify-content:center;color:var(--ink-4);flex-shrink:0;"><i class="bi bi-person" style="font-size:24px;"></i></div>
                    <?php endif; ?>
                    <div style="flex:1;min-width:200px;">
                      <input type="file" name="photo" accept="image/*" class="bf-set-input" style="padding:7px;font-size:12px;margin-bottom:8px;">
                      <input type="file" name="cover" accept="image/*" class="bf-set-input" style="padding:7px;font-size:12px;">
                      <div style="font-size:10.5px;color:var(--ink-4);margin-top:6px;">JPG, PNG or WebP. Cover photo optional.</div>
                    </div>
                    <button type="submit" style="font-size:12px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:9px 18px;cursor:pointer;"><i class="bi bi-upload me-1"></i>Upload</button>
                  </div>
                </form>

                <form method="post">
                  <input type="hidden" name="action" value="save_professional">
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label class="bf-set-lbl">Primary trade / profession</label>
                      <select name="trade" class="bf-set-input">
                        <option value="">— Select —</option>
                        <?php foreach ($trades as $t): ?><option <?= ($prof['trade'] ?? '')===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="bf-set-lbl">Professional title (headline)</label>
                      <input name="title" class="bf-set-input" value="<?= htmlspecialchars($prof['title'] ?? '') ?>" placeholder="e.g. Civil Contractor &amp; Project Manager">
                    </div>
                    <div class="col-md-4">
                      <label class="bf-set-lbl">Years of experience</label>
                      <select name="years_experience" class="bf-set-input"><option value="">—</option><?php foreach ($yearsOpts as $y): ?><option <?= ($prof['years_experience'] ?? '')===$y?'selected':'' ?>><?= $y ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-4">
                      <label class="bf-set-lbl">Starting rate</label>
                      <div style="display:flex;align-items:center;border:1px solid var(--line);border-radius:9px;padding:0 13px;">
                        <span style="font-size:12px;color:var(--ink-4);font-weight:600;">KES</span>
                        <input name="day_rate" style="border:none;outline:none;font-size:13px;padding:10px 8px;width:100%;font-family:inherit;color:var(--ink);" value="<?= htmlspecialchars($rateDisp) ?>" placeholder="8,500">
                        <span style="font-size:12px;color:var(--ink-4);">/day</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="bf-set-lbl">Availability</label>
                      <select name="availability" class="bf-set-input"><?php foreach ($availOpts as $k=>$lbl): ?><option value="<?=$k?>" <?= $curAvail===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-6">
                      <label class="bf-set-lbl">Business / company name <span style="color:var(--ink-4);font-weight:500;">(optional)</span></label>
                      <input name="company_name" class="bf-set-input" value="<?= htmlspecialchars($prof['company_name'] ?? '') ?>" placeholder="e.g. Mwenda Builders Ltd">
                    </div>
                    <div class="col-md-3">
                      <label class="bf-set-lbl">NCA registration no.</label>
                      <input name="nca_number" class="bf-set-input" value="<?= htmlspecialchars($prof['nca_number'] ?? '') ?>" placeholder="NCA/G3/…">
                    </div>
                    <div class="col-md-3">
                      <label class="bf-set-lbl">NCA category</label>
                      <select name="nca_category" class="bf-set-input"><option value="">—</option><?php foreach ($ncaOpts as $g): ?><option <?= ($prof['nca_category'] ?? '')===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?></select>
                    </div>
                  </div>

                  <!-- Service areas -->
                  <div class="mb-3">
                    <label class="bf-set-lbl">Service areas <span style="color:var(--ink-4);font-weight:500;">(comma separated)</span></label>
                    <?php if ($uareas): ?><div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:7px;"><?php foreach ($uareas as $a): ?><span class="bf-pf-chip core"><?= htmlspecialchars($a) ?></span><?php endforeach; ?></div><?php endif; ?>
                    <input name="areas" class="bf-set-input" value="<?= htmlspecialchars(implode(', ', $uareas)) ?>" placeholder="Nairobi, Kiambu, Machakos">
                  </div>

                  <!-- Specialisations -->
                  <div class="mb-3">
                    <label class="bf-set-lbl">Specialisations <span style="color:var(--ink-4);font-weight:500;">(comma separated)</span></label>
                    <?php if ($uskills): ?><div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:7px;"><?php foreach ($uskills as $sk): ?><span class="bf-pf-chip"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?></div><?php endif; ?>
                    <input name="skills" class="bf-set-input" value="<?= htmlspecialchars(implode(', ', $uskills)) ?>" placeholder="Reinforced Concrete, Foundations, BOQ">
                  </div>

                  <!-- Completeness + link to public profile -->
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;background:var(--surface);border:1px solid var(--line-2);border-radius:12px;padding:14px 16px;margin:18px 0;">
                    <div style="display:flex;align-items:center;gap:12px;">
                      <i class="bi bi-stars" style="font-size:18px;color:#c9a84c;"></i>
                      <div>
                        <div style="font-size:12.5px;font-weight:800;color:var(--ink);">Professional profile is <?= $completion ?>% complete</div>
                        <div style="font-size:11.5px;color:var(--ink-3);margin-top:1px;">Fill trade, rate, areas &amp; specialisations to win more work.</div>
                      </div>
                    </div>
                    <a href="/pages/account/profile.php" style="font-size:11.5px;font-weight:700;color:#fff;background:#1e3a5f;padding:9px 16px;border-radius:9px;text-decoration:none;white-space:nowrap;">Manage public profile →</a>
                  </div>

                  <button type="submit" style="font-size:12.5px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:10px 22px;cursor:pointer;">Save professional details</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Security -->
          <div class="bf-set-card" id="security">
            <div class="bf-set-head"><i class="bi bi-shield-lock-fill"></i> Security</div>
            <div class="bf-set-body">
              <form method="post">
                <input type="hidden" name="action" value="change_password">
                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="bf-set-lbl">Current password</label>
                    <input name="current" class="bf-set-input" type="password" placeholder="••••••••" required>
                  </div>
                  <div class="col-md-4">
                    <label class="bf-set-lbl">New password</label>
                    <input name="new" class="bf-set-input" type="password" placeholder="Min. 6 characters" required>
                  </div>
                  <div class="col-md-4">
                    <label class="bf-set-lbl">Confirm new password</label>
                    <input name="confirm" class="bf-set-input" type="password" placeholder="Repeat password" required>
                  </div>
                </div>
                <button type="submit" style="font-size:12.5px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:10px 22px;cursor:pointer;margin-bottom:18px;">Change password</button>
              </form>

              <form method="post" id="secForm">
                <input type="hidden" name="action" value="save_security">
                <div class="bf-set-toggle-row" style="border-top:1px solid var(--line-2);padding-top:16px;">
                  <div>
                    <div style="font-size:13px;font-weight:700;color:var(--ink);">Two-factor authentication</div>
                    <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;">Add an extra layer of security via SMS or authenticator app.</div>
                  </div>
                  <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="two_factor" onchange="document.getElementById('secForm').submit()" <?= !empty($prefs['two_factor'])?'checked':'' ?>></div>
                </div>
                <div class="bf-set-toggle-row">
                  <div>
                    <div style="font-size:13px;font-weight:700;color:var(--ink);">Login alerts</div>
                    <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;">Get notified of sign-ins from new devices.</div>
                  </div>
                  <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="login_alerts" onchange="document.getElementById('secForm').submit()" <?= !empty($prefs['login_alerts'])?'checked':'' ?>></div>
                </div>
              </form>
            </div>
          </div>

          <!-- Notifications -->
          <div class="bf-set-card" id="notifications">
            <div class="bf-set-head"><i class="bi bi-bell-fill"></i> Notifications</div>
            <div class="bf-set-body">
              <form method="post">
                <input type="hidden" name="action" value="save_notifications">
                <?php foreach ([
                  ['notif_bids','New bids on my projects','Email + push when a professional submits a bid'],
                  ['notif_messages','Messages','Notify me when I receive a new message'],
                  ['notif_payments','Invoice & payment updates','Payment received, invoice due and escrow releases'],
                  ['notif_milestones','Project milestones','Updates when a milestone is reached or overdue'],
                  ['notif_weekly','Weekly summary','A digest of activity across your projects'],
                  ['notif_promos','Product news & offers','Occasional platform updates and promotions'],
                ] as [$k,$title,$desc]): ?>
                <div class="bf-set-toggle-row">
                  <div>
                    <div style="font-size:13px;font-weight:700;color:var(--ink);"><?=$title?></div>
                    <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;"><?=$desc?></div>
                  </div>
                  <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="<?=$k?>" <?= !empty($prefs[$k])?'checked':'' ?>></div>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:16px;"><button type="submit" style="font-size:12.5px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:10px 22px;cursor:pointer;">Save notifications</button></div>
              </form>
            </div>
          </div>

          <!-- Preferences -->
          <div class="bf-set-card" id="preferences">
            <div class="bf-set-head"><i class="bi bi-sliders"></i> Preferences</div>
            <div class="bf-set-body">
              <form method="post">
                <input type="hidden" name="action" value="save_preferences">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="bf-set-lbl">Currency</label>
                    <select name="currency_code" class="bf-set-input">
                      <?php foreach (['KES'=>'KES — Kenyan Shilling','USD'=>'USD — US Dollar','NGN'=>'NGN — Nigerian Naira','GHS'=>'GHS — Ghanaian Cedi'] as $cc=>$lbl): ?>
                      <option value="<?=$cc?>" <?= ($prefs['currency_code'] ?? 'KES')===$cc?'selected':'' ?>><?=$lbl?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="bf-set-lbl">Language</label>
                    <select name="language" class="bf-set-input"><?php foreach (['English','Kiswahili','Français'] as $lg): ?><option <?= ($prefs['language'] ?? 'English')===$lg?'selected':'' ?>><?=$lg?></option><?php endforeach; ?></select>
                  </div>
                  <div class="col-md-4">
                    <label class="bf-set-lbl">Timezone</label>
                    <select name="timezone" class="bf-set-input"><?php foreach (['EAT (UTC+3) — Nairobi','WAT (UTC+1) — Lagos','GMT (UTC+0) — Accra','GST (UTC+4) — Dubai'] as $tz): ?><option <?= ($prefs['timezone'] ?? '')===$tz?'selected':'' ?>><?=$tz?></option><?php endforeach; ?></select>
                  </div>
                </div>
                <div style="margin-top:16px;"><button type="submit" style="font-size:12.5px;font-weight:700;color:#fff;background:#1e3a5f;border:none;border-radius:9px;padding:10px 22px;cursor:pointer;">Save preferences</button></div>
              </form>
            </div>
          </div>

          <!-- Danger zone -->
          <div class="bf-set-card" id="danger" style="border-color:#f3c9c2;">
            <div class="bf-set-head" style="color:#c0392b;border-bottom-color:#f3c9c2;"><i class="bi bi-exclamation-triangle-fill" style="color:#c0392b;"></i> Danger zone</div>
            <div class="bf-set-body">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                  <div style="font-size:13px;font-weight:700;color:var(--ink);">Deactivate account</div>
                  <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;max-width:460px;">Your profile is hidden from the marketplace and you're signed out immediately. An admin can reactivate your account on request.</div>
                </div>
                <form method="post" onsubmit="return confirm('Deactivate your account? You will be signed out right away.');">
                  <input type="hidden" name="action" value="deactivate">
                  <button type="submit" style="font-size:12.5px;font-weight:700;color:#c0392b;background:#fff;border:1.5px solid #c0392b;border-radius:9px;padding:10px 20px;cursor:pointer;">Deactivate</button>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>

  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
