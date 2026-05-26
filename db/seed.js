'use strict';
const db = require('./database');
const bcrypt = require('bcryptjs');

console.log('Seeding BuildLink database...');

// Clear existing data
db.exec(`
  DELETE FROM notifications; DELETE FROM cart; DELETE FROM saved_professionals;
  DELETE FROM material_prices; DELETE FROM invoices; DELETE FROM reviews;
  DELETE FROM messages; DELETE FROM transport; DELETE FROM equipment;
  DELETE FROM materials; DELETE FROM bid_applications; DELETE FROM bid_trades;
  DELETE FROM bids; DELETE FROM project_invitations; DELETE FROM project_team;
  DELETE FROM projects; DELETE FROM professional_tags; DELETE FROM professional_profiles;
  DELETE FROM users;
`);

const hash = bcrypt.hashSync('password123', 10);

// Insert users (all professionals + demo client)
const insertUser = db.prepare(`INSERT INTO users (name,email,password_hash,role,location,avatar,bio,verified,verification_badge,rating,review_count) VALUES (?,?,?,?,?,?,?,?,?,?,?)`);

// Demo client
const clientId = insertUser.run('John Kariuki','john.k@karidevelopers.co.ke',hash,'client','Nairobi, Kenya','https://i.pravatar.cc/100?img=22','Property Developer | BuildLink Member since 2024',1,null,0,0).lastInsertRowid;

// Professionals
const pros = [
  {name:'James Kamau',email:'james.kamau@buildlink.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=11',bio:'NCA Grade 5 Licensed Civil & Structural Engineer with 12 years delivering high-rise, residential, and infrastructure projects across Kenya, Uganda, and Tanzania.',verified:1,badge:'Elite Pro',rating:4.9,reviews:214,trade:'Civil & Structural Engineer',exp:12,nca:'G5',rate:8500,jobs:187,tags:['Structural Design','AutoCAD','Foundation Engineering','High-Rise','Site Supervision']},
  {name:'Sarah Njoroge',email:'sarah.njoroge@arch.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=5',bio:'Award-winning architect specialising in commercial and residential design across East Africa.',verified:1,badge:'Top Rated',rating:4.8,reviews:163,trade:'Architect & Interior Designer',exp:9,nca:'G4',rate:7200,jobs:142,tags:['ArchiCAD','Revit','3D Rendering','Commercial','Interior Design']},
  {name:'Mohamed Osman',email:'m.osman@mepeng.co.ke',location:'Mombasa, Kenya',avatar:'https://i.pravatar.cc/100?img=15',bio:'MEP Engineer with expertise in plumbing, electrical, HVAC, and fire systems for commercial developments.',verified:1,badge:null,rating:4.7,reviews:98,trade:'MEP Engineer',exp:8,nca:'G3',rate:6800,jobs:109,tags:['Plumbing','Electrical','HVAC','Fire Systems']},
  {name:'Grace Wanjiku',email:'gwanjiku@qs.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=9',bio:'Chartered Quantity Surveyor delivering precise cost planning and tender management across East Africa.',verified:1,badge:'Top Rated',rating:4.9,reviews:241,trade:'Quantity Surveyor',exp:11,nca:'G4',rate:5500,jobs:228,tags:['Cost Estimation','BoQ','Tender','Valuation']},
  {name:'David Mwangi',email:'d.mwangi@pm.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=12',bio:'PMP-certified Project Manager with 15 years overseeing multi-million dollar construction projects.',verified:1,badge:'Elite Pro',rating:4.8,reviews:177,trade:'Project Manager',exp:15,nca:'G5',rate:9000,jobs:156,tags:['MS Project','Risk Mgmt','Site Supervision','PMP']},
  {name:'Aisha Abdi',email:'aisha.abdi@landscape.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=7',bio:'Landscape Architect creating sustainable outdoor spaces and irrigation systems.',verified:1,badge:null,rating:4.6,reviews:88,trade:'Landscape Architect',exp:7,nca:'G3',rate:4800,jobs:74,tags:['Landscape Design','AutoCAD','Irrigation','Planting']},
  {name:'Peter Otieno',email:'p.otieno@electrical.co.ke',location:'Kisumu, Kenya',avatar:'https://i.pravatar.cc/100?img=14',bio:'Electrical Engineer specialising in HV systems, solar installations, and industrial electrical works.',verified:1,badge:'Rising Star',rating:4.7,reviews:134,trade:'Electrical Engineer',exp:6,nca:'G3',rate:5200,jobs:118,tags:['HV Systems','Solar','Panel Design','Testing']},
  {name:'Fatuma Hassan',email:'fatuma.h@interior.co.ke',location:'Mombasa, Kenya',avatar:'https://i.pravatar.cc/100?img=6',bio:'Interior Designer with expertise in FF&E, commercial, and hospitality spaces.',verified:1,badge:'Top Rated',rating:4.9,reviews:196,trade:'Interior Designer',exp:10,nca:'G4',rate:6500,jobs:183,tags:['3D Viz','FF&E','Commercial','Hospitality']},
  {name:'Eric Mutua',email:'emutua@geotech.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=17',bio:'Geotechnical Engineer specialising in soil testing and foundation design.',verified:0,badge:null,rating:4.5,reviews:62,trade:'Geotechnical Engineer',exp:5,nca:'G2',rate:7800,jobs:54,tags:['Soil Testing','Foundation','Slope Stability']},
  {name:'Rose Achieng',email:'r.achieng@planning.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=10',bio:'Urban Planner specialising in GIS, zoning, EIA and master planning for municipalities.',verified:1,badge:'Certified',rating:4.8,reviews:111,trade:'Urban Planner',exp:9,nca:'G4',rate:6000,jobs:97,tags:['GIS','Zoning','EIA','Master Planning']},
  {name:'Hassan Abdi',email:'hassana@foreman.co.ke',location:'Nairobi, Kenya',avatar:'https://i.pravatar.cc/100?img=16',bio:'Experienced construction foreman with 20+ years managing large site operations.',verified:1,badge:null,rating:4.6,reviews:189,trade:'Construction Foreman',exp:20,nca:'G2',rate:3200,jobs:342,tags:['Site Management','Labour','Safety','QC']},
  {name:'Linda Chebet',email:'l.chebet@water.co.ke',location:'Eldoret, Kenya',avatar:'https://i.pravatar.cc/100?img=8',bio:'Water & Sanitation Engineer delivering borehole, drainage, and WASH projects.',verified:1,badge:null,rating:4.7,reviews:77,trade:'Water & Sanitation Engineer',exp:8,nca:'G3',rate:5800,jobs:66,tags:['Borehole','Drainage','WASH','Water Treatment']},
];

