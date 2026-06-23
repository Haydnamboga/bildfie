<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';
require_login();

$uid  = (int) current_user()['id'];
$name = trim($_POST['name'] ?? '');
$id   = (int) ($_POST['id'] ?? 0);

if ($name === '') {
    header('Location: /pages/projects/new.php' . ($id ? "?id=$id&error=name" : '?error=name')); exit;
}

// edit mode — only if the member owns the project
if ($id && project_get($id, $uid)) {
    project_update($id, $uid, $_POST);
    project_settings_save($id, $uid, $_POST);
    $pub = db_value("SELECT public_id FROM projects WHERE id=?", [$id]);
    header("Location: /pages/projects/view.php?id=$pub&saved=1"); exit;
}

$res = project_create($uid, $_POST);
if (!empty($_POST['seed_tasks'])) project_seed_default_tasks((int) $res['id']);   // default construction breakdown
header("Location: /pages/projects/view.php?id={$res['public_id']}&created=1");
exit;
