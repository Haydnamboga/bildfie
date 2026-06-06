'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  const events = await db('events')
    .leftJoin('churches as c','events.church_id','c.id')
    .orderBy('events.start_date','desc')
    .select('events.*','c.name as church_name');
  res.render('field/events/index', { title: 'Events', events });
});

router.get('/new', async (req, res) => {
  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  res.render('field/events/form', { title: 'New Event', event: {}, churches, errors: [] });
});

router.post('/', async (req, res) => {
  const { title, description, event_type, start_date, end_date, venue, church_id, field_wide, budget, expected_attendance } = req.body;
  if (!title) {
    const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
    return res.render('field/events/form', { title: 'New Event', event: req.body, churches, errors: ['Title required'] });
  }
  try {
    const id = await db.insertId('events', {
      title, description, event_type, start_date: start_date||null, end_date: end_date||null,
      venue, church_id: church_id||null, field_wide: !!field_wide,
      budget: budget||0, expected_attendance: expected_attendance||null,
      organizer_id: req.session.userId, status: 'upcoming',
    });
    res.redirect(`/field/events/${id}`);
  } catch (err) {
    const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
    res.render('field/events/form', { title: 'New Event', event: req.body, churches, errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const event = await db('events')
    .leftJoin('churches as c','events.church_id','c.id')
    .where('events.id', req.params.id)
    .select('events.*','c.name as church_name').first();
  if (!event) return res.status(404).render('error', { title: 'Not Found', message: 'Event not found.', user: req.session });
  const volunteers = await db('volunteers')
    .leftJoin('members as m','volunteers.member_id','m.id')
    .where('volunteers.event_id', event.id)
    .select('volunteers.*',db.raw("COALESCE(m.first_name || ' ' || m.last_name, volunteers.name) as display_name"));
  res.render('field/events/show', { title: event.title, event, volunteers });
});

router.post('/:id/status', async (req, res) => {
  await db('events').where('id', req.params.id).update({
    status: req.body.status,
    actual_attendance: req.body.actual_attendance || null,
    actual_cost: req.body.actual_cost || 0,
  });
  res.redirect(`/field/events/${req.params.id}`);
});

module.exports = router;
