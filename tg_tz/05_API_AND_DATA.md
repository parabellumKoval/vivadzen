# 05 — API и данные: переиспользование из src/front

## Принцип

`src/tg` — это **не отдельное приложение с нуля**.  
Всё что касается запросов к API, типов, утилит — берётся из `src/front`.  
В `src/tg` создаётся только UI-слой.

---

## Что переиспользовать напрямую

### Composables / утилиты

| Из src/front | Использование в src/tg |
|---|---|
| `useProducts()` | Список товаров, пагинация |
| `useProduct(slug)` | Данные конкретного товара |
| `useCategories()` | Список категорий с количеством |
| `useDeliveryMethods()` | Способы доставки для checkout |
| `usePaymentMethods()` | Способы оплаты для checkout |
| `useCreateOrder()` | Отправка заказа |
| `useOrders()` | История заказов пользователя |
| `useRegion()` / `useLocale()` | Текущий регион и язык |
| `useI18n()` | Переводы |

Если composables находятся в `src/front/composables/` — вынести общую логику в `src/shared/` или настроить Nuxt aliases чтобы импортировать напрямую.

### Типы TypeScript

Все типы (`Product`, `ProductVariant`, `Category`, `Order`, `DeliveryMethod`, `PaymentMethod`) берутся из `src/front/types/`.

---

## Настройка импортов

В `src/tg/nuxt.config.ts`:

```ts
export default defineNuxtConfig({
  alias: {
    // Доступ к типам и утилитам из front
    '@front': '../front',
    '@shared': '../shared',   // если есть общая папка
  }
})
```

Или использовать `workspaces` в `package.json` монорепо.

---

## API эндпоинты

Те же самые эндпоинты что использует `src/front`.  
Базовый URL конфигурируется через `NUXT_PUBLIC_API_BASE` в `.env`.

```env
# src/tg/.env
NUXT_PUBLIC_API_BASE=https://api.vivadzen.com
```

---

## Структура запросов

### Список товаров (каталог)

```
GET /api/{region}/products?category={slug}&page={n}&per_page=12&locale={locale}
```

Параметры: такие же как в `src/front`.

Ответ используется для:
- Рендера карточек `TgProductCard`
- Кнопки «Загрузить ещё» (cursor/page-based пагинация — как в front)

### Товар по slug

```
GET /api/{region}/products/{slug}?locale={locale}
```

Ответ содержит: `name`, `description`, `images[]`, `price`, `original_price`, `variants[]`, `category`.

### Создание заказа

```
POST /api/{region}/orders
```

Body: тот же формат что отправляет `src/front`.

---

## Переключение языка

Логика роутинга `/{region}/{locale}/...` сохраняется.

При смене языка:

```ts
const switchLocale = (newLocale: string) => {
  const route = useRoute()
  navigateTo(
    route.path.replace(`/${currentLocale}`, `/${newLocale}`)
  )
}
```

Список доступных локалей для региона — из того же источника что `src/front`.

---

## Telegram initData — идентификация пользователя

Если бэкенд поддерживает авторизацию через Telegram WebApp:

```ts
// composables/useTgUser.ts
import WebApp from '@twa-dev/sdk'

export const useTgUser = () => {
  const user = WebApp.initDataUnsafe?.user

  const tgHeaders = {
    'X-Telegram-Init-Data': WebApp.initData
  }

  return { user, tgHeaders }
}
```

Передавать `tgHeaders` в запросы к API для идентификации пользователя и получения его истории заказов.

---

## Пагинация каталога

Кнопка «Загрузить ещё» (не бесконечный скролл):

```ts
const { products, fetchMore, hasMore, loading } = useProducts({
  category: categorySlug,
  region: region,
  locale: locale
})
```

При клике «Загрузить ещё» → `fetchMore()` → новые товары **добавляются** к существующим (не перезаписываются).

При смене категории → сброс страницы, новый запрос с нуля.

---

## Обработка ошибок

- Сетевая ошибка → toast внизу экрана (5 секунд)
- 404 товара → редирект на главную
- Ошибка при оформлении заказа → показать сообщение под кнопкой

**Toast компонент:**

```css
.tg-toast {
  position: fixed;
  bottom: calc(72px + 8px);  /* над Bottom Nav */
  left: 16px; right: 16px;
  background: #1a1a1a;
  color: white;
  padding: 12px 16px;
  border-radius: var(--radius-md);
  font-size: 14px;
  z-index: 300;
  text-align: center;
}
```
