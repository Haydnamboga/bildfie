<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['ADMIN','SUPER_ADMIN']);
$me = current_user();
$page_title = 'Manage users';

// Only SUPER_ADMIN can change roles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    require_role('SUPER_ADMIN');
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['user_id'] ?? 0);

    if ($action === 'set_role' && $uid !== (int)$me['id']) {
        $role = $_POST['role'] ?? 'USER';
        if (in_array($role, ['USER','ADMIN','SUPER_ADMIN'], true)) {
            $stmt = $mysqli->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param('si', $role, $uid); $stmt->execute(); $stmt->close();
            flash('Role updated.');
        }
    } elseif ($action === 'delete' && $uid !== (int)$me['id']) {
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
        flash('User deleted.');
    }
    redirect('admin/users.php');
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT 200");
    $like = "%$q%"; $stmt->bind_param('ss', $like, $like); $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
} else {
    $users = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
}
$canEdit = $me['role'] === 'SUPER_ADMIN';

require __DIR__ . '/../includes/header.php';
?>
<a class="back-link" href="<?= e(url('admin/index.php')) ?>">← Back to admin</a>
<h1 class="section-title">Users</h1>
<form class="filters" method="get" action="<?= e(url('admin/users.php')) ?>">
  <div class="field"><label>Search</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="name or email"></div>
  <button class="btn btn-primary" type="submit">Search</button>
  <a class="btn btn-ghost" href="<?= e(url('admin/users.php')) ?>">Reset</a>
</form>

<table class="table">
  <thead><tr><th>Name</th><th>Email</th><th>Type</th><th>Role</th><?php if($canEdit):?><th>Actions</th><?php endif;?></tr></thead>
  <tbody>
  <?php foreach ($users as $usr): ?>
    <tr>
      <td><a href="<?= e(url('professional.php?id=' . $usr['id'])) ?>"><?= e($usr['full_name']) ?></a></td>
      <td class="muted"><?= e($usr['email']) ?></td>
      <td><?= $usr['is_pro'] ? '<span class="skill">Pro'.($usr['category']?' · '.e(nice($usr['category'])):'').'</span>' : '<span class="muted">Client</span>' ?></td>
      <td><?= badge($usr['role']) ?></td>
      <?php if ($canEdit): ?>
      <td>
        <?php if ((int)$usr['id'] !== (int)$me['id']): ?>
        <form method="post" action="<?= e(url('admin/users.php')) ?>" style="display:flex;gap:6px;align-items:center">
          <?= csrf_field() ?>
          <input type="hidden" name="user_id" value="<?= (int)$usr['id'] ?>">
          <select name="role" class="select" style="width:auto;padding:6px 8px">
            <?php foreach (['USER','ADMIN','SUPER_ADMIN'] as $r): ?>
              <option value="<?= $r ?>" <?= $usr['role']===$r?'selected':'' ?>><?= e(nice($r)) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-light btn-sm" name="action" value="set_role">Save</button>
          <button class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm('Delete this user?')">✕</button>
        </form>
        <?php else: ?><span class="muted">(you)</span><?php endif; ?>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php if (!$canEdit): ?><p class="help" style="margin-top:12px">Only Super Admins can change roles.</p><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
