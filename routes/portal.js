'use strict';
const express = require('express');
const db = require('../db/knex');
const { requireAuth } = require('../middleware/churchAuth');
const router = express.Router();

/* Member portal — public dashboard */
router.get('/dashboard', requireAuth, async (req, res) => {
  const churchId = req.session.churchId;
  const userId = req.session.userId;

  const member = churchId ? await db('members').where('user_id', userId).where('church_id', churchId).first() : null;
  const church = churchId ? await db('churches').where('id', churchId).first() : null;

  const events = await db('events')
    .where(function() {
      if (churchId) this.where('church_id', churchId).orWhere('field_wide', true);
      else this.where('field_wide', true);
    })
    .where('status','upcoming').orderBy('start_date').limit(6);

  const announcements = await db('announcements')
    .where(function() {
      if (churchId) this.where('church_id', churchId).orWhere('field_wide', true);
      else this.where('field_wide', true);
    })
    .where('active', true).orderBy('created_at','desc').limit(5);

  const myTasks = userId
    ? await db('tasks').where('assigned_to', userId).whereIn('status',['pending','in_progress']).orderBy('due_date').limit(5)
    : [];

  const prayerRequests = await db('prayer_requests')
    .where(function() {
      if (churchId) this.where('church_id', churchId);
      else this.where('field_wide', true);
    })
    .where('answered', false).orderBy('created_at','desc').limit(5);

  res.render('portal/dashboard', {
    title: 'My Dashboard', church, member, events, announcements, myTasks, prayerRequests,
  });
});

/* Prayer requests */
router.post('/prayer', requireAuth, async (req, res) => {
  const { request, anonymous } = req.body;
  const member = await db('members').where('user_id', req.session.userId).first();
  await db.insertId('prayer_requests', {
    church_id: req.session.churchId||null,
    member_id: member?.id || null,
    name: req.session.userName,
    request,
    anonymous: !!anonymous,
  });
  res.redirect('/portal/dashboard');
});

/* Profile page */
router.get('/profile', requireAuth, async (req, res) => {
  const user = await db('users').where('id', req.session.userId).first();
  const member = await db('members').where('user_id', req.session.userId).first();
  const church = user.church_id ? await db('churches').where('id', user.church_id).first() : null;
  res.render('portal/profile', { title: 'My Profile', user, member, church });
});

/* Church info (public) */
router.get('/church/:id', async (req, res) => {
  const church = await db('churches').where('id', req.params.id).first();
  if (!church) return res.status(404).render('error', { title: 'Not Found', message: 'Church not found.', user: req.session });

  const events = await db('events').where('church_id', church.id).where('status','upcoming').orderBy('start_date').limit(6);
  const announcements = await db('announcements').where('church_id', church.id).where('active', true).orderBy('created_at','desc').limit(5);
  const sermons = await db('sermons').where('church_id', church.id).orderBy('sermon_date','desc').limit(6);
  const departments = await db('departments').where('church_id', church.id).where('status','active');
  const memberCount = await db('members').where('church_id', church.id).where('membership_status','active').count('id as c').first();

  res.render('portal/church', {
    title: church.name, church, events, announcements, sermons, departments, memberCount: memberCount?.c || 0,
  });
});

module.exports = router;
