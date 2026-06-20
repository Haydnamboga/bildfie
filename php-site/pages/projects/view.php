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
    echo '<div class="container py-5 text-center"><h1 style="font-size:1.4rem;font-weight:700;">Project not found</h1><a href="/pages/projects/" class="bf-btn-dark mt-3">Back to Projects</a></div>';
    exit;
}
$pid = (int) $project['id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    // Milestones
    if ($action === 'milestone_add')         { milestone_add($pid, $uid, $_POST); }
    elseif ($action === 'milestone_edit')    { $mid = (int)($_POST['mid'] ?? 0); milestone_edit($mid, $uid, $_POST); }
    elseif ($action === 'milestone_set_status') { $mid = (int)($_POST['mid'] ?? 0); milestone_set_status($mid, $uid, $_POST['status'] ?? 'upcoming'); }
    elseif ($action === 'milestone_delete')  { $mid = (int)($_POST['mid'] ?? 0); milestone_delete($mid, $uid); }
    // Tasks
    elseif ($action === 'task_add')          { task_add($pid, $uid, $_POST); }
    elseif ($action === 'task_edit')         { $tid = (int)($_POST['tid'] ?? 0); task_edit($tid, $uid, $_POST); }
    elseif ($action === 'task_set_status')   { $tid = (int)($_POST['tid'] ?? 0); task_set_status($tid, $uid, $_POST['status'] ?? 'todo'); }
    elseif ($action === 'task_delete')       { $tid = (int)($_POST['tid'] ?? 0); task_delete($tid, $uid); }
    // Team
    elseif ($action === 'team_member_add')   { task_member_add((int)($_POST['task_id'] ?? 0), $uid, $_POST['name'] ?? '', $_POST['role'] ?? null); }
    elseif ($action === 'team_member_remove'){ task_member_remove((int)($_POST['member_id'] ?? 0), $uid); }
    // Project status
    elseif ($action === 'project_set_status'){ project_set_status($pid, $uid, $_POST['status'] ?? ''); }
    // Settings
    elseif ($action === 'project_settings_save') { project_settings_save($pid, $uid, $_POST); }

    // Reload project data and redirect back to same tab
    $tab = $_POST['_tab'] ?? $_GET['tab'] ?? 'overview';
    header('Location: /pages/projects/view.php?id=' . urlencode($project['public_id']) . '&tab=' . urlencode($tab));
    exit;
}

$tab = $_GET['tab'] ?? 'overview';

// Data
$milestones  = project_milestones($pid);
$ms_summary  = project_milestone_summary($pid);
$tasks_tree  = project_tasks_tree($pid);
$team        = project_team_roster($pid);
$status_defs = project_status_defs();
$task_status = project_task_status_defs();
$task_roles  = project_task_roles();

[$s_label, $s_color, $s_bg] = project_status_style($project['status'] ?? 'draft');

$sp           = 'projects';
$topbar_title = 'Project Workspace';
$page_title   = htmlspecialchars($project['name']);

