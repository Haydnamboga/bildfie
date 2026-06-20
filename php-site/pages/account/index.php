<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/profile_data.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$tab  = $_GET['tab'] ?? 'basic';
$msg  = '';
$err  = '';

// Regions for select
$regions = db_all("SELECT id, name FROM regions WHERE is_active=1 ORDER BY name", []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_tab'] ?? $tab;

    if ($action === 'basic') {
        $res = user_update_profile($uid, $_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['phone'] ?? null, $_POST['region_id'] ?? null);
        $msg = $res['ok'] ? 'Profile updated.' : '';
        $err = $res['ok'] ? '' : ($res['error'] ?? 'Error updating profile.');
    }
    elseif ($action === 'profession') {
        user_save_profession($uid, $_POST);
        $areas = array_filter(array_map('trim', explode(',', $_POST['serving_area_raw'] ?? '')));
        user_set_areas($uid, $areas);
        // Save business info
        user_save_business($uid, $_POST);
        // Save bio
        db_stmt("INSERT IGNORE INTO user_professions (user_id) VALUES (?)", [$uid]);
        db_stmt("UPDATE user_professions SET bio=? WHERE user_id=?", [trim($_POST['bio'] ?? ''), $uid]);
        member_provider_sync($uid);
        $msg = 'Professional profile saved.';
    }
    elseif ($action === 'skills') {
        $skills = array_filter(array_map('trim', preg_split('/[,\n]+/', $_POST['skills_raw'] ?? '')));
        user_set_skills($uid, $skills);
        member_provider_sync($uid);
        $msg = 'Skills updated.';
    }
    elseif ($action === 'portfolio') {
        $rows = [];
        foreach ($_POST['pf_title'] ?? [] as $i => $title) {
            if (trim($title) === '') continue;
            $img = member_upload_file_at('pf_image', $i, 'portfolio');
            $rows[] = ['title' => $title, 'category' => $_POST['pf_category'][$i] ?? '', 'year' => $_POST['pf_year'][$i] ?? '', 'image_url' => $img ?: ($_POST['pf_image_existing'][$i] ?? '')];
        }
        user_save_portfolio($uid, $rows);
        $msg = 'Portfolio updated.';
    }
    elseif ($action === 'certifications') {
        $rows = [];
        foreach ($_POST['cert_name'] ?? [] as $i => $name) {
            if (trim($name) === '') continue;
            $rows[] = ['name' => $name, 'issuer' => $_POST['cert_issuer'][$i] ?? '', 'status' => $_POST['cert_status'][$i] ?? ''];
        }
        user_save_certifications($uid, $rows);
        $msg = 'Certifications updated.';
    }
    elseif ($action === 'packages') {
        $rows = [];
        foreach ($_POST['pkg_title'] ?? [] as $i => $title) {
            if (trim($title) === '') continue;
            $rows[] = [
                'tier' => $_POST['pkg_tier'][$i] ?? '',
                'title' => $title,
                'price' => $_POST['pkg_price'][$i] ?? '',
                'price_unit' => $_POST['pkg_price_unit'][$i] ?? '',
                'description' => $_POST['pkg_description'][$i] ?? '',
                'delivery' => $_POST['pkg_delivery'][$i] ?? '',
                'revisions' => $_POST['pkg_revisions'][$i] ?? '',
                'features' => $_POST['pkg_features'][$i] ?? '',
                'is_featured' => !empty($_POST['pkg_featured'][$i]),
            ];
        }
        user_save_packages($uid, $rows);
        $msg = 'Packages updated.';
    }
    elseif ($action === 'work_history') {
        $rows = [];
        foreach ($_POST['wh_role'] ?? [] as $i => $role) {
            if (trim($role) === '') continue;
            $rows[] = ['role' => $role, 'organization' => $_POST['wh_org'][$i] ?? '', 'period' => $_POST['wh_period'][$i] ?? '', 'description' => $_POST['wh_desc'][$i] ?? ''];
        }
        user_save_work_history($uid, $rows);
        $msg = 'Work history updated.';
    }
    elseif ($action === 'education') {
        $rows = [];
        foreach ($_POST['edu_title'] ?? [] as $i => $title) {
            if (trim($title) === '') continue;
            $rows[] = ['title' => $title, 'institution' => $_POST['edu_inst'][$i] ?? ''];
        }
        user_save_education($uid, $rows);
        $msg = 'Education updated.';
    }
    elseif ($action === 'media') {
        $photo = member_upload_image('photo');
        $cover = member_upload_image('cover');
        if ($photo || $cover) {
            user_set_media($uid, $photo, $cover);
            $msg = 'Media updated.';
        } else {
            $err = 'Please select a valid image file (jpg, png, webp).';
        }
    }

    if (!$err) {
        $tab = $_POST['_tab'] ?? $tab;
        header('Location: /pages/account/?tab=' . urlencode($tab) . ($msg ? '&saved=1' : ''));
        exit;
    }
}

