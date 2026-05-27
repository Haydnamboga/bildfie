'use strict';
/**
 * Admin API — protected by requireRole('admin')
 *
 * User management:
 *   GET    /api/admin/users                   — list / search all users
 *   GET    /api/admin/users/:id               — single user detail
 *   PUT    /api/admin/users/:id/role          — change role
 *   PUT    /api/admin/users/:id/ban           — toggle ban (verified = false, role = banned)
 *
 * Verification documents:
 *   GET    /api/admin/verifications           — list pending documents
 *   PUT    /api/admin/verifications/:id/approve
 *   PUT    /api/admin/verifications/:id/reject
 *
 * Bids moderation:
 *   GET    /api/admin/bids                    — list all bids (newest first)
 *   DELETE /api/admin/bids/:id                — remove bid
 *
 * Stats:
 *   GET    /api/admin/stats                   — platform-wide analytics
 */

const express      = require('express');
const router       = express.Router();
const { body }     = require('express-validator');
const db           = require('../db/knex');
const requireAuth  = require('../middleware/auth');
const requireRole  = require('../middleware/role');
const validate     = require('../middleware/validate');
const mailer       = require('../services/email');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

/* ── Guard — every admin route requires authentication + admin role ── */
router.use(requireAuth, requireRole('admin'));

