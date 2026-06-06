'use strict';
const express  = require('express');
const bcrypt   = require('bcryptjs');
const db       = require('../db/knex');

const router = express.Router();

/* ── Login page ─────────────────────────────────────────────────────── */
router.get('/login', (req, res) => {
  if (req.session.userId) {
    const role = req.session.systemRole || '';
    if (role === 'field_admin' || role === 'field_staff') return res.redirect('/field/dashboard');
    if (['church_admin','church_elder','department_leader'].includes(role)) return res.redirect('/church/dashboard');
    return res.redirect('/portal/dashboard');
  }
  res.render('auth/login', { title: 'Sign In', error: null, email: '' });
});

router.post('/login', async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.render('auth/login', { title: 'Sign In', error: 'Email and password are required.', email: email || '' });
  }
  try {
    const user = await db('users').where('email', email.toLowerCase().trim()).first();
    if (!user || !await bcrypt.compare(password, user.password_hash)) {
      return res.render('auth/login', { title: 'Sign In', error: 'Invalid email or password.', email });
    }
    req.session.userId     = user.id;
    req.session.userName   = user.name;
    req.session.systemRole = user.system_role || 'member';
    req.session.churchId   = user.church_id;

    const returnTo = req.session.returnTo;
    delete req.session.returnTo;

    const role = user.system_role || 'member';
    if (role === 'field_admin' || role === 'field_staff') return res.redirect('/field/dashboard');
    if (['church_admin','church_elder','department_leader'].includes(role)) return res.redirect('/church/dashboard');
    return res.redirect(returnTo || '/portal/dashboard');
  } catch (err) {
    console.error(err);
    res.render('auth/login', { title: 'Sign In', error: 'Server error. Please try again.', email });
  }
});

/* ── Register ────────────────────────────────────────────────────────── */
router.get('/register', async (req, res) => {
  if (req.session.userId) return res.redirect('/portal/dashboard');
  const churches = await db('churches').where('status', 'active').orderBy('name').select('id','name');
  res.render('auth/register', { title: 'Create Account', error: null, churches });
});

router.post('/register', async (req, res) => {
  const churches = await db('churches').where('status', 'active').orderBy('name').select('id','name');
  const { name, email, password, phone, church_id, gender } = req.body;
  if (!name || !email || !password) {
    return res.render('auth/register', { title: 'Create Account', error: 'All required fields must be filled.', churches });
  }
  try {
    const exists = await db('users').where('email', email.toLowerCase().trim()).first();
    if (exists) {
      return res.render('auth/register', { title: 'Create Account', error: 'Email already registered.', churches });
    }
    const hash = await bcrypt.hash(password, 12);
    const id = await db.insertId('users', {
      name, email: email.toLowerCase().trim(), password_hash: hash,
      phone, church_id: church_id || null, gender,
      system_role: 'member', role: 'client',
    });
    req.session.userId    = id;
    req.session.userName  = name;
    req.session.systemRole = 'member';
    req.session.churchId  = church_id || null;
    res.redirect('/portal/dashboard');
  } catch (err) {
    console.error(err);
    res.render('auth/register', { title: 'Create Account', error: 'Registration failed. Try again.', churches });
  }
});

/* ── Logout ──────────────────────────────────────────────────────────── */
router.post('/logout', (req, res) => { req.session.destroy(() => res.redirect('/')); });
router.get('/logout', (req, res) => { req.session.destroy(() => res.redirect('/')); });

module.exports = router;
