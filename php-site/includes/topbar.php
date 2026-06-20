<?php
$_tu = current_user();
$_topbar_title = $topbar_title ?? 'Dashboard';
?>
<div class="bf-topbar d-flex align-items-center gap-3" id="bfTopbar"
     style="height:var(--topbar-h);border-bottom:1px solid var(--line);background:#fff;
            padding:0 20px;position:sticky;top:0;z-index:200;flex-shrink:0;">

  <!-- Hamburger (mobile) -->
  <button id="sidebarToggle" type="button"
          class="btn btn-sm border-0 p-1 d-lg-none"
          style="color:#3a3a3a;"
          aria-label="Toggle sidebar">
    <i class="bi bi-list" style="font-size:1.4rem;"></i>
  </button>

  <!-- Page title -->
  <h1 class="bf-dash-title mb-0 flex-grow-1"
      style="font-size:1.05rem;font-weight:700;color:#0d0d0d;letter-spacing:-.3px;">
    <?= htmlspecialchars($_topbar_title) ?>
  </h1>

  <!-- Right: icons + avatar -->
  <div class="d-flex align-items-center gap-2">
    <!-- Notifications -->
    <button class="btn btn-sm border-0 p-1 position-relative" style="color:#3a3a3a;" title="Notifications">
      <i class="bi bi-bell" style="font-size:1.1rem;"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
            style="background:#c0392b;font-size:.6rem;padding:2px 5px;">3</span>
    </button>

    <!-- Messages -->
    <a href="/pages/messages/" class="btn btn-sm border-0 p-1" style="color:#3a3a3a;" title="Messages">
      <i class="bi bi-chat-dots" style="font-size:1.1rem;"></i>
    </a>

    <!-- Avatar -->
    <a href="/pages/account/" class="d-flex align-items-center gap-2 text-decoration-none ms-1">
      <img src="<?= htmlspecialchars(user_avatar($_tu, 36)) ?>"
           alt="<?= htmlspecialchars($_tu['name']) ?>"
           width="32" height="32"
           style="border-radius:50%;object-fit:cover;border:2px solid #e8e8e8;">
    </a>
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('sidebarToggle');
  var sb  = document.getElementById('bfSidebar');
  var ov  = document.getElementById('bfSidebarOverlay');
  if (btn && sb) {
    btn.addEventListener('click', function(){
      sb.classList.toggle('open');
      if (ov) ov.style.display = sb.classList.contains('open') ? 'block' : 'none';
    });
  }
})();
</script>
