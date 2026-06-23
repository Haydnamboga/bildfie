<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';
require_login();
$uid = (int) current_user()['id'];
$page_title = 'My Projects'; $sp = 'projects';

$all  = user_projects($uid);
$defs = project_status_defs();
$byStatus = [];
foreach ($all as $p) { $byStatus[project_status_key($p['status'])][] = $p; }
$total = count($all);
$flash = ($_GET['saved'] ?? '') ? 'Project saved.' : (($_GET['created'] ?? '') ? 'Project created.' : '');
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <div class="bf-body" style="padding:28px;">

      <!-- header -->
      <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3">
        <div>
          <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Projects</div>
          <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">My projects</h1>
          <p style="font-size:13px;color:var(--ink-3);margin:0;">Organised by status — track progress, billing and timelines across all your builds.</p>
        </div>
        <a href="/pages/projects/new.php" class="bf-btn-accent" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>New project</a>
      </div>

      <?php if ($flash): ?>
      <div class="d-flex align-items-center gap-2 mb-3" data-ms="4000" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:11px 15px;font-size:13px;"><i class="bi bi-check-circle-fill"></i><div><?= htmlspecialchars($flash) ?></div></div>
      <?php endif; ?>

      <?php if (!$total): ?>
        <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-b">
          <div class="bf-pf-empty">
            <i class="bi bi-kanban"></i>
            <div class="t">No projects yet</div>
            <p>Create your first project to track its progress, billing and timeline — and to collaborate with your client in one place.</p>
            <a href="/pages/projects/new.php" class="bf-btn-accent" style="text-decoration:none;"><i class="bi bi-plus-lg me-1"></i>Create your first project</a>
          </div>
        </div></div>
      <?php else: ?>

      <!-- status filter chips -->
      <div class="d-flex flex-wrap gap-2 mb-3" id="statusChips">
        <button class="bf-chip on" data-filter="all">All <span class="n"><?= $total ?></span></button>
        <?php foreach ($defs as $sv => [$sl,$sc,$sbg]): $n = count($byStatus[$sv] ?? []); if (!$n) continue; ?>
        <button class="bf-chip" data-filter="<?= $sv ?>" style="--cc:<?= $sc ?>;--cbg:<?= $sbg ?>;"><?= $sl ?> <span class="n"><?= $n ?></span></button>
        <?php endforeach; ?>
        <div class="bf-login-field ms-auto" style="flex:0 1 280px;padding:0 12px;"><i class="bi bi-search"></i><input type="text" id="projSearch" placeholder="Search projects…" style="padding:9px 0;font-size:13px;"></div>
      </div>

      <!-- status sections -->
      <div id="projSections">
      <?php foreach ($defs as $sv => [$sl,$sc,$sbg]):
        $rows = $byStatus[$sv] ?? []; if (!$rows) continue; ?>
        <section class="bf-projsec" data-status="<?= $sv ?>">
          <div class="bf-projsec-h">
            <span class="dot" style="background:<?= $sc ?>;"></span>
            <span class="lbl"><?= $sl ?></span>
            <span class="cnt"><?= count($rows) ?></span>
          </div>
          <div class="bf-projsec-body">
          <?php foreach ($rows as $r):
            $pub = $r['public_id']; $pct = (int) $r['progress'];
            $cust = $r['customer_name'] ?: '—';
            $bill = project_billing_label($r['billing_type']);
            [$pl,$pc] = project_priority_defs()[$r['priority'] ?? 'normal'] ?? ['Normal','#1e3a5f'];
            $due = $r['deadline'] ? date('d M Y', strtotime($r['deadline'])) : ($r['deadline_label'] ?: '—');
            $bar = $pct>=80?'#16a34a':($pct<25?'#f59e0b':'#1e3a5f');
          ?>
          <div class="bf-projrow" data-name="<?= htmlspecialchars(strtolower($r['name'].' '.$cust), ENT_QUOTES) ?>">
            <div style="flex:1;min-width:0;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= $pc ?>;flex-shrink:0;" title="<?= $pl ?> priority"></span>
                <a href="/pages/projects/view.php?id=<?= urlencode($pub) ?>" class="bf-projrow-name"><?= htmlspecialchars($r['name']) ?></a>
              </div>
              <div class="bf-projrow-sub"><i class="bi bi-person"></i> <?= htmlspecialchars($cust) ?> &nbsp;·&nbsp; <i class="bi bi-cash-coin"></i> <?= htmlspecialchars($bill) ?><?php if ($r['type']): ?> &nbsp;·&nbsp; <?= htmlspecialchars($r['type']) ?><?php endif; ?></div>
            </div>
            <div class="bf-projrow-prog d-none d-md-block">
              <div class="d-flex align-items-center gap-2">
                <div style="flex:1;height:6px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?= $pct ?>%;background:<?= $bar ?>;border-radius:99px;"></div></div>
                <span style="font-size:11px;font-weight:700;color:var(--ink-3);width:32px;text-align:right;"><?= $pct ?>%</span>
              </div>
            </div>
            <div class="bf-projrow-budget d-none d-lg-block"><?= htmlspecialchars($r['budget_display'] ?: '—') ?></div>
            <div class="bf-projrow-due d-none d-lg-block"><i class="bi bi-calendar3" style="font-size:10px;"></i> <?= htmlspecialchars($due) ?></div>
            <div class="dropdown" style="width:40px;text-align:right;">
              <button class="btn btn-sm border-0 p-1" data-bs-toggle="dropdown" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:10px;">
                <li><a class="dropdown-item" href="/pages/projects/view.php?id=<?= urlencode($pub) ?>"><i class="bi bi-eye me-2 text-muted"></i>Open</a></li>
                <li><a class="dropdown-item" href="/pages/projects/new.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil me-2 text-muted"></i>Edit</a></li>
              </ul>
            </div>
          </div>
          <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
      </div>
      <div id="projNoResults" style="display:none;text-align:center;color:var(--ink-4);font-size:13px;padding:30px;">No projects match your search.</div>

      <?php endif; ?>

    </div>
  </div>
</div>
<script>
(function(){
  var chips=document.querySelectorAll('#statusChips .bf-chip'), search=document.getElementById('projSearch');
  var sections=document.querySelectorAll('.bf-projsec'), noRes=document.getElementById('projNoResults');
  var filter='all';
  function apply(){
    var q=(search&&search.value||'').trim().toLowerCase(), anyShown=false;
    sections.forEach(function(sec){
      var visRows=0;
      sec.querySelectorAll('.bf-projrow').forEach(function(row){
        var okStatus = (filter==='all' || sec.dataset.status===filter);
        var okText = !q || (row.dataset.name||'').indexOf(q)>-1;
        var show = okStatus && okText;
        row.style.display = show?'':'none';
        if(show) visRows++;
      });
      sec.style.display = visRows? '' : 'none';
      if(visRows) anyShown=true;
    });
    if(noRes) noRes.style.display = anyShown? 'none':'block';
  }
  chips.forEach(function(c){ c.addEventListener('click', function(){ chips.forEach(function(x){x.classList.remove('on');}); c.classList.add('on'); filter=c.dataset.filter; apply(); }); });
  if(search) search.addEventListener('input', apply);
})();
</script>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