const insertPro = db.prepare(`INSERT INTO professional_profiles (user_id,trade,experience_years,nca_grade,hourly_rate,available,jobs_done,on_time_percent) VALUES (?,?,?,?,?,1,?,98)`);
const insertTag = db.prepare(`INSERT INTO professional_tags (user_id,tag) VALUES (?,?)`);

const proIds = [];
pros.forEach(p => {
  const uid = insertUser.run(p.name,p.email,hash,'professional',p.location,p.avatar,p.bio,p.verified,p.badge,p.rating,p.reviews).lastInsertRowid;
  proIds.push(uid);
  insertPro.run(uid,p.trade,p.exp,p.nca,p.rate,p.jobs);
  p.tags.forEach(t => insertTag.run(uid,t));
});

// Materials
const insertMat = db.prepare(`INSERT INTO materials (name,category,specification,supplier_name,supplier_verified,supplier_rating,price,unit,stock_status,min_order,delivery_speed,badge,image_url,tags,location) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`);
const mats = [
  {name:'OPC Cement 42.5N (50kg)',cat:'Cement & Concrete',spec:'BS EN 197-1 | East African Portland',sup:'Bamburi Cement Ltd',price:980,unit:'per bag',stock:'in',min:'10 bags',del:'Same Day',badge:'bulk',img:'https://picsum.photos/seed/cement1/400/280',tags:'["42.5N","OPC","Portland"]'},
  {name:'Y12 Steel Rebar (12mm)',cat:'Steel & Rebar',spec:'KS 3196:2017 | Grade 460B | 12m length',sup:'Steel Structures Ltd',price:1536,unit:'per 12m bar',stock:'in',min:'50 bars',del:'Next Day',badge:'sale',img:'https://picsum.photos/seed/steel2/400/280',tags:'["Y12","Rebar","High-Yield"]'},
  {name:'Hardwood Timber 2"×4"',cat:'Timber & Lumber',spec:'Cypress | Air-dried | 4m length',sup:'Kenya Timber Merchants',price:320,unit:'per pc',stock:'low',min:'20 pcs',del:'2-3 Days',badge:null,img:'https://picsum.photos/seed/timber3/400/280',tags:'["Cypress","Timber","Structural"]'},
  {name:'Hollow Concrete Blocks 6"',cat:'Blocks & Bricks',spec:'150×200×400mm | 7N/mm² strength',sup:'QualiBlocks Kenya',price:55,unit:'per block',stock:'in',min:'500 blocks',del:'Same Day',badge:'bulk',img:'https://picsum.photos/seed/blocks4/400/280',tags:'["Hollow","6-Inch","Concrete"]'},
  {name:'Aluzinc Roofing Sheet 0.5mm',cat:'Roofing',spec:'Zincalume AZ150 | Box profile | 3.6m',sup:'Mabati Rolling Mills',price:2952,unit:'per sheet',stock:'in',min:'10 sheets',del:'Next Day',badge:'new',img:'https://picsum.photos/seed/roofing5/400/280',tags:'["Aluzinc","Box Profile","Roofing"]'},
  {name:'2.5mm² PVC Electrical Cable',cat:'Electrical',spec:'1 Core | Copper | 100m roll | IEC 60227',sup:'Nexans Kenya',price:3800,unit:'per 100m roll',stock:'in',min:'5 rolls',del:'Same Day',badge:null,img:'https://picsum.photos/seed/cable6/400/280',tags:'["PVC","2.5mm²","Electrical"]'},
  {name:'110mm uPVC Drainage Pipe',cat:'Plumbing & Pipes',spec:'SN4 | 6m length | BS EN 1401',sup:'Elson Plumbing Supplies',price:480,unit:'per 6m pipe',stock:'in',min:'20 pipes',del:'Same Day',badge:null,img:'https://picsum.photos/seed/pipe7/400/280',tags:'["uPVC","110mm","Drainage"]'},
  {name:'Vitrified Floor Tiles 60×60cm',cat:'Tiles & Flooring',spec:'Matt finish | R9 slip rating | Italian design',sup:'Classic Ceramics EA',price:1400,unit:'per m²',stock:'in',min:'50 m²',del:'2-3 Days',badge:'sale',img:'https://picsum.photos/seed/tiles8/400/280',tags:'["Vitrified","60x60","Floor Tiles"]'},
  {name:'Dulux Weathershield 20L',cat:'Paints & Finishes',spec:'Exterior | 10yr protection | White base',sup:'AkzoNobel Kenya',price:8200,unit:'per 20L tin',stock:'low',min:'4 tins',del:'Same Day',badge:null,img:'https://picsum.photos/seed/paint9/400/280',tags:'["Dulux","Exterior","Weathershield"]'},
  {name:'Crushed Stone Ballast 20mm',cat:'Aggregates',spec:'Granite | Clean washed | Per tonne',sup:'Athi River Quarry',price:3200,unit:'per tonne',stock:'in',min:'5 tonnes',del:'Next Day',badge:'bulk',img:'https://picsum.photos/seed/ballast10/400/280',tags:'["Ballast","20mm","Granite"]'},
  {name:'Float Glass 6mm Clear',cat:'Glass & Glazing',spec:'Clear | 2440×1220mm sheet',sup:'Glass & Aluminium Kenya',price:620,unit:'per m²',stock:'in',min:'20 m²',del:'2-3 Days',badge:null,img:'https://picsum.photos/seed/glass11/400/280',tags:'["Float Glass","6mm","Clear"]'},
  {name:'Bitumen Waterproof Membrane',cat:'Waterproofing',spec:'SBS modified | 4mm | Torch-on | 10m²/roll',sup:'Tremco Roofing EA',price:2400,unit:'per roll',stock:'in',min:'10 rolls',del:'Next Day',badge:null,img:'https://picsum.photos/seed/waterproof12/400/280',tags:'["Bitumen","SBS","Torch-on"]'},
];
mats.forEach(m => insertMat.run(m.name,m.cat,m.spec,m.sup,1,'★★★★★',m.price,m.unit,m.stock,m.min,m.del,m.badge||null,m.img,m.tags,'Nairobi, Kenya'));

