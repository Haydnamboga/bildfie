<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Dashboard';
$ap = 'dashboard';
$admin = current_admin();
$greeting = date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening');
$first = explode(' ', $admin['name'] ?? 'Owner')[0];

// ── Live metrics from the database ──
$members    = (int) db_value("SELECT COUNT(*) FROM users");
$providers  = (int) db_value("SELECT COUNT(*) FROM providers");
$verified   = (int) db_value("SELECT COUNT(*) FROM providers WHERE is_verified=1");
$staffN     = (int) db_value("SELECT COUNT(*) FROM staff");
$verticalsN = (int) db_value("SELECT COUNT(*) FROM verticals");
$servicesN  = (int) db_value("SELECT COUNT(*) FROM categories");
$deptN      = (int) db_value("SELECT COUNT(*) FROM departments");
$avgRating  = db_value("SELECT ROUND(AVG(rating),1) FROM providers") ?: '0.0';
$listed     = (int) db_value("SELECT COALESCE(SUM(provider_count),0) FROM verticals");

// Charts (real data)
$supplyRows = db_all("SELECT name, provider_count FROM verticals ORDER BY provider_count DESC");
$supplyTop = array_slice($supplyRows, 0, 6);
$supplyOther = array_sum(array_map(fn($r)=>(int)$r['provider_count'], array_slice($supplyRows, 6)));
$supplyLabels = array_map(fn($r)=>explode(' ',$r['name'])[0], $supplyTop);
$supplyData   = array_map(fn($r)=>(int)$r['provider_count'], $supplyTop);
if ($supplyOther > 0) { $supplyLabels[] = 'Other'; $supplyData[] = $supplyOther; }

$svcRows = db_all("SELECT v.name, COUNT(c.id) AS cnt FROM verticals v LEFT JOIN categories c ON c.vertical_id=v.id GROUP BY v.id ORDER BY cnt DESC LIMIT 8");
$svcLabels = array_map(fn($r)=>explode(' ',$r['name'])[0], $svcRows);
$svcData   = array_map(fn($r)=>(int)$r['cnt'], $svcRows);

$deptRows = db_all("SELECT d.name, COUNT(s.id) AS cnt FROM departments d LEFT JOIN staff s ON s.department_id=d.id GROUP BY d.id HAVING cnt > 0 ORDER BY cnt DESC");
$deptLabels = array_map(fn($r)=>$r['name'], $deptRows);
$deptData   = array_map(fn($r)=>(int)$r['cnt'], $deptRows);

// Real activity from the audit log
$activity = db_all("SELECT action, actor_name, entity_type, entity_id, created_at FROM audit_logs ORDER BY id DESC LIMIT 8");

// Verticals strip (real)
$vstrip = db_all("SELECT name, icon, color, bg, provider_count FROM verticals WHERE is_active=1 ORDER BY provider_count DESC LIMIT 6");
$vtotal = max(1, $listed);

