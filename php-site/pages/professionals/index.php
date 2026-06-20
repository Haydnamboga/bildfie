<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';

$nav        = 'marketplace';
$page_title = 'Find Professionals';

// Search & filter params
$q        = trim($_GET['q'] ?? '');
$trade    = trim($_GET['trade'] ?? '');
$location = trim($_GET['location'] ?? '');
$min_rate = trim($_GET['min_rate'] ?? '');
$max_rate = trim($_GET['max_rate'] ?? '');
$avail    = isset($_GET['available']) ? 1 : 0;
$verified = isset($_GET['verified']) ? 1 : 0;
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset   = ($page_num - 1) * $per_page;

// Build query
$where = ["p.status = 'active'"];
$params = [];

if ($q !== '') {
    $where[] = "(p.name LIKE ? OR p.headline LIKE ? OR ps.skill LIKE ?)";
    $lq = "%$q%";
    array_push($params, $lq, $lq, $lq);
}
if ($trade !== '') {
    $where[] = "p.headline LIKE ?";
    $params[] = "%$trade%";
}
if ($location !== '') {
    $where[] = "p.location LIKE ?";
    $params[] = "%$location%";
}
if ($avail) {
    $where[] = "p.is_available = 1";
}
if ($verified) {
    $where[] = "p.is_verified = 1";
}

$where_sql = 'WHERE ' . implode(' AND ', $where);

$count_sql = "SELECT COUNT(DISTINCT p.id) FROM providers p LEFT JOIN provider_skills ps ON ps.provider_id = p.id $where_sql";
$total = (int) db_value($count_sql, $params);
$pages = max(1, ceil($total / $per_page));

$sql = "SELECT DISTINCT p.id, p.public_id, p.name, p.headline, p.location, p.photo_url, p.day_rate, p.rating, p.reviews_count, p.is_verified, p.is_featured, p.is_available
        FROM providers p
        LEFT JOIN provider_skills ps ON ps.provider_id = p.id
        $where_sql
        ORDER BY p.is_featured DESC, p.rating DESC, p.reviews_count DESC
        LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

$providers = db_all($sql, $params);

// Skills for each provider
foreach ($providers as &$prov) {
    $prov['skills'] = array_column(db_all("SELECT skill FROM provider_skills WHERE provider_id=? ORDER BY sort_order LIMIT 5", [(int)$prov['id']]), 'skill');
}
unset($prov);

$categories = ['Architect', 'Civil Engineer', 'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Mason', 'Welder', 'Solar Installer', 'Interior Designer', 'Quantity Surveyor', 'Structural Engineer', 'Site Foreman', 'Landscaper', 'Tiler'];

function star_html(float $r): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<i class="bi bi-star' . ($i <= $r ? '-fill' : ($i - 0.5 <= $r ? '-half' : '')) . '" style="color:#c9a84c;font-size:.75rem;"></i>';
    }
    return $out;
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

