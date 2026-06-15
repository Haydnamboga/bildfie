'use strict';
require('dotenv').config();

const express    = require('express');
const session    = require('express-session');
const path       = require('path');
const cors       = require('cors');
const helmet     = require('helmet');
const compression = require('compression');
const morgan     = require('morgan');
const rateLimit  = require('express-rate-limit');

const app  = express();
const PORT = process.env.PORT || 3000;
const PROD = process.env.NODE_ENV === 'production';

/* ── Trust proxy (Railway / Render / Heroku sit behind a load balancer) */
// Required for req.ip, secure cookies, and rate-limiter to see real client IP.
if (PROD) app.set('trust proxy', 1);

/* ── HTTPS redirect (production only) ───────────────────────────────── */
app.use((req, res, next) => {
  if (PROD && req.headers['x-forwarded-proto'] !== 'https') {
    return res.redirect(301, `https://${req.headers.host}${req.url}`);
  }
  next();
});

/* ── Security headers (helmet) ───────────────────────────────────────── */
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc:    ["'self'"],
      scriptSrc:     ["'self'", "'unsafe-inline'", 'https://cdn.jsdelivr.net', 'https://unpkg.com'],
      scriptSrcAttr: ["'unsafe-inline'"],   // allow onclick= / onsubmit= in HTML
      styleSrc:      ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdn.jsdelivr.net'],
      fontSrc:       ["'self'", 'https://fonts.gstatic.com', 'https://cdn.jsdelivr.net'],
      imgSrc:        ["'self'", 'data:', 'https:', 'blob:'],
      connectSrc:    ["'self'", 'https://cdn.jsdelivr.net'],
      frameSrc:      ["'none'"],
      objectSrc:     ["'none'"],
      upgradeInsecureRequests: PROD ? [] : null,
    },
  },
  crossOriginEmbedderPolicy: false,
}));

/* ── Compression ─────────────────────────────────────────────────────── */
app.use(compression());

/* ── Request logging ─────────────────────────────────────────────────── */
// Compact format in production (combined → log aggregators), dev-friendly otherwise
app.use(morgan(PROD ? 'combined' : 'dev'));

/* ── CORS ────────────────────────────────────────────────────────────── */
const allowedOrigins = PROD
  ? (process.env.ALLOWED_ORIGINS || process.env.APP_URL || '').split(',').map(s => s.trim()).filter(Boolean)
  : true;   // dev: allow everything

app.use(cors({
  origin:      allowedOrigins,
  credentials: true,
  methods:     ['GET','POST','PUT','PATCH','DELETE','OPTIONS'],
  allowedHeaders: ['Content-Type','Authorization'],
}));

/* ── Global rate limiter ─────────────────────────────────────────────── */
// Coarse limit on all routes — per-route limiters (auth, forgot-pw) are tighter.
app.use(rateLimit({
  windowMs:        60 * 1000,   // 1 minute window
  max:             300,          // 300 req / min per IP
  standardHeaders: true,
  legacyHeaders:   false,
  skip: () => process.env.NODE_ENV === 'test',
  message: { error: 'Too many requests — please slow down' },
}));

/* ── Body parsers ────────────────────────────────────────────────────── */
// Tighten JSON payload limit (default is 100kb, we want to prevent large abuse)
app.use(express.json({ limit: '2mb' }));
app.use(express.urlencoded({ extended: true, limit: '2mb' }));

/* ── Session ─────────────────────────────────────────────────────────── */
app.use(session({
  secret:            process.env.SESSION_SECRET || 'bildfie-dev-secret-change-in-production',
  resave:            false,
  saveUninitialized: false,
  name:              'bl.sid',        // don't leak default "connect.sid" name
  cookie: {
    secure:   PROD,                   // HTTPS only in production
    httpOnly: true,                   // no JS access to cookie
    sameSite: PROD ? 'strict' : 'lax',
    maxAge:   7 * 24 * 60 * 60 * 1000  // 7 days
  },
}));

/* ── EJS templating ──────────────────────────────────────────────────── */
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

/* ── Expose session user to every EJS template ───────────────────────── */
app.use((req, res, next) => {
  res.locals.user = (req.session && req.session.userId)
    ? { id: req.session.userId, name: req.session.userName, role: req.session.userRole }
    : null;
  next();
});

/* ── Static assets (CSS, JS, images) ────────────────────────────────── */
app.use(express.static(path.join(__dirname, 'public'), {
  index: false,                     // never auto-serve index.html — EJS routes own the pages
  maxAge: PROD ? '7d' : 0,          // cache static assets for 7 days in prod
}));

/* ── Uploaded files (avatars, documents) ─────────────────────────────── */
// UUID-named files: not guessable, but not secret — serve publicly.
app.use('/uploads', express.static(path.join(__dirname, 'uploads'), {
  maxAge: PROD ? '30d' : 0,
}));

