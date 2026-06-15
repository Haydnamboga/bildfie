<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Ad Manager';
$ap = 'ads_overview';
$topbar_crumb = 'Advertising';
$topbar_action = '<button class="bf-topbar-new" style="border:none;cursor:pointer;background:#7c3aed;"><i class="bi bi-plus-lg me-1"></i>New campaign</button>';

$st = ['live'=>['Live','green'],'review'=>['In review','amber'],'paused'=>['Paused','grey'],'ended'=>['Ended','navy'],'limited'=>['Budget low','red']];
$campaigns = [
  ['Bamburi — Cement promo','Bamburi Cement','Sponsored Listing','KES 600K','KES 412K','2.1M','3.1%','KES 196','live'],
  ['Equity — SME loans','Equity Bank','Display & Banner','KES 500K','KES 348K','1.8M','1.9%','KES 193','live'],
  ['JCB — Backhoe hire','JCB Kenya','Promoted Search','KES 400K','KES 281K','940K','4.2%','KES 299','live'],
  ['Safaricom — M-Pesa biz','Safaricom','Display & Banner','KES 350K','KES 240K','1.2M','2.4%','KES 200','limited'],
  ['Diani Resorts — travel','Diani Collection','Sponsored Listing','KES 180K','KES 96K','420K','3.8%','KES 229','live'],
  ['Mabati — roofing','Mabati Rolling','Promoted Search','KES 150K','KES 0','—','—','—','review'],
  ['CIC — contractor cover','CIC Insurance','Video','KES 220K','KES 188K','610K','1.4%','KES 308','paused'],
];
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">

      <div class="bf-dash-h" style="margin-bottom:14px;">
        <div>
          <div class="bf-eyebrow2" style="color:#7c3aed;"><span>—</span> Advertising · Retail media</div>
          <h1 class="bf-dash-title">Ad Manager</h1>
          <p class="bf-dash-sub">Sponsored listings, display and promoted search across every vertical — and the revenue they earn.</p>
        </div>
      </div>

      <!-- KPIs with sparklines -->
      <div class="bf-kpis mb-3">
        <?php
        $k = [
          ['Ad Revenue (MTD)','KES 2.8M','bi-cash-coin','#7c3aed','#f5f0ff','+34%','up',[0.6,0.9,1.2,1.6,2.0,2.3,2.6,2.8]],
          ['Impressions','8.4M','bi-eye','#1e40af','#eff6ff','+12%','up',[5.1,5.9,6.4,6.9,7.4,7.9,8.2,8.4]],
          ['Clicks','226K','bi-cursor','#1e3a5f','#eaf0f6','+9%','up',[150,165,178,190,202,212,220,226]],
          ['Avg CTR','2.7%','bi-percent','#9a7d27','#fdf6e3','+0.2','up',[2.1,2.2,2.3,2.4,2.5,2.6,2.65,2.7]],
          ['eCPM','KES 333','bi-graph-up','#166534','#f0fdf4','+6%','up',[280,292,300,308,316,324,329,333]],
          ['Fill Rate','92%','bi-bullseye','#b45309','#fffbeb','+3%','up',[83,85,86,88,89,90,91,92]],
          ['Active Campaigns','214','bi-megaphone','#7c3aed','#f5f0ff','+22','up',[120,140,155,170,185,198,206,214]],
          ['Advertisers','86','bi-building','#1e40af','#eff6ff','+5','up',[58,62,66,70,74,79,83,86]],
        ];
        foreach ($k as [$l,$v,$ic,$c,$bg,$chg,$dir,$sp]): $spc = $dir==='up'?'#16a34a':($dir==='down'?'#c0392b':'#94a3b8'); ?>
        <div class="bf-kpi">
          <div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div><span class="bf-kpi-chg <?=$dir?>"><?php if($dir==='up'):?><i class="bi bi-arrow-up-short"></i><?php endif;?><?=$chg?></span></div>
          <div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div>
          <div class="bf-kpi-foot"><?= adm_spark($sp,$spc) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 mb-1">
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:12px;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-graph-up"></i> Ad revenue &amp; spend</div><span style="font-size:11px;color:var(--ink-4);">12 months</span></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:200px;"><canvas id="adRev"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:12px;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-pie-chart"></i> Revenue by format</div></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:200px;"><canvas id="adFmt"></canvas></div></div>
          </div>
        </div>
      </div>

      <!-- campaigns -->
      <div class="bf-tbl-wrap mb-3">
        <div class="bf-pf-card-h" style="border-bottom:1px solid var(--line);"><div class="bf-pf-card-t"><i class="bi bi-megaphone"></i> Campaigns</div><a href="#" class="bf-pf-card-edit" style="color:#c0392b;">All campaigns →</a></div>
        <div class="bf-tbl-head">
          <div style="flex:1;">Campaign</div>
          <div style="width:120px;" class="d-none d-lg-block">Format</div>
          <div style="width:110px;" class="d-none d-md-block">Budget / Spent</div>
          <div style="width:80px;" class="d-none d-md-block">Impr.</div>
          <div style="width:60px;" class="d-none d-lg-block">CTR</div>
          <div style="width:80px;" class="d-none d-xl-block">eCPM</div>
          <div style="width:90px;">Status</div>
        </div>
        <?php foreach ($campaigns as [$name,$adv,$fmt,$budget,$spent,$impr,$ctr,$ecpm,$status]): [$sl,$bc]=$st[$status]; ?>
        <div class="bf-tbl-row">
          <div style="flex:1;min-width:0;"><div class="pri"><?= $name ?></div><div style="font-size:11px;color:var(--ink-4);"><?= $adv ?></div></div>
          <div style="width:120px;" class="d-none d-lg-block"><span class="bf-badge" style="background:#f5f0ff;color:#7c3aed;"><?= $fmt ?></span></div>
          <div style="width:110px;font-size:11.5px;" class="d-none d-md-block"><span style="color:#7c3aed;font-weight:800;"><?= $spent ?></span> <span style="color:var(--ink-4);">/ <?= $budget ?></span></div>
          <div style="width:80px;" class="d-none d-md-block"><?= $impr ?></div>
          <div style="width:60px;" class="d-none d-lg-block"><?= $ctr ?></div>
          <div style="width:80px;" class="d-none d-xl-block"><?= $ecpm ?></div>
          <div style="width:90px;"><span class="bf-badge <?= $bc ?>"><?= $sl ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <!-- ad formats / placements -->
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-grid-1x2"></i> Placements &amp; inventory</div></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['Homepage hero banner','Display','94%','KES 680K','#1e40af'],
                ['Search results — top','Promoted Search','88%','KES 1.1M','#9a7d27'],
                ['Category sponsored row','Sponsored Listing','91%','KES 540K','#7c3aed'],
                ['Project feed inline','Sponsored Listing','76%','KES 320K','#7c3aed'],
                ['Pre-roll video','Video','62%','KES 180K','#166534'],
              ] as [$place,$fmt,$fill,$rev,$c]): ?>
              <div class="bf-li">
                <span class="bf-li-ic" style="background:var(--surface);color:<?=$c?>;border:1px solid var(--line);"><i class="bi bi-easel"></i></span>
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;color:var(--ink);"><?=$place?></div><div style="font-size:10.5px;color:var(--ink-4);"><?=$fmt?> · <?=$fill?> fill</div></div>
                <span style="font-size:12px;font-weight:800;color:#166534;"><?=$rev?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <!-- top advertisers -->
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-trophy"></i> Top advertisers</div></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['Bamburi Cement','KES 420K','Construction'],['Equity Bank','KES 360K','Finance'],
                ['JCB Kenya','KES 280K','Equipment'],['Safaricom','KES 240K','Telco'],
                ['CIC Insurance','KES 188K','Insurance'],['Diani Collection','KES 96K','Travel'],
              ] as [$n,$amt,$cat]): ?>
              <div class="bf-li"><span class="bf-li-ic" style="background:#f5f0ff;color:#7c3aed;"><i class="bi bi-building"></i></span><div style="flex:1;min-width:0;"><div style="font-size:12px;font-weight:700;color:var(--ink);"><?=$n?></div><div style="font-size:10px;color:var(--ink-4);"><?=$cat?></div></div><span style="font-size:12px;font-weight:800;color:#166534;"><?=$amt?></span></div>
              <?php endforeach; ?>
            </div>
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
  var purple='#7c3aed', navy='#1e3a5f', gold='#c9a84c', green='#166534', blue='#1e40af', red='#c0392b';
  new Chart(document.getElementById('adRev'), {
    type:'line',
    data:{ labels:['Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May','Jun'], datasets:[
      {label:'Ad revenue (M)', data:[0.6,0.8,1.0,1.2,1.5,1.7,1.9,2.1,2.3,2.5,2.6,2.8], borderColor:purple, backgroundColor:'rgba(124,58,237,.12)', fill:true, tension:.4, borderWidth:2, pointRadius:0},
      {label:'Advertiser spend (M)', data:[0.9,1.1,1.4,1.6,2.0,2.3,2.6,2.9,3.1,3.4,3.6,3.9], borderColor:gold, backgroundColor:'transparent', tension:.4, borderWidth:2, pointRadius:0}
    ]},
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:true,labels:{boxWidth:10,usePointStyle:true}}}, scales:{y:{grid:{color:'#eee'}},x:{grid:{display:false}}} }
  });
  new Chart(document.getElementById('adFmt'), {
    type:'doughnut',
    data:{ labels:['Sponsored Listings','Display & Banner','Promoted Search','Video','Offsite'], datasets:[{ data:[38,26,22,9,5], backgroundColor:[purple,blue,gold,green,navy], borderWidth:0 }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'60%', plugins:{legend:{position:'bottom',labels:{boxWidth:9,boxHeight:9,usePointStyle:true,font:{size:9.5}}}} }
  });
});
</script>
<?php include __DIR__ . '/includes/foot.php'; ?>
