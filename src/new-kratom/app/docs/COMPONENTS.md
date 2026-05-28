# COMPONENTS — каталог Blade-компонентов

Все компоненты — **анонимные** Blade-компоненты (без отдельного PHP-класса), лежат в `resources/views/components/`. Имя компонента = имя файла, namespace = подкаталог.

| Файл | Использование |
|------|---------------|
| `components/ui/button.blade.php` | `<x-ui.button>` |
| `components/global/header.blade.php` | `<x-global.header>` |
| `components/sections/hero.blade.php` | `<x-sections.hero>` |
| `components/layouts/app.blade.php` | `<x-layouts.app>` |

Стили — `resources/scss/components/_*.scss`, JS — `resources/js/components/*.js` (регистрируются как `Alpine.data('header', …)` в `app.js`).

---

## UI-компоненты — `<x-ui.*>`

### `<x-ui.button>` — кнопка

| Prop | Тип | Default | Описание |
|------|-----|---------|----------|
| `variant` | `primary \| secondary \| outline-light \| solid-dark \| grass \| terracotta \| ghost \| text` | `primary` | Визуальный вариант. `secondary` рассчитан на тёмный фон. |
| `size` | `sm \| md \| lg` | `md` | Высота: 36 / 44 / 52 px. |
| `href` | string | null | Если задан → тег `<a>`, иначе `<button>`. |
| `type` | string | `button` | type для `<button>`. |
| `block` | bool | false | width: 100%. |
| `icon` | string | null | Имя иконки из `<x-ui.icon>`. |
| `iconPosition` | `left \| right` | `right` | Сторона иконки. |
| `disabled` | bool | false | Через `disabled` HTML-атрибут. |

```blade
<x-ui.button href="/kratom" variant="primary" size="lg" icon="arrow-right">
    Prohlédnout kratom
</x-ui.button>
```

### `<x-ui.badge>` — статусный бейдж

`variant`: `age` (18+), `lab`, `licence`, `sale`, `out`, `subscription`, `express`, `tag`, `tag-amber`, `tag-terra`.

```blade
<x-ui.badge variant="lab" icon="flask">Akreditovaná laboratoř</x-ui.badge>
<x-ui.badge variant="express">EXPRESS 180</x-ui.badge>
```

### `<x-ui.icon>` — SVG-иконка

Inline-SVG, color через `currentColor`. Добавление новой иконки — расширить массив `$icons` в `icon.blade.php`.

Текущий набор: `arrow-right`, `arrow-left`, `chevron-down`, `chevron-right`, `search`, `shopping-bag`, `user`, `menu`, `x`, `shield-check`, `flask`, `store`, `zap`, `star`, `leaf`, `mail`, `map-pin`, `truck`, `badge-check`, `sparkles`.

```blade
<x-ui.icon name="leaf" :size="32" />
```

### `<x-ui.input>` — поле формы

| Prop | Default | Описание |
|------|---------|----------|
| `label` | null | Лейбл над инпутом. |
| `helper` | null | Подсказка под инпутом. |
| `error` | null | Сообщение об ошибке (заменяет helper). |
| `required` | false | Показывает `*` в лейбле. |
| `onDark` | false | Тема для тёмного фона. |
| `pill` | false | Закруглить до pill. |
| `labelOnDark` | false | Светлый цвет лейбла. |

Атрибут `name` автоматически становится `id` (если `id` не задан явно). Принимает любые HTML-атрибуты через `$attributes` (например `x-model`).

```blade
<x-ui.input
    type="email"
    name="email"
    label="Email"
    placeholder="vy@example.cz"
    required
    x-model="email"
/>
```

### `<x-ui.placeholder>` — заглушка ассета

Используется везде, где **позже** будет картинка/SVG.

| Prop | Описание |
|------|----------|
| `variant` | `dark` или null |
| `shape` | `square \| portrait \| wide \| hero` |
| `label` | Подпись для скрин-ридера и UI |
| `hint` | Ожидаемый путь к финальному ассету (`/assets/...avif`) |
| `icon` | Имя иконки в центре |

```blade
<x-ui.placeholder
    variant="dark"
    shape="hero"
    icon="leaf"
    label="Hero image"
    hint="/assets/ai-generated/hero/home-hero.avif"
/>
```

### `<x-ui.product-card>` — мини-карточка товара

| Prop | Описание |
|------|----------|
| `name` | Название продукта |
| `strainLabel` | Eyebrow («Zelená žilka · Borneo») |
| `vein` | `red \| green \| white \| yellow \| mix` — цветной кружок |
| `mitragynin` | Текст под названием («1,42 % · jemně mletý») |
| `price25`, `price50` | Цены за 25 и 50 г. Если оба заданы — показывает toggle. |
| `rating`, `reviewsCount` | Рейтинг + кол-во |
| `badge` | `['variant' => 'sale', 'label' => '−10 %']` |
| `href` | Ссылка на страницу товара |
| `image`, `imageLabel` | Картинка (если null — placeholder) |

Реактивная часть: Alpine `productCard()` — переключение размера + расчёт `pricePerGram`.