// Departments strip (real staff per dept)
$depts = db_all("SELECT d.name, COUNT(s.id) AS cnt FROM departments d LEFT JOIN staff s ON s.department_id=d.id GROUP BY d.id ORDER BY d.id");
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <div class="bf-dash-h" style="margin-bottom:14px;">
        <div>
          <div class="bf-eyebrow2"><span>—</span> Mission Control</div>
          <h1 class="bf-dash-title">Good <?= $greeting ?>, <?= htmlspecialchars($first) ?> 👋</h1>
          <p class="bf-dash-sub">Live metrics straight from the database.</p>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2">
          <span style="font-size:11px;color:#16a34a;font-weight:700;"><span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;"></span> Live · DB</span>
          <span style="font-size:12px;color:var(--ink-4);"><i class="bi bi-calendar3 me-1"></i><?= date('D, d M Y') ?></span>
        </div>
      </div>

      <!-- ═══ real KPI strip ═══ -->
      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Members', number_format($members), 'bi-people','#1e40af','#eff6ff'],
          ['Providers', number_format($providers), 'bi-person-badge','#1e3a5f','#eaf0f6'],
          ['Verified', number_format($verified), 'bi-patch-check','#166534','#f0fdf4'],
          ['Avg rating', $avgRating, 'bi-star','#9a7d27','#fdf6e3'],
          ['Verticals', number_format($verticalsN), 'bi-grid-3x3-gap','#1e3a5f','#eaf0f6'],
          ['Services', number_format($servicesN), 'bi-tags','#9a7d27','#fdf6e3'],
          ['Staff', number_format($staffN), 'bi-person-workspace','#1e40af','#eff6ff'],
          ['Departments', number_format($deptN), 'bi-diagram-3','#166534','#f0fdf4'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi">
          <div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div><span class="bf-kpi-chg flat" style="color:#16a34a;"><i class="bi bi-circle-fill" style="font-size:6px;"></i> live</span></div>
          <div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- ═══ real charts ═══ -->
      <div class="row g-3 mb-3">
        <div class="col-lg-5">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-pie-chart"></i> Listed supply by vertical</div><span style="font-size:11px;color:var(--ink-4);"><?= number_format($listed) ?> listed</span></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:210px;"><canvas id="supplyChart"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-bar-chart"></i> Services per vertical</div></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:210px;"><canvas id="svcChart"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-people"></i> Staff by department</div></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:210px;"><canvas id="deptChart"></canvas></div></div>
          </div>
        </div>
      </div>

      <!-- ═══ verticals (real) ═══ -->
      <div style="font-size:13px;font-weight:800;color:var(--ink);margin:4px 0 10px;display:flex;align-items:center;gap:8px;">Top verticals <span class="bf-badge navy"><?= $verticalsN ?> total</span><a href="/admin/catalog.php" style="margin-left:auto;font-size:11px;font-weight:700;color:#c0392b;text-decoration:none;">Manage →</a></div>
      <div class="row g-2 mb-3">
        <?php foreach ($vstrip as $v): $pct = round(((int)$v['provider_count'] / $vtotal) * 100); ?>
        <div class="col-6 col-md-4 col-xl-2">
          <div class="adm-vert">
            <div class="adm-vert-top"><div class="adm-vert-ic" style="background:<?=htmlspecialchars($v['bg'])?>;color:<?=htmlspecialchars($v['color'])?>;"><i class="bi <?=htmlspecialchars($v['icon'])?>"></i></div><div class="adm-vert-title"><?=htmlspecialchars(explode(' ',$v['name'])[0])?></div></div>
            <div class="adm-vert-amt"><?= number_format((int)$v['provider_count']) ?></div>
            <div class="adm-vert-bar"><span style="width:<?=$pct?>%;background:<?=htmlspecialchars($v['color'])?>;"></span></div>
            <div class="adm-vert-pct"><?=$pct?>% of supply</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <!-- real audit activity -->
        <div class="col-lg-5">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-clipboard-data"></i> Activity</div><span class="bf-badge green" style="font-size:9px;"><i class="bi bi-database"></i> Audit log</span></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php if (!$activity): ?>
              <div style="font-size:12.5px;color:var(--ink-4);padding:8px 0;">No activity recorded yet.</div>
              <?php else: foreach ($activity as $a): ?>
              <div class="bf-li"><span class="bf-li-ic" style="background:var(--surface);color:#1e3a5f;border:1px solid var(--line);"><i class="bi bi-dot" style="font-size:20px;"></i></span>
                <div style="flex:1;min-width:0;"><div style="font-size:12px;color:var(--ink-2);"><b><?= htmlspecialchars($a['actor_name'] ?? 'System') ?></b> · <code style="font-size:11px;color:#1e3a5f;"><?= htmlspecialchars($a['action']) ?></code></div></div>
                <span style="font-size:10.5px;color:var(--ink-4);white-space:nowrap;"><?= date('d M · H:i', strtotime($a['created_at'])) ?></span>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>

        <!-- platform explorer (real counts) -->
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-binoculars"></i> Explore the platform</div></div>
            <div class="bf-pf-card-b" style="padding-top:12px;">
              <div class="row g-2">
                <?php foreach ([
                  ['Members', number_format($members),'bi-people','/admin/roles.php'],
                  ['Providers', number_format($providers),'bi-person-badge','/admin/providers.php'],
                  ['Verticals', number_format($verticalsN),'bi-grid-3x3-gap','/admin/catalog.php'],
                  ['Services', number_format($servicesN),'bi-tags','/admin/catalog.php'],
                ] as [$l,$v,$ic,$h]): ?>
                <div class="col-6"><a href="<?=$h?>" style="display:flex;align-items:center;gap:10px;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:10px 12px;text-decoration:none;">
                  <i class="bi <?=$ic?>" style="color:#1e3a5f;font-size:16px;"></i><div><div style="font-size:14px;font-weight:900;color:var(--ink);line-height:1;"><?=$v?></div><div style="font-size:10px;color:var(--ink-4);"><?=$l?></div></div>
                </a></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- system health (infra) -->
        <div class="col-lg-3">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-activity"></i> System</div><span style="font-size:11px;color:#16a34a;font-weight:700;">Healthy</span></div>
            <div class="bf-pf-card-b" style="padding-top:10px;">
              <?php foreach ([['Database','MariaDB','ok'],['Web server','PHP 8','ok'],['Migrations','0001–0004','ok'],['Members table',$members.' rows','ok']] as [$s,$v,$st]): ?>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-top:1px solid var(--line-2);font-size:11.5px;"><span style="color:var(--ink-2);"><?=$s?></span><span style="font-weight:700;color:#16a34a;"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:5px;"></span><?=$v?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- departments (real staff counts) -->
        <div class="col-12"><div style="font-size:13px;font-weight:800;color:var(--ink);margin:6px 0 2px;">Departments</div></div>
        <?php
        $deptIcons = ['Executive'=>['bi-people','#1e3a5f','#eaf0f6'],'Operations'=>['bi-shop','#1e40af','#eff6ff'],'Finance'=>['bi-cash-coin','#166534','#f0fdf4'],'Technical'=>['bi-activity','#1e3a5f','#eaf0f6'],'People & HR'=>['bi-person-hearts','#c0392b','#fef2f2'],'Marketing'=>['bi-megaphone','#9a7d27','#fdf6e3'],'Business Dev'=>['bi-handshake','#9a7d27','#fdf6e3'],'Support'=>['bi-headset','#1e40af','#eff6ff'],'Trust & Safety'=>['bi-shield-check','#b45309','#fffbeb']];
        foreach ($depts as $d): [$ic,$c,$bg] = $deptIcons[$d['name']] ?? ['bi-diagram-3','#1e3a5f','#eaf0f6']; ?>
        <div class="col-6 col-md-4 col-xl-2">
          <div class="adm-deptc">
            <div class="d-flex align-items-center justify-content-between">
              <div class="adm-vert-ic" style="background:<?=$bg?>;color:<?=$c?>;width:32px;height:32px;font-size:15px;"><i class="bi <?=$ic?>"></i></div>
              <span class="bf-badge <?= (int)$d['cnt']>0?'green':'grey' ?>" style="font-size:8.5px;"><?= (int)$d['cnt'] ?> staff</span>
            </div>
            <div style="font-size:12px;font-weight:800;color:var(--ink);margin-top:9px;line-height:1.2;"><?=htmlspecialchars($d['name'])?></div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- transactions pending note -->
        <div class="col-12">
          <div style="background:#eaf0f6;border:1px solid #d6e2ee;border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <i class="bi bi-graph-up-arrow" style="font-size:20px;color:#1e3a5f;"></i>
            <div style="flex:1;"><div style="font-size:12.5px;font-weight:800;color:#1e3a5f;">Revenue, GMV, escrow &amp; orders come online with the next modules</div><div style="font-size:11.5px;color:var(--ink-3);">These KPIs activate once the transaction (0005) and payments (0006) tables exist — then they compute live, like the metrics above.</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (typeof Chart === 'undefined') return;
  Chart.defaults.font.size = 11; Chart.defaults.color = '#6b6b6b';
  var palette = ['#1e3a5f','#1e40af','#9a7d27','#166534','#c0392b','#7c3aed','#b45309'];
  new Chart(document.getElementById('supplyChart'), {
    type:'doughnut',
    data:{ labels:<?= json_encode($supplyLabels) ?>, datasets:[{ data:<?= json_encode($supplyData) ?>, backgroundColor:palette, borderWidth:0 }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'60%', plugins:{legend:{position:'right',labels:{boxWidth:9,boxHeight:9,usePointStyle:true,font:{size:10}}}} }
  });
  new Chart(document.getElementById('svcChart'), {
    type:'bar',
    data:{ labels:<?= json_encode($svcLabels) ?>, datasets:[{ data:<?= json_encode($svcData) ?>, backgroundColor:'#1e3a5f', borderRadius:5 }]},
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{grid:{color:'#eee'},ticks:{precision:0}},x:{grid:{display:false}}} }
  });
  new Chart(document.getElementById('deptChart'), {
    type:'doughnut',
    data:{ labels:<?= json_encode($deptLabels) ?>, datasets:[{ data:<?= json_encode($deptData) ?>, backgroundColor:palette, borderWidth:0 }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'58%', plugins:{legend:{position:'bottom',labels:{boxWidth:8,boxHeight:8,usePointStyle:true,font:{size:9}}}} }
  });
});
</script>
<?php include __DIR__ . '/includes/foot.php'; ?>
