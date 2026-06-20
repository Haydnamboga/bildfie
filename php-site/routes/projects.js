'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');
const requireAuth = require('../middleware/auth');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/projects — public showcase projects
router.get('/', wrap(async (req, res) => {
  const rows = await db('projects as p')
    .join('users as u', 'p.owner_id', 'u.id')
    .whereNot('p.status', 'planning')
    .orderBy('p.created_at', 'desc')
    .limit(20)
    .select('p.*', 'u.name as owner_name');
  res.json(rows);
}));

// GET /api/projects/my — current user's projects (auth required)
// NOTE: must be defined BEFORE /:id to avoid "my" being treated as an id
router.get('/my', requireAuth, wrap(async (req, res) => {
  const rows = await db('projects')
    .where({ owner_id: req.session.userId })
    .orderBy('updated_at', 'desc');
  res.json(rows);
}));

// GET /api/projects/:id
router.get('/:id', wrap(async (req, res) => {
  const proj = await db('projects as p')
    .join('users as u', 'p.owner_id', 'u.id')
    .where('p.id', req.params.id)
    .select('p.*', 'u.name as owner_name')
    .first();
  if (!proj) return res.status(404).json({ error: 'Not found' });
  const team = await db('project_team as pt')
    .join('users as u', 'pt.user_id', 'u.id')
    .where('pt.project_id', req.params.id)
    .select('u.id','u.name','u.avatar','u.role','pt.role as project_role');
  res.json({ ...proj, team, tags: JSON.parse(proj.tags || '[]') });
}));

// POST /api/projects — create new project (auth required)
router.post('/', requireAuth, wrap(async (req, res) => {
  const { name, description, type, location, budget_min, budget_max, due_date } = req.body;
  if (!name) return res.status(400).json({ error: 'Project name required' });
  const id = await db.insertId('projects', {
    owner_id: req.session.userId,
    name, description, type, location,
    budget_min, budget_max, due_date,
  });
  res.status(201).json({ id, name, status: 'planning' });
}));

// PUT /api/projects/:id — update project
router.put('/:id', requireAuth, wrap(async (req, res) => {
  const proj = await db('projects')
    .where({ id: req.params.id, owner_id: req.session.userId })
    .first('id');
  if (!proj) return res.status(403).json({ error: 'Not authorized' });
  const { name, description, type, location, budget_min, budget_max, status, phase, progress, due_date } = req.body;
  const updates = { updated_at: db.fn.now() };
  if (name        !== undefined) updates.name        = name;
  if (description !== undefined) updates.description = description;
  if (type        !== undefined) updates.type        = type;
  if (location    !== undefined) updates.location    = location;
  if (budget_min  !== undefined) updates.budget_min  = budget_min;
  if (budget_max  !== undefined) updates.budget_max  = budget_max;
  if (status      !== undefined) updates.status      = status;
  if (phase       !== undefined) updates.phase       = phase;
  if (progress    !== undefined) updates.progress    = progress;
  if (due_date    !== undefined) updates.due_date    = due_date;
  await db('projects').where({ id: req.params.id }).update(updates);
  res.json({ message: 'Updated' });
}));

// DELETE /api/projects/:id
router.delete('/:id', requireAuth, wrap(async (req, res) => {
  const proj = await db('projects')
    .where({ id: req.params.id, owner_id: req.session.userId })
    .first('id');
  if (!proj) return res.status(403).json({ error: 'Not authorized' });
  await db('projects').where({ id: req.params.id }).delete();
  res.json({ message: 'Deleted' });
}));

module.exports = router;