### `<x-ui.category-card>` — карточка категории

| Prop | Описание |
|------|----------|
| `title` | Название |
| `subtitle` | Подпись |
| `href` | Ссылка |
| `glyph` | `green \| white \| red \| yellow \| amber \| terra \| forest` — цвет круга |
| `icon` | Имя `<x-ui.icon>` внутри круга (как плейсхолдер до Nano Banana) |

### `<x-ui.review-card>` — карточка отзыва (dark)

`quote`, `name`, `date`, `chip`, `rating` (по умолчанию 5).

### `<x-ui.accordion>` — FAQ

Принимает `items = [['question' => '...', 'answer' => '<p>HTML</p>'], …]`. Использует нативный `<details>/<summary>` — текст ответа всегда в DOM (важно для SEO).

### `<x-ui.section-head>` — заголовок секции

`eyebrow`, `eyebrowVariant` (null/`soft`/`grass`), `title`, `titleTag` (h1..h6), `titleClass`, `lead`, `center`.

---

## Глобальные компоненты — `<x-global.*>`

### `<x-global.header>`

Sticky-header с лого, навигацией, поиском, корзиной и mobile-drawer.

| Prop | Описание |
|------|----------|
| `transparent` | На главной (hero — forest) header стартует прозрачным; на остальных — `header--solid`. |

Alpine: `header` (см. [`resources/js/components/header.js`](../resources/js/components/header.js)) — `scrolled` (>80px), `drawerOpen`.

### `<x-global.footer>`

4-колоночный, с обязательными бейджами Akreditovaná lab. + PML + 18+, newsletter-формой и платёжными лого (заглушки).

### `<x-global.age-strip>`

Тонкая полоса под header'ом: 18+ · PML · LAB. Не sticky, не закрываемая.

### `<x-global.announcement-bar>`

Закрываемая amber-полоса с EXPRESS-сообщением. Помнит закрытие на 7 дней через `localStorage`.

| Prop | Default | Описание |
|------|---------|----------|
| `message` | `EXPRESS doručení do 180 minut v Praze a Ostravě` | Текст |
| `link` | `/doruceni` | Куда вести |
| `linkLabel` | `Podrobnosti` | Лейбл ссылки |

---

## Секции главной — `<x-sections.*>`

Каждая секция — _законченный_ блок главной. Если копирующий ТЗ значения нужно менять — менять только тут.

| Компонент | Файл TZ |
|-----------|---------|
| `<x-sections.hero>` | `04_HOMEPAGE.md §3` |
| `<x-sections.trust-bar>` | §6 |
| `<x-sections.categories>` | §7 |
| `<x-sections.bestsellers>` | §8 |
| `<x-sections.why-vivadzen>` | §9 |
| `<x-sections.google-reviews>` | §10 |
| `<x-sections.trusted>` | §11 |
| `<x-sections.content-hub>` | §12 |
| `<x-sections.faq>` | §13 (+ `FAQPage` JSON-LD `@push('schema')`) |
| `<x-sections.newsletter>` | §14 |

Данные (тексты, продукты, отзывы) пока **захардкожены** в каждой секции в массивах `@php $items = [...] @endphp` сверху. Перед интеграцией с БД эти массивы заменяются на Eloquent-результаты.

---

## Layout — `<x-layouts.app>`

| Prop | Default | Описание |
|------|---------|----------|
| `title` | дефолт главной | `<title>` |
| `description` | дефолт главной | `meta description` |
| `transparentHeader` | false | Прозрачный старт header (только главная) |
| `announcement` | true | Показать AnnouncementBar |

Slots:
- основной — `{{ $slot }}` (содержимое страницы)
- стэк `@push('schema')` — для JSON-LD скриптов (выводится в конце `<body>`)
- стэк `@push('head')` — для дополнительных meta/preload в `<head>`

```blade
<x-layouts.app :transparentHeader="true" title="...">
    <x-sections.hero />
    {{-- ... --}}

    @push('schema')
        <script type="application/ld+json">...</script>
    @endpush
</x-layouts.app>
```

> **JSON-LD трюк:** Blade трактует `@context`, `@type` и т.п. как директивы.
> Способы экранирования:
> - `@verbatim` … `@endverbatim` — оборачивая весь блок
> - `"{{ '@' }}context": "..."` — точечно
> - `@@context` — выводит литерал `@context`

---

## Принципы

1. **Один компонент — одна ответственность.** Если в секции появляется логика более чем 60 строк — выносим в `ui/*` компонент.
2. **Не дублировать стили.** Если 2+ компонента используют одинаковый паттерн — токенизировать (CSS-переменная или утилитарный класс).
3. **Alpine — только там, где он реально нужен.** Header (scroll-state, drawer), AnnouncementBar (close-state), ProductCard (size-toggle), NewsletterForm (validate). Всё остальное — нативный HTML/CSS.
4. **PML-комплаенс на уровне компонента.** Бейджи `lab`/`licence`/`age` — не убирать из footer. ReviewCard — только сервисные отзывы, без эффект-claims.
