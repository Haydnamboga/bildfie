# bildfie.com

A single platform with two layers:

1. **Marketplace (acquisition).** Any user signs up and can both outsource for skills (hire) and offer their own skills (get hired). One general user type.
2. **CRM / Dashboard (execution).** Every user manages their own projects, teams, and tasks. A hired person crosses from the marketplace into a project's CRM when invited.

Behind both sits an **admin back-office**, and above that a **super-admin portal**.

## Core principle

**One application. One login. The user's role decides what they see. No subdomains.**

Everyone logs in at `bildfie.com/login`. Server-side RBAC routes each person to their area. Unauthorized access to back-office / super-admin URLs returns **404, not 403** — the area's existence stays invisible.

## Tech stack

| Layer | Choice |
|-------|--------|
| Monorepo | Turborepo + pnpm |
| Web | Next.js 15 (App Router) |
| Backend / API | NestJS (TypeScript) |
| Database | PostgreSQL |
| ORM | Prisma |
| Mobile | React Native + Expo (users only) |
| Auth / RBAC | JWT/session + policy engine |
| Payments | M-Pesa Daraja + Stripe |
| Deploy | Docker + cloud + CDN |

## Repository layout

```
bildfie/
├── apps/
│   ├── web/        # Next.js — all zones, role-gated
│   ├── api/        # NestJS backend (brain + DB access)
│   └── mobile/     # React Native + Expo (users only)
├── packages/
│   ├── db/         # Prisma schema + migrations (single DB truth)
│   ├── types/      # shared TypeScript types / DTOs
│   ├── api-client/ # typed SDK every client calls
│   ├── auth/       # sessions, JWT, RBAC roles & guards
│   ├── ui/         # shared design system (web)
│   ├── validation/ # Zod schemas (front + back)
│   ├── config/     # env, constants, feature flags
│   ├── eslint-config/
│   └── tsconfig/
└── infra/
    ├── docker/     # Dockerfiles per app
    ├── terraform/  # cloud provisioning
    └── ci-cd/      # build & deploy pipelines
```

## Getting started

```bash
# 1. Install dependencies
pnpm install

# 2. Set up environment
cp .env.example .env   # then fill in values

# 3. Generate Prisma client + run first migration
pnpm db:generate
pnpm db:migrate

# 4. Run everything in dev
pnpm dev
```

## Build order

1. `packages/db` — User, Role, Project, Task, Milestone, Offer, Team, Payment.
2. `packages/auth` + RBAC roles and guards.
3. `marketplace` module (profiles, search, offers, hiring).
4. CRM modules (`projects` → `teams` → `tasks` → `milestones`).
5. `payments` (M-Pesa Daraja + Stripe, milestone-triggered).
6. `back-office` + `super-admin` zones.
7. `apps/mobile` (users-only) once the API is stable.

## Security rules (non-negotiable)

- Server-side RBAC on every route and every API endpoint. UI gating is not security.
- 404, not 403, for unauthorized back-office / super-admin access.
- MFA required for admin and super-admin logins.
- Audit-log every privileged change (who, what, when).
- No admin/super-admin surface on mobile.
- One database, accessed only through the API.
