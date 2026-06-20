<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

logout();
session_destroy();
header('Location: /');
exit;
