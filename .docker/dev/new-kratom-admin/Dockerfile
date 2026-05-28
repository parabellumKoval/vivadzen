# Nuxt 4 — админ-панель для new-kratom.
# В dev — hot reload, node_modules как named volume чтобы не залазить из host.

FROM node:22-alpine

WORKDIR /app

# Зависимости устанавливаются на старте (см. command в docker-compose),
# так как том монтируется поверх образа.

EXPOSE 3000

CMD ["sh", "-lc", "\
  [ -d node_modules ] && [ -e node_modules/.bin/nuxt ] || npm install; \
  npm run dev -- --host 0.0.0.0 --port 3000 \
"]
