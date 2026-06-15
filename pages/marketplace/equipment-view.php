<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

$name  = trim($_GET['name'] ?? 'CAT 320 Excavator');
$cat   = trim($_GET['cat'] ?? 'Earthmoving');
$img   = filter_var($_GET['img'] ?? '', FILTER_VALIDATE_URL) ? $_GET['img'] : 'https://picsum.photos/id/1072/900/560';
$day   = trim($_GET['day'] ?? 'KES 28,000');
$week  = trim($_GET['week'] ?? 'KES 140,000');
$loc   = trim($_GET['loc'] ?? 'Nairobi');
$rat   = trim($_GET['rat'] ?? '4.8');
$hires = (int)($_GET['hires'] ?? 36);

$specs = [
  [trim($_GET['k1'] ?? 'Operating weight'), trim($_GET['v1'] ?? '20 tonne')],
  [trim($_GET['k2'] ?? 'Engine power'),     trim($_GET['v2'] ?? '122 kW')],
  [trim($_GET['k3'] ?? 'Bucket'),           trim($_GET['v3'] ?? '0.9 m³')],
  [trim($_GET['k4'] ?? 'Max dig depth'),    trim($_GET['v4'] ?? '6.7 m')],
  ['Year', '2021'], ['Fuel', 'Diesel'],
];
$included = ['Trained, certified operator','Delivery & collection within 30km','Full insurance cover','Daily fuel top-up service','24/7 breakdown support'];
$page_title = $name . ' — Hire';
$nav = 'equipment';
$ctx = htmlspecialchars($name, ENT_QUOTES);
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);padding:22px 0 48px;">
  <div class="container" style="max-width:1080px;">

    <div style="font-size:12px;color:var(--ink-4);margin-bottom:14px;">
      <a href="/pages/marketplace/equipment.php" style="color:var(--ink-3);text-decoration:none;">Equipment</a>
      <i class="bi bi-chevron-right" style="font-size:9px;"></i> <span style="color:var(--ink-2);"><?= htmlspecialchars($name) ?></span>
    </div>

    <div class="row g-3">
      <!-- LEFT -->
      <div class="col-lg-7">
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;overflow:hidden;position:relative;margin-bottom:12px;">
          <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($name) ?>" style="width:100%;height:360px;object-fit:cover;display:block;">
          <button class="bf-fav" data-fav="eq:<?= urlencode($name) ?>" title="Save" style="position:absolute;top:14px;right:14px;"><i class="bi bi-heart"></i></button>
          <span style="position:absolute;top:14px;left:14px;font-size:10px;font-weight:800;background:#dcfce7;color:#166534;padding:4px 10px;border-radius:6px;">AVAILABLE</span>
        </div>

        <div class="bf-pf-card">
          <div class="bf-pf-card-b">
            <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#c0392b;"><?= htmlspecialchars($cat) ?></div>
            <h1 style="font-size:24px;font-weight:900;color:var(--ink);margin:6px 0 8px;letter-spacing:-.02em;"><?= htmlspecialchars($name) ?></h1>
            <div style="font-size:12px;color:var(--ink-3);"><i class="bi bi-geo-alt-fill" style="color:#c0392b;"></i> <?= htmlspecialchars($loc) ?> · <i class="bi bi-star-fill" style="color:#f59e0b;"></i> <?= htmlspecialchars($rat) ?> · <?= $hires ?> hires</div>
          </div>
        </div>

        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-list-check"></i> Specifications</div></div>
          <div class="bf-pf-card-b">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 24px;">
              <?php foreach ($specs as [$k,$v]): ?>
              <div class="bf-pf-contact" style="justify-content:space-between;"><span style="color:var(--ink-3);"><?= htmlspecialchars($k) ?></span><span style="font-weight:700;color:var(--ink);"><?= htmlspecialchars($v) ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="bf-pf-card">
          <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-check2-circle"></i> What's included</div></div>
          <div class="bf-pf-card-b">
            <?php foreach ($included as $inc): ?>
            <div style="display:flex;gap:9px;font-size:12.5px;color:var(--ink-2);padding:5px 0;"><i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:13px;margin-top:2px;"></i><?= $inc ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-lg-5">
        <div style="position:sticky;top:80px;">
          <div class="bf-pf-card">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-calendar-check"></i> Hire this machine</div></div>
            <form class="js-engage-form">
              <div class="bf-pf-card-b">
                <!-- rates -->
                <div style="background:var(--surface);border-radius:10px;padding:12px 14px;margin-bottom:16px;">
                  <?php foreach ([['Daily',$day],['Weekly (5 days)',$week],['Monthly','POA']] as [$rk,$rv]): ?>
                  <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:3px 0;"><span style="color:var(--ink-3);"><?= $rk ?></span><span style="font-weight:800;color:var(--ink);"><?= htmlspecialchars($rv) ?></span></div>
                  <?php endforeach; ?>
                </div>
                <div class="row g-2">
                  <div class="col-6"><label class="bf-f-lbl">From</label><input type="date" class="bf-f-input"></div>
                  <div class="col-6"><label class="bf-f-lbl">To</label><input type="date" class="bf-f-input"></div>
                </div>
                <label class="bf-f-lbl mt-2">Delivery site</label>
                <input class="bf-f-input" placeholder="Site address / area" value="<?= htmlspecialchars($loc, ENT_QUOTES) ?>">
                <label class="bf-f-lbl mt-2" style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" checked style="width:15px;height:15px;accent-color:#1e3a5f;"> Include certified operator</label>
              </div>
              <div class="bf-pf-card-b" style="padding-top:0;">
                <button type="submit" class="bf-btn-accent" style="width:100%;border:none;cursor:pointer;"><i class="bi bi-calendar-check me-1"></i>Request hire</button>
                <button type="button" class="bf-btn-ghost" data-engage="quote" data-ctx="<?= $ctx ?>" style="width:100%;margin-top:8px;">Ask a question</button>
                <div style="font-size:10.5px;color:var(--ink-4);margin-top:10px;text-align:center;"><i class="bi bi-shield-fill-check" style="color:#16a34a;"></i> Escrow-protected · free cancellation 24h before</div>
              </div>
            </form>
            <div class="bf-modal-success js-engage-success">
              <div class="bf-modal-success-ic"><i class="bi bi-check-lg"></i></div>
              <div class="bf-modal-success-t">Hire request sent</div>
              <div class="bf-modal-success-s">The owner will confirm availability and delivery shortly. Track it under Messages.</div>
              <a href="/pages/dashboard/messages.php" class="bf-btn-navy" style="text-decoration:none;">Go to messages</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
