const express = require('express');
const puppeteer = require('puppeteer');
const axios = require('axios');
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

  let browser;
  try {
    // Запуск браузера
    browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });
    const page = await browser.newPage();

    // Настройка User-Agent для эмуляции реального браузера
    const userAgent =
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    await page.setUserAgent(userAgent);

    // Переход на страницу
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });

    // Получение cookies
    const cookies = await page.cookies();
    const cookieHeader = cookies.map(c => `${c.name}=${c.value}`).join('; ');

    // Проверка, является ли URL прямой ссылкой на изображение
    const contentType = await page.evaluate(() => document.contentType);
    let imageUrl = url;

    if (!contentType.startsWith('image/')) {
      // Если это страница, ищем первое изображение
      imageUrl = await page.evaluate(() => {
        const img = document.querySelector('img');
        return img ? img.src : null;
      });

      if (!imageUrl) {
        await browser.close();
        return res.status(404).json({ error: 'No image found on the page' });
      }
    }

    // Попытка загрузки изображения через axios
    try {
      const response = await axios.get(imageUrl, {
        responseType: 'arraybuffer', // Получаем бинарные данные
        headers: {
          'User-Agent': userAgent,
          Accept: 'image/jpeg,image/png,image/webp,*/*',
          Referer: url,
          Cookie: cookieHeader, // Передаем cookies от Puppeteer
        },
        timeout: 30000,
      });

      // Установка правильного Content-Type
      const contentTypeHeader = response.headers['content-type'];
      if (!contentTypeHeader.startsWith('image/')) {
        await browser.close();
        return res.status(400).json({ error: 'URL does not point to an image' });
      }

      res.set('Content-Type', contentTypeHeader);
      res.send(response.data);
    } catch (axiosError) {
      console.error('Axios error:', axiosError.message);
      if (axiosError.response && axiosError.response.status === 403) {
        // Запасной вариант: загрузка через fetch в Puppeteer
        const imgResponse = await page.evaluate(async (url, userAgent) => {
          const response = await fetch(url, {
            headers: {
              'User-Agent': userAgent,
              Accept: 'image/jpeg,image/png,image/webp,*/*',
            },
          });
          if (!response.ok) throw new Error(`Fetch failed with status ${response.status}`);
          const buffer = await response.arrayBuffer();
          return Array.from(new Uint8Array(buffer));
        }, imageUrl, userAgent);

        const contentTypeHeader = imageUrl.endsWith('.png') ? 'image/png' : 'image/jpeg';
        res.set('Content-Type', contentTypeHeader);
        res.send(Buffer.from(imgResponse));
      } else {
        throw axiosError;
      }
    }

    // Закрытие браузера
    await browser.close();
  } catch (error) {
    console.error(error);
    if (browser) await browser.close();
    res.status(500).json({ error: 'Failed to fetch content: ' + error.message });
  }
});

// Запуск сервера
app.listen(port, () => {
  console.log(`Proxy server running on http://localhost:${port}`);
});