/* ══════════════════════════════════════════════════════════════════════
   USERS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/users?role=&search=&page=&limit=
router.get('/users', wrap(async (req, res) => {
  const { role, search, page = 1, limit = 30 } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('users as u')
      .leftJoin('professional_profiles as pp', 'pp.user_id', 'u.id')
      .select(
        'u.id','u.name','u.email','u.role','u.phone','u.location',
        'u.verified','u.verification_badge','u.rating','u.review_count',
        'u.created_at','pp.trade','pp.hourly_rate','pp.available'
      );
    if (role)   q = q.where('u.role', role);
    if (search) q = q.where(b => b.whereILike('u.name', `%${search}%`).orWhereILike('u.email', `%${search}%`));
    return q;
  };

  const [users, [{ total }]] = await Promise.all([
    base().orderBy('u.created_at', 'desc').limit(Number(limit)).offset(offset),
    base().count('u.id as total'),
  ]);

  res.json({ users, total: Number(total), page: Number(page), limit: Number(limit) });
}));

// GET /api/admin/users/:id
router.get('/users/:id', wrap(async (req, res) => {
  const user = await db('users as u')
    .leftJoin('professional_profiles as pp', 'pp.user_id', 'u.id')
    .where('u.id', req.params.id)
    .select(
      'u.id','u.name','u.email','u.role','u.phone','u.location','u.bio',
      'u.avatar','u.verified','u.verification_badge','u.rating',
      'u.review_count','u.created_at','u.updated_at',
      'pp.trade','pp.experience_years','pp.nca_grade','pp.nca_license',
      'pp.hourly_rate','pp.available','pp.jobs_done','pp.on_time_percent'
    )
    .first();
  if (!user) return res.status(404).json({ error: 'User not found' });

  const tags  = await db('professional_tags').where({ user_id: user.id }).select('tag');
  const docs  = await db('verification_documents')
    .where({ user_id: user.id }).orderBy('created_at', 'desc')
    .select('id','original_name','document_type','status','created_at','reviewed_at');

  res.json({ ...user, tags: tags.map(r => r.tag), documents: docs });
}));

// PUT /api/admin/users/:id/role
const roleRules = validate([
  body('role').isIn(['client','professional','supplier','admin'])
    .withMessage('Role must be client, professional, supplier, or admin'),
]);
router.put('/users/:id/role', roleRules, wrap(async (req, res) => {
  const { role } = req.body;
  const user = await db('users').where({ id: req.params.id }).first('id','name');
  if (!user) return res.status(404).json({ error: 'User not found' });
  if (user.id === req.session.userId)
    return res.status(400).json({ error: 'You cannot change your own role' });

  await db('users').where({ id: req.params.id }).update({ role, updated_at: db.fn.now() });
  res.json({ message: `${user.name}'s role updated to ${role}` });
}));

// PUT /api/admin/users/:id/ban  — toggles ban
router.put('/users/:id/ban', wrap(async (req, res) => {
  const user = await db('users').where({ id: req.params.id }).first('id','name','role');
  if (!user) return res.status(404).json({ error: 'User not found' });
  if (user.id === req.session.userId)
    return res.status(400).json({ error: 'You cannot ban yourself' });

  const banning = user.role !== 'banned';
  await db('users').where({ id: req.params.id }).update({
    role:       banning ? 'banned' : 'client',
    verified:   banning ? 0 : 0,
    updated_at: db.fn.now(),
  });

  res.json({ message: banning ? `${user.name} has been banned` : `${user.name}'s ban has been lifted`, banned: banning });
}));

/* ══════════════════════════════════════════════════════════════════════
   VERIFICATION DOCUMENTS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/verifications?status=pending
router.get('/verifications', wrap(async (req, res) => {
  const status = req.query.status || 'pending';
  const docs = await db('verification_documents as vd')
    .join('users as u', 'vd.user_id', 'u.id')
    .leftJoin('users as r', 'vd.reviewer_id', 'r.id')
    .leftJoin('professional_profiles as pp', 'pp.user_id', 'u.id')
    .where('vd.status', status)
    .orderBy('vd.created_at', 'asc')
    .select(
      'vd.id','vd.original_name','vd.document_type','vd.mime_type',
      'vd.size_bytes','vd.status','vd.rejection_reason',
      'vd.created_at','vd.reviewed_at',
      'u.id as user_id','u.name as user_name','u.email as user_email',
      'pp.trade',
      'r.name as reviewer_name'
    );
  res.json(docs);
}));

// PUT /api/admin/verifications/:id/approve
router.put('/verifications/:id/approve', wrap(async (req, res) => {
  const doc = await db('verification_documents').where({ id: req.params.id }).first();
  if (!doc) return res.status(404).json({ error: 'Document not found' });
  if (doc.status !== 'pending')
    return res.status(409).json({ error: 'Document already reviewed' });

  // Determine badge label from document type
  const badge = {
    nca_license:  'NCA Verified',
    id_document:  'ID Verified',
    portfolio:    'Portfolio Verified',
    other:        'Verified',
  }[doc.document_type] || 'Verified';

  await db.transaction(async trx => {
    await trx('verification_documents').where({ id: doc.id }).update({
      status:      'approved',
      reviewer_id: req.session.userId,
      reviewed_at: db.fn.now(),
    });
    await trx('users').where({ id: doc.user_id }).update({
      verified:             1,
      verification_badge:   badge,
      updated_at:           db.fn.now(),
    });
    await db.insertId.call(trx, 'notifications', {
      user_id: doc.user_id,
      type:    'verification',
      title:   'Document Approved ✓',
      body:    `Your ${doc.document_type.replace(/_/g, ' ')} has been approved. Your profile now shows "${badge}".`,
      link:    '/profile',
    });
  });

  // Email the user (non-blocking)
  const approvedUser = await db('users').where({ id: doc.user_id }).select('email','name').first();
  if (approvedUser) {
    mailer.sendVerificationResult(approvedUser.email, approvedUser.name, true, doc.document_type, badge)
      .catch(err => console.warn('[email] verify-approve failed:', err.message));
  }

  res.json({ message: 'Document approved — user marked as verified', badge });
}));

// PUT /api/admin/verifications/:id/reject
const rejectRules = validate([
  body('reason').trim().notEmpty().withMessage('Rejection reason required'),
]);
router.put('/verifications/:id/reject', rejectRules, wrap(async (req, res) => {
  const doc = await db('verification_documents').where({ id: req.params.id }).first();
  if (!doc) return res.status(404).json({ error: 'Document not found' });
  if (doc.status !== 'pending')
    return res.status(409).json({ error: 'Document already reviewed' });

  const reason = req.body.reason;

  await db.transaction(async trx => {
    await trx('verification_documents').where({ id: doc.id }).update({
      status:           'rejected',
      reviewer_id:      req.session.userId,
      reviewed_at:      db.fn.now(),
      rejection_reason: reason,
    });
    await db.insertId.call(trx, 'notifications', {
      user_id: doc.user_id,
      type:    'verification',
      title:   'Document Rejected',
      body:    `Your ${doc.document_type.replace(/_/g, ' ')} could not be approved: ${reason}. Please re-upload a clearer document.`,
      link:    '/profile',
    });
  });

  // Email the user (non-blocking)
  const rejectedUser = await db('users').where({ id: doc.user_id }).select('email','name').first();
  if (rejectedUser) {
    mailer.sendVerificationResult(rejectedUser.email, rejectedUser.name, false, doc.document_type, reason)
      .catch(err => console.warn('[email] verify-reject failed:', err.message));
  }

  res.json({ message: 'Document rejected — user notified' });
}));

/* ══════════════════════════════════════════════════════════════════════
   BIDS MODERATION
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/bids?status=open&page=1
router.get('/bids', wrap(async (req, res) => {
  const { status, page = 1, limit = 30 } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  let q = db('bids as b')
    .join('users as u', 'b.poster_id', 'u.id')
    .select(
      'b.id','b.title','b.budget_min','b.budget_max','b.currency',
      'b.location','b.status','b.applications_count','b.created_at',
      'u.name as poster_name','u.email as poster_email'
    )
    .orderBy('b.created_at', 'desc')
    .limit(Number(limit))
    .offset(offset);

  if (status) q = q.where('b.status', status);

  const bids = await q;
  res.json(bids);
}));

// DELETE /api/admin/bids/:id
router.delete('/bids/:id', wrap(async (req, res) => {
  const bid = await db('bids').where({ id: req.params.id }).first('id','title');
  if (!bid) return res.status(404).json({ error: 'Bid not found' });

  await db('bids').where({ id: req.params.id }).delete();
  res.json({ message: `Bid "${bid.title}" removed` });
}));

/* ══════════════════════════════════════════════════════════════════════
   PLATFORM STATS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/stats
router.get('/stats', wrap(async (req, res) => {
  const [
    [{ users }],
    [{ professionals }],
    [{ projects }],
    [{ bids }],
    [{ transactions }],
    [{ revenue }],
    [{ pending_verifications }],
    recent_payments,
  ] = await Promise.all([
    db('users').count('id as users'),
    db('users').where('role', 'professional').count('id as professionals'),
    db('projects').count('id as projects'),
    db('bids').count('id as bids'),
    db('mpesa_transactions').count('id as transactions'),
    db('mpesa_transactions').where('status', 'completed').sum('amount as revenue'),
    db('verification_documents').where('status', 'pending').count('id as pending_verifications'),
    db('mpesa_transactions as t')
      .join('users as u', 't.user_id', 'u.id')
      .where('t.status', 'completed')
      .orderBy('t.completed_at', 'desc')
      .limit(10)
      .select('t.id','t.amount','t.mpesa_receipt','t.completed_at','u.name as user_name'),
  ]);

  res.json({
    users:                 Number(users),
    professionals:         Number(professionals),
    projects:              Number(projects),
    bids:                  Number(bids),
    transactions:          Number(transactions),
    revenue_kes:           Number(revenue) || 0,
    pending_verifications: Number(pending_verifications),
    recent_payments,
  });
}));

/* ══════════════════════════════════════════════════════════════════════
   TRANSACTIONS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/transactions?page=&limit=&search=
router.get('/transactions', wrap(async (req, res) => {
  const { page = 1, limit = 30, search } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('mpesa_transactions as t')
      .join('users as u', 't.user_id', 'u.id')
      .select(
        't.id','t.amount','t.phone','t.mpesa_receipt',
        't.status','t.created_at','t.completed_at',
        'u.id as user_id','u.name as user_name','u.email as user_email'
      );
    if (search) q = q.where(b =>
      b.whereILike('u.name', `%${search}%`).orWhereILike('t.mpesa_receipt', `%${search}%`)
    );
    return q;
  };

  const [transactions, [{ total }]] = await Promise.all([
    base().orderBy('t.created_at', 'desc').limit(Number(limit)).offset(offset),
    base().count('t.id as total'),
  ]);

  res.json({ transactions, total: Number(total), page: Number(page), limit: Number(limit) });
}));

/* ══════════════════════════════════════════════════════════════════════
   ANALYTICS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/analytics  — revenue & signups, last 30 days
router.get('/analytics', wrap(async (req, res) => {
  const isPg = db.client.config.client === 'pg';

  const [revenueByDay, signupsByDay] = await Promise.all([
    isPg
      ? db('mpesa_transactions').where('status', 'completed')
          .whereRaw("created_at >= NOW() - INTERVAL '30 days'")
          .select(db.raw("to_char(created_at::date,'YYYY-MM-DD') as day"), db.raw('SUM(amount) as total'))
          .groupByRaw("to_char(created_at::date,'YYYY-MM-DD')").orderBy('day')
      : db('mpesa_transactions').where('status', 'completed')
          .whereRaw("created_at >= date('now','-30 days')")
          .select(db.raw("strftime('%Y-%m-%d',created_at) as day"), db.raw('SUM(amount) as total'))
          .groupByRaw("strftime('%Y-%m-%d',created_at)").orderBy('day'),

    isPg
      ? db('users')
          .whereRaw("created_at >= NOW() - INTERVAL '30 days'")
          .select(db.raw("to_char(created_at::date,'YYYY-MM-DD') as day"), db.raw('COUNT(id) as total'))
          .groupByRaw("to_char(created_at::date,'YYYY-MM-DD')").orderBy('day')
      : db('users')
          .whereRaw("created_at >= date('now','-30 days')")
          .select(db.raw("strftime('%Y-%m-%d',created_at) as day"), db.raw('COUNT(id) as total'))
          .groupByRaw("strftime('%Y-%m-%d',created_at)").orderBy('day'),
  ]);

  res.json({ revenueByDay, signupsByDay });
}));

/* ══════════════════════════════════════════════════════════════════════
   RESET PASSWORD
══════════════════════════════════════════════════════════════════════ */

