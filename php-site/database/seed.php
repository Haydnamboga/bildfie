<?php
/**
 * Idempotent seed for things SQL can't do well (bcrypt hashing). mysqli.
 *   php database/seed.php
 */
require_once __DIR__ . '/../config/db.php';

function uuidv4(): string {
    $d = random_bytes(16);
    $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
    $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

$superId = (int) db_value("SELECT id FROM roles WHERE code='super'");
$execId  = (int) db_value("SELECT id FROM departments WHERE code='exec'");

// Super Admin role gets every permission
if ($superId) {
    db()->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
                 SELECT {$superId}, id FROM permissions");
}

// ── Owner / Super Admin (protected account) ──
$email = getenv('SEED_ADMIN_EMAIL') ?: 'ambogahaydn@gmail.com';
$exists = db_value("SELECT id FROM staff WHERE email = ?", [$email]);
if (!$exists) {
    // Password is supplied at runtime via env var (never hard-coded/committed).
    // e.g.  SEED_ADMIN_PASSWORD='your-strong-pass' php database/seed.php
    $plain = getenv('SEED_ADMIN_PASSWORD');
    if (!$plain) {
        $plain = bin2hex(random_bytes(6)); // random fallback if none provided
    }
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    db_insert(
        "INSERT INTO staff
           (public_id,name,email,password_hash,role_id,department_id,status,is_protected,photo_url,cover_url,onboarded_year)
         VALUES (?,?,?,?,?,?, 'active', 1, ?,?,?)",
        [uuidv4(), 'Haydn Amboga', $email, $hash, $superId, $execId,
         null, null, 2024]
    );
    echo "Created protected Super Admin: {$email} / {$plain}\n";
} else {
    echo "Super Admin {$email} already exists.\n";
}

// Remove the earlier demo placeholder, if present
db()->query("DELETE FROM staff WHERE email='owner@bildfie.com'");

// ── Leadership & managers (real staff records) ──
$staffSeed = [
    ['Brian Otieno','brian@bildfie.com','coo','ops','men/41',2020],
    ['Diana Mwakio','diana@bildfie.com','cfo','finance','women/12',2020],
    ['Victor Kimani','victor@bildfie.com','cto','tech','men/3',2019],
    ['Lucy Wanjiru','lucy@bildfie.com','chro','people','women/29',2021],
    ['Mark Anyona','mark@bildfie.com','cmo','marketing','men/22',2021],
    ['Grace Achieng','grace@bildfie.com','bdm','bizdev','women/50',2022],
    ['Samuel Kibe','samuel@bildfie.com','salesmgr','ops','men/15',2022],
    ['Peter Gathua','peter@bildfie.com','finmgr','finance','men/60',2023],
    ['Janet Mueni','janet@bildfie.com','support','support','women/33',2023],
];
$added = 0;
foreach ($staffSeed as [$n,$e,$rc,$dc,$ph,$yr]) {
    if (db_value("SELECT id FROM staff WHERE email=?", [$e])) continue;
    $rid = (int) db_value("SELECT id FROM roles WHERE code=?", [$rc]);
    $did = (int) db_value("SELECT id FROM departments WHERE code=?", [$dc]);
    db_insert(
        "INSERT INTO staff (public_id,name,email,password_hash,role_id,department_id,status,is_protected,photo_url,onboarded_year)
         VALUES (?,?,?,?,?,?, 'active', 0, ?, ?)",
        [uuidv4(), $n, $e, password_hash('Staff1234', PASSWORD_DEFAULT), $rid, $did,
         "https://randomuser.me/api/portraits/{$ph}.jpg", $yr]
    );
    $added++;
}
echo "Staff seeded (+{$added}, default password 'Staff1234').\n";

// ── A ready member account ──
if (!db_value("SELECT id FROM users WHERE email=?", ['james@bildfie.com'])) {
    db_insert(
        "INSERT INTO users (public_id,name,email,phone,password_hash,is_provider,is_client,status)
         VALUES (?,?,?,?,?,1,1,'active')",
        [uuidv4(), 'James Mwenda', 'james@bildfie.com', '+254712345678', password_hash('Member123', PASSWORD_DEFAULT)]
    );
    echo "Created member: james@bildfie.com / Member123\n";
}

$staff = db_value("SELECT COUNT(*) FROM staff");
$users = db_value("SELECT COUNT(*) FROM users");
$roles = db_value("SELECT COUNT(*) FROM roles");
echo "Seed complete · staff={$staff} users={$users} roles={$roles}\n";
