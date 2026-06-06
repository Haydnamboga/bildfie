'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { search, gender, status, dept } = req.query;
  let q = db('members').where('members.church_id', churchId).orderBy('members.first_name').select('members.*');
  if (search) q = q.where(function(){ this.whereILike('first_name',`%${search}%`).orWhereILike('last_name',`%${search}%`).orWhereILike('phone',`%${search}%`); });
  if (gender) q = q.where('gender', gender);
  if (status) q = q.where('membership_status', status);
  if (dept) {
    q = q.join('department_members as dm','members.id','dm.member_id').where('dm.department_id', dept);
  }

  const [members, departments] = await Promise.all([q, db('departments').where('church_id', churchId).orderBy('name')]);
  res.render('church/members/index', { title: 'Members', members, departments, filters: { search, gender, status, dept } });
});

router.get('/new', async (req, res) => {
  res.render('church/members/form', { title: 'Add Member', member: {}, errors: [] });
});

router.post('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { first_name, last_name, email, phone, dob, gender, address, baptism_date, joined_date, membership_status, marital_status, occupation, notes } = req.body;
  if (!first_name || !last_name) {
    return res.render('church/members/form', { title: 'Add Member', member: req.body, errors: ['Name is required'] });
  }
  try {
    const id = await db.insertId('members', {
      church_id: churchId, first_name, last_name, email, phone,
      dob: dob||null, gender, address, baptism_date: baptism_date||null,
      joined_date: joined_date||null, membership_status: membership_status||'active',
      marital_status, occupation, notes,
    });
    res.redirect(`/church/members/${id}`);
  } catch (err) {
    res.render('church/members/form', { title: 'Add Member', member: req.body, errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const churchId = getChurchId(req);
  const member = await db('members').where('id', req.params.id).where('church_id', churchId).first();
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

  res.render('church/members/show', {
    title: `${member.first_name} ${member.last_name}`,
    member, depts, attendance, giving, givingTotal: givingTotal?.t || 0,
  });
});

router.get('/:id/edit', async (req, res) => {
  const churchId = getChurchId(req);
  const member = await db('members').where('id', req.params.id).where('church_id', churchId).first();
  if (!member) return res.status(404).render('error', { title: 'Not Found', message: 'Member not found.', user: req.session });
  res.render('church/members/form', { title: 'Edit Member', member, errors: [] });
});

router.post('/:id/edit', async (req, res) => {
  const churchId = getChurchId(req);
  const { first_name, last_name, email, phone, dob, gender, address, baptism_date, joined_date, membership_status, marital_status, occupation, notes } = req.body;
  try {
    await db('members').where('id', req.params.id).where('church_id', churchId).update({
      first_name, last_name, email, phone, dob: dob||null, gender, address,
      baptism_date: baptism_date||null, joined_date: joined_date||null,
      membership_status, marital_status, occupation, notes,
    });
    res.redirect(`/church/members/${req.params.id}`);
  } catch (err) {
    const member = { ...req.body, id: req.params.id };
    res.render('church/members/form', { title: 'Edit Member', member, errors: [err.message] });
  }
});

module.exports = router;
