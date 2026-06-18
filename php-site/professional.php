<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc(); $stmt->close();

if (!$p) { http_response_code(404); $page_title='Not found'; require __DIR__.'/includes/header.php';
  echo '<div class="empty"><h3>Professional not found</h3><a href="'.e(url('marketplace.php')).'">Back to marketplace</a></div>';
  require __DIR__.'/includes/footer.php'; exit; }

$page_title = $p['full_name'];

// Portfolio
$ps = $mysqli->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY created_at DESC");
$ps->bind_param('i', $id); $ps->execute();
$portfolio = $ps->get_result()->fetch_all(MYSQLI_ASSOC); $ps->close();

// Reviews + avg
$rs = $mysqli->prepare("SELECT r.*, u.full_name author FROM reviews r JOIN users u ON r.author_id=u.id WHERE subject_id = ? ORDER BY r.created_at DESC LIMIT 20");
$rs->bind_param('i', $id); $rs->execute();
$reviews = $rs->get_result()->fetch_all(MYSQLI_ASSOC); $rs->close();
$avg = $mysqli->query("SELECT ROUND(AVG(rating),1) a, COUNT(*) c FROM reviews WHERE subject_id = $id")->fetch_assoc();

require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= e(url('marketplace.php')) ?>">← Back to professionals</a>
<div class="card">
  <div class="spread">
    <div class="row">
      <div class="avatar" style="width:64px;height:64px;font-size:24px"><?= e(strtoupper(substr($p['full_name'],0,1))) ?></div>
      <div>
        <h1 style="font-size:26px"><?= e($p['full_name']) ?></h1>
        <div class="meta"><?= e($p['headline'] ?: nice($p['category'])) ?></div>
        <div class="muted" style="margin-top:4px">
          📍 <?= e($p['location'] ?: '—') ?>
          <?php if ($avg['c'] > 0): ?> · ⭐ <?= e($avg['a']) ?> (<?= (int)$avg['c'] ?> reviews)<?php endif; ?>
        </div>
      </div>
    </div>
    <div style="text-align:right">
      <?= badge($p['pro_level']) ?>
      <div class="muted" style="margin-top:8px"><?= $p['hourly_rate'] ? e(money($p['hourly_rate'])).'/hr' : '' ?></div>
    </div>
  </div>

  <?php if ($p['bio']): ?>
    <div class="divider"></div>
    <div class="prose"><p><?= nl2br(e($p['bio'])) ?></p></div>
  <?php endif; ?>

  <?php if ($p['skills']): ?>
    <div class="skills" style="margin-top:14px">
      <?php foreach (array_filter(array_map('trim', explode(',', $p['skills']))) as $s): ?>
        <span class="skill"><?= e($s) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($portfolio): ?>
  <h2 class="section-title" style="margin-top:32px">Portfolio</h2>
  <div class="grid grid-3">
    <?php foreach ($portfolio as $w): ?>
      <div class="card">
        <h3><?= e($w['title']) ?></h3>
        <div class="meta"><?= e(nice($w['category'])) ?><?= $w['completed_at'] ? ' · '.e(date('M Y', strtotime($w['completed_at']))) : '' ?></div>
        <?php if ($w['description']): ?><p class="muted" style="font-size:14px"><?= e($w['description']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($reviews): ?>
  <h2 class="section-title" style="margin-top:32px">Reviews</h2>
  <?php foreach ($reviews as $r): ?>
    <div class="card" style="margin-bottom:12px">
      <div class="spread">
        <strong><?= e($r['author']) ?></strong>
        <span><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span>
      </div>
      <?php if ($r['comment']): ?><p class="muted" style="margin-top:6px"><?= e($r['comment']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
