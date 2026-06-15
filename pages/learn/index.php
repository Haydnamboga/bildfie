<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Learn & Earn';
$nav = 'learn';

// Live from the database (catalog)
$flags = ['KE'=>'🇰🇪','TZ'=>'🇹🇿','UG'=>'🇺🇬','RW'=>'🇷🇼','NG'=>'🇳🇬','GH'=>'🇬🇭','ZA'=>'🇿🇦'];
$regions = db_all("SELECT code,name FROM regions WHERE is_active=1 ORDER BY id");
$levels  = db_all("SELECT * FROM learn_levels WHERE is_active=1 ORDER BY sort_order, id");
$subsByLevel = [];
foreach (db_all("SELECT level_id, name FROM learn_subjects ORDER BY sort_order, id") as $r) {
    $subsByLevel[$r['level_id']][] = $r['name'];
}

$library = [
  ['Past Papers','bi-file-earmark-text','#1e40af','#eff6ff','120,000+ papers · KCPE, KCSE, exams'],
  ['Revision Notes','bi-journal-bookmark','#166534','#f0fdf4','Topic notes & summaries, all levels'],
  ['Video Tutorials','bi-play-btn','#c0392b','#fef2f2','Watch & learn — bite-size lessons'],
  ['Projects & Assignments','bi-clipboard-check','#b45309','#fffbeb','Templates, samples & help on demand'],
  ['Research & Theses','bi-mortarboard','#7c3aed','#f5f0ff','Proposals, data analysis, dissertations'],
  ['Fun & Activities','bi-emoji-laughing','#f59e0b','#fffbeb','Games & worksheets for little ones'],
];

