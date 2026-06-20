<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/profile_data.php';
require_once __DIR__ . '/../../config/engage.php';
require_once __DIR__ . '/../../config/provider_badges.php';

$key = trim($_GET['id'] ?? '');
if ($key === '') { header('Location: /pages/professionals/'); exit; }

// Resolve provider
$provider = is_numeric($key)
    ? db_one("SELECT * FROM providers WHERE id=? LIMIT 1", [(int)$key])
    : db_one("SELECT * FROM providers WHERE public_id=? LIMIT 1", [$key]);

if (!$provider || $provider['status'] !== 'active') {
    http_response_code(404);
    include __DIR__ . '/../../includes/head.php';
    echo '<div class="container py-5 text-center"><h1>Profile not found</h1><a href="/pages/professionals/" class="bf-btn-dark mt-3">Browse professionals</a></div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$pid  = (int) $provider['id'];
$uid  = (int) $provider['user_id'];

// Data
$prof        = db_one("SELECT * FROM user_professions WHERE user_id=?", [$uid]) ?? [];
$skills      = user_skills($uid);
$portfolio   = user_portfolio($uid);
$packages    = user_packages($uid);
$certs       = user_certifications($uid);
$work_hist   = user_work_history($uid);
$education   = user_education($uid);
$areas       = user_areas($uid);
$reviews     = provider_reviews($pid, 20);
$rev_stats   = user_review_stats($uid);
$badges      = provider_badges($provider);
$tab         = $_GET['tab'] ?? 'about';

$page_title = htmlspecialchars($provider['name']) . ' · bildfie';
$nav        = 'marketplace';

function star_html2(float $r, string $size = '.9rem'): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $full = $i <= floor($r);
        $half = !$full && ($i - 0.5) <= $r;
        $out .= '<i class="bi bi-star' . ($full ? '-fill' : ($half ? '-half' : '')) . '" style="color:#c9a84c;font-size:' . $size . ';"></i>';
    }
    return $out;
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

