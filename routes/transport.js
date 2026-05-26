'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/transport — list with optional filters
router.get('/', wrap(async (req, res) => {
  const { type, location } = req.query;
  const applyFilters = qb => {
    if (type)     qb.where({ type });
    if (location) qb.where('location', 'like', `%${location}%`);
  };
  const rows = await db('transport').modify(applyFilters).orderBy('rating', 'desc');
  // modes stored as JSON text — parse on the way out
  res.json(rows.map(r => ({ ...r, modes: JSON.parse(r.modes || '[]') })));
}));

// GET /api/transport/:id
router.get('/:id', wrap(async (req, res) => {
  const t = await db('transport').where({ id: req.params.id }).first();
  if (!t) return res.status(404).json({ error: 'Not found' });
  res.json({ ...t, modes: JSON.parse(t.modes || '[]') });
}));

module.exports = router;
