# Security

## Golden rules
1. **Secrets never go in GitHub.** M-Pesa keys, DB password, SMS keys, `APP_KEY`,
   and mail passwords live **only** in the server `.env`. `.env` is git-ignored.
2. **The repo is private.** Keep `haydnamboga/bildfie` private on GitHub.
3. **Production runs locked down:** `APP_DEBUG=false`, `APP_ENV=production`,
   HTTPS on (`SESSION_SECURE_COOKIE=true`).
4. **Rotate anything that leaks.** Once a secret has been committed to git, treat it
   as compromised forever — even if you delete the file later. The only real fix is
   to rotate (regenerate) it.

## 🔴 Action required: credentials leaked in the previous version

The earlier (Node.js) version of this repository committed **real M-Pesa production
credentials** in plain text inside `DEPLOY.md` — the consumer key, consumer secret,
and passkey. They are still recoverable from old git history.

**This fresh Laravel rebuild does not contain them**, but deleting them is not enough.
You must do the following:

1. **Rotate the M-Pesa credentials now.** Log in to
   [Safaricom Daraja](https://developer.safaricom.co.ke) → your app → **regenerate**
   the Consumer Secret (and re-issue the passkey for your shortcode). The leaked
   values become useless the moment you rotate. Until then, anyone who saw the old
   repo could attempt to use them.
2. Put the **new** values only in the server `.env` on cPanel.
3. Do not paste real keys into chat, commits, issues, or `.env.example`.

> Optional hardening: the leaked values can also be purged from old git history
> (history rewrite + force-push, or deleting/recreating the repo). Rotation in step 1
> is what actually neutralises the risk; history cleanup is secondary. Ask if you want
> me to walk through it.

## How secrets flow
```
.env.example  (committed — placeholders only, safe)
        │ copy on the server, fill real values
        ▼
.env          (lives ONLY on cPanel, git-ignored, never uploaded to GitHub)
```

## Built-in protections (Laravel)
- Passwords hashed with bcrypt (`password` cast to `hashed`).
- CSRF protection on web forms, SQL-injection-safe queries via Eloquent.
- Request validation on every input (added per feature).
- Session cookies `HttpOnly` + `Secure` in production.

## Payments & money (spec §3, §4.4)
- Escrow is tied to a job and released only on owner approval (or 48h auto-release).
- The 2.5% fee is taken **at release**, not at deposit.
- M-Pesa callbacks must be verified server-side before any funds move; never trust a
  client-reported payment state.
