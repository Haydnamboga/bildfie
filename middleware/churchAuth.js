'use strict';
const db = require('../db/knex');

/** Require any authenticated user */
exports.requireAuth = (req, res, next) => {
  if (!req.session.userId) {
    req.session.returnTo = req.originalUrl;
    return res.redirect('/auth/login');
  }
  next();
};

/** Require field-level admin or staff */
exports.requireFieldAdmin = (req, res, next) => {
  if (!req.session.userId) return res.redirect('/auth/login');
  const role = req.session.systemRole || '';
  if (!['field_admin','field_staff'].includes(role)) {
    return res.status(403).render('error', { title: 'Access Denied', message: 'Field admin access required.', user: req.session });
  }
  next();
};

/** Require church admin or above */
exports.requireChurchAdmin = (req, res, next) => {
  if (!req.session.userId) return res.redirect('/auth/login');
  const role = req.session.systemRole || '';
  if (!['field_admin','field_staff','church_admin','church_elder'].includes(role)) {
    return res.status(403).render('error', { title: 'Access Denied', message: 'Church admin access required.', user: req.session });
  }
  next();
};

/** Attach user + church info to all requests */
exports.attachUser = async (req, res, next) => {
  if (req.session.userId) {
    try {
      const user = await db('users').where('id', req.session.userId).first();
      if (user) {
        req.currentUser = user;
        res.locals.currentUser = user;
        res.locals.systemRole = user.system_role;
        if (user.church_id) {
          const church = await db('churches').where('id', user.church_id).first();
          res.locals.currentChurch = church;
          req.currentChurch = church;
        }
        // Unread messages count
        res.locals.unreadMessages = await db('field_communications')
          .where('status', 'unread')
          .modify(q => {
            if (user.system_role === 'field_admin' || user.system_role === 'field_staff') {
              q.where('to_field', true);
            } else {
              q.where('to_church_id', user.church_id);
            }
          })
          .count('id as c').first().then(r => r?.c || 0).catch(() => 0);
      }
    } catch (e) { /* ignore */ }
  }
  res.locals.session = req.session;
  next();
};
