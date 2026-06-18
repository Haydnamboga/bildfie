<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$u = current_user();

function load_job($mysqli, $id) {
    $stmt = $mysqli->prepare("SELECT j.*, u.full_name client_name FROM job_posts j JOIN users u ON j.client_id=u.id WHERE j.id=? LIMIT 1");
    $stmt->bind_param('i', $id); $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc(); $stmt->close();
    return $job;
}
$job = load_job($mysqli, $id);
if (!$job) { http_response_code(404); $page_title='Not found'; require __DIR__.'/includes/header.php';
  echo '<div class="empty"><h3>Job not found</h3><a href="'.e(url('jobs.php')).'">Back to jobs</a></div>';
  require __DIR__.'/includes/footer.php'; exit; }

$is_owner = $u && (int)$u['id'] === (int)$job['client_id'];

/* ---------- Handle POST actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    require_login();
    $action = $_POST['action'] ?? '';

    // Pro submits a proposal
    if ($action === 'propose' && !$is_owner) {
        $cover = trim($_POST['cover_letter'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $timeline = trim($_POST['timeline'] ?? '');
        if ($cover === '' || $amount <= 0) {
            flash('Add a cover letter and a valid amount.', 'error');
        } else {
            $dup = $mysqli->prepare("SELECT id FROM proposals WHERE job_post_id=? AND pro_id=? LIMIT 1");
            $dup->bind_param('ii', $id, $u['id']); $dup->execute();
            if ($dup->get_result()->fetch_assoc()) {
                flash('You already submitted a proposal for this job.', 'error');
            } else {
                $stmt = $mysqli->prepare("INSERT INTO proposals (job_post_id, pro_id, cover_letter, amount, timeline) VALUES (?,?,?,?,?)");
                $stmt->bind_param('iisds', $id, $u['id'], $cover, $amount, $timeline);
                $stmt->execute(); $stmt->close();
                flash('Proposal submitted!');
            }
            $dup->close();
        }
        redirect('job.php?id=' . $id);
    }

    // Owner responds to a proposal
    if (in_array($action, ['shortlist','accept','reject'], true) && $is_owner) {
        $pid = (int)($_POST['proposal_id'] ?? 0);
        $map = ['shortlist'=>'SHORTLISTED','accept'=>'ACCEPTED','reject'=>'REJECTED'];
        $new = $map[$action];
        // ensure proposal belongs to this job
        $chk = $mysqli->prepare("SELECT id FROM proposals WHERE id=? AND job_post_id=? LIMIT 1");
        $chk->bind_param('ii', $pid, $id); $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            $stmt = $mysqli->prepare("UPDATE proposals SET status=? WHERE id=?");
            $stmt->bind_param('si', $new, $pid); $stmt->execute(); $stmt->close();
            if ($action === 'accept') {
                $mysqli->query("UPDATE job_posts SET status='AWARDED' WHERE id=" . (int)$id);
                $mysqli->query("UPDATE proposals SET status='REJECTED' WHERE job_post_id=" . (int)$id . " AND id<>" . (int)$pid . " AND status<>'WITHDRAWN'");
                flash('Proposal accepted — job marked as awarded.');
            } else {
                flash('Proposal ' . strtolower($new) . '.');
            }
        }
        $chk->close();
        redirect('job.php?id=' . $id);
    }
    redirect('job.php?id=' . $id);
}

$page_title = $job['title'];

// Proposals (owner sees all; pro sees their own)
$proposals = [];
$myProposal = null;
if ($is_owner) {
    $stmt = $mysqli->prepare("SELECT p.*, u.full_name, u.headline, u.pro_level FROM proposals p JOIN users u ON p.pro_id=u.id WHERE p.job_post_id=? ORDER BY FIELD(p.status,'ACCEPTED','SHORTLISTED','PENDING','REJECTED','WITHDRAWN'), p.amount ASC");
    $stmt->bind_param('i', $id); $stmt->execute();
    $proposals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
} elseif ($u) {
    $stmt = $mysqli->prepare("SELECT * FROM proposals WHERE job_post_id=? AND pro_id=? LIMIT 1");
    $stmt->bind_param('ii', $id, $u['id']); $stmt->execute();
    $myProposal = $stmt->get_result()->fetch_assoc(); $stmt->close();
}

require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= e(url('jobs.php')) ?>">← Back to jobs</a>
<div class="card">
  <div class="spread">
    <h1 style="font-size:26px"><?= e($job['title']) ?></h1>
    <?= badge($job['status']) ?>
  </div>
  <div class="meta">📍 <?= e($job['location']) ?> · <?= e(nice($job['category'])) ?> · posted by <?= e($job['client_name']) ?> · <?= e(time_ago($job['created_at'])) ?></div>
  <div class="divider"></div>
  <div class="prose"><p><?= nl2br(e($job['description'])) ?></p></div>
  <div class="spread" style="margin-top:16px">
    <strong>Budget: <?= e(money($job['budget_min'])) ?> – <?= e(money($job['budget_max'])) ?></strong>
    <?php if ($job['due_date']): ?><span class="muted">Due <?= e(date('M j, Y', strtotime($job['due_date']))) ?></span><?php endif; ?>
  </div>
</div>

<?php /* ---------------- Owner: manage proposals ---------------- */ ?>
<?php if ($is_owner): ?>
  <h2 class="section-title" style="margin-top:32px">Proposals (<?= count($proposals) ?>)</h2>
  <?php if (!$proposals): ?>
    <div class="empty"><h3>No proposals yet</h3><p>Share your job — pros will respond here.</p></div>
  <?php else: foreach ($proposals as $p): ?>
    <div class="card" style="margin-bottom:14px">
      <div class="spread">
        <div class="row">
          <div class="avatar"><?= e(strtoupper(substr($p['full_name'],0,1))) ?></div>
          <div>
            <a href="<?= e(url('professional.php?id=' . $p['pro_id'])) ?>"><strong><?= e($p['full_name']) ?></strong></a>
            <div class="meta"><?= e($p['headline'] ?: '') ?></div>
          </div>
        </div>
        <div style="text-align:right">
          <div><strong><?= e(money($p['amount'])) ?></strong></div>
          <?= badge($p['status']) ?>
        </div>
      </div>
      <p class="muted" style="margin-top:10px"><?= nl2br(e($p['cover_letter'])) ?></p>
      <?php if ($p['timeline']): ?><div class="muted" style="margin-top:6px">⏱ <?= e($p['timeline']) ?></div><?php endif; ?>
      <?php if (!in_array($p['status'], ['ACCEPTED','REJECTED','WITHDRAWN'], true)): ?>
      <div class="divider"></div>
      <form method="post" action="<?= e(url('job.php?id=' . $id)) ?>" style="display:flex;gap:8px">
        <?= csrf_field() ?>
        <input type="hidden" name="proposal_id" value="<?= (int)$p['id'] ?>">
        <button class="btn btn-light btn-sm" name="action" value="shortlist">Shortlist</button>
        <button class="btn btn-primary btn-sm" name="action" value="accept">Accept</button>
        <button class="btn btn-danger btn-sm" name="action" value="reject">Reject</button>
      </form>
      <?php endif; ?>
    </div>
  <?php endforeach; endif; ?>

