<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';
require_login();
$uid  = (int) current_user()['id'];
$proj = isset($_GET['id']) ? project_get($_GET['id'], $uid) : null;
if (!$proj) { $mine = user_projects($uid); $proj = $mine[0] ?? null; }
if (!$proj) { header('Location: /pages/projects/index.php'); exit; }

// POST actions (settings + milestones) → redirect → GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== '') {
    $pid = (int) $proj['id'];
    $back = '/pages/projects/view.php?id=' . urlencode($proj['public_id']);
    $mid = (int) ($_POST['mid'] ?? 0);
    $tid = (int) ($_POST['tid'] ?? 0);
    $ret = in_array($_POST['ret'] ?? '', ['tasks','team','milestones','settings'], true) ? $_POST['ret'] : 'tasks';
    switch ($_POST['action']) {
        case 'save_settings':     project_settings_save($pid, $uid, $_POST);                  header("Location: $back&saved=1#settings"); exit;
        case 'milestone_add':     milestone_add($pid, $uid, $_POST);                          header("Location: $back#milestones"); exit;
        case 'milestone_edit':    milestone_edit($mid, $uid, $_POST);                         header("Location: $back#milestones"); exit;
        case 'milestone_status':  milestone_set_status($mid, $uid, $_POST['status'] ?? '');   header("Location: $back#milestones"); exit;
        case 'milestone_release': milestone_toggle_release($mid, $uid);                       header("Location: $back#milestones"); exit;
        case 'milestone_del':     milestone_delete($mid, $uid);                               header("Location: $back#milestones"); exit;
        case 'seed_tasks':        project_seed_default_tasks($pid);                           header("Location: $back#tasks"); exit;
        case 'task_add':          task_add($pid, $uid, $_POST);                               header("Location: $back#tasks"); exit;
        case 'task_edit':         task_edit($tid, $uid, $_POST);                              header("Location: $back#tasks"); exit;
        case 'task_status':       task_set_status($tid, $uid, $_POST['status'] ?? '');        header("Location: $back#tasks"); exit;
        case 'task_release':      task_toggle_release($tid, $uid);                            header("Location: $back#tasks"); exit;
        case 'task_del':          task_delete($tid, $uid);                                    header("Location: $back#tasks"); exit;
        case 'member_add':        task_member_add((int)($_POST['task_id'] ?? 0), $uid, $_POST['name'] ?? '', $_POST['role'] ?? ''); header("Location: $back#$ret"); exit;
        case 'member_del':        task_member_remove((int)($_POST['memid'] ?? 0), $uid);      header("Location: $back#$ret"); exit;
        case 'member_role':       member_set_role((int)($_POST['memid'] ?? 0), $uid, $_POST['role'] ?? ''); header("Location: $back#$ret"); exit;
    }
}

$page_title = $proj['name']; $sp = 'projects';
[$sLabel, $sCol, $sBg] = project_status_style($proj['status']);
$pct  = (int) $proj['progress'];
$bill = project_billing_label($proj['billing_type']);
[$prioLabel, $prioCol] = project_priority_defs()[$proj['priority'] ?? 'normal'] ?? ['Normal', '#1e3a5f'];
$fmt  = fn($d) => $d ? date('d M Y', strtotime($d)) : '—';
// JSON for an onclick edit button (safe inside a single-quoted attribute)
$taskJson = fn($t) => htmlspecialchars(json_encode([
    'id' => (int) $t['id'], 'title' => $t['title'], 'description' => $t['description'],
    'amount' => $t['amount'], 'due_date' => $t['due_date'], 'is_milestone' => (int) $t['is_milestone'],
]), ENT_QUOTES);
$savedFlash = (bool) ($_GET['saved'] ?? '');

$av = fn($p) => 'https://randomuser.me/api/portraits/'.$p.'.jpg';

// Tasks by column
$columns = [
  ['todo','To Do','#6b6b6b', [
    ['MEP coordination meeting','MEP','men/54','High','08 Jun','0/3'],
    ['Order structural steel — Phase 2','Procurement','women/25','Medium','12 Jun','1/4'],
  ]],
  ['doing','In Progress','#1e40af', [
    ['Column reinforcement drawings','Structural','men/32','High','05 Jun','2/5'],
    ['Block work — Level 2','Finishing','men/76','Medium','10 Jun','3/6'],
  ]],
  ['review','Review','#b45309', [
    ['Structural audit report','Structural','women/44','High','07 Jun','4/4'],
  ]],
  ['done','Done','#166534', [
    ['Site clearing — Level 3','Site','men/76','—','03 Jun','5/5'],
    ['Foundation pour sign-off','Structural','men/32','—','28 May','6/6'],
  ]],
];
$prCol = ['High'=>'red','Medium'=>'amber','Low'=>'grey','—'=>'grey'];

$milestones = project_milestones((int) $proj['id']);   // real milestones
$msum       = project_milestone_summary((int) $proj['id']);
$curSym     = $proj['currency'] ?: 'KES';
$tasksTree  = project_tasks_tree((int) $proj['id']);   // sections → subtasks → members
$tsum       = project_task_summary((int) $proj['id']);
$taskStDefs = project_task_status_defs();
$teamRoster = project_team_roster((int) $proj['id']);  // everyone assigned across tasks
$taskOpts   = project_task_options((int) $proj['id']);
$projRoles  = project_task_roles();
$ini = function($n){ $n=trim((string)$n); $p=preg_split('/\s+/',$n); return strtoupper(count($p)>=2 ? substr($p[0],0,1).substr($p[1],0,1) : substr($n,0,2)); };

