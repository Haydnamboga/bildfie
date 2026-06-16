# Build & run the Next.js web app. Build from the repo root:
#   docker build -f infra/docker/web.Dockerfile -t bildfie-web .
FROM node:20-alpine AS base
RUN corepack enable
WORKDIR /app

FROM base AS deps
COPY pnpm-workspace.yaml package.json pnpm-lock.yaml* ./
COPY apps/web/package.json apps/web/
COPY packages ./packages
RUN pnpm install --frozen-lockfile || pnpm install

FROM base AS build
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN pnpm --filter @bildfie/web build

FROM base AS runtime
ENV NODE_ENV=production
COPY --from=build /app ./
EXPOSE 3000
CMD ["pnpm", "--filter", "@bildfie/web", "start"]
