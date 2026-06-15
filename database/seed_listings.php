<?php
/** Seed marketplace supply: suppliers + listings. php database/seed_listings.php */
require_once __DIR__ . '/../config/db.php';

if ((int) db_value("SELECT COUNT(*) FROM listings") > 0) { echo "Listings already seeded.\n"; exit; }
function uuid4(): string { $d=random_bytes(16);$d[6]=chr(ord($d[6])&0x0f|0x40);$d[8]=chr(ord($d[8])&0x3f|0x80);return vsprintf('%s%s-%s-%s-%s%s%s',str_split(bin2hex($d),4)); }
$regKE = (int) db_value("SELECT id FROM regions WHERE code='KE'");

// ── Materials suppliers + their products ──
$suppliers = [
  ['NK','Nairobi Builders Hub','Industrial Area, Nairobi',4.8,240,'+254 20 123 4567','KES 1,100 delivery within 20km','#dcfce7','#166534',1,[
    ['Portland Cement 50kg','KES 1,050','down -2%'],['Deformed Steel Rod 12mm','KES 850','up +1%'],['Building Sand (tonne)','KES 3,200',null],
    ['Roofing Tile (flat)','KES 65/pc',null],['Concrete Block 6"','KES 62',null],['Binding Wire 25kg','KES 1,800','down -1%']]],
  ['GB','Global Build Supplies','Victoria Island, Lagos',4.8,182,'+234 1 234 5678','$18 within 30km','#dbeafe','#1e40af',1,[
    ['Iron Sheet 32 Gauge','$5.50/sheet','up +2%'],['Hollow Section 40x40','$26',null],['Timber 2x4 (5m)','$5.20/pc',null],
    ['Ceramic Tiles 30x30','$14/m2','down -1%'],['PVC Pipe 110mm','$9.20/6m',null],['Interior Paint 20L','$44','up +3%']]],
  ['PS','Premium Build Mart','Accra, Ghana',4.9,98,'+233 30 987 6543','Free delivery over GHS 2,000','#fef9c3','#854d0e',1,[
    ['Granite Slab (m2)','GHS 580','up +5%'],['Gypsum Board 12mm','GHS 145',null],['UPVC Window Frame','GHS 1,020',null],
    ['Structural Steel I-Beam','GHS 2,200','up +2%'],['Cementboard 12mm','GHS 220',null],['Waterproofing Membrane','GHS 440/m2',null]]],
  ['TH','Thika Aggrecom Ltd','Thika, Kenya',4.7,156,'+254 722 111 222','KES 800 within 40km','#f3e8ff','#6b21a8',0,[
    ['Quarry Dust (tonne)','KES 1,800',null],['14mm Crushed Aggregate','KES 2,400','up +3%'],['River Sand (tonne)','KES 3,200','up +1%'],
    ['Ballast (tonne)','KES 2,100',null],['Hardcore (tonne)','KES 1,400',null],['Machine Blocks (each)','KES 58','down -2%']]],
  ['NB','Nairobi Tile & Bath','Westlands, Nairobi',4.9,312,'+254 722 444 555','Free delivery on orders over KES 15,000','#fff7ed','#c2410c',1,[
    ['Porcelain Tile 60x60','KES 980/m2','up +4%'],['Ceramic Wall Tile 30x45','KES 560/m2',null],['Sanitary Ware Set','KES 14,500','down -3%'],
    ['Shower Screen Tempered','KES 8,200',null],['Bathroom Cabinet','KES 6,800',null],['Grouting & Adhesive 5kg','KES 480',null]]],
  ['KW','KenwoodPaints EA','Industrial Area, Nairobi',4.6,205,'+254 722 777 888','KES 600 within 25km','#ecfdf5','#065f46',0,[
    ['Interior Emulsion 20L','KES 3,200','down -1%'],['Exterior Weather Shield 20L','KES 4,800','up +2%'],['Undercoat Primer 4L','KES 980',null],
    ['Gloss Paint 4L','KES 1,200',null],['Texture Paint 20kg','KES 2,600',null],['Paint Thinner 5L','KES 620','down -2%']]],
];
$so = 0;
foreach ($suppliers as [$code,$name,$loc,$rat,$rev,$tel,$del,$bg,$color,$ver,$products]) {
  $sid = db_insert("INSERT INTO suppliers (public_id,code,name,location,region_id,rating,reviews_count,phone,delivery_info,logo_bg,logo_color,is_verified,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
    [uuid4(),$code,$name,$loc,$regKE,$rat,$rev,$tel,$del,$bg,$color,$ver,$so++]);
  $ps = 0;
  foreach ($products as [$pn,$pp,$chg]) {
    db_insert("INSERT INTO listings (public_id,kind,supplier_id,title,price,price_change,location,region_id,is_verified,sort_order) VALUES (?,'materials',?,?,?,?,?,?,?,?)",
      [uuid4(),$sid,$pn,$pp,$chg,$loc,$regKE,$ver,$ps++]);
  }
}

