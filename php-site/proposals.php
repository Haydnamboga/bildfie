<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$u = current_user();
$page_title = 'My proposals';

// Withdraw
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['action'] ?? '') === 'withdraw') {
        $pid = (int)($_POST['proposal_id'] ?? 0);
        $stmt = $mysqli->prepare("UPDATE proposals SET status='WITHDRAWN' WHERE id=? AND pro_id=? AND status IN ('PENDING','SHORTLISTED')");
        $stmt->bind_param('ii', $pid, $u['id']); $stmt->execute(); $stmt->close();
        flash('Proposal withdrawn.');
    }
    redirect('proposals.php');
}

$stmt = $mysqli->prepare(
  "SELECT p.*, j.title job_title, j.status job_status, j.id job_id
   FROM proposals p JOIN job_posts j ON p.job_post_id=j.id
   WHERE p.pro_id=? ORDER BY p.created_at DESC"
);
$stmt->bind_param('i', $u['id']); $stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= e(url('dashboard.php')) ?>">← Back to dashboard</a>
<h1 class="section-title">My proposals</h1>
<?php if (!$rows): ?>
  <div class="empty"><h3>No proposals yet</h3><p>Browse <a href="<?= e(url('jobs.php')) ?>">open jobs</a> and send your first proposal.</p></div>
<?php else: ?>
  <?php foreach ($rows as $p): ?>
    <div class="card" style="margin-bottom:12px">
      <div class="spread">
        <a href="<?= e(url('job.php?id=' . $p['job_id'])) ?>"><strong><?= e($p['job_title']) ?></strong></a>
        <?= badge($p['status']) ?>
      </div>
      <div class="meta">Bid <?= e(money($p['amount'])) ?><?= $p['timeline'] ? ' · '.e($p['timeline']) : '' ?> · <?= e(time_ago($p['created_at'])) ?></div>
      <p class="muted" style="font-size:14px;margin-top:6px"><?= e(mb_strimwidth($p['cover_letter'],0,140,'…')) ?></p>
      <?php if (in_array($p['status'], ['PENDING','SHORTLISTED'], true)): ?>
        <form method="post" action="<?= e(url('proposals.php')) ?>" style="margin-top:10px">
          <?= csrf_field() ?>
          <input type="hidden" name="proposal_id" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-ghost btn-sm" name="action" value="withdraw" onclick="return confirm('Withdraw this proposal?')">Withdraw</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
