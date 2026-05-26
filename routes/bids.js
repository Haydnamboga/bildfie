'use strict';
const express    = require('express');
const router     = express.Router();
const { body }   = require('express-validator');
const db         = require('../db/knex');
const requireAuth = require('../middleware/auth');
const validate   = require('../middleware/validate');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

/* ── Validation ─────────────────────────────────────────────────────── */

const bidRules = validate([
  body('title').trim().notEmpty().withMessage('Bid title required')
    .isLength({ max: 200 }).withMessage('Title too long'),
  body('budget_min').optional().isInt({ min: 0 }).withMessage('Budget must be positive'),
  body('budget_max').optional().isInt({ min: 0 }).withMessage('Budget must be positive'),
  body('deadline_days').optional().isInt({ min: 1, max: 365 }).withMessage('Deadline must be 1–365 days'),
  body('trades').optional().isArray().withMessage('Trades must be an array'),
  body('urgency').optional().isIn(['new', 'warm', 'hot']).withMessage('Invalid urgency'),
]);

const applyRules = validate([
  body('proposal').trim().notEmpty().withMessage('Proposal text required'),
  body('quoted_amount').isInt({ min: 0 }).withMessage('Quoted amount required'),
  body('timeline').trim().notEmpty().withMessage('Timeline required'),
]);

/* ── Helper — attach trades array to bid rows ───────────────────────── */
async function withTrades(bids) {
  if (!bids.length) return bids;
  const ids = bids.map(b => b.id);
  const tradeRows = await db('bid_trades').whereIn('bid_id', ids).select('bid_id', 'trade');
  const map = {};
  for (const { bid_id, trade } of tradeRows) (map[bid_id] ??= []).push(trade);
  return bids.map(b => ({ ...b, trades: map[b.id] || [] }));
}

/* ── Routes ─────────────────────────────────────────────────────────── */

// GET /api/bids — list all open bids
router.get('/', wrap(async (req, res) => {
  const { trade, location, urgency, limit = 20, offset = 0 } = req.query;
  const applyFilters = qb => {
    qb.where({ status: 'open' });
    if (urgency)  qb.where({ urgency });
    if (location) qb.where('location', 'like', `%${location}%`);
  };
  const bids = await db('bids').modify(applyFilters).orderBy('created_at', 'desc')
    .limit(Number(limit)).offset(Number(offset));
  const [{ c }] = await db('bids').modify(applyFilters).count('id as c');

  let results = await withTrades(bids);
  // Filter by trade after fetch (avoid complex JOIN for now)
  if (trade && trade !== 'All Trades')
    results = results.filter(b => b.trades.some(t => t.toLowerCase().includes(trade.toLowerCase())));

  res.json({ bids: results, total: Number(c) });
}));

// GET /api/bids/my — bids posted by current user (must be before /:id)
router.get('/my', requireAuth, wrap(async (req, res) => {
  const bids = await db('bids')
    .where({ poster_id: req.session.userId })
    .orderBy('created_at', 'desc');
  res.json(await withTrades(bids));
}));

// GET /api/bids/:id — single bid
router.get('/:id', wrap(async (req, res) => {
  const bid = await db('bids').where({ id: req.params.id }).first();
  if (!bid) return res.status(404).json({ error: 'Not found' });
  const [withT] = await withTrades([bid]);
  res.json(withT);
}));

// POST /api/bids — post a new bid
router.post('/', requireAuth, bidRules, wrap(async (req, res) => {
  const {
    title, description, location,
    budget_min = 0, budget_max = 0, currency = 'KES',
    deadline_days = 7, urgency = 'new',
    trades = [],
  } = req.body;

  const id = await db.transaction(async trx => {
    const [bidId] = await trx('bids').insert({
      poster_id:     req.session.userId,
      owner_name:    req.session.userName,
      title, description, location,
      budget_min, budget_max, currency,
      deadline_days, urgency,
    });
    if (trades.length) {
      await trx('bid_trades').insert(
        trades.slice(0, 10).map(trade => ({ bid_id: bidId, trade: String(trade).trim() }))
      );
    }
    return bidId;
  });

  res.status(201).json({ id, title, status: 'open' });
}));

