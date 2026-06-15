<?php
/** Seed companies + specialties + affirmed/pending employment. php database/seed_companies.php */
require_once __DIR__ . '/../config/companies.php';

if ((int) db_value("SELECT COUNT(*) FROM companies") > 0) { echo "Companies already seeded.\n"; exit; }

$regKE = (int) db_value("SELECT id FROM regions WHERE code='KE'");

// name, tagline, industry, type, size, founded, hq, website, verified, [specialties], coverSeed
$companies = [
    ['Acacia Architects','Designing East Africa\'s built future.','Architecture','Private','11-50',2009,'Westlands, Nairobi','acaciaarchitects.co.ke',1,
        ['Architectural Design','Interior Design','Urban Planning','BIM','Sustainable Design'],'acacia'],
    ['Bovis Africa Construction','Building landmarks across the region since 1998.','Construction','Private','201-500',1998,'Industrial Area, Nairobi','bovisafrica.com',1,
        ['General Contracting','High-Rise','Infrastructure','Design & Build','Project Management'],'bovis'],
    ['Sahara Structural Consultants','Structural & civil engineering excellence.','Civil Engineering','Private','51-200',2005,'Upper Hill, Nairobi','saharastructural.co.ke',1,
        ['Structural Engineering','Foundations','Seismic Design','BOQ & Costing'],'sahara'],
    ['BuildRight Engineering','MEP & services engineering for modern builds.','Engineering','Private','11-50',2014,'Nyali, Mombasa','buildright.co.ke',0,
        ['MEP Engineering','Electrical','Plumbing','HVAC'],'buildright'],
    ['Kenya Institute of Construction','Training the next generation of builders.','Education','Institution','51-200',1986,'Ngara, Nairobi','kic.ac.ke',1,
        ['Vocational Training','Certification','Apprenticeships','Research'],'kic'],
];

$idBySlug = [];
foreach ($companies as [$name,$tag,$ind,$type,$size,$founded,$hq,$web,$ver,$specs,$cover]) {
    $slug = preg_replace('/[^a-z0-9]+/','-', strtolower($name)); $slug = trim($slug, '-');
    $cid = db_insert(
        "INSERT INTO companies (public_id,slug,name,tagline,industry,company_type,company_size,founded_year,hq_location,region_id,website,logo_url,cover_url,is_verified,status,followers,about)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'active', ?, ?)",
        [company_uuid(), $slug, $name, $tag, $ind, $type, $size, $founded, $hq, $regKE, $web,
         'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1e3a5f&color=fff&size=160&bold=true&format=png',
         'https://picsum.photos/seed/co-' . $cover . '/1200/280',
         $ver, random_int(120, 4800),
         $name . ' is a ' . strtolower($type) . ' ' . strtolower($ind) . ' organization based in ' . $hq . '. We deliver trusted, high-quality work across Kenya and the wider East African region, partnering with clients from concept through to handover.']
    );
    $idBySlug[$slug] = $cid;
    $i = 0; foreach ($specs as $s) db_insert("INSERT INTO company_specialties (company_id,specialty,sort_order) VALUES (?,?,?)", [$cid, $s, $i++]);
}

// ── employment: affirm James + a few members; one pending claim ──
function uidByEmail(string $e): int { return (int) db_value("SELECT id FROM users WHERE email=?", [$e]); }
function affirm(int $cid, int $uid, string $pos, string $type='Full-time'): void {
    if (!$cid || !$uid) return;
    db_insert("INSERT INTO company_members (company_id,user_id,position,employment_type,is_current,status,affirmed_at) VALUES (?,?,?,?,1,'affirmed',NOW())", [$cid,$uid,$pos,$type]);
}
function claim(int $cid, int $uid, string $pos): void {
    if (!$cid || !$uid) return;
    db_insert("INSERT INTO company_members (company_id,user_id,position,employment_type,is_current,status) VALUES (?,?,?,'Full-time',1,'pending')", [$cid,$uid,$pos]);
}

affirm($idBySlug['acacia-architects'], uidByEmail('james@bildfie.com'),       'Lead Architect');
affirm($idBySlug['acacia-architects'], uidByEmail('brenda.achieng@example.com'),'Junior Architect');
affirm($idBySlug['bovis-africa-construction'], uidByEmail('daniel.otieno@example.com'), 'Site Engineer');
affirm($idBySlug['bovis-africa-construction'], uidByEmail('peter.njoroge@example.com'), 'Project Manager');
affirm($idBySlug['sahara-structural-consultants'], uidByEmail('samuel.kip@example.com'), 'Structural Engineer');
affirm($idBySlug['kenya-institute-of-construction'], uidByEmail('esther.wambui@example.com'), 'Training Coordinator');
claim($idBySlug['bovis-africa-construction'], uidByEmail('catherine.w@example.com'), 'Quantity Surveyor');
claim($idBySlug['acacia-architects'], uidByEmail('kelvin.mutua@example.com'), 'Draughtsman');

echo "Seeded " . count($companies) . " companies, specialties, and employment (affirmed + pending).\n";
echo "Companies=" . db_value("SELECT COUNT(*) FROM companies") . " Members=" . db_value("SELECT COUNT(*) FROM company_members") . "\n";