// Equipment
const insertEq = db.prepare(`INSERT INTO equipment (owner_name,name,category,icon,hourly_rate,daily_rate,weekly_rate,available,location,specs,rating,review_count) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)`);
const eqs = [
  {own:'HeavyLift EA',name:'CAT 320 Excavator',cat:'Earthmoving',ico:'🚜',hr:18500,dr:98000,wr:580000,avail:1,specs:'{"Power":"110kW","Bucket":"0.9m³","Weight":"20.6t","Reach":"9.5m"}',rat:4.8,rev:94},
  {own:'Crane Masters Kenya',name:'Tower Crane TC6516',cat:'Lifting',ico:'🏗',hr:22000,dr:120000,wr:680000,avail:1,specs:'{"Capacity":"8t","Height":"65m","Jib":"60m","Radius":"16m"}',rat:4.9,rev:67},
  {own:'Equipment Hub KE',name:'Concrete Mixer 500L',cat:'Concrete',ico:'🔄',hr:2800,dr:15000,wr:88000,avail:0,specs:'{"Capacity":"500L","Engine":"12HP","Drum":"350rpm","Output":"2.5m³/hr"}',rat:4.6,rev:142},
  {own:'Access Equipment EA',name:'Scissor Lift 12m',cat:'Access',ico:'🔼',hr:3500,dr:18000,wr:105000,avail:1,specs:'{"Height":"12m","Capacity":"450kg","Width":"1.2m","Power":"Electric"}',rat:4.7,rev:88},
  {own:'Road Masters EA',name:'Compactor Roller 10T',cat:'Compaction',ico:'🛞',hr:6500,dr:35000,wr:200000,avail:1,specs:'{"Weight":"10t","Width":"1.8m","Speed":"12km/h","Engine":"Diesel"}',rat:4.5,rev:56},
  {own:'PowerGen Africa',name:'Generator 100kVA Perkins',cat:'Power',ico:'⚡',hr:4800,dr:25000,wr:145000,avail:1,specs:'{"Power":"100kVA","Fuel":"Diesel","Consumption":"18L/hr","Tank":"200L"}',rat:4.8,rev:203},
];
eqs.forEach(e => insertEq.run(e.own,e.name,e.cat,e.ico,e.hr,e.dr,e.wr,e.avail,'Nairobi, Kenya',e.specs,e.rat,e.rev));

