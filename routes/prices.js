'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/prices
router.get('/', wrap(async (req, res) => {
  const rows = await db('material_prices').orderBy('id');
  res.json(rows);
}));

module.exports = router;
