'use strict';
const express    = require('express');
const router     = express.Router();
const crypto     = require('crypto');
const path       = require('path');
const fs         = require('fs');
const rateLimit  = require('express-rate-limit');
const { body }   = require('express-validator');
const db         = require('../db/knex');
const bcrypt     = require('bcryptjs');
const validate   = require('../middleware/validate');
const { avatarUpload, documentUpload, wrapUpload } = require('../middleware/upload');

const mailer = require('../services/email');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

/* ── Rate limiters ──────────────────────────────────────────────────── */

/** 10 attempts per 15 min on login / register */
const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 10,
  message: { error: 'Too many attempts — please try again in 15 minutes' },
  standardHeaders: true,
  legacyHeaders: false,
  skip: () => process.env.NODE_ENV === 'test',
});

/** 3 forgot-password requests per hour per IP */
const forgotLimiter = rateLimit({
  windowMs: 60 * 60 * 1000,
  max: 3,
  message: { error: 'Too many password-reset requests — please try again later' },
  standardHeaders: true,
  legacyHeaders: false,
  skip: () => process.env.NODE_ENV === 'test',
});

/* ── Validation rule sets ───────────────────────────────────────────── */

const loginRules = validate([
  body('email').isEmail().withMessage('Valid email required').normalizeEmail(),
  body('password').notEmpty().withMessage('Password required'),
]);

const registerRules = validate([
  body('name').trim().notEmpty().withMessage('Name required')
    .isLength({ max: 100 }).withMessage('Name too long'),
  body('email').isEmail().withMessage('Valid email required').normalizeEmail(),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('role').optional().isIn(['client', 'professional', 'supplier'])
    .withMessage('Invalid role'),
  body('trade').if(body('role').equals('professional'))
    .notEmpty().withMessage('Trade is required for professionals'),
]);

const profileRules = validate([
  body('name').optional().trim().notEmpty().withMessage('Name cannot be blank'),
  body('phone').optional().isMobilePhone().withMessage('Invalid phone number'),
]);

const passwordRules = validate([
  body('current_password').notEmpty().withMessage('Current password required'),
  body('new_password').isLength({ min: 6 }).withMessage('New password must be at least 6 characters'),
]);

const forgotRules = validate([
  body('email').isEmail().withMessage('Valid email required').normalizeEmail(),
]);

const resetRules = validate([
  body('token').notEmpty().withMessage('Reset token required'),
  body('new_password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
]);

/* ── Session helper ─────────────────────────────────────────────────── */

/** Regenerate session ID (prevents session-fixation attacks) */
const regenerateSession = req =>
  new Promise((resolve, reject) =>
    req.session.regenerate(err => err ? reject(err) : resolve())
  );

/* ── Routes ─────────────────────────────────────────────────────────── */

// POST /api/auth/login
router.post('/login', authLimiter, loginRules, wrap(async (req, res) => {
  const { email, password } = req.body;
  const user = await db('users').where({ email }).first();
  if (!user || !await bcrypt.compare(password, user.password_hash))
    return res.status(401).json({ error: 'Invalid email or password' });

  // Regenerate session ID before writing data (session-fixation prevention)
  await regenerateSession(req);
  req.session.userId   = user.id;
  req.session.userName = user.name;
  req.session.userRole = user.role;

  res.json({
    id: user.id, name: user.name, email: user.email,
    role: user.role, avatar: user.avatar, location: user.location,
  });
}));

// POST /api/auth/register
router.post('/register', authLimiter, registerRules, wrap(async (req, res) => {
  const {
    name, email, password,
    role = 'client',
    location = 'Nairobi, Kenya',
    phone,
    // Professional-specific fields
    trade, experience_years = 0, hourly_rate = 0,
    tags = [],        // array of skill strings
  } = req.body;

  const existing = await db('users').where({ email }).first('id');
  if (existing) return res.status(409).json({ error: 'Email already registered' });

  const password_hash = await bcrypt.hash(password, 10);

  // Wrap user + optional profile creation in a single transaction
  const uid = await db.transaction(async trx => {
    const userId = await db.insertId.call(trx, 'users', {
      name, email, password_hash, role, location, phone: phone || null,
    });

    if (role === 'professional') {
      await trx('professional_profiles').insert({
        user_id: userId,
        trade:            trade || 'General',
        experience_years: Number(experience_years) || 0,
        hourly_rate:      Number(hourly_rate) || 0,
      });
      if (Array.isArray(tags) && tags.length) {
        await trx('professional_tags').insert(
          tags.slice(0, 10).map(tag => ({ user_id: userId, tag: String(tag).trim() }))
        );
      }
    }

    return userId;
  });

  await regenerateSession(req);
  req.session.userId   = uid;
  req.session.userName = name;
  req.session.userRole = role;

  // Send welcome email (non-blocking — don't fail registration if email fails)
  mailer.sendWelcome(email, name, role).catch(err =>
    console.warn('[email] welcome failed:', err.message)
  );

  res.status(201).json({ id: uid, name, email, role });
}));

// POST /api/auth/logout — for fetch() calls from JS
router.post('/logout', (req, res) => {
  req.session.destroy(() => res.json({ message: 'Logged out' }));
});

// GET /api/auth/logout — for direct href links (sidebar, etc.)
router.get('/logout', (req, res) => {
  req.session.destroy(() => res.redirect('/login'));
});

// GET /api/auth/me
router.get('/me', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  const user = await db('users')
    .where({ id: req.session.userId })
    .select('id','name','email','role','phone','location','avatar','bio',
            'verified','verification_badge','subscription_plan','rating','review_count','created_at')
    .first();
  if (!user) return res.status(404).json({ error: 'User not found' });
  if (user.role === 'professional') {
    const prof = await db('professional_profiles')
      .where({ user_id: user.id })
      .select('trade','experience_years','nca_grade','hourly_rate','available','jobs_done','on_time_percent')
      .first();
    const tagRows = await db('professional_tags').where({ user_id: user.id }).select('tag');
    Object.assign(user, prof || {}, { tags: tagRows.map(r => r.tag) });
  }
  res.json(user);
}));

