<?php $nav = $nav ?? '';
$navLinks = [
  ['professionals', 'Professionals',  '/pages/marketplace/professionals.php'],
  ['services',      'Services',       '/pages/services/index.php'],
];
?>
<nav class="bf-navbar">
  <div class="bf-navbar-grid container-fluid">

    <!-- Left: Brand (+ dashboard sidebar toggle, mobile only) -->
    <div class="d-flex align-items-center gap-2">
      <button class="bf-side-toggle" id="sidebarToggle" type="button" aria-label="Toggle workspace menu"><i class="bi bi-list"></i></button>
      <a href="/" class="bf-brand text-decoration-none d-flex align-items-center gap-2">
        <i class="bi bi-building-fill-up"></i>bildfie
      </a>
    </div>

    <!-- Centre: Nav links (Services last) -->
    <div class="d-none d-xl-flex align-items-center gap-1">
      <?php foreach ($navLinks as [$key, $label, $href]): ?>
      <a href="<?= $href ?>" class="bf-nav-link <?= $nav === $key ? 'active' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Right cluster (far-right → left): Profile · Dashboard(icon) · Post a Project · Messages · Notifications · To-do -->
    <div class="bf-nav-right">
      <button id="bfBurger" class="d-xl-none" type="button" aria-label="Open menu" style="border:none;background:none;color:var(--ink);font-size:23px;line-height:1;cursor:pointer;padding:2px 6px;"><i class="bi bi-list"></i></button>
      <?php if (is_logged_in()): $u = current_user(); ?>
        <a href="/pages/dashboard/index.php#tasks" class="bf-nav-ic d-none d-lg-inline-flex bf-nr-todo" title="To-do"><i class="bi bi-check2-square"></i></a>
        <a href="/pages/dashboard/notifications.php" class="bf-nav-ic d-none d-lg-inline-flex bf-nr-notif" title="Notifications"><i class="bi bi-bell"></i><span class="bf-nav-ic-dot"></span></a>
        <a href="/pages/dashboard/messages.php" class="bf-nav-ic d-none d-lg-inline-flex bf-nr-msg" title="Messages"><i class="bi bi-chat-dots"></i><span class="bf-nav-ic-cnt">3</span></a>
        <a href="/pages/projects/create.php" class="bf-btn-accent bf-nr-post">Post a Project</a>
        <a href="/pages/dashboard/index.php" class="bf-nav-ic bf-nr-dash" title="Dashboard"><i class="bi bi-grid-1x2-fill"></i></a>

        <!-- Profile (far right): comprehensive account menu -->
        <div class="dropdown bf-nr-acct">
          <button class="d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false"
            style="width:36px;height:36px;border-radius:50%;border:2px solid var(--line);cursor:pointer;padding:0;background:none;overflow:hidden;">
            <img src="<?= htmlspecialchars(user_avatar($u, 72), ENT_QUOTES) ?>" alt="<?= htmlspecialchars($u['name']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0 bf-acct-menu">
            <li>
              <div class="bf-acct-head">
                <img src="<?= htmlspecialchars(user_avatar($u, 96), ENT_QUOTES) ?>" alt="">
                <div style="min-width:0;">
                  <div class="nm"><?= htmlspecialchars($u['name']) ?></div>
                  <div class="em"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </li>
            <li><a class="dropdown-item bf-acct-item" href="/pages/account/wallet.php"><i class="bi bi-wallet2"></i>Wallet</a></li>
            <li><a class="dropdown-item bf-acct-item" href="/pages/account/profile.php"><i class="bi bi-person-circle"></i>Profile</a></li>
            <li><a class="dropdown-item bf-acct-item" href="/pages/account/settings.php"><i class="bi bi-gear"></i>Settings</a></li>
            <li><a class="dropdown-item bf-acct-item" href="/pages/subscriptions/index.php"><i class="bi bi-stars"></i>Subscriptions</a></li>

            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item bf-acct-item danger" href="/api/auth/logout.php"><i class="bi bi-box-arrow-right"></i>Sign out</a></li>
          </ul>
        </div>

      <?php else: ?>
        <a href="/pages/auth/login.php" class="bf-btn-outline bf-nr-dash">Sign In</a>
        <a href="/pages/auth/register.php" class="bf-btn-accent bf-nr-post">Post a Project</a>
      <?php endif; ?>
    </div>

  </div>

  <!-- Mobile slide-down menu -->
  <div id="bfMobileMenu" class="d-xl-none" style="display:none;border-top:1px solid var(--line);background:var(--white);padding:6px 18px 12px;">
    <?php foreach ($navLinks as [$key, $label, $href]): ?>
    <a href="<?= $href ?>" class="bf-nav-link <?= $nav === $key ? 'active' : '' ?>" style="display:block;padding:11px 4px;border-bottom:1px solid var(--line-2);"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
</nav>
<script>
(function(){var b=document.getElementById('bfBurger'),m=document.getElementById('bfMobileMenu');if(b&&m){b.addEventListener('click',function(){m.style.display=(!m.style.display||m.style.display==='none')?'block':'none';});}})();
</script>
