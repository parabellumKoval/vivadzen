# ТЗ: Telegram Mini App — src/tg

## Общее описание

Nuxt 3 приложение в папке `src/tg` — упрощённая мобильная версия интернет-магазина для работы как **Telegram Mini App**.  
Это не отдельный проект, а альтернативный фронт, переиспользующий всю логику запросов, эндпоинты и composables из `src/front`.

---

## Принципы

- **Только мобайл.** Адаптация под десктоп не нужна. Целевая ширина: 320–480px.
- **Максимальная простота.** Никаких сложных фильтров, сортировок, сложных анимаций. Пользователь должен найти товар и оформить заказ в 3 клика.
- **Telegram-native UX.** Приложение открывается через Telegram WebApp SDK. Учитывать safe areas, цвет статусбара, нативную кнопку «Назад».
- **Переиспользование кода.** Все API-запросы, типы, утилиты берутся из `src/front`. Не дублировать логику.

---

## Структура директории

```
src/tg/
├── app.vue
├── nuxt.config.ts
├── pages/
│   ├── [region]/
│   │   └── [locale]/
│   │       ├── index.vue              # Главная / Каталог
│   │       ├── [category].vue         # Категория товаров
│   │       ├── product/
│   │       │   └── [slug].vue         # Страница товара
│   │       ├── checkout.vue           # Оформление заказа
│   │       ├── thank-you.vue          # Спасибо за заказ
│   │       ├── about.vue              # О нас
│   │       ├── account.vue            # Аккаунт / Настройки
│   │       └── orders.vue             # История заказов
├── components/
│   ├── layout/
│   ├── catalog/
│   ├── product/
│   ├── cart/
│   └── ui/
├── composables/          # только tg-специфичные
├── stores/               # Pinia, cart + user
├── assets/
│   └── tg.css            # глобальные стили
└── public/
```

---

## URL структура

Логика `/{region}/{locale}/...` сохраняется как в `src/front`.

| Страница | URL |
|---|---|
| Каталог (все товары / категория) | `/{region}/{locale}/` или `/{region}/{locale}/{category}` |
| Товар | `/{region}/{locale}/product/{slug}` |
| Оформление заказа | `/{region}/{locale}/checkout` |
| Спасибо | `/{region}/{locale}/thank-you` |
| О нас | `/{region}/{locale}/about` |
| Аккаунт | `/{region}/{locale}/account` |
| История заказов | `/{region}/{locale}/orders` |

Примеры боевых ссылок из BotFather:
- `tg.vivadzen.com/cz` → регион `cz`, редирект на дефолтный локаль
- `tg.vivadzen.com/ua` → регион `ua`

---

## Технологии

| | |
|---|---|
| Framework | Nuxt 3 |
| State | Pinia |
| Стили | CSS (нативные переменные) + Tailwind utility классы если используется в `src/front` |
| Telegram SDK | `@twa-dev/sdk` |
| Иконки | Тот же набор что в `src/front` |
| Шрифты | Тот же шрифт что в `src/front` |

---

## Цветовая палитра

```css
:root {
  --color-primary:    #73c56f;   /* зелёный — кнопки, акценты */
  --color-accent:     #ff8400;   /* оранжевый — цена, sale, CTA */
  --color-bg:         #fff7ed;   /* основной фон */
  --color-bg-card:    #ffecd4;   /* фон карточки, инпутов */
  --color-text:       #1a1a1a;
  --color-text-muted: #7a7a7a;
  --color-border:     #e8d5b7;
  --color-white:      #ffffff;
}
```

---

## Связанные документы

- [01_DESIGN_SYSTEM.md](./01_DESIGN_SYSTEM.md) — Дизайн-система, компоненты, типографика
- [02_PAGES.md](./02_PAGES.md) — Детальное описание каждой страницы (UI + UX)
- [03_COMPONENTS.md](./03_COMPONENTS.md) — Компоненты каталога, карточки, модалки
- [04_CART_AND_CHECKOUT.md](./04_CART_AND_CHECKOUT.md) — Корзина, модификации, оформление
- [05_API_AND_DATA.md](./05_API_AND_DATA.md) — Как переиспользовать данные из src/front
- [06_TELEGRAM_SDK.md](./06_TELEGRAM_SDK.md) — Интеграция Telegram WebApp SDK
