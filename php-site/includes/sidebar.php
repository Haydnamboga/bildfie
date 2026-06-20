<?php
$_su = current_user();
$sp  = $sp ?? '';
$_avatar_url = user_avatar($_su, 40);

// Sidebar nav items: [key, icon, label, href]
$_sidebar_items = [
  ['section', 'Overview'],
  ['dashboard',  'bi-grid-1x2',         'Dashboard',         '/pages/dashboard/'],
  ['section', 'Projects'],
  ['projects',   'bi-kanban',            'My Projects',       '/pages/projects/'],
  ['create_project','bi-plus-circle',    'Post Project',      '/pages/projects/create.php'],
  ['bids',       'bi-inbox',             'Bids Received',     '/pages/bids/'],
  ['section', 'Marketplace'],
  ['marketplace','bi-search',            'Find Professionals','/pages/professionals/'],
  ['messages',   'bi-chat-dots',         'Messages',          '/pages/messages/'],
  ['section', 'Finance'],
  ['invoices',   'bi-receipt',           'Invoices',          '/pages/dashboard/invoices.php'],
  ['estimates',  'bi-calculator',        'Estimates',         '/pages/sales/estimates.php'],
  ['proposals',  'bi-file-earmark-text', 'Proposals',         '/pages/sales/proposals.php'],
  ['contracts',  'bi-file-earmark-ruled','Contracts',         '/pages/sales/contracts.php'],
  ['section', 'Account'],
  ['profile',    'bi-person',            'Profile',           '/pages/account/'],
  ['settings',   'bi-gear',              'Settings',          '/pages/account/settings.php'],
];
?>
<aside class="bf-sidebar" id="bfSidebar">

  <!-- Brand -->
  <a href="/pages/dashboard/" class="bf-brand d-flex align-items-center gap-2 text-decoration-none"
     style="padding:0 16px;height:var(--topbar-h);border-bottom:1px solid var(--line);flex-shrink:0;">
    <i class="bi bi-building-fill-up" style="font-size:20px;color:#1e3a5f;"></i>
    <span style="font-weight:800;font-size:1.05rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
  </a>

  <!-- Nav -->
  <nav class="bf-sidebar-nav" style="flex:1;overflow-y:auto;padding:12px 8px;">
    <?php foreach ($_sidebar_items as $item):
      if ($item[0] === 'section'): ?>
        <div class="bf-eyebrow2" style="padding:14px 10px 4px;font-size:.67rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-4);"><?= htmlspecialchars($item[1]) ?></div>
      <?php continue; endif;
      [$key, $icon, $label, $href] = $item;
      $active = ($sp === $key); ?>
      <a href="<?= htmlspecialchars($href) ?>"
         class="bf-li d-flex align-items-center gap-2 text-decoration-none mb-0"
         style="border-radius:8px;padding:8px 10px;margin-bottom:1px;font-size:.875rem;font-weight:<?= $active ? '600':'500' ?>;
                color:<?= $active ? '#1e3a5f':'#3a3a3a' ?>;background:<?= $active ? '#eaf0f6':'transparent' ?>;
                transition:background .15s,color .15s;">
        <i class="bi <?= $icon ?>" style="font-size:1rem;width:18px;text-align:center;color:<?= $active ? '#1e3a5f':'#6b6b6b' ?>;"></i>
        <?= htmlspecialchars($label) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- User area -->
  <div style="border-top:1px solid var(--line);padding:12px 12px;display:flex;align-items:center;gap:10px;flex-shrink:0;">
    <img src="<?= htmlspecialchars($_avatar_url) ?>"
         alt="<?= htmlspecialchars($_su['name']) ?>"
         width="32" height="32"
         style="border-radius:50%;object-fit:cover;border:2px solid var(--line);flex-shrink:0;">
    <div style="flex:1;min-width:0;">
      <div style="font-size:.8rem;font-weight:600;color:#0d0d0d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        <?= htmlspecialchars($_su['name']) ?>
      </div>
      <div style="font-size:.72rem;color:var(--ink-3);">Member</div>
    </div>
    <a href="/pages/auth/logout.php" title="Sign out" style="color:var(--ink-4);font-size:1rem;flex-shrink:0;">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>

</aside>

<!-- Sidebar overlay for mobile -->
<div class="bf-sidebar-overlay d-lg-none" id="bfSidebarOverlay"
     style="display:none!important;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:299;"
     onclick="document.getElementById('bfSidebar').classList.remove('open');this.style.display='none';"></div>
