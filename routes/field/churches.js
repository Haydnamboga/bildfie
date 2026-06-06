'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

/* List all churches */
router.get('/', async (req, res) => {
  const { status, search } = req.query;
  let q = db('churches').orderBy('name');
  if (status) q = q.where('status', status);
  if (search) q = q.whereILike('name', `%${search}%`);
  const churches = await q;

  // Enrich with member counts
  const ids = churches.map(c => c.id);
  const counts = ids.length
    ? await db('members').whereIn('church_id', ids).where('membership_status','active').groupBy('church_id').select('church_id', db.raw('count(*) as total'))
    : [];
  const countMap = Object.fromEntries(counts.map(c => [c.church_id, c.total]));

  res.render('field/churches/index', {
    title: 'Churches', churches: churches.map(c => ({ ...c, member_count: countMap[c.id] || 0 })),
    filters: { status, search },
  });
});

/* New church form */
router.get('/new', (req, res) => {
  res.render('field/churches/form', { title: 'Add Church', church: {}, errors: [] });
});

/* Create church */
router.post('/', async (req, res) => {
  const { name, code, county, sub_county, ward, village, address, pastor_name, pastor_email, pastor_phone,
    established_date, status, church_type, capacity, description, building_status } = req.body;
  if (!name) {
    return res.render('field/churches/form', { title: 'Add Church', church: req.body, errors: ['Church name is required'] });
  }
  try {
    const id = await db.insertId('churches', {
      name, code, county: county || 'Nyamira', sub_county, ward, village, address,
      pastor_name, pastor_email, pastor_phone, established_date: established_date || null,
      status: status || 'active', church_type: church_type || 'local',
      capacity: capacity || null, description, building_status,
    });
    res.redirect(`/field/churches/${id}`);
  } catch (err) {
    res.render('field/churches/form', { title: 'Add Church', church: req.body, errors: [err.message] });
  }
});

/* View church detail */
router.get('/:id', async (req, res) => {
  const church = await db('churches').where('id', req.params.id).first();
  if (!church) return res.status(404).render('error', { title: 'Not Found', message: 'Church not found.', user: req.session });

  const [members, departments, projects, recentServices, tasks, giving] = await Promise.all([
    db('members').where('church_id', church.id).where('membership_status','active').orderBy('first_name').limit(20),
    db('departments').where('church_id', church.id),
    db('church_projects').where('church_id', church.id).whereIn('status',['active','planning']).orderBy('created_at','desc').limit(5),
    db('services').where('church_id', church.id).orderBy('scheduled_at','desc').limit(5),
    db('tasks').where('church_id', church.id).whereIn('status',['pending','in_progress']).limit(8),
    db('giving').where('church_id', church.id).whereRaw("date >= date('now', '-90 days')").sum('amount as total').first(),
  ]);

  const totalMembers = await db('members').where('church_id', church.id).where('membership_status','active').count('id as c').first();

  res.render('field/churches/show', {
    title: church.name, church, members, departments, projects, recentServices, tasks,
    totalMembers: totalMembers?.c || 0, giving90: giving?.total || 0,
  });
});

/* Edit form */
router.get('/:id/edit', async (req, res) => {
  const church = await db('churches').where('id', req.params.id).first();
  if (!church) return res.status(404).render('error', { title: 'Not Found', message: 'Church not found.', user: req.session });
  res.render('field/churches/form', { title: 'Edit Church', church, errors: [] });
});

/* Update church */
router.post('/:id/edit', async (req, res) => {
  const { name, code, county, sub_county, ward, village, address, pastor_name, pastor_email, pastor_phone,
    established_date, status, church_type, capacity, description, building_status,
    has_land, has_building } = req.body;
  try {
    await db('churches').where('id', req.params.id).update({
      name, code, county, sub_county, ward, village, address,
      pastor_name, pastor_email, pastor_phone,
      established_date: established_date || null,
      status, church_type, capacity: capacity || null, description, building_status,
      has_land: !!has_land, has_building: !!has_building,
    });
    res.redirect(`/field/churches/${req.params.id}`);
  } catch (err) {
    const church = { ...req.body, id: req.params.id };
    res.render('field/churches/form', { title: 'Edit Church', church, errors: [err.message] });
  }
});

module.exports = router;
