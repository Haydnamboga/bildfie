# infra/terraform

Cloud provisioning for bildfie (AWS/GCP) — the one API + one PostgreSQL DB,
fronted by a CDN. Add `.tf` files here (or swap for a `k8s/` directory).

Suggested resources:

- Managed PostgreSQL (RDS / Cloud SQL)
- Container service for `apps/api` and `apps/web` (ECS/Fargate, Cloud Run, or k8s)
- CDN in front of the web app + static assets
- Secrets manager for the values in `.env.example`
