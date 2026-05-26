'use strict';
/**
 * Migration: initial schema
 * Creates all BuildLink tables; safe to run on an existing database
 * (every table uses createTableIfNotExists).
 */

exports.up = async function(knex) {

  /* ── users ─────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('users')) {
    await knex.schema.createTable('users', t => {
      t.increments('id');
      t.string('name').notNullable();
      t.string('email').unique().notNullable();
      t.string('password_hash').notNullable();
      t.string('role').defaultTo('client');
      t.string('phone');
      t.string('location').defaultTo('Nairobi, Kenya');
      t.string('avatar');
      t.text('bio');
      t.boolean('verified').defaultTo(false);
      t.string('verification_badge');
      t.string('subscription_plan').defaultTo('starter');
      t.float('rating').defaultTo(0);
      t.integer('review_count').defaultTo(0);
      t.timestamps(true, true);
    });
  }

  /* ── professional_profiles ──────────────────────────────────────── */
  if (!await knex.schema.hasTable('professional_profiles')) {
    await knex.schema.createTable('professional_profiles', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('trade').notNullable();
      t.integer('experience_years').defaultTo(0);
      t.string('nca_grade');
      t.string('nca_license');
      t.integer('hourly_rate').defaultTo(0);
      t.string('rate_currency').defaultTo('KES');
      t.boolean('available').defaultTo(true);
      t.integer('jobs_done').defaultTo(0);
      t.integer('on_time_percent').defaultTo(98);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── professional_tags ──────────────────────────────────────────── */
  if (!await knex.schema.hasTable('professional_tags')) {
    await knex.schema.createTable('professional_tags', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('tag').notNullable();
    });
  }

  /* ── projects ───────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('projects')) {
    await knex.schema.createTable('projects', t => {
      t.increments('id');
      t.integer('owner_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('name').notNullable();
      t.text('description');
      t.string('type').defaultTo('Residential');
      t.string('location');
      t.integer('budget_min').defaultTo(0);
      t.integer('budget_max').defaultTo(0);
      t.string('currency').defaultTo('KES');
      t.string('status').defaultTo('planning');
      t.string('phase').defaultTo('Design');
      t.integer('progress').defaultTo(0);
      t.date('due_date');
      t.string('contractor');
      t.string('thumb_url');
      t.string('value');
      t.string('duration');
      t.text('tags').defaultTo('[]');
      t.timestamps(true, true);
    });
  }

  /* ── project_team ───────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('project_team')) {
    await knex.schema.createTable('project_team', t => {
      t.increments('id');
      t.integer('project_id').references('id').inTable('projects').onDelete('CASCADE');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('role');
      t.timestamp('joined_at').defaultTo(knex.fn.now());
      t.unique(['project_id', 'user_id']);
    });
  }

  /* ── project_invitations ────────────────────────────────────────── */
  if (!await knex.schema.hasTable('project_invitations')) {
    await knex.schema.createTable('project_invitations', t => {
      t.increments('id');
      t.integer('project_id').references('id').inTable('projects').onDelete('CASCADE');
      t.integer('inviter_id').references('id').inTable('users');
      t.integer('invitee_id').references('id').inTable('users');
      t.string('role');
      t.text('message');
      t.string('status').defaultTo('pending');
      t.timestamp('created_at').defaultTo(knex.fn.now());
      t.timestamp('responded_at');
    });
  }

  /* ── bids ───────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('bids')) {
    await knex.schema.createTable('bids', t => {
      t.increments('id');
      t.integer('project_id');
      t.integer('poster_id').references('id').inTable('users');
      t.string('title').notNullable();
      t.text('description');
      t.string('owner_name');
      t.integer('budget_min').defaultTo(0);
      t.integer('budget_max').defaultTo(0);
      t.string('currency').defaultTo('KES');
      t.string('location');
      t.integer('deadline_days').defaultTo(7);
      t.string('urgency').defaultTo('new');
      t.string('status').defaultTo('open');
      t.integer('applications_count').defaultTo(0);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── bid_trades ─────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('bid_trades')) {
    await knex.schema.createTable('bid_trades', t => {
      t.increments('id');
      t.integer('bid_id').references('id').inTable('bids').onDelete('CASCADE');
      t.string('trade').notNullable();
    });
  }

  /* ── bid_applications ───────────────────────────────────────────── */
  if (!await knex.schema.hasTable('bid_applications')) {
    await knex.schema.createTable('bid_applications', t => {
      t.increments('id');
      t.integer('bid_id').references('id').inTable('bids').onDelete('CASCADE');
      t.integer('applicant_id').references('id').inTable('users');
      t.text('proposal');
      t.integer('quoted_amount');
      t.string('currency').defaultTo('KES');
      t.string('timeline');
      t.string('status').defaultTo('pending');
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── materials ──────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('materials')) {
    await knex.schema.createTable('materials', t => {
      t.increments('id');
      t.integer('supplier_id').references('id').inTable('users');
      t.string('name').notNullable();
      t.string('category').notNullable();
      t.text('specification');
      t.string('supplier_name');
      t.boolean('supplier_verified').defaultTo(true);
      t.string('supplier_rating').defaultTo('★★★★★');
      t.integer('price').notNullable();
      t.string('currency').defaultTo('KES');
      t.string('unit').defaultTo('per unit');
      t.string('stock_status').defaultTo('in');
      t.string('min_order').defaultTo('1 unit');
      t.string('delivery_speed').defaultTo('Next Day');
      t.string('badge');
      t.string('image_url');
      t.text('tags').defaultTo('[]');
      t.string('location').defaultTo('Nairobi, Kenya');
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── equipment ──────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('equipment')) {
    await knex.schema.createTable('equipment', t => {
      t.increments('id');
      t.integer('owner_id').references('id').inTable('users');
      t.string('owner_name');
      t.string('name').notNullable();
      t.string('category');
      t.string('icon').defaultTo('🏗');
      t.text('description');
      t.integer('hourly_rate').defaultTo(0);
      t.integer('daily_rate').defaultTo(0);
      t.integer('weekly_rate').defaultTo(0);
      t.string('currency').defaultTo('KES');
      t.boolean('available').defaultTo(true);
      t.string('location').defaultTo('Nairobi, Kenya');
      t.text('specs').defaultTo('{}');
      t.float('rating').defaultTo(4.5);
      t.integer('review_count').defaultTo(0);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── transport ──────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('transport')) {
    await knex.schema.createTable('transport', t => {
      t.increments('id');
      t.integer('owner_id').references('id').inTable('users');
      t.string('name').notNullable();
      t.string('type');
      t.string('icon').defaultTo('🚛');
      t.float('rating').defaultTo(4.5);
      t.integer('review_count').defaultTo(0);
      t.string('rate_text');
      t.string('location');
      t.string('capacity');
      t.text('modes').defaultTo('[]');
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── messages ───────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('messages')) {
    await knex.schema.createTable('messages', t => {
      t.increments('id');
      t.integer('sender_id').references('id').inTable('users');
      t.integer('recipient_id').references('id').inTable('users');
      t.integer('project_id').references('id').inTable('projects');
      t.text('content').notNullable();
      t.boolean('read').defaultTo(false);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── reviews ────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('reviews')) {
    await knex.schema.createTable('reviews', t => {
      t.increments('id');
      t.integer('reviewer_id').references('id').inTable('users');
      t.integer('reviewee_id').references('id').inTable('users');
      t.integer('project_id').references('id').inTable('projects');
      t.integer('rating');
      t.text('comment');
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── invoices ───────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('invoices')) {
    await knex.schema.createTable('invoices', t => {
      t.increments('id');
      t.integer('project_id').references('id').inTable('projects');
      t.integer('issuer_id').references('id').inTable('users');
      t.integer('recipient_id').references('id').inTable('users');
      t.integer('amount').notNullable();
      t.string('currency').defaultTo('KES');
      t.string('milestone');
      t.text('description');
      t.string('status').defaultTo('pending');
      t.string('escrow_status').defaultTo('held');
      t.date('due_date');
      t.timestamp('created_at').defaultTo(knex.fn.now());
      t.timestamp('paid_at');
    });
  }

  /* ── material_prices ────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('material_prices')) {
    await knex.schema.createTable('material_prices', t => {
      t.increments('id');
      t.string('item').notNullable();
      t.string('price').notNullable();
      t.string('unit');
      t.string('change_pct');
      t.string('direction').defaultTo('flat');
      t.timestamp('updated_at').defaultTo(knex.fn.now());
    });
  }

  /* ── notifications ──────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('notifications')) {
    await knex.schema.createTable('notifications', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.string('type').defaultTo('system');
      t.string('title');
      t.text('body');
      t.string('link');
      t.boolean('read').defaultTo(false);
      t.timestamp('created_at').defaultTo(knex.fn.now());
    });
  }

  /* ── cart ───────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('cart')) {
    await knex.schema.createTable('cart', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.integer('material_id').references('id').inTable('materials');
      t.integer('quantity').defaultTo(1);
      t.timestamp('added_at').defaultTo(knex.fn.now());
    });
  }

  /* ── saved_professionals ────────────────────────────────────────── */
  if (!await knex.schema.hasTable('saved_professionals')) {
    await knex.schema.createTable('saved_professionals', t => {
      t.increments('id');
      t.integer('user_id').references('id').inTable('users').onDelete('CASCADE');
      t.integer('professional_id').references('id').inTable('users').onDelete('CASCADE');
      t.timestamp('saved_at').defaultTo(knex.fn.now());
      t.unique(['user_id', 'professional_id']);
    });
  }
};

exports.down = async function(knex) {
  const tables = [
    'saved_professionals','cart','notifications','material_prices',
    'invoices','reviews','messages','transport','equipment','materials',
    'bid_applications','bid_trades','bids','project_invitations',
    'project_team','projects','professional_tags','professional_profiles','users',
  ];
  for (const t of tables) await knex.schema.dropTableIfExists(t);
};