<style>
.bf-mkt-hero { background:linear-gradient(135deg,#1e3a5f 0%,#0d0d0d 100%);padding:56px 0 40px;color:#fff; }
.bf-mkt-hero h1 { font-size:2rem;font-weight:800;letter-spacing:-.04em;margin-bottom:8px; }
.bf-filter-card { background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px; }
.bf-pro-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px; }
</style>

<!-- Hero + search -->
<section class="bf-mkt-hero">
  <div class="container">
    <div class="text-center mb-5">
      <h1>Find Construction Professionals</h1>
      <p style="font-size:1rem;color:rgba(255,255,255,.7);max-width:480px;margin:0 auto;">
        Verified architects, engineers, contractors and tradespeople across Africa.
      </p>
    </div>
    <form method="GET" action="">
      <div class="bf-searchbar d-flex gap-2 flex-wrap justify-content-center">
        <input type="text" name="q" class="form-control" style="max-width:400px;border-radius:10px;border:none;"
               placeholder='Search by name, skill, trade...' value="<?= htmlspecialchars($q) ?>">
        <input type="text" name="location" class="form-control" style="max-width:220px;border-radius:10px;border:none;"
               placeholder="Location (e.g. Nairobi)" value="<?= htmlspecialchars($location) ?>">
        <button type="submit" class="bf-btn-dark" style="padding:10px 24px;">Search</button>
      </div>
    </form>

    <!-- Category pills -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
      <?php foreach ($categories as $cat): ?>
        <a href="?q=<?= urlencode($cat) ?>" class="bf-pill <?= $q === $cat ? 'active' : '' ?>">
          <?= htmlspecialchars($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4">
    <!-- Filter sidebar -->
    <div class="col-12 col-lg-3">
      <form method="GET" action="">
        <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
        <div class="bf-filter-card">
          <h6 style="font-weight:700;font-size:.85rem;margin-bottom:16px;">Filters</h6>

          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Trade / Specialisation</label>
            <select name="trade" class="form-select form-select-sm">
              <option value="">Any trade</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $trade === $cat ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Location</label>
            <input type="text" name="location" class="form-control form-control-sm"
                   placeholder="City or region" value="<?= htmlspecialchars($location) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Day Rate (KES)</label>
            <div class="d-flex gap-2">
              <input type="number" name="min_rate" class="form-control form-control-sm" placeholder="Min" value="<?= htmlspecialchars($min_rate) ?>">
              <input type="number" name="max_rate" class="form-control form-control-sm" placeholder="Max" value="<?= htmlspecialchars($max_rate) ?>">
            </div>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="fc_avail" name="available" <?= $avail ? 'checked' : '' ?>>
            <label class="form-check-label" for="fc_avail" style="font-size:.85rem;">Available now</label>
          </div>

          <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="fc_verified" name="verified" <?= $verified ? 'checked' : '' ?>>
            <label class="form-check-label" for="fc_verified" style="font-size:.85rem;">Verified only</label>
          </div>

          <button type="submit" class="bf-btn-dark w-100" style="justify-content:center;">Apply Filters</button>
          <a href="/pages/professionals/" class="btn btn-light w-100 mt-2" style="font-size:.85rem;">Clear All</a>
        </div>
      </form>
    </div>

    <!-- Results -->
    <div class="col-12 col-lg-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <span style="font-size:.875rem;color:var(--ink-3);">
          <?= number_format($total) ?> professional<?= $total !== 1 ? 's' : '' ?> found
          <?= $q ? ' for <strong>' . htmlspecialchars($q) . '</strong>' : '' ?>
        </span>
      </div>

      <?php if (empty($providers)): ?>
        <div class="text-center py-5">
          <i class="bi bi-search" style="font-size:3rem;color:var(--line);display:block;margin-bottom:16px;"></i>
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">No results found</h3>
          <p style="color:var(--ink-3);font-size:.875rem;">Try different keywords or remove some filters.</p>
          <a href="/pages/professionals/" class="bf-btn-outline mt-2">Reset search</a>
        </div>
      <?php else: ?>
        <div class="bf-pro-grid">
          <?php foreach ($providers as $prov): ?>
            <div class="bf-pro-card">
              <div class="bf-pro-card-head" style="background:linear-gradient(135deg,#1e3a5f,#0d0d0d);">
                <?php if ($prov['is_featured']): ?>
                  <span class="position-absolute" style="top:10px;right:10px;background:#c9a84c;color:#fff;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.05em;">FEATURED</span>
                <?php endif; ?>
              </div>
              <div class="bf-pro-avatar">
                <img src="<?= htmlspecialchars($prov['photo_url'] ?: user_avatar(['name'=>$prov['name']], 80)) ?>"
                     alt="<?= htmlspecialchars($prov['name']) ?>"
                     width="64" height="64"
                     style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.12);">
              </div>
              <div style="padding:40px 18px 18px;text-align:center;">
                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                  <h3 style="font-size:.95rem;font-weight:700;margin:0;color:#0d0d0d;">
                    <a href="/pages/professionals/profile.php?id=<?= urlencode($prov['public_id']) ?>"
                       style="color:inherit;text-decoration:none;">
                      <?= htmlspecialchars($prov['name']) ?>
                    </a>
                  </h3>
                  <?php if ($prov['is_verified']): ?>
                    <i class="bi bi-patch-check-fill" style="color:#1e3a5f;font-size:.85rem;" title="Verified"></i>
                  <?php endif; ?>
                </div>

                <?php if ($prov['headline']): ?>
                  <p style="font-size:.8rem;color:var(--ink-3);margin:0 0 6px;"><?= htmlspecialchars($prov['headline']) ?></p>
                <?php endif; ?>

                <?php if ($prov['location']): ?>
                  <p style="font-size:.75rem;color:var(--ink-4);margin:0 0 8px;">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($prov['location']) ?>
                  </p>
                <?php endif; ?>

                <?php if ($prov['rating'] > 0): ?>
                  <div class="d-flex align-items-center justify-content-center gap-1 mb-8">
                    <?= star_html((float)$prov['rating']) ?>
                    <span style="font-size:.75rem;color:var(--ink-3);"><?= number_format((float)$prov['rating'], 1) ?> (<?= (int)$prov['reviews_count'] ?>)</span>
                  </div>
                <?php endif; ?>

                <?php if ($prov['day_rate']): ?>
                  <div style="font-size:.875rem;font-weight:700;color:#1e3a5f;margin-bottom:10px;"><?= htmlspecialchars($prov['day_rate']) ?></div>
                <?php endif; ?>

                <!-- Skills -->
                <?php if (!empty($prov['skills'])): ?>
                  <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                    <?php foreach (array_slice($prov['skills'], 0, 3) as $sk): ?>
                      <span class="bf-pill" style="font-size:.7rem;padding:2px 8px;"><?= htmlspecialchars($sk) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($prov['skills']) > 3): ?>
                      <span class="bf-pill" style="font-size:.7rem;padding:2px 8px;background:#f4f4f2;">+<?= count($prov['skills']) - 3 ?></span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <a href="/pages/professionals/profile.php?id=<?= urlencode($prov['public_id']) ?>"
                   class="bf-btn-dark w-100" style="justify-content:center;font-size:.83rem;padding:8px;">
                  View Profile
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
          <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination pagination-sm mb-0">
              <?php if ($page_num > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num - 1])) ?>">
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>
              <?php endif; ?>
              <?php for ($i = max(1, $page_num - 2); $i <= min($pages, $page_num + 2); $i++): ?>
                <li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($page_num < $pages): ?>
                <li class="page-item">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num + 1])) ?>">
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
