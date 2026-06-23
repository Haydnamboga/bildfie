<?php
/** Seed a realistic wallet + ledger for the demo member. php database/seed_wallet.php */
require_once __DIR__ . '/../config/db.php';

$uid = (int) db_value("SELECT id FROM users WHERE email='james@bildfie.com'");
if (!$uid) { echo "James not found.\n"; exit; }
if ((int) db_value("SELECT COUNT(*) FROM wallet_transactions WHERE user_id=?", [$uid]) > 0) { echo "Wallet already seeded.\n"; exit; }

db()->query("INSERT IGNORE INTO wallets (user_id, balance, currency_code) VALUES ($uid, 0, 'KES')");

// type, amount, description, method, days_ago
$ledger = [
    ['deposit',    50000, 'Top-up via M-Pesa',              'M-Pesa',        25],
    ['payment',    12000, 'Hired Amina Osei — site visit',  'Wallet',        20],
    ['deposit',    30000, 'Top-up via M-Pesa',              'M-Pesa',        14],
    ['fee',          800, 'Platform service fee',           'Wallet',        14],
    ['withdrawal', 25000, 'Withdrawal to bank',             'Bank transfer',  9],
    ['refund',      5000, 'Refund — cancelled booking',     'Wallet',         5],
    ['payout',     18500, 'Earnings payout — Westlands job','Wallet',         3],
    ['withdrawal', 15000, 'Withdrawal to M-Pesa',           'M-Pesa',         1],
];

$bal = 0.0;
foreach ($ledger as [$type, $amt, $desc, $method, $days]) {
    $credit = in_array($type, ['deposit','refund','payout'], true);
    $bal = $credit ? $bal + $amt : $bal - $amt;
    $ref = strtoupper(substr($type, 0, 3)) . '-' . date('ymd', strtotime("-$days days")) . '-' . strtoupper(bin2hex(random_bytes(2)));
    $created = date('Y-m-d H:i:s', strtotime("-$days days"));
    db_insert("INSERT INTO wallet_transactions (user_id,type,amount,balance_after,status,method,reference,description,created_at) VALUES (?,?,?,?,?,?,?,?,?)",
        [$uid, $type, $amt, $bal, 'completed', $method, $ref, $desc, $created]);
}
db_stmt("UPDATE wallets SET balance=? WHERE user_id=?", [$bal, $uid]);
echo "Wallet seeded for James — balance KES " . number_format($bal) . " across " . count($ledger) . " transactions.\n";
