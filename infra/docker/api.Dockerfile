# Build & run the NestJS API. Build from the repo root:
#   docker build -f infra/docker/api.Dockerfile -t bildfie-api .
FROM node:20-alpine AS base
RUN corepack enable
WORKDIR /app

FROM base AS deps
COPY pnpm-workspace.yaml package.json pnpm-lock.yaml* ./
COPY apps/api/package.json apps/api/
COPY packages ./packages
RUN pnpm install --frozen-lockfile || pnpm install

FROM base AS build
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN pnpm --filter @bildfie/db generate
RUN pnpm --filter @bildfie/api build

FROM base AS runtime
ENV NODE_ENV=production
COPY --from=build /app ./
EXPOSE 4000
CMD ["node", "apps/api/dist/main.js"]
