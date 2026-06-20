# BuildLink — Full Deployment Guide
## Architecture: Novahost domain → Render (Node.js + PostgreSQL)

Your domain lives at Novahost. The app runs on Render.
Two DNS records connect them. Render handles SSL automatically.

```
User browser
    │
    ▼
buildlink.co.ke  (Novahost DNS — CNAME points here ↓)
    │
    ▼
buildlink.onrender.com  (Render — Node.js + PostgreSQL)
```

---

## STEP 1 — Buy your domain at Novahost

1. Go to **novahost.co.ke** → Domain Search
2. Search for `buildlink.co.ke` (or `buildlink.africa`, etc.)
3. Purchase it — select **1 year minimum**
4. During checkout — you do NOT need their hosting plan, just the domain
5. Once purchased it appears in **cPanel → Domains**

---

## STEP 2 — Push code to GitHub

In your project folder (D:/0), open a terminal:

```bash
# One-time setup (only if you haven't already)
git init
git remote add origin https://github.com/YOUR_USERNAME/buildlink.git

# Commit and push
git add .
git commit -m "production-ready buildlink"
git push -u origin main
```

> **Make sure `.env` is NOT pushed** — it is in `.gitignore` so git ignores it automatically.
> Double-check: `git status` should NOT show `.env` in the list.

---

## STEP 3 — Deploy on Render (5 minutes)

### 3a. Create Render account
Go to **render.com** → Sign up with GitHub (same account as above).

### 3b. New Blueprint deploy
1. Dashboard → **New** → **Blueprint**
2. Connect your GitHub repo (`buildlink`)
3. Render reads `render.yaml` and automatically creates:
   - **Web Service** `buildlink` — runs Node.js
   - **PostgreSQL Database** `buildlink-db` — free, 1 GB

### 3c. Fill in secret environment variables
After the Blueprint is created, go to:
**buildlink** service → **Environment** tab → add these:

| Variable | Value |
|---|---|
| `APP_URL` | `https://www.yourdomain.co.ke` ← your actual domain |
| `ALLOWED_ORIGINS` | `https://www.yourdomain.co.ke` |
| `MPESA_CONSUMER_KEY` | from Daraja portal |
| `MPESA_CONSUMER_SECRET` | from Daraja portal |
| `MPESA_SHORTCODE` | `4051585` |
| `MPESA_HEAD_OFFICE_SHORTCODE` | parent shortcode if needed |
| `MPESA_PASSKEY` | from Daraja portal |
| `MPESA_CALLBACK_URL` | `https://www.yourdomain.co.ke/api/mpesa/callback` |

`SESSION_SECRET` — Render auto-generates this, no action needed.
`DATABASE_URL` — Render injects this from the attached database, no action needed.

### 3d. First deploy
Click **Manual Deploy** → **Deploy latest commit**.
Watch the logs — you should see:

```
Running migration...
Already up to date — no migrations to run.   (or: Ran batch 1: [...])
BuildLink [production] → http://0.0.0.0:10000
```

Render gives you a temporary URL like `buildlink-abc.onrender.com` — note it down.

---

## STEP 4 — Point your Novahost domain to Render

### 4a. Add the domain in Render first
1. Render dashboard → **buildlink** service → **Settings** → **Custom Domains**
2. Click **Add Custom Domain**
3. Add **`www.yourdomain.co.ke`**
4. Render shows you a CNAME value — copy it (looks like `buildlink-abc.onrender.com`)

### 4b. Log into Novahost cPanel
Go to **novahost.co.ke** → Client Area → login → **cPanel**

### 4c. Open Zone Editor
In cPanel search bar type **Zone Editor** → open it → click **Manage** next to your domain.

### 4d. Add 2 DNS records

**Record 1 — www subdomain (CNAME):**
| Field | Value |
|---|---|
| Name | `www.yourdomain.co.ke.` |
| Type | `CNAME` |
| Value | `buildlink-abc.onrender.com.` (the value Render gave you) |
| TTL | `300` |

**Record 2 — root domain redirect (A record or redirect):**

