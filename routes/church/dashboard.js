'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

router.get('/', async (req, res) => {
  const churchId = req.session.churchId;
  if (!churchId) return res.redirect('/field/dashboard');

  const church = await db('churches').where('id', churchId).first();
  if (!church) return res.redirect('/auth/logout');

  const [membersCount, activeTasks, upcomingEvents, announcements, giving, recentServices, unread] = await Promise.all([
    db('members').where('church_id', churchId).where('membership_status','active').count('id as c').first(),
    db('tasks').where('church_id', churchId).whereIn('status',['pending','in_progress']).count('id as c').first(),
    db('events').where(function() { this.where('church_id', churchId).orWhere('field_wide', true); }).where('status','upcoming').orderBy('start_date').limit(5),
    db('announcements').where(function() { this.where('church_id', churchId).orWhere('field_wide', true); }).where('active',true).orderBy('created_at','desc').limit(5),
    db('giving').where('church_id', churchId).whereRaw("date >= date('now','start of month')").sum('amount as total').first(),
    db('services').where('church_id', churchId).orderBy('scheduled_at','desc').limit(4),
    db('field_communications').where('to_church_id', churchId).where('from_field', true).where('status','unread').count('id as c').first(),
  ]);

  const departments = await db('departments').where('church_id', churchId).select('id','name','status');
  const projects = await db('church_projects').where('church_id', churchId).whereIn('status',['active','planning']).limit(5);

  const tasks = await db('tasks')
    .leftJoin('users as u','tasks.assigned_to','u.id')
    .where('tasks.church_id', churchId)
    .whereIn('tasks.status',['pending','in_progress'])
    .orderBy('tasks.due_date').limit(8)
    .select('tasks.*','u.name as assignee_name');

  res.render('church/dashboard', {
    title: church.name + ' — Dashboard',
    church,
    stats: {
      members: membersCount?.c || 0,
      activeTasks: activeTasks?.c || 0,
      givingThisMonth: giving?.total || 0,
      unreadMessages: unread?.c || 0,
    },
    upcomingEvents, announcements, departments, projects, tasks, recentServices,
  });
});

module.exports = router;
