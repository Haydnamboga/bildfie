<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
$page_title = 'Catalog · Verticals';
$ap = 'categories';
$topbar_crumb = 'Catalog';

function slugify(string $s): string {
    $s = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($s)));
    return trim($s, '-') ?: ('item-' . substr(uniqid(), -5));
}
function flash(string $m): void { $_SESSION['cat_flash'] = $m; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    $redir = '/admin/catalog.php';

    if ($a === 'add_vertical') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $slug = slugify($name);
            if (db_value("SELECT id FROM verticals WHERE slug=?", [$slug])) $slug .= '-' . substr(uniqid(), -4);
            $vid = db_insert(
                "INSERT INTO verticals (slug,name,icon,color,bg,href,provider_count,sort_order) VALUES (?,?,?,?,?,?,?,?)",
                [$slug, $name, trim($_POST['icon'] ?? 'bi-grid') ?: 'bi-grid', trim($_POST['color'] ?? '#1e3a5f') ?: '#1e3a5f',
                 trim($_POST['bg'] ?? '#eaf0f6') ?: '#eaf0f6', trim($_POST['href'] ?? '') ?: null,
                 (int)($_POST['provider_count'] ?? 0), (int)($_POST['sort_order'] ?? 99)]
            );
            admin_audit('catalog.vertical.add', 'vertical', $vid, ['name' => $name]);
            flash("Added vertical “{$name}”.");
            $redir = '/admin/catalog.php?edit=' . $vid;
        }
    } elseif ($a === 'update_vertical') {
        $id = (int)$_POST['id'];
        db_stmt("UPDATE verticals SET name=?, icon=?, color=?, bg=?, href=?, provider_count=?, sort_order=? WHERE id=?",
            [trim($_POST['name'] ?? ''), trim($_POST['icon'] ?? 'bi-grid') ?: 'bi-grid', trim($_POST['color'] ?? '#1e3a5f'),
             trim($_POST['bg'] ?? '#eaf0f6'), trim($_POST['href'] ?? '') ?: null,
             (int)($_POST['provider_count'] ?? 0), (int)($_POST['sort_order'] ?? 0), $id]);
        admin_audit('catalog.vertical.update', 'vertical', $id);
        flash('Vertical updated.');
        $redir = '/admin/catalog.php?edit=' . $id;
    } elseif ($a === 'toggle_vertical') {
        $id = (int)$_POST['id'];
        db_stmt("UPDATE verticals SET is_active = 1 - is_active WHERE id=?", [$id]);
        admin_audit('catalog.vertical.toggle', 'vertical', $id);
        flash('Visibility updated.');
    } elseif ($a === 'delete_vertical') {
        $id = (int)$_POST['id'];
        db_stmt("DELETE FROM verticals WHERE id=?", [$id]);
        admin_audit('catalog.vertical.delete', 'vertical', $id);
        flash('Vertical deleted.');
    } elseif ($a === 'add_category') {
        $vid = (int)$_POST['vertical_id']; $name = trim($_POST['name'] ?? '');
        if ($name !== '' && $vid) {
            $slug = slugify($name);
            if (!db_value("SELECT id FROM categories WHERE vertical_id=? AND slug=?", [$vid, $slug])) {
                db_insert("INSERT INTO categories (vertical_id,slug,name,sort_order) VALUES (?,?,?,?)",
                    [$vid, $slug, $name, (int)($_POST['sort_order'] ?? 99)]);
                admin_audit('catalog.category.add', 'category', $vid, ['name' => $name]);
            }
            flash("Added service “{$name}”.");
        }
        $redir = '/admin/catalog.php?edit=' . $vid;
    } elseif ($a === 'delete_category') {
        $cid = (int)$_POST['id']; $vid = (int)$_POST['vertical_id'];
        db_stmt("DELETE FROM categories WHERE id=?", [$cid]);
        admin_audit('catalog.category.delete', 'category', $cid);
        flash('Service removed.');
        $redir = '/admin/catalog.php?edit=' . $vid;
    }
    header('Location: ' . $redir); exit;
}

$msg = $_SESSION['cat_flash'] ?? ''; unset($_SESSION['cat_flash']);
$editId = (int)($_GET['edit'] ?? 0);
$editV  = $editId ? db_one("SELECT * FROM verticals WHERE id=?", [$editId]) : null;
$editCats = $editV ? db_all("SELECT * FROM categories WHERE vertical_id=? ORDER BY sort_order, name", [$editId]) : [];

