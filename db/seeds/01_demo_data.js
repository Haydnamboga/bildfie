'use strict';
const bcrypt = require('bcryptjs');

/**
 * Knex seed — wipes and repopulates all tables with demo data.
 * Run: npx knex seed:run
 */
exports.seed = async function(knex) {
  // Wipe in reverse-dependency order
  await knex('notifications').del();
  await knex('cart').del();
  await knex('saved_professionals').del();
  await knex('material_prices').del();
  await knex('invoices').del();
  await knex('reviews').del();
  await knex('messages').del();
  await knex('transport').del();
  await knex('equipment').del();
  await knex('materials').del();
  await knex('bid_applications').del();
  await knex('bid_trades').del();
  await knex('bids').del();
  await knex('project_invitations').del();
  await knex('project_team').del();
  await knex('projects').del();
  await knex('professional_tags').del();
  await knex('professional_profiles').del();
  await knex('users').del();

  const hash = bcrypt.hashSync('password123', 10);

  /* ── Users ── */
  const [clientId] = await knex('users').insert({
    name: 'John Kariuki', email: 'john.k@karidevelopers.co.ke',
    password_hash: hash, role: 'client', location: 'Nairobi, Kenya',
    avatar: 'https://i.pravatar.cc/100?img=22',
    bio: 'Property Developer | BuildLink Member since 2024',
    verified: true, rating: 0, review_count: 0,
  });

  const proData = [
    { name:'James Kamau',     email:'james.kamau@buildlink.co.ke',  location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=11', bio:'NCA Grade 5 Licensed Civil & Structural Engineer with 12 years delivering high-rise, residential, and infrastructure projects across Kenya, Uganda, and Tanzania.', verified:true,  badge:'Elite Pro',   rating:4.9, reviews:214, trade:'Civil & Structural Engineer',     exp:12, nca:'G5', rate:8500, jobs:187, tags:['Structural Design','AutoCAD','Foundation Engineering','High-Rise','Site Supervision'] },
    { name:'Sarah Njoroge',   email:'sarah.njoroge@arch.co.ke',      location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=5',  bio:'Award-winning architect specialising in commercial and residential design across East Africa.', verified:true,  badge:'Top Rated',   rating:4.8, reviews:163, trade:'Architect & Interior Designer',     exp:9,  nca:'G4', rate:7200, jobs:142, tags:['ArchiCAD','Revit','3D Rendering','Commercial','Interior Design'] },
    { name:'Mohamed Osman',   email:'m.osman@mepeng.co.ke',         location:'Mombasa, Kenya',  avatar:'https://i.pravatar.cc/100?img=15', bio:'MEP Engineer with expertise in plumbing, electrical, HVAC, and fire systems for commercial developments.', verified:true,  badge:null,          rating:4.7, reviews:98,  trade:'MEP Engineer',                      exp:8,  nca:'G3', rate:6800, jobs:109, tags:['Plumbing','Electrical','HVAC','Fire Systems'] },
    { name:'Grace Wanjiku',   email:'gwanjiku@qs.co.ke',             location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=9',  bio:'Chartered Quantity Surveyor delivering precise cost planning and tender management across East Africa.', verified:true,  badge:'Top Rated',   rating:4.9, reviews:241, trade:'Quantity Surveyor',                 exp:11, nca:'G4', rate:5500, jobs:228, tags:['Cost Estimation','BoQ','Tender','Valuation'] },
    { name:'David Mwangi',    email:'d.mwangi@pm.co.ke',            location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=12', bio:'PMP-certified Project Manager with 15 years overseeing multi-million dollar construction projects.', verified:true,  badge:'Elite Pro',   rating:4.8, reviews:177, trade:'Project Manager',                    exp:15, nca:'G5', rate:9000, jobs:156, tags:['MS Project','Risk Mgmt','Site Supervision','PMP'] },
    { name:'Aisha Abdi',      email:'aisha.abdi@landscape.co.ke',   location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=7',  bio:'Landscape Architect creating sustainable outdoor spaces and irrigation systems.', verified:true,  badge:null,          rating:4.6, reviews:88,  trade:'Landscape Architect',               exp:7,  nca:'G3', rate:4800, jobs:74,  tags:['Landscape Design','AutoCAD','Irrigation','Planting'] },
    { name:'Peter Otieno',    email:'p.otieno@electrical.co.ke',    location:'Kisumu, Kenya',   avatar:'https://i.pravatar.cc/100?img=14', bio:'Electrical Engineer specialising in HV systems, solar installations, and industrial electrical works.', verified:true,  badge:'Rising Star', rating:4.7, reviews:134, trade:'Electrical Engineer',                exp:6,  nca:'G3', rate:5200, jobs:118, tags:['HV Systems','Solar','Panel Design','Testing'] },
    { name:'Fatuma Hassan',   email:'fatuma.h@interior.co.ke',      location:'Mombasa, Kenya',  avatar:'https://i.pravatar.cc/100?img=6',  bio:'Interior Designer with expertise in FF&E, commercial, and hospitality spaces.', verified:true,  badge:'Top Rated',   rating:4.9, reviews:196, trade:'Interior Designer',                  exp:10, nca:'G4', rate:6500, jobs:183, tags:['3D Viz','FF&E','Commercial','Hospitality'] },
    { name:'Eric Mutua',      email:'emutua@geotech.co.ke',         location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=17', bio:'Geotechnical Engineer specialising in soil testing and foundation design.', verified:false, badge:null,          rating:4.5, reviews:62,  trade:'Geotechnical Engineer',             exp:5,  nca:'G2', rate:7800, jobs:54,  tags:['Soil Testing','Foundation','Slope Stability'] },
    { name:'Rose Achieng',    email:'r.achieng@planning.co.ke',     location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=10', bio:'Urban Planner specialising in GIS, zoning, EIA and master planning for municipalities.', verified:true,  badge:'Certified',   rating:4.8, reviews:111, trade:'Urban Planner',                     exp:9,  nca:'G4', rate:6000, jobs:97,  tags:['GIS','Zoning','EIA','Master Planning'] },
    { name:'Hassan Abdi',     email:'hassana@foreman.co.ke',        location:'Nairobi, Kenya',  avatar:'https://i.pravatar.cc/100?img=16', bio:'Experienced construction foreman with 20+ years managing large site operations.', verified:true,  badge:null,          rating:4.6, reviews:189, trade:'Construction Foreman',               exp:20, nca:'G2', rate:3200, jobs:342, tags:['Site Management','Labour','Safety','QC'] },
    { name:'Linda Chebet',    email:'l.chebet@water.co.ke',         location:'Eldoret, Kenya',  avatar:'https://i.pravatar.cc/100?img=8',  bio:'Water & Sanitation Engineer delivering borehole, drainage, and WASH projects.', verified:true,  badge:null,          rating:4.7, reviews:77,  trade:'Water & Sanitation Engineer',       exp:8,  nca:'G3', rate:5800, jobs:66,  tags:['Borehole','Drainage','WASH','Water Treatment'] },
  ];

  const proIds = [];
  for (const p of proData) {
    const [uid] = await knex('users').insert({
      name: p.name, email: p.email, password_hash: hash,
      role: 'professional', location: p.location, avatar: p.avatar,
      bio: p.bio, verified: p.verified, verification_badge: p.badge,
      rating: p.rating, review_count: p.reviews,
    });
    proIds.push(uid);
    await knex('professional_profiles').insert({
      user_id: uid, trade: p.trade, experience_years: p.exp,
      nca_grade: p.nca, hourly_rate: p.rate, available: true,
      jobs_done: p.jobs, on_time_percent: 98,
    });
    await knex('professional_tags').insert(p.tags.map(tag => ({ user_id: uid, tag })));
  }

  /* ── Materials ── */
  await knex('materials').insert([
    { name:'OPC Cement 42.5N (50kg)',        category:'Cement & Concrete',   specification:'BS EN 197-1 | East African Portland', supplier_name:'Bamburi Cement Ltd',         supplier_verified:true, price:980,  unit:'per bag',        stock_status:'in',  min_order:'10 bags',     delivery_speed:'Same Day', badge:'bulk', image_url:'https://picsum.photos/seed/cement1/400/280',    tags:'["42.5N","OPC","Portland"]'          },
    { name:'Y12 Steel Rebar (12mm)',         category:'Steel & Rebar',       specification:'KS 3196:2017 | Grade 460B | 12m length', supplier_name:'Steel Structures Ltd',      supplier_verified:true, price:1536, unit:'per 12m bar',    stock_status:'in',  min_order:'50 bars',     delivery_speed:'Next Day', badge:'sale', image_url:'https://picsum.photos/seed/steel2/400/280',     tags:'["Y12","Rebar","High-Yield"]'        },
    { name:'Hardwood Timber 2"×4"',         category:'Timber & Lumber',     specification:'Cypress | Air-dried | 4m length',        supplier_name:'Kenya Timber Merchants',    supplier_verified:true, price:320,  unit:'per pc',         stock_status:'low', min_order:'20 pcs',      delivery_speed:'2-3 Days', badge:null,   image_url:'https://picsum.photos/seed/timber3/400/280',    tags:'["Cypress","Timber","Structural"]'   },
    { name:'Hollow Concrete Blocks 6"',     category:'Blocks & Bricks',     specification:'150×200×400mm | 7N/mm² strength',        supplier_name:'QualiBlocks Kenya',         supplier_verified:true, price:55,   unit:'per block',      stock_status:'in',  min_order:'500 blocks',  delivery_speed:'Same Day', badge:'bulk', image_url:'https://picsum.photos/seed/blocks4/400/280',    tags:'["Hollow","6-Inch","Concrete"]'      },
    { name:'Aluzinc Roofing Sheet 0.5mm',   category:'Roofing',             specification:'Zincalume AZ150 | Box profile | 3.6m',   supplier_name:'Mabati Rolling Mills',      supplier_verified:true, price:2952, unit:'per sheet',      stock_status:'in',  min_order:'10 sheets',   delivery_speed:'Next Day', badge:'new',  image_url:'https://picsum.photos/seed/roofing5/400/280',   tags:'["Aluzinc","Box Profile","Roofing"]' },
    { name:'2.5mm² PVC Electrical Cable',   category:'Electrical',          specification:'1 Core | Copper | 100m roll | IEC 60227', supplier_name:'Nexans Kenya',              supplier_verified:true, price:3800, unit:'per 100m roll',  stock_status:'in',  min_order:'5 rolls',     delivery_speed:'Same Day', badge:null,   image_url:'https://picsum.photos/seed/cable6/400/280',     tags:'["PVC","2.5mm²","Electrical"]'       },
    { name:'110mm uPVC Drainage Pipe',       category:'Plumbing & Pipes',    specification:'SN4 | 6m length | BS EN 1401',           supplier_name:'Elson Plumbing Supplies',   supplier_verified:true, price:480,  unit:'per 6m pipe',    stock_status:'in',  min_order:'20 pipes',    delivery_speed:'Same Day', badge:null,   image_url:'https://picsum.photos/seed/pipe7/400/280',      tags:'["uPVC","110mm","Drainage"]'         },
    { name:'Vitrified Floor Tiles 60×60cm', category:'Tiles & Flooring',    specification:'Matt finish | R9 slip rating | Italian design', supplier_name:'Classic Ceramics EA',   supplier_verified:true, price:1400, unit:'per m²',         stock_status:'in',  min_order:'50 m²',       delivery_speed:'2-3 Days', badge:'sale', image_url:'https://picsum.photos/seed/tiles8/400/280',     tags:'["Vitrified","60x60","Floor Tiles"]' },
    { name:'Dulux Weathershield 20L',       category:'Paints & Finishes',   specification:'Exterior | 10yr protection | White base',supplier_name:'AkzoNobel Kenya',           supplier_verified:true, price:8200, unit:'per 20L tin',    stock_status:'low', min_order:'4 tins',      delivery_speed:'Same Day', badge:null,   image_url:'https://picsum.photos/seed/paint9/400/280',     tags:'["Dulux","Exterior","Weathershield"]'},
    { name:'Crushed Stone Ballast 20mm',    category:'Aggregates',          specification:'Granite | Clean washed | Per tonne',    supplier_name:'Athi River Quarry',         supplier_verified:true, price:3200, unit:'per tonne',      stock_status:'in',  min_order:'5 tonnes',    delivery_speed:'Next Day', badge:'bulk', image_url:'https://picsum.photos/seed/ballast10/400/280',  tags:'["Ballast","20mm","Granite"]'        },
    { name:'Float Glass 6mm Clear',         category:'Glass & Glazing',     specification:'Clear | 2440×1220mm sheet',              supplier_name:'Glass & Aluminium Kenya',   supplier_verified:true, price:620,  unit:'per m²',         stock_status:'in',  min_order:'20 m²',       delivery_speed:'2-3 Days', badge:null,   image_url:'https://picsum.photos/seed/glass11/400/280',    tags:'["Float Glass","6mm","Clear"]'       },
    { name:'Bitumen Waterproof Membrane',   category:'Waterproofing',       specification:'SBS modified | 4mm | Torch-on | 10m²/roll',supplier_name:'Tremco Roofing EA',        supplier_verified:true, price:2400, unit:'per roll',       stock_status:'in',  min_order:'10 rolls',    delivery_speed:'Next Day', badge:null,   image_url:'https://picsum.photos/seed/waterproof12/400/280',tags:'["Bitumen","SBS","Torch-on"]'        },
  ]);

  /* ── Equipment ── */
  await knex('equipment').insert([
    { owner_name:'HeavyLift EA',          name:'CAT 320 Excavator',           category:'Earthmoving', hourly_rate:18500, daily_rate:98000,  weekly_rate:580000, available:true,  location:'Nairobi, Kenya', specs:'{"Power":"110kW","Bucket":"0.9m³","Weight":"20.6t","Reach":"9.5m"}',   rating:4.8, review_count:94  },
    { owner_name:'Crane Masters Kenya',   name:'Tower Crane TC6516',          category:'Lifting',     hourly_rate:22000, daily_rate:120000, weekly_rate:680000, available:true,  location:'Nairobi, Kenya', specs:'{"Capacity":"8t","Height":"65m","Jib":"60m","Radius":"16m"}',          rating:4.9, review_count:67  },
    { owner_name:'Equipment Hub KE',      name:'Concrete Mixer 500L',         category:'Concrete',    hourly_rate:2800,  daily_rate:15000,  weekly_rate:88000,  available:false, location:'Nairobi, Kenya', specs:'{"Capacity":"500L","Engine":"12HP","Drum":"350rpm","Output":"2.5m³/hr"}',rating:4.6, review_count:142 },
    { owner_name:'Access Equipment EA',   name:'Scissor Lift 12m',            category:'Access',      hourly_rate:3500,  daily_rate:18000,  weekly_rate:105000, available:true,  location:'Nairobi, Kenya', specs:'{"Height":"12m","Capacity":"450kg","Width":"1.2m","Power":"Electric"}',  rating:4.7, review_count:88  },
    { owner_name:'Road Masters EA',       name:'Compactor Roller 10T',        category:'Compaction',  hourly_rate:6500,  daily_rate:35000,  weekly_rate:200000, available:true,  location:'Nairobi, Kenya', specs:'{"Weight":"10t","Width":"1.8m","Speed":"12km/h","Engine":"Diesel"}',     rating:4.5, review_count:56  },
    { owner_name:'PowerGen Africa',       name:'Generator 100kVA Perkins',    category:'Power',       hourly_rate:4800,  daily_rate:25000,  weekly_rate:145000, available:true,  location:'Nairobi, Kenya', specs:'{"Power":"100kVA","Fuel":"Diesel","Consumption":"18L/hr","Tank":"200L"}',  rating:4.8, review_count:203 },
  ]);

  /* ── Transport ── */
  await knex('transport').insert([
    { name:'KenTrans Logistics',    type:'Heavy Haulage',          rating:4.8, review_count:312, rate_text:'KES 180/km',     location:'Nairobi', capacity:'30 tonnes', modes:'["available","contract","tripbased"]' },
    { name:'FastBuild Deliveries',  type:'Construction Materials', rating:4.7, review_count:188, rate_text:'KES 12,000/trip', location:'Nairobi', capacity:'10 tonnes', modes:'["available","tripbased"]'            },
    { name:'Crane & Hoist Movers',  type:'Oversize & Machinery',   rating:4.9, review_count:94,  rate_text:'KES 45,000/trip', location:'Nairobi', capacity:'80 tonnes', modes:'["contract"]'                        },
    { name:'QuickLoad Express',     type:'Mixed Cargo',            rating:4.6, review_count:224, rate_text:'KES 8,500/trip',  location:'Mombasa', capacity:'5 tonnes',  modes:'["available","tripbased"]'            },
  ]);

  /* ── Projects ── */
  const [p1id] = await knex('projects').insert({ owner_id:clientId, name:'3-Bedroom Bungalow — Karen',      description:'Luxury 3-bed bungalow with swimming pool and servant quarters', type:'Residential', location:'Karen, Nairobi',      budget_min:7000000,   budget_max:8500000,   status:'ongoing',   phase:'Roofing',    progress:68,  contractor:'Apex Construction', due_date:'2026-08-31', thumb_url:'https://picsum.photos/seed/up1/300/200', value:'KES 8.5M', duration:'18 months', tags:'["Residential","Luxury","Bungalow"]'  });
  const [p2id] = await knex('projects').insert({ owner_id:clientId, name:'Office Extension — Westlands',   description:'2-floor office extension to existing 3-storey commercial block',  type:'Commercial',  location:'Westlands, Nairobi',  budget_min:1800000,   budget_max:2100000,   status:'planning',  phase:'Design',     progress:12,  contractor:'Pending Award',     due_date:'2026-12-31', thumb_url:'https://picsum.photos/seed/up2/300/200', value:'KES 2.1M', duration:'8 months',  tags:'["Commercial","Office","Extension"]'  });
  const [p3id] = await knex('projects').insert({ owner_id:clientId, name:'Perimeter Wall — Ruaka',         description:'Precast perimeter wall with electric fence and automated gate',    type:'Residential', location:'Ruaka, Kiambu',       budget_min:600000,    budget_max:680000,    status:'completed', phase:'Completed',  progress:100, contractor:'BuildRight Ltd',    due_date:'2026-03-31', thumb_url:'https://picsum.photos/seed/up3/300/200', value:'KES 680K', duration:'3 months',  tags:'["Security","Wall","Residential"]'    });

  await knex('project_team').insert([
    { project_id:p1id, user_id:proIds[0], role:'Civil Engineer'      },
    { project_id:p1id, user_id:proIds[3], role:'Quantity Surveyor'   },
    { project_id:p1id, user_id:proIds[4], role:'Project Manager'     },
  ]);

  /* ── Bids ── */
  const bidData = [
    { title:'Construction of 12-Unit Apartment Block', own:'Kamau Developers Ltd',   min:45000000,  max:60000000,  loc:'Kilimani, Nairobi',  days:3,  urg:'hot',  apps:18, trades:['Civil Engineering','Architecture','Electrical']      },
    { title:'Commercial Office Fit-Out (3,500 sqm)',   own:'Strathmore Business Park',min:8000000,   max:12000000,  loc:'Madaraka, Nairobi',  days:7,  urg:'new',  apps:9,  trades:['Interior Design','MEP','Partitioning']               },
    { title:'Borehole Drilling & Water Supply',        own:'Mugumo Investors',        min:800000,    max:1200000,   loc:'Thika, Kenya',       days:5,  urg:'warm', apps:7,  trades:['Water Engineering','Electrical']                     },
    { title:'Factory Roofing Replacement (2,400 sqm)', own:'Bidco Africa Ltd',        min:3500000,   max:5000000,   loc:'Thika Road',         days:2,  urg:'hot',  apps:22, trades:['Roofing','Structural Engineering']                   },
    { title:'Hospital Extension Block (G+4)',          own:'Aga Khan Health Services',min:180000000, max:250000000, loc:'Parklands, Nairobi', days:14, urg:'new',  apps:5,  trades:['Architecture','Civil','MEP','QS']                    },
    { title:'Residential Perimeter Wall & Gate',       own:'Private Client',          min:400000,    max:700000,    loc:'Lavington, Nairobi', days:4,  urg:'warm', apps:14, trades:['Civil Engineering','Masonry']                        },
  ];
  for (const b of bidData) {
    const [bid_id] = await knex('bids').insert({ poster_id:clientId, title:b.title, owner_name:b.own, budget_min:b.min, budget_max:b.max, location:b.loc, deadline_days:b.days, urgency:b.urg, applications_count:b.apps });
    await knex('bid_trades').insert(b.trades.map(trade => ({ bid_id, trade })));
  }

  /* ── Messages ── */
  await knex('messages').insert([
    { sender_id:proIds[0], recipient_id:clientId, project_id:p1id, content:'I can start the foundation assessment this Friday. Please send the site drawings and soil report.', read:false },
    { sender_id:clientId,  recipient_id:proIds[0], project_id:p1id, content:'Will send across today. The soil report from 2024 is still valid.', read:true  },
    { sender_id:proIds[3], recipient_id:clientId, project_id:p1id, content:'Revised BoQ attached. Main change is in the finishes section — tiles upgraded from standard to Italian vitrified.', read:false },
    { sender_id:proIds[4], recipient_id:clientId, project_id:p1id, content:'Site visit report for Karen project attached. Overall progress is on track at 68%.', read:true  },
  ]);

  /* ── Invoices ── */
  await knex('invoices').insert([
    { project_id:p1id, issuer_id:proIds[0], recipient_id:clientId, amount:34000, currency:'KES', milestone:'Foundation Assessment Review',  status:'pending',  escrow_status:'held',     due_date:'2026-05-27' },
    { project_id:p1id, issuer_id:proIds[3], recipient_id:clientId, amount:22000, currency:'KES', milestone:'BoQ Preparation — Revised',      status:'pending',  escrow_status:'held',     due_date:'2026-05-30' },
    { project_id:p1id, issuer_id:proIds[4], recipient_id:clientId, amount:45000, currency:'KES', milestone:'Project Management — May 2026',  status:'approved', escrow_status:'held',     due_date:'2026-06-01' },
    { project_id:p3id, issuer_id:proIds[0], recipient_id:clientId, amount:15000, currency:'KES', milestone:'Final Structural Sign-off',      status:'paid',     escrow_status:'released', due_date:'2026-03-28' },
  ]);

  /* ── Material prices ── */
  await knex('material_prices').insert([
    { item:'OPC Cement 42.5N',      price:'KES 980',   unit:'/50kg bag', change_pct:'+2.1%', direction:'up'   },
    { item:'Y12 Steel Rebar',       price:'KES 128',   unit:'/kg',       change_pct:'-0.8%', direction:'dn'   },
    { item:'Cypress Timber 2×4"',   price:'KES 320',   unit:'/pc',       change_pct:'+1.4%', direction:'up'   },
    { item:'Hollow Block 6"',       price:'KES 55',    unit:'/block',    change_pct:'0%',    direction:'flat' },
    { item:'Aluzinc Roofing 0.5mm', price:'KES 820',   unit:'/m²',       change_pct:'+0.6%', direction:'up'   },
    { item:'PVC Cable 2.5mm²',      price:'KES 38',    unit:'/m',        change_pct:'-1.2%', direction:'dn'   },
    { item:'uPVC Pipe 110mm',       price:'KES 480',   unit:'/6m',       change_pct:'0%',    direction:'flat' },
    { item:'Vitrified Tiles 60×60', price:'KES 1,400', unit:'/m²',       change_pct:'-0.5%', direction:'dn'   },
    { item:'Ballast 20mm Granite',  price:'KES 3,200', unit:'/tonne',    change_pct:'+0.3%', direction:'up'   },
    { item:'Bitumen Membrane SBS',  price:'KES 2,400', unit:'/roll',     change_pct:'+1.8%', direction:'up'   },
  ]);

  /* ── Notifications ── */
  await knex('notifications').insert([
    { user_id:clientId, type:'payment',    title:'Invoice Pending Approval',   body:'James Kamau submitted KES 34,000 invoice for foundation assessment.',      link:'/dashboard' },
    { user_id:clientId, type:'invitation', title:'Project Invitation Accepted', body:'Grace Wanjiku has accepted your invitation to Karen Bungalow project.',    link:'/dashboard' },
    { user_id:clientId, type:'message',    title:'New Message',                 body:'James Kamau: I can start the foundation assessment this Friday...',         link:'/dashboard/messages' },
    { user_id:clientId, type:'system',     title:'Account Upgraded',            body:'Your account has been upgraded to Professional Verified status.',           link:'/dashboard' },
  ]);

  console.log('✅ BuildLink seeded');
  console.log('   Login: john.k@karidevelopers.co.ke / password123');
};