$tutors = [
  ['Esther Njeri','Mathematics & Physics','women/22','4.9','KES 600/hr'],
  ['Brian Otieno','English & Kiswahili','men/41','4.8','KES 500/hr'],
  ['Dr. Aisha Said','Research & Thesis','women/40','5.0','KES 1,500/hr'],
  ['Kevin Mwangi','Coding & ICT','men/67','4.7','KES 800/hr'],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<!-- hero -->
<div style="background:linear-gradient(135deg,#0d1f36,#1e3a5f);padding:40px 0 36px;color:#fff;">
  <div class="container" style="max-width:1100px;">
    <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#ffd97a;margin-bottom:10px;">— bildfie Learn</div>
    <h1 style="font-size:clamp(26px,4.5vw,40px);font-weight:800;letter-spacing:-.02em;margin:0 0 10px;line-height:1.1;">Learn, study &amp; earn —<br>for every level, across Africa.</h1>
    <p style="font-size:14.5px;color:rgba(255,255,255,.75);max-width:660px;margin:0 0 20px;">Homework, exams, projects, research and thesis help — plus a huge content library. Get matched to verified tutors and experts, or <strong style="color:#fff;">earn as one</strong>. All payments escrow-protected.</p>
    <div class="d-flex flex-wrap gap-2">
      <button class="bf-btn-accent" data-engage="quote" data-ctx="academic help" style="border:none;cursor:pointer;"><i class="bi bi-pencil-square me-1"></i>Post your task — get help</button>
      <a href="/pages/auth/register.php" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);color:#fff;font-size:13px;font-weight:700;padding:11px 20px;border-radius:10px;text-decoration:none;"><i class="bi bi-cash-coin me-1"></i>Earn as a tutor</a>
    </div>
    <!-- region selector -->
    <div style="margin-top:24px;">
      <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Curriculum &amp; region</div>
      <div class="d-flex flex-wrap gap-2" id="regionTabs">
        <?php foreach ($regions as $i=>$rg): ?>
        <button class="bf-region-tab <?= $i===0?'active':'' ?>" style="<?= $i===0?'':'background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.85);' ?>"><?= $flags[$rg['code']] ?? '🌍' ?> <?= htmlspecialchars($rg['name']) ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="container" style="max-width:1100px;padding-top:32px;padding-bottom:60px;">

  <!-- academic levels -->
  <div class="bf-section-eyebrow mb-2"><i class="bi bi-mortarboard" style="color:#c0392b;"></i> By academic level</div>
  <h2 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 16px;letter-spacing:-.01em;">Pick your level</h2>
  <div class="row g-3 mb-5">
    <?php foreach ($levels as $lv): $types = $subsByLevel[$lv['id']] ?? []; ?>
    <div class="col-sm-6 col-lg-4">
      <a href="#" class="bf-level">
        <div class="bf-level-head" style="background:<?= htmlspecialchars($lv['gradient'], ENT_QUOTES) ?>;">
          <div class="bf-level-emoji"><?= htmlspecialchars($lv['emoji']) ?></div>
          <div class="bf-level-name"><?= htmlspecialchars($lv['name']) ?></div>
          <div class="bf-level-age"><?= htmlspecialchars($lv['age_label']) ?></div>
        </div>
        <div class="bf-level-body">
          <div style="margin-bottom:10px;"><?php foreach ($types as $t): ?><span class="bf-svc-chip"><?= htmlspecialchars($t) ?></span><?php endforeach; ?></div>
          <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--line-2);padding-top:10px;">
            <span style="font-size:11px;color:var(--ink-4);"><?= number_format((int)$lv['resource_count']) ?> resources</span>
            <span style="font-size:12px;font-weight:700;color:#c0392b;">Open <i class="bi bi-arrow-right"></i></span>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- content library -->
  <div class="bf-section-eyebrow mb-2"><i class="bi bi-collection" style="color:#c0392b;"></i> Content library</div>
  <h2 style="font-size:22px;font-weight:800;color:var(--ink);margin:0 0 16px;letter-spacing:-.01em;">Study material &amp; resources</h2>
  <div class="row g-3 mb-5">
    <?php foreach ($library as [$n,$ic,$c,$bg,$desc]): ?>
    <div class="col-sm-6 col-lg-4">
      <a href="#" class="bf-vcard" style="display:flex;gap:14px;align-items:flex-start;">
        <div class="bf-vcard-ic" style="background:<?=$bg?>;color:<?=$c?>;flex-shrink:0;"><i class="bi <?=$ic?>"></i></div>
        <div><div class="bf-vcard-name"><?=$n?></div><div style="font-size:11.5px;color:var(--ink-3);margin-top:3px;line-height:1.5;"><?=$desc?></div></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- two-sided: get help / earn -->
  <div class="row g-3 mb-5">
    <div class="col-md-6">
      <div style="background:#eaf0f6;border:1px solid #d6e2ee;border-radius:16px;padding:24px;height:100%;">
        <div style="font-size:24px;">🙋</div>
        <div style="font-size:17px;font-weight:800;color:#1e3a5f;margin-top:8px;">Need help with your work?</div>
        <p style="font-size:13px;color:var(--ink-3);line-height:1.6;margin:6px 0 14px;">Post an assignment, exam revision, project or thesis — get bids from verified tutors and experts, pick the best, and pay safely via escrow.</p>
        <button class="bf-btn-accent" data-engage="quote" data-ctx="academic help" style="border:none;cursor:pointer;"><i class="bi bi-pencil-square me-1"></i>Post your task</button>
      </div>
    </div>
    <div class="col-md-6">
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;padding:24px;height:100%;">
        <div style="font-size:24px;">💸</div>
        <div style="font-size:17px;font-weight:800;color:#166534;margin-top:8px;">Earn as a tutor or expert</div>
        <p style="font-size:13px;color:var(--ink-3);line-height:1.6;margin:6px 0 14px;">Teach, tutor, write or research. Set your rates, build your reputation, and get paid on time — bildfie handles matching and payments.</p>
        <a href="/pages/auth/register.php" style="background:#166534;color:#fff;font-size:13px;font-weight:700;padding:11px 20px;border-radius:10px;text-decoration:none;display:inline-block;"><i class="bi bi-rocket-takeoff me-1"></i>Start earning</a>
      </div>
    </div>
  </div>

  <!-- featured tutors -->
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div><div class="bf-section-eyebrow"><i class="bi bi-stars" style="color:#c0392b;"></i> Top rated</div><h2 style="font-size:22px;font-weight:800;color:var(--ink);margin:4px 0 0;letter-spacing:-.01em;">Featured tutors &amp; experts</h2></div>
    <a href="/pages/marketplace/professionals.php" style="font-size:13px;font-weight:700;color:#c0392b;text-decoration:none;">All tutors →</a>
  </div>
  <div class="row g-3">
    <?php foreach ($tutors as [$n,$subj,$ph,$rt,$rate]): ?>
    <div class="col-6 col-lg-3">
      <a href="/pages/marketplace/professional.php?name=<?= urlencode($n) ?>&role=<?= urlencode($subj.' Tutor') ?>&photo=<?= urlencode('https://randomuser.me/api/portraits/'.$ph.'.jpg') ?>&rating=<?= $rt ?>&rate=<?= urlencode($rate) ?>" class="bf-vcard" style="text-align:center;">
        <img src="https://randomuser.me/api/portraits/<?= $ph ?>.jpg" alt="<?= $n ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin:0 auto 10px;display:block;">
        <div style="font-size:13.5px;font-weight:800;color:var(--ink);"><?= $n ?></div>
        <div style="font-size:11.5px;color:var(--ink-3);"><?= $subj ?></div>
        <div style="font-size:11px;margin-top:6px;"><i class="bi bi-star-fill" style="color:#f59e0b;"></i> <strong><?= $rt ?></strong> · <span style="color:#c0392b;font-weight:700;"><?= $rate ?></span></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
document.querySelectorAll('#regionTabs .bf-region-tab').forEach(function(t){
  t.addEventListener('click', function(){
    document.querySelectorAll('#regionTabs .bf-region-tab').forEach(function(x){ x.classList.remove('active'); x.style.cssText='background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.85);'; });
    t.classList.add('active'); t.style.cssText='';
  });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
