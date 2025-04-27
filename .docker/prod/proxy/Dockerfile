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