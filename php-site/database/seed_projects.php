<?php
/** Seed projects (public open-bid + James's own) + bids. php database/seed_projects.php */
require_once __DIR__ . '/../config/db.php';
if ((int) db_value("SELECT COUNT(*) FROM projects") > 0) { echo "Projects already seeded.\n"; exit; }
function uuid4(): string { $d=random_bytes(16);$d[6]=chr(ord($d[6])&0x0f|0x40);$d[8]=chr(ord($d[8])&0x3f|0x80);return vsprintf('%s%s-%s-%s-%s%s%s',str_split(bin2hex($d),4)); }
$jid = (int) db_value("SELECT id FROM users WHERE email='james@bildfie.com'");

// ── Public open-bid projects [segment,name,urgency,owner,ownerLabel,loc,budget,dur,start,bidsN,deadline,remaining,trades,desc] ──
$open = [
  ['commercial','4-Storey Residential Apartment — Structural & Finishing Works','hot','Daniel Otieno','Verified Owner','Kilimani, NBI','KES 48,000–65,000','8 months','Immediate',14,'May 2025','0d 18h','Structural,Finishing,Tiling,Painting','Residential apartment block — 24 units. G+4. Structural framework complete, seeking finishing contractors. BOQ available on request.'],
  ['residential','Architectural Design + BOQ — 5-Bedroom Villa, Dubai','new','Aisha Al-Rashid','Verified Owner','Palm Jumeirah, UAE','KES 22,000–30,000','3 months','Immediate',3,'June 2025','5d 2h','Architecture,BOQ,3D Rendering','Luxury 5-bedroom villa on Palm Jumeirah. Full architectural package needed including concept, design development and working drawings.'],
  ['infrastructure','Road Construction — 12km Murram Access Road, Nakuru County','hot','County Roads Authority','Verified Govt.','Nakuru, Kenya','KES 280,000+','14 months','June 2025',27,'May 2025','3d 14h','Civil Eng.,Earthmoving,Compaction,Drainage','Construction of 12km murram access road connecting 3 villages. Includes culverts, side drains and 3 drift crossings. County government project.'],
  ['commercial','MEP Installation — Commercial Office Block, Lagos Island','new','Landline Properties Ltd','Verified Owner','Lagos, Nigeria','$35,000–52,000','5 months','Immediate',8,'June 2025','3d 8h','MEP Engineer,Electrician,Plumber','5-floor commercial office block. Full MEP scope — HVAC, electrical (11kV intake), plumbing, fire suppression and BMS integration.'],
  ['residential','Renovation & Extension — Period Bungalow, Karen Nairobi','closing','James & Mary Njoroge','Verified Owner','Karen, Nairobi','KES 4,500–7,000','4 months','Immediate',19,'May 2025','1d 6h','Renovation,Structural,Roofing,Interiors','1960s bungalow. Adding 2 bedrooms + family room extension, full renovation of existing structure, new roof, kitchen and bathrooms.'],
  ['government','Solar Installation — 60kW Off-Grid System, 3 Rural Schools','new','Ministry of Energy','Govt. Tender','Turkana County, KE','KES 18,000–25,000','3 months','June 2025',5,'June 2025','6d 12h','Solar PV,Electrical,Battery Storage','60kW total capacity across 3 schools in Turkana. Off-grid with battery storage. EPRA-registered contractors only. Turnkey including installation and commissioning.'],
];
foreach ($open as $i => [$seg,$name,$urg,$owner,$lbl,$loc,$bud,$dur,$start,$bn,$dl,$rem,$tr,$desc]) {
  db_insert("INSERT INTO projects (public_id,owner_name,owner_label,name,segment,location,description,budget_display,duration,start_label,deadline_label,remaining,urgency,trades,bids_count,status,visibility,created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'open','public', ?)",
    [uuid4(),$owner,$lbl,$name,$seg,$loc,$desc,$bud,$dur,$start,$dl,$rem,$urg,$tr,$bn, date('Y-m-d H:i:s', strtotime('-'.($i+1).' days'))]);
}

// ── James's own projects [name,type,status,phase,pct,budget,due] ──
$mine = [
  ['Westlands Office Block','Commercial','active','Structure',68,'KES 45M','30 Jun 2025'],
  ['Riverside Apartments (Block A)','Residential','active','Foundation',22,'KES 28M','15 Sep 2025'],
  ['School Extension – Ngong','Institutional','active','Finishing',90,'KES 8.5M','10 Jun 2025'],
  ['Thika Road Retail Park','Commercial','draft','Design',10,'KES 120M','Dec 2025'],
  ['Lavington Home Renovation','Residential','hold','Interiors',55,'KES 3.2M','Aug 2025'],
];
foreach ($mine as [$name,$type,$st,$phase,$pct,$bud,$due]) {
  db_insert("INSERT INTO projects (public_id,owner_user_id,name,type,phase,progress,budget_display,deadline_label,status,visibility)
             VALUES (?,?,?,?,?,?,?,?,?, 'private')",
    [uuid4(),$jid,$name,$type,$phase,$pct,$bud,$due,$st]);
}

// ── a few bids on the first open project ──
$p1 = (int) db_value("SELECT id FROM projects WHERE visibility='public' ORDER BY id LIMIT 1");
foreach ([['Brian Mutua','KES 5.8M','7 months','Available immediately, NCA-G4 team of 18.'],['Grace Wanjiku','KES 6.2M','8 months','Premium finishing specialist, 40+ similar projects.'],['Kevin Ochieng','KES 5.4M','7.5 months','Competitive rate, own scaffolding & plant.']] as $b) {
  db_insert("INSERT INTO bids (public_id,project_id,bidder_name,amount,timeline,message,status) VALUES (?,?,?,?,?,?, 'submitted')", [uuid4(),$p1,$b[0],$b[1],$b[2],$b[3]]);
}

echo "Seeded projects=" . db_value("SELECT COUNT(*) FROM projects") . " (public=" . db_value("SELECT COUNT(*) FROM projects WHERE visibility='public'") . ", mine=" . db_value("SELECT COUNT(*) FROM projects WHERE owner_user_id=$jid") . ") bids=" . db_value("SELECT COUNT(*) FROM bids") . "\n";
