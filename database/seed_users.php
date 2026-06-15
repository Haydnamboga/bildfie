<?php
/**
 * Seed realistic marketplace members (frontend users).
 *   php database/seed_users.php
 * Idempotent — skips emails that already exist. Default password: Member123
 */
require_once __DIR__ . '/../config/db.php';

function uuidv4(): string {
    $d = random_bytes(16);
    $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
    $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

$regs = [];
foreach (db_all("SELECT id,code FROM regions") as $r) $regs[$r['code']] = (int)$r['id'];

// name, email, phone, region, is_provider, status, verified, days_ago
$members = [
    ['Peter Njoroge','peter.njoroge@example.com','+254701000011','KE',0,'active',1,2],
    ['Amina Said','amina.said@example.com','+255712000022','TZ',1,'active',1,4],
    ['Daniel Otieno','daniel.otieno@example.com','+254702000033','KE',1,'active',1,5],
    ['Brenda Achieng','brenda.achieng@example.com','+254703000044','KE',0,'pending',0,1],
    ['Joseph Mensah','joseph.mensah@example.com','+233244000055','GH',1,'active',1,8],
    ['Ngozi Okeke','ngozi.okeke@example.com','+234803000066','NG',0,'active',1,10],
    ['Hassan Juma','hassan.juma@example.com','+255713000077','TZ',1,'suspended',1,22],
    ['Catherine Wairimu','catherine.w@example.com','+254704000088','KE',0,'active',0,3],
    ['Emmanuel Bwana','emmanuel.bwana@example.com','+256772000099','UG',1,'active',1,12],
    ['Fatima Yusuf','fatima.yusuf@example.com','+234804000110','NG',0,'pending',0,1],
    ['Kelvin Mutua','kelvin.mutua@example.com','+254705000121','KE',1,'active',1,7],
    ['Grace Nakimuli','grace.nakimuli@example.com','+256773000132','UG',0,'active',1,15],
    ['Samuel Kiprotich','samuel.kip@example.com','+254706000143','KE',1,'active',1,20],
    ['Linda Mwakalebela','linda.mwaka@example.com','+255714000154','TZ',0,'active',0,6],
    ['Chinedu Obi','chinedu.obi@example.com','+234805000165','NG',1,'active',1,18],
    ['Esther Wambui','esther.wambui@example.com','+254707000176','KE',0,'active',1,9],
    ['Patrick Niyonzima','patrick.n@example.com','+250788000187','RW',1,'pending',0,2],
    ['Mary Adhiambo','mary.adhiambo@example.com','+254708000198','KE',0,'active',1,11],
    ['Tendai Moyo','tendai.moyo@example.com','+27710000209','ZA',1,'active',1,25],
    ['Abdul Rahman','abdul.rahman@example.com','+255715000210','TZ',0,'suspended',0,30],
    ['Janet Cheptoo','janet.cheptoo@example.com','+254709000221','KE',1,'active',1,14],
    ['Kwame Asante','kwame.asante@example.com','+233245000232','GH',0,'active',1,16],
    ['Sarah Nabukenya','sarah.nab@example.com','+256774000243','UG',1,'active',0,5],
    ['Victor Oduya','victor.oduya@example.com','+254710000254','KE',0,'pending',0,1],
    ['Halima Mohammed','halima.moh@example.com','+234806000265','NG',1,'active',1,21],
    ['Dennis Karanja','dennis.karanja@example.com','+254711000276','KE',0,'active',1,13],
];

$added = 0;
$pwd = password_hash('Member123', PASSWORD_DEFAULT);
foreach ($members as [$n,$e,$ph,$rc,$prov,$st,$ver,$days]) {
    if (db_value("SELECT id FROM users WHERE email=?", [$e])) continue;
    $created = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    $verAt   = $ver ? date('Y-m-d H:i:s', strtotime("-{$days} days +3 hours")) : null;
    db_insert(
        "INSERT INTO users (public_id,name,email,phone,password_hash,region_id,is_provider,is_client,status,email_verified_at,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [uuidv4(), $n, $e, $ph, $pwd, $regs[$rc] ?? null, $prov, 1, $st, $verAt, $created]
    );
    $added++;
}

echo "Members seeded (+{$added}, password 'Member123').\n";
echo "Total users: " . db_value("SELECT COUNT(*) FROM users") . "\n";
