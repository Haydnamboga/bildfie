<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Browse jobs';

$q        = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$location = trim($_GET['location'] ?? '');
if ($category !== '' && !in_array($category, trade_categories(), true)) $category = '';

$sql = "SELECT j.*, u.full_name client_name,
        (SELECT COUNT(*) FROM proposals p WHERE p.job_post_id=j.id) AS proposal_count
        FROM job_posts j JOIN users u ON j.client_id=u.id
        WHERE j.status IN ('OPEN','IN_REVIEW')";
$params=[]; $types='';
if ($q !== '')        { $sql.=" AND (j.title LIKE ? OR j.description LIKE ?)"; $like="%$q%"; $params[]=$like;$params[]=$like; $types.='ss'; }
if ($category !== '') { $sql.=" AND j.category = ?"; $params[]=$category; $types.='s'; }
if ($location !== '') { $sql.=" AND j.location LIKE ?"; $params[]="%$location%"; $types.='s'; }
$sql .= " ORDER BY j.created_at DESC LIMIT 60";

$stmt=$mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$jobs=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

require __DIR__ . '/includes/header.php';
?>
<div class="spread">
  <h1 class="section-title">Open jobs</h1>
  <a class="btn btn-primary" href="<?= e(url('post-job.php')) ?>">+ Post a Job</a>
</div>
<form class="filters" method="get" action="<?= e(url('jobs.php')) ?>">
  <div class="field"><label>Search</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="keyword…"></div>
  <div class="field"><label>Trade</label>
    <select name="category" class="select">
      <option value="">All trades</option>
      <?php foreach (trade_categories() as $c): ?><option value="<?= e($c) ?>" <?= $category===$c?'selected':'' ?>><?= e(nice($c)) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>Location</label><input class="input" name="location" value="<?= e($location) ?>" placeholder="e.g. Nairobi"></div>
  <button class="btn btn-primary" type="submit">Filter</button>
  <a class="btn btn-ghost" href="<?= e(url('jobs.php')) ?>">Reset</a>
</form>

<?php if (!$jobs): ?>
  <div class="empty"><h3>No jobs found</h3><p>Be the first to <a href="<?= e(url('post-job.php')) ?>">post a job</a>.</p></div>
<?php else: ?>
  <div class="grid grid-2">
    <?php foreach ($jobs as $j): ?>
      <a class="card card-link" href="<?= e(url('job.php?id=' . $j['id'])) ?>">
        <div class="spread"><h3><?= e($j['title']) ?></h3><?= badge($j['category']) ?></div>
        <div class="meta">📍 <?= e($j['location']) ?> · by <?= e($j['client_name']) ?> · <?= e(time_ago($j['created_at'])) ?></div>
        <p class="muted" style="font-size:14px"><?= e(mb_strimwidth($j['description'], 0, 120, '…')) ?></p>
        <div class="spread" style="margin-top:12px">
          <span class="muted"><?= e(money($j['budget_min'])) ?> – <?= e(money($j['budget_max'])) ?></span>
          <span class="skill"><?= (int)$j['proposal_count'] ?> proposal<?= $j['proposal_count']==1?'':'s' ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
