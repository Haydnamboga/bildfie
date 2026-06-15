'use strict';
const express     = require('express');
const router      = express.Router();
const { body }    = require('express-validator');
const db          = require('../db/knex');
const requireAuth = require('../middleware/auth');
const validate    = require('../middleware/validate');
const mpesa       = require('../services/mpesa');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

/* ── Validation rules ───────────────────────────────────────────────── */

const pushRules = validate([
  body('phone').notEmpty().withMessage('Phone number required'),
  body('amount').isInt({ min: 1 }).withMessage('Amount must be at least KES 1'),
]);

/* ── POST /api/mpesa/stk-push ───────────────────────────────────────── */
// Free-form payment: caller provides phone + amount directly.

router.post('/stk-push', requireAuth, pushRules, wrap(async (req, res) => {
  const { phone, amount, account_ref = 'Bildfie', description = 'Payment' } = req.body;

  const result = await mpesa.stkPush({ phone, amount, accountRef: account_ref, description });

  const txId = await db.insertId('mpesa_transactions', {
    user_id:             req.session.userId,
    phone:               mpesa.normalizePhone(phone),
    amount:              Math.ceil(amount),
    checkout_request_id: result.CheckoutRequestID,
    merchant_request_id: result.MerchantRequestID,
    status:              'pending',
  });

  res.json({
    transaction_id:      txId,
    checkout_request_id: result.CheckoutRequestID,
    customer_message:    result.CustomerMessage,
  });
}));

/* ── POST /api/mpesa/stk-push/invoice/:invoiceId ────────────────────── */
// Pay a specific bildfie invoice via STK push.
// Invoice must be in 'approved' status and belong to the logged-in client.

router.post('/stk-push/invoice/:invoiceId', requireAuth, wrap(async (req, res) => {
  const invoice = await db('invoices as i')
    .join('projects as p', 'i.project_id', 'p.id')
    .where('i.id', req.params.invoiceId)
    .where('i.recipient_id', req.session.userId)
    .where('i.status', 'approved')
    .select('i.*', 'p.name as project_name')
    .first();

  if (!invoice)
    return res.status(404).json({ error: 'Invoice not found, not yet approved, or not yours' });

  // Prevent duplicate pending payments
  const inflight = await db('mpesa_transactions')
    .where({ invoice_id: invoice.id, status: 'pending' })
    .first('id');
  if (inflight)
    return res.status(409).json({ error: 'A payment is already in progress for this invoice' });

  // Resolve phone: body > user profile > error
  const user  = await db('users').where({ id: req.session.userId }).select('phone').first();
  const phone = req.body.phone || user?.phone;
  if (!phone)
    return res.status(400).json({
      error: 'Phone number required — add one to your profile or include "phone" in this request',
    });

  const result = await mpesa.stkPush({
    phone,
    amount:      invoice.amount,
    accountRef:  `INV-${invoice.id}`,
    description: (invoice.milestone || 'Invoice').slice(0, 13),
  });

  const txId = await db.insertId('mpesa_transactions', {
    invoice_id:          invoice.id,
    user_id:             req.session.userId,
    phone:               mpesa.normalizePhone(phone),
    amount:              invoice.amount,
    checkout_request_id: result.CheckoutRequestID,
    merchant_request_id: result.MerchantRequestID,
    status:              'pending',
  });

  res.json({
    transaction_id:      txId,
    checkout_request_id: result.CheckoutRequestID,
    customer_message:    result.CustomerMessage,
    invoice_id:          invoice.id,
    amount:              invoice.amount,
    currency:            invoice.currency,
  });
}));

/* ── POST /api/mpesa/callback ───────────────────────────────────────── */
// Daraja posts the STK push result here (server-to-server — no session auth).
// We respond 200 immediately; Daraja retries if we don't acknowledge fast enough.
//
// Production hardening TODO:
//   Validate that the request originates from Safaricom's IP range:
//   196.201.214.0/24, 196.201.216.0/24, 196.201.213.0/24, 196.201.217.0/24
//   196.201.214.0/24, 196.201.218.14/24, 125.164.196.0/24, 125.164.197.0/24

