<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Team'; $sp = 'team';
$topbar_crumb = 'Delivery';
$topbar_action = '<button class="bf-topbar-new" data-engage="hire" data-ctx="a new team member" style="border:none;cursor:pointer;"><i class="bi bi-person-plus me-1"></i>Invite member</button>';

$members = [
  ['John Mwangi','Structural Engineer','men/32',true,3,'4.9',['Structural','ETABS','RC Design']],
  ['Amina Osei','Architect & Lead','women/44',true,4,'4.9',['Architecture','Revit','Concept']],
  ['Peter Njoroge','Site Foreman','men/76',false,2,'4.7',['Supervision','Safety','Logistics']],
  ['Grace Wanjiku','Quantity Surveyor','women/25',true,5,'4.8',['BOQ','Cost Control','NCA']],
  ['Samuel Otieno','MEP Engineer','men/54',true,2,'4.6',['HVAC','Electrical','Plumbing']],
  ['Zara Abdi','Interior Designer','women/62',false,1,'4.5',['FF&E','SketchUp','Finishes']],
  ['Kevin Ochieng','Electrical Engineer','men/67',true,3,'4.6',['HV/LV','Solar','EPRA']],
  ['Miriam Ndungu','Geotechnical Eng.','women/33',false,1,'4.7',['Soil','Piling','Testing']],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Delivery</div>
        <h1 class="bf-dash-title">Team</h1>
        <p class="bf-dash-sub">The people delivering your projects — each with an independent profile, roles and assignments.</p>
      </div></div>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Team members','12','bi-people','#1e3a5f','#eaf0f6'],
          ['Online now','7','bi-broadcast','#166534','#f0fdf4'],
          ['Open roles','3','bi-person-plus','#b45309','#fffbeb'],
          ['Projects covered','7','bi-kanban','#1e40af','#eff6ff'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?= $bg ?>;color:<?= $c ?>;"><i class="bi <?= $ic ?>"></i></div></div><div class="bf-kpi-v"><?= $v ?></div><div class="bf-kpi-l"><?= $l ?></div></div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <?php foreach ($members as [$n,$role,$ph,$on,$projects,$rt,$skills]): ?>
        <div class="col-sm-6 col-xl-3">
          <div class="bf-pf-card" style="margin-bottom:0;height:100%;">
            <div class="bf-pf-card-b" style="text-align:center;">
              <div style="position:relative;display:inline-block;margin-bottom:10px;">
                <img src="https://randomuser.me/api/portraits/<?= $ph ?>.jpg" alt="<?= $n ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--line);">
                <span style="position:absolute;bottom:2px;right:2px;width:13px;height:13px;border-radius:50%;background:<?= $on?'#22c55e':'#d8d8d4' ?>;border:2.5px solid var(--white);"></span>
              </div>
              <div style="font-size:13.5px;font-weight:800;color:var(--ink);"><?= $n ?></div>
              <div style="font-size:11.5px;color:var(--ink-3);margin-bottom:8px;"><?= $role ?></div>
              <div style="display:flex;justify-content:center;gap:14px;font-size:11px;color:var(--ink-4);margin-bottom:10px;">
                <span><i class="bi bi-kanban" style="color:#1e3a5f;"></i> <?= $projects ?> projects</span>
                <span><i class="bi bi-star-fill" style="color:#f59e0b;"></i> <?= $rt ?></span>
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:5px;justify-content:center;margin-bottom:12px;min-height:24px;">
                <?php foreach ($skills as $sk): ?><span class="bf-badge grey" style="font-weight:600;"><?= $sk ?></span><?php endforeach; ?>
              </div>
              <div style="display:flex;gap:8px;">
                <a href="/pages/dashboard/messages.php" class="bf-btn-ghost" style="flex:1;text-decoration:none;text-align:center;font-size:11.5px;padding:8px 0;"><i class="bi bi-chat-dots"></i> Message</a>
                <a href="/pages/marketplace/professional.php?name=<?= urlencode($n) ?>&role=<?= urlencode($role) ?>&photo=<?= urlencode('https://randomuser.me/api/portraits/'.$ph.'.jpg') ?>&rating=<?= $rt ?>" class="bf-btn-navy" style="flex:1;text-decoration:none;text-align:center;font-size:11.5px;padding:8px 0;">Profile</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
