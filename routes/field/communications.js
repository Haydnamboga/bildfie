'use strict';
const express = require('express');
const db = require('../../db/knex');
const { requireFieldAdmin } = require('../../middleware/churchAuth');
const router = express.Router();

router.use(requireFieldAdmin);

router.get('/', async (req, res) => {
  const inbox = await db('field_communications')
    .leftJoin('churches as c','field_communications.from_church_id','c.id')
    .leftJoin('users as u','field_communications.sender_id','u.id')
    .where('field_communications.to_field', true)
    .orderBy('field_communications.created_at','desc')
    .select('field_communications.*','c.name as from_church_name','u.name as sender_name');

  const sent = await db('field_communications')
    .leftJoin('churches as c','field_communications.to_church_id','c.id')
    .where('field_communications.from_field', true)
    .orderBy('field_communications.created_at','desc')
    .select('field_communications.*','c.name as to_church_name');

  res.render('field/communications/index', { title: 'Communications', inbox, sent });
});

router.get('/compose', async (req, res) => {
  const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
  res.render('field/communications/compose', { title: 'Send Message', churches, errors: [] });
});

router.post('/compose', async (req, res) => {
  const { to_church_id, subject, message, message_type } = req.body;
  if (!subject || !message) {
    const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
    return res.render('field/communications/compose', { title: 'Send Message', churches, errors: ['Subject and message are required'] });
  }
  try {
    await db.insertId('field_communications', {
      to_church_id: to_church_id||null, subject, message,
      message_type: message_type||'general',
      from_field: true, to_field: false,
      sender_id: req.session.userId,
    });
    res.redirect('/field/communications');
  } catch (err) {
    const churches = await db('churches').where('status','active').orderBy('name').select('id','name');
    res.render('field/communications/compose', { title: 'Send Message', churches, errors: [err.message] });
  }
});

router.get('/:id', async (req, res) => {
  const msg = await db('field_communications')
    .leftJoin('churches as c','field_communications.from_church_id','c.id')
    .leftJoin('churches as c2','field_communications.to_church_id','c2.id')
    .leftJoin('users as u','field_communications.sender_id','u.id')
    .where('field_communications.id', req.params.id)
    .select('field_communications.*','c.name as from_church_name','c2.name as to_church_name','u.name as sender_name').first();
  if (!msg) return res.status(404).render('error', { title: 'Not Found', message: 'Message not found.', user: req.session });

  // Mark as read
  if (msg.status === 'unread') await db('field_communications').where('id', msg.id).update({ status: 'read' });

  const replies = await db('field_communications')
    .leftJoin('users as u','field_communications.sender_id','u.id')
    .where('field_communications.reply_to', msg.id)
    .select('field_communications.*','u.name as sender_name');

  res.render('field/communications/show', { title: msg.subject, msg, replies });
});

router.post('/:id/reply', async (req, res) => {
  const { message, to_church_id } = req.body;
  const orig = await db('field_communications').where('id', req.params.id).first();
  if (!orig) return res.redirect('/field/communications');
  await db.insertId('field_communications', {
    to_church_id: to_church_id || orig.from_church_id || null,
    subject: 'Re: ' + orig.subject,
    message,
    from_field: true, to_field: false,
    sender_id: req.session.userId,
    reply_to: orig.id,
    message_type: 'response',
  });
  await db('field_communications').where('id', orig.id).update({ status: 'replied' });
  res.redirect(`/field/communications/${req.params.id}`);
});

module.exports = router;