// Transport
const insertTr = db.prepare(`INSERT INTO transport (name,type,icon,rating,review_count,rate_text,location,capacity,modes) VALUES (?,?,?,?,?,?,?,?,?)`);
const trs = [
  {name:'KenTrans Logistics',type:'Heavy Haulage',ico:'🚛',rat:4.8,rev:312,rate:'KES 180/km',loc:'Nairobi',cap:'30 tonnes',modes:'["available","contract","tripbased"]'},
  {name:'FastBuild Deliveries',type:'Construction Materials',ico:'🚚',rat:4.7,rev:188,rate:'KES 12,000/trip',loc:'Nairobi',cap:'10 tonnes',modes:'["available","tripbased"]'},
  {name:'Crane & Hoist Movers',type:'Oversize & Machinery',ico:'🏗',rat:4.9,rev:94,rate:'KES 45,000/trip',loc:'Nairobi',cap:'80 tonnes',modes:'["contract"]'},
  {name:'QuickLoad Express',type:'Mixed Cargo',ico:'📦',rat:4.6,rev:224,rate:'KES 8,500/trip',loc:'Mombasa',cap:'5 tonnes',modes:'["available","tripbased"]'},
];
trs.forEach(t => insertTr.run(t.name,t.type,t.ico,t.rat,t.rev,t.rate,t.loc,t.cap,t.modes));

