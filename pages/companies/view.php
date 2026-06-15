<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/companies.php';

$key = $_GET['slug'] ?? ($_GET['id'] ?? '');
$c = $key !== '' ? company_find($key) : null;

// member claims a position here → pending until the org affirms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $c && is_logged_in() && ($_POST['action'] ?? '') === 'claim') {
    $r = company_claim((int)$c['id'], (int)current_user()['id'], $_POST['position'] ?? '', $_POST['employment_type'] ?? 'Full-time');
    $_SESSION['co_flash'] = $r['ok']
        ? ['ok', 'Claim submitted — ' . htmlspecialchars($c['name']) . ' will review and affirm your role.']
        : ['err', $r['error']];
    header('Location: /pages/companies/view.php?slug=' . urlencode($c['slug'])); exit;
}

// member applies directly to an advertised vacancy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $c && is_logged_in() && ($_POST['action'] ?? '') === 'apply') {
    $u = current_user();
    $r = vacancy_apply((int)($_POST['vacancy_id'] ?? 0), (int)$u['id'], $u['name'], $u['email'] ?? null, $_POST['message'] ?? '');
    $_SESSION['co_flash'] = $r['ok']
        ? ['ok', 'Application sent — ' . htmlspecialchars($c['name']) . ' can now review it. Good luck!']
        : ['err', $r['error']];
    header('Location: /pages/companies/view.php?slug=' . urlencode($c['slug'])); exit;
}

if (!$c) {
    http_response_code(404);
    $page_title = 'Company not found'; $nav = 'companies';
    include __DIR__ . '/../../includes/head.php'; include __DIR__ . '/../../includes/navbar.php';
    echo '<div class="container" style="padding:80px 0;text-align:center;color:var(--ink-3);"><i class="bi bi-building-x" style="font-size:34px;color:var(--ink-4);"></i><p style="margin-top:14px;">That company doesn\'t exist. <a href="/pages/companies/index.php" style="color:#1e3a5f;">Browse companies</a></p></div>';
    include __DIR__ . '/../../includes/footer.php'; include __DIR__ . '/../../includes/scripts.php'; exit;
}

