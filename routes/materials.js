'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/materials — list materials with optional filters
router.get('/', wrap(async (req, res) => {
  const { category, stock, delivery, search, limit = 20, offset = 0 } = req.query;

  const applyFilters = qb => {
    if (category && category !== 'All Categories') qb.where({ category });
    if (stock)    qb.where({ stock_status: stock });
    if (delivery) qb.where({ delivery_speed: delivery });
    if (search)   qb.where(q2 => q2
      .orWhere('name',          'like', `%${search}%`)
      .orWhere('category',      'like', `%${search}%`)
      .orWhere('specification', 'like', `%${search}%`));
  };

  const [rows, [{ c }]] = await Promise.all([
    db('materials').modify(applyFilters).orderBy('id').limit(Number(limit)).offset(Number(offset)),
    db('materials').modify(applyFilters).count('id as c'),
  ]);
  res.json({ materials: rows, total: Number(c) });
}));

// GET /api/materials/:id
router.get('/:id', wrap(async (req, res) => {
  const m = await db('materials').where({ id: req.params.id }).first();
  if (!m) return res.status(404).json({ error: 'Not found' });
  res.json(m);
}));

module.exports = router;
