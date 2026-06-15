<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';

$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$page_title = $q !== '' ? "Search: $q" : 'Search';

// Demo result sets (a real build would query a DB by $q / $cat).
$pros = [
  ['John Mwangi','Structural Engineer','Nairobi, Kenya','KES 8,500/day','4.9','127','https://randomuser.me/api/portraits/men/32.jpg'],
  ['Amina Osei','Architect','Accra, Ghana','GHS 1,200/day','4.9','93','https://randomuser.me/api/portraits/women/44.jpg'],
  ['Samuel Otieno','MEP Engineer','Nairobi, Kenya','KES 10,000/day','4.6','56','https://randomuser.me/api/portraits/men/54.jpg'],
];
$mats = [
  ['Portland Cement 50kg','KES 1,050','Nairobi Builders Hub'],
  ['Y12 Deformed Bar','KES 118/kg','Steel Masters'],
  ['Roofing Sheet (G28)','KES 950/m','Mabati Rolling'],
];
$equip = [
  ['CAT 320 Excavator','Earthmoving','KES 28,000','https://picsum.photos/id/1072/600/360'],
  ['Liebherr Tower Crane','Lifting','KES 75,000','https://picsum.photos/id/177/600/360'],
  ['Dynapac Compactor','Compaction','KES 14,000','https://picsum.photos/id/1062/600/360'],
];
$bids = [
  ['4-Storey Apartment — Structural & Finishing','Kilimani, Nairobi','KES 48,000–65,000','commercial'],
  ['Architectural Design — 5-Bed Villa','Palm Jumeirah, UAE','KES 22,000–30,000','residential'],
  ['Road Construction — 12km Murram','Nakuru, Kenya','KES 280,000+','infrastructure'],
];

