'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/equipment — list equipment with optional filters
router.get('/', wrap(async (req, res) => {
  const { category, available } = req.query;
  const applyFilters = qb => {
    if (category)          qb.where({ category });
    if (available === '1') qb.where({ available: 1 });
  };
  const rows = await db('equipment').modify(applyFilters).orderBy('rating', 'desc');
  res.json(rows);
}));

// GET /api/equipment/:id
router.get('/:id', wrap(async (req, res) => {
  const e = await db('equipment').where({ id: req.params.id }).first();
  if (!e) return res.status(404).json({ error: 'Not found' });
  res.json(e);
}));

module.exports = router;