$verticals = db_all("SELECT v.*, (SELECT COUNT(*) FROM categories c WHERE c.vertical_id=v.id) AS cat_count FROM verticals v ORDER BY v.sort_order, v.name");
$cVert = (int)db_value("SELECT COUNT(*) FROM verticals");
$cCat  = (int)db_value("SELECT COUNT(*) FROM categories");
$cActive = (int)db_value("SELECT COUNT(*) FROM verticals WHERE is_active=1");
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body" style="max-width:1080px;">
      <div class="bf-dash-h">
        <div>
          <div class="bf-eyebrow2"><span>—</span> Catalog &amp; Resources</div>
          <h1 class="bf-dash-title">Verticals &amp; services</h1>
          <p class="bf-dash-sub">Manage the service verticals shown on the public site — changes appear instantly on <a href="/pages/services/index.php" target="_blank" style="color:#c0392b;">/services</a>.</p>
        </div>
      </div>

      <?php if ($msg): ?><div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:11px 15px;font-size:13px;margin-bottom:14px;"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <div class="bf-kpis mb-3">
        <?php foreach ([['Verticals',$cVert,'bi-grid-3x3-gap','#1e3a5f','#eaf0f6'],['Services',$cCat,'bi-tags','#9a7d27','#fdf6e3'],['Active',$cActive,'bi-eye','#166534','#f0fdf4'],['Hidden',$cVert-$cActive,'bi-eye-slash','#6b6b6b','#f4f4f2']] as [$l,$v,$ic,$c,$bg]): ?>
        <div class="bf-kpi" style="grid-column:span 2;"><div class="bf-kpi-top"><div class="bf-kpi-ic" style="background:<?=$bg?>;color:<?=$c?>;"><i class="bi <?=$ic?>"></i></div></div><div class="bf-kpi-v"><?=$v?></div><div class="bf-kpi-l"><?=$l?></div></div>
        <?php endforeach; ?>
      </div>

      <?php if ($editV): ?>
      <!-- ════ EDIT VERTICAL ════ -->
      <a href="/admin/catalog.php" style="font-size:12px;color:var(--ink-3);text-decoration:none;"><i class="bi bi-arrow-left"></i> Back to all verticals</a>
      <div class="row g-3 mt-1">
        <div class="col-lg-6">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi <?= htmlspecialchars($editV['icon']) ?>"></i> Edit “<?= htmlspecialchars($editV['name']) ?>”</div></div>
            <div class="bf-pf-card-b">
              <form method="POST">
                <input type="hidden" name="action" value="update_vertical"><input type="hidden" name="id" value="<?= (int)$editV['id'] ?>">
                <label class="bf-f-lbl">Name</label><input class="bf-f-input" name="name" value="<?= htmlspecialchars($editV['name'], ENT_QUOTES) ?>" required>
                <div class="row g-2 mt-1">
                  <div class="col-6"><label class="bf-f-lbl">Icon (bootstrap-icons)</label><input class="bf-f-input" name="icon" value="<?= htmlspecialchars($editV['icon'], ENT_QUOTES) ?>"></div>
                  <div class="col-3"><label class="bf-f-lbl">Color</label><input class="bf-f-input" name="color" value="<?= htmlspecialchars($editV['color'], ENT_QUOTES) ?>"></div>
                  <div class="col-3"><label class="bf-f-lbl">BG</label><input class="bf-f-input" name="bg" value="<?= htmlspecialchars($editV['bg'], ENT_QUOTES) ?>"></div>
                </div>
                <label class="bf-f-lbl mt-2">Link (href)</label><input class="bf-f-input" name="href" value="<?= htmlspecialchars($editV['href'] ?? '', ENT_QUOTES) ?>">
                <div class="row g-2 mt-1">
                  <div class="col-6"><label class="bf-f-lbl">Provider count</label><input type="number" class="bf-f-input" name="provider_count" value="<?= (int)$editV['provider_count'] ?>"></div>
                  <div class="col-6"><label class="bf-f-lbl">Sort order</label><input type="number" class="bf-f-input" name="sort_order" value="<?= (int)$editV['sort_order'] ?>"></div>
                </div>
                <button class="bf-btn-navy" style="margin-top:14px;"><i class="bi bi-check-lg me-1"></i>Save changes</button>
              </form>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-tags"></i> Services (<?= count($editCats) ?>)</div></div>
            <div class="bf-pf-card-b" style="padding-top:8px;">
              <?php foreach ($editCats as $cat): ?>
              <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-top:1px solid var(--line-2);">
                <span style="flex:1;font-size:12.5px;color:var(--ink);"><?= htmlspecialchars($cat['name']) ?></span>
                <form method="POST" onsubmit="return confirm('Remove this service?');"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="id" value="<?= (int)$cat['id'] ?>"><input type="hidden" name="vertical_id" value="<?= (int)$editV['id'] ?>"><button class="bf-badge red" style="border:none;cursor:pointer;">Remove</button></form>
              </div>
              <?php endforeach; ?>
              <form method="POST" style="display:flex;gap:8px;margin-top:12px;">
                <input type="hidden" name="action" value="add_category"><input type="hidden" name="vertical_id" value="<?= (int)$editV['id'] ?>">
                <input class="bf-f-input" name="name" placeholder="Add a service…" required>
                <button class="bf-btn-navy" style="white-space:nowrap;">Add</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <?php else: ?>
      <!-- ════ ADD VERTICAL ════ -->
      <div class="bf-pf-card">
        <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-plus-circle"></i> Add a vertical</div></div>
        <div class="bf-pf-card-b">
          <form method="POST" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="add_vertical">
            <div class="col-md-4"><label class="bf-f-lbl">Name</label><input class="bf-f-input" name="name" placeholder="e.g. Photography" required></div>
            <div class="col-md-2"><label class="bf-f-lbl">Icon</label><input class="bf-f-input" name="icon" value="bi-grid" placeholder="bi-camera"></div>
            <div class="col-md-2"><label class="bf-f-lbl">Color</label><input class="bf-f-input" name="color" value="#1e3a5f"></div>
            <div class="col-md-2"><label class="bf-f-lbl">BG</label><input class="bf-f-input" name="bg" value="#eaf0f6"></div>
            <div class="col-md-2"><button class="bf-btn-accent" style="border:none;cursor:pointer;width:100%;"><i class="bi bi-plus-lg me-1"></i>Add</button></div>
          </form>
        </div>
      </div>

      <!-- ════ LIST ════ -->
      <div class="bf-tbl-wrap">
        <div class="bf-tbl-head">
          <div style="width:46px;"></div>
          <div style="flex:1;">Vertical</div>
          <div style="width:90px;" class="d-none d-md-block">Services</div>
          <div style="width:110px;" class="d-none d-md-block">Providers</div>
          <div style="width:70px;">Order</div>
          <div style="width:80px;">Status</div>
          <div style="width:170px;text-align:right;">Actions</div>
        </div>
        <?php foreach ($verticals as $v): ?>
        <div class="bf-tbl-row">
          <div style="width:46px;"><span style="width:32px;height:32px;border-radius:8px;background:<?= htmlspecialchars($v['bg']) ?>;color:<?= htmlspecialchars($v['color']) ?>;display:flex;align-items:center;justify-content:center;"><i class="bi <?= htmlspecialchars($v['icon']) ?>"></i></span></div>
          <div style="flex:1;min-width:0;"><div class="pri"><?= htmlspecialchars($v['name']) ?></div><div style="font-size:11px;color:var(--ink-4);"><?= htmlspecialchars($v['slug']) ?></div></div>
          <div style="width:90px;" class="d-none d-md-block"><?= (int)$v['cat_count'] ?></div>
          <div style="width:110px;" class="d-none d-md-block"><?= number_format((int)$v['provider_count']) ?></div>
          <div style="width:70px;"><?= (int)$v['sort_order'] ?></div>
          <div style="width:80px;"><span class="bf-badge <?= $v['is_active']?'green':'grey' ?>"><?= $v['is_active']?'Active':'Hidden' ?></span></div>
          <div style="width:170px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
            <a href="/admin/catalog.php?edit=<?= (int)$v['id'] ?>" class="bf-badge navy" style="text-decoration:none;">Edit</a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="toggle_vertical"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><button class="bf-badge grey" style="border:none;cursor:pointer;"><?= $v['is_active']?'Hide':'Show' ?></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this vertical and its services?');"><input type="hidden" name="action" value="delete_vertical"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><button class="bf-badge red" style="border:none;cursor:pointer;"><i class="bi bi-trash"></i></button></form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
