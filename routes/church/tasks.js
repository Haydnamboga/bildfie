'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireChurchAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireChurchAdmin);

const getChurchId = (req) => req.session.churchId;

router.get('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { status, assigned_to } = req.query;
  let q = db('tasks')
    .leftJoin('users as u','tasks.assigned_to','u.id')
    .leftJoin('users as ab','tasks.assigned_by','ab.id')
    .leftJoin('projects as p','tasks.project_id','p.id')
    .where('tasks.church_id', churchId)
    .orderBy('tasks.due_date')
    .select('tasks.*','u.name as assignee_name','ab.name as assigned_by_name','p.title as project_title');
  if (status) q = q.where('tasks.status', status);
  if (assigned_to) q = q.where('tasks.assigned_to', assigned_to);

  const users = await db('users').where('church_id', churchId).orderBy('name').select('id','name');
  const tasks = await q;
  res.render('church/tasks/index', { title: 'Tasks', tasks, users, filters: { status, assigned_to } });
});

router.post('/', async (req, res) => {
  const churchId = getChurchId(req);
  const { title, description, assigned_to, due_date, priority, project_id } = req.body;
  if (!title) return res.redirect('/church/tasks?error=Title+required');
  try {
    await db.insertId('tasks', {
      church_id: churchId, title, description,
      assigned_to: assigned_to||null, assigned_by: req.session.userId,
      due_date: due_date||null, priority: priority||'medium',
      project_id: project_id||null,
    });
    res.redirect('/church/tasks');
  } catch (err) {
    res.redirect('/church/tasks?error=' + encodeURIComponent(err.message));
  }
});

router.post('/:id/status', async (req, res) => {
  const churchId = getChurchId(req);
  const { status, completion_notes } = req.body;
  const upd = { status };
  if (status === 'completed') { upd.completed_at = new Date().toISOString(); upd.completion_notes = completion_notes; }
  await db('tasks').where('id', req.params.id).where('church_id', churchId).update(upd);
  res.redirect('/church/tasks');
});

module.exports = router;
