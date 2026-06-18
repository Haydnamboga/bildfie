<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Hire trusted trade professionals';

// A few featured pros + recent jobs for the landing page
$pros = $mysqli->query(
  "SELECT id, full_name, headline, category, location, pro_level
   FROM users WHERE is_pro = 1 ORDER BY created_at DESC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

$jobs = $mysqli->query(
  "SELECT id, title, category, location, budget_min, budget_max, created_at
   FROM job_posts WHERE status = 'OPEN' ORDER BY created_at DESC LIMIT 4"
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Build with the right <span class="hl">trade pros</span>.</h1>
  <p>bildfie connects clients with verified electricians, plumbers, carpenters,
     masons and more — post a job, compare proposals, and hire with confidence.</p>
  <div class="hero-actions">
    <a class="btn btn-primary" href="<?= e(url('post-job.php')) ?>">Post a Job</a>
    <a class="btn btn-light" href="<?= e(url('marketplace.php')) ?>">Browse Professionals</a>
  </div>
  <div class="cats">
    <?php foreach (trade_categories() as $c): ?>
      <a class="cat-chip" href="<?= e(url('marketplace.php?category=' . $c)) ?>"><?= e(nice($c)) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section style="margin-top:48px">
  <div class="spread">
    <h2 class="section-title">Featured professionals</h2>
    <a class="back-link" href="<?= e(url('marketplace.php')) ?>">View all →</a>
  </div>
  <?php if (!$pros): ?>
    <div class="empty"><h3>No professionals yet</h3><p>Be the first — <a href="<?= e(url('register.php')) ?>">create a pro profile</a>.</p></div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($pros as $p): ?>
        <a class="card card-link" href="<?= e(url('professional.php?id=' . $p['id'])) ?>">
          <div class="row">
            <div class="avatar"><?= e(strtoupper(substr($p['full_name'],0,1))) ?></div>
            <div>
              <h3><?= e($p['full_name']) ?></h3>
              <div class="meta"><?= e($p['headline'] ?: nice($p['category'])) ?></div>
            </div>
          </div>
          <div class="spread" style="margin-top:12px">
            <span class="muted">📍 <?= e($p['location'] ?: 'Remote') ?></span>
            <?= badge($p['pro_level']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section style="margin-top:48px">
  <div class="spread">
    <h2 class="section-title">Recent jobs</h2>
    <a class="back-link" href="<?= e(url('jobs.php')) ?>">View all →</a>
  </div>
  <?php if (!$jobs): ?>
    <div class="empty"><h3>No open jobs</h3><p><a href="<?= e(url('post-job.php')) ?>">Post the first job →</a></p></div>
  <?php else: ?>
    <div class="grid grid-2">
      <?php foreach ($jobs as $j): ?>
        <a class="card card-link" href="<?= e(url('job.php?id=' . $j['id'])) ?>">
          <div class="spread">
            <h3><?= e($j['title']) ?></h3>
            <?= badge($j['category']) ?>
          </div>
          <div class="meta">📍 <?= e($j['location']) ?> · <?= e(time_ago($j['created_at'])) ?></div>
          <div class="muted">Budget: <?= e(money($j['budget_min'])) ?> – <?= e(money($j['budget_max'])) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
