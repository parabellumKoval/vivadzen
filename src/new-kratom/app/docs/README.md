# Vivadzen — Frontend (Laravel 12 + Blade + SCSS + Alpine)

Базовый проект e-shop **Vivadzen** (кратом, ČR, PML-режим). На этом этапе реализованы:

- Дизайн-система (токены, типографика, базовые компоненты)
- UI-кит (Blade-компоненты + SCSS)
- Глобальные компоненты (Header, Footer, AgeStrip, AnnouncementBar)
- Главная страница (статика, согласно `04_STRANKA_HOMEPAGE.md`)
- Внутренний styleguide на `/styleguide`

> Все ТЗ хранятся в репозитории **рядом** с этим проектом — в каталоге
> `../vivadzen-design-tz/` и `../kratom-seo-tz/`. Эти файлы **не дублируются**
> в проект, на них только ссылаются документы из `docs/`.

---

## Быстрый старт

```bash
# 1. Зависимости
composer install
npm install

# 2. .env
cp .env.example .env       # уже создан create-project'ом
php artisan key:generate    # уже выполнено

# 3. Запуск
php artisan serve           # http://127.0.0.1:8000
npm run dev                 # vite dev server (HMR для SCSS + JS)
```

Альтернатива одной командой: `composer run dev` (запускает serve, queue, log, vite в concurrent).

### Билд продакшн-ассетов

```bash
npm run build
```

Артефакты ложатся в `public/build/`, манифест читается `@vite()` директивой.

---

## Ключевые точки входа

| Файл | Назначение |
|------|------------|
| [routes/web.php](../routes/web.php) | Все маршруты страниц |
| [resources/views/components/layouts/app.blade.php](../resources/views/components/layouts/app.blade.php) | Единый layout (header / footer / age-strip / announcement) |
| [resources/views/pages/home.blade.php](../resources/views/pages/home.blade.php) | Главная страница, собирается из секций |
| [resources/views/pages/styleguide.blade.php](../resources/views/pages/styleguide.blade.php) | Каталог UI-компонентов — `/styleguide` |
| [resources/scss/app.scss](../resources/scss/app.scss) | Entry SCSS (порядок импортов фиксированный) |
| [resources/scss/tokens/](../resources/scss/tokens) | Все design-tokens (цвета, типографика, spacing/motion/shadow) |
| [resources/js/app.js](../resources/js/app.js) | Alpine entry + регистрация Alpine.data |

---

## Стек

| Слой | Технология | Зачем |
|------|------------|-------|
| Backend | **Laravel 12** (PHP 8.4) | Стандарт для CZ-e-shop'ов, Blade для SSR, готовый роутинг/валидация под checkout |
| Templating | **Blade**, anonymous components | Прямое соответствие "компонент дизайна = компонент Blade", без VDOM-оверхеда |
| Стили | **Dart Sass 1.x**, без Tailwind | ТЗ построено на семантических токенах — SCSS лучше выражает их через `@use` и CSS-переменные |
| Интерактив | **Alpine.js 3.x** | Минимальный JS — toggle 25/50, accordion, drawer, form-валидация |
| Сборка | **Vite 7** через `laravel-vite-plugin` | HMR для Blade + SCSS |
| Иконки | Собственный inline-SVG компонент `<x-ui.icon>` | Lucide-style паттерн, без CDN |
| Шрифты | Google Fonts (Playfair Display + Inter) через `<link rel="stylesheet">` | preconnect в layout |

> **Tailwind не используется.** Он был удалён в пользу SCSS-токенов сразу
> после `create-project`. Если возникнет потребность в utility-классах —
> добавляйте свой малый набор в `resources/scss/base/_layout.scss` (там уже
> есть `.flex`, `.gap-*`, `.mt-*`, `.t-*` и др.).

---

## Структура каталогов

```
app/
├─ docs/                           # документация проекта
│   ├─ README.md                   # ← этот файл
│   ├─ COMPONENTS.md               # каталог Blade-компонентов
│   ├─ STYLEGUIDE.md               # design-tokens, как и когда применять
│   ├─ NEW_PAGE.md                 # рецепт: как добавить новую страницу
│   └─ ASSETS.md                   # placeholder-инвентарь и naming
│
├─ resources/
│   ├─ scss/
│   │   ├─ app.scss                # entry, фиксированный порядок импортов
│   │   ├─ tokens/                 # _colors / _typography / _spacing
│   │   ├─ base/                   # reset, типографика, layout утилиты
│   │   ├─ components/             # button, badge, input, card,
│   │   │                          # product-card, category-card, review-card,
│   │   │                          # accordion, placeholder, header, footer
│   │   ├─ sections/               # стили секций главной страницы
│   │   └─ utils/                  # _a11y и т.п.
│   │
│   ├─ js/
│   │   ├─ app.js                  # Alpine entry + регистрация data()
│   │   ├─ bootstrap.js            # axios
│   │   └─ components/             # header / announcement-bar / product-card / newsletter-form
│   │
│   └─ views/
│       ├─ pages/                  # home, styleguide, …
│       └─ components/
│           ├─ layouts/app.blade.php
│           ├─ ui/                 # button, badge, input, icon, placeholder,
│           │                      # product-card, category-card, review-card,
│           │                      # accordion, section-head
│           ├─ global/             # header, footer, age-strip, announcement-bar
│           └─ sections/           # hero, trust-bar, categories, bestsellers,
│                                  # why-vivadzen, google-reviews, trusted,
│                                  # content-hub, faq, newsletter
│
└─ public/
    └─ assets/                     # ассеты по структуре из 02_ASSETS_A_NANO_BANANA.md
        ├─ brand/                  # лого, фавикон
        ├─ decorative/             # botanical-pattern.svg и др.
        ├─ icons/category/         # PNG-глифы категорий (96×96)
        └─ placeholders/           # тестовые заглушки (если понадобятся)
```

---

## Чек-лист «перед коммитом»

- [ ] `npm run build` проходит без warnings
- [ ] `php artisan route:list` без ошибок, нет 500 на главной и `/styleguide`
- [ ] Новые HEX-значения вынесены в [tokens/_colors.scss](../resources/scss/tokens/_colors.scss)
- [ ] Новые компоненты добавлены в `STYLEGUIDE`-страницу
- [ ] Если был добавлен SCSS-файл — он подключён в [app.scss](../resources/scss/app.scss)
- [ ] Картинки добавлены в `public/assets/` или оставлен `<x-ui.placeholder>` с `hint`-путём
- [ ] Тексты на чешском, не на русском
- [ ] PML-комплаенс: на product/marketing не появилось «обещаний эффектов»

---

## Связанные документы

- [docs/COMPONENTS.md](COMPONENTS.md) — каталог Blade-компонентов, props и примеры
- [docs/STYLEGUIDE.md](STYLEGUIDE.md) — токены и принципы их применения
- [docs/NEW_PAGE.md](NEW_PAGE.md) — пошаговый рецепт создания новой страницы
- [docs/ASSETS.md](ASSETS.md) — инвентарь placeholder'ов и naming-конвенция

ТЗ-первоисточники (в соседних каталогах, не в этом проекте):

- `../vivadzen-design-tz/01_DESIGN_SYSTEM.md` — токены
- `../vivadzen-design-tz/03_GLOBALNI_KOMPONENTY.md` — глобальные компоненты
- `../vivadzen-design-tz/04_STRANKA_HOMEPAGE.md` — главная страница
- `../kratom-seo-tz/06_TECHNICKE_SEO_IMPLEMENTACE.md` — SEO / schema.org