$goals = [
  ['bi-shield-check','#166534','#f0fdf4','Zero safety incidents','142 days incident-free on site',96,'142 / 150 day target'],
  ['bi-clock-history','#1e3a5f','#eaf0f6','Deliver 2 weeks early','Ahead of programme by 9 days',75,'9 / 14 days ahead'],
  ['bi-cash-coin','#9a7d27','#fdf6e3','Stay within budget','Currently 2% under budget',88,'KES 30.6M of 45M'],
  ['bi-patch-check','#1e40af','#eff6ff','Quality score ≥ 95%','Client QA inspections passing',92,'92% average score'],
];

$teamAreas = [
  ['Project management','bi-clipboard-data',[['Amina Osei','Project Lead','women/44'],['Grace Wanjiku','QS / Cost Control','women/25']]],
  ['Structural','bi-building',[['John Mwangi','Lead Structural Eng.','men/32'],['Miriam Ndungu','Geotechnical Eng.','women/33']]],
  ['MEP','bi-lightning-charge',[['Samuel Otieno','MEP Engineer','men/54'],['Kevin Ochieng','Electrical Eng.','men/67']]],
  ['Site & finishing','bi-cone-striped',[['Peter Njoroge','Site Foreman','men/76'],['Zara Abdi','Interior Designer','women/62']]],
];

$docs = [
  ['Architectural drawings — Rev C','PDF','3.1 MB','Amina Osei','02 Jun','bi-file-earmark-pdf','#c0392b'],
  ['Structural drawings — Rev B','PDF','2.4 MB','John Mwangi','30 May','bi-file-earmark-pdf','#c0392b'],
  ['Bill of Quantities (BOQ)','XLSX','420 KB','Grace Wanjiku','28 May','bi-file-earmark-spreadsheet','#166534'],
  ['Main works contract — signed','PDF','680 KB','James Mwenda','01 Feb','bi-file-earmark-text','#1e3a5f'],
  ['Soil investigation report','PDF','5.2 MB','Miriam Ndungu','15 Jan','bi-file-earmark-pdf','#c0392b'],
  ['Site progress photos — May','ZIP','24 MB','Peter Njoroge','31 May','bi-file-earmark-zip','#9a7d27'],
];

