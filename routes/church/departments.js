'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

const DEPT_TYPES = ['Youth Fellowship','Women\'s Ministry','Men\'s Ministry','Children\'s Ministry',
  'Music Ministry','Evangelism','Elders Board','Deacons Board','Pathfinder Club',
  'Adventurer Club','Community Services','Health Ministry','Education','Other'];

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const departments = await db('departments')
    .leftJoin('members as l','departments.leader_id','l.id')
    .where('departments.church_id', churchId)
    .select('departments.*',db.raw("l.first_name || ' ' || l.last_name as leader_name"));

  // Count members per dept
  const deptIds = departments.map(d => d.id);
  const counts = deptIds.length
    ? await db('department_members').whereIn('department_id', deptIds).groupBy('department_id').select('department_id',db.raw('count(*) as c'))
    : [];
  const countMap = Object.fromEntries(counts.map(c => [c.department_id, c.c]));

  res.render('church/departments/index', {
    title: 'Departments', DEPT_TYPES,
    departments: departments.map(d => ({ ...d, member_count: countMap[d.id] || 0 })),
  });
});

router.post('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { name, code, description, leader_id, meeting_day, meeting_time } = req.body;
  if (!name) return res.redirect('/church/departments?error=Name+required');
  try {
    await db.insertId('departments', { church_id: churchId, name, code, description, leader_id: leader_id||null, meeting_day, meeting_time });
    res.redirect('/church/departments');
  } catch (err) {
    res.redirect('/church/departments?error=' + encodeURIComponent(err.message));
  }
});

router.get('/:id', async (req, res) => {
  const churchId = getChurchId(req);
  const dept = await db('departments').where('id', req.params.id).where('church_id', churchId).first();
  if (!dept) return res.status(404).render('error', { title: 'Not Found', message: 'Department not found.', user: req.session });

  const members = await db('department_members')
    .join('members as m','department_members.member_id','m.id')
    .where('department_members.department_id', dept.id)
    .select('department_members.*',db.raw("m.first_name || ' ' || m.last_name as name"),'m.phone','m.email','m.profile_pic');

  const allMembers = await db('members').where('church_id', churchId).where('membership_status','active').orderBy('first_name')
    .select('id',db.raw("first_name || ' ' || last_name as name"));

  res.render('church/departments/show', { title: dept.name, dept, members, allMembers });
});

router.post('/:id/members', async (req, res) => {
  const { member_id, role_in_dept } = req.body;
  try {
    await db('department_members').insert({ department_id: req.params.id, member_id, role_in_dept: role_in_dept||'member', joined_at: new Date().toISOString().slice(0,10) })
      .onConflict(['department_id','member_id']).ignore();
    res.redirect(`/church/departments/${req.params.id}`);
  } catch (err) {
    res.redirect(`/church/departments/${req.params.id}?error=` + encodeURIComponent(err.message));
  }
});

router.post('/:id/members/:memberId/remove', async (req, res) => {
  await db('department_members').where('department_id', req.params.id).where('member_id', req.params.memberId).delete();
  res.redirect(`/church/departments/${req.params.id}`);
});

module.exports = router;
