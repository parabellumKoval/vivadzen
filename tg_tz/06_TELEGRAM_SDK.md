# 06 — Telegram WebApp SDK

## Установка

```bash
npm install @twa-dev/sdk
```

---

## Инициализация

**Файл:** `plugins/telegram.client.ts`

```ts
import WebApp from '@twa-dev/sdk'

export default defineNuxtPlugin(() => {
  // Раскрыть на весь экран
  WebApp.expand()

  // Сообщить Telegram что приложение готово
  WebApp.ready()

  // Установить цвет хедера под цвет нашего TopBar
  WebApp.setHeaderColor('#ffffff')
  WebApp.setBackgroundColor('#fff7ed')

  return {
    provide: {
      tg: WebApp
    }
  }
})
```

---

## Кнопка «Назад» Telegram

Telegram предоставляет нативную кнопку «Назад» в хедере.  
**Не использовать** её как основную навигацию — это ненадёжно.  
Используем собственную кнопку «←» в `TgTopBar`.

Опционально: синхронизировать с Telegram BackButton:

```ts
// в TgTopBar.vue
const { $tg } = useNuxtApp()

onMounted(() => {
  if (props.showBack) {
    $tg.BackButton.show()
    $tg.BackButton.onClick(() => router.back())
  } else {
    $tg.BackButton.hide()
  }
})

onUnmounted(() => {
  $tg.BackButton.hide()
  $tg.BackButton.offClick()
})
```

---

## MainButton (нижняя кнопка Telegram)

Не использовать `WebApp.MainButton` — у нас собственная кнопка в UI.  
Это обеспечивает единый стиль и контроль над состоянием.

---

## HapticFeedback

Добавить тактильный отклик для ключевых действий:

```ts
const { $tg } = useNuxtApp()

// Добавление в корзину
$tg.HapticFeedback.impactOccurred('light')

// Успешное оформление заказа
$tg.HapticFeedback.notificationOccurred('success')

// Ошибка
$tg.HapticFeedback.notificationOccurred('error')

// Нажатие кнопки счётчика +/-
$tg.HapticFeedback.selectionChanged()
```

---

## Safe Area / Viewport

```ts
// В app.vue или plugin
WebApp.onEvent('viewportChanged', () => {
  document.documentElement.style.setProperty(
    '--tg-viewport-height',
    `${WebApp.viewportHeight}px`
  )
})
```

```css
/* В tg.css */
body {
  min-height: var(--tg-viewport-height, 100dvh);
}
```

---

## Данные пользователя Telegram

```ts
const user = WebApp.initDataUnsafe?.user
/*
{
  id: 123456789,
  first_name: "Иван",
  last_name: "Иванов",
  username: "ivan",
  language_code: "ru",
  photo_url: "https://..."
}
*/
```

Использовать для:
- Отображения имени и аватара на странице Аккаунт
- Передачи на бэкенд для идентификации заказов

**Не доверять на фронте** — верификация `initData` только на бэкенде через HMAC.

---

## Определение языка из Telegram

При первом запуске — автоматически применить язык из Telegram:

```ts
// middleware/tg-locale.ts
export default defineNuxtRouteMiddleware((to) => {
  const tgLang = WebApp.initDataUnsafe?.user?.language_code  // 'ru', 'cs', 'uk'
  
  // Маппинг Telegram language_code → наши локали
  const langMap: Record<string, string> = {
    'ru': 'ru',
    'uk': 'uk', 
    'cs': 'cs',
    'en': 'en'
  }

  // Применять только если локаль не задана явно в URL
  // Логика зависит от структуры роутинга
})
```

---

## Проверка среды

Не все функции SDK доступны вне Telegram. Добавить guard:

```ts
// composables/useTelegram.ts
export const useTelegram = () => {
  const isTelegram = typeof window !== 'undefined' && !!window.Telegram?.WebApp?.initData

  const haptic = (type: 'light' | 'medium' | 'success' | 'error') => {
    if (!isTelegram) return
    if (type === 'success' || type === 'error') {
      WebApp.HapticFeedback.notificationOccurred(type)
    } else {
      WebApp.HapticFeedback.impactOccurred(type)
    }
  }

  return { isTelegram, haptic }
}
```

---

## Конфигурация в BotFather

Каждый региональный бот → своя ссылка на Mini App:

```
/mybots → Выбрать бота CZ → Bot Settings → Menu Button
URL: https://tg.vivadzen.com/cz

/mybots → Выбрать бота UA → Bot Settings → Menu Button  
URL: https://tg.vivadzen.com/ua
```

Редиректы на дефолтный локаль настраиваются в Nuxt middleware или на уровне сервера (nginx/edge):
```
tg.vivadzen.com/cz → tg.vivadzen.com/cz/cs  (дефолтный язык для CZ)
tg.vivadzen.com/ua → tg.vivadzen.com/ua/uk  (дефолтный язык для UA)
```

---

## nuxt.config.ts — минимальная конфигурация

```ts
// src/tg/nuxt.config.ts
export default defineNuxtConfig({
  ssr: false,   // SPA режим для Telegram Mini App

  app: {
    head: {
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' }
      ],
      script: [
        // Telegram WebApp script (альтернатива npm пакету)
        // { src: 'https://telegram.org/js/telegram-web-app.js' }
      ]
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.vivadzen.com'
    }
  }
})
```

> `ssr: false` — важно для Telegram Mini App, так как `WebApp` доступен только в браузере.
