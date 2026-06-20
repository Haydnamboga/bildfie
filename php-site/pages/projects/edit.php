<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$key = trim($_GET['id'] ?? '');
if ($key === '') { header('Location: /pages/projects/'); exit; }

$project = project_get($key, $uid);
if (!$project) {
    http_response_code(404);
    echo '<div class="container py-5 text-center"><h1>Project not found</h1><a href="/pages/projects/" class="bf-btn-dark mt-3">Back</a></div>';
    exit;
}
$pid = (int) $project['id'];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'delete') {
        // Only delete own project
        db_stmt("DELETE FROM projects WHERE id=? AND owner_user_id=?", [$pid, $uid]);
        $_SESSION['flash'] = 'Project deleted.';
        header('Location: /pages/projects/');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Project name is required.';
    } else {
        project_update($pid, $uid, $_POST);
        $_SESSION['flash'] = 'Project updated.';
        header('Location: /pages/projects/view.php?id=' . urlencode($project['public_id']));
        exit;
    }
}

// Pre-fill from project or POST
$d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $project;
$trades_set = !empty($project['trades']) ? array_map('trim', explode(',', $project['trades'])) : [];
if (!empty($_POST['trades'])) $trades_set = (array)$_POST['trades'];

$sp           = 'projects';
$topbar_title = 'Edit Project';
$page_title   = 'Edit Project';

$status_defs   = project_status_defs();
$billing_defs  = project_billing_defs();
$priority_defs = project_priority_defs();
$currencies    = ['KES', 'USD', 'EUR', 'GBP', 'NGN', 'TZS', 'UGX'];
$types         = ['Residential', 'Commercial', 'Institutional', 'Renovation', 'Infrastructure', 'Government', 'Industrial'];
$trades_list   = ['Architect', 'Structural Engineer', 'Civil Engineer', 'MEP Engineer', 'Electrician', 'Plumber', 'Painter', 'Carpenter', 'Mason', 'Steel Fixer', 'Interior Designer', 'Quantity Surveyor', 'Site Foreman', 'Landscaper', 'Tiler', 'Welder', 'Solar Installer', 'Security Systems', 'Glazier', 'Roofing Specialist'];
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.section-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:24px;margin-bottom:20px;}
.section-card-title{font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--line-2);}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="/pages/projects/view.php?id=<?= urlencode($project['public_id']) ?>" style="color:var(--ink-3);text-decoration:none;font-size:.85rem;">
      <i class="bi bi-arrow-left me-1"></i>Back to project
    </a>
    <h2 style="font-size:1.2rem;font-weight:800;margin:0;">Edit: <?= htmlspecialchars($project['name']) ?></h2>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="row g-0">
      <div class="col-12 col-xl-8">

        <div class="section-card">
          <div class="section-card-title">Project Details</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Project Name *</label>
              <input type="text" name="name" class="form-control" required
                     value="<?= htmlspecialchars($d['name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Type</label>
              <select name="type" class="form-select">
                <option value="">Select type...</option>
                <?php foreach ($types as $t): ?>
                  <option value="<?= $t ?>" <?= ($d['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Location</label>
              <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($d['location'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Description</label>
              <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($d['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="section-card-title">Budget &amp; Finance</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Budget</label>
              <input type="number" name="budget" class="form-control" min="0" step="0.01"
                     value="<?= htmlspecialchars($d['budget'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency" class="form-select">
                <?php foreach ($currencies as $c): ?>
                  <option value="<?= $c ?>" <?= ($d['currency'] ?? 'KES') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Billing Type</label>
              <select name="billing_type" class="form-select">
                <?php foreach ($billing_defs as $k => $v): ?>
                  <option value="<?= $k ?>" <?= ($d['billing_type'] ?? 'milestone') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Payment Terms</label>
              <input type="text" name="payment_terms" class="form-control" value="<?= htmlspecialchars($d['payment_terms'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="section-card-title">Timeline</div>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($d['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">End Date</label>
              <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($d['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Est. Completion</label>
              <input type="date" name="est_completion" class="form-control" value="<?= htmlspecialchars($d['est_completion'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Deadline</label>
              <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($d['deadline'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="section-card-title">Client Information</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Name</label>
              <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($d['customer_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Email</label>
              <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($d['customer_email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Phone</label>
              <input type="text" name="customer_phone" class="form-control" value="<?= htmlspecialchars($d['customer_phone'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="section-card-title">Trades Needed</div>
          <div class="row g-2">
            <?php foreach ($trades_list as $trade): ?>
              <div class="col-6 col-md-4">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="trades[]"
                         value="<?= htmlspecialchars($trade) ?>" id="trade_e_<?= md5($trade) ?>"
                         <?= in_array($trade, $trades_set) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="trade_e_<?= md5($trade) ?>" style="font-size:.85rem;">
                    <?= htmlspecialchars($trade) ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <div class="col-12 col-xl-4" style="padding-left:20px;">
        <div class="section-card">
          <div class="section-card-title">Settings</div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Status</label>
            <select name="status" class="form-select">
              <?php foreach ($status_defs as $k => [$label]): ?>
                <option value="<?= $k ?>" <?= ($d['status'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Priority</label>
            <select name="priority" class="form-select">
              <?php foreach ($priority_defs as $k => [$label]): ?>
                <option value="<?= $k ?>" <?= ($d['priority'] ?? 'normal') === $k ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Progress (%)</label>
            <input type="number" name="progress" class="form-control" min="0" max="100"
                   value="<?= htmlspecialchars($d['progress'] ?? '0') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Visibility</label>
            <select name="visibility" class="form-select">
              <option value="private" <?= ($d['visibility'] ?? 'private') === 'private' ? 'selected' : '' ?>>Private</option>
              <option value="public" <?= ($d['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
            </select>
          </div>
        </div>

        <div class="d-grid gap-2 mb-3">
          <button type="submit" class="bf-btn-dark" style="justify-content:center;padding:12px;">
            <i class="bi bi-check-lg me-1"></i>Save Changes
          </button>
          <a href="/pages/projects/view.php?id=<?= urlencode($project['public_id']) ?>" class="btn btn-light">Cancel</a>
        </div>

        <!-- Danger zone -->
        <div class="section-card" style="border-color:#fee2e2;">
          <div class="section-card-title" style="color:#b91c1c;">Danger Zone</div>
          <p style="font-size:.83rem;color:var(--ink-3);margin-bottom:12px;">Deleting this project is permanent and cannot be undone.</p>
          <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this project? This cannot be undone.');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger btn-sm w-100">
              <i class="bi bi-trash me-1"></i>Delete Project
            </button>
          </form>
        </div>
      </div>
    </div>
  </form>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
