'use strict';
const bcrypt = require('bcryptjs');

exports.seed = async function(knex) {
  // Clear church tables (in reverse FK order)
  await knex('department_members').del();
  await knex('volunteers').del();
  await knex('prayer_requests').del();
  await knex('reports').del();
  await knex('field_communications').del();
  await knex('sermons').del();
  await knex('announcements').del();
  await knex('giving').del();
  await knex('tasks').del();
  await knex('church_projects').del();
  await knex('attendance').del();
  await knex('services').del();
  await knex('events').del();
  await knex('department_members').del();
  await knex('departments').del();
  await knex('members').del();
  await knex('churches').del();

  // Field admin user
  const hash = await bcrypt.hash('admin123', 12);
  const existingAdmin = await knex('users').where('email', 'admin@nyamirawest.sda').first();
  let adminId;
  if (!existingAdmin) {
    adminId = await knex('users').insert({
      name: 'Field Administrator', email: 'admin@nyamirawest.sda',
      password_hash: hash, system_role: 'field_admin', role: 'admin', verified: true,
    }).then(r => Array.isArray(r) ? r[0] : r);
  } else {
    await knex('users').where('id', existingAdmin.id).update({ system_role: 'field_admin' });
    adminId = existingAdmin.id;
  }

  // Churches
  const churchIds = await knex('churches').insert([
    { name: 'Nyamira Central SDA', code: 'NWF-001', county: 'Nyamira', sub_county: 'Nyamira North', ward: 'Central', pastor_name: 'Pr. James Ombongi', pastor_phone: '+254 712 000 001', status: 'active', church_type: 'local', capacity: 300, has_land: true, has_building: true, building_status: 'owned', established_date: '1985-03-15', description: 'The oldest SDA church in Nyamira West Field.' },
    { name: 'Nyansiongo SDA Church', code: 'NWF-002', county: 'Nyamira', sub_county: 'Nyamira South', ward: 'Nyansiongo', pastor_name: 'Pr. Grace Kemunto', pastor_phone: '+254 713 000 002', status: 'active', church_type: 'local', capacity: 150, has_land: true, has_building: true, building_status: 'owned', established_date: '1998-06-01' },
    { name: 'Manga SDA Church', code: 'NWF-003', county: 'Nyamira', sub_county: 'Manga', ward: 'Manga', pastor_name: 'Pr. David Bosire', pastor_phone: '+254 714 000 003', status: 'active', church_type: 'local', capacity: 200, has_land: true, has_building: false, building_status: 'under_construction', established_date: '2005-01-20' },
    { name: 'Kebirigo SDA Church', code: 'NWF-004', county: 'Nyamira', sub_county: 'West Mugirango', ward: 'Kebirigo', pastor_name: 'Pr. Ruth Nyaboke', pastor_phone: '+254 715 000 004', status: 'active', church_type: 'local', capacity: 100, has_land: false, building_status: 'rented', established_date: '2010-09-10' },
    { name: 'Bogichora Company Church', code: 'NWF-005', county: 'Nyamira', sub_county: 'Masaba North', ward: 'Bogichora', status: 'active', church_type: 'company', capacity: 60, has_land: false, building_status: 'rented', established_date: '2015-04-01' },
    { name: 'Esise Church Plant', code: 'NWF-006', county: 'Nyamira', sub_county: 'Nyamira North', ward: 'Esise', status: 'planting', church_type: 'group', capacity: 30, established_date: '2023-07-01' },
  ]).returning('id');

  // Get actual IDs
  const churches = await knex('churches').orderBy('id').select('id', 'name');
  const [c1, c2, c3, c4] = churches;

  // Church admin user
  const churchAdminHash = await bcrypt.hash('church123', 12);
  const existingChurchAdmin = await knex('users').where('email', 'pastor@nyamiracentral.sda').first();
  let churchAdminId;
  if (!existingChurchAdmin) {
    churchAdminId = await knex('users').insert({
      name: 'Pr. James Ombongi', email: 'pastor@nyamiracentral.sda',
      password_hash: churchAdminHash, system_role: 'church_admin', role: 'client',
      church_id: c1.id, verified: true,
    }).then(r => Array.isArray(r) ? r[0] : r);
  } else {
    await knex('users').where('id', existingChurchAdmin.id).update({ system_role: 'church_admin', church_id: c1.id });
    churchAdminId = existingChurchAdmin.id;
  }

  // Members for church 1
  const memberIds = await knex('members').insert([
    { church_id: c1.id, first_name: 'Mary', last_name: 'Achieng', phone: '+254 720 100 001', gender: 'female', membership_status: 'active', baptism_date: '2010-03-12', joined_date: '2010-03-12', marital_status: 'married', occupation: 'Teacher' },
    { church_id: c1.id, first_name: 'Peter', last_name: 'Mwangi', phone: '+254 720 100 002', gender: 'male', membership_status: 'active', baptism_date: '2005-06-15', joined_date: '2005-06-15', marital_status: 'married', occupation: 'Farmer' },
    { church_id: c1.id, first_name: 'Sarah', last_name: 'Moraa', phone: '+254 720 100 003', gender: 'female', membership_status: 'active', joined_date: '2018-01-01', marital_status: 'single', occupation: 'Student' },
    { church_id: c1.id, first_name: 'John', last_name: 'Omari', phone: '+254 720 100 004', gender: 'male', membership_status: 'active', baptism_date: '2015-09-20', joined_date: '2015-09-20', marital_status: 'married' },
    { church_id: c2.id, first_name: 'Agnes', last_name: 'Nyaboke', phone: '+254 721 200 001', gender: 'female', membership_status: 'active', baptism_date: '2012-04-08', joined_date: '2012-04-08', marital_status: 'married' },
    { church_id: c2.id, first_name: 'James', last_name: 'Onkundi', phone: '+254 721 200 002', gender: 'male', membership_status: 'active', baptism_date: '2008-02-14', joined_date: '2008-02-14', marital_status: 'married' },
    { church_id: c3.id, first_name: 'Rebecca', last_name: 'Gesare', phone: '+254 722 300 001', gender: 'female', membership_status: 'active', baptism_date: '2019-11-30', joined_date: '2019-11-30', marital_status: 'single' },
  ]).returning('id');
  const members = await knex('members').where('church_id', c1.id).select('id', 'first_name');

  // Departments for church 1
  const deptList = await knex('departments').insert([
    { church_id: c1.id, name: "Youth Fellowship", code: "YF", status: "active", meeting_day: "Sabbath", meeting_time: "9:00 AM" },
    { church_id: c1.id, name: "Women's Ministry", code: "WM", status: "active", meeting_day: "Thursday", meeting_time: "3:00 PM" },
    { church_id: c1.id, name: "Men's Ministry", code: "MM", status: "active", meeting_day: "Wednesday", meeting_time: "6:00 PM" },
    { church_id: c1.id, name: "Pathfinder Club", code: "PF", status: "active", meeting_day: "Saturday", meeting_time: "2:00 PM" },
    { church_id: c1.id, name: "Music Ministry", code: "MU", status: "active" },
    { church_id: c1.id, name: "Community Services", code: "CS", status: "active" },
  ]).returning('id');

  // Services
  const today = new Date();
  const lastSabbath = new Date(today);
  lastSabbath.setDate(today.getDate() - today.getDay() + 6 - 7);

  const serviceIds = await knex('services').insert([
    { church_id: c1.id, title: 'Sabbath Morning Service', service_type: 'sabbath', scheduled_at: new Date(lastSabbath).toISOString(), members_present: 87, visitors_present: 12, children_present: 15, status: 'completed', theme: 'Faith in Action', scripture_reference: 'Hebrews 11:1' },
    { church_id: c1.id, title: 'Midweek Bible Study', service_type: 'midweek', scheduled_at: new Date(today.getFullYear(), today.getMonth(), today.getDate()-3).toISOString(), members_present: 34, visitors_present: 3, status: 'completed', theme: 'The Book of Daniel' },
    { church_id: c2.id, title: 'Sabbath Morning Service', service_type: 'sabbath', scheduled_at: new Date(lastSabbath).toISOString(), members_present: 55, visitors_present: 8, status: 'completed' },
  ]).returning('id');

  // Projects
  await knex('church_projects').insert([
    { title: 'Field Evangelism Campaign 2026', description: 'Field-wide evangelism targeting 500 decisions for Christ', category: 'evangelism', status: 'active', priority: 'critical', budget: 500000, spent: 125000, manager_id: adminId, completion_percent: 25, start_date: '2026-01-01', end_date: '2026-12-31' },
    { church_id: c3.id, title: 'Manga Church Building Project', description: 'Complete the construction of the main church building', category: 'construction', status: 'active', priority: 'high', budget: 2000000, spent: 650000, completion_percent: 32, start_date: '2025-06-01', end_date: '2027-03-31' },
    { church_id: c1.id, title: 'Digital Sound System Upgrade', description: 'Upgrade the PA system for better service quality', category: 'administrative', status: 'planning', priority: 'medium', budget: 85000, spent: 0, completion_percent: 0 },
  ]);

  // Events
  const future = new Date(today);
  future.setDate(today.getDate() + 14);
  await knex('events').insert([
    { title: 'Youth Camp Meeting 2026', event_type: 'camp_meeting', field_wide: true, start_date: new Date(future).toISOString(), end_date: new Date(future.setDate(future.getDate()+3)).toISOString(), venue: 'Nyamira Primary School Grounds', status: 'upcoming', expected_attendance: 500, budget: 250000, organizer_id: adminId, description: 'Annual field-wide camp meeting for all youth ministries.' },
    { church_id: c1.id, title: 'Nyamira Central Evangelism Week', event_type: 'crusade', field_wide: false, start_date: new Date(today.getFullYear(), today.getMonth()+1, 1).toISOString(), end_date: new Date(today.getFullYear(), today.getMonth()+1, 7).toISOString(), venue: 'Central Market Grounds', status: 'upcoming', expected_attendance: 300, budget: 80000, organizer_id: adminId },
    { title: "Pastor's Seminar — Financial Stewardship", event_type: 'seminar', field_wide: true, start_date: new Date(today.getFullYear(), today.getMonth()+2, 10).toISOString(), venue: 'Field Office Conference Hall', status: 'upcoming', expected_attendance: 50, budget: 30000 },
  ]);

  // Announcements
  await knex('announcements').insert([
    { field_wide: true, title: 'Youth Camp Meeting Registration Open', content: 'Registration for the 2026 Youth Camp Meeting is now open. All churches are encouraged to register their youth by end of this month. Contact the field office for registration forms.', priority: 'important', author_id: adminId, active: true },
    { field_wide: true, title: 'Quarterly Report Deadline', content: 'All churches are reminded to submit their Q1 2026 reports to the field office by 30th March 2026. Reports should include attendance, giving, and membership statistics.', priority: 'urgent', author_id: adminId, active: true },
    { church_id: c1.id, title: 'Building Fund Drive', content: 'We are collecting towards the sound system upgrade. Please contribute generously during the special collection this Sabbath.', priority: 'normal', author_id: adminId, active: true },
  ]);

  // Giving records
  const thisMonth = new Date().toISOString().slice(0, 7);
  await knex('giving').insert([
    { church_id: c1.id, giving_type: 'tithe', amount: 15000, date: `${thisMonth}-01`, currency: 'KES', recorded_by: churchAdminId },
    { church_id: c1.id, giving_type: 'tithe', amount: 12500, date: `${thisMonth}-08`, currency: 'KES', recorded_by: churchAdminId },
    { church_id: c1.id, giving_type: 'offering', amount: 8200, date: `${thisMonth}-01`, currency: 'KES', recorded_by: churchAdminId },
    { church_id: c1.id, giving_type: 'building_fund', amount: 5000, date: `${thisMonth}-15`, currency: 'KES', recorded_by: churchAdminId },
    { church_id: c2.id, giving_type: 'tithe', amount: 9500, date: `${thisMonth}-01`, currency: 'KES' },
    { church_id: c2.id, giving_type: 'offering', amount: 4300, date: `${thisMonth}-01`, currency: 'KES' },
  ]);

  // Tasks
  await knex('tasks').insert([
    { church_id: c1.id, title: 'Prepare Sabbath School quarterly reports', description: 'Compile attendance and lesson completion stats', assigned_to: churchAdminId, assigned_by: adminId, due_date: '2026-03-30', priority: 'medium', status: 'pending' },
    { church_id: c1.id, title: 'Follow up with new visitors from last Sabbath', assigned_by: churchAdminId, due_date: '2026-06-08', priority: 'high', status: 'pending' },
    { title: 'Coordinate youth camp registration across all churches', assigned_to: adminId, assigned_by: adminId, due_date: '2026-06-20', priority: 'high', status: 'in_progress' },
  ]);

  // Communications
  await knex('field_communications').insert([
    { from_church_id: c2.id, to_field: true, subject: 'Request for Evangelism Support', message: 'We would like to request the field office to support our upcoming crusade with a trained evangelist and PA equipment.', message_type: 'general', status: 'unread' },
    { to_church_id: c1.id, from_field: true, subject: 'Q1 Report Reminder', message: 'Please ensure your Q1 2026 church report is submitted to the field office by March 30th, 2026.', message_type: 'directive', sender_id: adminId, status: 'read' },
  ]);

  console.log('✅ Church seed data inserted successfully');
  console.log('   Field Admin: admin@nyamirawest.sda / admin123');
  console.log('   Church Admin: pastor@nyamiracentral.sda / church123');
};
