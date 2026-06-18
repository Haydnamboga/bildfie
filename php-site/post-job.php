<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$u = current_user();
$page_title = 'Post a job';
$errors = [];
$old = ['title'=>'','description'=>'','category'=>'','location'=>$u['location'] ?? '','budget_min'=>'','budget_max'=>'','due_date'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($old as $k=>$v) $old[$k] = trim($_POST[$k] ?? '');

    if ($old['title'] === '')                              $errors[] = 'Title is required.';
    if (strlen($old['description']) < 10)                  $errors[] = 'Please add a longer description.';
    if (!in_array($old['category'], trade_categories(), true)) $errors[] = 'Choose a trade category.';
    if ($old['location'] === '')                           $errors[] = 'Location is required.';

    if (!$errors) {
        $bmin = $old['budget_min'] === '' ? null : (float)$old['budget_min'];
        $bmax = $old['budget_max'] === '' ? null : (float)$old['budget_max'];
        $due  = $old['due_date'] === '' ? null : $old['due_date'];
        $stmt = $mysqli->prepare(
          'INSERT INTO job_posts (client_id, title, description, category, location, budget_min, budget_max, due_date)
           VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->bind_param('issssdds', $u['id'], $old['title'], $old['description'], $old['category'], $old['location'], $bmin, $bmax, $due);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id; $stmt->close();
            flash('Job posted! Pros can now send you proposals.');
            redirect('job.php?id=' . $newId);
        }
        $stmt->close();
        $errors[] = 'Could not save the job. Please try again.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= e(url('jobs.php')) ?>">← Back to jobs</a>
<div class="form-card form-wide">
  <h1 class="auth-title">Post a job</h1>
  <p class="auth-sub">Describe the work and start receiving proposals from pros.</p>
  <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" action="<?= e(url('post-job.php')) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Job title</label>
      <input class="input" name="title" value="<?= e($old['title']) ?>" placeholder="e.g. Rewire 3-bedroom house" required></div>
    <div class="form-row">
      <div class="field"><label>Trade</label>
        <select name="category" class="select" required>
          <option value="">Select…</option>
          <?php foreach (trade_categories() as $c): ?><option value="<?= e($c) ?>" <?= $old['category']===$c?'selected':'' ?>><?= e(nice($c)) ?></option><?php endforeach; ?>
        </select></div>
      <div class="field"><label>Location</label>
        <input class="input" name="location" value="<?= e($old['location']) ?>" placeholder="e.g. Nairobi" required></div>
    </div>
    <div class="field"><label>Description</label>
      <textarea class="textarea" name="description" placeholder="Scope, materials, expectations…" required><?= e($old['description']) ?></textarea></div>
    <div class="form-row">
      <div class="field"><label>Budget min (KES)</label><input class="input" type="number" step="0.01" name="budget_min" value="<?= e($old['budget_min']) ?>"></div>
      <div class="field"><label>Budget max (KES)</label><input class="input" type="number" step="0.01" name="budget_max" value="<?= e($old['budget_max']) ?>"></div>
      <div class="field"><label>Due date</label><input class="input" type="date" name="due_date" value="<?= e($old['due_date']) ?>"></div>
    </div>
    <button class="btn btn-primary" type="submit">Publish job</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
