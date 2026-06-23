'use strict';
/**
 * db/clear-demo.js — remove seed / demo data from the production database.
 *
 * Deletes only the known demo emails inserted by db/seeds/01_demo_data.js
 * plus demo materials, equipment, and transport by their exact seed names.
 * Real user accounts, projects, and listings are NOT touched.
 *
 * Usage (Render Shell or local):
 *   NODE_ENV=production node db/clear-demo.js
 */

require('dotenv').config();
const db = require('./knex');

const DEMO_EMAILS = [
  'john.k@karidevelopers.co.ke',
  'james.kamau@buildlink.co.ke',
  'sarah.njoroge@arch.co.ke',
  'm.osman@mepeng.co.ke',
  'gwanjiku@qs.co.ke',
  'd.mwangi@pm.co.ke',
  'aisha.abdi@landscape.co.ke',
  'p.otieno@electrical.co.ke',
  'fatuma.h@interior.co.ke',
  'emutua@geotech.co.ke',
  'r.achieng@planning.co.ke',
  'hassana@foreman.co.ke',
  'l.chebet@water.co.ke',
];

const DEMO_MATERIALS = [
  'OPC Cement 42.5N (50kg)', 'Y12 Steel Rebar (12mm)', 'Hardwood Timber 2"×4"',
  'Hollow Concrete Blocks 6"', 'Aluzinc Roofing Sheet 0.5mm', '2.5mm² PVC Electrical Cable',
  '110mm uPVC Drainage Pipe', 'Vitrified Floor Tiles 60×60cm', 'Dulux Weathershield 20L',
  'Crushed Stone Ballast 20mm', 'Float Glass 6mm Clear', 'Bitumen Waterproof Membrane',
];

const DEMO_EQUIPMENT = [
  'CAT 320 Excavator', 'Tower Crane TC6516', 'Concrete Mixer 500L',
  'Scissor Lift 12m', 'Compactor Roller 10T', 'Generator 100kVA Perkins',
];

const DEMO_TRANSPORT = [
  'KenTrans Logistics', 'FastBuild Deliveries', 'Crane & Hoist Movers', 'QuickLoad Express',
];

async function clearDemo() {
  const users = await db('users').whereIn('email', DEMO_EMAILS).select('id', 'email');

  if (!users.length) {
    console.log('✅ No demo users found — database is already clean.');
  } else {
    const ids = users.map(u => u.id);
    console.log(`Found ${users.length} demo users:`, users.map(u => u.email).join(', '));

    // Notifications and cart
    await db('notifications').whereIn('user_id', ids).del();
    await db('cart').whereIn('user_id', ids).del();
    await db('saved_professionals')
      .where(q => q.whereIn('user_id', ids).orWhereIn('professional_id', ids))
      .del();

    // Invoices linked to demo users
    await db('invoices')
      .where(q => q.whereIn('issuer_id', ids).orWhereIn('recipient_id', ids))
      .del();

    // Reviews
    await db('reviews')
      .where(q => q.whereIn('reviewer_id', ids).orWhereIn('professional_id', ids))
      .del();

    // Messages
    await db('messages')
      .where(q => q.whereIn('sender_id', ids).orWhereIn('recipient_id', ids))
      .del();

    // Projects owned by demo users (+ their team / invitations)
    const projectIds = await db('projects').whereIn('owner_id', ids).pluck('id');
    if (projectIds.length) {
      await db('project_invitations').whereIn('project_id', projectIds).del();
      await db('project_team').whereIn('project_id', projectIds).del();
      await db('projects').whereIn('id', projectIds).del();
    }

    // Bids posted by demo users (+ applications / trades)
    const bidIds = await db('bids').whereIn('poster_id', ids).pluck('id');
    if (bidIds.length) {
      await db('bid_applications').whereIn('bid_id', bidIds).del();
      await db('bid_trades').whereIn('bid_id', bidIds).del();
      await db('bids').whereIn('id', bidIds).del();
    }

    // Professional profile data
    await db('professional_tags').whereIn('user_id', ids).del();
    await db('professional_profiles').whereIn('user_id', ids).del();

    // Finally the users themselves
    const nUsers = await db('users').whereIn('id', ids).del();
    console.log(`   Removed ${nUsers} demo users`);
  }

  // Materials, equipment, transport (identified by exact seed names)
  const nMat = await db('materials').whereIn('name', DEMO_MATERIALS).del();
  const nEq  = await db('equipment').whereIn('name', DEMO_EQUIPMENT).del();
  const nTr  = await db('transport').whereIn('name', DEMO_TRANSPORT).del();

  if (nMat || nEq || nTr)
    console.log(`   Removed ${nMat} demo materials, ${nEq} demo equipment, ${nTr} demo transport providers`);

  // Clear demo material prices
  const demoPriceItems = [
    'OPC Cement 42.5N', 'Y12 Steel Rebar', 'Cypress Timber 2×4"',
    'Hollow Block 6"', 'Aluzinc Roofing 0.5mm', 'PVC Cable 2.5mm²',
    'uPVC Pipe 110mm', 'Vitrified Tiles 60×60', 'Ballast 20mm Granite', 'Bitumen Membrane SBS',
  ];
  const nPrices = await db('material_prices').whereIn('item', demoPriceItems).del();
  if (nPrices) console.log(`   Removed ${nPrices} demo material price entries`);

  console.log('\n✅ Done. Only real user registrations remain in the database.');
}

clearDemo()
  .catch(err => { console.error('Error:', err.message); process.exit(1); })
  .finally(() => db.destroy());
