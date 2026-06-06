'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  const { status, church_id } = req.query;
  let q = db('church_projects')
    .leftJoin('churches as c','church_projects.church_id','c.id')
    .leftJoin('users as u','church_projects.manager_id','u.id')
    .orderBy('church_projects.created_at','desc')
    .select('church_projects.*','c.name as church_name','u.name as manager_name');
  if (status) q = q.where(church_church_projects.status, status);
  if (church_id) q = q.where('church_projects.church_id', church_id);

  const [projects, churches] = await Promise.all([q, db('churches').where('status','active').orderBy('name').select('id','name')]);
  res.render('field/projects/index', { title: 'Projects', projects, churches, filters: { status, church_id } });
});

router.get('/new', async (req, res) => {
  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  const users = await db('users').whereIn('system_role',['field_admin','field_staff','church_admin']).orderBy('name').select('id','name');
  res.render('field/projects/form', { title: 'New Project', project: {}, churches, users, errors: [] });
});

router.post('/', async (req, res) => {
  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  const users = await db('users').whereIn('system_role',['field_admin','field_staff','church_admin']).orderBy('name').select('id','name');
  const { title, description, category, status, priority, budget, church_id, start_date, end_date, manager_id, notes } = req.body;
  if (!title) return res.render('field/projects/form', { title: 'New Project', project: req.body, churches, users, errors: ['Title required'] });
  try {
    const id = await db.insertId('church_projects', {
      title, description, category, status: status||'planning', priority: priority||'medium',
      budget: budget||0, church_id: church_id||null, start_date: start_date||null,
      end_date: end_date||null, manager_id: manager_id||null, notes,
    });
    res.redirect(`/field/projects/${id}`);
  } catch (err) {
    res.render('field/projects/form', { title: 'New Project', project: req.body, churches, users, errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const project = await db('church_projects')
    .leftJoin('churches as c','church_projects.church_id','c.id')
    .leftJoin('users as u','church_projects.manager_id','u.id')
    .where('church_projects.id', req.params.id)
    .select('church_projects.*','c.name as church_name','u.name as manager_name').first();
  if (!project) return res.status(404).render('error', { title: 'Not Found', message: 'Project not found.', user: req.session });

  const tasks = await db('tasks')
    .leftJoin('users as u','tasks.assigned_to','u.id')
    .where('tasks.project_id', project.id)
    .orderBy('tasks.due_date')
    .select('tasks.*','u.name as assignee_name');

  const users = await db('users').orderBy('name').select('id','name');
  res.render('field/projects/show', { title: project.title, project, tasks, users });
});

router.get('/:id/edit', async (req, res) => {
  const project = await db('church_projects').where('id', req.params.id).first();
  if (!project) return res.status(404).render('error', { title: 'Not Found', message: 'Project not found.', user: req.session });
  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  const users = await db('users').orderBy('name').select('id','name');
  res.render('field/projects/form', { title: 'Edit Project', project, churches, users, errors: [] });
});

router.post('/:id/edit', async (req, res) => {
  const { title, description, category, status, priority, budget, spent, church_id, start_date, end_date, manager_id, completion_percent, notes } = req.body;
  try {
    await db('church_projects').where('id', req.params.id).update({
      title, description, category, status, priority,
      budget: budget||0, spent: spent||0,
      church_id: church_id||null, start_date: start_date||null, end_date: end_date||null,
      manager_id: manager_id||null, completion_percent: completion_percent||0, notes,
    });
    res.redirect(`/field/projects/${req.params.id}`);
  } catch (err) {
    const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
    const users = await db('users').orderBy('name').select('id','name');
    res.render('field/projects/form', { title: 'Edit Project', project: { ...req.body, id: req.params.id }, churches, users, errors: [err.message] });
  }
});

/* Add task to project */
router.post('/:id/tasks', async (req, res) => {
  const { title, description, assigned_to, due_date, priority } = req.body;
  const project = await db('church_projects').where('id', req.params.id).first();
  try {
    await db.insertId('tasks', {
      project_id: req.params.id,
      church_id: project?.church_id || null,
      title, description, assigned_to: assigned_to||null,
      assigned_by: req.session.userId,
      due_date: due_date||null, priority: priority||'medium',
    });
    res.redirect(`/field/projects/${req.params.id}`);
  } catch (err) {
    res.redirect(`/field/projects/${req.params.id}?error=${encodeURIComponent(err.message)}`);
  }
});

module.exports = router;
