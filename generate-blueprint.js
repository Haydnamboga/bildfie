'use strict';
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, BorderStyle, ShadingType,
} = require('docx');
const fs = require('fs');

function h1(text) {
  return new Paragraph({
    text,
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 400, after: 120 },
  });
}

function h2(text) {
  return new Paragraph({
    text,
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 300, after: 100 },
  });
}

function h3(text) {
  return new Paragraph({
    text,
    heading: HeadingLevel.HEADING_3,
    spacing: { before: 200, after: 80 },
  });
}

function p(text) {
  return new Paragraph({
    children: [new TextRun({ text, size: 24 })],
    spacing: { before: 80, after: 80 },
  });
}

function bullet(text, level = 0) {
  return new Paragraph({
    children: [new TextRun({ text, size: 24 })],
    bullet: { level },
    spacing: { before: 40, after: 40 },
  });
}

function tableRow(cells, bold = false) {
  return new TableRow({
    children: cells.map(text =>
      new TableCell({
        children: [new Paragraph({
          children: [new TextRun({ text: String(text), size: 22, bold })],
          spacing: { before: 60, after: 60 },
        })],
        margins: { top: 60, bottom: 60, left: 100, right: 100 },
      })
    ),
  });
}

function simpleTable(headers, rows) {
  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    rows: [
      tableRow(headers, true),
      ...rows.map(r => tableRow(r)),
    ],
  });
}

function blank() {
  return new Paragraph({ text: '', spacing: { before: 60, after: 60 } });
}

