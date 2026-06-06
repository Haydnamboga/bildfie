'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { type } = req.query;
  let q = db('services').where('church_id', churchId).orderBy('scheduled_at','desc').limit(50);
  if (type) q = q.where('service_type', type);
  const services = await q;
  res.render('church/services/index', { title: 'Services', services, filters: { type } });
});

router.get('/new', async (req, res) => {
  const churchId = getChurchId(req);
  const preachers = await db('members').where('church_id', churchId).where('membership_status','active').orderBy('first_name').select('id',db.raw("first_name || ' ' || last_name as name"));
  res.render('church/services/form', { title: 'Record Service', service: {}, preachers, errors: [] });
});

router.post('/', async (req, res) => {
  const churchId = getChurchId(req);
  const preachers = await db('members').where('church_id', churchId).orderBy('first_name').select('id',db.raw("first_name || ' ' || last_name as name"));
  const { title, service_type, scheduled_at, preacher_id, officiant_id, theme, scripture_reference, program_notes, members_present, visitors_present, children_present } = req.body;
  if (!title || !scheduled_at) {
    return res.render('church/services/form', { title: 'Record Service', service: req.body, preachers, errors: ['Title and date required'] });
  }
  try {
    const id = await db.insertId('services', {
      church_id: churchId, title, service_type: service_type||'sabbath',
      scheduled_at, preacher_id: preacher_id||null, officiant_id: officiant_id||null,
      theme, scripture_reference, program_notes,
      members_present: members_present||0, visitors_present: visitors_present||0,
      children_present: children_present||0, status: 'completed',
    });
    res.redirect(`/church/services/${id}`);
  } catch (err) {
    res.render('church/services/form', { title: 'Record Service', service: req.body, preachers, errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const churchId = getChurchId(req);
  const service = await db('services')
    .leftJoin('members as p','services.preacher_id','p.id')
    .leftJoin('members as o','services.officiant_id','o.id')
    .where('services.id', req.params.id).where('services.church_id', churchId)
    .select('services.*',
      db.raw("p.first_name || ' ' || p.last_name as preacher_name"),
      db.raw("o.first_name || ' ' || o.last_name as officiant_name")).first();
  if (!service) return res.status(404).render('error', { title: 'Not Found', message: 'Service not found.', user: req.session });

  const attendees = await db('attendance')
    .leftJoin('members as m','attendance.member_id','m.id')
    .where('attendance.service_id', service.id)
    .select('attendance.*',db.raw("m.first_name || ' ' || m.last_name as member_name"));

  const sermon = await db('sermons').where('service_id', service.id).first();
  const giving = await db('giving').where('service_id', service.id).orderBy('giving_type');
  const givingTotal = giving.reduce((a,g) => a + Number(g.amount||0), 0);

  res.render('church/services/show', { title: service.title, service, attendees, sermon, giving, givingTotal });
});

/* Record attendance for a service */
router.post('/:id/attendance', async (req, res) => {
  const churchId = getChurchId(req);
  const { member_id, visitor_name, visitor_phone } = req.body;
  if (member_id) {
    await db('attendance').insert({ service_id: req.params.id, member_id }).onConflict(['service_id','member_id']).ignore();
  } else if (visitor_name) {
    await db.insertId('attendance', { service_id: req.params.id, visitor_name, visitor_phone });
  }
  res.redirect(`/church/services/${req.params.id}`);
});

module.exports = router;