if (isset($_GET['saved'])) $msg = 'Saved successfully.';

// Load data
user_reload($uid);
$u    = current_user();
$prof = user_profession($uid);
$skills = user_skills($uid);
$portfolio = user_portfolio($uid);
$certs = user_certifications($uid);
$packages = user_packages($uid);
$work_hist = user_work_history($uid);
$education = user_education($uid);
$areas = user_areas($uid);
$completion = profile_completion($uid, $prof);
$avatar_url = user_avatar($u, 80);

$sp           = 'profile';
$topbar_title = 'My Profile';
$page_title   = 'Profile';

$tabs = [
  'basic' => 'Basic Info',
  'profession' => 'Professional Profile',
  'skills' => 'Skills',
  'portfolio' => 'Portfolio',
  'certifications' => 'Certifications',
  'packages' => 'Packages',
  'work_history' => 'Experience',
  'education' => 'Education',
  'media' => 'Photos',
];
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.section-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px;margin-bottom:16px;}
.pf-tab-nav{display:flex;flex-direction:column;gap:1px;}
.pf-tab-link{display:block;padding:8px 12px;border-radius:8px;font-size:.85rem;font-weight:500;color:var(--ink-2);text-decoration:none;transition:background .12s,color .12s;}
.pf-tab-link.active,.pf-tab-link:hover{background:#eaf0f6;color:#1e3a5f;font-weight:600;}
.repeater-row{background:var(--surface);border:1px solid var(--line);border-radius:8px;padding:14px;margin-bottom:10px;position:relative;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <div class="row g-4">
    <!-- Left: tab nav + completion -->
    <div class="col-12 col-lg-3">
      <!-- Profile card -->
      <div class="section-card text-center mb-3">
        <img src="<?= htmlspecialchars($avatar_url) ?>" alt="<?= htmlspecialchars($u['name']) ?>"
             width="64" height="64" style="border-radius:50%;object-fit:cover;border:3px solid var(--line);">
        <div style="font-weight:700;margin-top:10px;font-size:.95rem;"><?= htmlspecialchars($u['name']) ?></div>
        <div style="font-size:.8rem;color:var(--ink-3);"><?= htmlspecialchars($u['email']) ?></div>
        <!-- Completion -->
        <div class="mt-3">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--ink-3);margin-bottom:6px;">Profile <?= $completion['pct'] ?>% complete</div>
          <div style="height:5px;background:var(--line);border-radius:3px;overflow:hidden;">
            <div style="width:<?= $completion['pct'] ?>%;height:100%;background:#1e3a5f;border-radius:3px;"></div>
          </div>
        </div>
        <a href="/pages/professionals/profile.php?id=<?= urlencode($u['public_id'] ?? '') ?>" target="_blank"
           class="btn btn-light btn-sm w-100 mt-3" style="font-size:.8rem;">
          <i class="bi bi-eye me-1"></i>View public profile
        </a>
      </div>
      <!-- Tab nav -->
      <div class="section-card">
        <nav class="pf-tab-nav">
          <?php foreach ($tabs as $tk => $tl): ?>
            <a href="?tab=<?= $tk ?>" class="pf-tab-link <?= $tab === $tk ? 'active' : '' ?>"><?= $tl ?></a>
          <?php endforeach; ?>
        </nav>
      </div>
    </div>

    <!-- Right: content -->
    <div class="col-12 col-lg-9">
      <?php if ($msg): ?><div class="alert alert-success py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

      <?php if ($tab === 'basic'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Basic Information</h2>
        <form method="POST">
          <input type="hidden" name="_tab" value="basic">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Full Name *</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($u['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Email Address *</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Phone</label>
              <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($u['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Region</label>
              <select name="region_id" class="form-select">
                <option value="">Select region...</option>
                <?php foreach ($regions as $r): ?>
                  <option value="<?= (int)$r['id'] ?>" <?= (int)($u['region_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <button type="submit" class="bf-btn-dark mt-3">Save Changes</button>
        </form>
      </div>

      <?php elseif ($tab === 'profession'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Professional Profile</h2>
        <form method="POST">
          <input type="hidden" name="_tab" value="profession">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Trade / Specialisation</label>
              <input type="text" name="trade" class="form-control" value="<?= htmlspecialchars($prof['trade'] ?? '') ?>" placeholder="e.g. Structural Engineer">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Professional Title</label>
              <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($prof['title'] ?? '') ?>" placeholder="e.g. Senior Structural Engineer">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Years Experience</label>
              <input type="number" name="years_experience" class="form-control" min="0" value="<?= htmlspecialchars($prof['years_experience'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Day Rate</label>
              <input type="number" name="day_rate" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($prof['day_rate'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Rate Unit</label>
              <select name="rate_unit" class="form-select">
                <?php foreach (['day','hour','week','month','project'] as $ru): ?>
                  <option value="<?= $ru ?>" <?= ($prof['rate_unit'] ?? 'day') === $ru ? 'selected' : '' ?>><?= $ru ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency_code" class="form-select">
                <?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $c): ?>
                  <option value="<?= $c ?>" <?= ($prof['currency_code'] ?? 'KES') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Availability</label>
              <select name="availability" class="form-select">
                <?php foreach (['available'=>'Available','busy'=>'Busy','unavailable'=>'Unavailable'] as $k=>$v): ?>
                  <option value="<?= $k ?>" <?= ($prof['availability'] ?? 'available') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Company Name</label>
              <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($prof['company_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">NCA Number</label>
              <input type="text" name="nca_number" class="form-control" value="<?= htmlspecialchars($prof['nca_number'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Bio / About You</label>
              <textarea name="bio" class="form-control" rows="5" placeholder="Tell clients about yourself, your expertise..."><?= htmlspecialchars($prof['bio'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Service Areas <span style="font-weight:400;color:var(--ink-4);">(comma-separated)</span></label>
              <input type="text" name="serving_area_raw" class="form-control" placeholder="Nairobi, Mombasa, Kisumu"
                     value="<?= htmlspecialchars(implode(', ', $areas)) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Website</label>
              <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($prof['website'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Public Phone</label>
              <input type="tel" name="public_phone" class="form-control" value="<?= htmlspecialchars($prof['public_phone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Years in Business</label>
              <input type="text" name="years_in_business" class="form-control" value="<?= htmlspecialchars($prof['years_in_business'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Team Size</label>
              <input type="text" name="team_size" class="form-control" value="<?= htmlspecialchars($prof['team_size'] ?? '') ?>" placeholder="e.g. 1-5, 10-50">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Working Hours</label>
              <input type="text" name="working_hours" class="form-control" value="<?= htmlspecialchars($prof['working_hours'] ?? '') ?>" placeholder="e.g. Mon-Fri 8am-5pm">
            </div>
          </div>
          <button type="submit" class="bf-btn-dark mt-3">Save Professional Profile</button>
        </form>
      </div>

      <?php elseif ($tab === 'skills'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;">Skills</h2>
        <p style="font-size:.83rem;color:var(--ink-3);margin-bottom:18px;">Enter your skills separated by commas or new lines.</p>
        <form method="POST">
          <input type="hidden" name="_tab" value="skills">
          <textarea name="skills_raw" class="form-control" rows="6"
                    placeholder="Structural Design, AutoCAD, Construction Management..."><?= htmlspecialchars(implode(', ', $skills)) ?></textarea>
          <div id="skillPills" class="d-flex flex-wrap gap-2 mt-3"></div>
          <button type="submit" class="bf-btn-dark mt-3">Save Skills</button>
        </form>
      </div>

      <?php elseif ($tab === 'portfolio'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;">Portfolio</h2>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="_tab" value="portfolio">
          <div id="portfolioRepeater">
            <?php foreach ($portfolio as $i => $item): ?>
            <div class="repeater-row">
              <div class="row g-2">
                <div class="col-md-5"><input type="text" name="pf_title[]" class="form-control form-control-sm" placeholder="Project title *" value="<?= htmlspecialchars($item['title']) ?>" required></div>
                <div class="col-md-3"><input type="text" name="pf_category[]" class="form-control form-control-sm" placeholder="Category" value="<?= htmlspecialchars($item['category'] ?? '') ?>"></div>
                <div class="col-md-2"><input type="text" name="pf_year[]" class="form-control form-control-sm" placeholder="Year" value="<?= htmlspecialchars($item['year'] ?? '') ?>"></div>
                <div class="col-md-2"><button type="button" class="btn btn-sm btn-light remove-row w-100">Remove</button></div>
                <div class="col-md-6"><input type="file" name="pf_image[<?= $i ?>]" class="form-control form-control-sm" accept="image/*">
                  <input type="hidden" name="pf_image_existing[]" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>"></div>
                <?php if ($item['image_url']): ?>
                  <div class="col-md-2"><img src="<?= htmlspecialchars($item['image_url']) ?>" style="height:40px;border-radius:4px;object-fit:cover;"></div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" id="addPortfolio" class="btn btn-sm" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;margin-bottom:12px;"><i class="bi bi-plus me-1"></i>Add project</button>
          <div><button type="submit" class="bf-btn-dark">Save Portfolio</button></div>
        </form>
      </div>

      <?php elseif ($tab === 'certifications'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Certifications &amp; Licences</h2>
        <form method="POST">
          <input type="hidden" name="_tab" value="certifications">
          <div id="certRepeater">
            <?php foreach ($certs as $cert): ?>
            <div class="repeater-row">
              <div class="row g-2">
                <div class="col-md-5"><input type="text" name="cert_name[]" class="form-control form-control-sm" placeholder="Certificate name *" value="<?= htmlspecialchars($cert['name']) ?>" required></div>
                <div class="col-md-4"><input type="text" name="cert_issuer[]" class="form-control form-control-sm" placeholder="Issuing body" value="<?= htmlspecialchars($cert['issuer'] ?? '') ?>"></div>
                <div class="col-md-2"><input type="text" name="cert_status[]" class="form-control form-control-sm" placeholder="Status" value="<?= htmlspecialchars($cert['status'] ?? '') ?>"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn btn-sm add-row-btn" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;" data-target="certRepeater"
                  data-template='<div class="repeater-row"><div class="row g-2"><div class="col-md-5"><input type="text" name="cert_name[]" class="form-control form-control-sm" placeholder="Certificate name *" required></div><div class="col-md-4"><input type="text" name="cert_issuer[]" class="form-control form-control-sm" placeholder="Issuing body"></div><div class="col-md-2"><input type="text" name="cert_status[]" class="form-control form-control-sm" placeholder="Status"></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div></div></div>'>
            <i class="bi bi-plus me-1"></i>Add certification
          </button>
          <div class="mt-3"><button type="submit" class="bf-btn-dark">Save Certifications</button></div>
        </form>
      </div>

      <?php elseif ($tab === 'packages'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:6px;">Service Packages</h2>
        <p style="font-size:.83rem;color:var(--ink-3);margin-bottom:18px;">Create Fiverr-style package tiers (e.g. Basic / Standard / Premium).</p>
        <form method="POST">
          <input type="hidden" name="_tab" value="packages">
          <div id="pkgRepeater">
            <?php foreach ($packages as $pkg): ?>
            <div class="repeater-row">
              <div class="row g-2">
                <div class="col-md-2"><input type="text" name="pkg_tier[]" class="form-control form-control-sm" placeholder="Tier (Basic...)" value="<?= htmlspecialchars($pkg['tier'] ?? '') ?>"></div>
                <div class="col-md-4"><input type="text" name="pkg_title[]" class="form-control form-control-sm" placeholder="Package title *" value="<?= htmlspecialchars($pkg['title']) ?>" required></div>
                <div class="col-md-2"><input type="text" name="pkg_price[]" class="form-control form-control-sm" placeholder="Price" value="<?= htmlspecialchars($pkg['price'] ?? '') ?>"></div>
                <div class="col-md-2"><input type="text" name="pkg_price_unit[]" class="form-control form-control-sm" placeholder="Unit (KES, /day...)" value="<?= htmlspecialchars($pkg['price_unit'] ?? '') ?>"></div>
                <div class="col-md-2 d-flex align-items-center gap-2">
                  <div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="pkg_featured[<?= count($packages) ?>]" <?= !empty($pkg['is_featured']) ? 'checked' : '' ?>><label class="form-check-label" style="font-size:.78rem;">Featured</label></div>
                  <button type="button" class="btn btn-sm btn-light remove-row"><i class="bi bi-trash"></i></button>
                </div>
                <div class="col-12"><textarea name="pkg_description[]" class="form-control form-control-sm" rows="2" placeholder="Description"><?= htmlspecialchars($pkg['description'] ?? '') ?></textarea></div>
                <div class="col-md-3"><input type="text" name="pkg_delivery[]" class="form-control form-control-sm" placeholder="Delivery (e.g. 7 days)" value="<?= htmlspecialchars($pkg['delivery'] ?? '') ?>"></div>
                <div class="col-md-3"><input type="text" name="pkg_revisions[]" class="form-control form-control-sm" placeholder="Revisions (e.g. 2)" value="<?= htmlspecialchars($pkg['revisions'] ?? '') ?>"></div>
                <div class="col-12"><textarea name="pkg_features[]" class="form-control form-control-sm" rows="2" placeholder="Features (one per line)"><?= htmlspecialchars($pkg['features'] ?? '') ?></textarea></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" id="addPkg" class="btn btn-sm" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;"><i class="bi bi-plus me-1"></i>Add package</button>
          <div class="mt-3"><button type="submit" class="bf-btn-dark">Save Packages</button></div>
        </form>
      </div>

      <?php elseif ($tab === 'work_history'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Work History</h2>
        <form method="POST">
          <input type="hidden" name="_tab" value="work_history">
          <div id="whRepeater">
            <?php foreach ($work_hist as $w): ?>
            <div class="repeater-row">
              <div class="row g-2">
                <div class="col-md-4"><input type="text" name="wh_role[]" class="form-control form-control-sm" placeholder="Role / Title *" value="<?= htmlspecialchars($w['role']) ?>" required></div>
                <div class="col-md-4"><input type="text" name="wh_org[]" class="form-control form-control-sm" placeholder="Organization" value="<?= htmlspecialchars($w['organization'] ?? '') ?>"></div>
                <div class="col-md-3"><input type="text" name="wh_period[]" class="form-control form-control-sm" placeholder="Period (e.g. 2020–2022)" value="<?= htmlspecialchars($w['period'] ?? '') ?>"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div>
                <div class="col-12"><textarea name="wh_desc[]" class="form-control form-control-sm" rows="2" placeholder="Responsibilities"><?= htmlspecialchars($w['description'] ?? '') ?></textarea></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn btn-sm add-row-btn" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;" data-target="whRepeater"
                  data-template='<div class="repeater-row"><div class="row g-2"><div class="col-md-4"><input type="text" name="wh_role[]" class="form-control form-control-sm" placeholder="Role / Title *" required></div><div class="col-md-4"><input type="text" name="wh_org[]" class="form-control form-control-sm" placeholder="Organization"></div><div class="col-md-3"><input type="text" name="wh_period[]" class="form-control form-control-sm" placeholder="Period"></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div><div class="col-12"><textarea name="wh_desc[]" class="form-control form-control-sm" rows="2" placeholder="Responsibilities"></textarea></div></div></div>'>
            <i class="bi bi-plus me-1"></i>Add position
          </button>
          <div class="mt-3"><button type="submit" class="bf-btn-dark">Save Work History</button></div>
        </form>
      </div>

      <?php elseif ($tab === 'education'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Education &amp; Training</h2>
        <form method="POST">
          <input type="hidden" name="_tab" value="education">
          <div id="eduRepeater">
            <?php foreach ($education as $e): ?>
            <div class="repeater-row">
              <div class="row g-2">
                <div class="col-md-5"><input type="text" name="edu_title[]" class="form-control form-control-sm" placeholder="Degree / Course *" value="<?= htmlspecialchars($e['title']) ?>" required></div>
                <div class="col-md-6"><input type="text" name="edu_inst[]" class="form-control form-control-sm" placeholder="Institution" value="<?= htmlspecialchars($e['institution'] ?? '') ?>"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn btn-sm add-row-btn" style="background:#eaf0f6;color:#1e3a5f;font-size:.8rem;" data-target="eduRepeater"
                  data-template='<div class="repeater-row"><div class="row g-2"><div class="col-md-5"><input type="text" name="edu_title[]" class="form-control form-control-sm" placeholder="Degree / Course *" required></div><div class="col-md-6"><input type="text" name="edu_inst[]" class="form-control form-control-sm" placeholder="Institution"></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-light remove-row w-100"><i class="bi bi-trash"></i></button></div></div></div>'>
            <i class="bi bi-plus me-1"></i>Add education
          </button>
          <div class="mt-3"><button type="submit" class="bf-btn-dark">Save Education</button></div>
        </form>
      </div>

      <?php elseif ($tab === 'media'): ?>
      <div class="section-card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:18px;">Profile &amp; Cover Photos</h2>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="_tab" value="media">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Profile Photo</label>
              <div class="mb-2">
                <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Current photo" width="64" height="64"
                     style="border-radius:50%;object-fit:cover;border:2px solid var(--line);">
              </div>
              <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
              <div style="font-size:.75rem;color:var(--ink-3);margin-top:4px;">Max 10MB · jpg, png, webp, gif</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Cover Image</label>
              <?php if (!empty($prof['cover_url'])): ?>
                <div class="mb-2"><img src="<?= htmlspecialchars($prof['cover_url']) ?>" alt="Cover" style="height:60px;border-radius:6px;object-fit:cover;max-width:160px;"></div>
              <?php endif; ?>
              <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
              <div style="font-size:.75rem;color:var(--ink-3);margin-top:4px;">Recommended: 1200×400px</div>
            </div>
          </div>
          <button type="submit" class="bf-btn-dark mt-4">Upload Photos</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
</div>
</div>

<script>
// Generic remove-row
document.addEventListener('click', function(e) {
  if (e.target.closest('.remove-row')) {
    var row = e.target.closest('.repeater-row');
    if (row) row.remove();
  }
});
// Generic add-row
document.querySelectorAll('.add-row-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var target = document.getElementById(btn.dataset.target);
    if (target) target.insertAdjacentHTML('beforeend', btn.dataset.template);
  });
});
// Portfolio add
var addPf = document.getElementById('addPortfolio');
if (addPf) {
  addPf.addEventListener('click', function(){
    var r = document.getElementById('portfolioRepeater');
    var idx = r.querySelectorAll('.repeater-row').length;
    r.insertAdjacentHTML('beforeend', `<div class="repeater-row"><div class="row g-2">
      <div class="col-md-5"><input type="text" name="pf_title[]" class="form-control form-control-sm" placeholder="Project title *" required></div>
      <div class="col-md-3"><input type="text" name="pf_category[]" class="form-control form-control-sm" placeholder="Category"></div>
      <div class="col-md-2"><input type="text" name="pf_year[]" class="form-control form-control-sm" placeholder="Year"></div>
      <div class="col-md-2"><button type="button" class="btn btn-sm btn-light remove-row w-100">Remove</button></div>
      <div class="col-md-6"><input type="file" name="pf_image[${idx}]" class="form-control form-control-sm" accept="image/*">
        <input type="hidden" name="pf_image_existing[]" value=""></div>
    </div></div>`);
  });
}
// Package add
var addPkg = document.getElementById('addPkg');
if (addPkg) {
  addPkg.addEventListener('click', function(){
    var r = document.getElementById('pkgRepeater');
    r.insertAdjacentHTML('beforeend', `<div class="repeater-row"><div class="row g-2">
      <div class="col-md-2"><input type="text" name="pkg_tier[]" class="form-control form-control-sm" placeholder="Tier"></div>
      <div class="col-md-4"><input type="text" name="pkg_title[]" class="form-control form-control-sm" placeholder="Package title *" required></div>
      <div class="col-md-2"><input type="text" name="pkg_price[]" class="form-control form-control-sm" placeholder="Price"></div>
      <div class="col-md-2"><input type="text" name="pkg_price_unit[]" class="form-control form-control-sm" placeholder="Unit"></div>
      <div class="col-md-2 d-flex align-items-center gap-2"><button type="button" class="btn btn-sm btn-light remove-row"><i class="bi bi-trash"></i></button></div>
      <div class="col-12"><textarea name="pkg_description[]" class="form-control form-control-sm" rows="2" placeholder="Description"></textarea></div>
      <div class="col-md-3"><input type="text" name="pkg_delivery[]" class="form-control form-control-sm" placeholder="Delivery"></div>
      <div class="col-md-3"><input type="text" name="pkg_revisions[]" class="form-control form-control-sm" placeholder="Revisions"></div>
      <div class="col-12"><textarea name="pkg_features[]" class="form-control form-control-sm" rows="2" placeholder="Features (one per line)"></textarea></div>
    </div></div>`);
  });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