// ── Equipment ──
$equipment = [
  ['CAT 320 Excavator','Excavators','https://picsum.photos/id/96/800/400','POWER · DIESEL',['OUTPUT'=>'350 HP','BUCKET'=>'1.4m3','REACH'=>'9.92m','CAPACITY'=>'22,000 kg'],'$420/day','$1,800/wk',4.9,240,'Nairobi · Delivery incl.','bi-arrows-move'],
  ['Perkins 200 kVA Generator','Generators','https://picsum.photos/id/376/800/400','POWER · DIESEL SILENT',['OUTPUT'=>'200 kVA','ENGINE'=>'6 Cyl 530L','LOAD'=>'160 kVA','FUEL TANK'=>'330 L'],'$120/day','$520/wk',4.7,128,'Nairobi · Delivery incl.','bi-lightning-charge'],
  ['Putzmeister BSF 36Z','Concrete','https://picsum.photos/id/209/800/400','CONCRETE · 36M BOOM',['BOOM REACH'=>'36m','OUTPUT'=>'160 m3/hr','PIPE'=>'DN125','SECTIONS'=>'5 section'],'$650/day','$2,800/wk',4.9,21,'Mombasa · Op. incl.','bi-truck'],
  ['JLG 3246ES Scissor Lift','Access','https://picsum.photos/id/111/800/400','ACCESS · ELECTRIC 10M',['PLATFORM HT.'=>'9.92m','WIDTH'=>'0.81m','CAPACITY'=>'450 kg','DRIVE'=>'Electric'],'$95/day','$420/wk',4.5,73,'Nairobi · Self-drive','bi-arrow-up-square'],
  ['Dynapac CC4200 Roller','Compaction','https://picsum.photos/id/149/800/400','COMPACTION · TANDEM',['WEIGHT'=>'10,500 kg','WIDTH'=>'2.13m','AMPLITUDE'=>'0.5/1.0mm','TRAVEL'=>'11 km/h'],'$180/day','$760/wk',4.8,55,'Nairobi · Op. incl.','bi-circle'],
  ['Terex TF60 Tower Crane','Lifting','https://picsum.photos/id/177/800/400','LIFTING · 60M JIB',['MAX LOAD'=>'6,000 kg','JIB LENGTH'=>'60m','HEIGHT'=>'Up to 72m','TIP LOAD'=>'1,300 kg'],'$850/day','$3,600/wk',5.0,14,'Nairobi · Erect incl.','bi-arrows-expand'],
];
$so = 0;
foreach ($equipment as [$name,$cat,$img,$badge,$specs,$day,$week,$rat,$hires,$loc,$icon]) {
  db_insert("INSERT INTO listings (public_id,kind,title,category,image_url,badge,specs,price,price_secondary,rating,usage_count,location,icon,region_id,avail_label,is_verified,sort_order) VALUES (?,'equipment',?,?,?,?,?,?,?,?,?,?,?,?,'AVAILABLE',1,?)",
    [uuid4(),$name,$cat,$img,$badge,json_encode($specs),$day,$week,$rat,$hires,$loc,$icon,$regKE,$so++]);
}

// ── Transport / vehicles ──
$vehicles = [
  ['7-Tonne Lorry','Moses Kariuki · Nairobi','bi-truck','Pickups & Light Haulage','$45/trip','$180/day','AVAIL.','Specialises in sand, ballast and construction material haulage. 80km radius. Licensed, insured, GPS tracked.',4.8,212,'men/11'],
  ['3/4-Tonne Pickup','Peter Ouma · Nairobi','bi-car-front','Materials Pickup','$25/trip','$80/day','AVAIL.','Light materials pickup and site runs. Half-tonne. Flexible same-day booking.',4.9,406,'men/22'],
  ['3-Truck Fleet · Regional','Sunrise Haulage Ltd','bi-truck','Commercial Fleet','From $120/day','POA','AVAIL.','Commercial fleet: 10-tonne, 7-tonne, pickup. GPS tracked. Long-distance available. Min 3-day contract.',4.7,1240,'men/33'],
  ['Low Loader · Heavy Haul','SteelMove Express','bi-truck','Heavy Machinery','$220/trip','POA','FEW','Heavy machinery and structural steel transport. Low loader with permit handling. Crane-assist on request.',4.5,54,'men/44'],
  ['Concrete Transit Mixer','ReadyMix Nairobi','bi-truck','Concrete Delivery','$95/load','$380/day','AVAIL.','8m3 agitator drum. Self-loading at batching plant. Serves Nairobi, Thika, Kiambu. Min 4m3 per order.',4.8,318,'men/55'],
  ['10T Tipper · Aggregates','Kamau Tippers Ltd','bi-truck','Bulk Aggregates','$70/trip','$220/day','AVAIL.','Hardcore, sand, gravel and quarry aggregates. Own quarry supply available. Nairobi metro + 60km radius.',4.6,189,'men/66'],
  ['Crane Lorry · 15T','HeavyLift Kenya','bi-truck','Crane & Erection','$340/day','POA','AVAIL.','15-tonne telescopic crane lorry. EPRA certified. Steel erection, precast placement, facade lifts. Crew included.',4.9,87,'men/77'],
  ['Tanker · Water/Fuel','Site Utilities Co.','bi-droplet','Water & Fuel Delivery','$55/load','$190/day','AVAIL.','10,000-litre water tanker. Site water supply, curing water, dust suppression. Fuel tanker variant available.',4.7,263,'men/88'],
];
$so = 0;
foreach ($vehicles as [$name,$owner,$icon,$type,$trip,$dayr,$status,$desc,$rat,$trips,$portrait]) {
  db_insert("INSERT INTO listings (public_id,kind,title,vendor_name,icon,category,price,price_secondary,avail_label,description,rating,usage_count,specs,region_id,sort_order) VALUES (?,'transport',?,?,?,?,?,?,?,?,?,?,?,?,?)",
    [uuid4(),$name,$owner,$icon,$type,$trip,$dayr,$status,$desc,$rat,$trips,json_encode(['portrait'=>$portrait]),$regKE,$so++]);
}