router.post('/callback', async (req, res) => {
  // Acknowledge immediately — do not await processing
  res.json({ ResultCode: 0, ResultDesc: 'Accepted' });

  try {
    const callback   = req.body?.Body?.stkCallback;
    if (!callback) return;

    const {
      CheckoutRequestID,
      ResultCode,
      ResultDesc,
    } = callback;

    const tx = await db('mpesa_transactions')
      .where({ checkout_request_id: CheckoutRequestID })
      .first();
    if (!tx) {
      console.warn('[M-Pesa] Unknown CheckoutRequestID:', CheckoutRequestID);
      return;
    }

    // Determine status
    //   0 = success, 1032 = cancelled by user, anything else = failed
    const status =
      ResultCode === 0    ? 'completed' :
      ResultCode === 1032 ? 'cancelled' : 'failed';

    const updates = {
      result_code:  ResultCode,
      result_desc:  ResultDesc,
      status,
      completed_at: db.fn.now(),
    };

    // On success, extract the M-Pesa receipt number from CallbackMetadata
    if (ResultCode === 0) {
      const items = callback.CallbackMetadata?.Item ?? [];
      const get   = name => items.find(i => i.Name === name)?.Value;
      updates.mpesa_receipt = get('MpesaReceiptNumber');
    }

    await db('mpesa_transactions').where({ id: tx.id }).update(updates);

    // If payment succeeded and it's linked to an invoice — mark paid + notify issuer
    if (ResultCode === 0 && tx.invoice_id) {
      await db('invoices').where({ id: tx.invoice_id }).update({
        status:        'paid',
        escrow_status: 'held',
        paid_at:       db.fn.now(),
      });

      const inv = await db('invoices as i')
        .join('users as payer', 'i.recipient_id', 'payer.id')
        .where('i.id', tx.invoice_id)
        .select('i.issuer_id', 'i.amount', 'i.milestone', 'payer.name as payer_name')
        .first();

      if (inv) {
        const receipt = updates.mpesa_receipt ? ` (${updates.mpesa_receipt})` : '';
        await db.insertId('notifications', {
          user_id: inv.issuer_id,
          type:    'payment',
          title:   'Payment Received',
          body:    `KES ${inv.amount.toLocaleString()} received from ${inv.payer_name}${receipt}. Funds held in escrow.`,
          link:    '/dashboard/',
        });
      }
    }
  } catch (err) {
    console.error('[M-Pesa callback error]', err.message);
  }
});

/* ── GET /api/mpesa/status/:checkoutRequestId ───────────────────────── */
// Poll the status of a pending STK push.
// First checks the local DB; if still pending, also queries Daraja directly.

router.get('/status/:checkoutRequestId', requireAuth, wrap(async (req, res) => {
  const tx = await db('mpesa_transactions')
    .where({ checkout_request_id: req.params.checkoutRequestId })
    .first();

  if (!tx) return res.status(404).json({ error: 'Transaction not found' });
  if (tx.user_id !== req.session.userId)
    return res.status(403).json({ error: 'Not authorized' });

  // Live Daraja query when still pending
  if (tx.status === 'pending') {
    try {
      const live = await mpesa.querySTK(req.params.checkoutRequestId);
      return res.json({ status: tx.status, transaction: tx, daraja: live });
    } catch {
      // Daraja query failed (e.g. no credentials yet) — fall through to cached status
    }
  }

  res.json({ status: tx.status, transaction: tx });
}));

/* ── GET /api/mpesa/transactions ────────────────────────────────────── */
// Current user's full payment history.

router.get('/transactions', requireAuth, wrap(async (req, res) => {
  const rows = await db('mpesa_transactions as t')
    .leftJoin('invoices as i', 't.invoice_id', 'i.id')
    .where('t.user_id', req.session.userId)
    .orderBy('t.created_at', 'desc')
    .limit(20)
    .select(
      't.*',
      'i.milestone as invoice_milestone',
      'i.amount as invoice_amount'
    );
  res.json(rows);
}));

/* ── POST /api/mpesa/invoices/:id/release-escrow ────────────────────── */
// The invoice ISSUER (professional) requests that escrow funds be released.
// In a real escrow you'd have a dispute window; here we release immediately.

router.post('/invoices/:id/release-escrow', requireAuth, wrap(async (req, res) => {
  const invoice = await db('invoices')
    .where({
      id:            req.params.id,
      issuer_id:     req.session.userId,
      status:        'paid',
      escrow_status: 'held',
    })
    .first('id', 'amount', 'recipient_id', 'milestone');

  if (!invoice)
    return res.status(404).json({ error: 'Invoice not found, not paid, or escrow already released' });

  await db('invoices').where({ id: invoice.id }).update({ escrow_status: 'released' });

  // Notify the client that funds have been released
  await db.insertId('notifications', {
    user_id: invoice.recipient_id,
    type:    'payment',
    title:   'Escrow Released',
    body:    `KES ${invoice.amount.toLocaleString()} escrow for "${invoice.milestone || 'Invoice'}" has been released to the professional.`,
    link:    '/dashboard/invoices',
  });

  res.json({ message: 'Escrow released successfully' });
}));

module.exports = router;
