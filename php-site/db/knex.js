'use strict';
const knexLib = require('knex');
const config  = require('../knexfile');

const env = process.env.NODE_ENV || 'development';
const db  = knexLib(config[env]);

/**
 * Cross-database insert that returns the new row's id.
 *
 *   SQLite  → returns [lastID]  (no .returning() needed)
 *   Postgres → needs .returning('id')
 */
/**
 * insertId(table, data) — insert and return new row id.
 * Call as db.insertId(table, data)  → uses db connection
 * Call as db.insertId.call(trx, table, data) → uses transaction context
 */
db.insertId = async function(table, data) {
  // Always check the outer db client type (safe for transaction .call() usage too)
  const isPostgres = db.client.config.client === 'pg';
  if (isPostgres) {
    const [row] = await this(table).insert(data).returning('id');
    return typeof row === 'object' ? row.id : row;
  }
  const [id] = await this(table).insert(data);
  return id;
};

module.exports = db;
