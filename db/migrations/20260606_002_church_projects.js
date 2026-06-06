'use strict';
/**
 * Creates church_projects table since `projects` already exists for the
 * construction marketplace and has a different schema.
 */

exports.up = async function(knex) {
  if (!await knex.schema.hasTable('church_projects')) {
    await knex.schema.createTable('church_projects', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.string('title').notNullable();
      t.text('description');
      t.string('category'); // construction, evangelism, education, health, social, spiritual, administrative
      t.string('status').defaultTo('planning');
      t.string('priority').defaultTo('medium');
      t.decimal('budget', 14, 2).defaultTo(0);
      t.decimal('spent', 14, 2).defaultTo(0);
      t.date('start_date');
      t.date('end_date');
      t.integer('manager_id').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.integer('completion_percent').defaultTo(0);
      t.text('notes');
      t.timestamps(true, true);
    });
  }

  // Add project_id FK to tasks pointing to church_projects
  const hasChurchProjectId = await knex.schema.hasColumn('tasks', 'church_project_id');
  if (!hasChurchProjectId) {
    await knex.schema.table('tasks', t => {
      t.integer('church_project_id').references('id').inTable('church_projects').onDelete('SET NULL').nullable();
    });
  }
};

exports.down = async function(knex) {
  await knex.schema.dropTableIfExists('church_projects');
};
