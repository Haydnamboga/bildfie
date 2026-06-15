<?php
/**
 * Demo data for the transaction core (inventory, documents, contracts).
 *   php database/seed_sales.php            → seeds the default demo members
 *   php database/seed_sales.php 5 7        → seeds specific user ids
 * Idempotent: a category is only seeded for a user who has none of it yet.
 */
require_once __DIR__ . '/../config/sales.php';
require_once __DIR__ . '/../config/auth.php';

$inventory = [
  ['Portland Cement 50kg','CEM-OPC-50','material',1050,420,'bags',80],
  ['Y12 Deformed Bar (12m)','STL-Y12','material',1416,38,'lengths',50],
  ['Bosch GBH 2-26 Hammer Drill','HW-BOSCH-226','hardware',18500,12,'units',4],
  ['DeWalt Site Generator 3.5kVA','HW-DW-GEN35','hardware',96000,0,'units',2],
  ['AutoCAD LT — Annual Licence','SW-ACAD-LT','software',62000,25,'seats',5],
  ['PlanSwift Estimating Licence','SW-PLAN-1','software',88000,3,'seats',5],
  ['Site Safety Helmet (EN397)','HW-PPE-HELM','hardware',850,240,'units',50],
  ['Waterproofing Membrane Roll','MAT-WP-ROLL','material',7400,9,'rolls',12],
  ['Project QA Inspection (per visit)','SVC-QA-01','service',6000,0,'',0],
];

// [client, subject, amount, issue_date, due_date, status]
$invoices = [
  ['Daniel Otieno','Westlands Office Block — structural works',480000,'2026-05-12','2026-05-26','sent'],
  ['Riverside Devs Ltd','Riverside Apartments — finishing',1250000,'2026-05-02','2026-05-16','paid'],
  ['County Roads Authority','Murram Access Road — drainage',2800000,'2026-04-21','2026-05-05','overdue'],
  ['James & Mary Njoroge','Karen Renovation',320000,'2026-04-18','2026-05-02','paid'],
  ['Landline Properties','Lagos Office MEP',960000,'2026-04-10','2026-04-24','paid'],
  ['Aisha Al-Rashid','Palm Jumeirah Villa',540000,null,null,'draft'],
];
// [client, subject, amount, issue_date, status]
$estimates = [
  ['Skyline Developers','Structural works — G+4',11800000,'2026-05-11','accepted'],
  ['Riverside Devs Ltd','Finishing & tiling',7900000,'2026-05-09','sent'],
  ['Karibu Homes','Roofing supply & fix',1400000,'2026-05-05','invoiced'],
  ['County Roads Authority','Drainage works',5200000,'2026-04-30','draft'],
  ['Aisha Al-Rashid','Villa design & BOQ',4000000,'2026-04-22','expired'],
];
$proposals = [
  ['Riverside Devs Ltd','24-unit apartment — finishing works',8400000,'2026-05-12','sent'],
  ['Skyline Developers','Office block structural package',12000000,'2026-05-08','accepted'],
  ['County Roads Authority','Murram access road — 12km',28000000,'2026-05-02','open'],
  ['James & Mary Njoroge','Karen bungalow renovation',3200000,'2026-04-28','declined'],
  ['Landline Properties','Lagos MEP installation',6600000,'2026-04-20','draft'],
];
$creditNotes = [
  ['Riverside Devs Ltd','Overcharge correction — tiling',120000,'2026-05-14','issued'],
  ['Daniel Otieno','Returned materials credit',45000,'2026-05-03','applied'],
];
// [title, counterparty, value, start_date, end_date, status]
$contracts = [
  ['Structural Works Agreement — Westlands','Skyline Developers',11800000,'2026-05-15','2026-11-15','signed'],
  ['Finishing Subcontract — Riverside','Riverside Devs Ltd',7900000,'2026-05-20',null,'sent'],
  ['Roofing Supply & Fix','Karibu Homes',1400000,'2026-04-10','2026-06-10','draft'],
];

