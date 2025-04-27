# # Базовый образ с Node.js
# FROM node:18

# # Установка Chrome для Puppeteer
# RUN apt-get update && apt-get install -y \
#   wget \
#   gnupg \
#   ca-certificates \
#   && wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | apt-key add - \
#   && echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list \
#   && apt-get update \
#   && apt-get install -y google-chrome-stable \
#   && rm -rf /var/lib/apt/lists/*

# # Создание рабочей директории
# WORKDIR /app

# # Копирование package.json и установка зависимостей
# COPY /src/proxy/package.json .
# RUN npm install

# # Копирование исходного кода
# COPY /src/proxy/server.js .

# # Открытие порта
# EXPOSE 3000

# # Запуск приложения
# CMD ["npm", "start"]

# Базовый образ с Puppeteer и Chrome
FROM ghcr.io/puppeteer/puppeteer:latest

# Создание рабочей директории
WORKDIR /app

# Копирование package.json и установка зависимостей
COPY /src/proxy/package.json .
RUN npm install

# Копирование исходного кода
COPY /src/proxy/server.js .

# Открытие порта
EXPOSE 3000

# Запуск приложения
CMD ["npm", "start"]