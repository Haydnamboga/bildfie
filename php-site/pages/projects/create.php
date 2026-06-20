<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Project name is required.';
    } else {
        $result = project_create($uid, $_POST);
        project_seed_default_tasks((int)$result['id']);
        $_SESSION['flash'] = 'Project created successfully!';
        header('Location: /pages/projects/view.php?id=' . urlencode($result['public_id']));
        exit;
    }
}

$sp           = 'create_project';
$topbar_title = 'New Project';
$page_title   = 'New Project';

$status_defs  = project_status_defs();
$billing_defs = project_billing_defs();
$priority_defs = project_priority_defs();
$currencies   = ['KES', 'USD', 'EUR', 'GBP', 'NGN', 'TZS', 'UGX'];
$types        = ['Residential', 'Commercial', 'Institutional', 'Renovation', 'Infrastructure', 'Government', 'Industrial'];
$trades       = ['Architect', 'Structural Engineer', 'Civil Engineer', 'MEP Engineer', 'Electrician', 'Plumber', 'Painter', 'Carpenter', 'Mason', 'Steel Fixer', 'Interior Designer', 'Quantity Surveyor', 'Site Foreman', 'Landscaper', 'Tiler', 'Welder', 'Solar Installer', 'Security Systems', 'Glazier', 'Roofing Specialist'];
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
    <a href="/pages/projects/" style="color:var(--ink-3);text-decoration:none;font-size:.85rem;">
      <i class="bi bi-arrow-left me-1"></i>Projects
    </a>
    <h2 style="font-size:1.2rem;font-weight:800;margin:0;">New Project</h2>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" novalidate>
    <div class="row g-0">
      <div class="col-12 col-xl-8">

        <!-- Basic info -->
        <div class="section-card">
          <div class="section-card-title">Project Details</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Project Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Westlands Office Renovation"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Project Type</label>
              <select name="type" class="form-select">
                <option value="">Select type...</option>
                <?php foreach ($types as $t): ?>
                  <option value="<?= $t ?>" <?= ($_POST['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Location</label>
              <input type="text" name="location" class="form-control" placeholder="e.g. Nairobi, Kenya"
                     value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Description</label>
              <textarea name="description" class="form-control" rows="4"
                        placeholder="Describe the project scope, requirements..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Budget & Finance -->
        <div class="section-card">
          <div class="section-card-title">Budget &amp; Finance</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Budget</label>
              <input type="number" name="budget" class="form-control" placeholder="0.00" min="0" step="0.01"
                     value="<?= htmlspecialchars($_POST['budget'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
              <select name="currency" class="form-select">
                <?php foreach ($currencies as $c): ?>
                  <option value="<?= $c ?>" <?= ($_POST['currency'] ?? 'KES') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Billing Type</label>
              <select name="billing_type" class="form-select">
                <?php foreach ($billing_defs as $k => $v): ?>
                  <option value="<?= $k ?>" <?= ($_POST['billing_type'] ?? 'milestone') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Payment Terms</label>
              <input type="text" name="payment_terms" class="form-control" placeholder="e.g. 30% upfront, 70% on completion"
                     value="<?= htmlspecialchars($_POST['payment_terms'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="section-card">
          <div class="section-card-title">Timeline</div>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">End Date</label>
              <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Est. Completion</label>
              <input type="date" name="est_completion" class="form-control" value="<?= htmlspecialchars($_POST['est_completion'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Deadline</label>
              <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Client info (optional) -->
        <div class="section-card">
          <div class="section-card-title">Client Information <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink-4);">(optional)</span></div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Name</label>
              <input type="text" name="customer_name" class="form-control" placeholder="Client's name"
                     value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Email</label>
              <input type="email" name="customer_email" class="form-control" placeholder="client@example.com"
                     value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:.8rem;font-weight:600;">Client Phone</label>
              <input type="text" name="customer_phone" class="form-control" placeholder="+254..."
                     value="<?= htmlspecialchars($_POST['customer_phone'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Trades needed -->
        <div class="section-card">
          <div class="section-card-title">Trades Needed</div>
          <div class="row g-2">
            <?php foreach ($trades as $trade): ?>
              <div class="col-6 col-md-4">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="trades[]"
                         value="<?= htmlspecialchars($trade) ?>"
                         id="trade_<?= md5($trade) ?>"
                         <?php if (!empty($_POST['trades']) && in_array($trade, (array)$_POST['trades'])): ?>checked<?php endif; ?>>
                  <label class="form-check-label" for="trade_<?= md5($trade) ?>" style="font-size:.85rem;">
                    <?= htmlspecialchars($trade) ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /col -->

      <!-- Right sidebar settings -->
      <div class="col-12 col-xl-4" style="padding-left:20px;">
        <div class="section-card">
          <div class="section-card-title">Project Settings</div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Status</label>
            <select name="status" class="form-select">
              <?php foreach ($status_defs as $k => [$label]): ?>
                <option value="<?= $k ?>" <?= ($_POST['status'] ?? 'started') === $k ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Priority</label>
            <select name="priority" class="form-select">
              <?php foreach ($priority_defs as $k => [$label]): ?>
                <option value="<?= $k ?>" <?= ($_POST['priority'] ?? 'normal') === $k ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Visibility</label>
            <select name="visibility" class="form-select">
              <option value="private" <?= ($_POST['visibility'] ?? 'private') === 'private' ? 'selected' : '' ?>>Private — only me</option>
              <option value="public" <?= ($_POST['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public — visible on marketplace</option>
            </select>
          </div>
        </div>

        <div class="section-card">
          <div class="section-card-title">Collaboration</div>
          <?php $bools = ['client_can_comment'=>'Client can comment','client_can_view_budget'=>'Client can view budget','client_can_view_documents'=>'Client can view documents','require_milestone_approval'=>'Require milestone approval','use_escrow'=>'Use escrow payments','notify_client_updates'=>'Notify client on updates']; ?>
          <?php foreach ($bools as $k => $label): ?>
            <div class="form-check mb-2">
              <input type="checkbox" class="form-check-input" name="<?= $k ?>" id="<?= $k ?>"
                     <?= !empty($_POST[$k]) ? 'checked' : '' ?>>
              <label class="form-check-label" for="<?= $k ?>" style="font-size:.83rem;"><?= $label ?></label>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="bf-btn-dark" style="justify-content:center;padding:12px;">
            <i class="bi bi-check-lg me-1"></i>Create Project
          </button>
          <a href="/pages/projects/" class="btn btn-light">Cancel</a>
        </div>
      </div>
    </div>
  </form>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
