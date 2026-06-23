'use strict';
/**
 * Migration: verification_documents
 * Stores NCA licence scans and portfolio uploads for professional verification.
 */

exports.up = async function(knex) {
  if (!await knex.schema.hasTable('verification_documents')) {
    await knex.schema.createTable('verification_documents', t => {
      t.increments('id');
      t.integer('user_id')
        .notNullable()
        .references('id').inTable('users').onDelete('CASCADE');
      t.string('filename').notNullable();        // UUID-based stored filename
      t.string('original_name').notNullable();   // Original upload filename
      t.string('mime_type').notNullable();
      t.integer('size_bytes').notNullable();
      t.string('document_type').defaultTo('nca_license');
                                                 // nca_license | portfolio | id_document | other
      t.string('status').defaultTo('pending');   // pending | approved | rejected
      t.integer('reviewer_id').references('id').inTable('users').onDelete('SET NULL');
      t.timestamp('reviewed_at');
      t.text('rejection_reason');
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }
};

exports.down = async function(knex) {
  await knex.schema.dropTableIfExists('verification_documents');
};
