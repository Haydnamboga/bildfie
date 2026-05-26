'use strict';
require('dotenv').config();

/**
 * Knex configuration — environment-aware
 *
 * development / test : better-sqlite3  (zero-config, file-based)
 * production         : PostgreSQL via DATABASE_URL
 *
 * Railway and Render provide DATABASE_URL automatically when you attach
 * a Postgres service.  SSL is required — we reject unauthorised certs
 * unless PGSSLMODE=no-verify is set (for self-signed certs in some setups).
 */

function pgConnection() {
  const url = process.env.DATABASE_URL;
  if (!url) throw new Error('DATABASE_URL is not set — required in production');

  // Render / Railway inject DATABASE_URL with ?ssl or sslmode in query string.
  // We always enable SSL in prod; use PGSSLMODE=no-verify only for self-signed.
  return {
    connectionString: url,
    ssl: process.env.PGSSLMODE === 'no-verify'
      ? { rejectUnauthorized: false }
      : { rejectUnauthorized: true },
  };
}

module.exports = {

  development: {
    client:           'better-sqlite3',
    connection:       { filename: process.env.DB_PATH || './db/buildlink.db' },
    useNullAsDefault: true,
    migrations: { directory: './db/migrations', tableName: 'knex_migrations' },
    seeds:      { directory: './db/seeds' },
  },

  test: {
    client:           'better-sqlite3',
    connection:       { filename: ':memory:' },
    useNullAsDefault: true,
    migrations: { directory: './db/migrations', tableName: 'knex_migrations' },
    seeds:      { directory: './db/seeds' },
  },

  production: {
    client:     'pg',
    connection: pgConnection,       // lazy function — called only when pg is needed
    pool:       { min: 2, max: 10 },
    acquireConnectionTimeout: 10000,
    migrations: { directory: './db/migrations', tableName: 'knex_migrations' },
    seeds:      { directory: './db/seeds' },
  },
};