// POST /api/admin/users/:id/reset-password
const resetPwRules = validate([
  body('password').isLength({ min: 8 }).withMessage('Password must be at least 8 characters'),
]);
router.post('/users/:id/reset-password', resetPwRules, wrap(async (req, res) => {
  const bcrypt = require('bcryptjs');
  const user = await db('users').where({ id: req.params.id }).first('id', 'name');
  if (!user) return res.status(404).json({ error: 'User not found' });

  const hash = await bcrypt.hash(req.body.password, 12);
  await db('users').where({ id: req.params.id }).update({ password_hash: hash, updated_at: db.fn.now() });
  res.json({ message: `Password reset for ${user.name}` });
}));

/* ══════════════════════════════════════════════════════════════════════
   TALENT REGISTRY (PROFESSIONALS)
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/professionals?page=&limit=&search=&trade=&available=
router.get('/professionals', wrap(async (req, res) => {
  const { page = 1, limit = 25, search, trade, available } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('users as u')
      .join('professional_profiles as pp', 'pp.user_id', 'u.id')
      .select(
        'u.id','u.name','u.email','u.verified','u.verification_badge',
        'u.rating','u.review_count','u.location','u.created_at',
        'pp.trade','pp.experience_years','pp.hourly_rate','pp.available',
        'pp.jobs_done','pp.on_time_percent','pp.nca_grade'
      );
    if (search) q = q.where(b => b.whereILike('u.name', `%${search}%`).orWhereILike('u.email', `%${search}%`));
    if (trade)  q = q.where('pp.trade', trade);
    if (available !== undefined && available !== '') q = q.where('pp.available', available === 'true' ? 1 : 0);
    return q;
  };

  const [professionals, [{ total }]] = await Promise.all([
    base().orderBy('u.rating', 'desc').limit(Number(limit)).offset(offset),
    base().count('u.id as total'),
  ]);

  const trades = await db('professional_profiles').distinct('trade').pluck('trade').orderBy('trade');
  res.json({ professionals, total: Number(total), page: Number(page), limit: Number(limit), trades });
}));

/* ══════════════════════════════════════════════════════════════════════
   PROJECTS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/projects?page=&limit=&status=&search=
router.get('/projects', wrap(async (req, res) => {
  const { page = 1, limit = 25, status, search } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('projects as p')
      .join('users as u', 'p.owner_id', 'u.id')
      .select(
        'p.id','p.name','p.type','p.location','p.status','p.phase',
        'p.progress','p.budget_min','p.budget_max','p.currency',
        'p.due_date','p.created_at',
        'u.id as owner_id','u.name as owner_name','u.email as owner_email'
      );
    if (status) q = q.where('p.status', status);
    if (search) q = q.whereILike('p.name', `%${search}%`);
    return q;
  };

  const [projects, [{ total }]] = await Promise.all([
    base().orderBy('p.created_at', 'desc').limit(Number(limit)).offset(offset),
    base().count('p.id as total'),
  ]);

  res.json({ projects, total: Number(total), page: Number(page), limit: Number(limit) });
}));

/* ══════════════════════════════════════════════════════════════════════
   REVIEWS & CONTENT MODERATION
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/reviews?page=&limit=&max_rating=
router.get('/reviews', wrap(async (req, res) => {
  const { page = 1, limit = 25, max_rating } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('reviews as r')
      .join('users as rv', 'r.reviewer_id', 'rv.id')
      .join('users as re', 'r.reviewee_id', 're.id')
      .leftJoin('projects as p', 'r.project_id', 'p.id')
      .select(
        'r.id','r.rating','r.comment','r.created_at',
        'rv.name as reviewer_name','rv.email as reviewer_email',
        're.name as reviewee_name',
        'p.name as project_name'
      );
    if (max_rating) q = q.where('r.rating', '<=', Number(max_rating));
    return q;
  };

  const [reviews, [{ total }]] = await Promise.all([
    base().orderBy('r.created_at', 'desc').limit(Number(limit)).offset(offset),
    base().count('r.id as total'),
  ]);

  res.json({ reviews, total: Number(total), page: Number(page), limit: Number(limit) });
}));

// DELETE /api/admin/reviews/:id
router.delete('/reviews/:id', wrap(async (req, res) => {
  const review = await db('reviews').where({ id: req.params.id }).first('id');
  if (!review) return res.status(404).json({ error: 'Review not found' });
  await db('reviews').where({ id: req.params.id }).delete();
  res.json({ message: 'Review removed' });
}));

/* ══════════════════════════════════════════════════════════════════════
   INVOICES & ESCROW
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/invoices?page=&limit=&status=
router.get('/invoices', wrap(async (req, res) => {
  const { page = 1, limit = 25, status } = req.query;
  const offset = (Number(page) - 1) * Number(limit);

  const base = () => {
    let q = db('invoices as i')
      .join('users as iss', 'i.issuer_id', 'iss.id')
      .join('users as rec', 'i.recipient_id', 'rec.id')
      .leftJoin('projects as p', 'i.project_id', 'p.id')
      .select(
        'i.id','i.amount','i.currency','i.milestone','i.status',
        'i.escrow_status','i.due_date','i.created_at','i.paid_at',
        'iss.name as issuer_name',
        'rec.name as recipient_name',
        'p.name as project_name'
      );
    if (status) q = q.where('i.status', status);
    return q;
  };

  const [[{ total }], [{ held }], [{ paid }], invoices] = await Promise.all([
    base().count('i.id as total'),
    db('invoices').where('escrow_status', 'held').sum('amount as held'),
    db('invoices').where('status', 'paid').sum('amount as paid'),
    base().orderBy('i.created_at', 'desc').limit(Number(limit)).offset(offset),
  ]);

  res.json({
    invoices, total: Number(total), page: Number(page), limit: Number(limit),
    escrow_held_kes: Number(held) || 0,
    total_paid_kes:  Number(paid) || 0,
  });
}));

/* ══════════════════════════════════════════════════════════════════════
   MARKETPLACE LISTINGS (materials / equipment / transport)
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/listings?type=materials|equipment|transport&page=&limit=
router.get('/listings', wrap(async (req, res) => {
  const { type = 'materials', page = 1, limit = 25 } = req.query;
  const offset = (Number(page) - 1) * Number(limit);
  const tableMap = { materials: 'materials', equipment: 'equipment', transport: 'transport' };
  const table = tableMap[type];
  if (!table) return res.status(400).json({ error: 'type must be materials, equipment, or transport' });

  let rows, total;
  if (type === 'materials') {
    [[{ total }], rows] = await Promise.all([
      db('materials').count('id as total'),
      db('materials as m').leftJoin('users as u', 'm.supplier_id', 'u.id')
        .select('m.id','m.name','m.category','m.price','m.currency','m.stock_status','m.location','m.created_at','u.name as owner_name')
        .orderBy('m.created_at','desc').limit(Number(limit)).offset(offset),
    ]);
  } else if (type === 'equipment') {
    [[{ total }], rows] = await Promise.all([
      db('equipment').count('id as total'),
      db('equipment as e').leftJoin('users as u', 'e.owner_id', 'u.id')
        .select('e.id','e.name','e.category','e.daily_rate','e.currency','e.available','e.location','e.created_at','u.name as owner_name')
        .orderBy('e.created_at','desc').limit(Number(limit)).offset(offset),
    ]);
  } else {
    [[{ total }], rows] = await Promise.all([
      db('transport').count('id as total'),
      db('transport as t').leftJoin('users as u', 't.owner_id', 'u.id')
        .select('t.id','t.name','t.type','t.rate_text','t.location','t.created_at','u.name as owner_name')
        .orderBy('t.created_at','desc').limit(Number(limit)).offset(offset),
    ]);
  }

  res.json({ data: rows, total: Number(total), page: Number(page), limit: Number(limit), type });
}));

// DELETE /api/admin/listings/:type/:id
router.delete('/listings/:type/:id', wrap(async (req, res) => {
  const { type, id } = req.params;
  const tableMap = { materials: 'materials', equipment: 'equipment', transport: 'transport' };
  const table = tableMap[type];
  if (!table) return res.status(400).json({ error: 'Invalid listing type' });
  const row = await db(table).where({ id }).first('id');
  if (!row) return res.status(404).json({ error: 'Listing not found' });
  await db(table).where({ id }).delete();
  res.json({ message: 'Listing removed' });
}));

/* ══════════════════════════════════════════════════════════════════════
   COMMUNICATIONS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/messages/stats
router.get('/messages/stats', wrap(async (req, res) => {
  const [[{ total }], [{ unread }], recent] = await Promise.all([
    db('messages').count('id as total'),
    db('messages').where('read', false).count('id as unread'),
    db('messages as m')
      .join('users as s', 'm.sender_id', 's.id')
      .join('users as r', 'm.recipient_id', 'r.id')
      .orderBy('m.created_at', 'desc').limit(10)
      .select('m.id','m.content','m.read','m.created_at','s.name as sender_name','r.name as recipient_name'),
  ]);
  res.json({ total: Number(total), unread: Number(unread), recent });
}));

// POST /api/admin/notifications/broadcast
router.post('/notifications/broadcast', wrap(async (req, res) => {
  const { title, body: notifBody, link, roles } = req.body;
  if (!title || !notifBody) return res.status(400).json({ error: 'Title and body are required' });

  let q = db('users').select('id').whereNot('role', 'banned');
  if (roles && roles.length) q = q.whereIn('role', roles);
  const users = await q;

  const notifications = users.map(u => ({
    user_id: u.id, type: 'system', title, body: notifBody, link: link || '/',
  }));

  if (notifications.length) await db.batchInsert('notifications', notifications, 100);
  res.json({ message: `Broadcast sent to ${notifications.length} users` });
}));

/* ══════════════════════════════════════════════════════════════════════
   SYSTEM HEALTH
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/system
router.get('/system', wrap(async (req, res) => {
  const mem = process.memoryUsage();

  const counts = await Promise.all([
    db('users').count('id as n').first(),
    db('projects').count('id as n').first(),
    db('bids').count('id as n').first(),
    db('messages').count('id as n').first(),
    db('notifications').count('id as n').first(),
    db('reviews').count('id as n').first(),
    db('materials').count('id as n').first(),
    db('equipment').count('id as n').first(),
    db('mpesa_transactions').count('id as n').first(),
    db('invoices').count('id as n').first(),
  ]);

  res.json({
    uptime_seconds: Math.floor(process.uptime()),
    node_version:   process.version,
    platform:       process.platform,
    env:            process.env.NODE_ENV || 'development',
    db_type:        db.client.config.client,
    memory_mb: {
      rss:        (mem.rss        / 1048576).toFixed(1),
      heap_used:  (mem.heapUsed   / 1048576).toFixed(1),
      heap_total: (mem.heapTotal  / 1048576).toFixed(1),
    },
    table_counts: {
      users:         Number(counts[0].n),
      projects:      Number(counts[1].n),
      bids:          Number(counts[2].n),
      messages:      Number(counts[3].n),
      notifications: Number(counts[4].n),
      reviews:       Number(counts[5].n),
      materials:     Number(counts[6].n),
      equipment:     Number(counts[7].n),
      transactions:  Number(counts[8].n),
      invoices:      Number(counts[9].n),
    },
  });
}));

/* ══════════════════════════════════════════════════════════════════════
   CRM — CLIENT ACCOUNTS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/crm
router.get('/crm', wrap(async (req, res) => {
  const [[{ clients }], [{ suppliers }], [{ active_projects }], [{ open_bids }], top_clients] = await Promise.all([
    db('users').where('role', 'client').count('id as clients'),
    db('users').where('role', 'supplier').count('id as suppliers'),
    db('projects').whereIn('status', ['active','in_progress','planning']).count('id as active_projects'),
    db('bids').where('status', 'open').count('id as open_bids'),
    db('users as u')
      .leftJoin('projects as p', 'p.owner_id', 'u.id')
      .where('u.role', 'client')
      .groupBy('u.id','u.name','u.email','u.location','u.created_at')
      .orderBy(db.raw('COUNT(p.id)'), 'desc')
      .limit(10)
      .select('u.id','u.name','u.email','u.location','u.created_at',
              db.raw('COUNT(p.id) as project_count')),
  ]);
  res.json({
    clients: Number(clients), suppliers: Number(suppliers),
    active_projects: Number(active_projects), open_bids: Number(open_bids),
    top_clients,
  });
}));

/* ══════════════════════════════════════════════════════════════════════
   GROWTH METRICS
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/growth
router.get('/growth', wrap(async (req, res) => {
  const isPg = db.client.config.client === 'pg';
  const since30 = isPg
    ? "created_at >= NOW() - INTERVAL '30 days'"
    : "created_at >= date('now','-30 days')";

  const [[{ new_users }], [{ new_bids }], [{ new_projects }], [{ applications }], roleBreakdown, topTrades] =
    await Promise.all([
      db('users').whereRaw(since30).count('id as new_users'),
      db('bids').whereRaw(since30).count('id as new_bids'),
      db('projects').whereRaw(since30).count('id as new_projects'),
      db('bid_applications').whereRaw(since30).count('id as applications'),
      db('users').groupBy('role').orderBy('role').select('role', db.raw('COUNT(id) as cnt')),
      db('professional_profiles').groupBy('trade').orderBy(db.raw('COUNT(*)'), 'desc').limit(8)
        .select('trade', db.raw('COUNT(*) as cnt')),
    ]);

  res.json({
    new_users: Number(new_users), new_bids: Number(new_bids),
    new_projects: Number(new_projects), applications: Number(applications),
    role_breakdown: roleBreakdown,
    top_trades: topTrades,
  });
}));

/* ══════════════════════════════════════════════════════════════════════
   AUDIT LOG  (derived from existing tables)
══════════════════════════════════════════════════════════════════════ */

