# Build roadmap (from the bildfie Developer Specification v1.0)

The spec is explicit: **build one phase, test it with real users, then start the next.**
Do not build anything outside the current phase. The "one rule": before building a
feature, ask *does this help someone post a job, bid on a job, or get paid for a job?*

## Status

- [x] **Foundation** — fresh, secure Laravel 13 + MySQL app, honest homepage
  (spec §2.3), health check, Phase 1 data model, cPanel deploy pipeline.
- [ ] **Phase 1 — Core Marketplace** (spec §4) *(next)*
- [ ] **Phase 2 — Marketplace Trust Layer** (spec §5)
- [ ] **Phase 3 — CRM Basics** (spec §6)
- [ ] **Phase 4 — CRM Advanced** (spec §7)

## Architecture rules (spec §3 — non-negotiable, already reflected in the schema)
1. Everything is connected — approving a milestone releases escrow automatically.
2. **Mobile first** — every CRM action is designed for a phone; owner may use web.
3. Payment flows through milestones (Phase 3+); Phase 1 holds the full amount.
4. **Photos are proof of work** — no photo, cannot mark complete.
5. All communication stays on-platform (audit trail).

## Phase 1 — Core Marketplace (the next build)
Goal: one full job — posted → bid → hired → escrow funded → work done → approved →
paid → reviewed, with zero errors.

1. **Auth & profiles** (§4.1) — phone-based sign-up; professional profile with photo,
   bio (≤200), single trade, portfolio (3–20 photos w/ caption+location+cost),
   3 private references, availability. Profile hidden until photo + 3 portfolio + availability.
2. **Job posting** (§4.2) — title, trade, description, city/area, fixed KES budget,
   up to 5 photos, start date, duration.
3. **Bidding** (§4.3) — fixed quote, timeline, ≤300-char pitch; owner picks one; job closes.
4. **Escrow** (§4.4) — fund full amount via M-Pesa before work; 2.5% fee at release.
5. **Completion & release** (§4.5) — Mark Complete requires ≥1 photo; owner approves or
   disputes within 48h; auto-release after 48h.
6. **Reviews** (§4.6) — 1–5 stars, 20–400 chars, optional tags; permanent, public.
7. **Disputes** (§4.7) — written + ≥1 photo; funds frozen; manual review at launch.
8. **Notifications** (§4.8) — in-app + SMS for every key event.

## Do NOT build (spec §2 / §8)
Materials/equipment/transport/facilities marketplace, Learn hub, project showcase,
fake activity feed, inflated stats, unearned accreditation logos, FX/weather widgets,
social links, "Follow", day-rate pricing, Gantt/Kanban, AI features, BIM, accounting
integrations, public API — **none of these, until a new spec says so.**
