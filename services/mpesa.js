'use strict';
/**
 * M-Pesa Daraja API service
 *
 * Supports both sandbox and production via MPESA_ENV env var.
 * Token is cached in-process and refreshed 60 s before expiry.
 *
 * Key env vars:
 *   MPESA_ENV              sandbox | production  (default: sandbox)
 *   MPESA_CONSUMER_KEY
 *   MPESA_CONSUMER_SECRET
 *   MPESA_SHORTCODE        Your till number (Buy Goods) or paybill
 *   MPESA_PASSKEY          From Daraja portal
 *   MPESA_CALLBACK_URL     Public HTTPS URL that Safaricom will POST to
 *   MPESA_TRANSACTION_TYPE CustomerBuyGoodsOnline | CustomerPayBillOnline (default: CustomerBuyGoodsOnline)
 */

const SANDBOX_URL    = 'https://sandbox.safaricom.co.ke';
const PRODUCTION_URL = 'https://api.safaricom.co.ke';

const baseUrl = () =>
  process.env.MPESA_ENV === 'production' ? PRODUCTION_URL : SANDBOX_URL;

/* ── OAuth token — in-memory cache ─────────────────────────────────── */

let _token   = null;
let _expires = 0;

async function getAccessToken() {
  if (_token && Date.now() < _expires) return _token;

  const key    = process.env.MPESA_CONSUMER_KEY;
  const secret = process.env.MPESA_CONSUMER_SECRET;
  if (!key || !secret) throw new Error('MPESA_CONSUMER_KEY / MPESA_CONSUMER_SECRET not set in .env');

  const auth = Buffer.from(`${key}:${secret}`).toString('base64');
  const res  = await fetch(
    `${baseUrl()}/oauth/v1/generate?grant_type=client_credentials`,
    { headers: { Authorization: `Basic ${auth}` } }
  );

  if (!res.ok) {
    const txt = await res.text();
    throw new Error(`M-Pesa OAuth ${res.status}: ${txt}`);
  }

  const { access_token, expires_in } = await res.json();
  _token   = access_token;
  _expires = Date.now() + (Number(expires_in) - 60) * 1000; // refresh 60 s early
  return _token;
}

/* ── Helpers ────────────────────────────────────────────────────────── */

/**
 * Normalise any Kenyan phone format to 2547XXXXXXXX
 * Accepts: 0712345678 | 254712345678 | +254712345678 | 712345678
 */
function normalizePhone(raw) {
  const digits = String(raw).replace(/\D/g, '');
  if (digits.startsWith('254') && digits.length === 12) return digits;
  if (digits.startsWith('0')   && digits.length === 10) return '254' + digits.slice(1);
  if (digits.length === 9 && (digits[0] === '7' || digits[0] === '1'))
    return '254' + digits;
  throw new Error(
    `Invalid Kenyan phone number "${raw}" — use 07XXXXXXXX, +2547XXXXXXXX, or 2547XXXXXXXX`
  );
}

/** YYYYMMDDHHmmss timestamp required by Daraja */
function darajaTimestamp() {
  return new Date().toISOString().replace(/[-T:.Z]/g, '').slice(0, 14);
}

/** Daraja password = base64(ShortCode + Passkey + Timestamp) */
function makePassword(shortCode, passkey, ts) {
  return Buffer.from(`${shortCode}${passkey}${ts}`).toString('base64');
}

/* ── STK Push ───────────────────────────────────────────────────────── */

/**
 * stkPush({ phone, amount, accountRef, description })
 *
 * Initiates a Lipa na M-Pesa Online (STK Push) prompt on the customer's phone.
 *
 * @param {string} phone       Kenyan number in any common format
 * @param {number} amount      Amount in KES (integers only — fractional part ignored)
 * @param {string} accountRef  Max 12 characters — shown on customer's receipt
 * @param {string} description Max 13 characters — shown in the push notification
 *
 * @returns {object} Daraja response with CheckoutRequestID and CustomerMessage
 */
async function stkPush({ phone, amount, accountRef = 'Bildfie', description = 'Payment' }) {
  const token     = await getAccessToken();
  const tillCode  = process.env.MPESA_SHORTCODE;
  // For tills under a head-office paybill, MPESA_HEAD_OFFICE_SHORTCODE is the
  // initiating shortcode (used for BusinessShortCode + password).
  // For standalone tills, leave it unset and both fields use MPESA_SHORTCODE.
  const headCode  = process.env.MPESA_HEAD_OFFICE_SHORTCODE || tillCode;
  const passkey   = process.env.MPESA_PASSKEY;
  const callback  = process.env.MPESA_CALLBACK_URL || 'https://placeholder.example.com/api/mpesa/callback';
  const txType    = process.env.MPESA_TRANSACTION_TYPE || 'CustomerBuyGoodsOnline';

  if (!tillCode) throw new Error('MPESA_SHORTCODE not set in .env');
  if (!passkey)  throw new Error('MPESA_PASSKEY not set in .env');

  const ts       = darajaTimestamp();
  const password = makePassword(headCode, passkey, ts);
  const phone254 = normalizePhone(phone);

  const payload = {
    BusinessShortCode: headCode,                    // Head-office or standalone shortcode
    Password:          password,
    Timestamp:         ts,
    TransactionType:   txType,
    Amount:            Math.ceil(Number(amount)),   // Daraja rejects decimals
    PartyA:            phone254,
    PartyB:            tillCode,                    // The till that receives the payment
    PhoneNumber:       phone254,
    CallBackURL:       callback,
    AccountReference:  String(accountRef).slice(0, 12),
    TransactionDesc:   String(description).slice(0, 13),
  };

  const res = await fetch(`${baseUrl()}/mpesa/stkpush/v1/processrequest`, {
    method: 'POST',
    headers: {
      Authorization:  `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  const data = await res.json();

  if (!res.ok || data.ResponseCode !== '0') {
    const msg = data.errorMessage || data.ResponseDescription || `STK Push failed (${res.status})`;
    throw Object.assign(new Error(msg), { mpesa: data, status: res.status });
  }

  return data;
  // Returns: { MerchantRequestID, CheckoutRequestID, ResponseCode, ResponseDescription, CustomerMessage }
}

/* ── Query STK status ───────────────────────────────────────────────── */

/**
 * querySTK(checkoutRequestId)
 *
 * Poll Daraja for the result of a previously initiated STK push.
 * Useful when your callback URL hasn't received a result yet.
 */
async function querySTK(checkoutRequestId) {
  const token     = await getAccessToken();
  const shortCode = process.env.MPESA_SHORTCODE;
  const passkey   = process.env.MPESA_PASSKEY;
  const ts        = darajaTimestamp();
  const password  = makePassword(shortCode, passkey, ts);

  const res = await fetch(`${baseUrl()}/mpesa/stkpush/v1/query`, {
    method: 'POST',
    headers: {
      Authorization:  `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      BusinessShortCode: shortCode,
      Password:          password,
      Timestamp:         ts,
      CheckoutRequestID: checkoutRequestId,
    }),
  });

  return res.json();
}

module.exports = { stkPush, querySTK, normalizePhone, getAccessToken };
