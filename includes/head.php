<?php
$page_title = isset($page_title) ? $page_title . ' — bildfie' : 'bildfie';
$extra_css  = $extra_css ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="bildfie — Africa's trusted construction marketplace and project CRM.">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://randomuser.me">
<link rel="dns-prefetch" href="https://picsum.photos">
<link rel="dns-prefetch" href="https://ui-avatars.com">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= ASSETS_URL ?>/css/app.css" rel="stylesheet">
<?php foreach ($extra_css as $href): ?>
<link href="<?= htmlspecialchars($href) ?>" rel="stylesheet">
<?php endforeach; ?>
</head>
<body>
