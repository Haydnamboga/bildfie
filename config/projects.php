<?php
/**
 * Project workspace helpers — status lifecycle, billing models, and CRUD
 * for the dashboard Projects feature (owner-scoped).
 */
require_once __DIR__ . '/db.php';

function project_uuid(): string {
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/** Status lifecycle, in board order → [label, text colour, bg colour]. */
function project_status_defs(): array {
    return [
        'started'     => ['Started',     '#4338ca', '#eef2ff'],
        'in_progress' => ['In Progress', '#1e40af', '#eff6ff'],
        'on_hold'     => ['On Hold',     '#b45309', '#fffbeb'],
        'open'        => ['Open for Bids','#1e3a5f', '#eaf0f6'],
        'draft'       => ['Draft',       '#6b6b6b', '#f4f4f2'],
        'finished'    => ['Finished',    '#166534', '#f0fdf4'],
        'cancelled'   => ['Cancelled',   '#b91c1c', '#fef2f2'],
    ];
}
/** Map any legacy/value to a current status key. */
function project_status_key(string $s): string {
    $s = strtolower(trim($s));
    $alias = ['active' => 'in_progress', 'hold' => 'on_hold', 'completed' => 'finished'];
    $s = $alias[$s] ?? $s;
    return isset(project_status_defs()[$s]) ? $s : 'open';
}
function project_status_style(string $s): array {
    return project_status_defs()[project_status_key($s)] ?? ['Open', '#1e3a5f', '#eaf0f6'];
}

/** Billing models → label. */
function project_billing_defs(): array {
    return [
        'milestone' => 'Per milestone',
        'fixed'     => 'Fixed / one-time',
        'hourly'    => 'Hourly / time-based',
        'retainer'  => 'Monthly retainer',
    ];
}
function project_billing_label(?string $b): string {
    return project_billing_defs()[$b] ?? 'Per milestone';
}

/** Priority → [label, colour]. */
function project_priority_defs(): array {
    return [
        'low'    => ['Low',    '#6b6b6b'],
        'normal' => ['Normal', '#1e3a5f'],
        'high'   => ['High',   '#b45309'],
        'urgent' => ['Urgent', '#b91c1c'],
    ];
}

/* ── CRUD (owner-scoped) ───────────────────────────────────── */

/** All projects owned by a member, newest first. */
function user_projects(int $uid): array {
    return db_all("SELECT * FROM projects WHERE owner_user_id=? ORDER BY updated_at DESC, id DESC", [$uid]);
}

/** One project owned by a member, by numeric id or public_id. */
function project_get($key, int $uid): ?array {
    return is_numeric($key)
        ? db_one("SELECT * FROM projects WHERE id=? AND owner_user_id=? LIMIT 1", [(int)$key, $uid])
        : db_one("SELECT * FROM projects WHERE public_id=? AND owner_user_id=? LIMIT 1", [$key, $uid]);
}

/** Sanitise the writable fields from a posted array. */
function project_clean(array $d): array {
    $statusKeys  = array_keys(project_status_defs());
    $billingKeys = array_keys(project_billing_defs());
    $prioKeys    = array_keys(project_priority_defs());
    $budget   = ($d['budget'] ?? '') !== '' ? (float) $d['budget'] : null;
    $cur      = $d['currency'] ?? 'KES';
    $currency = in_array($cur, ['KES','USD','EUR','GBP','NGN','TZS','UGX'], true) ? $cur : 'KES';
    $status   = in_array($d['status'] ?? '', $statusKeys, true) ? $d['status'] : 'started';
    return [
        'name'            => trim($d['name'] ?? ''),
        'customer_name'   => trim($d['customer_name'] ?? '')  ?: null,
        'customer_email'  => trim($d['customer_email'] ?? '') ?: null,
        'customer_phone'  => trim($d['customer_phone'] ?? '') ?: null,
        'type'            => trim($d['type'] ?? '') ?: null,
        'location'        => trim($d['location'] ?? '') ?: null,
        'description'     => trim($d['description'] ?? '') ?: null,
        'budget'          => $budget,
        'budget_display'  => $budget !== null ? $currency . ' ' . number_format($budget) : null,
        'currency'        => $currency,
        'billing_type'    => in_array($d['billing_type'] ?? '', $billingKeys, true) ? $d['billing_type'] : 'milestone',
        'payment_terms'   => trim($d['payment_terms'] ?? '') ?: null,
        'start_date'      => ($d['start_date'] ?? '')     !== '' ? $d['start_date'] : null,
        'end_date'        => ($d['end_date'] ?? '')       !== '' ? $d['end_date'] : null,
        'est_completion'  => ($d['est_completion'] ?? '') !== '' ? $d['est_completion'] : null,
        'deadline'        => ($d['deadline'] ?? '')       !== '' ? $d['deadline'] : null,
        'priority'        => in_array($d['priority'] ?? '', $prioKeys, true) ? $d['priority'] : 'normal',
        'progress'        => max(0, min(100, (int) ($d['progress'] ?? 0))),
        'status'          => $status,
        'visibility'      => ($d['visibility'] ?? 'private') === 'public' ? 'public' : 'private',
        'trades'          => isset($d['trades']) && is_array($d['trades']) ? implode(',', array_map('trim', $d['trades'])) : null,
    ];
}
/** Collaboration settings from a posted array (checkbox => 0/1). */
function project_settings_clean(array $d): array {
    $b = fn($k) => isset($d[$k]) ? 1 : 0;
    return [
        'client_can_comment'         => $b('client_can_comment'),
        'client_can_view_budget'     => $b('client_can_view_budget'),
        'client_can_view_documents'  => $b('client_can_view_documents'),
        'require_milestone_approval' => $b('require_milestone_approval'),
        'use_escrow'                 => $b('use_escrow'),
        'notify_client_updates'      => $b('notify_client_updates'),
    ];
}

/** Create a project for a member. Returns ['id','public_id']. */
function project_create(int $uid, array $d): array {
    $c = project_clean($d);
    $s = project_settings_clean($d);
    $uuid = project_uuid();
    $owner = db_value("SELECT name FROM users WHERE id=?", [$uid]) ?: 'Owner';
    $segment = $c['type'] ? strtolower($c['type']) : 'commercial';
    $id = db_insert(
      "INSERT INTO projects
         (public_id,owner_user_id,owner_name,owner_label,name,customer_name,customer_email,customer_phone,
          type,segment,location,description,budget,budget_display,currency,billing_type,payment_terms,
          start_date,end_date,est_completion,deadline,priority,phase,progress,urgency,status,visibility,trades,
          client_can_comment,client_can_view_budget,client_can_view_documents,require_milestone_approval,use_escrow,notify_client_updates)
       VALUES (?,?,?, 'Owner', ?,?,?,?, ?,?,?,?, ?,?,?,?,?, ?,?,?,?, ?, 'Planning', ?, 'new', ?, ?, ?, ?,?,?,?,?,?)",
      [$uuid, $uid, $owner, $c['name'], $c['customer_name'], $c['customer_email'], $c['customer_phone'],
       $c['type'], $segment, $c['location'], $c['description'], $c['budget'], $c['budget_display'], $c['currency'], $c['billing_type'], $c['payment_terms'],
       $c['start_date'], $c['end_date'], $c['est_completion'], $c['deadline'], $c['priority'], $c['progress'], $c['status'], $c['visibility'], $c['trades'],
       $s['client_can_comment'], $s['client_can_view_budget'], $s['client_can_view_documents'], $s['require_milestone_approval'], $s['use_escrow'], $s['notify_client_updates']]
    );
    return ['id' => $id, 'public_id' => $uuid];
}

/** Update a project's core fields (owner-scoped). */
function project_update(int $id, int $uid, array $d): void {
    $c = project_clean($d);
    db_stmt(
      "UPDATE projects SET name=?,customer_name=?,customer_email=?,customer_phone=?,type=?,location=?,description=?,
          budget=?,budget_display=?,currency=?,billing_type=?,payment_terms=?,start_date=?,end_date=?,est_completion=?,deadline=?,
          priority=?,progress=?,status=?,visibility=?,trades=?
       WHERE id=? AND owner_user_id=?",
      [$c['name'], $c['customer_name'], $c['customer_email'], $c['customer_phone'], $c['type'], $c['location'], $c['description'],
       $c['budget'], $c['budget_display'], $c['currency'], $c['billing_type'], $c['payment_terms'], $c['start_date'], $c['end_date'], $c['est_completion'], $c['deadline'],
       $c['priority'], $c['progress'], $c['status'], $c['visibility'], $c['trades'], $id, $uid]
    );
}

/** Save just the collaboration settings (owner-scoped). */
function project_settings_save(int $id, int $uid, array $d): void {
    $s = project_settings_clean($d);
    db_stmt(
      "UPDATE projects SET client_can_comment=?,client_can_view_budget=?,client_can_view_documents=?,require_milestone_approval=?,use_escrow=?,notify_client_updates=?
       WHERE id=? AND owner_user_id=?",
      [$s['client_can_comment'], $s['client_can_view_budget'], $s['client_can_view_documents'], $s['require_milestone_approval'], $s['use_escrow'], $s['notify_client_updates'], $id, $uid]
    );
}

/** Just change status (owner-scoped). */
function project_set_status(int $id, int $uid, string $status): void {
    if (!isset(project_status_defs()[$status])) return;
    db_stmt("UPDATE projects SET status=? WHERE id=? AND owner_user_id=?", [$status, $id, $uid]);
}

/* ── Milestones ────────────────────────────────────────────── */

function project_owns(int $projectId, int $uid): bool {
    return (bool) db_value("SELECT 1 FROM projects WHERE id=? AND owner_user_id=? LIMIT 1", [$projectId, $uid]);
}
function project_milestones(int $projectId): array {
    return db_all("SELECT * FROM project_milestones WHERE project_id=? ORDER BY sort_order, due_date IS NULL, due_date, id", [$projectId]);
}
/** Owner-scoped milestone fetch by id (returns row incl. project_id). */
function milestone_owned(int $id, int $uid): ?array {
    return db_one("SELECT pm.* FROM project_milestones pm JOIN projects p ON p.id=pm.project_id WHERE pm.id=? AND p.owner_user_id=? LIMIT 1", [$id, $uid]);
}
function milestone_add(int $projectId, int $uid, array $d): void {
    if (trim($d['title'] ?? '') === '' || !project_owns($projectId, $uid)) return;
    $next = (int) db_value("SELECT COALESCE(MAX(sort_order),-1)+1 FROM project_milestones WHERE project_id=?", [$projectId]);
    $status = in_array($d['status'] ?? '', ['upcoming','active','completed'], true) ? $d['status'] : 'upcoming';
    db_stmt(
      "INSERT INTO project_milestones (project_id,title,description,amount,due_date,status,sort_order,completed_at)
       VALUES (?,?,?,?,?,?,?, " . ($status === 'completed' ? 'NOW()' : 'NULL') . ")",
      [$projectId, trim($d['title']), (trim($d['description'] ?? '') ?: null),
       (($d['amount'] ?? '') !== '' ? (float) $d['amount'] : null), (($d['due_date'] ?? '') ?: null), $status, $next]
    );
    project_progress_from_milestones($projectId);
}
function milestone_edit(int $id, int $uid, array $d): void {
    $m = milestone_owned($id, $uid); if (!$m) return;
    db_stmt("UPDATE project_milestones SET title=?,description=?,amount=?,due_date=? WHERE id=?",
      [trim($d['title'] ?? $m['title']) ?: $m['title'], (trim($d['description'] ?? '') ?: null),
       (($d['amount'] ?? '') !== '' ? (float) $d['amount'] : null), (($d['due_date'] ?? '') ?: null), $id]);
}
function milestone_set_status(int $id, int $uid, string $status): void {
    if (!in_array($status, ['upcoming','active','completed'], true)) return;
    $m = milestone_owned($id, $uid); if (!$m) return;
    db_stmt("UPDATE project_milestones SET status=?, completed_at=" . ($status === 'completed' ? 'NOW()' : 'NULL') . " WHERE id=?", [$status, $id]);
    project_progress_from_milestones((int) $m['project_id']);
}
function milestone_toggle_release(int $id, int $uid): void {
    $m = milestone_owned($id, $uid); if (!$m) return;
    db_stmt("UPDATE project_milestones SET released=? WHERE id=?", [$m['released'] ? 0 : 1, $id]);
}
function milestone_delete(int $id, int $uid): void {
    $m = milestone_owned($id, $uid); if (!$m) return;
    db_stmt("DELETE FROM project_milestones WHERE id=?", [$id]);
    project_progress_from_milestones((int) $m['project_id']);
}
/** Totals for the milestones panel + escrow view. */
function project_milestone_summary(int $projectId): array {
    $out = ['total' => 0, 'done' => 0, 'value' => 0.0, 'done_value' => 0.0, 'released' => 0.0, 'pct' => 0];
    foreach (project_milestones($projectId) as $r) {
        $a = (float) $r['amount'];
        $out['total']++; $out['value'] += $a;
        if ($r['status'] === 'completed') { $out['done']++; $out['done_value'] += $a; }
        if ((int) $r['released']) $out['released'] += $a;
    }
    $out['pct'] = $out['total'] ? (int) round($out['done'] * 100 / $out['total']) : 0;
    return $out;
}
/** Auto-set the project's progress from completed milestones (only when any exist). */
function project_progress_from_milestones(int $projectId): void {
    $s = project_milestone_summary($projectId);
    if ($s['total'] > 0) db_stmt("UPDATE projects SET progress=? WHERE id=?", [$s['pct'], $projectId]);
}

/* ── Tasks (hierarchical sections → subtasks) + team ───────── */

function project_task_roles(): array {
    return ['Contractor','Project Manager','Engineer','Architect','Quantity Surveyor','Foreman','Mason','Carpenter','Electrician','Plumber','Steel Fixer','Painter','Welder','Labourer','Supplier','Other'];
}
function project_task_status_defs(): array {
    return ['todo'=>['To Do','#6b6b6b','#f4f4f2'],'in_progress'=>['In Progress','#1e40af','#eff6ff'],'review'=>['Review','#b45309','#fffbeb'],'done'=>['Done','#166534','#f0fdf4']];
}

/** Default construction work-breakdown applied to new projects. */
function project_default_task_template(): array {
    return [
        ['Site Survey & Planning',           ['Land surveys','Ground plans','3D visuals']],
        ['Foundation Works',                 ['Excavation','Concrete pouring','Drainage']],
        ['Structural Construction',          ['Columns','Beams','Walls','Slabs','Steel framing']],
        ['Roofing & Waterproofing',          ['Tiles','Sheets','Gutters','Insulation','Membranes']],
        ['Plumbing & Water Systems',         ['Piping','Tanks','Septic systems','Filtration']],
        ['Electrical & Solar',               ['Wiring','Lighting','Solar panels']],
        ['Security System',                  ['CCTV','Electric fences']],
        ['Interior Design & Finishes',       ['Painting','Gypsum','Lighting design','Décor']],
        ['Furnishings & Fittings',           ['Wardrobes','Beds','Sofas','Cabinets','Desks']],
        ['Flooring & Tiling',                ['Tiles','Marble','Terrazzo','Parquet','Paving']],
        ['Landscaping & Outdoor Works',      ['Lawns','Gardens','Driveways','Gazebos','Gates']],
        ['Kitchen & Bathroom Installations', ['Showers','Vanities','Countertops']],
    ];
}
/** Seed the default breakdown — sections (as milestones) + subtasks. No-op if tasks already exist. */
function project_seed_default_tasks(int $projectId): void {
    if (db_value("SELECT 1 FROM project_tasks WHERE project_id=? LIMIT 1", [$projectId])) return;
    $so = 0;
    foreach (project_default_task_template() as [$section, $subs]) {
        $sid = db_insert("INSERT INTO project_tasks (project_id,parent_id,title,is_milestone,sort_order) VALUES (?,NULL,?,1,?)", [$projectId, $section, $so++]);
        $cso = 0;
        foreach ($subs as $sub) {
            db_insert("INSERT INTO project_tasks (project_id,parent_id,title,sort_order) VALUES (?,?,?,?)", [$projectId, $sid, $sub, $cso++]);
        }
    }
}

/** Full task tree: sections → subtasks, each task carrying its members. */
function project_tasks_tree(int $projectId): array {
    $rows = db_all("SELECT * FROM project_tasks WHERE project_id=? ORDER BY (parent_id IS NOT NULL), parent_id, sort_order, id", [$projectId]);
    $mem = [];
    foreach (db_all("SELECT tm.* FROM project_task_members tm JOIN project_tasks t ON t.id=tm.task_id WHERE t.project_id=? ORDER BY tm.sort_order, tm.id", [$projectId]) as $m) {
        $mem[(int)$m['task_id']][] = $m;
    }
    $sections = []; $children = [];
    foreach ($rows as $r) {
        $r['members'] = $mem[(int)$r['id']] ?? [];
        if ($r['parent_id'] === null) { $r['subtasks'] = []; $sections[(int)$r['id']] = $r; }
        else $children[(int)$r['parent_id']][] = $r;
    }
    foreach ($children as $pid => $kids) { if (isset($sections[$pid])) $sections[$pid]['subtasks'] = $kids; }
    return array_values($sections);
}
function project_task_summary(int $projectId): array {
    $out = ['sections'=>0,'subtasks'=>0,'done'=>0,'total'=>0,'value'=>0.0,'released'=>0.0];
    foreach (db_all("SELECT parent_id,status,amount,released FROM project_tasks WHERE project_id=?", [$projectId]) as $r) {
        if ($r['parent_id'] === null) $out['sections']++; else $out['subtasks']++;
        $out['total']++;
        if ($r['status'] === 'done') $out['done']++;
        $out['value'] += (float)$r['amount'];
        if ((int)$r['released']) $out['released'] += (float)$r['amount'];
    }
    return $out;
}

function task_owned(int $taskId, int $uid): ?array {
    return db_one("SELECT t.* FROM project_tasks t JOIN projects p ON p.id=t.project_id WHERE t.id=? AND p.owner_user_id=? LIMIT 1", [$taskId, $uid]);
}
function task_add(int $projectId, int $uid, array $d): void {
    if (trim($d['title'] ?? '') === '' || !project_owns($projectId, $uid)) return;
    $parent = ($d['parent_id'] ?? '') !== '' ? (int)$d['parent_id'] : null;
    if ($parent !== null) { $p = task_owned($parent, $uid); if (!$p || (int)$p['project_id'] !== $projectId) return; }
    $next = (int) db_value("SELECT COALESCE(MAX(sort_order),-1)+1 FROM project_tasks WHERE project_id=? AND parent_id" . ($parent === null ? ' IS NULL' : '=?'), $parent === null ? [$projectId] : [$projectId, $parent]);
    $status = isset(project_task_status_defs()[$d['status'] ?? '']) ? $d['status'] : 'todo';
    db_stmt("INSERT INTO project_tasks (project_id,parent_id,title,description,status,amount,due_date,is_milestone,sort_order) VALUES (?,?,?,?,?,?,?,?,?)",
      [$projectId, $parent, trim($d['title']), (trim($d['description'] ?? '') ?: null), $status, (($d['amount'] ?? '') !== '' ? (float)$d['amount'] : null), (($d['due_date'] ?? '') ?: null), (!empty($d['is_milestone']) ? 1 : 0), $next]);
}
function task_edit(int $id, int $uid, array $d): void {
    $t = task_owned($id, $uid); if (!$t) return;
    db_stmt("UPDATE project_tasks SET title=?,description=?,amount=?,due_date=?,is_milestone=? WHERE id=?",
      [trim($d['title'] ?? $t['title']) ?: $t['title'], (trim($d['description'] ?? '') ?: null), (($d['amount'] ?? '') !== '' ? (float)$d['amount'] : null), (($d['due_date'] ?? '') ?: null), (!empty($d['is_milestone']) ? 1 : 0), $id]);
}
function task_set_status(int $id, int $uid, string $status): void {
    if (!isset(project_task_status_defs()[$status])) return;
    $t = task_owned($id, $uid); if (!$t) return;
    db_stmt("UPDATE project_tasks SET status=?, completed_at=" . ($status === 'done' ? 'NOW()' : 'NULL') . " WHERE id=?", [$status, $id]);
}
function task_toggle_release(int $id, int $uid): void {
    $t = task_owned($id, $uid); if (!$t) return;
    db_stmt("UPDATE project_tasks SET released=? WHERE id=?", [$t['released'] ? 0 : 1, $id]);
}
function task_delete(int $id, int $uid): void {
    $t = task_owned($id, $uid); if (!$t) return;
    db_stmt("DELETE FROM project_tasks WHERE id=?", [$id]);
}
function task_member_add(int $taskId, int $uid, string $name, ?string $role): void {
    $name = trim($name); if ($name === '' || !task_owned($taskId, $uid)) return;
    $next = (int) db_value("SELECT COALESCE(MAX(sort_order),-1)+1 FROM project_task_members WHERE task_id=?", [$taskId]);
    db_stmt("INSERT INTO project_task_members (task_id,name,role,sort_order) VALUES (?,?,?,?)", [$taskId, $name, (trim((string)$role) ?: null), $next]);
}
function task_member_remove(int $memberId, int $uid): void {
    $ok = db_value("SELECT 1 FROM project_task_members tm JOIN project_tasks t ON t.id=tm.task_id JOIN projects p ON p.id=t.project_id WHERE tm.id=? AND p.owner_user_id=? LIMIT 1", [$memberId, $uid]);
    if ($ok) db_stmt("DELETE FROM project_task_members WHERE id=?", [$memberId]);
}
function member_set_role(int $memberId, int $uid, string $role): void {
    $ok = db_value("SELECT 1 FROM project_task_members tm JOIN project_tasks t ON t.id=tm.task_id JOIN projects p ON p.id=t.project_id WHERE tm.id=? AND p.owner_user_id=? LIMIT 1", [$memberId, $uid]);
    if ($ok) db_stmt("UPDATE project_task_members SET role=? WHERE id=?", [trim($role) ?: null, $memberId]);
}
/** All team members across a project's tasks, with their task context (for the Team tab). */
function project_team_roster(int $projectId): array {
    return db_all(
      "SELECT tm.*, t.title AS task_title, t.parent_id
       FROM project_task_members tm JOIN project_tasks t ON t.id=tm.task_id
       WHERE t.project_id=? ORDER BY tm.role IS NULL, tm.role, tm.name", [$projectId]);
}
/** Flat list of all tasks (sections + subtasks) for selectors. */
function project_task_options(int $projectId): array {
    return db_all("SELECT id, title, parent_id FROM project_tasks WHERE project_id=? ORDER BY (parent_id IS NOT NULL), parent_id, sort_order, id", [$projectId]);
}
