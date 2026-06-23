<?php
/**
 * Seed marketplace providers (professionals) + skills. Idempotent by name.
 *   php database/seed_providers.php
 */
require_once __DIR__ . '/../config/db.php';

function uuidv4(): string {
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

$constructionId = (int) db_value("SELECT id FROM verticals WHERE slug='construction-built-environment'");
$keId = (int) db_value("SELECT id FROM regions WHERE code='KE'");

// name, headline, location, rating, reviews, day_rate, badge, available, verified, photo, [skills]
$pros = [
    ['John Mwangi','Structural Engineer','Nairobi, Kenya',4.9,127,'KES 8,500/day','Elite Pro',1,1,'men/32',['Structural Analysis','Foundation Design','ETABS','AutoCAD']],
    ['Amina Osei','Architect & Urban Planner','Accra, Ghana',4.9,93,'GHS 1,200/day','Top Pro',1,1,'women/44',['Architecture','Urban Planning','Revit','3D Rendering']],
    ['David Kariuki','Civil Contractor','Kisumu, Kenya',4.7,211,'KES 6,500/day',null,1,1,'men/76',['Roads','Drainage','Site Supervision','NCA G3']],
    ['Fatuma Hassan','Interior Designer','Dubai, UAE',4.9,84,'$180/day','Top Pro',0,1,'women/68',['Space Planning','FF&E','3ds Max','SketchUp']],
    ['Samuel Otieno','MEP Engineer','Nairobi, Kenya',4.6,56,'KES 10,000/day',null,1,1,'men/54',['HVAC','Electrical','Plumbing','BMS','AutoCAD MEP']],
    ['Grace Wanjiku','Quantity Surveyor','Nakuru, Kenya',4.8,72,'KES 7,000/day','Top Pro',1,1,'women/25',['BOQ Preparation','Cost Planning','NCA','MS Excel']],
    ['Brian Mutua','Project Manager','Nairobi, Kenya',4.7,148,'KES 15,000/day',null,1,0,'men/11',['PMP Certified','MS Project','Risk Management','Agile']],
    ['Zara Abdi','Landscape Architect','Mombasa, Kenya',4.5,31,'KES 8,000/day',null,0,1,'women/62',['Landscape Design','Irrigation','AutoCAD','Planting']],
    ['Chukwuemeka Eze','Civil Engineer','Lagos, Nigeria',4.8,189,'NGN 180,000/day','Elite Pro',1,1,'men/29',['Highway Design','Bridges','COREN','Geotechnical']],
    ['Aisha Al-Rashid','Architect','Abu Dhabi, UAE',4.9,64,'$220/day','Top Pro',1,1,'women/55',['Architecture','BIM','Revit','Luxury Residential']],
    ['Kevin Ochieng','Electrical Engineer','Nairobi, Kenya',4.6,103,'KES 9,000/day',null,1,1,'men/67',['HV/LV Systems','Solar PV','EPRA Registered','ETAP']],
    ['Miriam Ndungu','Geotechnical Engineer','Thika, Kenya',4.7,47,'KES 11,000/day',null,1,0,'women/33',['Soil Testing','Piling','Foundation','Site Investigation']],
];

$added = 0;
foreach ($pros as $i => [$name,$headline,$loc,$rating,$reviews,$rate,$badge,$avail,$verified,$ph,$skills]) {
    if (db_value("SELECT id FROM providers WHERE name=?", [$name])) continue;
    $pid = db_insert(
        "INSERT INTO providers
           (public_id,name,headline,vertical_id,location,region_id,photo_url,cover_url,day_rate,rating,reviews_count,jobs_completed,response_hours,badge,is_verified,is_available,is_featured,sort_order)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [uuidv4(), $name, $headline, $constructionId ?: null, $loc,
         (strpos($loc,'Kenya')!==false ? ($keId ?: null) : null),
         "https://randomuser.me/api/portraits/{$ph}.jpg",
         "https://picsum.photos/seed/prov" . $i . "/1100/280",
         $rate, $rating, $reviews, (int)round($reviews * 1.4) + 20, 2, $badge,
         $verified, $avail, ($badge === 'Elite Pro' ? 1 : 0), $i]
    );
    foreach ($skills as $j => $sk) {
        db_insert("INSERT INTO provider_skills (provider_id,skill,sort_order) VALUES (?,?,?)", [$pid, $sk, $j]);
    }
    $added++;
}

echo "Providers seeded (+{$added}). Totals · providers=" . db_value("SELECT COUNT(*) FROM providers")
   . " skills=" . db_value("SELECT COUNT(*) FROM provider_skills") . "\n";