Option A — If Zone Editor has an **ALIAS** or **ANAME** record type:
| Field | Value |
|---|---|
| Name | `yourdomain.co.ke.` |
| Type | `ALIAS` or `ANAME` |
| Value | `buildlink-abc.onrender.com.` |

Option B — If not, add a redirect in cPanel:
cPanel → **Redirects** → Add:
- Type: `Permanent (301)`
- `http://yourdomain.co.ke` → `https://www.yourdomain.co.ke`

> **Note:** DNS changes take 5–30 minutes to propagate (sometimes up to 2 hours).

### 4e. Wait for SSL
Back in Render → **buildlink** → **Settings** → **Custom Domains** —
once DNS propagates the padlock icon turns green and Render auto-provisions
a free Let's Encrypt certificate. No action needed.

---

## STEP 5 — Promote yourself to admin

Once the site is live, go to Render → **buildlink** → **Shell** tab and run:

```bash
node -e "
require('dotenv').config();
const db = require('./db/knex');
db('users').where({ email: 'your@email.com' }).update({ role: 'admin' })
  .then(n => { console.log('Done:', n, 'row(s) updated'); return db.destroy(); });
"
```

---

## STEP 6 — Update Safaricom Daraja callback URL

Log into **developer.safaricom.co.ke** → My Apps → your app →
update the Callback URL to:
```
https://www.yourdomain.co.ke/api/mpesa/callback
```

---

## STEP 7 — Test everything live

```
✅ https://www.yourdomain.co.ke             → homepage loads
✅ https://www.yourdomain.co.ke/api/health  → { status: "ok" }
✅ Register + login works
✅ STK push to your phone works
✅ M-Pesa callback hits the right URL
```

---

## Ongoing deployments (after launch)

Every time you push to GitHub main branch, Render auto-deploys:
```bash
git add .
git commit -m "your change"
git push
```
Render runs migrations automatically before starting the server (see `Procfile`).

---

## Free tier limits to know

| Resource | Free limit | Upgrade cost |
|---|---|---|
| Web service | Spins down after 15 min idle (30s cold start) | $7/mo — always-on |
| PostgreSQL | 1 GB storage, expires in **90 days** | $7/mo — persistent |
| Bandwidth | 100 GB/mo | Pay-as-you-go after |

> **Before 90 days**: upgrade the database to Starter ($7/mo) or export + reimport.
> For a production site taking real payments, the $14/mo total (web + db) is worth it.

---

## Quick reference — all env vars

```env
NODE_ENV=production
PORT=10000
APP_URL=https://www.yourdomain.co.ke
ALLOWED_ORIGINS=https://www.yourdomain.co.ke
SESSION_SECRET=<auto-generated by Render>
DATABASE_URL=<auto-injected by Render>

MPESA_ENV=production
MPESA_TRANSACTION_TYPE=CustomerBuyGoodsOnline
MPESA_CONSUMER_KEY=pP8iASjFLm244QASGYKyvbzCzO7jux7BCVHX8yfNl7CmALGA
MPESA_CONSUMER_SECRET=GC5FHM5S8Wfw0LJZcVHwnkvVrowcCrdt1nYTVVY3kIUARmbqUOLj5juwiRuiSoHK
MPESA_SHORTCODE=4051585
MPESA_HEAD_OFFICE_SHORTCODE=<from Daraja portal>
MPESA_PASSKEY=67cfefe79abde457578e6f5d79a849064224504966e89b5f7089b9c034ae6ba5
MPESA_CALLBACK_URL=https://www.yourdomain.co.ke/api/mpesa/callback
```

---

## Troubleshooting

**Site shows "Service Unavailable"** — free tier is waking up; wait 30 seconds and refresh.

**www.domain works but domain.co.ke doesn't** — add the redirect in cPanel → Redirects.

**SSL padlock missing** — DNS hasn't propagated yet; wait up to 2 hours.

**M-Pesa callback failing** — check `MPESA_CALLBACK_URL` is set to your live domain (not localhost or the serveo tunnel).

**Migrations failed on deploy** — check Render logs; usually a `DATABASE_URL` that wasn't set yet.