// GET /api/admin/audit?limit=
router.get('/audit', wrap(async (req, res) => {
  const limit = Math.min(Number(req.query.limit) || 60, 200);

  const [verifs, registrations, payments, newBids] = await Promise.all([
    db('verification_documents as vd')
      .join('users as u', 'vd.user_id', 'u.id')
      .leftJoin('users as r', 'vd.reviewer_id', 'r.id')
      .whereNotNull('vd.reviewed_at')
      .orderBy('vd.reviewed_at', 'desc').limit(limit)
      .select(
        db.raw("'verification' as event_type"),
        'vd.reviewed_at as ts',
        db.raw("COALESCE(r.name, 'System') as actor"),
        'u.name as subject',
        db.raw("vd.status || ' — ' || vd.document_type as detail")
      ),
    db('users')
      .orderBy('created_at', 'desc').limit(limit)
      .select(
        db.raw("'user_registered' as event_type"),
        'created_at as ts',
        db.raw("NULL as actor"),
        'name as subject',
        db.raw("role || ' account created' as detail")
      ),
    db('mpesa_transactions as t')
      .join('users as u', 't.user_id', 'u.id')
      .where('t.status', 'completed')
      .orderBy('t.completed_at', 'desc').limit(limit)
      .select(
        db.raw("'payment' as event_type"),
        't.completed_at as ts',
        db.raw("NULL as actor"),
        'u.name as subject',
        db.raw("'KES ' || CAST(t.amount AS TEXT) || ' via M-Pesa' as detail")
      ),
    db('bids')
      .orderBy('created_at', 'desc').limit(limit)
      .select(
        db.raw("'bid_posted' as event_type"),
        'created_at as ts',
        db.raw("NULL as actor"),
        'title as subject',
        db.raw("status || ' bid posted' as detail")
      ),
  ]);

  const all = [...verifs, ...registrations, ...payments, ...newBids]
    .filter(e => e.ts)
    .sort((a, b) => new Date(b.ts) - new Date(a.ts))
    .slice(0, limit);

  res.json(all);
}));

module.exports = router;
