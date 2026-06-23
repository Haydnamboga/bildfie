<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Dashboard'; $sp = 'dashboard';
$u = current_user();
$greeting = date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening');
$first = explode(' ', $u['name'])[0];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:24px 28px;">

      <!-- header -->
      <div class="bf-dash-h">
        <div>
          <div class="bf-eyebrow2"><span>—</span> Overview</div>
          <h1 class="bf-dash-title">Good <?= $greeting ?>, <?= htmlspecialchars($first) ?> 👋</h1>
          <p class="bf-dash-sub">Here's everything happening across your business today.</p>
        </div>
        <span style="font-size:12px;color:var(--ink-4);" class="d-none d-md-block"><i class="bi bi-calendar3 me-1"></i><?= date('l, d M Y') ?></span>
      </div>

      <!-- ═══ compact KPI strip ═══ -->
      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Active Projects','7','bi-kanban','#1e3a5f','#eaf0f6','+2','up'],
          ['Open Tasks','34','bi-check2-square','#b45309','#fffbeb','9 due','flat'],
          ['Team Members','12','bi-people','#166534','#f0fdf4','3 online','flat'],
          ['Awaiting Payment','KES 3.28M','bi-hourglass-split','#c0392b','#fef2f2','5 invoices','down'],
          ['Proposals Sent','9','bi-file-earmark-text','#1e40af','#eff6ff','4 pending','flat'],
          ['Active Contracts','5','bi-file-earmark-ruled','#1e3a5f','#eaf0f6','1 expiring','flat'],
          ['Inventory Items','148','bi-box-seam','#9a7d27','#fdf6e3','6 low','down'],
          ['Revenue (MTD)','KES 2.53M','bi-graph-up-arrow','#166534','#f0fdf4','+18%','up'],
        ] as [$l,$v,$ic,$icCol,$icBg,$chg,$dir]): ?>
        <div class="bf-kpi">
          <div class="bf-kpi-top">
            <div class="bf-kpi-ic" style="background:<?= $icBg ?>;color:<?= $icCol ?>;"><i class="bi <?= $ic ?>"></i></div>
            <span class="bf-kpi-chg <?= $dir ?>"><?php if($dir==='up'):?><i class="bi bi-arrow-up-short"></i><?php elseif($dir==='down'):?><i class="bi bi-arrow-down-short"></i><?php endif;?><?= $chg ?></span>
          </div>
          <div class="bf-kpi-v"><?= $v ?></div>
          <div class="bf-kpi-l"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <!-- Revenue chart -->
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-bar-chart-line"></i> Revenue &amp; collections</div>
              <span style="font-size:11.5px;color:var(--ink-4);">Last 6 months · <b style="color:var(--ink);">KES 14.2M</b></span>
            </div>
            <div class="bf-pf-card-b">
              <div class="bf-bars">
                <?php foreach ([['Jan',55],['Feb',68],['Mar',60],['Apr',82],['May',74],['Jun',95]] as $i=>[$m,$h]): ?>
                <div class="col"><div class="bar <?= $i===5?'alt':'' ?>" style="height:<?= $h ?>%;"></div><div class="lbl"><?= $m ?></div></div>
                <?php endforeach; ?>
              </div>
              <div style="display:flex;gap:24px;margin-top:16px;padding-top:14px;border-top:1px solid var(--line-2);">
                <?php foreach ([['Billed','KES 6.35M','#1e3a5f'],['Collected','KES 4.82M','#166534'],['Outstanding','KES 3.28M','#c0392b']] as [$k,$v,$c]): ?>
                <div><div style="font-size:10px;color:var(--ink-4);font-weight:700;text-transform:uppercase;letter-spacing:.05em;"><?= $k ?></div><div style="font-size:16px;font-weight:900;color:<?= $c ?>;margin-top:3px;"><?= $v ?></div></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales pipeline -->
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-funnel"></i> Sales pipeline</div></div>
            <div class="bf-pf-card-b" style="padding-top:10px;">
              <?php foreach ([
                ['Proposals','9','KES 18.4M','#1e40af',75],
                ['Estimates','12','KES 9.2M','#9a7d27',55],
                ['Invoices','23','KES 6.35M','#1e3a5f',88],
                ['Won this month','6','KES 4.1M','#166534',40],
              ] as [$k,$n,$val,$c,$pct]): ?>
              <div style="margin-bottom:13px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;"><span style="font-weight:700;color:var(--ink);"><?= $k ?> <span style="color:var(--ink-4);font-weight:600;">· <?= $n ?></span></span><span style="font-weight:800;color:<?= $c ?>;"><?= $val ?></span></div>
                <div style="height:6px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?= $pct ?>%;background:<?= $c ?>;border-radius:99px;"></div></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Active projects -->
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h">
              <div class="bf-pf-card-t"><i class="bi bi-kanban"></i> Active projects</div>
              <a href="/pages/projects/index.php" class="bf-pf-card-edit" style="color:#c0392b;">View all →</a>
            </div>
            <div class="bf-tbl-head">
              <div style="flex:1;">Project</div>
              <div style="width:160px;" class="d-none d-md-block">Progress</div>
              <div style="width:90px;" class="d-none d-md-block">Due</div>
              <div style="width:80px;">Status</div>
            </div>
            <?php foreach ([
              ['Westlands Office Block','Commercial · Structure',68,'30 Jun','green','Active'],
              ['Riverside Apartments','Residential · Foundation',22,'15 Sep','amber','At risk'],
              ['School Extension – Ngong','Institutional · Finishing',90,'10 Jun','green','Active'],
              ['Thika Road Retail Park','Commercial · Design',10,'Dec','navy','Planning'],
            ] as [$n,$meta,$pct,$due,$bc,$st]): ?>
            <div class="bf-tbl-row">
              <div style="flex:1;min-width:0;"><a href="/pages/projects/view.php" class="pri" style="text-decoration:none;"><?= $n ?></a><div style="font-size:11px;color:var(--ink-4);margin-top:2px;"><?= $meta ?></div></div>
              <div style="width:160px;" class="d-none d-md-block"><div style="display:flex;align-items:center;gap:8px;"><div style="flex:1;height:6px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?= $pct ?>%;background:<?= $pct>=80?'#22c55e':($pct<25?'#f59e0b':'#1e3a5f') ?>;"></div></div><span style="font-size:11px;font-weight:700;color:var(--ink-3);"><?= $pct ?>%</span></div></div>
              <div style="width:90px;font-size:11.5px;" class="d-none d-md-block"><?= $due ?></div>
              <div style="width:80px;"><span class="bf-badge <?= $bc ?>"><?= $st ?></span></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tasks due -->
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-check2-square"></i> Tasks due soon</div><span style="font-size:11px;color:#c0392b;font-weight:700;">9 open</span></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['Approve structural drawings','Westlands','Today','#c0392b'],
                ['Site inspection — Block A','Riverside','Tomorrow','#b45309'],
                ['Submit BOQ revision','Thika Park','2 days','#1e3a5f'],
                ['Pay supplier — Bamburi','Finance','3 days','#1e3a5f'],
                ['Review subcontractor bids','Ngong','5 days','#166534'],
              ] as [$t,$p,$d,$c]): ?>
              <label class="bf-li" style="cursor:pointer;">
                <input type="checkbox" style="width:15px;height:15px;accent-color:#1e3a5f;flex-shrink:0;">
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:600;color:var(--ink);"><?= $t ?></div><div style="font-size:10.5px;color:var(--ink-4);"><?= $p ?></div></div>
                <span style="font-size:10.5px;font-weight:700;color:<?= $c ?>;white-space:nowrap;"><?= $d ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Recent activity -->
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-activity"></i> Recent activity</div><span style="font-size:10px;color:#22c55e;font-weight:700;"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span> Live</span></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['bi-person-check','#166534','#f0fdf4','<strong>John Mwangi</strong> accepted your invite to Westlands Office Block','2h ago'],
                ['bi-receipt','#1e3a5f','#eaf0f6','Invoice <strong>#INV-2026-041</strong> was paid · KES 1,250,000','5h ago'],
                ['bi-file-earmark-text','#1e40af','#eff6ff','Proposal <strong>#PRP-118</strong> sent to Riverside Devs','Yesterday'],
                ['bi-exclamation-triangle','#b45309','#fffbeb','Task overdue: <strong>Structural drawings</strong>','Yesterday'],
                ['bi-box-seam','#9a7d27','#fdf6e3','Low stock: <strong>OPC Cement 50kg</strong> (12 left)','2 days ago'],
              ] as [$ic,$col,$bg,$txt,$when]): ?>
              <div class="bf-li">
                <span class="bf-li-ic" style="background:<?= $bg ?>;color:<?= $col ?>;"><i class="bi <?= $ic ?>"></i></span>
                <div style="flex:1;min-width:0;"><div style="font-size:12px;color:var(--ink-2);line-height:1.45;"><?= $txt ?></div><div style="font-size:10.5px;color:var(--ink-4);margin-top:1px;"><?= $when ?></div></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Right rail: milestones + team -->
        <div class="col-lg-4">
          <div class="bf-pf-card">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-flag"></i> Upcoming milestones</div></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['Foundation sign-off','Riverside','12 Jun','#f59e0b'],
                ['1st floor slab pour','Westlands','18 Jun','#1e3a5f'],
                ['Handover & snagging','Ngong','28 Jun','#166534'],
              ] as [$m,$p,$d,$c]): ?>
              <div class="bf-li">
                <span style="width:10px;height:10px;border-radius:50%;background:<?= $c ?>;flex-shrink:0;"></span>
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;color:var(--ink);"><?= $m ?></div><div style="font-size:10.5px;color:var(--ink-4);"><?= $p ?></div></div>
                <span style="font-size:11px;font-weight:700;color:var(--ink-3);"><?= $d ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-people"></i> Team</div><a href="/pages/team/index.php" class="bf-pf-card-edit" style="color:#c0392b;">Manage →</a></div>
            <div class="bf-pf-card-b" style="padding-top:14px;">
              <?php foreach ([
                ['John Mwangi','Structural Eng.','men/32',true],
                ['Amina Osei','Architect','women/44',true],
                ['Peter Njoroge','Site Foreman','men/76',false],
                ['Grace Wanjiku','QS / Budget','women/25',true],
              ] as [$n,$r,$ph,$on]): ?>
              <div class="bf-li" style="padding:8px 0;">
                <div style="position:relative;flex-shrink:0;"><img src="https://randomuser.me/api/portraits/<?= $ph ?>.jpg" style="width:34px;height:34px;border-radius:50%;object-fit:cover;"><span style="position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:<?= $on?'#22c55e':'#d8d8d4' ?>;border:2px solid var(--white);"></span></div>
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;color:var(--ink);"><?= $n ?></div><div style="font-size:10.5px;color:var(--ink-4);"><?= $r ?></div></div>
                <span style="font-size:10px;color:<?= $on?'#16a34a':'var(--ink-4)' ?>;font-weight:700;"><?= $on?'Online':'Away' ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Outstanding invoices -->
        <div class="col-lg-12">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-receipt"></i> Outstanding invoices</div><a href="/pages/dashboard/invoices.php" class="bf-pf-card-edit" style="color:#c0392b;">View all →</a></div>
            <div class="bf-tbl-head">
              <div style="width:130px;">Invoice</div>
              <div style="flex:1;">Client</div>
              <div style="width:120px;" class="d-none d-md-block">Amount</div>
              <div style="width:100px;" class="d-none d-md-block">Due</div>
              <div style="width:90px;">Status</div>
            </div>
            <?php foreach ([
              ['INV-2026-042','Daniel Otieno','KES 480,000','26 May','navy','Sent'],
              ['INV-2026-039','County Roads Authority','KES 2,800,000','05 May','red','Overdue'],
              ['INV-2026-044','Skyline Developers','KES 920,000','30 Jun','navy','Sent'],
            ] as [$num,$cl,$amt,$due,$bc,$st]): ?>
            <div class="bf-tbl-row">
              <div style="width:130px;font-weight:800;color:#1e3a5f;"><?= $num ?></div>
              <div style="flex:1;" class="pri"><?= $cl ?></div>
              <div style="width:120px;font-weight:800;color:var(--ink);" class="d-none d-md-block"><?= $amt ?></div>
              <div style="width:100px;" class="d-none d-md-block"><?= $due ?></div>
              <div style="width:90px;"><span class="bf-badge <?= $bc ?>"><?= $st ?></span></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
