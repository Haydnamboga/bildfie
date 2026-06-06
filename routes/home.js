'use strict';
const express = require('express');
const db = require('../db/knex');
const router = express.Router();

/* Public home page */
router.get('/', async (req, res) => {
  const [churches, events, announcements, sermons] = await Promise.all([
    db('churches').where('status','active').orderBy('name').select('id','name','sub_county','pastor_name','description','logo_url').limit(12),
    db('events').where('field_wide', true).where('status','upcoming').orderBy('start_date').limit(4),
    db('announcements').where('field_wide', true).where('active', true).orderBy('created_at','desc').limit(3),
    db('sermons').orderBy('sermon_date','desc').limit(3),
  ]);

  const stats = {
    churches: await db('churches').where('status','active').count('id as c').first().then(r=>r?.c||0),
    members: await db('members').where('membership_status','active').count('id as c').first().then(r=>r?.c||0),
  };

  res.render('home/index', { title: 'Nyamira West Field — SDA', churches, events, announcements, sermons, stats });
});

router.get('/about', (req, res) => res.render('home/about', { title: 'About Us — Nyamira West Field' }));

router.get('/churches', async (req, res) => {
  const { search, sub_county } = req.query;
  let q = db('churches').where('status','active').orderBy('name');
  if (search) q = q.whereILike('name',`%${search}%`);
  if (sub_county) q = q.where('sub_county', sub_county);
  const churches = await q;

  const subCounties = await db('churches').distinct('sub_county').whereNotNull('sub_county').pluck('sub_county');
  res.render('home/churches', { title: 'Our Churches', churches, subCounties, filters: { search, sub_county } });
});

router.get('/events', async (req, res) => {
  const events = await db('events')
    .leftJoin('churches as c','events.church_id','c.id')
    .where(q => q.where('events.field_wide', true).orWhereNotNull('events.church_id'))
    .orderBy('events.start_date')
    .select('events.*','c.name as church_name');
  res.render('home/events', { title: 'Events', events });
});

router.get('/sermons', async (req, res) => {
  const sermons = await db('sermons')
    .join('churches as c','sermons.church_id','c.id')
    .orderBy('sermons.sermon_date','desc').limit(30)
    .select('sermons.*','c.name as church_name');
  res.render('home/sermons', { title: 'Sermons', sermons });
});

router.get('/contact', (req, res) => res.render('home/contact', { title: 'Contact Us', sent: false }));

router.post('/contact', async (req, res) => {
  const { name, email, phone, subject, message, church_id } = req.body;
  try {
    await db.insertId('field_communications', {
      from_church_id: church_id||null, to_field: true,
      subject: subject || `Contact from ${name}`,
      message: `Name: ${name}\nEmail: ${email}\nPhone: ${phone}\n\n${message}`,
      message_type: 'general',
    });
    res.render('home/contact', { title: 'Contact Us', sent: true });
  } catch(e) {
    res.render('home/contact', { title: 'Contact Us', sent: false, error: 'Failed to send. Please try again.' });
  }
});

module.exports = router;
