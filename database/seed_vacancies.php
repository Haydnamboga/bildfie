<?php
/**
 * Seed a few starter vacancies on existing companies so the "Open positions"
 * panel is visible. Idempotent — skips a company that already has vacancies.
 *   php database/seed_vacancies.php
 */
require_once __DIR__ . '/../config/db.php';

function vac_uuid(): string {
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

$pool = [
    ['Site Engineer',              'Full-time', 'KES 120,000 – 180,000/mo'],
    ['Quantity Surveyor',          'Full-time', 'KES 100,000 – 150,000/mo'],
    ['Project Manager',            'Full-time', 'Competitive'],
    ['Civil Engineer',             'Contract',  'Negotiable'],
    ['Site Foreman',               'Full-time', 'KES 70,000 – 95,000/mo'],
    ['Architect',                  'Full-time', 'KES 130,000 – 200,000/mo'],
    ['Procurement Officer',        'Full-time', 'KES 80,000 – 110,000/mo'],
    ['Safety Officer (NEBOSH)',    'Contract',  'KES 90,000/mo'],
    ['Structural Engineer',        'Full-time', 'KES 140,000 – 210,000/mo'],
    ['Electrical Technician',      'Part-time', 'KES 45,000/mo'],
];

$companies = db_all("SELECT id, name, hq_location FROM companies WHERE status <> 'suspended' ORDER BY id");
$added = 0;
foreach ($companies as $i => $co) {
    if (db_value("SELECT id FROM company_vacancies WHERE company_id=? LIMIT 1", [(int)$co['id']])) continue; // already has some
    $n = 1 + ($i % 3);   // 1–3 openings per company
    $loc = trim(explode(',', $co['hq_location'] ?: 'Nairobi')[0]);
    for ($j = 0; $j < $n; $j++) {
        [$title, $type, $sal] = $pool[($i * 3 + $j) % count($pool)];
        db_insert(
            "INSERT INTO company_vacancies (public_id,company_id,title,employment_type,location,salary_display,description)
             VALUES (?,?,?,?,?,?,?)",
            [vac_uuid(), (int)$co['id'], $title, $type, $loc, $sal,
             "{$co['name']} is hiring a {$title}. Apply directly through bildfie — your profile is shared with the company's hiring team."]
        );
        $added++;
    }
}
echo "Vacancies seeded (+{$added}). Total: " . db_value("SELECT COUNT(*) FROM company_vacancies") . "\n";
