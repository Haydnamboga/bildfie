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

module.exports = router;
