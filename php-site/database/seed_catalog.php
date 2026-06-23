<?php
/**
 * Seed the catalog (verticals + categories + learn levels/subjects). Idempotent by slug.
 *   php database/seed_catalog.php
 */
require_once __DIR__ . '/../config/db.php';

function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

// [name, icon, color, bg, provider_count, href, [categories]]
$P = '/pages/marketplace/professionals.php';
$verticals = [
    ['Construction & Built Environment','bi-bricks','#1e3a5f','#eaf0f6',48600,$P,['Contractors','Architects','Engineers','Masons','Plumbers','Electricians','Painters','Roofers','Surveyors']],
    ['Home & Domestic','bi-house-gear','#1e40af','#eff6ff',12400,$P,['Cleaning','Appliance Repair','Pest Control','Moving','Gardening','Laundry','Locksmith','Handyman']],
    ['Professional & Business','bi-briefcase','#166534','#f0fdf4',8900,$P,['Accounting','Legal','Tax / eTIMS','HR & Payroll','Consulting','Valuation','Bookkeeping']],
    ['Creative & Digital','bi-palette','#7c3aed','#f5f0ff',6200,$P,['Graphic Design','Web & App Dev','Photography','Videography','Content','Social Media','Printing']],
    ['Beauty & Personal Care','bi-scissors','#c0392b','#fef2f2',9100,$P,['Salons','Barbers','Makeup','Nails','Spa & Massage','Mobile Beauty','Fitness']],
    ['Health & Care','bi-heart-pulse','#16a34a','#f0fdf4',4300,$P,['Home Nursing','Elder Care','Physiotherapy','Telehealth','Home Labs','Caregivers']],
    ['Auto & Mechanics','bi-car-front','#b45309','#fffbeb',5600,$P,['Mechanics','Car Wash','Towing','Spare Parts','Tyres','Diagnostics','Driving Schools']],
    ['Transport & Logistics','bi-truck','#9a7d27','#fdf6e3',2800,'/pages/marketplace/transport.php',['Trucking','Movers','Courier','Boda','Cold-chain','Car Hire']],
    ['Events & Hospitality','bi-balloon','#7c3aed','#f5f0ff',3400,$P,['Catering','Tents & Décor','Event Planning','DJs & Sound','Venues','Photography']],
    ['Property & Real Estate','bi-buildings','#1e3a5f','#eaf0f6',3400,$P,['Agents','Rentals','Short-lets','Management','Caretakers','Valuation']],
    ['Agriculture & Agritech','bi-flower1','#166534','#f0fdf4',5100,$P,['Farm Inputs','Mechanization','Agronomy','Veterinary','Produce Offtake','Irrigation']],
    ['Energy, Water & Environment','bi-lightning-charge','#b45309','#fffbeb',2200,$P,['Solar','Boreholes','Water Delivery','Generators','Waste Mgmt','Biogas']],
    ['Education & Tutoring','bi-mortarboard','#1e40af','#eff6ff',7200,'/pages/learn/index.php',['Tutors','Exam Prep','TVET','Certification','Assignments','Research & Thesis']],
    ['Security & Safety','bi-shield-check','#c0392b','#fef2f2',1900,$P,['Guards','CCTV & Alarm','Access Control','Fire Safety','Investigators']],
    ['Travel & Tourism','bi-airplane','#1e40af','#eff6ff',1400,$P,['Tours & Safaris','Guides','Booking','Transfers','Stays','Packages']],
    ['Equipment & Tools Hire','bi-gear-wide-connected','#1e3a5f','#eaf0f6',6400,'/pages/marketplace/equipment.php',['Construction','Generators','Power Tools','Event','Medical','AV']],
    ['Materials & Supplies','bi-box-seam','#9a7d27','#fdf6e3',24800,'/pages/marketplace/materials.php',['Building','Hardware','Electrical','Spares','Agri-inputs','PPE']],
    ['Errands & On-Demand','bi-bag','#6b6b6b','#f4f4f2',2100,$P,['Shopping','Deliveries','Errands','Pet Care','Bill Payments']],
];

