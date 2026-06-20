<?php
require_once __DIR__ . '/config/admin.php';
admin_logout();
header('Location: /admin/login.php');
exit;
