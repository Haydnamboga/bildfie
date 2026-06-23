/*
 * bildfie — Static Demo Data (used when API is offline)
 */
'use strict';
const BL = window.BL || {};

BL.professionals = [
  { id:1, name:'James Kamau', trade:'Civil & Structural Engineer', rating:4.9, review_count:214, hourly_rate:8500, rate_currency:'KES', jobs_done:187, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=11', verified:1, verification_badge:'Elite Pro', available:1, tags:['Structural Design','AutoCAD','Foundation Engineering','High-Rise','Site Supervision'] },
  { id:2, name:'Sarah Njoroge', trade:'Architect & Interior Designer', rating:4.8, review_count:163, hourly_rate:7200, rate_currency:'KES', jobs_done:142, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=5', verified:1, verification_badge:'Top Rated', available:1, tags:['ArchiCAD','Revit','3D Rendering','Commercial','Interior Design'] },
  { id:3, name:'Mohamed Osman', trade:'MEP Engineer', rating:4.7, review_count:98, hourly_rate:6800, rate_currency:'KES', jobs_done:109, location:'Mombasa, Kenya', avatar:'https://i.pravatar.cc/100?img=15', verified:1, verification_badge:null, available:0, tags:['Plumbing','Electrical','HVAC','Fire Systems'] },
  { id:4, name:'Grace Wanjiku', trade:'Quantity Surveyor', rating:4.9, review_count:241, hourly_rate:5500, rate_currency:'KES', jobs_done:228, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=9', verified:1, verification_badge:'Top Rated', available:1, tags:['Cost Estimation','BoQ','Tender','Valuation'] },
  { id:5, name:'David Mwangi', trade:'Project Manager', rating:4.8, review_count:177, hourly_rate:9000, rate_currency:'KES', jobs_done:156, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=12', verified:1, verification_badge:'Elite Pro', available:1, tags:['MS Project','Risk Mgmt','Site Supervision','PMP'] },
  { id:6, name:'Aisha Abdi', trade:'Landscape Architect', rating:4.6, review_count:88, hourly_rate:4800, rate_currency:'KES', jobs_done:74, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=7', verified:1, verification_badge:null, available:1, tags:['Landscape Design','AutoCAD','Irrigation','Planting'] },
  { id:7, name:'Peter Otieno', trade:'Electrical Engineer', rating:4.7, review_count:134, hourly_rate:5200, rate_currency:'KES', jobs_done:118, location:'Kisumu, Kenya', avatar:'https://i.pravatar.cc/100?img=14', verified:1, verification_badge:'Rising Star', available:1, tags:['HV Systems','Solar','Panel Design','Testing'] },
  { id:8, name:'Fatuma Hassan', trade:'Interior Designer', rating:4.9, review_count:196, hourly_rate:6500, rate_currency:'KES', jobs_done:183, location:'Mombasa, Kenya', avatar:'https://i.pravatar.cc/100?img=6', verified:1, verification_badge:'Top Rated', available:1, tags:['3D Viz','FF&E','Commercial','Hospitality'] },
  { id:9, name:'Eric Mutua', trade:'Geotechnical Engineer', rating:4.5, review_count:62, hourly_rate:7800, rate_currency:'KES', jobs_done:54, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=17', verified:0, verification_badge:null, available:0, tags:['Soil Testing','Foundation','Slope Stability'] },
  { id:10, name:'Rose Achieng', trade:'Urban Planner', rating:4.8, review_count:111, hourly_rate:6000, rate_currency:'KES', jobs_done:97, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=10', verified:1, verification_badge:'Certified', available:1, tags:['GIS','Zoning','EIA','Master Planning'] },
  { id:11, name:'Hassan Abdi', trade:'Construction Foreman', rating:4.6, review_count:189, hourly_rate:3200, rate_currency:'KES', jobs_done:342, location:'Nairobi, Kenya', avatar:'https://i.pravatar.cc/100?img=16', verified:1, verification_badge:null, available:1, tags:['Site Management','Labour','Safety','QC'] },
  { id:12, name:'Linda Chebet', trade:'Water & Sanitation Engineer', rating:4.7, review_count:77, hourly_rate:5800, rate_currency:'KES', jobs_done:66, location:'Eldoret, Kenya', avatar:'https://i.pravatar.cc/100?img=8', verified:1, verification_badge:null, available:1, tags:['Borehole','Drainage','WASH','Water Treatment'] },
];

BL.materials = [
  { id:1, name:'OPC Cement 42.5N (50kg)', category:'Cement & Concrete', specification:'BS EN 197-1 | East African Portland', supplier_name:'Bamburi Cement Ltd', supplier_verified:1, supplier_rating:'★★★★★', price:980, unit:'per bag', stock_status:'in', min_order:'10 bags', delivery_speed:'Same Day', badge:'bulk', image_url:'https://picsum.photos/seed/cement1/400/280', location:'Nairobi, Kenya', tags:['42.5N','OPC','Portland'] },
  { id:2, name:'Y12 Steel Rebar (12mm)', category:'Steel & Rebar', specification:'KS 3196:2017 | Grade 460B | 12m length', supplier_name:'Steel Structures Ltd', supplier_verified:1, supplier_rating:'★★★★½', price:1536, unit:'per 12m bar', stock_status:'in', min_order:'50 bars', delivery_speed:'Next Day', badge:'sale', image_url:'https://picsum.photos/seed/steel2/400/280', location:'Nairobi, Kenya', tags:['Y12','Rebar','High-Yield'] },
  { id:3, name:'Hardwood Timber 2"×4"', category:'Timber & Lumber', specification:'Cypress | Air-dried | 4m length', supplier_name:'Kenya Timber Merchants', supplier_verified:1, supplier_rating:'★★★★', price:320, unit:'per pc', stock_status:'low', min_order:'20 pcs', delivery_speed:'2-3 Days', badge:null, image_url:'https://picsum.photos/seed/timber3/400/280', location:'Nairobi, Kenya', tags:['Cypress','Timber','Structural'] },
  { id:4, name:'Hollow Concrete Blocks 6"', category:'Blocks & Bricks', specification:'150×200×400mm | 7N/mm² strength', supplier_name:'QualiBlocks Kenya', supplier_verified:1, supplier_rating:'★★★★½', price:55, unit:'per block', stock_status:'in', min_order:'500 blocks', delivery_speed:'Same Day', badge:'bulk', image_url:'https://picsum.photos/seed/blocks4/400/280', location:'Nairobi, Kenya', tags:['Hollow','6-Inch','Concrete'] },
  { id:5, name:'Aluzinc Roofing Sheet 0.5mm', category:'Roofing', specification:'Zincalume AZ150 | Box profile | 3.6m', supplier_name:'Mabati Rolling Mills', supplier_verified:1, supplier_rating:'★★★★★', price:2952, unit:'per sheet', stock_status:'in', min_order:'10 sheets', delivery_speed:'Next Day', badge:'new', image_url:'https://picsum.photos/seed/roofing5/400/280', location:'Nairobi, Kenya', tags:['Aluzinc','Box Profile','Roofing'] },
  { id:6, name:'2.5mm² PVC Electrical Cable', category:'Electrical', specification:'1 Core | Copper | 100m roll | IEC 60227', supplier_name:'Nexans Kenya', supplier_verified:1, supplier_rating:'★★★★', price:3800, unit:'per 100m roll', stock_status:'in', min_order:'5 rolls', delivery_speed:'Same Day', badge:null, image_url:'https://picsum.photos/seed/cable6/400/280', location:'Nairobi, Kenya', tags:['PVC','2.5mm²','Electrical'] },
  { id:7, name:'110mm uPVC Drainage Pipe', category:'Plumbing & Pipes', specification:'SN4 | 6m length | BS EN 1401', supplier_name:'Elson Plumbing Supplies', supplier_verified:1, supplier_rating:'★★★★', price:480, unit:'per 6m pipe', stock_status:'in', min_order:'20 pipes', delivery_speed:'Same Day', badge:null, image_url:'https://picsum.photos/seed/pipe7/400/280', location:'Nairobi, Kenya', tags:['uPVC','110mm','Drainage'] },
  { id:8, name:'Vitrified Floor Tiles 60×60cm', category:'Tiles & Flooring', specification:'Matt finish | R9 slip rating | Italian design', supplier_name:'Classic Ceramics EA', supplier_verified:1, supplier_rating:'★★★★★', price:1400, unit:'per m²', stock_status:'in', min_order:'50 m²', delivery_speed:'2-3 Days', badge:'sale', image_url:'https://picsum.photos/seed/tiles8/400/280', location:'Nairobi, Kenya', tags:['Vitrified','60x60','Floor Tiles'] },
  { id:9, name:'Dulux Weathershield 20L', category:'Paints & Finishes', specification:'Exterior | 10yr protection | White base', supplier_name:'AkzoNobel Kenya', supplier_verified:1, supplier_rating:'★★★★½', price:8200, unit:'per 20L tin', stock_status:'low', min_order:'4 tins', delivery_speed:'Same Day', badge:null, image_url:'https://picsum.photos/seed/paint9/400/280', location:'Nairobi, Kenya', tags:['Dulux','Exterior','Weathershield'] },
  { id:10, name:'Crushed Stone Ballast 20mm', category:'Aggregates', specification:'Granite | Clean washed | Per tonne', supplier_name:'Athi River Quarry', supplier_verified:1, supplier_rating:'★★★★', price:3200, unit:'per tonne', stock_status:'in', min_order:'5 tonnes', delivery_speed:'Next Day', badge:'bulk', image_url:'https://picsum.photos/seed/ballast10/400/280', location:'Nairobi, Kenya', tags:['Ballast','20mm','Granite'] },
  { id:11, name:'Float Glass 6mm Clear', category:'Glass & Glazing', specification:'Clear | 2440×1220mm sheet', supplier_name:'Glass & Aluminium Kenya', supplier_verified:1, supplier_rating:'★★★★', price:620, unit:'per m²', stock_status:'in', min_order:'20 m²', delivery_speed:'2-3 Days', badge:null, image_url:'https://picsum.photos/seed/glass11/400/280', location:'Nairobi, Kenya', tags:['Float Glass','6mm','Clear'] },
  { id:12, name:'Bitumen Waterproof Membrane', category:'Waterproofing', specification:'SBS modified | 4mm | Torch-on | 10m²/roll', supplier_name:'Tremco Roofing EA', supplier_verified:1, supplier_rating:'★★★★★', price:2400, unit:'per roll', stock_status:'in', min_order:'10 rolls', delivery_speed:'Next Day', badge:null, image_url:'https://picsum.photos/seed/waterproof12/400/280', location:'Nairobi, Kenya', tags:['Bitumen','SBS','Torch-on'] },
];

BL.equipment = [
  { id:1, name:'CAT 320 Excavator', category:'Earthmoving', icon:'🚜', available:1, specs:'{"Power":"110kW","Bucket":"0.9m³","Weight":"20.6t","Reach":"9.5m"}', hourly_rate:18500, daily_rate:98000, weekly_rate:580000, rating:4.8, review_count:94, location:'Nairobi, Kenya', owner_name:'HeavyLift EA' },
  { id:2, name:'Tower Crane TC6516', category:'Lifting', icon:'🏗', available:1, specs:'{"Capacity":"8t","Height":"65m","Jib":"60m","Radius":"16m"}', hourly_rate:22000, daily_rate:120000, weekly_rate:680000, rating:4.9, review_count:67, location:'Nairobi, Kenya', owner_name:'Crane Masters Kenya' },
  { id:3, name:'Concrete Mixer 500L', category:'Concrete', icon:'🔄', available:0, specs:'{"Capacity":"500L","Engine":"12HP","Drum":"350rpm","Output":"2.5m³/hr"}', hourly_rate:2800, daily_rate:15000, weekly_rate:88000, rating:4.6, review_count:142, location:'Nairobi, Kenya', owner_name:'Equipment Hub KE' },
  { id:4, name:'Scissor Lift 12m', category:'Access', icon:'🔼', available:1, specs:'{"Height":"12m","Capacity":"450kg","Width":"1.2m","Power":"Electric"}', hourly_rate:3500, daily_rate:18000, weekly_rate:105000, rating:4.7, review_count:88, location:'Nairobi, Kenya', owner_name:'Access Equipment EA' },
  { id:5, name:'Compactor Roller 10T', category:'Compaction', icon:'🛞', available:1, specs:'{"Weight":"10t","Width":"1.8m","Speed":"12km/h","Engine":"Diesel"}', hourly_rate:6500, daily_rate:35000, weekly_rate:200000, rating:4.5, review_count:56, location:'Mombasa, Kenya', owner_name:'Road Masters EA' },
  { id:6, name:'Generator 100kVA Perkins', category:'Power', icon:'⚡', available:1, specs:'{"Power":"100kVA","Fuel":"Diesel","Consumption":"18L/hr","Tank":"200L"}', hourly_rate:4800, daily_rate:25000, weekly_rate:145000, rating:4.8, review_count:203, location:'Nairobi, Kenya', owner_name:'PowerGen Africa' },
];

BL.projects = [
  { id:1, name:'Westlands Commercial Tower', type:'Commercial', location:'Westlands, Nairobi', value:'KES 4.2B', status:'completed', duration:'24 months', team:['https://i.pravatar.cc/100?img=11','https://i.pravatar.cc/100?img=5','https://i.pravatar.cc/100?img=12'], thumb_url:'https://picsum.photos/seed/proj1/600/400', tags:['High-Rise','Commercial','Grade A'] },
  { id:2, name:'Karen Residential Estate', type:'Residential', location:'Karen, Nairobi', value:'KES 890M', status:'completed', duration:'18 months', team:['https://i.pravatar.cc/100?img=9','https://i.pravatar.cc/100?img=7','https://i.pravatar.cc/100?img=15'], thumb_url:'https://picsum.photos/seed/proj2/600/400', tags:['Residential','Gated Estate','Luxury'] },
  { id:3, name:'Mombasa Port Warehouse', type:'Industrial', location:'Mombasa, Kenya', value:'KES 560M', status:'ongoing', duration:'12 months', team:['https://i.pravatar.cc/100?img=14','https://i.pravatar.cc/100?img=17'], thumb_url:'https://picsum.photos/seed/proj3/600/400', tags:['Industrial','Warehouse','Port'] },
  { id:4, name:'Thika Road Infrastructure', type:'Infrastructure', location:'Thika Road, Nairobi', value:'KES 2.1B', status:'completed', duration:'36 months', team:['https://i.pravatar.cc/100?img=16','https://i.pravatar.cc/100?img=10','https://i.pravatar.cc/100?img=8'], thumb_url:'https://picsum.photos/seed/proj4/600/400', tags:['Roads','Infrastructure','Government'] },
  { id:5, name:'Kilimani Apartments Block', type:'Residential', location:'Kilimani, Nairobi', value:'KES 320M', status:'completed', duration:'14 months', team:['https://i.pravatar.cc/100?img=6','https://i.pravatar.cc/100?img=11'], thumb_url:'https://picsum.photos/seed/proj5/600/400', tags:['Residential','Apartments','Mid-Rise'] },
  { id:6, name:'Kisumu Business Park', type:'Commercial', location:'Kisumu, Kenya', value:'KES 1.8B', status:'ongoing', duration:'28 months', team:['https://i.pravatar.cc/100?img=12','https://i.pravatar.cc/100?img=5','https://i.pravatar.cc/100?img=9'], thumb_url:'https://picsum.photos/seed/proj6/600/400', tags:['Commercial','Mixed-Use','Green Building'] },
];

BL.bids = [
  { id:1, title:'Construction of 12-Unit Apartment Block', owner_name:'Kamau Developers Ltd', budget_min:45000000, budget_max:60000000, location:'Kilimani, Nairobi', deadline_days:3, applications_count:18, trades:['Civil Engineering','Architecture','Electrical'], urgency:'hot' },
  { id:2, title:'Commercial Office Fit-Out (3,500 sqm)', owner_name:'Strathmore Business Park', budget_min:8000000, budget_max:12000000, location:'Madaraka, Nairobi', deadline_days:7, applications_count:9, trades:['Interior Design','MEP','Partitioning'], urgency:'new' },
  { id:3, title:'Borehole Drilling & Water Supply', owner_name:'Mugumo Investors', budget_min:800000, budget_max:1200000, location:'Thika, Kenya', deadline_days:5, applications_count:7, trades:['Water Engineering','Electrical'], urgency:'warm' },
  { id:4, title:'Factory Roofing Replacement (2,400 sqm)', owner_name:'Bidco Africa Ltd', budget_min:3500000, budget_max:5000000, location:'Thika Road', deadline_days:2, applications_count:22, trades:['Roofing','Structural Engineering'], urgency:'hot' },
  { id:5, title:'Hospital Extension Block (G+4)', owner_name:'Aga Khan Health Services', budget_min:180000000, budget_max:250000000, location:'Parklands, Nairobi', deadline_days:14, applications_count:5, trades:['Architecture','Civil','MEP','QS'], urgency:'new' },
  { id:6, title:'Residential Perimeter Wall & Gate', owner_name:'Private Client', budget_min:400000, budget_max:700000, location:'Lavington, Nairobi', deadline_days:4, applications_count:14, trades:['Civil Engineering','Masonry'], urgency:'warm' },
];

BL.testimonials = [
  { stars:5, text:'"bildfie transformed how we source professionals. Found our structural engineer and QS within 48 hours — both verified and outstanding. Project delivered on time and under budget."', name:'John Kariuki', role:'Property Developer', project:'Westlands Tower, Nairobi', avatar:'https://i.pravatar.cc/100?img=22' },
  { stars:5, text:'"As a contractor, the materials marketplace saved us weeks of supplier hunting. Prices are transparent, delivery reliable. The bulk RFQ feature alone is worth its weight in gold."', name:'Christine Mwema', role:'General Contractor', project:'Karen Residential Estate', avatar:'https://i.pravatar.cc/100?img=25' },
  { stars:5, text:'"I\'ve grown my engineering practice from 2 to 14 staff since joining bildfie. The credibility filter means clients come pre-qualified and serious about delivery."', name:'Eng. Samuel Kiplagat', role:'Civil Engineer', project:'Thika Road Infrastructure', avatar:'https://i.pravatar.cc/100?img=28' },
];

BL.transport = [
  { id:1, name:'KenTrans Logistics', type:'Heavy Haulage', icon:'🚛', rating:4.8, review_count:312, rate_text:'KES 180/km', location:'Nairobi', capacity:'30 tonnes', modes:['available','contract','tripbased'] },
  { id:2, name:'FastBuild Deliveries', type:'Construction Materials', icon:'🚚', rating:4.7, review_count:188, rate_text:'KES 12,000/trip', location:'Nairobi', capacity:'10 tonnes', modes:['available','tripbased'] },
  { id:3, name:'Crane & Hoist Movers', type:'Oversize & Machinery', icon:'🏗', rating:4.9, review_count:94, rate_text:'KES 45,000/trip', location:'Nairobi', capacity:'80 tonnes', modes:['contract'] },
  { id:4, name:'QuickLoad Express', type:'Mixed Cargo', icon:'📦', rating:4.6, review_count:224, rate_text:'KES 8,500/trip', location:'Mombasa', capacity:'5 tonnes', modes:['available','tripbased'] },
];

BL.prices = [
  { item:'OPC Cement 42.5N', price:'KES 980', unit:'/50kg bag', change_pct:'+2.1%', direction:'up' },
  { item:'Y12 Steel Rebar', price:'KES 128', unit:'/kg', change_pct:'-0.8%', direction:'dn' },
  { item:'Cypress Timber 2×4"', price:'KES 320', unit:'/pc', change_pct:'+1.4%', direction:'up' },
  { item:'Hollow Block 6"', price:'KES 55', unit:'/block', change_pct:'0%', direction:'flat' },
  { item:'Aluzinc Roofing 0.5mm', price:'KES 820', unit:'/m²', change_pct:'+0.6%', direction:'up' },
  { item:'PVC Cable 2.5mm²', price:'KES 38', unit:'/m', change_pct:'-1.2%', direction:'dn' },
  { item:'uPVC Pipe 110mm', price:'KES 480', unit:'/6m', change_pct:'0%', direction:'flat' },
  { item:'Vitrified Tiles 60×60', price:'KES 1,400', unit:'/m²', change_pct:'-0.5%', direction:'dn' },
  { item:'Ballast 20mm Granite', price:'KES 3,200', unit:'/tonne', change_pct:'+0.3%', direction:'up' },
  { item:'Bitumen Membrane SBS', price:'KES 2,400', unit:'/roll', change_pct:'+1.8%', direction:'up' },
];

BL.userProjects = [
  { id:1, name:'3-Bedroom Bungalow — Karen', status:'ongoing', progress:68, budget:'KES 8.5M', spent:'KES 5.8M', contractor:'Apex Construction', phase:'Roofing', due_date:'Aug 2026', thumb_url:'https://picsum.photos/seed/up1/300/200' },
  { id:2, name:'Office Extension — Westlands', status:'planning', progress:12, budget:'KES 2.1M', spent:'KES 0.25M', contractor:'Pending Award', phase:'Design', due_date:'Dec 2026', thumb_url:'https://picsum.photos/seed/up2/300/200' },
  { id:3, name:'Perimeter Wall — Ruaka', status:'completed', progress:100, budget:'KES 680K', spent:'KES 655K', contractor:'BuildRight Ltd', phase:'Completed', due_date:'Mar 2026', thumb_url:'https://picsum.photos/seed/up3/300/200' },
];

BL.messages = [
  { id:1, sender_name:'James Kamau', sender_role:'Civil Engineer', sender_avatar:'https://i.pravatar.cc/100?img=11', created_at:'2 mins ago', content:'I can start the foundation assessment this Friday. Please send the site drawings and soil report.', read:0 },
  { id:2, sender_name:'Bamburi Cement Ltd', sender_role:'Supplier', sender_avatar:'https://i.pravatar.cc/100?img=35', created_at:'1 hr ago', content:'Your order #BL-2809 of 200 bags has been dispatched. ETA today 4PM.', read:0 },
  { id:3, sender_name:'Grace Wanjiku', sender_role:'Quantity Surveyor', sender_avatar:'https://i.pravatar.cc/100?img=9', created_at:'3 hrs ago', content:'Revised BoQ attached. Main change is in the finishes section — tiles upgraded from standard to Italian vitrified.', read:1 },
  { id:4, sender_name:'David Mwangi', sender_role:'Project Manager', sender_avatar:'https://i.pravatar.cc/100?img=12', created_at:'Yesterday', content:'Site visit report for Karen project attached. Overall progress is on track at 68%.', read:1 },
  { id:5, sender_name:'bildfie Support', sender_role:'Platform', sender_avatar:'https://i.pravatar.cc/100?img=40', created_at:'2 days ago', content:'Your account has been upgraded to Professional Verified status. Congratulations!', read:1 },
];

BL.invoices = [
  { id:1, project_name:'Karen Bungalow', issuer_name:'James Kamau', amount:34000, currency:'KES', milestone:'Foundation Assessment Review', status:'pending', due_date:'2026-05-27' },
  { id:2, project_name:'Karen Bungalow', issuer_name:'Grace Wanjiku', amount:22000, currency:'KES', milestone:'BoQ Preparation — Revised', status:'pending', due_date:'2026-05-30' },
  { id:3, project_name:'Karen Bungalow', issuer_name:'David Mwangi', amount:45000, currency:'KES', milestone:'Project Management — May 2026', status:'approved', due_date:'2026-06-01' },
  { id:4, project_name:'Perimeter Wall — Ruaka', issuer_name:'James Kamau', amount:15000, currency:'KES', milestone:'Final Structural Sign-off', status:'paid', due_date:'2026-03-28' },
];

window.BL = BL;
