<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Find professionals';

$q        = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$location = trim($_GET['location'] ?? '');
if ($category !== '' && !in_array($category, trade_categories(), true)) $category = '';

$sql = "SELECT id, full_name, headline, bio, category, location, skills, hourly_rate, pro_level
        FROM users WHERE is_pro = 1";
$params = []; $types = '';
if ($q !== '')        { $sql .= " AND (full_name LIKE ? OR headline LIKE ? OR skills LIKE ?)"; $like="%$q%"; $params[]=$like;$params[]=$like;$params[]=$like; $types.='sss'; }
if ($category !== '') { $sql .= " AND category = ?"; $params[]=$category; $types.='s'; }
if ($location !== '') { $sql .= " AND location LIKE ?"; $params[]="%$location%"; $types.='s'; }
$sql .= " ORDER BY FIELD(pro_level,'TOP_RATED','VERIFIED','STARTER'), created_at DESC LIMIT 60";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$pros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require __DIR__ . '/includes/header.php';
?>
<h1 class="section-title">Find professionals</h1>
<form class="filters" method="get" action="<?= e(url('marketplace.php')) ?>">
  <div class="field">
    <label>Search</label>
    <input class="input" name="q" value="<?= e($q) ?>" placeholder="name, skill…">
  </div>
  <div class="field">
    <label>Trade</label>
    <select name="category" class="select">
      <option value="">All trades</option>
      <?php foreach (trade_categories() as $c): ?>
        <option value="<?= e($c) ?>" <?= $category===$c?'selected':'' ?>><?= e(nice($c)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label>Location</label>
    <input class="input" name="location" value="<?= e($location) ?>" placeholder="e.g. Nairobi">
  </div>
  <button class="btn btn-primary" type="submit">Filter</button>
  <a class="btn btn-ghost" href="<?= e(url('marketplace.php')) ?>">Reset</a>
</form>

<?php if (!$pros): ?>
  <div class="empty"><h3>No professionals found</h3><p>Try a different trade or location.</p></div>
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
        <p class="muted" style="margin-top:10px;font-size:14px"><?= e(mb_strimwidth($p['bio'] ?? '', 0, 90, '…')) ?></p>
        <?php if ($p['skills']): ?>
          <div class="skills">
            <?php foreach (array_slice(array_filter(array_map('trim', explode(',', $p['skills']))), 0, 3) as $s): ?>
              <span class="skill"><?= e($s) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="spread" style="margin-top:12px">
          <span class="muted">📍 <?= e($p['location'] ?: '—') ?></span>
          <?= badge($p['pro_level']) ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
