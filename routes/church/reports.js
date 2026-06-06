'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const reports = await db('reports').where('church_id', churchId).orderBy('created_at','desc');
  res.render('church/reports/index', { title: 'Reports', reports });
});

router.get('/submit', async (req, res) => {
  res.render('church/reports/form', { title: 'Submit Report', errors: [] });
});

router.post('/submit', async (req, res) => {
  const churchId = getChurchId(req);
  const { report_type, period_start, period_end, period_label, summary } = req.body;
  if (!report_type || !period_label) {
    return res.render('church/reports/form', { title: 'Submit Report', errors: ['Report type and period are required'] });
  }

  // Auto-gather data
  let data = {};
  try {
    if (report_type === 'attendance') {
      const services = await db('services')
        .where('church_id', churchId)
        .whereBetween('scheduled_at', [period_start, period_end + ' 23:59:59']);
      data = {
        total_services: services.length,
        total_members: services.reduce((a,s)=>a+(s.members_present||0),0),
        total_visitors: services.reduce((a,s)=>a+(s.visitors_present||0),0),
      };
    } else if (report_type === 'financial') {
      const rows = await db('giving').where('church_id', churchId).whereBetween('date',[period_start,period_end]);
      const byType = {};
      rows.forEach(r => { byType[r.giving_type] = (byType[r.giving_type]||0) + Number(r.amount||0); });
      data = { by_type: byType, total: rows.reduce((a,r)=>a+Number(r.amount||0),0) };
    } else if (report_type === 'membership') {
      const all = await db('members').where('church_id', churchId);
      data = { total: all.length, active: all.filter(m=>m.membership_status==='active').length, baptized: all.filter(m=>m.baptism_date).length };
    }

    const id = await db.insertId('reports', {
      church_id: churchId, report_type, period_start: period_start||null, period_end: period_end||null,
      period_label, summary, data: JSON.stringify(data),
      generated_by: req.session.userId, status: 'submitted', submitted_at: new Date().toISOString(),
    });
    res.redirect(`/church/reports/${id}`);
  } catch (err) {
    res.render('church/reports/form', { title: 'Submit Report', errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const churchId = getChurchId(req);
  const report = await db('reports').where('id', req.params.id).where('church_id', churchId).first();
  if (!report) return res.status(404).render('error', { title: 'Not Found', message: 'Report not found.', user: req.session });
  try { report.data = JSON.parse(report.data); } catch(e) { report.data = {}; }
  res.render('church/reports/show', { title: report.period_label || 'Report', report });
});

module.exports = router;
