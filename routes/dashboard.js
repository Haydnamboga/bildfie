'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');
const requireAuth = require('../middleware/auth');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/dashboard/summary
router.get('/summary', requireAuth, wrap(async (req, res) => {
  const uid = req.session.userId;
  const [
    [{ c: projects }],
    [{ c: active_projects }],
    [{ c: unread_messages }],
    [{ c: pending_invoices }],
    [{ t: total_budget }],
    notifications,
  ] = await Promise.all([
    db('projects').where({ owner_id: uid }).count('id as c'),
    db('projects').where({ owner_id: uid, status: 'ongoing' }).count('id as c'),
    db('messages').where({ recipient_id: uid, read: 0 }).count('id as c'),
    db('invoices').where({ recipient_id: uid, status: 'pending' }).count('id as c'),
    db('projects').where({ owner_id: uid }).sum('budget_max as t'),
    db('notifications').where({ user_id: uid, read: 0 }).orderBy('created_at', 'desc').limit(10),
  ]);
  res.json({
    projects:         Number(projects),
    active_projects:  Number(active_projects),
    unread_messages:  Number(unread_messages),
    pending_invoices: Number(pending_invoices),
    total_budget:     Number(total_budget) || 0,
    notifications,
  });
}));

// GET /api/dashboard/invoices
router.get('/invoices', requireAuth, wrap(async (req, res) => {
  const rows = await db('invoices as i')
    .join('users as u', 'i.issuer_id', 'u.id')
    .join('projects as p', 'i.project_id', 'p.id')
    .where('i.recipient_id', req.session.userId)
    .orderBy('i.created_at', 'desc')
    .select('i.*', 'u.name as issuer_name', 'p.name as project_name');
  res.json(rows);
}));

// PUT /api/dashboard/invoices/:id/approve
router.put('/invoices/:id/approve', requireAuth, wrap(async (req, res) => {
  await db('invoices')
    .where({ id: req.params.id, recipient_id: req.session.userId })
    .update({ status: 'approved' });
  res.json({ message: 'Invoice approved' });
}));

// GET /api/dashboard/notifications
router.get('/notifications', requireAuth, wrap(async (req, res) => {
  const rows = await db('notifications')
    .where({ user_id: req.session.userId })
    .orderBy('created_at', 'desc')
    .limit(20);
  res.json(rows);
}));

// PUT /api/dashboard/notifications/:id/read — mark one as read
router.put('/notifications/:id/read', requireAuth, wrap(async (req, res) => {
  await db('notifications')
    .where({ id: req.params.id, user_id: req.session.userId })
    .update({ read: 1 });
  res.json({ message: 'Read' });
}));

// PUT /api/dashboard/notifications/read-all — mark ALL as read
router.put('/notifications/read-all', requireAuth, wrap(async (req, res) => {
  const count = await db('notifications')
    .where({ user_id: req.session.userId, read: 0 })
    .update({ read: 1 });
  res.json({ message: 'All notifications marked as read', updated: count });
}));

module.exports = router;
