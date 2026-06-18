<?php
require_once __DIR__ . '/functions.php';
$u = current_user();
$page_title = $page_title ?? SITE_NAME;
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> · <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= e(url('index.php')) ?>">
      <span class="brand-mark">▰</span> bildfie
    </a>
    <nav class="main-nav">
      <a href="<?= e(url('marketplace.php')) ?>">Find Pros</a>
      <a href="<?= e(url('jobs.php')) ?>">Jobs</a>
      <?php if ($u): ?>
        <a href="<?= e(url('post-job.php')) ?>">Post a Job</a>
        <a href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
        <?php if (in_array($u['role'], ['ADMIN','SUPER_ADMIN'], true)): ?>
          <a href="<?= e(url('admin/index.php')) ?>">Admin</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="<?= e(url('profile.php')) ?>"><?= e($u['full_name']) ?></a>
        <a class="btn btn-light" href="<?= e(url('logout.php')) ?>">Log out</a>
      <?php else: ?>
        <a href="<?= e(url('login.php')) ?>">Log in</a>
        <a class="btn btn-primary" href="<?= e(url('register.php')) ?>">Sign up</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container page">
<?php foreach ((array)flash() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