<?php /* ---------------- Pro: submit / view proposal ---------------- */ ?>
<?php elseif ($u): ?>
  <?php if ($myProposal): ?>
    <div class="card" style="margin-top:28px">
      <div class="spread"><h3>Your proposal</h3><?= badge($myProposal['status']) ?></div>
      <div class="divider"></div>
      <p><strong><?= e(money($myProposal['amount'])) ?></strong><?= $myProposal['timeline'] ? ' · '.e($myProposal['timeline']) : '' ?></p>
      <p class="muted" style="margin-top:8px"><?= nl2br(e($myProposal['cover_letter'])) ?></p>
    </div>
  <?php elseif ($job['status'] === 'OPEN' || $job['status'] === 'IN_REVIEW'): ?>
    <div class="form-card form-wide" style="margin-top:28px">
      <h3 class="auth-title" style="font-size:20px">Submit a proposal</h3>
      <form method="post" action="<?= e(url('job.php?id=' . $id)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="propose">
        <div class="field"><label>Cover letter</label>
          <textarea class="textarea" name="cover_letter" placeholder="Why you're a great fit…" required></textarea></div>
        <div class="form-row">
          <div class="field"><label>Your price (KES)</label><input class="input" type="number" step="0.01" name="amount" required></div>
          <div class="field"><label>Timeline</label><input class="input" name="timeline" placeholder="e.g. 2 weeks"></div>
        </div>
        <button class="btn btn-primary" type="submit">Send proposal</button>
      </form>
    </div>
  <?php else: ?>
    <div class="empty" style="margin-top:28px"><h3>This job is closed</h3></div>
  <?php endif; ?>
<?php else: ?>
  <div class="empty" style="margin-top:28px">
    <h3>Want to bid on this job?</h3>
    <p><a class="btn btn-primary" href="<?= e(url('login.php')) ?>">Log in</a> or <a href="<?= e(url('register.php')) ?>">sign up</a> to submit a proposal.</p>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
