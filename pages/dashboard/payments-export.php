<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$uid = (int) current_user()['id'];

$rows = wallet_history($uid, 1000);

while (ob_get_level() > 0) { ob_end_clean(); } // drop the lazy-load buffer for a clean download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="bildfie-transactions-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Type', 'Description', 'Method', 'Reference', 'Amount', 'Status', 'Balance after']);
foreach ($rows as $t) {
    fputcsv($out, [
        date('Y-m-d H:i', strtotime($t['created_at'])),
        $t['type'],
        $t['description'],
        $t['method'],
        $t['reference'],
        number_format((float)$t['amount'], 2, '.', ''),
        $t['status'],
        number_format((float)$t['balance_after'], 2, '.', ''),
    ]);
}
fclose($out);
exit;
