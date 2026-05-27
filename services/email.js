'use strict';
/**
 * Email service — nodemailer with SMTP transport
 *
 * Required env vars (set in Render dashboard):
 *   SMTP_HOST   — e.g. smtp.gmail.com | smtp.sendgrid.net | smtp.mailgun.org
 *   SMTP_PORT   — 587 (STARTTLS) or 465 (SSL)
 *   SMTP_USER   — SMTP username / API key username
 *   SMTP_PASS   — SMTP password / API key
 *   SMTP_FROM   — Sender address, e.g. "Bildfie <noreply@bildfie.com>"
 *
 * In development:
 *   If SMTP_HOST is not set the mailer falls back to console.log so you
 *   can test the full flow without configuring email credentials.
 *
 * Gmail quick-start:
 *   1. Enable 2-Factor Authentication on your Google account
 *   2. Generate an App Password at https://myaccount.google.com/apppasswords
 *   3. Set SMTP_HOST=smtp.gmail.com, SMTP_PORT=587,
 *      SMTP_USER=you@gmail.com, SMTP_PASS=<app-password>,
 *      SMTP_FROM="Bildfie <you@gmail.com>"
 */

const nodemailer = require('nodemailer');

/* ── Create transporter (lazy — only when first email is sent) ───── */

let _transporter = null;

function getTransporter() {
  if (_transporter) return _transporter;

  const host = process.env.SMTP_HOST;
  if (!host) {
    // Dev fallback: log emails to console instead of sending them
    _transporter = nodemailer.createTransport({ jsonTransport: true });
    return _transporter;
  }

  _transporter = nodemailer.createTransport({
    host,
    port:   Number(process.env.SMTP_PORT) || 587,
    secure: Number(process.env.SMTP_PORT) === 465,   // true for port 465 (SSL)
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
    tls: {
      // Allow self-signed certs on internal networks (Render / Railway)
      rejectUnauthorized: process.env.NODE_ENV === 'production',
    },
  });

  return _transporter;
}

const FROM = () => process.env.SMTP_FROM || 'Bildfie <noreply@bildfie.com>';
const APP  = () => process.env.APP_URL  || 'http://localhost:3000';

/* ── Core send helper ────────────────────────────────────────────── */

async function sendMail({ to, subject, html, text }) {
  const transporter = getTransporter();
  const msg = { from: FROM(), to, subject, html, text };

  if (!process.env.SMTP_HOST) {
    // Dev mode: dump to console instead of sending
    console.log('\n📧 [EMAIL — not sent in dev, set SMTP_HOST to enable]');
    console.log(`  To:      ${to}`);
    console.log(`  Subject: ${subject}`);
    if (text) console.log(`  Body:    ${text.slice(0, 200)}`);
    console.log('');
    return;
  }

  await transporter.sendMail(msg);
}

/* ── Email templates ─────────────────────────────────────────────── */

/**
 * Wrap content in the standard Bildfie email shell
 */
function shell(title, body) {
  return `<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>${title}</title>
<style>
  body{margin:0;padding:0;background:#f2f4f8;font-family:'Segoe UI',Arial,sans-serif;font-size:14px;color:#1e293b}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e4e7ef}
  .hdr{background:#011D47;padding:24px 32px;text-align:center}
  .hdr-name{color:#fff;font-size:20px;font-weight:800;letter-spacing:.02em}
  .hdr-sub{color:#C43100;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;margin-top:3px}
  .body{padding:28px 32px}
  h2{font-size:17px;font-weight:700;color:#011D47;margin:0 0 12px}
  p{margin:0 0 14px;line-height:1.6;color:#374151}
  .btn{display:inline-block;padding:11px 28px;background:#C43100;color:#fff!important;border-radius:8px;
       text-decoration:none;font-weight:700;font-size:14px;margin:6px 0 14px}
  .code{background:#f2f4f8;border-radius:6px;padding:10px 16px;font-family:monospace;
        font-size:15px;letter-spacing:.08em;color:#011D47;font-weight:700}
  .ftr{background:#f2f4f8;padding:16px 32px;text-align:center;font-size:11px;color:#9ca3af}
  .ftr a{color:#6b7280}
  hr{border:none;border-top:1px solid #e4e7ef;margin:18px 0}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <div class="hdr-name">Bildfie</div>
    <div class="hdr-sub">Africa's Construction Marketplace</div>
  </div>
  <div class="body">${body}</div>
  <div class="ftr">
    © ${new Date().getFullYear()} Bildfie · <a href="${APP()}">bildfie.com</a>
    · This email was sent to you because you have an account on Bildfie
  </div>
</div>
</body>
</html>`;
}

