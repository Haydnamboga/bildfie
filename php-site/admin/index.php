<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['ADMIN','SUPER_ADMIN']);
$page_title = 'Admin';

$stats = [
  'users'     => $mysqli->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
  'pros'      => $mysqli->query("SELECT COUNT(*) c FROM users WHERE is_pro=1")->fetch_assoc()['c'],
  'jobs'      => $mysqli->query("SELECT COUNT(*) c FROM job_posts")->fetch_assoc()['c'],
  'proposals' => $mysqli->query("SELECT COUNT(*) c FROM proposals")->fetch_assoc()['c'],
];
$recentJobs = $mysqli->query("SELECT j.*, u.full_name client FROM job_posts j JOIN users u ON j.client_id=u.id ORDER BY j.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../includes/header.php';
?>
<div class="spread">
  <h1 class="section-title">Admin panel</h1>
  <a class="btn btn-light" href="<?= e(url('admin/users.php')) ?>">Manage users →</a>
</div>
<div class="stat-grid">
  <div class="stat"><div class="n"><?= (int)$stats['users'] ?></div><div class="l">Total users</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['pros'] ?></div><div class="l">Professionals</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['jobs'] ?></div><div class="l">Jobs posted</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['proposals'] ?></div><div class="l">Proposals</div></div>
</div>

<h2 class="section-title">Recent jobs</h2>
<table class="table">
  <thead><tr><th>Title</th><th>Client</th><th>Category</th><th>Status</th><th>Posted</th></tr></thead>
  <tbody>
  <?php foreach ($recentJobs as $j): ?>
    <tr>
      <td><a href="<?= e(url('job.php?id=' . $j['id'])) ?>"><?= e($j['title']) ?></a></td>
      <td><?= e($j['client']) ?></td>
      <td><?= e(nice($j['category'])) ?></td>
      <td><?= badge($j['status']) ?></td>
      <td class="muted"><?= e(time_ago($j['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$recentJobs): ?><tr><td colspan="5" class="muted">No jobs yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
