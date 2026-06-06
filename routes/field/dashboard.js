'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  try {
    const [churches, membersCount, projectsActive, tasksOpen, events, announcements, givingThisMonth] = await Promise.all([
      db('churches').where('status', 'active').count('id as c').first(),
      db('members').where('membership_status', 'active').count('id as c').first(),
      db('church_projects').whereIn('status', ['active','planning']).count('id as c').first(),
      db('tasks').whereIn('status', ['pending','in_progress']).count('id as c').first(),
      db('events').where('status', 'upcoming').orderBy('start_date').limit(5),
      db('announcements').where('field_wide', true).where('active', true).orderBy('created_at','desc').limit(5),
      db('giving').whereRaw("date >= date('now','start of month')").sum('amount as total').first(),
    ]);

    const churchList = await db('churches').where('status','active').orderBy('name').select('id','name','pastor_name','sub_county');

    // Recent activities
    const recentTasks = await db('tasks')
      .leftJoin('users as u', 'tasks.assigned_to', 'u.id')
      .leftJoin('projects as p', 'tasks.project_id', 'p.id')
      .orderBy('tasks.created_at','desc').limit(8)
      .select('tasks.*','u.name as assignee_name','p.title as project_title');

    // Attendance trend (last 4 sabbaths)
    const recentServices = await db('services')
      .where('service_type','sabbath')
      .orderBy('scheduled_at','desc').limit(20)
      .select('church_id','scheduled_at','members_present','visitors_present');

    res.render('field/dashboard', {
      title: 'Field Dashboard — Nyamira West Field',
      stats: {
        churches: churches?.c || 0,
        members: membersCount?.c || 0,
        projectsActive: projectsActive?.c || 0,
        tasksOpen: tasksOpen?.c || 0,
        givingThisMonth: givingThisMonth?.total || 0,
      },
      events, announcements, churchList, recentTasks, recentServices,
    });
  } catch (err) {
    console.error(err);
    res.render('error', { title: 'Error', message: err.message, user: req.session });
  }
});

module.exports = router;
