'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  const { church_id, search, gender, status } = req.query;
  let q = db('members')
    .join('churches as c','members.church_id','c.id')
    .orderBy('members.first_name')
    .select('members.*','c.name as church_name');
  if (church_id) q = q.where('members.church_id', church_id);
  if (search) q = q.where(function() {
    this.whereILike('members.first_name',`%${search}%`).orWhereILike('members.last_name',`%${search}%`).orWhereILike('members.email',`%${search}%`).orWhereILike('members.phone',`%${search}%`);
  });
  if (gender) q = q.where('members.gender', gender);
  if (status) q = q.where('members.membership_status', status);

  const [members, churches] = await Promise.all([q.limit(100), db('churches').where('status','active').orderBy('name').select('id','name')]);
  res.render('field/members/index', { title: 'All Members', members, churches, filters: { church_id, search, gender, status } });
});

router.get('/:id', async (req, res) => {
  const member = await db('members')
    .join('churches as c','members.church_id','c.id')
    .where('members.id', req.params.id)
    .select('members.*','c.name as church_name').first();
  if (!member) return res.status(404).render('error', { title: 'Not Found', message: 'Member not found.', user: req.session });

  const depts = await db('department_members')
    .join('departments as d','department_members.department_id','d.id')
    .where('department_members.member_id', member.id)
    .select('d.name','department_members.role_in_dept');

  const attendance = await db('attendance')
    .join('services as s','attendance.service_id','s.id')
    .where('attendance.member_id', member.id)
    .orderBy('s.scheduled_at','desc').limit(10)
    .select('s.title','s.scheduled_at','s.service_type');

  const giving = await db('giving').where('member_id', member.id).orderBy('date','desc').limit(10);
  const givingTotal = await db('giving').where('member_id', member.id).sum('amount as t').first();

  res.render('field/members/show', { title: `${member.first_name} ${member.last_name}`, member, depts, attendance, giving, givingTotal: givingTotal?.t || 0 });
});

module.exports = router;
