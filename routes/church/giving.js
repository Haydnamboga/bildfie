'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { month, type } = req.query;
  const period = month || new Date().toISOString().slice(0,7);

  let q = db('giving')
    .leftJoin('members as m','giving.member_id','m.id')
    .where('giving.church_id', churchId)
    .whereRaw("strftime('%Y-%m', giving.date) = ?", [period])
    .orderBy('giving.date','desc')
    .select('giving.*', db.raw("m.first_name || ' ' || m.last_name as member_name"));

  if (type) q = q.where('giving.giving_type', type);
  const records = await q;

  const totals = await db('giving')
    .where('church_id', churchId)
    .whereRaw("strftime('%Y-%m', date) = ?", [period])
    .select('giving_type', db.raw('sum(amount) as total'))
    .groupBy('giving_type');

  const grandTotal = totals.reduce((a,t) => a + Number(t.total||0), 0);

  const members = await db('members').where('church_id', churchId).where('membership_status','active').orderBy('first_name').select('id',db.raw("first_name || ' ' || last_name as name"));
  const services = await db('services').where('church_id', churchId).whereRaw("date(scheduled_at) >= date('now','-30 days')").orderBy('scheduled_at','desc').select('id','title','scheduled_at');

  res.render('church/giving/index', { title: 'Giving Records', records, totals, grandTotal, members, services, filters: { period, type } });
});

router.post('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { member_id, giving_type, amount, date, description, service_id, reference_no } = req.body;
  if (!amount || !date) return res.redirect('/church/giving?error=Amount+and+date+required');
  try {
    await db.insertId('giving', {
      church_id: churchId, member_id: member_id||null,
      giving_type: giving_type||'tithe', amount, date, description,
      service_id: service_id||null, reference_no,
      recorded_by: req.session.userId,
    });
    res.redirect('/church/giving');
  } catch (err) {
    res.redirect('/church/giving?error=' + encodeURIComponent(err.message));
  }
});

/* Summary / analytics */
router.get('/summary', async (req, res) => {
  const churchId = getChurchId(req);
  const year = req.query.year || new Date().getFullYear();

  const monthly = await db('giving')
    .where('church_id', churchId)
    .whereRaw("strftime('%Y', date) = ?", [String(year)])
    .select(db.raw("strftime('%m', date) as month"), 'giving_type', db.raw('sum(amount) as total'))
    .groupBy(db.raw("strftime('%m', date)"), 'giving_type');

  const byType = await db('giving')
    .where('church_id', churchId)
    .whereRaw("strftime('%Y', date) = ?", [String(year)])
    .select('giving_type', db.raw('sum(amount) as total'))
    .groupBy('giving_type');

  const yearTotal = byType.reduce((a,t) => a + Number(t.total||0), 0);

  res.render('church/giving/summary', { title: 'Giving Summary', monthly, byType, yearTotal, year });
});

module.exports = router;