<style>
.pf-cover { height:220px;background:linear-gradient(135deg,#1e3a5f,#0d0d0d);position:relative;overflow:hidden; }
.pf-cover img { width:100%;height:100%;object-fit:cover; }
.pf-avatar-wrap { margin-top:-52px;padding-left:28px; }
.pf-avatar { width:96px;height:96px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 2px 12px rgba(0,0,0,.15); }
.pf-tab { font-size:.875rem;font-weight:600;color:var(--ink-3);padding:10px 16px;border-bottom:2px solid transparent;cursor:pointer;text-decoration:none; }
.pf-tab.active { color:#1e3a5f;border-bottom-color:#1e3a5f; }
.pf-section-title { font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-3);margin-bottom:12px; }
.pkg-card { border:1px solid var(--line);border-radius:12px;padding:20px;height:100%; }
.pkg-card.featured { border-color:#1e3a5f;background:#eaf0f6; }
</style>

<!-- Cover -->
<div class="pf-cover">
  <?php if (!empty($prof['cover_url'])): ?>
    <img src="<?= htmlspecialchars($prof['cover_url']) ?>" alt="Cover">
  <?php endif; ?>
</div>

<div class="container">
  <!-- Avatar + name row -->
  <div class="row align-items-end" style="margin-top:-52px;padding-bottom:0;">
    <div class="col-auto">
      <img src="<?= htmlspecialchars($provider['photo_url'] ?: user_avatar(['name'=>$provider['name']], 96)) ?>"
           alt="<?= htmlspecialchars($provider['name']) ?>"
           class="pf-avatar" style="margin-top:0;display:block;">
    </div>
    <div class="col" style="padding-bottom:12px;padding-top:56px;">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h1 style="font-size:1.5rem;font-weight:800;margin:0;color:#0d0d0d;"><?= htmlspecialchars($provider['name']) ?></h1>
        <?php foreach ($badges as $b): ?>
          <span style="display:inline-flex;align-items:center;gap:4px;background:<?= htmlspecialchars($b['bg']) ?>;color:<?= htmlspecialchars($b['color']) ?>;border-radius:20px;font-size:.7rem;font-weight:700;padding:3px 10px;">
            <i class="bi <?= htmlspecialchars($b['icon']) ?>"></i><?= htmlspecialchars($b['label']) ?>
          </span>
        <?php endforeach; ?>
      </div>
      <?php if ($provider['headline']): ?>
        <p style="font-size:.9rem;color:var(--ink-2);margin:4px 0 0;"><?= htmlspecialchars($provider['headline']) ?></p>
      <?php endif; ?>
      <div class="d-flex flex-wrap align-items-center gap-3 mt-2" style="font-size:.8rem;color:var(--ink-3);">
        <?php if ($provider['location']): ?>
          <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($provider['location']) ?></span>
        <?php endif; ?>
        <?php if ($rev_stats['count'] > 0): ?>
          <span><?= star_html2((float)$rev_stats['avg']) ?> <?= number_format($rev_stats['avg'], 1) ?> (<?= $rev_stats['count'] ?> reviews)</span>
        <?php endif; ?>
        <?php if (!empty($prof['day_rate'])): ?>
          <span><i class="bi bi-cash me-1"></i><?= htmlspecialchars(($prof['currency_code'] ?? 'KES') . ' ' . number_format((float)$prof['day_rate'])) ?>/<?= htmlspecialchars($prof['rate_unit'] ?? 'day') ?></span>
        <?php endif; ?>
        <span class="<?= ($provider['is_available'] ?? 0) ? 'text-success' : 'text-danger' ?>">
          <i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i>
          <?= ($provider['is_available'] ?? 0) ? 'Available' : 'Busy' ?>
        </span>
      </div>
    </div>
    <!-- CTA -->
    <div class="col-auto d-none d-md-flex gap-2 pb-3">
      <?php if (is_logged_in()): ?>
        <button class="bf-btn-outline" data-bs-toggle="modal" data-bs-target="#quoteModal">
          <i class="bi bi-chat-quote me-1"></i>Request Quote
        </button>
        <button class="bf-btn-dark" data-bs-toggle="modal" data-bs-target="#hireModal">
          <i class="bi bi-person-check me-1"></i>Send Invite
        </button>
      <?php else: ?>
        <a href="/pages/auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="bf-btn-dark">
          <i class="bi bi-lock me-1"></i>Sign in to contact
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tabs -->
  <div style="border-bottom:1px solid var(--line);margin-top:16px;display:flex;gap:0;overflow-x:auto;">
    <?php
    $tabs = [['about','About'],['portfolio','Portfolio'],['reviews','Reviews (' . $rev_stats['count'] . ')'],['packages','Packages']];
    foreach ($tabs as [$tk, $tl]):
    ?>
      <a href="?id=<?= urlencode($key) ?>&tab=<?= $tk ?>" class="pf-tab <?= $tab === $tk ? 'active' : '' ?>">
        <?= $tl ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Tab content -->
  <div class="py-4">
    <div class="row g-4">
      <div class="col-12 col-lg-8">

        <?php if ($tab === 'about'): ?>
          <!-- Bio -->
          <?php if (!empty($prof['bio'])): ?>
            <div class="mb-4">
              <div class="pf-section-title">About</div>
              <p style="font-size:.9rem;line-height:1.75;color:var(--ink-2);"><?= nl2br(htmlspecialchars($prof['bio'])) ?></p>
            </div>
          <?php endif; ?>

          <!-- Skills -->
          <?php if (!empty($skills)): ?>
            <div class="mb-4">
              <div class="pf-section-title">Skills</div>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($skills as $sk): ?>
                  <span class="bf-pill"><?= htmlspecialchars($sk) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Certifications -->
          <?php if (!empty($certs)): ?>
            <div class="mb-4">
              <div class="pf-section-title">Certifications &amp; Licences</div>
              <?php foreach ($certs as $cert): ?>
                <div class="d-flex align-items-start gap-3 mb-2">
                  <i class="bi bi-patch-check" style="color:#1e3a5f;margin-top:2px;"></i>
                  <div>
                    <div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($cert['name']) ?></div>
                    <?php if ($cert['issuer']): ?>
                      <div style="font-size:.8rem;color:var(--ink-3);"><?= htmlspecialchars($cert['issuer']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Work History -->
          <?php if (!empty($work_hist)): ?>
            <div class="mb-4">
              <div class="pf-section-title">Experience</div>
              <?php foreach ($work_hist as $w): ?>
                <div class="mb-3">
                  <div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($w['role']) ?></div>
                  <?php if ($w['organization']): ?><div style="font-size:.8rem;color:var(--ink-2);"><?= htmlspecialchars($w['organization']) ?></div><?php endif; ?>
                  <?php if ($w['period']): ?><div style="font-size:.75rem;color:var(--ink-3);"><?= htmlspecialchars($w['period']) ?></div><?php endif; ?>
                  <?php if ($w['description']): ?><p style="font-size:.83rem;color:var(--ink-2);margin:4px 0 0;"><?= nl2br(htmlspecialchars($w['description'])) ?></p><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Education -->
          <?php if (!empty($education)): ?>
            <div class="mb-4">
              <div class="pf-section-title">Education</div>
              <?php foreach ($education as $e): ?>
                <div class="mb-2">
                  <div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($e['title']) ?></div>
                  <?php if ($e['institution']): ?><div style="font-size:.8rem;color:var(--ink-3);"><?= htmlspecialchars($e['institution']) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php elseif ($tab === 'portfolio'): ?>
          <?php if (empty($portfolio)): ?>
            <p style="color:var(--ink-3);">No portfolio items yet.</p>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($portfolio as $item): ?>
                <div class="col-6 col-md-4">
                  <div style="border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;">
                    <?php if ($item['image_url']): ?>
                      <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>"
                           style="width:100%;height:140px;object-fit:cover;">
                    <?php else: ?>
                      <div style="height:140px;background:#eaf0f6;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-image" style="font-size:2rem;color:var(--ink-4);"></i>
                      </div>
                    <?php endif; ?>
                    <div style="padding:10px 12px;">
                      <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($item['title']) ?></div>
                      <?php if ($item['category']): ?><div style="font-size:.75rem;color:var(--ink-3);"><?= htmlspecialchars($item['category']) ?></div><?php endif; ?>
                      <?php if ($item['year']): ?><div style="font-size:.72rem;color:var(--ink-4);"><?= htmlspecialchars($item['year']) ?></div><?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php elseif ($tab === 'reviews'): ?>
          <!-- Rating breakdown -->
          <?php if ($rev_stats['count'] > 0): ?>
            <div class="d-flex gap-4 align-items-center mb-4 p-3" style="background:#fff;border:1px solid var(--line);border-radius:12px;">
              <div class="text-center" style="flex-shrink:0;">
                <div style="font-size:2.5rem;font-weight:800;color:#0d0d0d;line-height:1;"><?= number_format($rev_stats['avg'], 1) ?></div>
                <div><?= star_html2((float)$rev_stats['avg']) ?></div>
                <div style="font-size:.75rem;color:var(--ink-3);"><?= $rev_stats['count'] ?> reviews</div>
              </div>
              <div style="flex:1;">
                <?php for ($s = 5; $s >= 1; $s--): ?>
                  <?php $c = $rev_stats['breakdown'][$s] ?? 0; $pct = $rev_stats['count'] ? round($c * 100 / $rev_stats['count']) : 0; ?>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="font-size:.75rem;width:12px;text-align:right;"><?= $s ?></span>
                    <div style="flex:1;height:6px;background:var(--line);border-radius:3px;overflow:hidden;">
                      <div style="width:<?= $pct ?>%;height:100%;background:#c9a84c;border-radius:3px;"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--ink-3);width:24px;"><?= $c ?></span>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (empty($reviews)): ?>
            <p style="color:var(--ink-3);">No reviews yet.</p>
          <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
              <div class="mb-3 p-3" style="background:#fff;border:1px solid var(--line);border-radius:10px;">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:34px;height:34px;border-radius:50%;background:#1e3a5f;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;">
                    <?= strtoupper(substr($rev['reviewer_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($rev['reviewer_name']) ?></div>
                    <?= star_html2((float)$rev['rating'], '.75rem') ?>
                  </div>
                  <div class="ms-auto" style="font-size:.75rem;color:var(--ink-3);">
                    <?= date('d M Y', strtotime($rev['created_at'])) ?>
                  </div>
                </div>
                <?php if ($rev['project']): ?>
                  <div style="font-size:.75rem;color:var(--ink-3);margin-bottom:4px;">Project: <?= htmlspecialchars($rev['project']) ?></div>
                <?php endif; ?>
                <?php if ($rev['body']): ?>
                  <p style="font-size:.875rem;color:var(--ink-2);margin:0;"><?= nl2br(htmlspecialchars($rev['body'])) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Leave a review -->
          <?php if (is_logged_in()): ?>
            <div class="mt-4 p-3" style="background:#eaf0f6;border-radius:10px;">
              <div style="font-weight:700;font-size:.875rem;margin-bottom:12px;">Leave a Review</div>
              <form method="POST" action="/api/engage.php" id="reviewForm">
                <input type="hidden" name="action" value="review">
                <input type="hidden" name="provider_id" value="<?= htmlspecialchars($provider['public_id']) ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label style="font-size:.8rem;font-weight:600;">Rating</label>
                    <select name="rating" class="form-select form-select-sm" required>
                      <?php for ($r = 5; $r >= 1; $r--): ?>
                        <option value="<?= $r ?>"><?= str_repeat('★', $r) ?> — <?= $r ?> star<?= $r > 1 ? 's' : '' ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label style="font-size:.8rem;font-weight:600;">Project name (optional)</label>
                    <input type="text" name="project" class="form-control form-control-sm" placeholder="e.g. Home renovation">
                  </div>
                  <div class="col-12">
                    <label style="font-size:.8rem;font-weight:600;">Review</label>
                    <textarea name="body" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="bf-btn-dark btn-sm">Post Review</button>
                  </div>
                </div>
              </form>
            </div>
          <?php endif; ?>

        <?php elseif ($tab === 'packages'): ?>
          <?php if (empty($packages)): ?>
            <p style="color:var(--ink-3);">No packages set up yet.</p>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($packages as $pkg): ?>
                <div class="col-md-4">
                  <div class="pkg-card <?= $pkg['is_featured'] ? 'featured' : '' ?>">
                    <?php if ($pkg['tier']): ?>
                      <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#1e3a5f;margin-bottom:8px;"><?= htmlspecialchars($pkg['tier']) ?></div>
                    <?php endif; ?>
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:6px;"><?= htmlspecialchars($pkg['title']) ?></h3>
                    <?php if ($pkg['price']): ?>
                      <div style="font-size:1.4rem;font-weight:800;color:#1e3a5f;margin-bottom:8px;">
                        <?= htmlspecialchars(($pkg['price_unit'] ?? '') . ' ' . $pkg['price']) ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($pkg['description']): ?>
                      <p style="font-size:.83rem;color:var(--ink-2);margin-bottom:10px;"><?= nl2br(htmlspecialchars($pkg['description'])) ?></p>
                    <?php endif; ?>
                    <?php if ($pkg['delivery'] || $pkg['revisions']): ?>
                      <div style="font-size:.78rem;color:var(--ink-3);margin-bottom:8px;">
                        <?php if ($pkg['delivery']): ?><span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($pkg['delivery']) ?></span>&nbsp;<?php endif; ?>
                        <?php if ($pkg['revisions']): ?><span><i class="bi bi-arrow-repeat me-1"></i><?= htmlspecialchars($pkg['revisions']) ?></span><?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <?php
                    $features = pkg_features($pkg['features'] ?? '');
                    if (!empty($features)): ?>
                      <ul style="padding-left:16px;margin:0 0 12px;font-size:.8rem;color:var(--ink-2);">
                        <?php foreach ($features as $f): ?>
                          <li><?= htmlspecialchars($f) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                    <?php if (is_logged_in()): ?>
                      <button class="bf-btn-dark w-100" style="justify-content:center;font-size:.83rem;"
                              data-bs-toggle="modal" data-bs-target="#quoteModal">
                        Get this package
                      </button>
                    <?php else: ?>
                      <a href="/pages/auth/login.php" class="bf-btn-outline w-100" style="justify-content:center;font-size:.83rem;">
                        Sign in to request
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Right sidebar -->
      <div class="col-12 col-lg-4">
        <!-- CTA -->
        <div style="background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px;margin-bottom:16px;">
          <?php if (is_logged_in()): ?>
            <button class="bf-btn-dark w-100 mb-2" style="justify-content:center;" data-bs-toggle="modal" data-bs-target="#quoteModal">
              <i class="bi bi-chat-quote me-2"></i>Request Quote
            </button>
            <button class="bf-btn-outline w-100" style="justify-content:center;" data-bs-toggle="modal" data-bs-target="#hireModal">
              <i class="bi bi-person-check me-2"></i>Send Invite
            </button>
          <?php else: ?>
            <a href="/pages/auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="bf-btn-dark w-100" style="justify-content:center;">
              <i class="bi bi-lock me-2"></i>Sign in to contact
            </a>
          <?php endif; ?>
        </div>

        <!-- Quick info -->
        <div style="background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px;">
          <div class="pf-section-title">Profile Info</div>
          <div class="d-flex flex-column gap-2" style="font-size:.85rem;">
            <?php if (!empty($prof['trade'])): ?>
              <div class="d-flex gap-2"><i class="bi bi-tools" style="color:#1e3a5f;width:18px;text-align:center;"></i><span><?= htmlspecialchars($prof['trade']) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($prof['years_experience'])): ?>
              <div class="d-flex gap-2"><i class="bi bi-calendar2" style="color:#1e3a5f;width:18px;text-align:center;"></i><span><?= htmlspecialchars($prof['years_experience']) ?> yrs experience</span></div>
            <?php endif; ?>
            <?php if (!empty($prof['availability'])): ?>
              <div class="d-flex gap-2"><i class="bi bi-circle-fill" style="color:<?= $prof['availability']==='available'?'#16a34a':'#b91c1c' ?>;width:18px;text-align:center;font-size:.5rem;padding-top:5px;"></i><span><?= ucfirst(htmlspecialchars($prof['availability'])) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($prof['company_name'])): ?>
              <div class="d-flex gap-2"><i class="bi bi-building" style="color:#1e3a5f;width:18px;text-align:center;"></i><span><?= htmlspecialchars($prof['company_name']) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($areas)): ?>
              <div class="d-flex gap-2"><i class="bi bi-geo-alt" style="color:#1e3a5f;width:18px;text-align:center;"></i><span><?= htmlspecialchars(implode(', ', array_slice($areas, 0, 3))) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($prof['website'])): ?>
              <div class="d-flex gap-2"><i class="bi bi-globe" style="color:#1e3a5f;width:18px;text-align:center;"></i><a href="<?= htmlspecialchars($prof['website']) ?>" target="_blank" rel="noopener" style="color:#1e3a5f;"><?= htmlspecialchars($prof['website']) ?></a></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" style="font-size:1rem;font-weight:700;">Request a Quote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="quoteForm">
        <div class="modal-body">
          <input type="hidden" name="action" value="quote">
          <input type="hidden" name="provider_id" value="<?= htmlspecialchars($provider['public_id']) ?>">
          <div class="mb-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="What do you need?"></div>
          <div class="mb-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Message</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Describe your project..."></textarea></div>
          <div class="row g-3">
            <div class="col-6"><label class="form-label" style="font-size:.8rem;font-weight:600;">Budget (optional)</label>
              <input type="text" name="budget" class="form-control" placeholder="e.g. KES 50,000"></div>
            <div class="col-6"><label class="form-label" style="font-size:.8rem;font-weight:600;">Needed by</label>
              <input type="date" name="needed_by" class="form-control"></div>
            <div class="col-12"><label class="form-label" style="font-size:.8rem;font-weight:600;">Location</label>
              <input type="text" name="location" class="form-control" placeholder="Where is the project?"></div>
          </div>
          <div id="quoteMsg" class="mt-3"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-dark">Send Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Hire / Invite Modal -->
<div class="modal fade" id="hireModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" style="font-size:1rem;font-weight:700;">Send Invite</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="hireForm">
        <div class="modal-body">
          <input type="hidden" name="action" value="hire">
          <input type="hidden" name="provider_id" value="<?= htmlspecialchars($provider['public_id']) ?>">
          <div class="mb-3"><label class="form-label" style="font-size:.8rem;font-weight:600;">Message</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Introduce your project and invite them to work with you..."></textarea></div>
          <div id="hireMsg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-dark">Send Invite</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function engageSubmit(formId, msgId) {
  var form = document.getElementById(formId);
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var data = new FormData(form);
    var msgEl = document.getElementById(msgId);
    msgEl.innerHTML = '<span style="font-size:.83rem;color:var(--ink-3);">Sending...</span>';
    fetch('/api/engage.php', {
      method: 'POST',
      headers: {'X-Requested-With': 'fetch'},
      body: data
    }).then(function(r){ return r.json(); }).then(function(res){
      if (res.ok) {
        msgEl.innerHTML = '<div class="alert alert-success py-2 px-3" style="font-size:.83rem;">Sent! They will be notified.</div>';
        setTimeout(function(){ bootstrap.Modal.getInstance(form.closest('.modal')).hide(); }, 2000);
      } else if (res.login) {
        window.location = '/pages/auth/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
      } else {
        msgEl.innerHTML = '<div class="alert alert-danger py-2 px-3" style="font-size:.83rem;">' + (res.error || 'An error occurred.') + '</div>';
      }
    }).catch(function(){
      msgEl.innerHTML = '<div class="alert alert-danger py-2 px-3" style="font-size:.83rem;">Network error. Try again.</div>';
    });
  });
}
engageSubmit('quoteForm', 'quoteMsg');
engageSubmit('hireForm', 'hireMsg');

// Review form via fetch
var reviewForm = document.getElementById('reviewForm');
if (reviewForm) {
  reviewForm.addEventListener('submit', function(e){
    e.preventDefault();
    var data = new FormData(reviewForm);
    fetch('/api/engage.php', {method:'POST', headers:{'X-Requested-With':'fetch'}, body:data})
      .then(function(r){ return r.json(); })
      .then(function(res){
        if (res.ok) {
          reviewForm.innerHTML = '<div class="alert alert-success py-2 px-3">Review posted! Thank you.</div>';
        } else {
          alert(res.error || 'Could not post review.');
        }
      });
  });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