function task_status_badge(string $s): string {
    $defs = project_task_status_defs();
    [$l,$c,$b] = $defs[$s] ?? ['To Do','#6b6b6b','#f4f4f2'];
    return '<span style="display:inline-block;padding:1px 8px;border-radius:20px;font-size:.7rem;font-weight:600;color:'.$c.';background:'.$b.';">'.$l.'</span>';
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:0;background:var(--surface);}
.pv-header{background:#fff;border-bottom:1px solid var(--line);padding:20px 24px;}
.pv-tabs{display:flex;gap:0;border-bottom:1px solid var(--line);background:#fff;overflow-x:auto;}
.pv-tab{font-size:.875rem;font-weight:600;color:var(--ink-3);padding:12px 18px;border-bottom:2px solid transparent;text-decoration:none;white-space:nowrap;}
.pv-tab.active{color:#1e3a5f;border-bottom-color:#1e3a5f;}
.pv-content{padding:24px;}
.section-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px;margin-bottom:16px;}
.ms-item{border:1px solid var(--line);border-radius:10px;padding:14px 16px;background:#fff;margin-bottom:10px;}
.task-section{border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-bottom:12px;}
.task-section-head{background:var(--surface);padding:10px 14px;font-weight:700;font-size:.875rem;border-bottom:1px solid var(--line);}
.task-row{padding:9px 14px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;gap:10px;font-size:.85rem;}
.task-row:last-child{border-bottom:none;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <!-- Project header -->
  <div class="pv-header">
    <div class="d-flex flex-wrap align-items-start gap-3 justify-content-between">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <a href="/pages/projects/" style="color:var(--ink-3);text-decoration:none;font-size:.8rem;"><i class="bi bi-arrow-left"></i> Projects</a>
        </div>
        <h1 style="font-size:1.3rem;font-weight:800;margin:0 0 4px;"><?= htmlspecialchars($project['name']) ?></h1>
        <div class="d-flex flex-wrap gap-2 align-items-center" style="font-size:.8rem;color:var(--ink-3);">
          <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:<?= $s_color ?>;background:<?= $s_bg ?>;"><?= $s_label ?></span>
          <?php if ($project['type']): ?><span><?= htmlspecialchars($project['type']) ?></span><?php endif; ?>
          <?php if ($project['location']): ?><span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($project['location']) ?></span><?php endif; ?>
          <?php if ($project['deadline']): ?><span><i class="bi bi-calendar me-1"></i>Due <?= date('d M Y', strtotime($project['deadline'])) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="/pages/projects/edit.php?id=<?= urlencode($project['public_id']) ?>" class="bf-btn-outline btn-sm">
          <i class="bi bi-pencil me-1"></i>Edit
        </a>
      </div>
    </div>
    <!-- Progress bar -->
    <?php $progress = (int)($project['progress'] ?? 0); ?>
    <div class="mt-3">
      <div class="d-flex justify-content-between" style="font-size:.75rem;color:var(--ink-3);margin-bottom:4px;">
        <span>Progress</span><span><?= $progress ?>%</span>
      </div>
      <div style="height:6px;background:var(--line);border-radius:3px;overflow:hidden;">
        <div style="width:<?= $progress ?>%;height:100%;background:#1e3a5f;border-radius:3px;transition:width .3s;"></div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="pv-tabs">
    <?php foreach ([['overview','Overview'],['milestones','Milestones ('.(count($milestones)).')'],['tasks','Tasks'],['team','Team ('.(count($team)).')'],['settings','Settings']] as [$tk,$tl]): ?>
      <a href="?id=<?= urlencode($key) ?>&tab=<?= $tk ?>" class="pv-tab <?= $tab === $tk ? 'active' : '' ?>"><?= $tl ?></a>
    <?php endforeach; ?>
  </div>

  <div class="pv-content">

    <?php if ($tab === 'overview'): ?>
    <div class="row g-3">
      <div class="col-md-8">
        <div class="section-card">
          <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3);margin-bottom:14px;">Project Details</div>
          <div class="row g-3" style="font-size:.875rem;">
            <?php $details = [
              'Customer' => $project['customer_name'] ?? '—',
              'Budget' => $project['budget_display'] ?? '—',
              'Billing' => project_billing_label($project['billing_type'] ?? ''),
              'Priority' => ucfirst($project['priority'] ?? 'normal'),
              'Start Date' => $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : '—',
              'End Date' => $project['end_date'] ? date('d M Y', strtotime($project['end_date'])) : '—',
              'Deadline' => $project['deadline'] ? date('d M Y', strtotime($project['deadline'])) : '—',
              'Visibility' => ucfirst($project['visibility'] ?? 'private'),
            ]; ?>
            <?php foreach ($details as $k => $v): ?>
              <div class="col-6 col-md-4">
                <div style="font-size:.72rem;color:var(--ink-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;"><?= $k ?></div>
                <div style="font-weight:600;color:#0d0d0d;"><?= htmlspecialchars($v) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ($project['description']): ?>
            <div class="mt-3 pt-3" style="border-top:1px solid var(--line-2);">
              <div style="font-size:.72rem;color:var(--ink-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Description</div>
              <p style="font-size:.875rem;color:var(--ink-2);margin:0;line-height:1.7;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="section-card text-center">
          <div style="font-size:.72rem;color:var(--ink-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;">Milestone Progress</div>
          <div style="position:relative;width:100px;height:100px;margin:0 auto 12px;">
            <svg viewBox="0 0 36 36" style="width:100px;height:100px;transform:rotate(-90deg);">
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e8e8e8" stroke-width="3"/>
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e3a5f" stroke-width="3"
                      stroke-dasharray="<?= $ms_summary['pct'] . ' ' . (100 - $ms_summary['pct']) ?>"
                      stroke-linecap="round"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#0d0d0d;"><?= $ms_summary['pct'] ?>%</div>
          </div>
          <div style="font-size:.85rem;color:var(--ink-3);"><?= $ms_summary['done'] ?> of <?= $ms_summary['total'] ?> milestones done</div>
          <?php if ($ms_summary['value'] > 0): ?>
            <div class="mt-2" style="font-size:.8rem;color:var(--ink-3);">
              Value: <?= htmlspecialchars($project['currency'] ?? 'KES') ?> <?= number_format($ms_summary['done_value']) ?> / <?= number_format($ms_summary['value']) ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Quick status change -->
        <div class="section-card">
          <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3);margin-bottom:10px;">Set Status</div>
          <form method="POST">
            <input type="hidden" name="_action" value="project_set_status">
            <input type="hidden" name="_tab" value="overview">
            <select name="status" class="form-select form-select-sm mb-2">
              <?php foreach ($status_defs as $k => [$l]): ?>
                <option value="<?= $k ?>" <?= ($project['status'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="bf-btn-dark w-100 btn-sm" style="justify-content:center;">Update Status</button>
          </form>
        </div>
      </div>
    </div>

    <?php elseif ($tab === 'milestones'): ?>
    <!-- Add milestone form -->
    <div class="section-card mb-3">
      <div style="font-size:.8rem;font-weight:700;margin-bottom:12px;">Add Milestone</div>
      <form method="POST" class="row g-2 align-items-end">
        <input type="hidden" name="_action" value="milestone_add">
        <input type="hidden" name="_tab" value="milestones">
        <div class="col-md-4"><input type="text" name="title" class="form-control form-control-sm" placeholder="Milestone title *" required></div>
        <div class="col-md-2"><input type="number" name="amount" class="form-control form-control-sm" placeholder="Amount" min="0" step="0.01"></div>
        <div class="col-md-2"><input type="date" name="due_date" class="form-control form-control-sm"></div>
        <div class="col-md-2">
          <select name="status" class="form-select form-select-sm">
            <option value="upcoming">Upcoming</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div class="col-md-2"><button type="submit" class="bf-btn-dark w-100 btn-sm" style="justify-content:center;">Add</button></div>
        <div class="col-12"><input type="text" name="description" class="form-control form-control-sm" placeholder="Description (optional)"></div>
      </form>
    </div>

    <?php if (empty($milestones)): ?>
      <div class="text-center py-4" style="color:var(--ink-3);font-size:.875rem;">No milestones yet.</div>
    <?php else: ?>
      <?php foreach ($milestones as $ms):
        $ms_col = $ms['status'] === 'completed' ? '#16a34a' : ($ms['status'] === 'active' ? '#1e40af' : '#6b6b6b');
        $ms_bg  = $ms['status'] === 'completed' ? '#dcfce7' : ($ms['status'] === 'active' ? '#eff6ff' : '#f4f4f2');
      ?>
      <div class="ms-item">
        <div class="d-flex align-items-start gap-3">
          <div style="width:28px;height:28px;border-radius:50%;background:<?= $ms_bg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
            <i class="bi bi-<?= $ms['status']==='completed'?'check-lg':'flag' ?>" style="font-size:.75rem;color:<?= $ms_col ?>;"></i>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($ms['title']) ?></div>
            <?php if ($ms['description']): ?><div style="font-size:.8rem;color:var(--ink-3);"><?= htmlspecialchars($ms['description']) ?></div><?php endif; ?>
            <div class="d-flex gap-3 mt-1" style="font-size:.75rem;color:var(--ink-3);">
              <span style="color:<?= $ms_col ?>;font-weight:600;"><?= ucfirst($ms['status']) ?></span>
              <?php if ($ms['due_date']): ?><span><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($ms['due_date'])) ?></span><?php endif; ?>
              <?php if ($ms['amount']): ?><span><?= htmlspecialchars($project['currency'] ?? 'KES') ?> <?= number_format((float)$ms['amount']) ?></span><?php endif; ?>
            </div>
          </div>
          <div class="d-flex gap-1">
            <!-- Status buttons -->
            <?php foreach (['upcoming','active','completed'] as $ms_s): ?>
              <?php if ($ms['status'] !== $ms_s): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="_action" value="milestone_set_status">
                  <input type="hidden" name="_tab" value="milestones">
                  <input type="hidden" name="mid" value="<?= (int)$ms['id'] ?>">
                  <input type="hidden" name="status" value="<?= $ms_s ?>">
                  <button type="submit" class="btn btn-xs btn-light" style="font-size:.7rem;padding:2px 6px;" title="Mark <?= $ms_s ?>">
                    <?= ucfirst($ms_s) ?>
                  </button>
                </form>
              <?php endif; ?>
            <?php endforeach; ?>
            <form method="POST" onsubmit="return confirm('Delete this milestone?');" style="display:inline;">
              <input type="hidden" name="_action" value="milestone_delete">
              <input type="hidden" name="_tab" value="milestones">
              <input type="hidden" name="mid" value="<?= (int)$ms['id'] ?>">
              <button type="submit" class="btn btn-xs btn-light text-danger" style="font-size:.7rem;padding:2px 6px;">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php elseif ($tab === 'tasks'): ?>
    <!-- Add section form -->
    <div class="section-card mb-3">
      <div class="row g-2">
        <div class="col-md-6">
          <div style="font-size:.8rem;font-weight:700;margin-bottom:8px;">Add Section (Phase)</div>
          <form method="POST" class="d-flex gap-2">
            <input type="hidden" name="_action" value="task_add">
            <input type="hidden" name="_tab" value="tasks">
            <input type="hidden" name="is_milestone" value="1">
            <input type="text" name="title" class="form-control form-control-sm" placeholder="Section name *" required>
            <button type="submit" class="bf-btn-dark btn-sm" style="white-space:nowrap;">Add Section</button>
          </form>
        </div>
      </div>
    </div>

    <?php if (empty($tasks_tree)): ?>
      <div class="text-center py-4 section-card">
        <p style="color:var(--ink-3);font-size:.875rem;margin-bottom:12px;">No tasks yet. A default construction work breakdown was seeded when you created the project.</p>
      </div>
    <?php else: ?>
      <?php foreach ($tasks_tree as $section): ?>
        <div class="task-section">
          <div class="task-section-head d-flex align-items-center justify-content-between">
            <span><?= htmlspecialchars($section['title']) ?></span>
            <div class="d-flex gap-2 align-items-center">
              <span style="font-size:.75rem;color:var(--ink-3);"><?= count($section['subtasks']) ?> tasks</span>
              <!-- Add subtask inline form trigger -->
              <button class="btn btn-xs btn-light" style="font-size:.72rem;" type="button"
                      data-bs-toggle="collapse" data-bs-target="#add_task_<?= (int)$section['id'] ?>">
                <i class="bi bi-plus"></i> Task
              </button>
              <form method="POST" onsubmit="return confirm('Delete this section and all its tasks?');" style="display:inline;">
                <input type="hidden" name="_action" value="task_delete">
                <input type="hidden" name="_tab" value="tasks">
                <input type="hidden" name="tid" value="<?= (int)$section['id'] ?>">
                <button type="submit" class="btn btn-xs btn-light text-danger" style="font-size:.72rem;"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </div>
          <!-- Add subtask form -->
          <div class="collapse" id="add_task_<?= (int)$section['id'] ?>">
            <form method="POST" class="d-flex gap-2 p-3" style="background:#f8f8f6;border-bottom:1px solid var(--line);">
              <input type="hidden" name="_action" value="task_add">
              <input type="hidden" name="_tab" value="tasks">
              <input type="hidden" name="parent_id" value="<?= (int)$section['id'] ?>">
              <input type="text" name="title" class="form-control form-control-sm" placeholder="Task name *" required>
              <select name="status" class="form-select form-select-sm" style="width:120px;">
                <?php foreach ($task_status as $k => [$l]): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?>
              </select>
              <button type="submit" class="bf-btn-dark btn-sm" style="white-space:nowrap;">Add</button>
            </form>
          </div>
          <!-- Subtasks -->
          <?php foreach ($section['subtasks'] as $task): ?>
            <div class="task-row">
              <form method="POST" style="display:contents;">
                <input type="hidden" name="_action" value="task_set_status">
                <input type="hidden" name="_tab" value="tasks">
                <input type="hidden" name="tid" value="<?= (int)$task['id'] ?>">
                <select name="status" class="form-select form-select-sm" style="width:110px;font-size:.72rem;"
                        onchange="this.form.submit()">
                  <?php foreach ($task_status as $k => [$l]): ?>
                    <option value="<?= $k ?>" <?= $task['status'] === $k ? 'selected' : '' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
              <span style="flex:1;color:#0d0d0d;"><?= htmlspecialchars($task['title']) ?></span>
              <?php if ($task['due_date']): ?>
                <span style="font-size:.72rem;color:var(--ink-3);"><?= date('d M', strtotime($task['due_date'])) ?></span>
              <?php endif; ?>
              <?= task_status_badge($task['status']) ?>
              <form method="POST" onsubmit="return confirm('Delete this task?');" style="display:inline;">
                <input type="hidden" name="_action" value="task_delete">
                <input type="hidden" name="_tab" value="tasks">
                <input type="hidden" name="tid" value="<?= (int)$task['id'] ?>">
                <button type="submit" class="btn btn-xs btn-light text-danger" style="font-size:.7rem;padding:2px 5px;"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php elseif ($tab === 'team'): ?>
    <!-- Add team member -->
    <div class="section-card mb-3">
      <div style="font-size:.8rem;font-weight:700;margin-bottom:12px;">Add Team Member</div>
      <?php $task_opts = project_task_options($pid); ?>
      <form method="POST" class="row g-2 align-items-end">
        <input type="hidden" name="_action" value="team_member_add">
        <input type="hidden" name="_tab" value="team">
        <div class="col-md-3">
          <label style="font-size:.75rem;font-weight:600;">Name *</label>
          <input type="text" name="name" class="form-control form-control-sm" placeholder="Full name" required>
        </div>
        <div class="col-md-3">
          <label style="font-size:.75rem;font-weight:600;">Role</label>
          <select name="role" class="form-select form-select-sm">
            <option value="">Select role...</option>
            <?php foreach ($task_roles as $r): ?><option value="<?= $r ?>"><?= $r ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label style="font-size:.75rem;font-weight:600;">Assign to Task</label>
          <select name="task_id" class="form-select form-select-sm" required>
            <option value="">Select task...</option>
            <?php foreach ($task_opts as $to): ?>
              <option value="<?= (int)$to['id'] ?>"><?= $to['parent_id'] ? '— ' : '' ?><?= htmlspecialchars($to['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2"><button type="submit" class="bf-btn-dark w-100 btn-sm" style="justify-content:center;">Add</button></div>
      </form>
    </div>

    <?php if (empty($team)): ?>
      <div class="text-center py-4" style="color:var(--ink-3);font-size:.875rem;">No team members yet.</div>
    <?php else: ?>
      <div class="bf-pf-card">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle" style="font-size:.875rem;">
            <thead style="background:var(--surface);">
              <tr>
                <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:10px 16px;">Name</th>
                <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Role</th>
                <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Task / Phase</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($team as $m): ?>
              <tr>
                <td style="padding:10px 16px;font-weight:600;"><?= htmlspecialchars($m['name']) ?></td>
                <td style="color:var(--ink-2);"><?= htmlspecialchars($m['role'] ?? '—') ?></td>
                <td style="font-size:.8rem;color:var(--ink-3);"><?= htmlspecialchars($m['task_title']) ?></td>
                <td>
                  <form method="POST" onsubmit="return confirm('Remove this team member?');" style="display:inline;">
                    <input type="hidden" name="_action" value="team_member_remove">
                    <input type="hidden" name="_tab" value="team">
                    <input type="hidden" name="member_id" value="<?= (int)$m['id'] ?>">
                    <button type="submit" class="btn btn-xs btn-light text-danger" style="font-size:.75rem;padding:3px 8px;">Remove</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php elseif ($tab === 'settings'): ?>
    <!-- Collaboration settings -->
    <div class="section-card">
      <div style="font-size:.8rem;font-weight:700;margin-bottom:14px;">Collaboration Settings</div>
      <form method="POST">
        <input type="hidden" name="_action" value="project_settings_save">
        <input type="hidden" name="_tab" value="settings">
        <?php $bools = ['client_can_comment'=>'Client can comment on project','client_can_view_budget'=>'Client can view budget','client_can_view_documents'=>'Client can view documents','require_milestone_approval'=>'Require client milestone approval','use_escrow'=>'Use escrow for payments','notify_client_updates'=>'Notify client on updates']; ?>
        <?php foreach ($bools as $k => $label): ?>
          <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" name="<?= $k ?>" id="s_<?= $k ?>"
                   <?= !empty($project[$k]) ? 'checked' : '' ?>>
            <label class="form-check-label" for="s_<?= $k ?>" style="font-size:.875rem;"><?= $label ?></label>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="bf-btn-dark mt-2">Save Settings</button>
      </form>
    </div>

    <!-- Danger zone -->
    <div class="section-card" style="border-color:#fee2e2;">
      <div style="font-size:.8rem;font-weight:700;color:#b91c1c;margin-bottom:8px;">Danger Zone</div>
      <p style="font-size:.83rem;color:var(--ink-3);margin-bottom:12px;">Permanently delete this project and all its milestones, tasks, and team members.</p>
      <form method="POST" action="/pages/projects/edit.php?id=<?= urlencode($key) ?>"
            onsubmit="return confirm('Are you absolutely sure? This cannot be undone.');">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger btn-sm">
          <i class="bi bi-trash me-1"></i>Delete Project Permanently
        </button>
      </form>
    </div>
    <?php endif; ?>

  </div><!-- /.pv-content -->
</div><!-- /.bf-body -->
</div><!-- /.bf-main -->
</div><!-- /.bf-layout -->

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
