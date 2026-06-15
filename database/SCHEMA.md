# bildfie — Database Structure

**Engine:** MariaDB / MySQL · **Driver:** mysqli (`config/db.php`) · **Charset:** utf8mb4 · **Tables:** InnoDB
**Migrations:** numbered SQL in `database/migrations/` applied by `php database/migrate.php` (tracked in `schema_migrations`, never run twice). Bcrypt/seed data via `php database/seed.php`.

## Conventions
- PK `id BIGINT UNSIGNED AUTO_INCREMENT`; FKs `<entity>_id`.
- `public_id CHAR(36)` (UUID) on anything exposed in URLs/APIs — never expose sequential ids.
- `created_at` / `updated_at` timestamps; `deleted_at` for soft deletes where needed.
- Booleans `is_*` TINYINT(1); statuses as ENUM (or lookup tables when they grow).
- Money: `DECIMAL(14,2)` + `currency_code`; the financial **ledger** uses integer minor units.
- Every FK and common filter/sort column is indexed. Foreign keys enforced.

## Domains (the organization)

| # | Domain | Tables | Status |
|---|---|---|---|
| 0 | **Foundation — Identity, Access & Config** | settings, currencies, regions, departments, roles, permissions, role_permissions, staff, users, audit_logs, schema_migrations | ✅ built |
| 1 | **Catalog & Verticals** | verticals, categories, services, service_attributes, units, tags | ⏳ next (0002) |
| 2 | **Profiles & Trust** | provider_profiles, provider_services, portfolios, certifications, skills, reviews, verifications (KYC) | ⏳ |
| 3 | **Learn / Education** | learn_levels, learn_subjects, learn_resources, learn_tasks, tutor_profiles | ⏳ |
| 4 | **Demand & Delivery** | listings, projects, bids, tasks, milestones, orders, bookings | ⏳ |
| 5 | **Money & Commerce** | wallets, ledger_entries, escrow_holds, invoices, payments, payouts, refunds, credit_notes, plans, subscriptions, transactions | ⏳ |
| 6 | **Advertising (retail media)** | advertisers, campaigns, creatives, placements, ad_events, ad_invoices | ⏳ |
| 7 | **CRM & Sales** | leads, contacts, companies, deals, activities, proposals, estimates | ⏳ |
| 8 | **Trust, Safety & Risk** | reports, moderation_cases, enforcement_actions, disputes, fraud_signals | ⏳ |
| 9 | **Communications** | conversations, messages, notifications, notification_templates, message_logs | ⏳ |
| 10 | **System & Analytics** | feature_flags, integrations, api_keys, webhooks, jobs, tax_rates, exchange_rates, event_logs | ⏳ |

## Step-by-step migration plan
- `0001_foundation.sql` — identity, access & config ✅
- `0002_catalog.sql` — verticals + categories + services (makes **Services** & **Learn** DB-driven)
- `0003_profiles.sql` — provider profiles, reviews, verifications
- `0004_marketplace.sql` — listings, projects, bids, tasks, orders
- `0005_money.sql` — wallet, ledger, escrow, invoices, payments, subscriptions
- `0006_ads.sql` — advertisers, campaigns, placements, events
- `0007_crm_trust_comms.sql` — leads, moderation, messaging/notifications
- `0008_system_analytics.sql` — flags, integrations, events + **performance pass** (composite indexes, counters, archival)

## Performance approach (added incrementally)
Index every FK + filter/sort column → add composite indexes from real query patterns (EXPLAIN) → denormalized counters & read-model tables for dashboards → pagination everywhere → archive `audit_logs`/`event_logs` → caching, then read-replica, as load grows.