// PUT /api/auth/profile — update own profile
router.put('/profile', profileRules, wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  const { name, phone, location, bio } = req.body;
  const updates = { updated_at: db.fn.now() };
  if (name     !== undefined) updates.name     = name;
  if (phone    !== undefined) updates.phone    = phone;
  if (location !== undefined) updates.location = location;
  if (bio      !== undefined) updates.bio      = bio;
  await db('users').where({ id: req.session.userId }).update(updates);
  if (name) req.session.userName = name;
  const user = await db('users')
    .where({ id: req.session.userId })
    .select('id','name','email','role','phone','location','avatar','bio',
            'verified','verification_badge','subscription_plan')
    .first();
  res.json(user);
}));

// PUT /api/auth/password — change password
router.put('/password', passwordRules, wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  const { current_password, new_password } = req.body;
  const user = await db('users').where({ id: req.session.userId }).select('password_hash').first();
  if (!await bcrypt.compare(current_password, user.password_hash))
    return res.status(401).json({ error: 'Current password is incorrect' });
  await db('users').where({ id: req.session.userId }).update({
    password_hash: await bcrypt.hash(new_password, 10),
    updated_at: db.fn.now(),
  });
  res.json({ message: 'Password updated successfully' });
}));

// POST /api/auth/forgot-password
// Generates a reset token valid for 1 hour.
// In dev: returns token in response body so you can test without SMTP.
// In prod: send token via email (wire up nodemailer in Phase 5).
router.post('/forgot-password', forgotLimiter, forgotRules, wrap(async (req, res) => {
  const { email } = req.body;
  const user = await db('users').where({ email }).select('id','name').first();

  // Always respond 200 to avoid user-enumeration
  if (!user) return res.json({ message: 'If that email exists you will receive a reset link' });

  // Invalidate any existing unused tokens for this user
  await db('password_resets').where({ user_id: user.id, used: false }).delete();

  const token = crypto.randomBytes(32).toString('hex');
  const expires_at = new Date(Date.now() + 60 * 60 * 1000); // 1 hour

  await db.insertId('password_resets', { user_id: user.id, token, expires_at });

  const resetLink = `${process.env.APP_URL || 'http://localhost:3000'}/reset-password?token=${token}`;

  // In dev: also expose the token/link in the response so you can test without SMTP
  const devExtra = process.env.NODE_ENV !== 'production'
    ? { token, resetLink }
    : {};

  // Send email (non-blocking — always respond 200 to prevent user enumeration)
  mailer.sendPasswordReset(email, user.name, token).catch(err =>
    console.error('[email] password-reset send failed:', err.message)
  );

  res.json({ message: 'If that email exists you will receive a reset link', ...devExtra });
}));

// POST /api/auth/reset-password
router.post('/reset-password', resetRules, wrap(async (req, res) => {
  const { token, new_password } = req.body;
  const record = await db('password_resets')
    .where({ token, used: false })
    .where('expires_at', '>', new Date())
    .first();
  if (!record) return res.status(400).json({ error: 'Invalid or expired reset token' });

  await db.transaction(async trx => {
    await trx('users').where({ id: record.user_id }).update({
      password_hash: await bcrypt.hash(new_password, 10),
      updated_at: db.fn.now(),
    });
    await trx('password_resets').where({ id: record.id }).update({ used: true });
  });

  res.json({ message: 'Password reset successfully — please log in' });
}));