$show = fn($c) => $cat === '' || strcasecmp($cat, $c) === 0;
$totalCats = count(array_filter(['Professionals','Materials','Equipment','Facilities'], fn($c)=>$show($c))) ;
$nav = '';
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Search header -->
<div style="background:var(--surface);border-bottom:1px solid var(--line);padding:30px 0 26px;">
  <div class="container" style="max-width:1080px;">
    <div class="bf-section-eyebrow mb-2"><i class="bi bi-search" style="color:#c0392b;"></i> Search</div>
    <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 14px;">
      <?php if ($q !== ''): ?>Results for &ldquo;<?= htmlspecialchars($q) ?>&rdquo;<?php else: ?>Search bildfie<?php endif; ?>
    </h1>
    <form class="bf-searchbar-v2" action="/pages/search.php" method="get" style="max-width:620px;">
      <span class="bf-searchbar-v2-icon"><i class="bi bi-search"></i></span>
      <input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" placeholder="Professionals, materials, equipment, projects…">
      <div class="bf-searchbar-v2-divider"></div>
      <select name="cat">
        <?php foreach (['' =>'All Categories','Professionals'=>'Professionals','Materials'=>'Materials','Equipment'=>'Equipment','Facilities'=>'Facilities'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= strcasecmp($cat,$v)===0?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="bf-searchbar-v2-btn">Search</button>
    </form>
  </div>
</div>

<div class="container" style="max-width:1080px;padding-top:30px;padding-bottom:60px;">

  <?php if ($show('Professionals')): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h2 style="font-size:16px;font-weight:800;color:var(--ink);margin:0;"><i class="bi bi-people me-2" style="color:#1e3a5f;"></i>Professionals</h2>
    <a href="/pages/marketplace/professionals.php" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">See all →</a>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach ($pros as [$name,$role,$loc,$rate,$rt,$rv,$photo]): ?>
    <div class="col-md-4">
      <a href="/pages/marketplace/professional.php?name=<?= urlencode($name) ?>&role=<?= urlencode($role) ?>&loc=<?= urlencode($loc) ?>&rate=<?= urlencode($rate) ?>&rating=<?= urlencode($rt) ?>&reviews=<?= urlencode($rv) ?>&photo=<?= urlencode($photo) ?>" style="text-decoration:none;display:flex;gap:12px;align-items:center;background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px;height:100%;">
        <img src="<?= $photo ?>" alt="<?= $name ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <div style="min-width:0;"><div style="font-size:13px;font-weight:800;color:var(--ink);"><?= $name ?></div><div style="font-size:11px;color:var(--ink-3);"><?= $role ?></div><div style="font-size:11px;color:#c0392b;font-weight:700;margin-top:2px;"><?= $rate ?> · <i class="bi bi-star-fill" style="color:#f59e0b;font-size:9px;"></i> <?= $rt ?></div></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($show('Materials')): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h2 style="font-size:16px;font-weight:800;color:var(--ink);margin:0;"><i class="bi bi-box-seam me-2" style="color:#1e3a5f;"></i>Materials</h2>
    <a href="/pages/marketplace/materials.php" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">See all →</a>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach ($mats as [$pn,$pp,$sup]): ?>
    <div class="col-md-4">
      <a href="/pages/marketplace/material.php?name=<?= urlencode($pn) ?>&price=<?= urlencode($pp) ?>&supplier=<?= urlencode($sup) ?>" style="text-decoration:none;display:block;background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px;height:100%;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);"><?= $pn ?></div>
        <div style="font-size:11px;color:var(--ink-4);margin-top:2px;"><i class="bi bi-shop" style="font-size:10px;"></i> <?= $sup ?></div>
        <div style="font-size:15px;font-weight:900;color:#c0392b;margin-top:6px;"><?= $pp ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($show('Equipment')): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h2 style="font-size:16px;font-weight:800;color:var(--ink);margin:0;"><i class="bi bi-truck me-2" style="color:#1e3a5f;"></i>Equipment</h2>
    <a href="/pages/marketplace/equipment.php" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">See all →</a>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach ($equip as [$en,$ec,$ed,$ei]): ?>
    <div class="col-md-4">
      <a href="/pages/marketplace/equipment-view.php?name=<?= urlencode($en) ?>&cat=<?= urlencode($ec) ?>&day=<?= urlencode($ed) ?>&img=<?= urlencode($ei) ?>" style="text-decoration:none;display:block;background:var(--white);border:1px solid var(--line);border-radius:12px;overflow:hidden;height:100%;">
        <img src="<?= $ei ?>" alt="<?= $en ?>" style="width:100%;height:120px;object-fit:cover;display:block;">
        <div style="padding:12px 14px;"><div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#c0392b;"><?= $ec ?></div><div style="font-size:13px;font-weight:800;color:var(--ink);margin-top:2px;"><?= $en ?></div><div style="font-size:12px;font-weight:800;color:var(--ink);margin-top:4px;"><?= $ed ?><span style="font-size:10px;color:var(--ink-4);font-weight:600;">/day</span></div></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($cat === '' || strcasecmp($cat,'Facilities')===0): // Bids shown when broad search ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h2 style="font-size:16px;font-weight:800;color:var(--ink);margin:0;"><i class="bi bi-megaphone me-2" style="color:#1e3a5f;"></i>Open projects to bid on</h2>
    <a href="/pages/bids/index.php" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">See all →</a>
  </div>
  <div class="row g-3">
    <?php foreach ($bids as [$bt,$bl,$bb,$bty]): ?>
    <div class="col-md-4">
      <a href="/pages/bids/view.php?title=<?= urlencode($bt) ?>&loc=<?= urlencode($bl) ?>&budget=<?= urlencode($bb) ?>&type=<?= urlencode($bty) ?>" style="text-decoration:none;display:block;background:var(--white);border:1px solid var(--line);border-radius:12px;padding:16px;height:100%;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);line-height:1.35;"><?= $bt ?></div>
        <div style="font-size:11px;color:var(--ink-4);margin-top:6px;"><i class="bi bi-geo-alt-fill" style="color:#c0392b;font-size:9px;"></i> <?= $bl ?></div>
        <div style="font-size:13px;font-weight:800;color:#c0392b;margin-top:6px;"><?= $bb ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/scripts.php'; ?>