// Demo projects for the client
const insertProj = db.prepare(`INSERT INTO projects (owner_id,name,description,type,location,budget_min,budget_max,status,phase,progress,contractor,due_date,thumb_url,value,duration,tags) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`);
const p1id = insertProj.run(clientId,'3-Bedroom Bungalow — Karen','Luxury 3-bed bungalow with swimming pool and servant quarters','Residential','Karen, Nairobi',7000000,8500000,'ongoing','Roofing',68,'Apex Construction','2026-08-31','https://picsum.photos/seed/up1/300/200','KES 8.5M','18 months','["Residential","Luxury","Bungalow"]').lastInsertRowid;
const p2id = insertProj.run(clientId,'Office Extension — Westlands','2-floor office extension to existing 3-storey commercial block','Commercial','Westlands, Nairobi',1800000,2100000,'planning','Design',12,'Pending Award','2026-12-31','https://picsum.photos/seed/up2/300/200','KES 2.1M','8 months','["Commercial","Office","Extension"]').lastInsertRowid;
const p3id = insertProj.run(clientId,'Perimeter Wall — Ruaka','Precast perimeter wall with electric fence and automated gate','Residential','Ruaka, Kiambu',600000,680000,'completed','Completed',100,'BuildRight Ltd','2026-03-31','https://picsum.photos/seed/up3/300/200','KES 680K','3 months','["Security","Wall","Residential"]').lastInsertRowid;

// Add professionals to project 1 team
const insertTeam = db.prepare(`INSERT OR IGNORE INTO project_team (project_id,user_id,role) VALUES (?,?,?)`);
insertTeam.run(p1id, proIds[0], 'Civil Engineer');
insertTeam.run(p1id, proIds[3], 'Quantity Surveyor');
insertTeam.run(p1id, proIds[4], 'Project Manager');

// Bids
const insertBid = db.prepare(`INSERT INTO bids (poster_id,title,description,owner_name,budget_min,budget_max,location,deadline_days,urgency,applications_count) VALUES (?,?,?,?,?,?,?,?,?,?)`);
const insertBidTrade = db.prepare(`INSERT INTO bid_trades (bid_id,trade) VALUES (?,?)`);
const bidData = [
  {title:'Construction of 12-Unit Apartment Block',own:'Kamau Developers Ltd',min:45000000,max:60000000,loc:'Kilimani, Nairobi',days:3,urg:'hot',apps:18,trades:['Civil Engineering','Architecture','Electrical']},
  {title:'Commercial Office Fit-Out (3,500 sqm)',own:'Strathmore Business Park',min:8000000,max:12000000,loc:'Madaraka, Nairobi',days:7,urg:'new',apps:9,trades:['Interior Design','MEP','Partitioning']},
  {title:'Borehole Drilling & Water Supply',own:'Mugumo Investors',min:800000,max:1200000,loc:'Thika, Kenya',days:5,urg:'warm',apps:7,trades:['Water Engineering','Electrical']},
  {title:'Factory Roofing Replacement (2,400 sqm)',own:'Bidco Africa Ltd',min:3500000,max:5000000,loc:'Thika Road',days:2,urg:'hot',apps:22,trades:['Roofing','Structural Engineering']},
  {title:'Hospital Extension Block (G+4)',own:'Aga Khan Health Services',min:180000000,max:250000000,loc:'Parklands, Nairobi',days:14,urg:'new',apps:5,trades:['Architecture','Civil','MEP','QS']},
  {title:'Residential Perimeter Wall & Gate',own:'Private Client',min:400000,max:700000,loc:'Lavington, Nairobi',days:4,urg:'warm',apps:14,trades:['Civil Engineering','Masonry']},
];
bidData.forEach(b => {
  const bid_id = insertBid.run(clientId,b.title,'',b.own,b.min,b.max,b.loc,b.days,b.urg,b.apps).lastInsertRowid;
  b.trades.forEach(t => insertBidTrade.run(bid_id,t));
});