/* ── API routes ──────────────────────────────────────────────────────── */
app.use('/api/auth',          require('./routes/auth'));
app.use('/api/projects',      require('./routes/projects'));
app.use('/api/professionals', require('./routes/professionals'));
app.use('/api/bids',          require('./routes/bids'));
app.use('/api/reviews',       require('./routes/reviews'));
app.use('/api/invitations',   require('./routes/invitations'));
app.use('/api/messages',      require('./routes/messages'));
app.use('/api/dashboard',     require('./routes/dashboard'));
app.use('/api/mpesa',         require('./routes/mpesa'));
app.use('/api/admin',         require('./routes/admin'));
app.get('/api/health',        (req, res) => res.json({
  status:  'ok',
  env:     process.env.NODE_ENV,
  uptime:  Math.floor(process.uptime()),
  time:    new Date(),
  version: '2026-06-15a',
}));

/* ── One-time admin bootstrap (disabled after first use) ─────────────── */
// Visit /api/setup-admin?secret=ADMIN_BOOTSTRAP_SECRET to promote the
// ADMIN_BOOTSTRAP_EMAIL account to admin. Env var is deleted after use.
app.get('/api/setup-admin', async (req, res) => {
  const secret = process.env.ADMIN_BOOTSTRAP_SECRET;
  const email  = process.env.ADMIN_BOOTSTRAP_EMAIL;
  if (!secret || !email) return res.status(410).json({ error: 'Setup endpoint not configured or already used' });
  if (req.query.secret !== secret) return res.status(403).json({ error: 'Invalid secret' });
  const db = require('./db/knex');
  const n  = await db('users').where({ email }).update({ role: 'admin' });
  if (!n) return res.status(404).json({ error: `No user found with email ${email}` });
  // Clear the env vars so this can never be used again
  delete process.env.ADMIN_BOOTSTRAP_SECRET;
  delete process.env.ADMIN_BOOTSTRAP_EMAIL;
  res.json({ message: `✅ ${email} is now an admin. This endpoint is now disabled.` });
});

/* ── Public pages ────────────────────────────────────────────────────── */
app.get('/',              (req, res) => res.render('pages/index'));
app.get('/professionals', (req, res) => res.render('pages/professionals'));
app.get('/login',           (req, res) => res.render('pages/login'));
app.get('/register',        (req, res) => res.render('pages/register'));
app.get('/reset-password',  (req, res) => res.render('pages/reset-password'));
app.get('/account',         (req, res) => res.render('pages/account'));
app.get('/profile',       (req, res) => res.render('pages/profile'));

/* ── Dashboard ───────────────────────────────────────────────────────── */
app.get('/dashboard',             (req, res) => res.render('pages/dashboard/index'));
app.get('/dashboard/messages',    (req, res) => res.render('pages/dashboard/messages'));
app.get('/dashboard/invitations', (req, res) => res.render('pages/dashboard/invitations'));
app.get('/dashboard/invoices',    (req, res) => res.render('pages/dashboard/invoices'));

/* ── Admin ───────────────────────────────────────────────────────────── */
app.get('/admin',  (req, res) => res.render('pages/admin/index'));
app.get('/admin/*', (req, res) => res.render('pages/admin/index'));  // SPA-style sub-routes

/* ── Global error handler ────────────────────────────────────────────── */
// eslint-disable-next-line no-unused-vars
app.use((err, req, res, next) => {
  // Never leak stack traces to clients in production
  const status  = err.status || err.statusCode || 500;
  const message = (PROD && status === 500) ? 'Internal server error' : (err.message || 'Internal server error');

  if (status >= 500) console.error('[ERROR]', err.stack || err.message);
  else               console.warn('[WARN]',  err.message);

  if (req.path.startsWith('/api'))
    return res.status(status).json({ error: message });

  res.status(status).render('pages/404');
});

/* ── 404 ─────────────────────────────────────────────────────────────── */
app.use((req, res) => {
  if (req.path.startsWith('/api')) return res.status(404).json({ error: 'Not found' });
  res.status(404).render('pages/404');
});

/* ── Start ───────────────────────────────────────────────────────────── */
app.listen(PORT, () => {
  console.log(`\nBildfie [${process.env.NODE_ENV}] → http://localhost:${PORT}`);
  if (!PROD) {
    console.log(`  Dashboard : http://localhost:${PORT}/dashboard`);
    console.log(`  Admin     : http://localhost:${PORT}/admin`);
  }
  if (!process.env.SESSION_SECRET || process.env.SESSION_SECRET.includes('bildfie-dev-secret'))
    console.warn('\n  ⚠  SESSION_SECRET is using the default dev value — set a strong secret before deploying!\n');
});