function seed_docs(int $uid, string $type, array $rows): int {
    if (count(doc_all($uid, $type)) > 0) return 0;
    foreach ($rows as $r) {
        $issue = $r[3]; $due = ($type === 'invoice') ? ($r[4] ?? null) : null;
        $status = $r[count($r) - 1];
        doc_save($uid, $type, [
            'client_name' => $r[0], 'subject' => $r[1],
            'issue_date'  => $issue, 'due_date' => $due,
            'tax_rate'    => 0, 'status' => $status,
        ], [['description' => $r[1], 'quantity' => 1, 'unit_price' => $r[2]]]);
    }
    return count($rows);
}

$targets = array_slice($argv, 1);
if (!$targets) $targets = ['1', '3']; // James Mwenda, Haydn Amboga

foreach ($targets as $t) {
    $uid = (int) $t;
    if (!$uid || !db_value("SELECT id FROM users WHERE id=?", [$uid])) { echo "Skip: no user #$t\n"; continue; }
    $name = db_value("SELECT name FROM users WHERE id=?", [$uid]);
    echo "Seeding #$uid ($name):\n";

    $n = 0;
    if ((int) inv_stats($uid)['cnt'] === 0) {
        foreach ($inventory as $i) {
            inv_save($uid, ['name'=>$i[0],'sku'=>$i[1],'item_type'=>$i[2],'unit_price'=>$i[3],'quantity'=>$i[4],'unit'=>$i[5],'reorder_level'=>$i[6]]);
        }
        $n = count($inventory);
    }
    echo "  inventory:   " . ($n ?: 'already present') . "\n";
    echo "  invoices:    " . (seed_docs($uid,'invoice',$invoices)        ?: 'already present') . "\n";
    echo "  estimates:   " . (seed_docs($uid,'estimate',$estimates)      ?: 'already present') . "\n";
    echo "  proposals:   " . (seed_docs($uid,'proposal',$proposals)      ?: 'already present') . "\n";
    echo "  creditNotes: " . (seed_docs($uid,'credit_note',$creditNotes) ?: 'already present') . "\n";

    if ((int) contract_stats($uid)['cnt'] === 0) {
        foreach ($contracts as $c) {
            $cid = contract_save($uid, ['title'=>$c[0],'counterparty'=>$c[1],'value'=>$c[2],'start_date'=>$c[3],'end_date'=>$c[4],'status'=>$c[5]]);
            if ($c[5] === 'signed') contract_set_status($uid, $cid, 'signed');
        }
        echo "  contracts:   " . count($contracts) . "\n";
    } else {
        echo "  contracts:   already present\n";
    }

    if (!wallet_history($uid, 1)) {
        wallet_post($uid, 'deposit',    480000,  'Milestone 3 — Westlands Office Block', 'M-Pesa');
        wallet_post($uid, 'deposit',    1250000, 'Escrow released — Riverside Apartments', 'M-Pesa');
        wallet_post($uid, 'deposit',    320000,  'Final payment — Karen Renovation', 'M-Pesa');
        wallet_post($uid, 'fee',        62500,   'Service fee (5%) — INV-2026-041', 'Auto-deducted');
        wallet_post($uid, 'withdrawal', 1200000, 'Withdrawal to Equity Bank ••4471', 'Bank transfer');
        wallet_post($uid, 'withdrawal', 250000,  'Withdrawal to M-Pesa ••5678', 'M-Pesa');
        echo "  wallet:      6 transactions\n";
    } else {
        echo "  wallet:      already present\n";
    }

    if (!client_all($uid)) {
        foreach (db_all("SELECT DISTINCT client_name, client_email, client_phone FROM documents WHERE user_id=? AND client_name IS NOT NULL", [$uid]) as $d) {
            $cid = client_find_or_create($uid, $d['client_name'], $d['client_email'], $d['client_phone']);
            if ($cid) sales_exec("UPDATE documents SET client_id=? WHERE user_id=? AND client_name=? AND client_id IS NULL", [$cid, $uid, $d['client_name']]);
        }
        echo "  clients:     " . count(client_all($uid)) . " (linked from documents)\n";
    } else {
        echo "  clients:     already present\n";
    }
}
echo "\nDone.\n";
