<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$u = current_user();
$page_title = 'Dashboard';
$uid = (int)$u['id'];

// Stats
$myJobs = $mysqli->query("SELECT COUNT(*) c FROM job_posts WHERE client_id = $uid")->fetch_assoc()['c'];
$myProps = $mysqli->query("SELECT COUNT(*) c FROM proposals WHERE pro_id = $uid")->fetch_assoc()['c'];
$openJobs = $mysqli->query("SELECT COUNT(*) c FROM job_posts WHERE status='OPEN'")->fetch_assoc()['c'];

// Proposals received on my jobs
$recv = $mysqli->query(
  "SELECT COUNT(*) c FROM proposals p JOIN job_posts j ON p.job_post_id=j.id WHERE j.client_id = $uid"
)->fetch_assoc()['c'];

// My recent jobs
$stmt = $mysqli->prepare("SELECT * FROM job_posts WHERE client_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $uid); $stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

require __DIR__ . '/includes/header.php';
?>
<div class="spread">
  <div>
    <h1 class="section-title" style="margin-bottom:2px">Hi, <?= e($u['full_name']) ?> 👋</h1>
    <p class="muted"><?= $u['is_pro'] ? 'Professional' : 'Client' ?> account<?= $u['is_pro'] && $u['category'] ? ' · ' . e(nice($u['category'])) : '' ?></p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('post-job.php')) ?>">+ Post a Job</a>
</div>

<div class="stat-grid" style="margin-top:22px">
  <div class="stat"><div class="n"><?= (int)$myJobs ?></div><div class="l">Jobs posted</div></div>
  <div class="stat"><div class="n"><?= (int)$recv ?></div><div class="l">Proposals received</div></div>
  <div class="stat"><div class="n"><?= (int)$myProps ?></div><div class="l">Proposals sent</div></div>
  <div class="stat"><div class="n"><?= (int)$openJobs ?></div><div class="l">Open jobs (all)</div></div>
</div>

<div class="grid grid-2">
  <div class="card">
    <div class="spread"><h3>Your job posts</h3><a class="back-link" href="<?= e(url('post-job.php')) ?>">New →</a></div>
    <div class="divider"></div>
    <?php if (!$jobs): ?>
      <p class="muted">You haven't posted any jobs yet.</p>
    <?php else: foreach ($jobs as $j): ?>
      <div class="spread" style="padding:8px 0;border-bottom:1px solid var(--line)">
        <a href="<?= e(url('job.php?id=' . $j['id'])) ?>"><?= e($j['title']) ?></a>
        <?= badge($j['status']) ?>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="card">
    <div class="spread"><h3>Quick links</h3></div>
    <div class="divider"></div>
    <p style="padding:6px 0"><a href="<?= e(url('proposals.php')) ?>">→ My submitted proposals</a></p>
    <p style="padding:6px 0"><a href="<?= e(url('marketplace.php')) ?>">→ Find professionals</a></p>
    <p style="padding:6px 0"><a href="<?= e(url('jobs.php')) ?>">→ Browse open jobs</a></p>
    <p style="padding:6px 0"><a href="<?= e(url('profile.php')) ?>">→ Edit my profile</a></p>
    <?php if (in_array($u['role'], ['ADMIN','SUPER_ADMIN'], true)): ?>
      <p style="padding:6px 0"><a href="<?= e(url('admin/index.php')) ?>">→ Admin panel</a></p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
