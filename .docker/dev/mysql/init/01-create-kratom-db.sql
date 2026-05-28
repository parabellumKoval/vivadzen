-- Создание изолированной БД для new-kratom (продакшн-сайт + админка)
-- Выполняется автоматически при первом запуске mysql-контейнера
-- (docker-entrypoint-initdb.d). Безопасно: не трогает существующие БД.

CREATE DATABASE IF NOT EXISTS `kratom`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'kratom'@'%' IDENTIFIED BY 'kratom_secret';
GRANT ALL PRIVILEGES ON `kratom`.* TO 'kratom'@'%';

FLUSH PRIVILEGES;