/* ── sendPasswordReset ───────────────────────────────────────────── */

async function sendPasswordReset(to, name, token) {
  const link = `${APP()}/reset-password?token=${token}`;
  const subject = 'Reset your Bildfie password';
  const html = shell(subject, `
    <h2>Password Reset Request</h2>
    <p>Hi ${esc(name)},</p>
    <p>We received a request to reset your Bildfie password. Click the button below to choose a new one:</p>
    <p><a href="${link}" class="btn">Reset My Password</a></p>
    <p style="font-size:12px;color:#6b7280">This link expires in <strong>1 hour</strong>.
       If you did not request this, you can safely ignore this email — your password will not change.</p>
    <hr>
    <p style="font-size:12px;color:#6b7280">Or copy this link into your browser:<br>
       <span style="color:#2563eb;word-break:break-all">${link}</span></p>
  `);
  const text = `Hi ${name},\n\nReset your Bildfie password: ${link}\n\nLink expires in 1 hour.\n`;
  await sendMail({ to, subject, html, text });
}

/* ── sendWelcome ─────────────────────────────────────────────────── */

async function sendWelcome(to, name, role) {
  const roleNote = {
    professional: 'Start by completing your profile and uploading your NCA licence to get verified.',
    supplier:     'Add your first material or equipment listing to start reaching contractors.',
    client:       'Post your first project or browse professionals ready to work.',
  }[role] || 'Explore the platform and let us know how we can help.';

  const subject = `Welcome to Bildfie, ${name}!`;
  const html = shell(subject, `
    <h2>Welcome aboard, ${esc(name)}!</h2>
    <p>Your Bildfie account is ready. You joined as a <strong>${role}</strong>.</p>
    <p>${roleNote}</p>
    <p><a href="${APP()}" class="btn">Go to Bildfie</a></p>
    <hr>
    <p style="font-size:12px;color:#6b7280">Questions? Reply to this email or visit our help centre.</p>
  `);
  const text = `Welcome to Bildfie, ${name}!\n\nGo to ${APP()} to get started.\n`;
  await sendMail({ to, subject, html, text });
}

/* ── sendVerificationResult ──────────────────────────────────────── */

async function sendVerificationResult(to, name, approved, docType, badgeOrReason) {
  const friendly = docType.replace(/_/g, ' ');
  if (approved) {
    const subject = 'Your document has been approved ✓';
    const html = shell(subject, `
      <h2>Document Approved ✓</h2>
      <p>Hi ${esc(name)},</p>
      <p>Great news — your <strong>${friendly}</strong> has been reviewed and approved.
         Your profile now shows the <strong>"${esc(badgeOrReason)}"</strong> badge.</p>
      <p><a href="${APP()}/profile" class="btn">View Your Profile</a></p>
    `);
    await sendMail({ to, subject, html, text: `Hi ${name}, your ${friendly} was approved. Badge: ${badgeOrReason}` });
  } else {
    const subject = 'Action required — document could not be approved';
    const html = shell(subject, `
      <h2>Document Not Approved</h2>
      <p>Hi ${esc(name)},</p>
      <p>We were unable to approve your <strong>${friendly}</strong>.</p>
      <p><strong>Reason:</strong> ${esc(badgeOrReason)}</p>
      <p>Please re-upload a clearer or more complete document.</p>
      <p><a href="${APP()}/profile" class="btn">Re-upload Document</a></p>
    `);
    await sendMail({ to, subject, html, text: `Hi ${name}, your ${friendly} was not approved. Reason: ${badgeOrReason}` });
  }
}

/* ── HTML escape (safe to use in email templates) ────────────────── */

function esc(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

module.exports = {
  sendMail,
  sendPasswordReset,
  sendWelcome,
  sendVerificationResult,
};
