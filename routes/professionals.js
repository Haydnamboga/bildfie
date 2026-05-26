'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/professionals — list all professionals with optional filters
router.get('/', wrap(async (req, res) => {
  const { trade, location, verified, available, minRate, maxRate, limit = 20, offset = 0 } = req.query;

  const applyFilters = qb => {
    if (trade && trade !== 'All Trades')         qb.where('pp.trade', 'like', `%${trade}%`);
    if (location && location !== 'Any Location') qb.where('u.location', 'like', `%${location}%`);
    if (verified  === '1') qb.where('u.verified', 1);
    if (available === '1') qb.where('pp.available', 1);
    if (minRate)           qb.where('pp.hourly_rate', '>=', Number(minRate));
    if (maxRate)           qb.where('pp.hourly_rate', '<=', Number(maxRate));
  };

  const base = () => db('users as u')
    .join('professional_profiles as pp', 'u.id', 'pp.user_id')
    .where('u.role', 'professional')
    .modify(applyFilters);

  const [rows, [{ c }]] = await Promise.all([
    base()
      .select(
        'u.id','u.name','u.location','u.avatar','u.verified','u.verification_badge',
        'u.rating','u.review_count',
        'pp.trade','pp.experience_years','pp.nca_grade','pp.hourly_rate',
        'pp.rate_currency','pp.available','pp.jobs_done','pp.on_time_percent'
      )
      .orderBy('u.rating', 'desc')
      .limit(Number(limit))
      .offset(Number(offset)),
    base().count('u.id as c'),
  ]);

  // Fetch tags separately and merge — avoids GROUP_CONCAT portability issues
  const ids = rows.map(r => r.id);
  const tagRows = ids.length
    ? await db('professional_tags').whereIn('user_id', ids).select('user_id', 'tag')
    : [];
  const tagMap = {};
  for (const { user_id, tag } of tagRows) {
    (tagMap[user_id] ??= []).push(tag);
  }

  res.json({
    professionals: rows.map(r => ({ ...r, tags: tagMap[r.id] || [] })),
    total: Number(c),
  });
}));

// GET /api/professionals/:id — single professional
router.get('/:id', wrap(async (req, res) => {
  const u = await db('users as u')
    .join('professional_profiles as pp', 'u.id', 'pp.user_id')
    .where('u.id', req.params.id)
    .select(
      'u.id','u.name','u.location','u.avatar','u.bio','u.verified','u.verification_badge',
      'u.rating','u.review_count',
      'pp.trade','pp.experience_years','pp.nca_grade','pp.nca_license',
      'pp.hourly_rate','pp.rate_currency','pp.available','pp.jobs_done','pp.on_time_percent'
    )
    .first();
  if (!u) return res.status(404).json({ error: 'Not found' });
  const tagRows = await db('professional_tags').where({ user_id: req.params.id }).select('tag');
  res.json({ ...u, tags: tagRows.map(r => r.tag) });
}));

module.exports = router;