$budget = [
  ['Preliminaries','KES 4.5M','KES 4.1M',91],
  ['Substructure','KES 9.0M','KES 9.0M',100],
  ['Superstructure','KES 14.0M','KES 9.8M',70],
  ['MEP services','KES 8.0M','KES 4.2M',53],
  ['Finishes','KES 7.5M','KES 2.1M',28],
  ['Contingency','KES 2.0M','KES 1.4M',70],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php
    $topbar_title='Westlands Office Block'; $topbar_crumb='Projects';
    $topbar_action='<button class="bf-btn-ghost" data-engage="hire" data-ctx="this project" style="cursor:pointer;"><i class="bi bi-send me-1"></i>Invite</button>'
      .'<button class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Add Task</button>';
    include __DIR__ . '/../../includes/topbar.php';
    ?>

    <div class="bf-body" style="padding:24px 28px;">

      <?php if ($savedFlash): ?>
      <div class="d-flex align-items-center gap-2 mb-3" data-ms="4000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:11px 15px;font-size:13px;"><i class="bi bi-check-circle-fill"></i><div>Project settings saved.</div></div>
      <?php endif; ?>

      <!-- project header (real) -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:22px 24px;margin-bottom:16px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
          <div class="d-flex gap-2 flex-wrap">
            <span class="bf-badge" style="color:<?=$sCol?>;background:<?=$sBg?>;"><?=$sLabel?></span>
            <?php if ($proj['type']): ?><span class="bf-badge grey"><?=htmlspecialchars($proj['type'])?></span><?php endif; ?>
            <span class="bf-badge" style="color:<?=$prioCol?>;background:var(--surface);"><?=$prioLabel?> priority</span>
            <?php if ($proj['visibility']==='public'): ?><span class="bf-badge navy">Open for bids</span><?php endif; ?>
          </div>
          <div class="d-flex gap-2">
            <a href="/pages/projects/new.php?id=<?=(int)$proj['id']?>" class="bf-btn-outline" style="text-decoration:none;"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="/pages/projects/index.php" class="bf-btn-ghost" style="text-decoration:none;"><i class="bi bi-arrow-left me-1"></i>Projects</a>
          </div>
        </div>
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <h1 style="font-size:22px;font-weight:800;color:var(--ink);letter-spacing:-.02em;margin:0 0 6px;"><?=htmlspecialchars($proj['name'])?></h1>
            <div style="font-size:12.5px;color:var(--ink-3);">
              <?php if ($proj['customer_name']): ?><i class="bi bi-person me-1" style="color:#1e3a5f;"></i><?=htmlspecialchars($proj['customer_name'])?> &nbsp;·&nbsp; <?php endif; ?>
              <?php if ($proj['location']): ?><i class="bi bi-geo-alt me-1" style="color:#c0392b;"></i><?=htmlspecialchars($proj['location'])?> &nbsp;·&nbsp; <?php endif; ?>
              <i class="bi bi-cash-coin me-1"></i><?=htmlspecialchars($bill)?>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="row g-2 text-center mb-2">
              <?php foreach ([['Budget',$proj['budget_display']?:'—','var(--ink)'],['Deadline',$fmt($proj['deadline']),'#c0392b'],['Progress',$pct.'%','#1e3a5f']] as [$l,$vv,$c]): ?>
              <div class="col-4">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);"><?=$l?></div>
                <div style="font-size:16px;font-weight:900;color:<?=$c?>;letter-spacing:-.02em;margin-top:2px;"><?=htmlspecialchars($vv)?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div style="height:8px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?=$pct?>%;background:<?= $pct>=80?'#16a34a':($pct<25?'#f59e0b':'#1e3a5f') ?>;border-radius:99px;"></div></div>
          </div>
        </div>
      </div>

      <!-- TABS -->
      <div class="bf-tabs2" data-tabnav>
        <button class="bf-tab2 active" data-tab="overview"><i class="bi bi-grid-1x2"></i> Overview</button>
        <button class="bf-tab2" data-tab="tasks"><i class="bi bi-check2-square"></i> Tasks <span class="cnt">34</span></button>
        <button class="bf-tab2" data-tab="milestones"><i class="bi bi-flag"></i> Milestones <span class="cnt">8</span></button>
        <button class="bf-tab2" data-tab="goals"><i class="bi bi-bullseye"></i> Goals <span class="cnt">4</span></button>
        <button class="bf-tab2" data-tab="team"><i class="bi bi-people"></i> Team <span class="cnt">8</span></button>
        <button class="bf-tab2" data-tab="documents"><i class="bi bi-folder2"></i> Documents <span class="cnt">12</span></button>
        <button class="bf-tab2" data-tab="budget"><i class="bi bi-cash-stack"></i> Budget</button>
        <button class="bf-tab2" data-tab="settings"><i class="bi bi-sliders"></i> Settings</button>
      </div>

      <div data-tabroot>

        <!-- ═══ OVERVIEW ═══ -->
        <div class="bf-panel active" data-panel="overview">
          <div class="bf-kpis mb-3">
            <?php foreach ([
              ['Tasks done','18 / 34','bi-check2-square','#1e3a5f','#eaf0f6'],
              ['Milestones','3 / 8','bi-flag','#166534','#f0fdf4'],
              ['Open issues','2','bi-exclamation-triangle','#b45309','#fffbeb'],
              ['Days left','24','bi-calendar-event','#c0392b','#fef2f2'],
            ] as [$l,$v,$ic,$c,$bg]): ?>
            <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
            <?php endforeach; ?>
          </div>
          <div class="row g-3">
            <div class="col-lg-8">
              <div class="bf-pf-card"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-info-circle"></i> About this project</div><a href="/pages/projects/new.php?id=<?=(int)$proj['id']?>" class="bf-pf-card-edit"><i class="bi bi-pencil"></i> Edit</a></div>
                <div class="bf-pf-card-b">
                  <p style="font-size:13px;color:var(--ink-2);line-height:1.7;margin:0 0 14px;"><?= $proj['description'] ? nl2br(htmlspecialchars($proj['description'])) : '<span style="color:var(--ink-4);">No description yet — <a href="/pages/projects/new.php?id='.(int)$proj['id'].'" style="color:#1e3a5f;">add one</a>.</span>' ?></p>
                  <div class="row g-2">
                    <?php foreach ([
                      ['Client', $proj['customer_name'] ?: '—'],
                      ['Billing', $bill],
                      ['Payment terms', $proj['payment_terms'] ?: '—'],
                      ['Start date', $fmt($proj['start_date'])],
                      ['Est. completion', $fmt($proj['est_completion'])],
                      ['Deadline', $fmt($proj['deadline'])],
                    ] as [$k,$vv]): ?>
                    <div class="col-sm-6"><div style="border:1px solid var(--line-2);border-radius:10px;padding:9px 12px;">
                      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-4);"><?=$k?></div>
                      <div style="font-size:12.5px;font-weight:700;color:var(--ink);margin-top:2px;"><?=htmlspecialchars($vv)?></div>
                    </div></div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-activity"></i> Recent activity</div></div>
                <div class="bf-pf-card-b" style="padding-top:8px;">
                  <?php foreach ([
                    ['bi-check-circle','#166534','#f0fdf4','<strong>Foundation pour sign-off</strong> marked complete by John Mwangi','2h ago'],
                    ['bi-upload','#1e3a5f','#eaf0f6','<strong>Architectural drawings Rev C</strong> uploaded','5h ago'],
                    ['bi-cash-coin','#9a7d27','#fdf6e3','Milestone payment <strong>KES 6.0M</strong> released from escrow','Yesterday'],
                    ['bi-chat-dots','#1e40af','#eff6ff','New comment on <strong>Column reinforcement drawings</strong>','Yesterday'],
                  ] as [$ic,$col,$bg,$txt,$when]): ?>
                  <div class="bf-li"><span class="bf-li-ic" style="background:<?=$bg?>;color:<?=$col?>;"><i class="bi <?=$ic?>"></i></span><div style="flex:1;min-width:0;"><div style="font-size:12px;color:var(--ink-2);line-height:1.45;"><?=$txt?></div><div style="font-size:10.5px;color:var(--ink-4);"><?=$when?></div></div></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="bf-pf-card"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-flag"></i> Next milestone</div></div>
                <div class="bf-pf-card-b">
                  <div style="font-size:13px;font-weight:800;color:var(--ink);">Superstructure — Levels 1–4</div>
                  <div style="font-size:11.5px;color:var(--ink-3);margin:4px 0 10px;">Target 30 Jun · 68% complete</div>
                  <div style="height:6px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:68%;background:#1e3a5f;"></div></div>
                  <div style="font-size:11px;color:#9a7d27;font-weight:700;margin-top:10px;"><i class="bi bi-shield-lock"></i> KES 8.0M held in escrow</div>
                </div>
              </div>
              <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-cash-stack"></i> Budget snapshot</div></div>
                <div class="bf-pf-card-b">
                  <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span style="color:var(--ink-3);">Spent</span><span style="font-weight:800;color:#c0392b;">KES 30.6M</span></div>
                  <div style="height:8px;border-radius:99px;background:var(--line-2);overflow:hidden;margin-bottom:6px;"><div style="height:100%;width:68%;background:#c0392b;"></div></div>
                  <div style="font-size:11px;color:var(--ink-4);">68% of KES 45M · KES 14.4M remaining</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ═══ TASKS (real · hierarchical · minimal) ═══ -->
        <div class="bf-panel" data-panel="tasks">
          <?php $tmoney = fn($a) => ($a !== null && $a !== '') ? $curSym.' '.number_format((float)$a) : ''; ?>
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div style="font-size:12.5px;color:var(--ink-3);"><b style="color:var(--ink);"><?=$tsum['done']?>/<?=$tsum['total']?></b> done · <?=$tsum['sections']?> sections<?php if($tsum['value']>0):?> · <b style="color:#1e3a5f;"><?=$tmoney($tsum['value'])?></b><?php endif;?></div>
            <button type="button" class="bf-btn-outline" onclick="bfTask('')"><i class="bi bi-plus-lg me-1"></i>Add section</button>
          </div>

          <?php if (!$tasksTree): ?>
            <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-b">
              <div class="bf-pf-empty"><i class="bi bi-list-check"></i><div class="t">No tasks yet</div>
                <p>Start with the default construction breakdown — sections, subtasks &amp; milestones you can edit.</p>
                <form method="post"><input type="hidden" name="action" value="seed_tasks"><button class="bf-btn-accent"><i class="bi bi-magic me-1"></i>Use the default breakdown</button></form>
              </div>
            </div></div>
          <?php else: foreach ($tasksTree as $sec):
            [$ssl,$ssc,$ssbg] = $taskStDefs[$sec['status']] ?? ['To Do','#6b6b6b','#f4f4f2'];
          ?>
          <div class="bf-tsec">
            <div class="bf-tsec-h">
              <div class="bf-tsec-main">
                <span class="bf-tsec-title"><?=htmlspecialchars($sec['title'])?></span>
                <?php if((int)$sec['is_milestone']):?><span class="bf-tag gold"><i class="bi bi-flag-fill"></i><?= $sec['amount']!==null ? ' '.$tmoney($sec['amount']) : ' Milestone' ?><?= (int)$sec['released']?' · paid':'' ?></span><?php endif;?>
                <span class="bf-tag" style="color:<?=$ssc?>;background:<?=$ssbg?>;"><?=$ssl?></span>
              </div>
              <div class="bf-ava-row">
                <?php foreach ($sec['members'] as $mb): ?><span class="bf-ava" title="<?=htmlspecialchars($mb['name'].($mb['role']?' · '.$mb['role']:''),ENT_QUOTES)?>"><?=$ini($mb['name'])?></span><?php endforeach; ?>
                <button type="button" class="bf-ava-add" onclick="bfMem(<?=$sec['id']?>)" title="Assign team"><i class="bi bi-plus"></i></button>
              </div>
              <div class="dropdown">
                <button class="bf-icon-btn" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 bf-tmenu">
                  <li><button class="dropdown-item" type="button" onclick="bfTask(<?=$sec['id']?>)"><i class="bi bi-plus-lg me-2 text-muted"></i>Add subtask</button></li>
                  <li><button class="dropdown-item" type="button" onclick='bfTaskEdit(<?=$taskJson($sec)?>)'><i class="bi bi-pencil me-2 text-muted"></i>Edit</button></li>
                  <?php foreach ($taskStDefs as $sv=>[$sl]): if($sv===$sec['status'])continue; ?>
                  <li><form method="post"><input type="hidden" name="action" value="task_status"><input type="hidden" name="tid" value="<?=$sec['id']?>"><input type="hidden" name="status" value="<?=$sv?>"><button class="dropdown-item" type="submit">Mark <?=$sl?></button></form></li>
                  <?php endforeach; ?>
                  <?php if((int)$sec['is_milestone'] && $sec['amount']!==null):?><li><form method="post"><input type="hidden" name="action" value="task_release"><input type="hidden" name="tid" value="<?=$sec['id']?>"><button class="dropdown-item" type="submit"><i class="bi bi-unlock me-2 text-muted"></i><?=(int)$sec['released']?'Mark unpaid':'Release payment'?></button></form></li><?php endif;?>
                  <li><hr class="dropdown-divider"></li>
                  <li><form method="post" onsubmit="return confirm('Delete this section and its subtasks?')"><input type="hidden" name="action" value="task_del"><input type="hidden" name="tid" value="<?=$sec['id']?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                </ul>
              </div>
            </div>
            <?php foreach ($sec['subtasks'] as $sub):
              [$bsl,$bsc,$bsbg] = $taskStDefs[$sub['status']] ?? ['To Do','#6b6b6b','#f4f4f2'];
              $done = $sub['status']==='done';
            ?>
            <div class="bf-trow">
              <form method="post" style="margin:0;"><input type="hidden" name="action" value="task_status"><input type="hidden" name="tid" value="<?=$sub['id']?>"><input type="hidden" name="status" value="<?=$done?'todo':'done'?>"><button class="bf-check <?=$done?'on':''?>" title="<?=$done?'Mark not done':'Mark done'?>"><?php if($done):?><i class="bi bi-check"></i><?php endif;?></button></form>
              <span class="bf-trow-title <?=$done?'done':''?>"><?=htmlspecialchars($sub['title'])?></span>
              <?php if($sub['amount']!==null):?><span class="bf-trow-meta" style="color:#1e3a5f;font-weight:700;"><?=$tmoney($sub['amount'])?></span><?php endif;?>
              <?php if($sub['due_date']):?><span class="bf-trow-meta d-none d-sm-inline"><i class="bi bi-calendar3"></i> <?=$fmt($sub['due_date'])?></span><?php endif;?>
              <span class="bf-tag" style="color:<?=$bsc?>;background:<?=$bsbg?>;"><?=$bsl?></span>
              <div class="bf-ava-row">
                <?php foreach ($sub['members'] as $mb): ?><span class="bf-ava sm" title="<?=htmlspecialchars($mb['name'].($mb['role']?' · '.$mb['role']:''),ENT_QUOTES)?>"><?=$ini($mb['name'])?></span><?php endforeach; ?>
                <button type="button" class="bf-ava-add sm" onclick="bfMem(<?=$sub['id']?>)" title="Assign team"><i class="bi bi-plus"></i></button>
              </div>
              <div class="dropdown">
                <button class="bf-icon-btn" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 bf-tmenu">
                  <li><button class="dropdown-item" type="button" onclick='bfTaskEdit(<?=$taskJson($sub)?>)'><i class="bi bi-pencil me-2 text-muted"></i>Edit</button></li>
                  <?php foreach ($taskStDefs as $sv=>[$sl]): if($sv===$sub['status'])continue; ?>
                  <li><form method="post"><input type="hidden" name="action" value="task_status"><input type="hidden" name="tid" value="<?=$sub['id']?>"><input type="hidden" name="status" value="<?=$sv?>"><button class="dropdown-item" type="submit">Mark <?=$sl?></button></form></li>
                  <?php endforeach; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><form method="post" onsubmit="return confirm('Delete this subtask?')"><input type="hidden" name="action" value="task_del"><input type="hidden" name="tid" value="<?=$sub['id']?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                </ul>
              </div>
            </div>
            <?php endforeach; ?>
            <button type="button" class="bf-trow-add" onclick="bfTask(<?=$sec['id']?>)"><i class="bi bi-plus"></i> Add subtask</button>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- ═══ MILESTONES (real) ═══ -->
        <div class="bf-panel" data-panel="milestones">
          <?php $money = fn($a) => ($a !== null && $a !== '') ? $curSym.' '.number_format((float)$a) : '—'; ?>
          <div class="bf-kpis mb-3" style="grid-template-columns:repeat(4,1fr);">
            <?php foreach ([
              ['Milestones', $msum['done'].' / '.$msum['total'], 'bi-flag','#1e3a5f','#eaf0f6'],
              ['Total value', $money($msum['value']), 'bi-cash-stack','#166534','#f0fdf4'],
              ['Released', $money($msum['released']), 'bi-unlock','#9a7d27','#fdf6e3'],
              ['Progress', $msum['pct'].'%', 'bi-graph-up','#1e40af','#eff6ff'],
            ] as [$l,$vv,$ic,$c,$bg]): ?>
            <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$vv?></div><div class="bf-kpi-l"><?=$l?></div></div>
            <?php endforeach; ?>
          </div>

          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-flag"></i> Milestones</div>
              <button type="button" class="bf-pf-card-edit" onclick="bfMs()"><i class="bi bi-plus-lg"></i> Add milestone</button>
            </div>
            <div class="bf-pf-card-b">
              <?php if (!$milestones): ?>
                <div class="bf-pf-empty"><i class="bi bi-flag"></i><div class="t">No milestones yet</div>
                  <p>Break the project into payable stages. With <b>per-milestone billing</b> each completed milestone can release its payment — and completing milestones moves your project's progress automatically.</p>
                  <button type="button" class="bf-btn-accent" onclick="bfMs()"><i class="bi bi-plus-lg me-1"></i>Add your first milestone</button>
                </div>
              <?php else: ?>
              <div class="bf-timeline">
                <?php foreach ($milestones as $m):
                  $st = $m['status']; $rel = (int) $m['released'];
                  $dotCls = $st==='completed'?'done':($st==='active'?'active':'');
                  $icon   = $st==='completed'?'<i class="bi bi-check-lg"></i>':($st==='active'?'<i class="bi bi-arrow-clockwise"></i>':'');
                  $sb     = $st==='completed'?['green','Completed']:($st==='active'?['navy','In progress']:['grey','Upcoming']);
                  $pay    = $rel ? ['#166534','<i class="bi bi-check-circle-fill"></i> Payment released']
                                 : ($st==='completed' ? ['#9a7d27','<i class="bi bi-unlock"></i> Ready to release']
                                                      : ['#6b6b6b','<i class="bi bi-clock-history"></i> '.($proj['use_escrow']?'Held in escrow on completion':'Scheduled')]);
                ?>
                <div class="bf-tl-item">
                  <span class="bf-tl-dot <?=$dotCls?>"><?=$icon?></span>
                  <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;align-items:flex-start;">
                    <div>
                      <div style="font-size:13.5px;font-weight:800;color:var(--ink);"><?=htmlspecialchars($m['title'])?><?php if($m['amount']!==null):?> <span style="color:#1e3a5f;">· <?=$money($m['amount'])?></span><?php endif;?></div>
                      <div style="font-size:11.5px;color:var(--ink-4);margin-top:2px;"><?php if($m['due_date']):?><i class="bi bi-calendar3"></i> Due <?=$fmt($m['due_date'])?><?php else:?>No due date<?php endif;?></div>
                    </div>
                    <span class="bf-badge <?=$sb[0]?>"><?=$sb[1]?></span>
                  </div>
                  <?php if($m['description']):?><div style="font-size:12.5px;color:var(--ink-2);line-height:1.5;margin-top:5px;"><?=htmlspecialchars($m['description'])?></div><?php endif;?>
                  <div style="font-size:11px;font-weight:700;color:<?=$pay[0]?>;margin-top:6px;"><?=$pay[1]?></div>
                  <div class="d-flex flex-wrap gap-2 mt-2 bf-ms-actions">
                    <?php if($st!=='completed'): ?>
                    <form method="post"><input type="hidden" name="action" value="milestone_status"><input type="hidden" name="mid" value="<?=$m['id']?>"><input type="hidden" name="status" value="completed"><button class="bf-ms-btn solid"><i class="bi bi-check2"></i> Mark complete</button></form>
                    <?php else: ?>
                    <form method="post"><input type="hidden" name="action" value="milestone_status"><input type="hidden" name="mid" value="<?=$m['id']?>"><input type="hidden" name="status" value="active"><button class="bf-ms-btn"><i class="bi bi-arrow-counterclockwise"></i> Reopen</button></form>
                    <form method="post"><input type="hidden" name="action" value="milestone_release"><input type="hidden" name="mid" value="<?=$m['id']?>"><button class="bf-ms-btn <?=$rel?'':'solid'?>"><i class="bi bi-<?=$rel?'lock':'unlock'?>"></i> <?=$rel?'Mark unpaid':'Release payment'?></button></form>
                    <?php endif; ?>
                    <button type="button" class="bf-ms-btn" onclick='bfMs(<?= htmlspecialchars(json_encode(["id"=>(int)$m["id"],"title"=>$m["title"],"amount"=>$m["amount"],"due_date"=>$m["due_date"],"description"=>$m["description"]]), ENT_QUOTES) ?>)'><i class="bi bi-pencil"></i> Edit</button>
                    <form method="post" onsubmit="return confirm('Delete this milestone?')"><input type="hidden" name="action" value="milestone_del"><input type="hidden" name="mid" value="<?=$m['id']?>"><button class="bf-ms-btn danger" title="Delete"><i class="bi bi-trash"></i></button></form>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ═══ GOALS ═══ -->
        <div class="bf-panel" data-panel="goals">
          <div class="row g-3">
            <?php foreach ($goals as [$ic,$col,$bg,$title,$desc,$pct,$kr]): ?>
            <div class="col-md-6">
              <div class="bf-pf-card" style="margin-bottom:0;height:100%;"><div class="bf-pf-card-b">
                <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;">
                  <span style="width:42px;height:42px;border-radius:11px;background:<?=$bg?>;color:<?=$col?>;display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;"><i class="bi <?=$ic?>"></i></span>
                  <div><div style="font-size:14px;font-weight:800;color:var(--ink);"><?=$title?></div><div style="font-size:11.5px;color:var(--ink-3);"><?=$desc?></div></div>
                  <span style="margin-left:auto;font-size:17px;font-weight:900;color:<?=$col?>;"><?=$pct?>%</span>
                </div>
                <div style="height:7px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?=$pct?>%;background:<?=$col?>;border-radius:99px;"></div></div>
                <div style="font-size:11px;color:var(--ink-4);margin-top:8px;"><i class="bi bi-graph-up"></i> <?=$kr?></div>
              </div></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- ═══ TEAM (real roster across this project's tasks) ═══ -->
        <div class="bf-panel" data-panel="team">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div style="font-size:12.5px;color:var(--ink-3);"><b style="color:var(--ink);"><?=count($teamRoster)?></b> assignment<?=count($teamRoster)==1?'':'s'?> across this project</div>
            <button type="button" class="bf-btn-accent" onclick="bfTeamAdd()" <?= $tasksTree?'':'disabled' ?>><i class="bi bi-person-plus me-1"></i>Add member</button>
          </div>
          <?php if (!$teamRoster): ?>
            <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-b">
              <div class="bf-pf-empty"><i class="bi bi-people"></i><div class="t">No team members yet</div>
                <p>Assign people to tasks — from the Tasks tab or here — and they'll appear in this roster with their roles, which you can change any time.</p>
                <?php if($tasksTree):?><button type="button" class="bf-btn-accent" onclick="bfTeamAdd()"><i class="bi bi-person-plus me-1"></i>Add a member</button><?php else:?><span style="font-size:12px;color:var(--ink-4);">Add tasks first, then assign a team.</span><?php endif;?>
              </div>
            </div></div>
          <?php else: ?>
          <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-b" style="padding:4px 0;">
            <?php foreach ($teamRoster as $mb): ?>
            <div class="bf-roster">
              <span class="bf-ava lg"><?=$ini($mb['name'])?></span>
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:var(--ink);"><?=htmlspecialchars($mb['name'])?></div>
                <div style="font-size:11px;color:var(--ink-4);"><i class="bi bi-<?= $mb['parent_id']===null?'folder2':'arrow-return-right' ?>"></i> <?=htmlspecialchars($mb['task_title'])?></div>
              </div>
              <form method="post" class="bf-roster-role" style="margin:0;"><input type="hidden" name="action" value="member_role"><input type="hidden" name="ret" value="team"><input type="hidden" name="memid" value="<?=$mb['id']?>">
                <select name="role" class="bf-fi" onchange="this.form.submit()" title="Change role">
                  <option value="">No role</option>
                  <?php foreach ($projRoles as $rl): ?><option <?= ($mb['role']??'')===$rl?'selected':'' ?>><?=$rl?></option><?php endforeach; ?>
                </select>
              </form>
              <form method="post" onsubmit="return confirm('Remove this team member?')" style="margin:0;"><input type="hidden" name="action" value="member_del"><input type="hidden" name="ret" value="team"><input type="hidden" name="memid" value="<?=$mb['id']?>"><button class="bf-icon-btn" title="Remove"><i class="bi bi-trash"></i></button></form>
            </div>
            <?php endforeach; ?>
          </div></div>
          <?php endif; ?>
        </div>

        <!-- ═══ DOCUMENTS ═══ -->
        <div class="bf-panel" data-panel="documents">
          <div class="bf-tbl-wrap">
            <div class="bf-tbl-head"><div style="flex:1;">Document</div><div style="width:90px;" class="d-none d-md-block">Type</div><div style="width:90px;" class="d-none d-md-block">Size</div><div style="width:140px;" class="d-none d-lg-block">Uploaded by</div><div style="width:80px;">Date</div><div style="width:40px;"></div></div>
            <?php foreach ($docs as [$n,$ty,$sz,$by,$dt,$ic,$col]): ?>
            <div class="bf-tbl-row">
              <div style="flex:1;min-width:0;display:flex;align-items:center;gap:11px;"><span style="width:34px;height:34px;border-radius:8px;background:var(--surface);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:<?=$col?>;flex-shrink:0;"><i class="bi <?=$ic?>"></i></span><span class="pri"><?=$n?></span></div>
              <div style="width:90px;" class="d-none d-md-block"><span class="bf-badge grey"><?=$ty?></span></div>
              <div style="width:90px;" class="d-none d-md-block"><?=$sz?></div>
              <div style="width:140px;" class="d-none d-lg-block"><?=$by?></div>
              <div style="width:80px;"><?=$dt?></div>
              <div style="width:40px;text-align:right;"><a href="#" style="color:#1e3a5f;"><i class="bi bi-download"></i></a></div>
            </div>
            <?php endforeach; ?>
            <div style="padding:16px 18px;border-top:1px solid var(--line-2);">
              <div style="border:1.5px dashed var(--line);border-radius:10px;padding:18px;text-align:center;color:var(--ink-4);"><i class="bi bi-cloud-arrow-up" style="font-size:22px;color:#1e3a5f;"></i><div style="font-size:12.5px;margin-top:5px;">Drop files here or <span style="color:#1e3a5f;font-weight:700;">browse</span> — drawings, BOQ, reports, photos</div></div>
            </div>
          </div>
        </div>

        <!-- ═══ BUDGET ═══ -->
        <div class="bf-panel" data-panel="budget">
          <div class="bf-kpis cols-2 mb-3" style="grid-template-columns:repeat(4,1fr);">
            <?php foreach ([
              ['Total budget','KES 45.0M','bi-cash-stack','#1e3a5f','#eaf0f6'],
              ['Spent to date','KES 30.6M','bi-graph-down','#c0392b','#fef2f2'],
              ['Remaining','KES 14.4M','bi-wallet2','#166534','#f0fdf4'],
              ['Variance','2% under','bi-check2-circle','#166534','#f0fdf4'],
            ] as [$l,$v,$ic,$c,$bg]): ?>
            <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
            <?php endforeach; ?>
          </div>
          <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-bar-chart-steps"></i> Cost breakdown by package</div></div>
            <div class="bf-pf-card-b">
              <?php foreach ($budget as [$cat,$alloc,$spent,$pct]): ?>
              <div style="margin-bottom:15px;">
                <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px;"><span style="font-weight:700;color:var(--ink);"><?=$cat?></span><span style="color:var(--ink-3);"><b style="color:#c0392b;"><?=$spent?></b> / <?=$alloc?></span></div>
                <div style="height:7px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?=$pct?>%;background:<?= $pct>=95?'#c0392b':'#1e3a5f' ?>;border-radius:99px;"></div></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ═══ SETTINGS ═══ -->
        <div class="bf-panel" data-panel="settings">
          <div class="row g-3">
            <div class="col-lg-7">
              <form method="post" class="bf-pf-card" style="margin-bottom:0;">
                <input type="hidden" name="action" value="save_settings">
                <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-people"></i> Client collaboration</div></div>
                <div class="bf-pf-card-b" style="padding-top:10px;">
                  <p class="bf-modal-hint">Control what your client can see and do on this project.</p>
                  <?php foreach ([
                    ['client_can_comment','Client can comment &amp; message on the project'],
                    ['client_can_view_budget','Client can see the budget &amp; spend'],
                    ['client_can_view_documents','Client can see documents &amp; drawings'],
                    ['require_milestone_approval','Require client approval before a milestone is paid'],
                    ['use_escrow','Use bildfie escrow to protect payments'],
                    ['notify_client_updates','Email the client when there are updates'],
                  ] as [$k,$lbl]): ?>
                  <label class="bf-toggle-row"><input type="checkbox" name="<?=$k?>" <?= (int)($proj[$k] ?? 0) ? 'checked':'' ?>><span><?=$lbl?></span></label>
                  <?php endforeach; ?>
                  <div class="mt-3"><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save settings</button></div>
                </div>
              </form>
            </div>
            <div class="col-lg-5">
              <div class="bf-pf-card" style="margin-bottom:0;">
                <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-gear"></i> Project setup</div><a href="/pages/projects/new.php?id=<?=(int)$proj['id']?>" class="bf-pf-card-edit"><i class="bi bi-pencil"></i> Edit</a></div>
                <div class="bf-pf-card-b" style="padding-top:8px;padding-bottom:8px;">
                  <?php foreach ([
                    ['Status',$sLabel],['Billing',$bill],['Priority',$prioLabel],
                    ['Currency',(string)$proj['currency']],['Visibility',ucfirst((string)$proj['visibility'])],
                  ] as [$k,$vv]): ?>
                  <div class="bf-pf-contact" style="justify-content:space-between;"><span style="color:var(--ink-3);"><?=$k?></span><span style="font-weight:700;color:var(--ink);"><?=htmlspecialchars($vv)?></span></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- ═══ Add / edit task modal ═══ -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" id="tkAction" value="task_add">
      <input type="hidden" name="tid" id="tkTid" value="">
      <input type="hidden" name="parent_id" id="tkParent" value="">
      <div class="modal-header"><h5 class="modal-title" id="tkHead"><i class="bi bi-list-check me-2" style="color:#c0392b;"></i>Add task</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="bf-fl">Title <span style="color:#c0392b;">*</span></label><input name="title" id="tkTitle" class="bf-fi" placeholder="e.g. Foundation Works" required></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="bf-fl">Payment value (<?=htmlspecialchars($curSym)?>)</label><input name="amount" id="tkAmount" type="number" min="0" step="any" class="bf-fi" placeholder="0"></div>
          <div class="col-6"><label class="bf-fl">Due date</label><input name="due_date" id="tkDue" type="date" class="bf-fi"></div>
        </div>
        <label class="bf-toggle-row" style="margin-bottom:6px;"><input type="checkbox" name="is_milestone" id="tkMile"><span>This stage is a payment milestone</span></label>
        <div><label class="bf-fl">Description</label><textarea name="description" id="tkDesc" rows="2" class="bf-fi" style="resize:vertical;" placeholder="What this covers"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<!-- ═══ Add team member modal ═══ -->
<div class="modal fade" id="memModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="member_add">
      <input type="hidden" name="task_id" id="memTaskId" value="">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus me-2" style="color:#c0392b;"></i>Add team member</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-7"><label class="bf-fl">Name <span style="color:#c0392b;">*</span></label><input name="name" id="memName" class="bf-fi" placeholder="e.g. John Mwangi" required></div>
          <div class="col-5"><label class="bf-fl">Role</label><select name="role" class="bf-fi"><?php foreach (project_task_roles() as $rl): ?><option><?=$rl?></option><?php endforeach; ?></select></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-person-plus me-1"></i>Add member</button></div>
    </form>
  </div>
</div>
<!-- ═══ Add team member from the Team tab (pick a task) ═══ -->
<div class="modal fade" id="teamAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" value="member_add">
      <input type="hidden" name="ret" value="team">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus me-2" style="color:#c0392b;"></i>Add team member</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="row g-2 mb-2">
          <div class="col-7"><label class="bf-fl">Name <span style="color:#c0392b;">*</span></label><input name="name" class="bf-fi" placeholder="e.g. John Mwangi" required></div>
          <div class="col-5"><label class="bf-fl">Role</label><select name="role" class="bf-fi"><?php foreach ($projRoles as $rl): ?><option><?=$rl?></option><?php endforeach; ?></select></div>
        </div>
        <label class="bf-fl">Assign to</label>
        <select name="task_id" class="bf-fi" required>
          <?php foreach ($tasksTree as $s): ?>
          <option value="<?=$s['id']?>"><?=htmlspecialchars($s['title'])?></option>
          <?php foreach ($s['subtasks'] as $st): ?>
          <option value="<?=$st['id']?>">&nbsp;&nbsp;↳ <?=htmlspecialchars($st['title'])?></option>
          <?php endforeach; endforeach; ?>
        </select>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent"><i class="bi bi-person-plus me-1"></i>Add member</button></div>
    </form>
  </div>
</div>
<script>
function bfShow(id){ bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show(); }
function bfTeamAdd(){ bfShow('teamAddModal'); }
function bfTask(parentId){
  document.getElementById('tkAction').value='task_add';
  document.getElementById('tkTid').value='';
  document.getElementById('tkParent').value=parentId;
  document.getElementById('tkHead').innerHTML = parentId ? '<i class="bi bi-plus-lg me-2" style="color:#c0392b;"></i>Add subtask' : '<i class="bi bi-folder-plus me-2" style="color:#c0392b;"></i>Add section';
  ['tkTitle','tkAmount','tkDue','tkDesc'].forEach(function(i){ document.getElementById(i).value=''; });
  document.getElementById('tkMile').checked = !parentId;
  bfShow('taskModal');
}
function bfTaskEdit(d){
  document.getElementById('tkAction').value='task_edit';
  document.getElementById('tkTid').value=d.id;
  document.getElementById('tkParent').value='';
  document.getElementById('tkHead').innerHTML='<i class="bi bi-pencil me-2" style="color:#c0392b;"></i>Edit task';
  document.getElementById('tkTitle').value=d.title||'';
  document.getElementById('tkAmount').value=(d.amount!=null)?d.amount:'';
  document.getElementById('tkDue').value=d.due_date||'';
  document.getElementById('tkDesc').value=d.description||'';
  document.getElementById('tkMile').checked=!!d.is_milestone;
  bfShow('taskModal');
}
function bfMem(taskId){ document.getElementById('memTaskId').value=taskId; document.getElementById('memName').value=''; bfShow('memModal'); }
</script>

<!-- ═══ Add / edit milestone modal ═══ -->
<div class="modal fade" id="msModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content bf-edit-modal">
      <input type="hidden" name="action" id="msAction" value="milestone_add">
      <input type="hidden" name="mid" id="msId" value="">
      <div class="modal-header"><h5 class="modal-title" id="msHead"><i class="bi bi-flag me-2" style="color:#c0392b;"></i>Add milestone</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="bf-fl">Title <span style="color:#c0392b;">*</span></label><input name="title" id="msTitleInput" class="bf-fi" placeholder="e.g. Foundation &amp; substructure" required></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="bf-fl">Value (<?= htmlspecialchars($curSym) ?>)</label><input name="amount" id="msAmount" type="number" min="0" step="any" class="bf-fi" placeholder="0"></div>
          <div class="col-6"><label class="bf-fl">Due date</label><input name="due_date" id="msDue" type="date" class="bf-fi"></div>
        </div>
        <div class="mb-3" id="msStatusWrap"><label class="bf-fl">Status</label><select name="status" class="bf-fi"><option value="upcoming">Upcoming</option><option value="active">In progress</option><option value="completed">Completed</option></select></div>
        <div><label class="bf-fl">Description</label><textarea name="description" id="msDesc" rows="2" class="bf-fi" style="resize:vertical;" placeholder="What this stage covers"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="bf-btn-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bf-btn-accent" id="msSave"><i class="bi bi-check-lg me-1"></i>Add milestone</button></div>
    </form>
  </div>
</div>
<script>
function bfMs(d){
  var edit = !!d;
  document.getElementById('msAction').value = edit ? 'milestone_edit' : 'milestone_add';
  document.getElementById('msId').value = edit ? d.id : '';
  document.getElementById('msHead').innerHTML = edit ? '<i class="bi bi-pencil me-2" style="color:#c0392b;"></i>Edit milestone' : '<i class="bi bi-flag me-2" style="color:#c0392b;"></i>Add milestone';
  document.getElementById('msTitleInput').value = edit ? (d.title || '') : '';
  document.getElementById('msAmount').value = (edit && d.amount != null) ? d.amount : '';
  document.getElementById('msDue').value = edit ? (d.due_date || '') : '';
  document.getElementById('msDesc').value = edit ? (d.description || '') : '';
  document.getElementById('msStatusWrap').style.display = edit ? 'none' : '';
  document.getElementById('msSave').innerHTML = edit ? '<i class="bi bi-check-lg me-1"></i>Save milestone' : '<i class="bi bi-check-lg me-1"></i>Add milestone';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('msModal')).show();
}
</script>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
