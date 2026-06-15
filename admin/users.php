<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Users';
$ap = 'users';
$topbar_crumb = 'List';

// Admin-awardable provider trust badges — single source of truth (config/provider_badges.php).
require_once __DIR__ . '/../config/provider_badges.php';
$flagDefs = provider_trust_defs();   // column => [label, icon, bg, color]; each toggles independently

// ───────── actions (POST) ─────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'toggle_status':
            db_stmt("UPDATE users SET status = IF(status='active','suspended','active') WHERE id=? AND status<>'deleted'", [$id]);
            $ns = db_value("SELECT status FROM users WHERE id=?", [$id]);     // keep the listing in step with the account
            if ($ns === 'suspended')   db_stmt("UPDATE providers SET status='suspended' WHERE user_id=? AND status='active'", [$id]);
            elseif ($ns === 'active')  db_stmt("UPDATE providers SET status='active' WHERE user_id=? AND status='suspended'", [$id]);
            admin_audit('user.status.toggle', 'user', $id); break;
        case 'toggle_flag':
            $pid  = (int)($_POST['pid'] ?? 0);
            $flag = (string)($_POST['flag'] ?? '');
            if ($pid && array_key_exists($flag, $flagDefs)) {   // whitelist → safe to interpolate column
                db_stmt("UPDATE providers SET `$flag` = 1 - `$flag` WHERE id=?", [$pid]);
                admin_audit('provider.flag.toggle', 'provider', $pid, ['flag'=>$flag]);
            }
            break;
        case 'soft_delete':
            db_stmt("UPDATE users SET status='deleted' WHERE id=?", [$id]);
            db_stmt("UPDATE providers SET status='suspended' WHERE user_id=?", [$id]);
            admin_audit('user.soft_delete', 'user', $id); break;
        case 'restore':
            db_stmt("UPDATE users SET status='active' WHERE id=?", [$id]);
            db_stmt("UPDATE providers SET status='active' WHERE user_id=? AND status='suspended'", [$id]);
            admin_audit('user.restore', 'user', $id); break;
        case 'toggle_verify':   // admin fallback when a verification email can't reach the user
            db_stmt("UPDATE users SET email_verified_at = IF(email_verified_at IS NULL, NOW(), NULL) WHERE id=?", [$id]);
            admin_audit('user.verify.toggle', 'user', $id); break;
    }
    $_SESSION['users_flash'] = 'Users updated.';
    header('Location: /admin/users.php?' . ($_POST['qs'] ?? '')); exit;
}
$flash = $_SESSION['users_flash'] ?? ''; unset($_SESSION['users_flash']);

// ───────── filters / sort / paging (GET) ─────────
$q       = trim($_GET['q'] ?? '');
$fStatus = $_GET['status'] ?? '';
$fType   = $_GET['type'] ?? '';
$fVer    = $_GET['verified'] ?? '';
$per     = (int)($_GET['per'] ?? 25); if (!in_array($per, [10,25,50,100])) $per = 25;
$page    = max(1, (int)($_GET['page'] ?? 1));
$sort    = $_GET['sort'] ?? 'created_at';
$dir     = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$sortMap = ['id'=>'u.id','created_at'=>'u.created_at','name'=>'u.name','email'=>'u.email','status'=>'u.status'];
$sortSql = $sortMap[$sort] ?? 'u.created_at';

