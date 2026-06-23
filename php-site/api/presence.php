<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (!is_logged_in()) { echo json_encode(['ok'=>false]); exit; }
$uid = current_user()['id'];
$ip  = $_SERVER['REMOTE_ADDR'] ?? null;
db_stmt("UPDATE users SET last_seen_at=NOW(), last_seen_ip=? WHERE id=?", [$ip, $uid]);
echo json_encode(['ok'=>true,'ts'=>time()]);