// POST /api/bids/:id/apply — apply to a bid
router.post('/:id/apply', requireAuth, applyRules, wrap(async (req, res) => {
  const { proposal, quoted_amount, timeline } = req.body;
  const bid = await db('bids').where({ id: req.params.id }).first('id', 'status', 'poster_id');
  if (!bid) return res.status(404).json({ error: 'Bid not found' });
  if (bid.status !== 'open') return res.status(400).json({ error: 'Bid is no longer open' });
  if (bid.poster_id === req.session.userId)
    return res.status(400).json({ error: 'You cannot apply to your own bid' });

  const existing = await db('bid_applications')
    .where({ bid_id: req.params.id, applicant_id: req.session.userId })
    .first('id');
  if (existing) return res.status(409).json({ error: 'Already applied' });

  const id = await db.insertId('bid_applications', {
    bid_id:       req.params.id,
    applicant_id: req.session.userId,
    proposal, quoted_amount, timeline,
  });
  await db('bids').where({ id: req.params.id }).increment('applications_count', 1);

  // Notify the bid poster
  await db.insertId('notifications', {
    user_id: bid.poster_id,
    type:    'bid',
    title:   'New Bid Application',
    body:    `${req.session.userName} applied to your bid.`,
    link:    `/dashboard/`,
  });

  res.status(201).json({ id });
}));

// GET /api/bids/:id/applications — list applications (bid owner only)
router.get('/:id/applications', requireAuth, wrap(async (req, res) => {
  const bid = await db('bids')
    .where({ id: req.params.id, poster_id: req.session.userId })
    .first('id');
  if (!bid) return res.status(403).json({ error: 'Not authorized' });

  const apps = await db('bid_applications as ba')
    .join('users as u', 'ba.applicant_id', 'u.id')
    .leftJoin('professional_profiles as pp', 'pp.user_id', 'u.id')
    .where('ba.bid_id', req.params.id)
    .orderBy('ba.created_at', 'asc')
    .select(
      'ba.id', 'ba.proposal', 'ba.quoted_amount', 'ba.currency',
      'ba.timeline', 'ba.status', 'ba.created_at',
      'u.id as applicant_id', 'u.name', 'u.avatar', 'u.rating', 'u.review_count',
      'pp.trade', 'pp.experience_years', 'pp.jobs_done'
    );
  res.json(apps);
}));

// PUT /api/bids/:id/applications/:appId/accept — accept an application
router.put('/:id/applications/:appId/accept', requireAuth, wrap(async (req, res) => {
  const bid = await db('bids')
    .where({ id: req.params.id, poster_id: req.session.userId })
    .first('id', 'title');
  if (!bid) return res.status(403).json({ error: 'Not authorized' });

  const app = await db('bid_applications')
    .where({ id: req.params.appId, bid_id: req.params.id })
    .first('id', 'applicant_id', 'status');
  if (!app) return res.status(404).json({ error: 'Application not found' });
  if (app.status !== 'pending') return res.status(400).json({ error: 'Application already processed' });

  await db.transaction(async trx => {
    // Accept this application
    await trx('bid_applications').where({ id: app.id }).update({ status: 'accepted' });
    // Decline all others for this bid
    await trx('bid_applications')
      .where({ bid_id: req.params.id })
      .whereNot({ id: app.id })
      .update({ status: 'declined' });
    // Close the bid
    await trx('bids').where({ id: req.params.id }).update({ status: 'awarded' });
  });

  // Notify the successful applicant
  await db.insertId('notifications', {
    user_id: app.applicant_id,
    type:    'bid',
    title:   'Bid Application Accepted',
    body:    `Your application for "${bid.title}" has been accepted!`,
    link:    '/dashboard/',
  });

  res.json({ message: 'Application accepted, bid awarded' });
}));

// DELETE /api/bids/:id — close/withdraw own bid
router.delete('/:id', requireAuth, wrap(async (req, res) => {
  const bid = await db('bids')
    .where({ id: req.params.id, poster_id: req.session.userId })
    .first('id');
  if (!bid) return res.status(403).json({ error: 'Not authorized' });
  await db('bids').where({ id: req.params.id }).update({ status: 'closed' });
  res.json({ message: 'Bid closed' });
}));

module.exports = router;