/* ── Professional profile management ───────────────────────────────── */

const proProfileRules = validate([
  body('trade').optional().trim().notEmpty().withMessage('Trade cannot be blank'),
  body('experience_years').optional().isInt({ min: 0, max: 60 }),
  body('hourly_rate').optional().isInt({ min: 0 }),
  body('available').optional().isBoolean(),
  body('tags').optional().isArray().withMessage('Tags must be an array'),
  body('nca_grade').optional().trim(),
]);

const becomeProfRules = validate([
  body('trade').trim().notEmpty().withMessage('Trade is required'),
  body('experience_years').optional().isInt({ min: 0, max: 60 }),
  body('hourly_rate').optional().isInt({ min: 0 }),
  body('nca_grade').optional().trim(),
  body('tags').optional().isArray(),
]);

// PUT /api/auth/professional — update professional profile (or upgrade client → professional)
router.put('/professional', proProfileRules, wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  if (req.session.userRole === 'admin') return res.status(403).json({ error: 'Admin accounts cannot use this endpoint' });

  const { trade, experience_years, hourly_rate, available, nca_grade, tags } = req.body;

  // If the user is not yet a professional, require a trade and upgrade their role
  const isUpgrade = req.session.userRole !== 'professional';
  if (isUpgrade && !trade) return res.status(400).json({ error: 'Trade is required' });

  const profileUpdates = {};
  if (trade            !== undefined) profileUpdates.trade            = trade;
  if (experience_years !== undefined) profileUpdates.experience_years = Number(experience_years);
  if (hourly_rate      !== undefined) profileUpdates.hourly_rate      = Number(hourly_rate);
  if (available        !== undefined) profileUpdates.available        = available ? 1 : 0;
  if (nca_grade        !== undefined) profileUpdates.nca_grade        = nca_grade;

  await db.transaction(async trx => {
    // Upgrade role if needed
    if (isUpgrade) {
      await trx('users').where({ id: req.session.userId }).update({ role: 'professional', updated_at: db.fn.now() });
    }

    const exists = await trx('professional_profiles').where({ user_id: req.session.userId }).first('id');
    if (exists) {
      if (Object.keys(profileUpdates).length)
        await trx('professional_profiles').where({ user_id: req.session.userId }).update(profileUpdates);
    } else {
      await trx('professional_profiles').insert({ user_id: req.session.userId, trade: trade || 'General', ...profileUpdates });
    }

    // Replace tags wholesale if provided
    if (Array.isArray(tags)) {
      await trx('professional_tags').where({ user_id: req.session.userId }).delete();
      if (tags.length) {
        await trx('professional_tags').insert(
          tags.slice(0, 15).map(tag => ({ user_id: req.session.userId, tag: String(tag).trim() }))
        );
      }
    }
  });

  if (isUpgrade) req.session.userRole = 'professional';

  // Return updated profile + user info
  const user    = await db('users').where({ id: req.session.userId })
    .select('id','name','email','role','phone','location','avatar','bio','verified','verification_badge').first();
  const prof    = await db('professional_profiles').where({ user_id: req.session.userId }).first();
  const tagRows = await db('professional_tags').where({ user_id: req.session.userId }).select('tag');
  res.json({ ...user, ...(prof || {}), tags: tagRows.map(r => r.tag) });
}));

// POST /api/auth/become-professional — upgrade a client account to professional
router.post('/become-professional', becomeProfRules, wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });

  const { trade, experience_years = 0, hourly_rate = 0, nca_grade, tags = [] } = req.body;

  await db.transaction(async trx => {
    await trx('users').where({ id: req.session.userId }).update({
      role: 'professional',
      updated_at: db.fn.now(),
    });

    const exists = await trx('professional_profiles').where({ user_id: req.session.userId }).first('id');
    const profileData = {
      trade,
      experience_years: Number(experience_years) || 0,
      hourly_rate:      Number(hourly_rate) || 0,
      ...(nca_grade ? { nca_grade } : {}),
    };
    if (exists) {
      await trx('professional_profiles').where({ user_id: req.session.userId }).update(profileData);
    } else {
      await trx('professional_profiles').insert({ user_id: req.session.userId, ...profileData });
    }

    if (Array.isArray(tags) && tags.length) {
      await trx('professional_tags').where({ user_id: req.session.userId }).delete();
      await trx('professional_tags').insert(
        tags.slice(0, 15).map(tag => ({ user_id: req.session.userId, tag: String(tag).trim() }))
      );
    }
  });

  req.session.userRole = 'professional';

  const user = await db('users').where({ id: req.session.userId })
    .select('id','name','email','role','phone','location','avatar','bio','verified','verification_badge').first();
  const prof    = await db('professional_profiles').where({ user_id: req.session.userId }).first();
  const tagRows = await db('professional_tags').where({ user_id: req.session.userId }).select('tag');

  res.json({ ...user, ...(prof || {}), tags: tagRows.map(r => r.tag) });
}));