$page_title = $c['name'];
$nav = 'companies';
$cid = (int) $c['id'];
$specs = company_specialties($cid);
$people = company_people($cid);
$empCount = company_employee_count($cid);
$flash = $_SESSION['co_flash'] ?? null; unset($_SESSION['co_flash']);
$myMembership = is_logged_in() ? db_one("SELECT status,position FROM company_members WHERE company_id=? AND user_id=? ORDER BY id DESC LIMIT 1", [$cid, (int)current_user()['id']]) : null;
function emp_avatar($p) { return !empty($p['photo_url']) ? $p['photo_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($p['name']) . '&background=eaf0f6&color=1e3a5f&size=80&bold=true'; }
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="container" style="padding:22px 0 60px;">

  <?php if ($flash): ?>
  <div style="border-radius:11px;padding:12px 16px;font-size:13px;margin-bottom:14px;<?= $flash[0]==='ok' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;' ?>"><i class="bi <?= $flash[0]==='ok'?'bi-check-circle-fill':'bi-exclamation-triangle-fill' ?> me-1"></i><?= $flash[1] ?></div>
  <?php endif; ?>

  <!-- header card -->
  <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;overflow:hidden;">
    <div style="height:150px;background:<?= !empty($c['cover_url']) ? 'url(\''.htmlspecialchars($c['cover_url']).'\') center/cover' : 'linear-gradient(120deg,#1e3a5f,#2a4d78)' ?>;"></div>
    <div style="padding:0 24px 22px;">
      <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:18px;">
        <img src="<?= htmlspecialchars(company_logo($c), ENT_QUOTES) ?>" style="width:96px;height:96px;border-radius:16px;object-fit:cover;border:4px solid var(--white);background:#fff;margin-top:-48px;box-shadow:0 4px 14px rgba(0,0,0,.1);">
        <div style="flex:1;min-width:200px;padding-top:12px;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <h1 style="font-size:23px;font-weight:800;color:var(--ink);margin:0;letter-spacing:-.02em;"><?= htmlspecialchars($c['name']) ?></h1>
            <?php if ((int)$c['is_verified']): ?><span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:800;background:#eaf0f6;color:#1e3a5f;padding:3px 9px;border-radius:20px;"><i class="bi bi-patch-check-fill"></i> Verified</span><?php endif; ?>
          </div>
          <div style="font-size:13px;color:var(--ink-3);margin-top:3px;"><?= htmlspecialchars($c['tagline'] ?? '') ?></div>
          <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:8px;font-size:12px;color:var(--ink-4);">
            <span><i class="bi bi-buildings"></i> <?= htmlspecialchars($c['industry'] ?? '—') ?></span>
            <span><i class="bi bi-geo-alt-fill" style="color:#c0392b;"></i> <?= htmlspecialchars($c['hq_location'] ?? '—') ?></span>
            <span><i class="bi bi-people"></i> <?= htmlspecialchars($c['company_size'] ?? '—') ?> employees</span>
            <span><i class="bi bi-person-badge"></i> <?= $empCount ?> on bildfie</span>
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;padding-top:12px;flex-wrap:wrap;">
          <?php if (!empty($c['website'])): ?><a href="https://<?= htmlspecialchars(preg_replace('#^https?://#','',$c['website'])) ?>" target="_blank" rel="noopener" style="text-decoration:none;font-size:12.5px;font-weight:700;border:1.5px solid var(--line);color:#1e3a5f;padding:8px 15px;border-radius:9px;"><i class="bi bi-globe me-1"></i>Website</a><?php endif; ?>
          <button class="bf-follow" type="button" data-follow="co:<?= htmlspecialchars($c['slug'], ENT_QUOTES) ?>">
            <span class="bf-follow-off"><i class="bi bi-plus-lg"></i>Follow</span>
            <span class="bf-follow-on"><i class="bi bi-check-lg"></i>Following</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3" style="margin-top:4px;">
    <!-- main -->
    <div class="col-lg-8">
      <!-- About -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:14px;">
        <div style="font-size:14px;font-weight:800;color:var(--ink);margin-bottom:10px;"><i class="bi bi-info-circle me-1" style="color:#1e3a5f;"></i> About</div>
        <p style="font-size:13px;color:var(--ink-2);line-height:1.7;margin:0;"><?= nl2br(htmlspecialchars($c['about'] ?? '')) ?></p>
        <?php if ($specs): ?>
        <div style="margin-top:16px;">
          <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-4);margin-bottom:8px;">Specialties</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">
            <?php foreach ($specs as $s): ?><span style="font-size:11px;font-weight:600;background:var(--surface);border:1px solid var(--line);color:var(--ink-2);padding:4px 11px;border-radius:20px;"><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- People (affirmed employees) -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
          <div style="font-size:14px;font-weight:800;color:var(--ink);"><i class="bi bi-people-fill me-1" style="color:#1e3a5f;"></i> People at <?= htmlspecialchars($c['name']) ?></div>
          <span style="font-size:11px;color:var(--ink-4);"><?= count($people) ?> affirmed</span>
        </div>
        <?php if (!$people): ?>
          <div style="font-size:12.5px;color:var(--ink-4);padding:8px 0;">No affirmed employees yet.</div>
        <?php else: ?>
        <div class="row g-2">
          <?php foreach ($people as $pp):
            $emHref = $pp['provider_id'] ? '/pages/marketplace/professional.php?id=' . (int)$pp['provider_id'] : null;
            $title = $pp['position'] ?: ($pp['prof_title'] ?: $pp['trade'] ?: 'Team member');
          ?>
          <div class="col-md-6">
            <<?= $emHref ? 'a href="'.$emHref.'"' : 'div' ?> style="display:flex;align-items:center;gap:11px;background:var(--surface);border:1px solid var(--line-2);border-radius:11px;padding:11px 13px;text-decoration:none;height:100%;">
              <img src="<?= htmlspecialchars(emp_avatar($pp), ENT_QUOTES) ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;">
              <div style="min-width:0;flex:1;">
                <div style="font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($pp['name']) ?></div>
                <div style="font-size:11px;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($title) ?></div>
              </div>
              <span style="flex-shrink:0;display:inline-flex;align-items:center;gap:3px;font-size:8.5px;font-weight:700;background:#f0fdf4;color:#166534;padding:3px 7px;border-radius:5px;" title="Employment affirmed by <?= htmlspecialchars($c['name'], ENT_QUOTES) ?>"><i class="bi bi-patch-check-fill" style="font-size:8px;"></i> Affirmed</span>
            </<?= $emHref ? 'a' : 'div' ?>>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- side -->
    <div class="col-lg-4">
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;margin-bottom:14px;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:12px;">Overview</div>
        <?php foreach ([
          ['Industry', $c['industry'], 'bi-buildings'],
          ['Company size', $c['company_size'] ? $c['company_size'] . ' employees' : null, 'bi-people'],
          ['Type', $c['company_type'], 'bi-diagram-3'],
          ['Founded', $c['founded_year'], 'bi-calendar3'],
          ['Headquarters', $c['hq_location'], 'bi-geo-alt'],
          ['Website', $c['website'], 'bi-globe'],
        ] as [$k,$v,$ic]): if (!$v) continue; ?>
        <div style="display:flex;gap:10px;padding:8px 0;border-top:1px solid var(--line-2);">
          <i class="bi <?= $ic ?>" style="color:#1e3a5f;font-size:14px;width:18px;flex-shrink:0;"></i>
          <div style="min-width:0;"><div style="font-size:10px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.04em;"><?= $k ?></div><div style="font-size:12.5px;color:var(--ink-2);font-weight:600;word-break:break-word;"><?= htmlspecialchars($v) ?></div></div>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;gap:10px;padding:8px 0;border-top:1px solid var(--line-2);">
          <i class="bi bi-rss" style="color:#1e3a5f;font-size:14px;width:18px;flex-shrink:0;"></i>
          <div><div style="font-size:10px;color:var(--ink-4);text-transform:uppercase;letter-spacing:.04em;">Followers</div><div style="font-size:12.5px;color:var(--ink-2);font-weight:600;"><?= number_format((int)$c['followers']) ?></div></div>
        </div>
      </div>

      <!-- Open positions (vacancies + direct apply) -->
      <?php $vacancies = company_vacancies($cid); if ($vacancies):
        $myApplied = is_logged_in() ? user_applied_vacancy_ids((int)current_user()['id']) : []; ?>
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <div style="font-size:13px;font-weight:800;color:var(--ink);"><i class="bi bi-briefcase-fill me-1" style="color:#1e3a5f;"></i> Open positions</div>
          <span style="font-size:10px;font-weight:800;background:#eaf0f6;color:#1e3a5f;padding:2px 8px;border-radius:20px;"><?= count($vacancies) ?> hiring</span>
        </div>
        <?php foreach ($vacancies as $v): $applied = in_array((int)$v['id'], $myApplied, true); ?>
        <div style="padding:11px 0;border-top:1px solid var(--line-2);">
          <div style="font-size:13px;font-weight:700;color:var(--ink);line-height:1.3;"><?= htmlspecialchars($v['title']) ?></div>
          <div style="display:flex;flex-wrap:wrap;gap:8px 12px;margin-top:4px;font-size:11px;color:var(--ink-4);">
            <span><i class="bi bi-clock" style="font-size:10px;"></i> <?= htmlspecialchars($v['employment_type']) ?></span>
            <?php if ($v['location']): ?><span><i class="bi bi-geo-alt-fill" style="font-size:10px;color:#c0392b;"></i> <?= htmlspecialchars($v['location']) ?></span><?php endif; ?>
          </div>
          <?php if ($v['salary_display']): ?><div style="font-size:11.5px;color:#166534;font-weight:700;margin-top:4px;"><i class="bi bi-cash-coin"></i> <?= htmlspecialchars($v['salary_display']) ?></div><?php endif; ?>
          <div style="margin-top:9px;">
            <?php if (!is_logged_in()): ?>
              <a href="/pages/auth/login.php?redirect=<?= urlencode('/pages/companies/view.php?slug='.$c['slug']) ?>" style="display:inline-block;font-size:11.5px;font-weight:700;background:#1e3a5f;color:#fff;padding:7px 16px;border-radius:8px;text-decoration:none;"><i class="bi bi-box-arrow-in-right me-1"></i>Sign in to apply</a>
            <?php elseif ($applied): ?>
              <span style="display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;color:#166534;background:#f0fdf4;border:1px solid #bbf7d0;padding:7px 14px;border-radius:8px;"><i class="bi bi-check-circle-fill"></i> Application sent</span>
            <?php else: ?>
              <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="apply">
                <input type="hidden" name="vacancy_id" value="<?= (int)$v['id'] ?>">
                <button type="submit" style="font-size:11.5px;font-weight:700;background:#c0392b;color:#fff;border:none;padding:7px 18px;border-radius:8px;cursor:pointer;"><i class="bi bi-send-fill me-1"></i>Apply now</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="font-size:10.5px;color:var(--ink-4);margin-top:11px;padding-top:10px;border-top:1px solid var(--line-2);"><i class="bi bi-shield-check" style="color:#16a34a;"></i> Apply directly — your bildfie profile is shared with <?= htmlspecialchars($c['name']) ?>.</div>
      </div>
      <?php endif; ?>

      <!-- claim "I work here" -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:4px;"><i class="bi bi-person-check me-1" style="color:#1e3a5f;"></i> Work here?</div>
        <?php if (!is_logged_in()): ?>
          <p style="font-size:12px;color:var(--ink-3);margin:6px 0 10px;">Sign in to claim your position — <?= htmlspecialchars($c['name']) ?> will affirm it.</p>
          <a href="/pages/auth/login.php?redirect=<?= urlencode('/pages/companies/view.php?slug='.$c['slug']) ?>" style="text-decoration:none;font-size:12.5px;font-weight:700;background:#1e3a5f;color:#fff;padding:9px 16px;border-radius:9px;display:inline-block;">Sign in</a>
        <?php elseif ($myMembership && $myMembership['status']==='affirmed'): ?>
          <p style="font-size:12.5px;color:#166534;margin:6px 0 0;"><i class="bi bi-patch-check-fill"></i> Your role <strong><?= htmlspecialchars($myMembership['position']) ?></strong> is affirmed here.</p>
        <?php elseif ($myMembership && $myMembership['status']==='pending'): ?>
          <p style="font-size:12.5px;color:#b45309;margin:6px 0 0;"><i class="bi bi-hourglass-split"></i> Your claim for <strong><?= htmlspecialchars($myMembership['position']) ?></strong> is awaiting affirmation.</p>
        <?php else: ?>
          <p style="font-size:12px;color:var(--ink-3);margin:6px 0 10px;">Claim your position — the organization affirms it before it appears publicly.</p>
          <form method="post">
            <input type="hidden" name="action" value="claim">
            <input name="position" placeholder="Your position / title" required style="width:100%;font-size:13px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:8px;outline:none;font-family:inherit;">
            <select name="employment_type" style="width:100%;font-size:12.5px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:10px;outline:none;font-family:inherit;color:var(--ink);"><option>Full-time</option><option>Part-time</option><option>Contract</option><option>Internship</option></select>
            <button type="submit" style="width:100%;font-size:12.5px;font-weight:700;background:#1e3a5f;color:#fff;border:none;padding:9px;border-radius:9px;cursor:pointer;">Claim my position</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
