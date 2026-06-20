<?php
// Flash message helper
$_flash = '';
if (!empty($_SESSION['flash'])) {
    $_flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
$_u = is_logged_in() ? current_user() : null;
$_nav = $nav ?? '';
?>
<nav class="bf-navbar">
  <div class="container-fluid d-flex align-items-center gap-3">

    <!-- Brand -->
    <a href="/" class="bf-brand d-flex align-items-center gap-2 text-decoration-none me-2">
      <i class="bi bi-building-fill-up" style="font-size:22px;color:#1e3a5f;"></i>
      <span style="font-weight:800;font-size:1.18rem;color:#0d0d0d;letter-spacing:-.5px;">bildfie</span>
    </a>

    <!-- Desktop nav links -->
    <div class="d-none d-md-flex align-items-center gap-1 flex-grow-1">
      <a href="/pages/professionals/" class="bf-nav-link <?= $_nav==='marketplace'?'active':'' ?>">Find Pros</a>
      <a href="/pages/projects/" class="bf-nav-link <?= $_nav==='projects'?'active':'' ?>">Projects</a>
    </div>

    <!-- Right side -->
    <div class="d-flex align-items-center gap-2 ms-auto">
      <?php if ($_u): ?>
        <a href="/pages/dashboard/" class="bf-nav-link d-none d-md-inline">Dashboard</a>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
            <img src="<?= htmlspecialchars(user_avatar($_u, 36)) ?>"
                 alt="<?= htmlspecialchars($_u['name']) ?>"
                 width="32" height="32"
                 style="border-radius:50%;object-fit:cover;border:2px solid #e8e8e8;">
            <span class="d-none d-md-inline" style="font-size:.85rem;font-weight:600;color:#0d0d0d;"><?= htmlspecialchars(explode(' ', $_u['name'])[0]) ?></span>
            <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:.7rem;color:#6b6b6b;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:180px;">
            <li><a class="dropdown-item" href="/pages/dashboard/"><i class="bi bi-grid me-2 text-muted"></i>Dashboard</a></li>
            <li><a class="dropdown-item" href="/pages/account/"><i class="bi bi-person me-2 text-muted"></i>Profile</a></li>
            <li><a class="dropdown-item" href="/pages/account/settings.php"><i class="bi bi-gear me-2 text-muted"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/pages/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="/pages/auth/login.php" class="bf-btn-outline d-none d-sm-inline-flex">Sign in</a>
        <a href="/pages/auth/register.php" class="bf-btn-dark">Join free</a>
      <?php endif; ?>

      <!-- Mobile hamburger -->
      <button class="btn btn-sm d-md-none border-0 p-1 ms-1" id="mobileNavToggle" type="button"
              aria-label="Menu" style="color:#0d0d0d;">
        <i class="bi bi-list" style="font-size:1.4rem;"></i>
      </button>
    </div>
  </div>

  <!-- Mobile nav collapse -->
  <div id="mobileNavMenu" style="display:none;border-top:1px solid #e8e8e8;padding:12px 20px 16px;">
    <a href="/pages/professionals/" class="bf-nav-link d-block mb-1">Find Pros</a>
    <a href="/pages/projects/" class="bf-nav-link d-block mb-1">Projects</a>
    <?php if ($_u): ?>
      <a href="/pages/dashboard/" class="bf-nav-link d-block mb-1">Dashboard</a>
      <a href="/pages/account/" class="bf-nav-link d-block mb-1">Profile</a>
      <a href="/pages/auth/logout.php" class="d-block text-danger small mt-2">Sign out</a>
    <?php else: ?>
      <div class="d-flex gap-2 mt-2">
        <a href="/pages/auth/login.php" class="bf-btn-outline">Sign in</a>
        <a href="/pages/auth/register.php" class="bf-btn-dark">Join free</a>
      </div>
    <?php endif; ?>
  </div>
</nav>

<?php if ($_flash): ?>
<div class="container-fluid">
  <div class="alert alert-info alert-dismissible fade show mb-0 rounded-0 border-0 border-bottom border-info"
       role="alert" style="font-size:.875rem;">
    <?= htmlspecialchars($_flash) ?>
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<script>
(function(){
  var btn = document.getElementById('mobileNavToggle');
  var menu = document.getElementById('mobileNavMenu');
  if (btn && menu) btn.addEventListener('click', function(){ menu.style.display = menu.style.display === 'none' ? 'block' : 'none'; });
})();
</script>