// Messages
const insertMsg = db.prepare(`INSERT INTO messages (sender_id,recipient_id,project_id,content,read,created_at) VALUES (?,?,?,?,?,?)`);
insertMsg.run(proIds[0],clientId,p1id,'I can start the foundation assessment this Friday. Please send the site drawings and soil report.',0, new Date(Date.now()-2*60000).toISOString());
insertMsg.run(clientId,proIds[0],p1id,'Will send across today. The soil report from 2024 is still valid.',1, new Date(Date.now()-60*60000).toISOString());
insertMsg.run(proIds[3],clientId,p1id,'Revised BoQ attached. Main change is in the finishes section — tiles upgraded from standard to Italian vitrified.',0, new Date(Date.now()-3*3600000).toISOString());
insertMsg.run(proIds[4],clientId,p1id,'Site visit report for Karen project attached. Overall progress is on track at 68%.',1, new Date(Date.now()-24*3600000).toISOString());

// Invoices
const insertInv = db.prepare(`INSERT INTO invoices (project_id,issuer_id,recipient_id,amount,currency,milestone,status,escrow_status,due_date) VALUES (?,?,?,?,?,?,?,?,?)`);
insertInv.run(p1id,proIds[0],clientId,34000,'KES','Foundation Assessment Review','pending','held','2026-05-27');
insertInv.run(p1id,proIds[3],clientId,22000,'KES','BoQ Preparation — Revised','pending','held','2026-05-30');
insertInv.run(p1id,proIds[4],clientId,45000,'KES','Project Management — May 2026','approved','held','2026-06-01');
insertInv.run(p3id,proIds[0],clientId,15000,'KES','Final Structural Sign-off','paid','released','2026-03-28');

// Material prices
const insertPrice = db.prepare(`INSERT INTO material_prices (item,price,unit,change_pct,direction) VALUES (?,?,?,?,?)`);
const prices = [
  {item:'OPC Cement 42.5N',price:'KES 980',unit:'/50kg bag',chg:'+2.1%',dir:'up'},
  {item:'Y12 Steel Rebar',price:'KES 128',unit:'/kg',chg:'-0.8%',dir:'dn'},
  {item:'Cypress Timber 2×4"',price:'KES 320',unit:'/pc',chg:'+1.4%',dir:'up'},
  {item:'Hollow Block 6"',price:'KES 55',unit:'/block',chg:'0%',dir:'flat'},
  {item:'Aluzinc Roofing 0.5mm',price:'KES 820',unit:'/m²',chg:'+0.6%',dir:'up'},
  {item:'PVC Cable 2.5mm²',price:'KES 38',unit:'/m',chg:'-1.2%',dir:'dn'},
  {item:'uPVC Pipe 110mm',price:'KES 480',unit:'/6m',chg:'0%',dir:'flat'},
  {item:'Vitrified Tiles 60×60',price:'KES 1,400',unit:'/m²',chg:'-0.5%',dir:'dn'},
  {item:'Ballast 20mm Granite',price:'KES 3,200',unit:'/tonne',chg:'+0.3%',dir:'up'},
  {item:'Bitumen Membrane SBS',price:'KES 2,400',unit:'/roll',chg:'+1.8%',dir:'up'},
];
prices.forEach(p => insertPrice.run(p.item,p.price,p.unit,p.chg,p.dir));

// Notifications for demo client
const insertNotif = db.prepare(`INSERT INTO notifications (user_id,type,title,body,link) VALUES (?,?,?,?,?)`);
insertNotif.run(clientId,'payment','Invoice Pending Approval','James Kamau submitted KES 34,000 invoice for foundation assessment.','/dashboard/');
insertNotif.run(clientId,'invitation','Project Invitation','Grace Wanjiku has accepted your invitation to Karen Bungalow project.','/dashboard/');
insertNotif.run(clientId,'message','New Message','James Kamau: I can start the foundation assessment this Friday...','/dashboard/messages.html');
insertNotif.run(clientId,'system','Account Upgraded','Your account has been upgraded to Professional Verified status.','/dashboard/');

console.log('✅ BuildLink database seeded successfully!');
console.log(`   Demo login: john.k@karidevelopers.co.ke / password123`);
console.log(`   ${pros.length} professionals | ${mats.length} materials | ${eqs.length} equipment | ${bidData.length} bids`);
