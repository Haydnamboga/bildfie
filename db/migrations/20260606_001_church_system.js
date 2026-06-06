'use strict';
/**
 * Church Field Management System — Nyamira West Field
 * Full schema: users, churches, members, departments, services,
 * attendance, events, projects, tasks, giving, announcements,
 * sermons, reports, communications.
 */

exports.up = async function(knex) {

  /* ── Extend users table ──────────────────────────────────────────── */
  if (await knex.schema.hasTable('users')) {
    const hasChurchId = await knex.schema.hasColumn('users', 'church_id');
    if (!hasChurchId) {
      await knex.schema.table('users', t => {
        t.integer('church_id').nullable();
        t.string('system_role').defaultTo('member');
      });
    }
    const hasSystemRole = await knex.schema.hasColumn('users', 'system_role');
    // already handled above
    const hasGender = await knex.schema.hasColumn('users', 'gender');
    if (!hasGender) {
      await knex.schema.table('users', t => { t.string('gender'); });
    }
    const hasDob = await knex.schema.hasColumn('users', 'dob');
    if (!hasDob) {
      await knex.schema.table('users', t => { t.date('dob'); });
    }
    const hasProfilePic = await knex.schema.hasColumn('users', 'profile_pic');
    if (!hasProfilePic) {
      await knex.schema.table('users', t => { t.string('profile_pic'); });
    }
    const hasAddress = await knex.schema.hasColumn('users', 'address');
    if (!hasAddress) {
      await knex.schema.table('users', t => { t.text('address'); });
    }
    const hasChurchActive = await knex.schema.hasColumn('users', 'church_active');
    if (!hasChurchActive) {
      await knex.schema.table('users', t => { t.boolean('church_active').defaultTo(true); });
    }
  }

  /* ── churches ────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('churches')) {
    await knex.schema.createTable('churches', t => {
      t.increments('id');
      t.string('name').notNullable();
      t.string('code').unique();
      t.string('county').defaultTo('Nyamira');
      t.string('sub_county');
      t.string('ward');
      t.string('village');
      t.text('address');
      t.string('pastor_name');
      t.string('pastor_email');
      t.string('pastor_phone');
      t.date('established_date');
      t.string('status').defaultTo('active'); // active, inactive, planting
      t.string('church_type').defaultTo('local'); // local, company, group
      t.integer('capacity');
      t.string('logo_url');
      t.string('website_url');
      t.text('description');
      t.float('location_lat');
      t.float('location_lng');
      t.boolean('has_land').defaultTo(false);
      t.boolean('has_building').defaultTo(false);
      t.string('building_status'); // rented, owned, under_construction
      t.timestamps(true, true);
    });
  }

  /* ── members ──────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('members')) {
    await knex.schema.createTable('members', t => {
      t.increments('id');
      t.integer('church_id').notNullable().references('id').inTable('churches').onDelete('CASCADE');
      t.integer('user_id').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.string('first_name').notNullable();
      t.string('last_name').notNullable();
      t.string('email');
      t.string('phone');
      t.date('dob');
      t.string('gender'); // male, female
      t.text('address');
      t.date('baptism_date');
      t.date('joined_date');
      t.string('membership_status').defaultTo('active'); // active, inactive, transferred, deceased
      t.string('marital_status'); // single, married, divorced, widowed
      t.string('occupation');
      t.string('education_level');
      t.string('profile_pic');
      t.text('notes');
      t.boolean('is_visitor').defaultTo(false);
      t.timestamps(true, true);
    });
  }

  /* ── departments ─────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('departments')) {
    await knex.schema.createTable('departments', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.boolean('field_level').defaultTo(false); // field-wide departments
      t.string('name').notNullable(); // Youth, Women, Men, Children, Music, Evangelism, Elders, Deacons, AYS, AHS, etc.
      t.string('code'); // YF, WM, MM, CM, MU, EV, EL, DC
      t.text('description');
      t.integer('leader_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('meeting_day'); // Sabbath, Friday, Wednesday
      t.string('meeting_time');
      t.string('status').defaultTo('active');
      t.timestamps(true, true);
    });
  }

  /* ── department_members ───────────────────────────────────────────── */
  if (!await knex.schema.hasTable('department_members')) {
    await knex.schema.createTable('department_members', t => {
      t.increments('id');
      t.integer('department_id').notNullable().references('id').inTable('departments').onDelete('CASCADE');
      t.integer('member_id').notNullable().references('id').inTable('members').onDelete('CASCADE');
      t.string('role_in_dept').defaultTo('member'); // leader, secretary, treasurer, member
      t.date('joined_at');
      t.unique(['department_id','member_id']);
    });
  }

  /* ── services ────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('services')) {
    await knex.schema.createTable('services', t => {
      t.increments('id');
      t.integer('church_id').notNullable().references('id').inTable('churches').onDelete('CASCADE');
      t.string('title').notNullable();
      t.string('service_type').defaultTo('sabbath'); // sabbath, midweek, prayer, special, evangelism, camp_meeting
      t.timestamp('scheduled_at');
      t.integer('officiant_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.integer('preacher_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('theme');
      t.string('scripture_reference');
      t.text('program_notes');
      t.string('status').defaultTo('scheduled'); // scheduled, completed, cancelled
      t.integer('members_present').defaultTo(0);
      t.integer('visitors_present').defaultTo(0);
      t.integer('children_present').defaultTo(0);
      t.timestamps(true, true);
    });
  }

  /* ── attendance ───────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('attendance')) {
    await knex.schema.createTable('attendance', t => {
      t.increments('id');
      t.integer('service_id').notNullable().references('id').inTable('services').onDelete('CASCADE');
      t.integer('member_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('visitor_name');
      t.string('visitor_phone');
      t.timestamp('checked_in_at').defaultTo(knex.fn.now());
      t.unique(['service_id','member_id']);
    });
  }

  /* ── events ──────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('events')) {
    await knex.schema.createTable('events', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.boolean('field_wide').defaultTo(false);
      t.string('title').notNullable();
      t.text('description');
      t.string('event_type'); // crusade, camp_meeting, seminar, training, sports, outreach, fundraiser, burial, wedding, dedication
      t.timestamp('start_date');
      t.timestamp('end_date');
      t.string('venue');
      t.integer('organizer_id').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.string('status').defaultTo('upcoming'); // upcoming, ongoing, completed, cancelled
      t.integer('expected_attendance');
      t.integer('actual_attendance');
      t.decimal('budget', 14, 2).defaultTo(0);
      t.decimal('actual_cost', 14, 2).defaultTo(0);
      t.string('image_url');
      t.timestamps(true, true);
    });
  }

  /* ── projects ─────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('projects')) {
    await knex.schema.createTable('projects', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable(); // null = field project
      t.string('title').notNullable();
      t.text('description');
      t.string('category'); // construction, evangelism, education, health, social, spiritual, administrative
      t.string('status').defaultTo('planning'); // planning, active, on_hold, completed, cancelled
      t.string('priority').defaultTo('medium'); // low, medium, high, critical
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

  /* ── tasks ────────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('tasks')) {
    await knex.schema.createTable('tasks', t => {
      t.increments('id');
      t.integer('project_id').references('id').inTable('projects').onDelete('SET NULL').nullable();
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.string('title').notNullable();
      t.text('description');
      t.integer('assigned_to').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.integer('assigned_by').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.date('due_date');
      t.string('priority').defaultTo('medium');
      t.string('status').defaultTo('pending'); // pending, in_progress, completed, cancelled
      t.text('completion_notes');
      t.timestamp('completed_at');
      t.timestamps(true, true);
    });
  }

  /* ── giving ───────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('giving')) {
    await knex.schema.createTable('giving', t => {
      t.increments('id');
      t.integer('church_id').notNullable().references('id').inTable('churches').onDelete('CASCADE');
      t.integer('member_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('giving_type').defaultTo('tithe'); // tithe, offering, building_fund, special, camp_meeting, bible_school
      t.decimal('amount', 14, 2).notNullable();
      t.date('date').notNullable();
      t.string('currency').defaultTo('KES');
      t.string('description');
      t.string('reference_no');
      t.integer('recorded_by').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.integer('service_id').references('id').inTable('services').onDelete('SET NULL').nullable();
      t.timestamps(true, true);
    });
  }

  /* ── announcements ────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('announcements')) {
    await knex.schema.createTable('announcements', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.boolean('field_wide').defaultTo(false);
      t.string('title').notNullable();
      t.text('content').notNullable();
      t.string('priority').defaultTo('normal'); // normal, important, urgent
      t.timestamp('published_at').defaultTo(knex.fn.now());
      t.timestamp('expires_at');
      t.integer('author_id').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.boolean('active').defaultTo(true);
      t.timestamps(true, true);
    });
  }

  /* ── sermons ──────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('sermons')) {
    await knex.schema.createTable('sermons', t => {
      t.increments('id');
      t.integer('church_id').notNullable().references('id').inTable('churches').onDelete('CASCADE');
      t.integer('service_id').references('id').inTable('services').onDelete('SET NULL').nullable();
      t.string('title').notNullable();
      t.string('preacher').notNullable();
      t.string('scripture_reference');
      t.text('summary');
      t.text('notes');
      t.string('audio_url');
      t.string('video_url');
      t.date('sermon_date');
      t.timestamps(true, true);
    });
  }

  /* ── field_communications ─────────────────────────────────────────── */
  if (!await knex.schema.hasTable('field_communications')) {
    await knex.schema.createTable('field_communications', t => {
      t.increments('id');
      t.integer('from_church_id').references('id').inTable('churches').onDelete('SET NULL').nullable();
      t.integer('to_church_id').references('id').inTable('churches').onDelete('SET NULL').nullable();
      t.integer('sender_id').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.boolean('to_field').defaultTo(false);
      t.boolean('from_field').defaultTo(false);
      t.string('subject').notNullable();
      t.text('message').notNullable();
      t.string('status').defaultTo('unread'); // unread, read, replied, archived
      t.string('message_type').defaultTo('general'); // general, report_request, approval, directive, response
      t.integer('reply_to').references('id').inTable('field_communications').onDelete('SET NULL').nullable();
      t.timestamps(true, true);
    });
  }

  /* ── reports ──────────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('reports')) {
    await knex.schema.createTable('reports', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.boolean('field_report').defaultTo(false);
      t.string('report_type').notNullable(); // attendance, financial, membership, quarterly, annual, special
      t.string('period_label'); // e.g. "Q1 2026", "January 2026"
      t.date('period_start');
      t.date('period_end');
      t.json('data');
      t.text('summary');
      t.string('status').defaultTo('draft'); // draft, submitted, reviewed, approved
      t.integer('generated_by').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.integer('reviewed_by').references('id').inTable('users').onDelete('SET NULL').nullable();
      t.timestamp('submitted_at');
      t.timestamps(true, true);
    });
  }

  /* ── prayer_requests ──────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('prayer_requests')) {
    await knex.schema.createTable('prayer_requests', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.boolean('field_wide').defaultTo(false);
      t.integer('member_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('name'); // for non-members
      t.text('request').notNullable();
      t.boolean('anonymous').defaultTo(false);
      t.boolean('answered').defaultTo(false);
      t.text('testimony');
      t.timestamps(true, true);
    });
  }

  /* ── volunteers ───────────────────────────────────────────────────── */
  if (!await knex.schema.hasTable('volunteers')) {
    await knex.schema.createTable('volunteers', t => {
      t.increments('id');
      t.integer('church_id').references('id').inTable('churches').onDelete('CASCADE').nullable();
      t.integer('event_id').references('id').inTable('events').onDelete('CASCADE').nullable();
      t.integer('project_id').references('id').inTable('projects').onDelete('CASCADE').nullable();
      t.integer('member_id').references('id').inTable('members').onDelete('SET NULL').nullable();
      t.string('name');
      t.string('phone');
      t.string('role');
      t.string('status').defaultTo('confirmed'); // confirmed, pending, withdrawn
      t.timestamps(true, true);
    });
  }

};

exports.down = async function(knex) {
  const tables = [
    'volunteers','prayer_requests','reports','field_communications',
    'sermons','announcements','giving','tasks','projects','events',
    'attendance','services','department_members','departments','members','churches'
  ];
  for (const t of tables) await knex.schema.dropTableIfExists(t);
};
