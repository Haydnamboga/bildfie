<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'All Services';
$nav = 'services';

// Live from the database (catalog)
$verticals = db_all("SELECT * FROM verticals WHERE is_active=1 ORDER BY sort_order, name");
$catsByVert = [];
foreach (db_all("SELECT vertical_id, name FROM categories WHERE is_active=1 ORDER BY sort_order, name") as $row) {
    $catsByVert[$row['vertical_id']][] = $row['name'];
}
shuffle($verticals); // random order on each load (like the homepage)
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<!-- header -->
<div style="background:var(--surface);border-bottom:1px solid var(--line);padding:34px 0 30px;">
  <div class="container" style="max-width:1100px;">
    <div class="bf-section-eyebrow mb-2"><i class="bi bi-grid-3x3-gap" style="color:#c0392b;"></i> Every service, one trusted platform</div>
    <h1 style="font-size:clamp(24px,4vw,34px);font-weight:800;color:var(--ink);margin:0 0 8px;letter-spacing:-.02em;">Browse <?= count($verticals) ?> service verticals</h1>
    <p style="font-size:14px;color:var(--ink-3);margin:0 0 18px;max-width:640px;">From construction to beauty, agriculture to tutoring — find verified providers, agree terms, and pay safely through escrow.</p>
    <form class="bf-searchbar-v2" action="/pages/search.php" method="get" style="max-width:560px;">
      <span class="bf-searchbar-v2-icon"><i class="bi bi-search"></i></span>
      <input type="text" name="q" placeholder="Search any service — plumber, tutor, solar, catering…">
      <button type="submit" class="bf-searchbar-v2-btn">Search</button>
    </form>
    <div class="d-flex flex-wrap gap-3 mt-3" style="font-size:12px;color:var(--ink-3);">
      <span><i class="bi bi-patch-check-fill" style="color:#16a34a;"></i> Verified providers</span>
      <span><i class="bi bi-shield-fill-check" style="color:#1e3a5f;"></i> Escrow-protected</span>
      <span><i class="bi bi-star-fill" style="color:#f59e0b;"></i> Rated &amp; reviewed</span>
      <span><i class="bi bi-globe-africa" style="color:#c0392b;"></i> Across Africa</span>
    </div>
  </div>
</div>

<div class="container" style="max-width:1100px;padding-top:30px;padding-bottom:60px;">
  <div class="row g-3">
    <?php foreach ($verticals as $v): $subs = $catsByVert[$v['id']] ?? []; ?>
    <div class="col-md-6 col-lg-4">
      <a href="<?= htmlspecialchars($v['href'] ?: '#', ENT_QUOTES) ?>" class="bf-vcard">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="bf-vcard-ic" style="background:<?= htmlspecialchars($v['bg'], ENT_QUOTES) ?>;color:<?= htmlspecialchars($v['color'], ENT_QUOTES) ?>;"><i class="bi <?= htmlspecialchars($v['icon'], ENT_QUOTES) ?>"></i></div>
          <div style="min-width:0;">
            <div class="bf-vcard-name"><?= htmlspecialchars($v['name']) ?></div>
            <div class="bf-vcard-count"><?= number_format((int)$v['provider_count']) ?> providers</div>
          </div>
        </div>
        <div>
          <?php foreach ($subs as $s): ?><span class="bf-svc-chip"><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
        </div>
        <div style="font-size:12px;font-weight:700;color:#c0392b;margin-top:10px;">Explore <?= htmlspecialchars(explode(' ', $v['name'])[0]) ?> <i class="bi bi-arrow-right"></i></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- become a provider -->
  <div style="background:linear-gradient(135deg,#1e3a5f,#0d1f36);border-radius:18px;padding:32px;margin-top:28px;display:flex;flex-wrap:wrap;align-items:center;gap:20px;justify-content:space-between;">
    <div>
      <div style="font-size:19px;font-weight:800;color:#fff;">Offer your service on bildfie</div>
      <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:4px;">Join any vertical, get verified, and win work with escrow-protected payments.</div>
    </div>
    <a href="/pages/auth/register.php" class="bf-btn-accent" style="white-space:nowrap;">Become a provider</a>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
