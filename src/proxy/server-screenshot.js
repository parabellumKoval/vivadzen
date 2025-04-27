const express = require('express');
const puppeteer = require('puppeteer');
const app = express();
const port = 3000;

// Парсинг JSON в теле запроса
app.use(express.json());

// Эндпоинт для получения содержимого
app.post('/fetch', async (req, res) => {
  const { url } = req.body;

  if (!url) {
    return res.status(400).json({ error: 'URL is required' });
  }

  try {
    // Запуск браузера
    const browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });
    const page = await browser.newPage();

    // Настройка User-Agent для эмуляции реального браузера
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    );

    // Переход на страницу
    await page.goto(url, { waitUntil: 'networkidle2' });

    // Проверка, является ли URL прямой ссылкой на изображение
    const contentType = await page.evaluate(() => document.contentType);
    let response;

    if (contentType.startsWith('image/')) {
      // Если это изображение, получаем его содержимое
      response = await page.screenshot({ encoding: 'binary', type: 'jpeg' });
      res.set('Content-Type', 'image/jpeg');
      res.send(response);
    } else {
      // Если это страница, ищем первое изображение
      const imgSrc = await page.evaluate(() => {
        const img = document.querySelector('img');
        return img ? img.src : null;
      });

      if (!imgSrc) {
        await browser.close();
        return res.status(404).json({ error: 'No image found on the page' });
      }

      // Загружаем изображение
      const imgPage = await browser.newPage();
      await imgPage.goto(imgSrc, { waitUntil: 'networkidle2' });
      response = await imgPage.screenshot({ encoding: 'binary', type: 'jpeg' });
      res.set('Content-Type', 'image/jpeg');
      res.send(response);
      await imgPage.close();
    }

    // Закрытие браузера
    await browser.close();
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: 'Failed to fetch content: ' + error.message });
  }
});

// Запуск сервера
app.listen(port, () => {
  console.log(`Proxy server running on http://localhost:${port}`);
});