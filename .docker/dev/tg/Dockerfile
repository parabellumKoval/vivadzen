FROM node:20-alpine

WORKDIR /workspace/src/tg

ENV NUXT_TELEMETRY_DISABLED=1

COPY ./src/tg/package*.json ./

RUN npm ci

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "3000"]
