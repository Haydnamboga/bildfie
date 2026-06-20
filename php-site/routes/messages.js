'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');
const requireAuth = require('../middleware/auth');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/messages — inbox for current user
router.get('/', requireAuth, wrap(async (req, res) => {
  const rows = await db('messages as m')
    .join('users as u', 'm.sender_id', 'u.id')
    .where('m.recipient_id', req.session.userId)
    .orderBy('m.created_at', 'desc')
    .limit(50)
    .select('m.*', 'u.name as sender_name', 'u.avatar as sender_avatar', 'u.role as sender_role');
  res.json(rows);
}));

// POST /api/messages — send a message
router.post('/', requireAuth, wrap(async (req, res) => {
  const { recipient_id, content, project_id } = req.body;
  if (!recipient_id || !content) return res.status(400).json({ error: 'recipient_id and content required' });
  const id = await db.insertId('messages', {
    sender_id:    req.session.userId,
    recipient_id,
    project_id:   project_id || null,
    content,
  });
  res.status(201).json({ id });
}));

// PUT /api/messages/:id/read
router.put('/:id/read', requireAuth, wrap(async (req, res) => {
  await db('messages')
    .where({ id: req.params.id, recipient_id: req.session.userId })
    .update({ read: 1 });
  res.json({ message: 'Marked as read' });
}));

module.exports = router;
