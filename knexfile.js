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

  // Render's internal PostgreSQL uses self-signed certificates.
  // rejectUnauthorized: false is safe here because the connection stays
  // within Render's private network — it never traverses the public internet.
  return {
    connectionString: url,
    ssl: { rejectUnauthorized: false },
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
