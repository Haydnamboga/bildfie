<?php
$admin = current_admin();
$tt = $topbar_title ?? preg_replace('/ · bildfie Admin$/', '', $page_title ?? 'Dashboard');
?>
<div class="adm-topbar">
  <button class="adm-burger" id="admBurger"><i class="bi bi-list"></i></button>
  <a href="/admin/index.php" class="d-lg-none text-decoration-none d-inline-flex align-items-center gap-1" style="font-weight:800;font-size:15px;letter-spacing:-.03em;color:var(--ink);" title="Admin dashboard"><i class="bi bi-building-fill-up" style="color:#1e3a5f;"></i>bildfie</a>
  <div class="d-none d-md-block">
    <span style="font-weight:800;color:var(--ink);font-size:14.5px;"><?= htmlspecialchars($tt) ?></span>
    <?php if (!empty($topbar_crumb)): ?><span style="color:var(--ink-4);font-size:12px;">&nbsp;/&nbsp;<?= htmlspecialchars($topbar_crumb) ?></span><?php endif; ?>
  </div>

  <form class="adm-search mx-auto" action="#" onsubmit="return false;">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search staff, users, projects, invoices, tickets…">
  </form>

  <div class="d-flex align-items-center gap-2 ms-auto">
    <span class="adm-role d-none d-sm-inline"><i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($admin['role'] ?? 'Super Admin') ?></span>
    <a href="/admin/index.php" class="adm-ic" title="Admin dashboard"><i class="bi bi-grid-1x2-fill"></i></a>
    <a href="/" target="_blank" rel="noopener" class="adm-ic" title="View public site (frontend)"><i class="bi bi-box-arrow-up-right"></i></a>
    <a href="#" class="adm-ic" title="Schedule &amp; calendar"><i class="bi bi-calendar3"></i></a>
    <a href="#" class="adm-ic" title="Alerts"><i class="bi bi-bell"></i><span class="dot"></span></a>
    <div class="dropdown">
      <button class="adm-av" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($admin['photo'] ?? 'https://randomuser.me/api/portraits/women/65.jpg') ?>" alt="">
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size:13px;border-radius:12px;padding:6px;min-width:210px;margin-top:8px;">
        <li><div style="padding:10px 14px 8px;border-bottom:1px solid var(--line);margin-bottom:4px;">
          <div style="font-weight:700;color:var(--ink);"><?= htmlspecialchars($admin['name'] ?? 'Owner') ?></div>
          <div style="font-size:11px;color:var(--ink-3);"><?= htmlspecialchars($admin['email'] ?? '') ?></div>
          <div style="margin-top:6px;"><span class="bf-badge red" style="font-size:9px;"><i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($admin['level'] ?? 'L0') ?> · Full access</span></div>
        </div></li>
        <li><a class="dropdown-item rounded-2 py-2" href="/admin/account.php"><i class="bi bi-person-circle me-2 text-muted"></i>My Account</a></li>
        <li><a class="dropdown-item rounded-2 py-2" href="#"><i class="bi bi-gear me-2 text-muted"></i>Console Settings</a></li>
        <li><a class="dropdown-item rounded-2 py-2" href="/" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-muted"></i>View public site</a></li>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item rounded-2 py-2 text-danger" href="/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
      </ul>
    </div>
  </div>
</div>
