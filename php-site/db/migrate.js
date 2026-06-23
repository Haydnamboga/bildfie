#!/usr/bin/env node
'use strict';
/**
 * db/migrate.js — run all pending Knex migrations
 *
 * Usage:
 *   node db/migrate.js           # run latest migrations
 *   node db/migrate.js rollback  # roll back the last batch
 *   node db/migrate.js status    # show migration status
 *
 * Called automatically by "npm run migrate" and by the Render/Railway
 * pre-deploy hook (see package.json scripts).
 */

require('dotenv').config();
const db  = require('./knex');
const cmd = process.argv[2] || 'latest';

async function main() {
  try {
    if (cmd === 'rollback') {
      const [batch, list] = await db.migrate.rollback();
      console.log(`Rolled back batch ${batch}:`, list);
    } else if (cmd === 'status') {
      const [completed, pending] = await db.migrate.list();
      console.log('Completed migrations:', completed.map(m => m.name));
      console.log('Pending migrations:',  pending.map(m => m.name));
    } else {
      const [batch, list] = await db.migrate.latest();
      if (list.length === 0) {
        console.log('Already up to date — no migrations to run.');
      } else {
        console.log(`Ran batch ${batch}:`, list);
      }
    }
  } catch (err) {
    console.error('Migration failed:', err.message);
    process.exit(1);
  } finally {
    await db.destroy();
  }
}

main();
