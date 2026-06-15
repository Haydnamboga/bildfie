# bildfie

**Verified construction marketplace + CRM for Kenya.**
Owners post jobs, verified professionals bid, money is held in escrow and released
only when work is approved.

> Built strictly to the bildfie Developer Specification v1.0. Each phase is built
> and tested before the next begins — see [`docs/ROADMAP.md`](docs/ROADMAP.md).

## Stack

| Layer | Choice | Why |
|---|---|---|
| Framework | **Laravel 13** (PHP 8.3) | Leading, secure PHP framework; runs natively on cPanel |
| Database | **MySQL / MariaDB** | Native cPanel database |
| Hosting | **cPanel** | Your existing host; deploy via a built ZIP |
| Code store | **GitHub (private)** | Safe version history; builds the deploy ZIP automatically |
| Payments | M-Pesa (Safaricom Daraja) | Escrow funding + payout |
| SMS | Africa's Talking | Notifications (spec §4.8) |

## How hosting works (read this first)

```
You push code  ─►  GitHub (private repo, safe history)
                        │
                        ▼  (GitHub Action builds it)
                 bildfie-cpanel.zip  ─►  upload + extract in cPanel  ─►  https://your-domain
                                                                              │
                                                  open it on your phone AND your PC
```

GitHub **stores** the code and **builds the ZIP**. cPanel **runs** the live site and
gives one public web address that any device can open. Full steps:
[`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).

## Local development

Requires PHP 8.3+ and Composer (Node optional, only for front-end assets).

```bash
composer install
cp .env.example .env
php artisan key:generate

# Quick start with SQLite (no MySQL needed locally):
#   set DB_CONNECTION=sqlite and DB_DATABASE=/abs/path/database/database.sqlite in .env
touch database/database.sqlite
php artisan migrate

php artisan serve   # http://127.0.0.1:8000
```

Health check: `GET /health` → `{ "status": "ok", "database": "ok" }`.

## Project layout

| Path | What |
|---|---|
| `routes/web.php` | Web routes (homepage, health, Phase 1 entry points) |
| `resources/views/` | Blade templates (mobile-first, spec §9) |
| `database/migrations/` | Schema — `..._create_bildfie_phase1_tables.php` is the core model |
| `app/Models/` | Eloquent models |
| `.github/workflows/build-cpanel-zip.yml` | Builds the cPanel deploy ZIP on every push |
| `docs/` | Deployment, security, and roadmap docs |

## Security

Secrets (M-Pesa keys, DB password, SMS keys) live **only** in the server `.env`,
never in GitHub. See [`docs/SECURITY.md`](docs/SECURITY.md) — including the action
required for credentials that leaked in the previous version of this repo.
