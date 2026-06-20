<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$projects = user_projects($uid);

$sp           = 'projects';
$topbar_title = 'My Projects';
$page_title   = 'My Projects';

$tab = $_GET['tab'] ?? 'mine';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.status-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 style="font-size:1.3rem;font-weight:800;margin:0;">Projects</h2>
      <p style="font-size:.875rem;color:var(--ink-3);margin:0;"><?= count($projects) ?> project<?= count($projects) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/pages/projects/create.php" class="bf-btn-dark">
      <i class="bi bi-plus-lg me-1"></i>New Project
    </a>
  </div>

  <!-- Tabs -->
  <div style="border-bottom:1px solid var(--line);margin-bottom:20px;display:flex;gap:0;">
    <a href="?tab=mine" style="font-size:.875rem;font-weight:600;padding:8px 16px;text-decoration:none;border-bottom:2px solid <?= $tab==='mine'?'#1e3a5f':'transparent' ?>;color:<?= $tab==='mine'?'#1e3a5f':'var(--ink-3)' ?>;">My Projects</a>
    <a href="?tab=public" style="font-size:.875rem;font-weight:600;padding:8px 16px;text-decoration:none;border-bottom:2px solid <?= $tab==='public'?'#1e3a5f':'transparent' ?>;color:<?= $tab==='public'?'#1e3a5f':'var(--ink-3)' ?>;">Public Showcase</a>
  </div>

  <?php
  $display = ($tab === 'public')
    ? array_filter($projects, fn($p) => $p['visibility'] === 'public')
    : $projects;
  $display = array_values($display);
  ?>

  <?php if (empty($display)): ?>
    <div class="text-center py-5">
      <i class="bi bi-kanban" style="font-size:3rem;color:var(--line);display:block;margin-bottom:16px;"></i>
      <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">
        <?= $tab === 'public' ? 'No public projects yet' : 'No projects yet' ?>
      </h3>
      <p style="color:var(--ink-3);font-size:.875rem;">
        <?= $tab === 'public' ? 'Set a project to Public visibility to showcase it here.' : 'Post your first project to get started.' ?>
      </p>
      <?php if ($tab !== 'public'): ?>
        <a href="/pages/projects/create.php" class="bf-btn-dark mt-2">Post a Project</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="bf-pf-card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--surface);">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:10px 16px;">Project</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Type</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Status</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Progress</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Deadline</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Budget</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($display as $p):
              [$label, $color, $bg] = project_status_style($p['status'] ?? 'draft');
              $progress = (int)($p['progress'] ?? 0);
            ?>
            <tr>
              <td style="padding:12px 16px;">
                <div style="font-weight:600;color:#0d0d0d;">
                  <a href="/pages/projects/view.php?id=<?= urlencode($p['public_id']) ?>" style="color:inherit;text-decoration:none;">
                    <?= htmlspecialchars($p['name']) ?>
                  </a>
                </div>
                <?php if ($p['location']): ?>
                  <div style="font-size:.75rem;color:var(--ink-3);"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['location']) ?></div>
                <?php endif; ?>
              </td>
              <td style="color:var(--ink-2);"><?= htmlspecialchars($p['type'] ?? '—') ?></td>
              <td>
                <span class="status-badge" style="color:<?= $color ?>;background:<?= $bg ?>;"><?= htmlspecialchars($label) ?></span>
              </td>
              <td style="min-width:100px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="flex:1;height:5px;background:var(--line);border-radius:3px;overflow:hidden;">
                    <div style="width:<?= $progress ?>%;height:100%;background:#1e3a5f;border-radius:3px;"></div>
                  </div>
                  <span style="font-size:.75rem;color:var(--ink-3);white-space:nowrap;"><?= $progress ?>%</span>
                </div>
              </td>
              <td style="color:var(--ink-3);">
                <?= $p['deadline'] ? date('d M Y', strtotime($p['deadline'])) : '—' ?>
              </td>
              <td style="font-weight:600;">
                <?= $p['budget_display'] ? htmlspecialchars($p['budget_display']) : '—' ?>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="/pages/projects/view.php?id=<?= urlencode($p['public_id']) ?>" class="btn btn-xs btn-light" style="font-size:.75rem;padding:3px 8px;">
                    <i class="bi bi-eye"></i> View
                  </a>
                  <a href="/pages/projects/edit.php?id=<?= urlencode($p['public_id']) ?>" class="btn btn-xs btn-light" style="font-size:.75rem;padding:3px 8px;">
                    <i class="bi bi-pencil"></i> Edit
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
