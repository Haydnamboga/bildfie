'use strict';
/**
 * Migration: password_resets
 * Stores one-time tokens for the forgot-password flow.
 */

exports.up = async function(knex) {
  if (!await knex.schema.hasTable('password_resets')) {
    await knex.schema.createTable('password_resets', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('token').notNullable().unique();
      t.timestamp('expires_at').notNullable();
      t.boolean('used').defaultTo(false);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }
};

exports.down = async function(knex) {
  await knex.schema.dropTableIfExists('password_resets');
};