const doc = new Document({
  styles: {
    default: {
      document: {
        run: { font: 'Times New Roman', size: 24 },
      },
    },
  },
  sections: [{
    properties: {},
    children: [

      // ── TITLE ──────────────────────────────────────────────────────
      new Paragraph({
        children: [new TextRun({ text: 'Nyamira West Field', bold: true, size: 36 })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 80 },
      }),
      new Paragraph({
        children: [new TextRun({ text: 'Church Field Management System', bold: true, size: 28 })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 80 },
      }),
      new Paragraph({
        children: [new TextRun({ text: 'System Blueprint and Workflow Documentation', size: 24, italics: true })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 80 },
      }),
      new Paragraph({
        children: [new TextRun({ text: 'Version 1.0  |  June 2026', size: 22 })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 600 },
      }),

      // ── SECTION 1 ─────────────────────────────────────────────────
      h1('1. Overview'),
      p('Nyamira West Field is a Seventh-day Adventist church field in Kenya. This system is a digital platform that connects the field office with its affiliated local churches and their members. It replaces paper-based reporting, manual attendance records, and informal communication with a structured, internet-based management system that also works offline when there is no internet connection.'),
      blank(),
      p('The system has four parts that work together:'),
      bullet('Public Field Website — accessible to anyone, shows field news, events, and affiliated churches.'),
      bullet('Member Portal — for registered members of any affiliated church. Members can view their own records, submit prayer requests, register for events, and communicate with their church.'),
      bullet('Church Admin Portal — for church workers (pastors, elders, department leaders). They record attendance, services, giving, manage members, and submit reports to the field.'),
      bullet('Field CRM — for field-level administrators. They oversee all churches, send directives, review reports, manage projects, and generate field-wide reports.'),
      blank(),

      // ── SECTION 2 ─────────────────────────────────────────────────
      h1('2. Decisions Made'),
      p('The following decisions were made before design began. They shape everything in this document.'),
      blank(),
      simpleTable(
        ['Decision', 'Choice Made', 'Reason'],
        [
          ['Hosting and deployment', 'Deferred to later phase', 'Will be decided when the system is ready for production'],
          ['Member registration', 'Self-registration, with admin approval required', 'Members sign up themselves; a church admin must approve each account before access is granted'],
          ['Giving records visibility', 'Private — members cannot see their own giving amounts online', 'Sensitive financial data stays with church administration only'],
          ['Notification channels', 'WhatsApp (primary) and Email (secondary)', 'Most members in the area use WhatsApp more than email'],
          ['Offline capability', 'Yes — the system will work as a PWA with offline support', 'Internet is unreliable in some areas; church workers must be able to record data offline'],
          ['Language', 'English only for now', 'Kiswahili and other languages can be added later'],
          ['Tithe receipts', 'Included in data model, but PDF generation deferred to a later phase', 'The structure is built now; actual PDF output will come in a future update'],
        ]
      ),
      blank(),

      // ── SECTION 3 ─────────────────────────────────────────────────
      h1('3. User Roles'),
      p('The system has six user roles. Each role sees and does only what is assigned to it.'),
      blank(),
      simpleTable(
        ['Role', 'Who They Are', 'What They Can Do'],
        [
          ['field_admin', 'Field director, field secretary', 'Full access to all churches, all data, all reports, system settings'],
          ['field_staff', 'Field department leaders, field treasurer', 'Access to reports, communications, and their specific department data across all churches'],
          ['church_admin', 'Pastor, church clerk', 'Full access to their own church: members, services, giving, reports, tasks'],
          ['church_elder', 'Elders and deacons', 'Can record services, attendance, take prayer requests; cannot see financial records'],
          ['department_leader', 'Pathfinder director, Sabbath School leader, etc.', 'Manages their own department: attendance, events, volunteers'],
          ['member', 'Regular church member', 'Views their own profile, submits prayer requests, registers for events, reads announcements'],
        ]
      ),
      blank(),

      // ── SECTION 4 ─────────────────────────────────────────────────
      h1('4. Notification Triggers'),
      p('The following events automatically send a notification. WhatsApp is used as the primary channel via Africa\'s Talking API. Email is the secondary channel via Nodemailer.'),
      blank(),
      simpleTable(
        ['Event', 'Who Gets Notified', 'Channel'],
        [
          ['New member registers', 'Church admin of that church', 'WhatsApp + Email'],
          ['Church admin approves a member', 'The new member', 'WhatsApp + Email'],
          ['Church admin rejects a member', 'The new member', 'WhatsApp + Email'],
          ['Field sends a new directive or announcement', 'All church admins in the field', 'WhatsApp + Email'],
          ['Church submits a monthly report', 'Assigned field staff reviewer', 'Email'],
          ['Field admin comments on a church report', 'Church admin of that church', 'WhatsApp + Email'],
          ['A new task is assigned to a church', 'Church admin of that church', 'WhatsApp + Email'],
          ['A task deadline is approaching (3 days before)', 'Assigned church admin', 'WhatsApp'],
          ['A new event is published by the field', 'All church admins', 'WhatsApp + Email'],
          ['A prayer request is submitted', 'Church admin and assigned elder', 'Email'],
        ]
      ),
      blank(),

      // ── SECTION 5 ─────────────────────────────────────────────────
      h1('5. Offline Strategy (PWA)'),
      p('The system is built as a Progressive Web App. This means it can be installed on a phone or computer and used without internet. The following describes what works offline and what requires internet.'),
      blank(),
      h2('5.1 What Works Offline'),
      bullet('Recording service attendance'),
      bullet('Recording giving for a service'),
      bullet('Writing meeting minutes or sermon notes'),
      bullet('Viewing previously loaded member lists'),
      bullet('Viewing previously loaded church calendar and events'),
      blank(),
      h2('5.2 What Requires Internet'),
      bullet('Submitting reports to the field'),
      bullet('Approving or rejecting member registrations'),
      bullet('Sending notifications or messages'),
      bullet('Loading new announcements or directives from the field'),
      bullet('Generating PDF reports'),
      blank(),
      h2('5.3 Sync and Conflict Resolution'),
      p('When internet returns, the app syncs offline data to the server automatically using a background sync queue. If a conflict is detected (for example, the same record was edited online and offline), the server-side version wins and the user is shown a notification about the conflict. All sync events are logged.'),
      blank(),

      // ── SECTION 6 ─────────────────────────────────────────────────
      h1('6. Data Model'),
      p('The following tables make up the database. Each table is listed with its columns and a brief description of what each column holds. This is the complete structure; no table is left out.'),
      blank(),

      h2('users'),
      p('Stores all system users across all roles.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', 'Unique user ID'],
          ['name', 'string', 'Full name'],
          ['email', 'string, unique', 'Login email address'],
          ['phone', 'string', 'WhatsApp-capable phone number'],
          ['password_hash', 'string', 'Bcrypt-hashed password'],
          ['role', 'string', 'One of: field_admin, field_staff, church_admin, church_elder, department_leader, member'],
          ['church_id', 'integer, nullable', 'Links to the church this user belongs to; null for field-level users'],
          ['status', 'string', 'pending, active, or suspended'],
          ['avatar_url', 'string, nullable', 'Profile photo path'],
          ['last_login_at', 'datetime, nullable', 'Timestamp of last successful login'],
          ['created_at', 'datetime', 'Account creation time'],
          ['updated_at', 'datetime', 'Last record update time'],
        ]
      ),
      blank(),

      h2('churches'),
      p('Each affiliated local church in the field.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', 'Unique church ID'],
          ['name', 'string', 'Official church name'],
          ['location', 'string', 'Town or village where the church is located'],
          ['district', 'string', 'The district within Nyamira West Field'],
          ['pastor_user_id', 'integer, nullable', 'Links to the user who is the current pastor'],
          ['phone', 'string, nullable', 'Church contact number'],
          ['email', 'string, nullable', 'Church contact email'],
          ['established_year', 'integer, nullable', 'Year the church was established'],
          ['logo_url', 'string, nullable', 'Church logo image path'],
          ['status', 'string', 'active or inactive'],
          ['created_at', 'datetime', ''],
          ['updated_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('members'),
      p('Church-specific member profiles, linked to a user account.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['user_id', 'integer', 'Links to users table'],
          ['church_id', 'integer', 'Links to churches table'],
          ['membership_number', 'string, nullable', 'Local membership number if assigned'],
          ['baptism_date', 'date, nullable', ''],
          ['transfer_from', 'string, nullable', 'Previous church if transferred in'],
          ['transfer_to', 'string, nullable', 'Destination church if transferred out'],
          ['gender', 'string', 'male or female'],
          ['date_of_birth', 'date, nullable', ''],
          ['occupation', 'string, nullable', ''],
          ['address', 'string, nullable', ''],
          ['status', 'string', 'active, transferred, deceased, or disfellowshipped'],
          ['created_at', 'datetime', ''],
          ['updated_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('departments'),
      p('Church departments such as Sabbath School, Pathfinders, Health, Stewardship, etc.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer', 'Links to churches table'],
          ['name', 'string', 'Department name'],
          ['leader_user_id', 'integer, nullable', 'Links to users table — the department leader'],
          ['description', 'text, nullable', ''],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('department_members'),
      p('Many-to-many link between members and departments.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['department_id', 'integer', ''],
          ['member_id', 'integer', 'Links to members table'],
          ['role_in_dept', 'string, nullable', 'e.g., secretary, assistant leader'],
          ['joined_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('services'),
      p('Each church service or meeting held by a local church.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer', ''],
          ['service_type', 'string', 'sabbath, prayer_meeting, youth, special, other'],
          ['service_date', 'date', ''],
          ['theme', 'string, nullable', 'Service or programme theme'],
          ['officiant_user_id', 'integer, nullable', 'User who led the service'],
          ['notes', 'text, nullable', ''],
          ['recorded_by_user_id', 'integer', 'User who created this record'],
          ['synced', 'boolean', 'false if created offline and not yet sent to server'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('attendance'),
      p('Attendance record for each service.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['service_id', 'integer', ''],
          ['church_id', 'integer', ''],
          ['adults_men', 'integer', ''],
          ['adults_women', 'integer', ''],
          ['youth', 'integer', ''],
          ['children', 'integer', ''],
          ['visitors', 'integer', ''],
          ['total', 'integer', 'Computed total stored for quick reporting'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('sermons'),
      p('Sermon record linked to a service.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['service_id', 'integer', ''],
          ['church_id', 'integer', ''],
          ['title', 'string', ''],
          ['speaker_name', 'string', ''],
          ['scripture_reference', 'string, nullable', 'e.g., John 3:16'],
          ['summary', 'text, nullable', ''],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('giving'),
      p('Financial contributions recorded per service. Visible only to church_admin and field_admin.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['service_id', 'integer', ''],
          ['church_id', 'integer', ''],
          ['category', 'string', 'tithe, offering, building_fund, special, other'],
          ['amount', 'decimal(12,2)', ''],
          ['currency', 'string', 'Default: KES'],
          ['recorded_by_user_id', 'integer', ''],
          ['notes', 'text, nullable', ''],
          ['synced', 'boolean', 'false if offline and not yet synced'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('giving_receipts'),
      p('Placeholder table for tithe and offering receipts. PDF generation is deferred to a later phase.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['giving_id', 'integer', ''],
          ['member_id', 'integer, nullable', 'Null if the giver is not a registered member'],
          ['receipt_number', 'string', ''],
          ['issued_at', 'datetime', ''],
          ['pdf_path', 'string, nullable', 'Path to generated PDF — empty until PDF phase is built'],
        ]
      ),
      blank(),

      h2('church_projects'),
      p('Projects managed at the church level (construction, evangelism campaigns, etc.).'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer', ''],
          ['title', 'string', ''],
          ['description', 'text, nullable', ''],
          ['category', 'string', 'construction, evangelism, welfare, education, other'],
          ['status', 'string', 'planning, active, on_hold, completed, cancelled'],
          ['budget', 'decimal(14,2), nullable', ''],
          ['amount_raised', 'decimal(14,2)', 'Default 0'],
          ['start_date', 'date, nullable', ''],
          ['end_date', 'date, nullable', ''],
          ['created_by_user_id', 'integer', ''],
          ['created_at', 'datetime', ''],
          ['updated_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('tasks'),
      p('Tasks assigned within a church or from field to church.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer, nullable', 'Null if it is a field-level task not tied to one church'],
          ['project_id', 'integer, nullable', 'Links to church_projects if part of a project'],
          ['title', 'string', ''],
          ['description', 'text, nullable', ''],
          ['assigned_to_user_id', 'integer, nullable', ''],
          ['created_by_user_id', 'integer', ''],
          ['due_date', 'date, nullable', ''],
          ['status', 'string', 'open, in_progress, completed, overdue'],
          ['priority', 'string', 'low, medium, high'],
          ['created_at', 'datetime', ''],
          ['updated_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('events'),
      p('Field-wide or church-level events (camp meetings, seminars, youth days, etc.).'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer, nullable', 'Null if it is a field-wide event'],
          ['title', 'string', ''],
          ['description', 'text, nullable', ''],
          ['event_date', 'date', ''],
          ['end_date', 'date, nullable', ''],
          ['location', 'string, nullable', ''],
          ['created_by_user_id', 'integer', ''],
          ['max_volunteers', 'integer, nullable', ''],
          ['is_public', 'boolean', 'Whether it shows on the public field website'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('volunteers'),
      p('Members who sign up to help at an event.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['event_id', 'integer', ''],
          ['member_id', 'integer', ''],
          ['role', 'string, nullable', 'e.g., usher, cook, driver'],
          ['status', 'string', 'registered, confirmed, attended, absent'],
          ['registered_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('field_communications'),
      p('Official communications sent from the field office to churches.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['subject', 'string', ''],
          ['body', 'text', ''],
          ['type', 'string', 'directive, circular, memo, notice'],
          ['sent_by_user_id', 'integer', ''],
          ['target', 'string', 'all_churches, specific_church, or specific_district'],
          ['target_church_id', 'integer, nullable', 'Filled if target is specific_church'],
          ['target_district', 'string, nullable', 'Filled if target is specific_district'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('announcements'),
      p('Short notices published on the public field website or the member portal.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer, nullable', 'Null if field-wide announcement'],
          ['title', 'string', ''],
          ['body', 'text', ''],
          ['publish_date', 'date', ''],
          ['expiry_date', 'date, nullable', ''],
          ['created_by_user_id', 'integer', ''],
          ['is_public', 'boolean', ''],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('reports'),
      p('Monthly and quarterly reports submitted by churches to the field.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['church_id', 'integer', ''],
          ['report_type', 'string', 'monthly, quarterly, annual'],
          ['period_year', 'integer', ''],
          ['period_month', 'integer, nullable', 'Null for annual or quarterly'],
          ['period_quarter', 'integer, nullable', 'Null for monthly or annual'],
          ['total_services', 'integer', ''],
          ['total_attendance', 'integer', ''],
          ['total_tithe', 'decimal(14,2)', ''],
          ['total_offerings', 'decimal(14,2)', ''],
          ['new_members', 'integer', ''],
          ['transfers_in', 'integer', ''],
          ['transfers_out', 'integer', ''],
          ['baptisms', 'integer', ''],
          ['status', 'string', 'draft, submitted, reviewed, approved, returned'],
          ['submitted_by_user_id', 'integer', ''],
          ['reviewed_by_user_id', 'integer, nullable', ''],
          ['field_comments', 'text, nullable', ''],
          ['submitted_at', 'datetime, nullable', ''],
          ['reviewed_at', 'datetime, nullable', ''],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('prayer_requests'),
      p('Prayer requests submitted by members.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['member_id', 'integer', ''],
          ['church_id', 'integer', ''],
          ['subject', 'string', ''],
          ['details', 'text, nullable', ''],
          ['is_private', 'boolean', 'If true, only church admin and assigned elder can see it'],
          ['status', 'string', 'open, praying, answered, closed'],
          ['created_at', 'datetime', ''],
        ]
      ),
      blank(),

      h2('notifications_log'),
      p('Record of every notification sent by the system.'),
      simpleTable(
        ['Column', 'Type', 'Description'],
        [
          ['id', 'integer, primary key', ''],
          ['recipient_user_id', 'integer', ''],
          ['channel', 'string', 'whatsapp or email'],
          ['subject', 'string, nullable', ''],
          ['message', 'text', ''],
          ['status', 'string', 'sent, failed, pending'],
          ['sent_at', 'datetime, nullable', ''],
          ['error', 'text, nullable', 'Error message if failed'],
        ]
      ),
      blank(),

      // ── SECTION 7 ─────────────────────────────────────────────────
      h1('7. Screen and Page Map'),
      p('Every page in the system is listed here by portal. Pages marked [Auth Required] are only accessible after login.'),
      blank(),

      h2('7.1 Public Field Website'),
      p('Accessible to anyone without login.'),
      simpleTable(
        ['URL Path', 'Page Name', 'What It Shows'],
        [
          ['/', 'Home', 'Field welcome message, latest announcements, upcoming events, quick links'],
          ['/about', 'About the Field', 'History, mission, field leadership profiles'],
          ['/churches', 'Affiliated Churches', 'List of all active churches with location and contact'],
          ['/churches/:id', 'Church Profile', 'Public profile of a specific church — name, photo, location, pastor'],
          ['/events', 'Events', 'Upcoming field-wide events open to the public'],
          ['/contact', 'Contact', 'Field office contact details and inquiry form'],
          ['/login', 'Login', 'Login page for all users'],
          ['/register', 'Register', 'New member self-registration form'],
        ]
      ),
      blank(),

      h2('7.2 Member Portal [Auth Required — role: member]'),
      simpleTable(
        ['URL Path', 'Page Name', 'What It Shows'],
        [
          ['/portal', 'Member Home', 'Personal greeting, church announcements, upcoming events'],
          ['/portal/profile', 'My Profile', 'Personal details, profile photo, membership info (read-only)'],
          ['/portal/events', 'Events', 'Field and church events; button to register as volunteer'],
          ['/portal/announcements', 'Announcements', 'All announcements for member\'s church and field-wide'],
          ['/portal/prayer', 'Prayer Requests', 'Submit a new prayer request; list of own past requests and their status'],
          ['/portal/messages', 'Messages', 'Simple inbox for messages from church admin or field'],
        ]
      ),
      blank(),

      h2('7.3 Church Admin Portal [Auth Required — role: church_admin, church_elder, department_leader]'),
      simpleTable(
        ['URL Path', 'Page Name', 'What It Shows'],
        [
          ['/church', 'Dashboard', 'Summary cards: this week\'s attendance, pending member approvals, open tasks, recent giving total'],
          ['/church/members', 'Members', 'Full member list with search and filter; pending approvals shown at top'],
          ['/church/members/:id', 'Member Detail', 'Full member profile; edit option for church_admin'],
          ['/church/members/approve', 'Approval Queue', 'New registrations awaiting church_admin approval'],
          ['/church/services', 'Services', 'List of all recorded services; button to record a new one'],
          ['/church/services/new', 'Record Service', 'Form: date, type, attendance counts, giving entries, sermon details'],
          ['/church/services/:id', 'Service Detail', 'Full record of a past service'],
          ['/church/giving', 'Giving', 'Summary of giving by category and period; detail table below (church_admin only)'],
          ['/church/departments', 'Departments', 'List of departments; manage members within each department'],
          ['/church/projects', 'Projects', 'List of church projects with status and budget progress'],
          ['/church/projects/new', 'New Project', 'Form to create a new church project'],
          ['/church/projects/:id', 'Project Detail', 'Project details, tasks linked to the project, budget updates'],
          ['/church/tasks', 'Tasks', 'All tasks for this church; filter by status and assignee'],
          ['/church/reports', 'Reports', 'Monthly and quarterly report forms; list of past submitted reports'],
          ['/church/reports/new', 'New Report', 'Auto-populated monthly report form using recorded service and giving data'],
          ['/church/events', 'Events', 'Church-level events and field events; create a new church event'],
          ['/church/communications', 'Field Communications', 'Inbox for directives and circulars received from the field'],
          ['/church/announcements', 'Announcements', 'Create and manage church announcements'],
          ['/church/prayer', 'Prayer Requests', 'View and manage all prayer requests from church members'],
        ]
      ),
      blank(),

      h2('7.4 Field CRM [Auth Required — role: field_admin, field_staff]'),
      simpleTable(
        ['URL Path', 'Page Name', 'What It Shows'],
        [
          ['/field', 'CRM Dashboard', 'Field-wide summary: total active members, services this month, giving totals, pending reports, open tasks'],
          ['/field/churches', 'Churches', 'All affiliated churches with status; click to view any church\'s full profile'],
          ['/field/churches/:id', 'Church Detail', 'Full view of a church including members, services, giving, reports, and tasks'],
          ['/field/members', 'All Members', 'Field-wide member search across all churches'],
          ['/field/reports', 'Reports', 'All submitted church reports; filter by church, period, and status'],
          ['/field/reports/:id', 'Report Review', 'Read a report, write field comments, approve or return it'],
          ['/field/communications', 'Communications', 'Compose and send directives or circulars to selected churches'],
          ['/field/projects', 'Field Projects', 'Projects managed at the field level'],
          ['/field/tasks', 'Tasks', 'All tasks across the field; assign to churches or field staff'],
          ['/field/events', 'Events', 'Create and manage field-wide events'],
          ['/field/analytics', 'Analytics', 'Charts and trend lines: attendance over time, giving totals, membership growth'],
          ['/field/announcements', 'Announcements', 'Field-wide announcements for the public website and member portal'],
          ['/field/users', 'User Management', 'Create, edit, suspend field-level user accounts (field_admin only)'],
          ['/field/settings', 'Settings', 'System settings: notification templates, field info, districts (field_admin only)'],
        ]
      ),
      blank(),

      // ── SECTION 8 ─────────────────────────────────────────────────
      h1('8. Workflows'),
      p('The five workflows below cover the most common and critical processes in the system. Each step is numbered and the role responsible is stated.'),
      blank(),

      h2('Workflow A: New Member Joins a Church'),
      simpleTable(
        ['Step', 'Who', 'Action'],
        [
          ['1', 'Visitor', 'Opens the public field website and clicks Register'],
          ['2', 'Visitor', 'Fills in the registration form: name, email, phone, password, selects their church from a list'],
          ['3', 'System', 'Creates a user account with status = pending. Sends WhatsApp and email notification to the church admin of the selected church'],
          ['4', 'church_admin', 'Logs in and goes to Church Portal > Members > Approval Queue. Reviews the request'],
          ['5a', 'church_admin', 'Clicks Approve. System sets status = active. Sends WhatsApp and email to the new member: "Your registration has been approved."'],
          ['5b', 'church_admin', 'Clicks Reject with a reason. System sets status = rejected. Sends WhatsApp and email to the person with the reason.'],
          ['6', 'member', 'Logs in and accesses the Member Portal for the first time'],
        ]
      ),
      blank(),

      h2('Workflow B: Church Submits a Monthly Report'),
      simpleTable(
        ['Step', 'Who', 'Action'],
        [
          ['1', 'church_admin', 'Logs in and goes to Church Portal > Reports > New Report'],
          ['2', 'System', 'Auto-fills the report form using data already recorded for the selected month: total services, total attendance, total giving by category, new members, baptisms, transfers'],
          ['3', 'church_admin', 'Reviews the auto-filled data, makes any manual corrections, adds any narrative notes'],
          ['4', 'church_admin', 'Clicks Submit. Report status changes from draft to submitted'],
          ['5', 'System', 'Sends email notification to the assigned field staff reviewer: "Church X has submitted their monthly report."'],
          ['6', 'field_staff', 'Opens Field CRM > Reports. Opens the submitted report'],
          ['7a', 'field_staff', 'Reviews, writes comments, clicks Approve. Status changes to approved. Church admin receives a WhatsApp notification.'],
          ['7b', 'field_staff', 'Writes comments explaining what needs correction. Clicks Return. Status changes to returned. Church admin receives a WhatsApp notification and must resubmit.'],
        ]
      ),
      blank(),

      h2('Workflow C: Field Sends a Directive to All Churches'),
      simpleTable(
        ['Step', 'Who', 'Action'],
        [
          ['1', 'field_admin', 'Logs in and goes to Field CRM > Communications > Compose'],
          ['2', 'field_admin', 'Selects type (Directive), sets target (All Churches), writes the subject and body of the directive'],
          ['3', 'field_admin', 'Clicks Send'],
          ['4', 'System', 'Saves the communication record. Sends WhatsApp and email to every church_admin account in the system'],
          ['5', 'church_admin', 'Receives the WhatsApp message. Opens it to read. Also sees it in Church Portal > Field Communications inbox'],
          ['6', 'church_admin', 'Can mark the communication as Read in the portal. The field admin can see read receipts in the communications log'],
        ]
      ),
      blank(),

      h2('Workflow D: Recording a Service Offline'),
      simpleTable(
        ['Step', 'Who', 'Action'],
        [
          ['1', 'church_elder', 'Opens the church portal app on their phone at a location with no internet'],
          ['2', 'church_elder', 'Goes to Services > Record New Service. The app opens the form normally because it is a PWA installed on the phone'],
          ['3', 'church_elder', 'Fills in service date, type, attendance counts, giving total per category, sermon details'],
          ['4', 'church_elder', 'Clicks Save. The app saves the record to local storage on the phone (IndexedDB). A small icon shows the record is "offline — not yet synced."'],
          ['5', 'System', 'When the phone reconnects to the internet, a background sync process runs automatically and uploads all pending offline records to the server'],
          ['6', 'System', 'If the sync is successful, the record\'s offline icon disappears. If there is a conflict, the server version is kept and the church_elder sees a conflict notification on next login'],
        ]
      ),
      blank(),

      h2('Workflow E: Task Assignment and Follow-up'),
      simpleTable(
        ['Step', 'Who', 'Action'],
        [
          ['1', 'field_admin', 'Logs in and goes to Field CRM > Tasks > New Task'],
          ['2', 'field_admin', 'Fills in: title, description, assigns to a specific church_admin, sets a due date and priority'],
          ['3', 'System', 'Saves the task. Sends WhatsApp and email to the assigned church_admin: "A new task has been assigned to you: [title]."'],
          ['4', 'church_admin', 'Opens the task in Church Portal > Tasks. Reviews the details. Changes status to In Progress'],
          ['5', 'System', 'Three days before the due date, automatically sends a reminder WhatsApp to the assigned church_admin'],
          ['6', 'church_admin', 'Completes the work. Opens the task and clicks Mark Complete. Status changes to Completed'],
          ['7', 'field_admin', 'Can see the task status update in Field CRM > Tasks at any time without waiting for a report'],
        ]
      ),
      blank(),

      // ── SECTION 9 ─────────────────────────────────────────────────
      h1('9. Build Order'),
      p('The system is built in eight phases. Each phase produces a working, usable result before the next phase begins.'),
      blank(),
      simpleTable(
        ['Phase', 'Name', 'What Gets Built'],
        [
          ['1', 'Foundation', 'Database migrations for all tables, Knex configuration, environment setup, base Express.js server structure'],
          ['2', 'Authentication', 'Registration form, login, logout, session handling, role-based route protection, member approval workflow, basic notification triggers for registration and approval'],
          ['3', 'Church Admin Portal', 'All pages under /church: dashboard, member management, service recording (with attendance, giving, sermon), giving ledger, department management'],
          ['4', 'Reporting', 'Report form with auto-population from service and giving data, report submission and status workflow, field review and approval pages under /field/reports'],
          ['5', 'Field CRM', 'All pages under /field: church directory, member search, communications composer and inbox, task management, event management, analytics charts'],
          ['6', 'Member Portal', 'All pages under /portal: member home, profile view, event listing with volunteer registration, prayer request submission, announcements, messages inbox'],
          ['7', 'PWA and Offline', 'Service worker setup, IndexedDB storage for services and giving, background sync for offline records, conflict notification, install prompt for mobile'],
          ['8', 'Future Enhancements', 'PDF receipt generation, Kiswahili language support, multi-field support, SMS fallback, tithe certificate export, advanced analytics'],
        ]
      ),
      blank(),

      // ── END ───────────────────────────────────────────────────────
      new Paragraph({
        children: [new TextRun({ text: 'End of Document', italics: true, size: 22 })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 600, after: 0 },
      }),
    ],
  }],
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync('Nyamira_West_Field_System_Blueprint.docx', buffer);
  console.log('Done: Nyamira_West_Field_System_Blueprint.docx');
});
