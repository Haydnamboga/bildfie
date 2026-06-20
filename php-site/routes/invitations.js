'use strict';
const express = require('express');
const router  = express.Router();
const db      = require('../db/knex');
const requireAuth = require('../middleware/auth');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

// GET /api/invitations/my — invitations sent to me
router.get('/my', requireAuth, wrap(async (req, res) => {
  const rows = await db('project_invitations as pi')
    .join('projects as p', 'pi.project_id', 'p.id')
    .join('users as u', 'pi.inviter_id', 'u.id')
    .where('pi.invitee_id', req.session.userId)
    .orderBy('pi.created_at', 'desc')
    .select(
      'pi.*',
      'p.name as project_name', 'p.type as project_type', 'p.location as project_location',
      'u.name as inviter_name', 'u.avatar as inviter_avatar'
    );
  res.json(rows);
}));

// GET /api/invitations/sent — invitations I sent
router.get('/sent', requireAuth, wrap(async (req, res) => {
  const rows = await db('project_invitations as pi')
    .join('projects as p', 'pi.project_id', 'p.id')
    .join('users as u', 'pi.invitee_id', 'u.id')
    .where('pi.inviter_id', req.session.userId)
    .orderBy('pi.created_at', 'desc')
    .select(
      'pi.*',
      'p.name as project_name',
      'u.name as invitee_name', 'u.avatar as invitee_avatar', 'u.role as invitee_role'
    );
  res.json(rows);
}));

// POST /api/invitations — send invitation to professional
router.post('/', requireAuth, wrap(async (req, res) => {
  const { project_id, invitee_id, role, message } = req.body;
  if (!project_id || !invitee_id) return res.status(400).json({ error: 'project_id and invitee_id required' });
  const proj = await db('projects')
    .where({ id: project_id, owner_id: req.session.userId })
    .first('id');
  if (!proj) return res.status(403).json({ error: 'Not authorized to invite to this project' });
  const existing = await db('project_invitations')
    .where({ project_id, invitee_id, status: 'pending' })
    .first('id');
  if (existing) return res.status(409).json({ error: 'Invitation already sent' });
  const id = await db.insertId('project_invitations', {
    project_id, inviter_id: req.session.userId, invitee_id, role, message,
  });
  await db.insertId('notifications', {
    user_id: invitee_id,
    type:    'invitation',
    title:   'Project Invitation',
    body:    `You have been invited to join a project as ${role}`,
    link:    '/dashboard/',
  });
  res.status(201).json({ id, status: 'pending' });
}));

// PUT /api/invitations/:id/respond — accept or decline
router.put('/:id/respond', requireAuth, wrap(async (req, res) => {
  const { status } = req.body;
  if (!['accepted', 'declined'].includes(status))
    return res.status(400).json({ error: 'Invalid status' });
  const inv = await db('project_invitations')
    .where({ id: req.params.id, invitee_id: req.session.userId })
    .first();
  if (!inv) return res.status(403).json({ error: 'Not found or not authorized' });
  await db('project_invitations').where({ id: req.params.id }).update({
    status,
    responded_at: db.fn.now(),
  });
  if (status === 'accepted') {
    await db('project_team')
      .insert({ project_id: inv.project_id, user_id: req.session.userId, role: inv.role })
      .onConflict(['project_id', 'user_id']).ignore();
    await db.insertId('notifications', {
      user_id: inv.inviter_id,
      type:    'invitation',
      title:   'Invitation Accepted',
      body:    `${req.session.userName} accepted your project invitation.`,
      link:    '/dashboard/',
    });
  }
  res.json({ message: `Invitation ${status}` });
}));

module.exports = router;
