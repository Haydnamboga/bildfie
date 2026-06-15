<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/companies.php';
$page_title = 'Companies & Organizations';
$nav = 'companies';
$q = trim($_GET['q'] ?? '');
$industry = trim($_GET['industry'] ?? '');
$companies = company_all($q, $industry);
$industries = array_column(db_all("SELECT DISTINCT industry FROM companies WHERE industry IS NOT NULL AND status<>'suspended' ORDER BY industry"), 'industry');
$total = (int) db_value("SELECT COUNT(*) FROM companies WHERE status<>'suspended'");
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="container" style="padding:30px 0 60px;">

  <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
    <div>
      <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:7px;"><span style="opacity:.4;">—</span> Directory</div>
      <h1 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--ink);margin:0 0 5px;letter-spacing:-.02em;">Companies &amp; organizations</h1>
      <p style="font-size:13px;color:var(--ink-3);margin:0;"><?= number_format($total) ?> firms, institutions &amp; organizations building on bildfie.</p>
    </div>
    <a href="/pages/auth/register.php" class="bf-btn-dark" style="text-decoration:none;font-size:13px;padding:10px 18px;border-radius:9px;background:#1e3a5f;color:#fff;display:inline-flex;align-items:center;gap:7px;"><i class="bi bi-plus-lg"></i> Create company page</a>
  </div>

  <!-- filter -->
  <form method="get" class="d-flex flex-wrap gap-2 mb-4" style="background:var(--white);border:1px solid var(--line);border-radius:12px;padding:10px 12px;">
    <div class="bf-topbar-search" style="flex:1;min-width:220px;display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--line);border-radius:9px;padding:0 12px;height:38px;">
      <i class="bi bi-search" style="color:var(--ink-4);"></i>
      <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search companies, industry or city…" style="border:none;background:none;outline:none;font-size:13px;width:100%;font-family:inherit;color:var(--ink);">
    </div>
    <select name="industry" class="form-select" style="width:auto;font-size:13px;border-color:var(--line);">
      <option value="">All industries</option>
      <?php foreach ($industries as $ind): ?><option value="<?= htmlspecialchars($ind) ?>" <?= $industry===$ind?'selected':'' ?>><?= htmlspecialchars($ind) ?></option><?php endforeach; ?>
    </select>
    <button class="bf-btn-dark" style="border:none;background:#1e3a5f;color:#fff;font-size:13px;padding:0 18px;border-radius:9px;font-weight:700;">Filter</button>
  </form>

  <div class="row g-3">
    <?php if (!$companies): ?>
      <div class="col-12"><div style="text-align:center;color:var(--ink-4);padding:50px;background:var(--white);border:1px solid var(--line);border-radius:14px;">No companies match your search.</div></div>
    <?php else: foreach ($companies as $c): ?>
    <div class="col-sm-6 col-lg-4">
      <div data-href="/pages/companies/view.php?slug=<?= urlencode($c['slug']) ?>" style="background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;height:100%;cursor:pointer;transition:box-shadow .18s,transform .18s;" onmouseover="this.style.boxShadow='0 12px 28px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
        <div style="height:64px;background:linear-gradient(120deg,#1e3a5f,#2a4d78);"></div>
        <div style="padding:0 18px 18px;">
          <img src="<?= htmlspecialchars(company_logo($c), ENT_QUOTES) ?>" style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:3px solid var(--white);background:#fff;margin-top:-30px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
          <div style="display:flex;align-items:center;gap:6px;margin-top:10px;">
            <span style="font-size:15px;font-weight:800;color:var(--ink);"><?= htmlspecialchars($c['name']) ?></span>
            <?php if ((int)$c['is_verified']): ?><i class="bi bi-patch-check-fill" style="color:#1e3a5f;font-size:13px;" title="Verified organization"></i><?php endif; ?>
          </div>
          <div style="font-size:12px;color:var(--ink-3);margin-top:3px;line-height:1.45;"><?= htmlspecialchars($c['tagline'] ?? '') ?></div>
          <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:11px;font-size:11px;color:var(--ink-4);">
            <span><i class="bi bi-buildings" style="font-size:10px;"></i> <?= htmlspecialchars($c['industry'] ?? '—') ?></span>
            <span><i class="bi bi-people" style="font-size:10px;"></i> <?= htmlspecialchars($c['company_size'] ?? '—') ?></span>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:11px;border-top:1px solid var(--line-2);">
            <span style="font-size:11px;color:var(--ink-4);"><i class="bi bi-geo-alt-fill" style="font-size:10px;color:#c0392b;"></i> <?= htmlspecialchars(explode(',', $c['hq_location'] ?? 'Kenya')[0]) ?></span>
            <span style="font-size:11px;font-weight:700;color:#1e3a5f;"><?= (int)$c['emp_count'] ?> on bildfie</span>
          </div>
          <div style="display:flex;gap:8px;margin-top:13px;">
            <a href="/pages/companies/view.php?slug=<?= urlencode($c['slug']) ?>" class="bf-action-outline" style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding:8px 0;font-size:12px;font-weight:700;text-decoration:none;"><i class="bi bi-eye"></i> View</a>
            <button class="bf-follow sm" type="button" data-follow="co:<?= htmlspecialchars($c['slug'], ENT_QUOTES) ?>" style="flex:1;">
              <span class="bf-follow-off"><i class="bi bi-bookmark"></i>Save</span>
              <span class="bf-follow-on"><i class="bi bi-bookmark-check-fill"></i>Saved</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