$vAdded = 0; $cAdded = 0;
foreach ($verticals as $i => [$name,$icon,$color,$bg,$count,$href,$cats]) {
    $slug = slugify($name);
    $vid = (int) db_value("SELECT id FROM verticals WHERE slug=?", [$slug]);
    if (!$vid) {
        $vid = db_insert(
            "INSERT INTO verticals (slug,name,icon,color,bg,href,provider_count,sort_order) VALUES (?,?,?,?,?,?,?,?)",
            [$slug,$name,$icon,$color,$bg,$href,$count,$i]
        );
        $vAdded++;
    }
    foreach ($cats as $j => $cn) {
        $cslug = slugify($cn);
        if (!db_value("SELECT id FROM categories WHERE vertical_id=? AND slug=?", [$vid,$cslug])) {
            db_insert("INSERT INTO categories (vertical_id,slug,name,sort_order) VALUES (?,?,?,?)", [$vid,$cslug,$cn,$j]);
            $cAdded++;
        }
    }
}

// [name, emoji, age_label, gradient, resource_count, [subjects]]
$levels = [
    ['Early Years','🧸','Babies & Pre-school (0–6)','linear-gradient(135deg,#f59e0b,#fbbf24)',1240,['Activities & Play','Stories','Counting & Colours','Colouring','Fun & Games','Parent Guides']],
    ['Primary School','✏️','PP1 – Grade 6','linear-gradient(135deg,#1e40af,#3b6fd4)',8600,['Homework Help','Tutorials','Past Papers','Exams','Projects','Revision Notes']],
    ['Secondary / High School','📐','Grade 7–12 · Form 1–4','linear-gradient(135deg,#166534,#22a45a)',11200,['KCSE / Exam Prep','Tutorials','Past Papers','Assignments','Sciences','Languages']],
    ['College & TVET','🛠️','Diploma & Certificate','linear-gradient(135deg,#b45309,#e0892f)',4300,['Coursework','Attachment Reports','Projects','Skills','Certification','Notes']],
    ['University','🎓','Undergraduate & Postgraduate','linear-gradient(135deg,#7c3aed,#9d6bf0)',9800,['Assignments','Research','Thesis & Dissertation','Data Analysis','Proposals','Citations']],
    ['Professional & Skills','💼','Career & lifelong learning','linear-gradient(135deg,#c0392b,#e05546)',3100,['CPD','Certifications','Coding','Business','Languages','Career Coaching']],
];

$lAdded = 0; $sAdded = 0;
foreach ($levels as $i => [$name,$emoji,$age,$grad,$count,$subs]) {
    $slug = slugify($name);
    $lid = (int) db_value("SELECT id FROM learn_levels WHERE slug=?", [$slug]);
    if (!$lid) {
        $lid = db_insert(
            "INSERT INTO learn_levels (slug,name,emoji,age_label,gradient,resource_count,sort_order) VALUES (?,?,?,?,?,?,?)",
            [$slug,$name,$emoji,$age,$grad,$count,$i]
        );
        $lAdded++;
    }
    foreach ($subs as $j => $sn) {
        if (!db_value("SELECT id FROM learn_subjects WHERE level_id=? AND name=?", [$lid,$sn])) {
            db_insert("INSERT INTO learn_subjects (level_id,name,sort_order) VALUES (?,?,?)", [$lid,$sn,$j]);
            $sAdded++;
        }
    }
}

echo "Catalog seeded · +{$vAdded} verticals, +{$cAdded} categories, +{$lAdded} levels, +{$sAdded} subjects\n";
echo "Totals · verticals=" . db_value("SELECT COUNT(*) FROM verticals")
   . " categories=" . db_value("SELECT COUNT(*) FROM categories")
   . " levels=" . db_value("SELECT COUNT(*) FROM learn_levels")
   . " subjects=" . db_value("SELECT COUNT(*) FROM learn_subjects") . "\n";