$where = []; $params = [];
if ($q !== '') { $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)"; $lk = "%$q%"; array_push($params, $lk, $lk, $lk); }
if (in_array($fStatus, ['active','pending','suspended','deleted'])) { $where[] = "u.status=?"; $params[] = $fStatus; }
else { $where[] = "u.status<>'deleted'"; }
if ($fType === 'provider') $where[] = "u.is_provider=1";
elseif ($fType === 'client') $where[] = "u.is_provider=0";
if ($fVer === 'yes') $where[] = "u.email_verified_at IS NOT NULL";
elseif ($fVer === 'no') $where[] = "u.email_verified_at IS NULL";
$wsql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$total  = (int) db_value("SELECT COUNT(*) FROM users u $wsql", $params);
$pages  = max(1, (int)ceil($total / $per));
$page   = min($page, $pages);
$offset = ($page - 1) * $per;
$flagCols = implode(', ', array_map(fn($c) => "p.`$c`", array_keys($flagDefs)));  // all badge columns, auto-synced with the registry
$rows   = db_all("SELECT u.*, r.name AS region_name, r.code AS region_code,
                         p.id AS provider_id, p.status AS p_status, $flagCols
                  FROM users u
                  LEFT JOIN regions r   ON r.id = u.region_id
                  LEFT JOIN providers p ON p.user_id = u.id
                  $wsql ORDER BY $sortSql $dir, u.id DESC LIMIT $per OFFSET $offset", $params);

$kTotal = (int) db_value("SELECT COUNT(*) FROM users WHERE status<>'deleted'");
$kVer   = (int) db_value("SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL AND status<>'deleted'");
$kProv  = (int) db_value("SELECT COUNT(*) FROM users WHERE is_provider=1 AND status<>'deleted'");
$kSusp  = (int) db_value("SELECT COUNT(*) FROM users WHERE status='suspended'");

$curQs = http_build_query($_GET);
$from  = $total ? $offset + 1 : 0;
$to    = min($offset + $per, $total);
$flags = ['KE'=>'🇰🇪','TZ'=>'🇹🇿','UG'=>'🇺🇬','RW'=>'🇷🇼','NG'=>'🇳🇬','GH'=>'🇬🇭','ZA'=>'🇿🇦'];

function sort_th(string $col, string $label): string {
    global $sort, $dir;
    $nd  = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    $qs  = http_build_query(array_merge($_GET, ['sort'=>$col,'dir'=>$nd,'page'=>1]));
    $act = $sort === $col ? 'act' : '';
    $ic  = $sort === $col ? ($dir === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up';
    return '<a class="bf-srt ' . $act . '" href="?' . $qs . '">' . $label . ' <i class="bi ' . $ic . '"></i></a>';
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body">
      <div class="bf-dash-h">
        <div>
          <div class="bf-crumb"><a href="/admin/index.php">Dashboard</a> <i class="bi bi-chevron-right"></i> <span>Users</span> <i class="bi bi-chevron-right"></i> <span style="color:var(--ink-2);">List</span></div>
          <h1 class="bf-dash-title">Users</h1>
          <p class="bf-dash-sub">All marketplace members — clients &amp; providers. Showing <?= $from ?>–<?= $to ?> of <?= number_format($total) ?> entries.</p>
        </div>
      </div>

      <?php if ($flash): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:12px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([
          ['Members',$kTotal,'bi-people','#1e3a5f','#eaf0f6'],
          ['Verified',$kVer,'bi-patch-check','#166534','#f0fdf4'],
          ['Providers',$kProv,'bi-person-badge','#1e40af','#eff6ff'],
          ['Suspended',$kSusp,'bi-slash-circle','#c0392b','#fef2f2'],
        ] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?= number_format($v) ?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <!-- filter bar -->
      <form method="get" class="bf-filterbar">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
        <div class="adm-search" style="height:34px;max-width:280px;flex:1;min-width:200px;"><i class="bi bi-search"></i><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search name, email or phone…"></div>
        <select name="status" class="bf-fld">
          <?php foreach (['' => 'All status','active'=>'Active','pending'=>'Pending','suspended'=>'Suspended','deleted'=>'Deleted'] as $k=>$lbl): ?>
          <option value="<?=$k?>" <?= $fStatus===$k?'selected':'' ?>><?=$lbl?></option>
          <?php endforeach; ?>
        </select>
        <select name="type" class="bf-fld">
          <?php foreach (['' => 'All types','provider'=>'Providers','client'=>'Clients'] as $k=>$lbl): ?>
          <option value="<?=$k?>" <?= $fType===$k?'selected':'' ?>><?=$lbl?></option>
          <?php endforeach; ?>
        </select>
        <select name="verified" class="bf-fld">
          <?php foreach (['' => 'Any email','yes'=>'Verified','no'=>'Unverified'] as $k=>$lbl): ?>
          <option value="<?=$k?>" <?= $fVer===$k?'selected':'' ?>><?=$lbl?></option>
          <?php endforeach; ?>
        </select>
        <button class="bf-btn-s solid"><i class="bi bi-funnel"></i> Apply</button>
        <a href="/admin/users.php" class="bf-btn-s ghost" style="text-decoration:none;">Reset</a>
        <div style="margin-left:auto;display:flex;align-items:center;gap:7px;">
          <span style="font-size:11.5px;color:var(--ink-4);">Per page</span>
          <select name="per" class="bf-fld" onchange="this.form.submit()" style="height:34px;">
            <?php foreach ([10,25,50,100] as $n): ?><option value="<?=$n?>" <?= $per===$n?'selected':'' ?>><?=$n?></option><?php endforeach; ?>
          </select>
        </div>
      </form>

      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="bf-badge green" style="font-size:9.5px;"><i class="bi bi-database"></i> Live from database</span>
      </div>

      <div class="bf-tbl-wrap compact">
        <div class="bf-tbl-head">
          <div style="width:46px;" class="d-none d-md-block"><?= sort_th('id','ID') ?></div>
          <div style="flex:1;"><?= sort_th('name','User') ?></div>
          <div style="width:110px;" class="d-none d-xl-block">Region</div>
          <div style="width:88px;" class="d-none d-md-block"><?= sort_th('created_at','Joined') ?></div>
          <div style="width:96px;" class="d-none d-sm-block">Provider</div>
          <div style="width:340px;text-align:right;">Badges &amp; actions</div>
        </div>

        <?php if (!$rows): ?>
          <div class="bf-tbl-row" style="justify-content:center;color:var(--ink-4);padding:34px;">No users match these filters.</div>
        <?php else: foreach ($rows as $u):
          $id = (int)$u['id']; $st = $u['status']; $rc = $u['region_code'] ?? '';
          $pid = $u['provider_id'] ? (int)$u['provider_id'] : 0; $isProv = $pid > 0;
          $pVer = $isProv && (int)$u['is_verified']; $pFeat = $isProv && (int)$u['is_featured'];
          $profHref = $isProv ? "/pages/marketplace/professional.php?id={$pid}" : "/admin/member.php?id={$id}";
          if (!$isProv)                      { $pbT='Client';   $pbC='grey'; }
          elseif ($pFeat)                    { $pbT='Featured'; $pbC='amber'; }
          elseif ($pVer)                     { $pbT='Verified'; $pbC='green'; }
          elseif ($u['p_status']==='active') { $pbT='Listed';   $pbC='navy'; }
          else                               { $pbT='Pending';  $pbC='grey'; }
        ?>
        <div class="bf-tbl-row">
          <div style="width:46px;font-size:11px;color:var(--ink-4);" class="d-none d-md-block">#<?= $id ?></div>
          <div style="flex:1;min-width:0;">
            <a href="<?= $profHref ?>"<?= $isProv?' target="_blank"':'' ?> class="pri" style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3;text-decoration:none;color:var(--ink);cursor:pointer;" title="View profile" onmouseover="this.style.textDecoration='underline';this.style.color='#1e3a5f'" onmouseout="this.style.textDecoration='none';this.style.color='var(--ink)'"><?= htmlspecialchars($u['name']) ?></a>
            <div style="font-size:10.5px;color:var(--ink-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3;"><?= htmlspecialchars($u['email']) ?></div>
          </div>
          <div style="width:110px;font-size:12px;" class="d-none d-xl-block"><?= $rc ? ($flags[$rc] ?? '') . ' ' . htmlspecialchars($u['region_name']) : '<span style="color:var(--ink-4);">—</span>' ?></div>
          <div style="width:88px;font-size:11.5px;color:var(--ink-3);" class="d-none d-md-block"><?= date('d M Y', strtotime($u['created_at'])) ?></div>
          <div style="width:96px;" class="d-none d-sm-block"><span class="bf-badge <?= $pbC ?>" style="font-size:8.5px;"><?= $pbT ?></span></div>
          <div style="width:340px;display:flex;gap:8px;justify-content:flex-end;align-items:center;">
            <?php if ($isProv): ?>
            <div style="display:flex;flex-wrap:wrap;gap:3px;justify-content:flex-end;flex:1;min-width:0;">
              <?php foreach ($flagDefs as $fcol => [$flbl,$fic,$fbg,$fc]): $fon = (int)($u[$fcol] ?? 0); ?>
              <form method="post" style="display:inline;margin:0;">
                <input type="hidden" name="action" value="toggle_flag">
                <input type="hidden" name="flag" value="<?= $fcol ?>">
                <input type="hidden" name="pid" value="<?= $pid ?>">
                <input type="hidden" name="qs" value="<?= htmlspecialchars($curQs) ?>">
                <button type="submit" class="uib badge-tog" style="<?= $fon ? "background:{$fbg};border-color:{$fc};color:{$fc};" : '' ?>" title="<?= htmlspecialchars($flbl) ?> — <?= $fon?'ON · click to remove':'OFF · click to award' ?>"><i class="bi <?= $fic ?>"></i></button>
              </form>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div style="display:flex;gap:3px;flex-shrink:0;align-items:center;">
              <form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_verify"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="qs" value="<?= htmlspecialchars($curQs) ?>"><button class="uib <?= $u['email_verified_at']?'':'warn' ?>" title="<?= $u['email_verified_at']?'Email verified — click to un-verify':'Mark email verified' ?>"><i class="bi <?= $u['email_verified_at']?'bi-envelope-check-fill':'bi-envelope-exclamation' ?>"></i></button></form>
              <?php if ($st !== 'deleted'): ?>
              <form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="qs" value="<?= htmlspecialchars($curQs) ?>"><button class="uib <?= $st==='suspended'?'warn':'' ?>" title="<?= $st==='active'?'Suspend account':'Activate account' ?>"><i class="bi <?= $st==='active'?'bi-pause-circle':'bi-play-circle' ?>"></i></button></form>
              <?php endif; ?>
              <?php if ($st === 'deleted'): ?>
              <form method="post" style="display:inline;"><input type="hidden" name="action" value="restore"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="qs" value="<?= htmlspecialchars($curQs) ?>"><button class="uib" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button></form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>

        <div class="bf-tfoot">
          <span>Showing <strong><?= $from ?></strong>–<strong><?= $to ?></strong> of <strong><?= number_format($total) ?></strong></span>
          <?php if ($pages > 1): ?>
          <div class="bf-pager">
            <?php $pv = max(1,$page-1); $nx = min($pages,$page+1); ?>
            <a class="<?= $page<=1?'dis':'' ?>" href="?<?= http_build_query(array_merge($_GET,['page'=>$pv])) ?>">‹</a>
            <?php
              $start = max(1, $page-2); $end = min($pages, $start+4); $start = max(1, $end-4);
              if ($start > 1) echo '<a href="?'.http_build_query(array_merge($_GET,['page'=>1])).'">1</a>'.($start>2?'<span class="dis">…</span>':'');
              for ($i=$start; $i<=$end; $i++):
                if ($i==$page) echo '<span class="on">'.$i.'</span>';
                else echo '<a href="?'.http_build_query(array_merge($_GET,['page'=>$i])).'">'.$i.'</a>';
              endfor;
              if ($end < $pages) echo ($end<$pages-1?'<span class="dis">…</span>':'').'<a href="?'.http_build_query(array_merge($_GET,['page'=>$pages])).'">'.$pages.'</a>';
            ?>
            <a class="<?= $page>=$pages?'dis':'' ?>" href="?<?= http_build_query(array_merge($_GET,['page'=>$nx])) ?>">›</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
