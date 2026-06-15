<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Notifications'; $sp = 'notifications';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">

    <?php $topbar_action='<button class="bf-btn-ghost"><i class="bi bi-check2-all me-1"></i>Mark all read</button>'; include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:28px;">

      <div class="mb-4">
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Activity</div>
        <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">Notifications</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Updates across your projects, invoices and team.</p>
      </div>

      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;max-width:760px;">
        <?php foreach ([
          ['bi-person-check',        'success', '<strong>John Mwangi</strong> accepted your invite to Westlands Office Block.', '2 hours ago', true,  '#166534','#f0fdf4'],
          ['bi-file-earmark-check',  'primary', 'Invoice <strong>#INV-042</strong> has been approved · KES 480,000 pending payment.', '5 hours ago', true, '#1e3a5f','#eaf0f6'],
          ['bi-chat-dots',           'info',    'New message from <strong>Amina Osei</strong> regarding project drawings.', 'Yesterday', true, '#1e40af','#eff6ff'],
          ['bi-exclamation-triangle','warning', 'Task overdue: <strong>Structural drawings</strong> — Westlands Office Block.', 'Yesterday', false, '#b45309','#fffbeb'],
          ['bi-check-circle',        'success', 'Milestone completed: <strong>Foundation Phase</strong> — Riverside Apartments.', '2 days ago', false, '#166534','#f0fdf4'],
          ['bi-bell',                'muted',   'Your professional profile is under review. Expected: 24–48 hours.', '3 days ago', false, '#6b6b6b','#f8f8f6'],
        ] as $i => [$ico,$c,$txt,$time,$unread,$iconCol,$iconBg]): ?>
        <div class="d-flex gap-3 align-items-start" style="padding:16px 20px;<?= $i>0?'border-top:1px solid var(--line-2);':'' ?><?= $unread?'background:#fcfdfe;':'' ?>">
          <span style="width:36px;height:36px;border-radius:10px;background:<?=$iconBg?>;color:<?=$iconCol?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;"><i class="bi <?=$ico?>"></i></span>
          <div class="flex-grow-1" style="min-width:0;">
            <div style="font-size:13px;color:var(--ink-2);line-height:1.5;<?= $unread?'font-weight:600;color:var(--ink);':'' ?>"><?=$txt?></div>
            <div style="font-size:11px;color:var(--ink-4);margin-top:3px;"><?=$time?></div>
          </div>
          <?php if ($unread): ?>
          <span style="font-size:9px;font-weight:800;letter-spacing:.05em;background:#fef2f2;color:#c0392b;padding:3px 9px;border-radius:5px;flex-shrink:0;">NEW</span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
