'use strict';
/**
 * Migration: mpesa_transactions
 * Records every STK Push attempt and its Daraja callback result.
 */

exports.up = async function(knex) {
  if (!await knex.schema.hasTable('mpesa_transactions')) {
    await knex.schema.createTable('mpesa_transactions', t => {
      t.increments('id');
      t.integer('invoice_id').nullable().references('id').inTable('invoices').onDelete('SET NULL');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('phone').notNullable();
      t.integer('amount').notNullable();
      t.string('checkout_request_id').unique();
      t.string('merchant_request_id');
      // pending | completed | failed | cancelled
      t.string('status').defaultTo('pending');
      t.string('mpesa_receipt');
      t.integer('result_code');
      t.text('result_desc');
      t.timestamp('created_at').defaultTo(knex.fn.now());
      t.timestamp('completed_at');
    });
  }
};

exports.down = async function(knex) {
  await knex.schema.dropTableIfExists('mpesa_transactions');
};
