<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'AI Hub';
$ap = 'ai_overview';
$topbar_crumb = 'AI Hub';
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">

      <div class="bf-dash-h" style="margin-bottom:14px;">
        <div>
          <div class="bf-eyebrow2" style="color:#7c3aed;"><span>—</span> Intelligence</div>
          <h1 class="bf-dash-title">AI Hub</h1>
          <p class="bf-dash-sub">Every AI service powering bildfie — matching, safety, forecasting, pricing and the assistant.</p>
        </div>
        <button class="bf-topbar-new" style="border:none;cursor:pointer;background:#7c3aed;"><i class="bi bi-plus-lg me-1"></i>New automation</button>
      </div>

      <!-- KPIs -->
      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['AI Requests (30d)','4.8M','bi-cpu','#7c3aed','#f5f0ff','+22%','up'],
          ['Avg Accuracy','96.4%','bi-bullseye','#166534','#f0fdf4','+1.2','up'],
          ['Automations','38','bi-robot','#1e3a5f','#eaf0f6','+5','up'],
          ['Hours Saved (mo)','2,140','bi-clock-history','#1e40af','#eff6ff','+18%','up'],
          ['Fraud Caught','KES 4.2M','bi-shield-check','#c0392b','#fef2f2','312 cases','flat'],
          ['Match Success','83%','bi-diagram-2','#9a7d27','#fdf6e3','+6','up'],
          ['Assistant Chats','61K','bi-chat-dots','#7c3aed','#f5f0ff','+30%','up'],
          ['AI Spend (mo)','KES 1.1M','bi-currency-dollar','#b45309','#fffbeb','-4%','up'],
        ] as [$l,$v,$ic,$c,$bg,$chg,$dir]): ?>
        <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div><span class="bf-kpi-chg <?=$dir?>"><?=$chg?></span></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 mb-1">
        <!-- AI services -->
        <div class="col-lg-8">
          <div style="font-size:13px;font-weight:800;color:var(--ink);margin:2px 0 10px;">AI services</div>
          <div class="row g-2">
            <?php foreach ([
              ['Smart Matching','bi-diagram-2','#7c3aed','#f5f0ff','Matches pros, suppliers & projects','Live','96%','1.9M calls/mo'],
              ['Fraud & Risk','bi-shield-exclamation','#c0392b','#fef2f2','Scores accounts, bids & payments','Live','98%','842K calls/mo'],
              ['Content Moderation','bi-eye','#1e40af','#eff6ff','Reviews listings, photos & reviews','Live','94%','610K calls/mo'],
              ['Demand Forecasting','bi-graph-up-arrow','#166534','#f0fdf4','Predicts demand & stock by region','Live','91%','Daily runs'],
              ['Dynamic Pricing','bi-tags','#9a7d27','#fdf6e3','Optimises hire & listing prices','Beta','89%','Hourly runs'],
              ['AI Assistant (Copilot)','bi-robot','#7c3aed','#f5f0ff','In-app help for staff & users','Live','—','61K chats/mo'],
            ] as [$n,$ic,$c,$bg,$desc,$status,$acc,$usage]): ?>
            <div class="col-md-6">
              <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
                <div class="bf-pf-card-b">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="adm-vert-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" <?= $status!=='Off'?'checked':'' ?> style="cursor:pointer;"></div>
                  </div>
                  <div style="font-size:13.5px;font-weight:800;color:var(--ink);margin-top:10px;"><?=$n?> <span class="bf-badge <?= $status==='Live'?'green':($status==='Beta'?'amber':'grey') ?>" style="font-size:8px;"><?=$status?></span></div>
                  <div style="font-size:11.5px;color:var(--ink-3);margin-top:3px;"><?=$desc?></div>
                  <div style="display:flex;gap:18px;margin-top:12px;padding-top:10px;border-top:1px solid var(--line-2);">
                    <div><div style="font-size:14px;font-weight:900;color:var(--ink);"><?=$acc?></div><div style="font-size:9.5px;color:var(--ink-4);">Accuracy</div></div>
                    <div><div style="font-size:14px;font-weight:900;color:var(--ink);"><?=$usage?></div><div style="font-size:9.5px;color:var(--ink-4);">Usage</div></div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- usage chart + activity -->
        <div class="col-lg-4">
          <div class="bf-pf-card"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-graph-up"></i> AI usage (30 days)</div></div>
            <div class="bf-pf-card-b"><div class="adm-chart" style="height:170px;"><canvas id="aiChart"></canvas></div></div>
          </div>
          <div class="bf-pf-card" style="margin-bottom:0;"><div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-activity"></i> AI activity</div></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ([
                ['bi-diagram-2','#7c3aed','#f5f0ff','Re-trained matching model · +2.1% accuracy','2h'],
                ['bi-shield-exclamation','#c0392b','#fef2f2','Flagged 4 accounts for manual review','3h'],
                ['bi-tags','#9a7d27','#fdf6e3','Pricing model adjusted 1,204 listings','5h'],
                ['bi-robot','#7c3aed','#f5f0ff','Assistant resolved 318 chats autonomously','Today'],
              ] as [$ic,$col,$bg,$t,$w]): ?>
              <div class="bf-li"><span class="bf-li-ic" style="background:<?=$bg?>;color:<?=$col?>;"><i class="bi <?=$ic?>"></i></span><div style="flex:1;min-width:0;font-size:11.5px;color:var(--ink-2);"><?=$t?></div><span style="font-size:10px;color:var(--ink-4);"><?=$w?></span></div>
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
  new Chart(document.getElementById('aiChart'), {
    type:'line',
    data:{ labels:Array.from({length:14},(_,i)=>'D'+(i+1)), datasets:[{ data:[120,135,128,150,162,158,175,182,190,205,198,220,235,248], borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.12)', fill:true, tension:.4, borderWidth:2, pointRadius:0 }]},
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{grid:{color:'#eee'},ticks:{callback:v=>v+'K'}},x:{grid:{display:false},ticks:{maxTicksLimit:7}}} }
  });
});
</script>
<?php include __DIR__ . '/includes/foot.php'; ?>
