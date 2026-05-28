FROM node:20-alpine AS builder

WORKDIR /workspace/src/tg

ENV NUXT_TELEMETRY_DISABLED=1

COPY ./src/tg/package*.json ./

RUN npm ci

COPY ./src/tg /workspace/src/tg
COPY ./src/front /workspace/src/front

RUN npm run build

FROM node:20-alpine

WORKDIR /app

ENV NODE_ENV=production
ENV NUXT_TELEMETRY_DISABLED=1
ENV HOST=0.0.0.0
ENV PORT=3000

COPY --from=builder /workspace/src/tg/.output /app/.output

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