/* ── Avatar upload ──────────────────────────────────────────────────── */

// PUT /api/auth/avatar — upload or replace profile picture
// multipart/form-data field name: "avatar"
router.put('/avatar', wrapUpload(avatarUpload.single('avatar')), wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  if (!req.file) return res.status(400).json({ error: 'No file uploaded' });

  // Delete old avatar file if it exists and is locally stored
  const existing = await db('users').where({ id: req.session.userId }).select('avatar').first();
  if (existing?.avatar && existing.avatar.startsWith('/uploads/')) {
    const oldPath = path.join(__dirname, '..', existing.avatar);
    if (fs.existsSync(oldPath)) fs.unlinkSync(oldPath);
  }

  const avatarUrl = `/uploads/avatars/${req.file.filename}`;
  await db('users').where({ id: req.session.userId }).update({
    avatar:     avatarUrl,
    updated_at: db.fn.now(),
  });

  res.json({ avatar: avatarUrl });
}));

/* ── Verification documents ─────────────────────────────────────────── */

const VALID_DOC_TYPES = ['nca_license', 'portfolio', 'id_document', 'other'];

// POST /api/auth/professional/documents — upload a document for verification
// multipart/form-data fields: files[] (up to 5), document_type (optional)
router.post('/professional/documents',
  wrapUpload(documentUpload.array('files', 5)),
  wrap(async (req, res) => {
    if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
    if (req.session.userRole !== 'professional')
      return res.status(403).json({ error: 'Professional account required' });
    if (!req.files?.length) return res.status(400).json({ error: 'No files uploaded' });

    const docType = VALID_DOC_TYPES.includes(req.body.document_type)
      ? req.body.document_type : 'nca_license';

    const rows = req.files.map(f => ({
      user_id:       req.session.userId,
      filename:      f.filename,
      original_name: f.originalname,
      mime_type:     f.mimetype,
      size_bytes:    f.size,
      document_type: docType,
      status:        'pending',
    }));

    await db('verification_documents').insert(rows);

    res.status(201).json({
      message:  `${rows.length} document(s) uploaded and pending review`,
      files:    rows.map(r => ({ original_name: r.original_name, document_type: r.document_type })),
    });
  })
);

// GET /api/auth/professional/documents — list own uploaded documents
router.get('/professional/documents', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  const docs = await db('verification_documents')
    .where({ user_id: req.session.userId })
    .orderBy('created_at', 'desc')
    .select('id','original_name','document_type','mime_type','size_bytes','status','rejection_reason','created_at','reviewed_at');
  res.json(docs);
}));

// DELETE /api/auth/professional/documents/:id — delete own document (only if pending)
router.delete('/professional/documents/:id', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });

  const doc = await db('verification_documents')
    .where({ id: req.params.id, user_id: req.session.userId })
    .first();
  if (!doc) return res.status(404).json({ error: 'Document not found' });
  if (doc.status !== 'pending')
    return res.status(409).json({ error: 'Cannot delete a document that has already been reviewed' });

  // Remove physical file
  const filePath = path.join(__dirname, '..', 'uploads', 'documents', doc.filename);
  if (fs.existsSync(filePath)) fs.unlinkSync(filePath);

  await db('verification_documents').where({ id: doc.id }).delete();
  res.json({ message: 'Document deleted' });
}));

/* ── Saved professionals ────────────────────────────────────────────── */

// GET /api/auth/saved
router.get('/saved', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  const rows = await db('saved_professionals as sp')
    .join('users as u', 'sp.professional_id', 'u.id')
    .leftJoin('professional_profiles as pp', 'pp.user_id', 'u.id')
    .where('sp.user_id', req.session.userId)
    .orderBy('sp.saved_at', 'desc')
    .select('u.id','u.name','u.avatar','u.location','u.rating','u.review_count',
            'u.verification_badge','pp.trade','pp.hourly_rate','pp.available');
  res.json(rows);
}));

// POST /api/auth/saved/:id
router.post('/saved/:id', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  await db('saved_professionals')
    .insert({ user_id: req.session.userId, professional_id: req.params.id })
    .onConflict(['user_id', 'professional_id']).ignore();
  res.json({ saved: true });
}));

// DELETE /api/auth/saved/:id
router.delete('/saved/:id', wrap(async (req, res) => {
  if (!req.session.userId) return res.status(401).json({ error: 'Not authenticated' });
  await db('saved_professionals')
    .where({ user_id: req.session.userId, professional_id: req.params.id })
    .delete();
  res.json({ saved: false });
}));

module.exports = router;
