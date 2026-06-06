'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  const reports = await db('reports')
    .leftJoin('churches as c','reports.church_id','c.id')
    .leftJoin('users as u','reports.generated_by','u.id')
    .orderBy('reports.created_at','desc')
    .select('reports.*','c.name as church_name','u.name as generated_by_name');
  res.render('field/reports/index', { title: 'Reports', reports });
});

/* Generate summary report */
router.get('/generate', async (req, res) => {
  const { type, period_start, period_end, church_id } = req.query;

  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  if (!type) return res.render('field/reports/generate', { title: 'Generate Report', churches, result: null, params: {} });

  const params = { type, period_start, period_end, church_id };
  let data = {};

  try {
    if (type === 'attendance') {
      let q = db('services').whereBetween('scheduled_at', [period_start, period_end + ' 23:59:59']);
      if (church_id) q = q.where('church_id', church_id);
      const services = await q;
      data = {
        total_services: services.length,
        total_members_present: services.reduce((a,s) => a + (s.members_present||0), 0),
        total_visitors: services.reduce((a,s) => a + (s.visitors_present||0), 0),
        avg_attendance: services.length ? Math.round(services.reduce((a,s)=>a+(s.members_present||0),0)/services.length) : 0,
        by_type: {},
      };
      for (const s of services) {
        if (!data.by_type[s.service_type]) data.by_type[s.service_type] = { count: 0, members: 0 };
        data.by_type[s.service_type].count++;
        data.by_type[s.service_type].members += s.members_present||0;
      }
    } else if (type === 'financial') {
      let q = db('giving').whereBetween('date', [period_start, period_end]);
      if (church_id) q = q.where('church_id', church_id);
      const rows = await q;
      data = { total: 0, by_type: {} };
      for (const r of rows) {
        data.total += Number(r.amount||0);
        if (!data.by_type[r.giving_type]) data.by_type[r.giving_type] = 0;
        data.by_type[r.giving_type] += Number(r.amount||0);
      }
    } else if (type === 'membership') {
      let q = db('members');
      if (church_id) q = q.where('church_id', church_id);
      const all = await q;
      data = {
        total: all.length,
        active: all.filter(m=>m.membership_status==='active').length,
        inactive: all.filter(m=>m.membership_status==='inactive').length,
        male: all.filter(m=>m.gender==='male').length,
        female: all.filter(m=>m.gender==='female').length,
        baptized: all.filter(m=>m.baptism_date).length,
      };
    }

    res.render('field/reports/generate', { title: 'Generate Report', churches, result: data, params });
  } catch (err) {
    res.render('field/reports/generate', { title: 'Generate Report', churches, result: null, params, error: err.message });
  }
});

/* Save report */
router.post('/save', async (req, res) => {
  const { report_type, period_start, period_end, period_label, church_id, summary, data } = req.body;
  try {
    const id = await db.insertId('reports', {
      report_type, period_start: period_start||null, period_end: period_end||null,
      period_label, church_id: church_id||null,
      field_report: !church_id,
      summary, data: typeof data === 'string' ? data : JSON.stringify(data),
      generated_by: req.session.userId,
    });
    res.redirect(`/field/reports/${id}`);
  } catch (err) {
    res.redirect('/field/reports?error=' + encodeURIComponent(err.message));
  }
});

router.get('/:id', async (req, res) => {
  const report = await db('reports')
    .leftJoin('churches as c','reports.church_id','c.id')
    .leftJoin('users as u','reports.generated_by','u.id')
    .where('reports.id', req.params.id)
    .select('reports.*','c.name as church_name','u.name as generated_by_name').first();
  if (!report) return res.status(404).render('error', { title: 'Not Found', message: 'Report not found.', user: req.session });
  try { report.data = JSON.parse(report.data); } catch(e) { report.data = {}; }
  res.render('field/reports/show', { title: report.period_label || 'Report', report });
});

module.exports = router;