// ── Facilities ──
$facilities = [
  ['Westlands Site Office Complex','Modular 8-office block with boardroom, kitchenette, ablutions and Fibre internet. Raised floor, CCTV, 24hr security.','Nairobi · Westlands','KES 95,000/mo','https://picsum.photos/id/260/600/400','Site Office','3 units avail.','bi-building','Furnished,CCTV,Fibre,Parking'],
  ['Athi River Bonded Warehouse','2,400m2 bonded warehouse with 6 loading bays, crane gantry, cold-room annex and 24hr security. Customs agents on site.','Machakos · Athi River','KES 320,000/mo','https://picsum.photos/id/329/600/400','Warehouse','1 unit avail.','bi-building-up','Loading Bays,Crane,Cold-Room,Bonded'],
  ['Ruiru Batching Plant Yard','Serviced 1.5-acre yard with installed batching plant, mixer bay, weigh bridge and admin office. Power and water on-site.','Kiambu · Ruiru','KES 240,000/mo','https://picsum.photos/id/366/600/400','Batching Yard','2 units avail.','bi-gear','Batching Plant,Weighbridge,Power,Water'],
  ['Miritini Container Storage','Port-adjacent 3-acre bonded yard. Container stackers, hardstand parking and customs clearing agents. Monthly or annual.','Mombasa · Miritini','KES 180,000/mo','https://picsum.photos/id/119/600/400','Storage Yard','Available now','bi-box','Bonded,Stackers,Port Access,CCTV'],
  ['Karen Road Workshop & Stores','Fitted workshop with 4 vehicle/plant bays, 2-tonne overhead crane, parts store, staff room and admin. 3-phase power.','Nairobi · Karen','KES 145,000/mo','https://picsum.photos/id/250/600/400','Workshop','Available','bi-tools','4 Bays,3-Phase,Crane,Stores'],
  ['Thika Road Labour Camp','120-bed pre-engineered camp. Dormitories, ablution blocks, kitchen, dining hall and recreation area. Large-project ready.','Kiambu · Thika Rd','KES 280,000/mo','https://picsum.photos/id/292/600/400','Labour Camp','1 avail.','bi-house-door','120 Beds,Kitchen,Security,Medical Bay'],
];
$so = 0;
foreach ($facilities as [$name,$desc,$loc,$price,$img,$type,$avail,$icon,$tags]) {
  db_insert("INSERT INTO listings (public_id,kind,title,description,location,price,image_url,category,avail_label,icon,tags,region_id,sort_order) VALUES (?,'facilities',?,?,?,?,?,?,?,?,?,?,?)",
    [uuid4(),$name,$desc,$loc,$price,$img,$type,$avail,$icon,$tags,$regKE,$so++]);
}

echo "Seeded suppliers=" . db_value("SELECT COUNT(*) FROM suppliers") . " listings=" . db_value("SELECT COUNT(*) FROM listings") .
     " (materials=" . db_value("SELECT COUNT(*) FROM listings WHERE kind='materials'") .
     ", equipment=" . db_value("SELECT COUNT(*) FROM listings WHERE kind='equipment'") .
     ", transport=" . db_value("SELECT COUNT(*) FROM listings WHERE kind='transport'") .
     ", facilities=" . db_value("SELECT COUNT(*) FROM listings WHERE kind='facilities'") . ")\n";
