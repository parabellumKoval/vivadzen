# NEW_PAGE — рецепт создания новой страницы

Краткий чек-лист, чтобы не терять детали при добавлении нового раздела.

---

## 1. Открыть ТЗ

Все ТЗ страниц лежат в `../vivadzen-design-tz/`:

- `04_STRANKA_HOMEPAGE.md` — главная (готова)
- `05_STRANKA_KATALOG_KATEGORIE.md` — каталог, категория, strain-hub, поиск
- `06_STRANKA_PRODUKT.md` — карточка товара (включая обязательные PML-блоки)
- `07_STRANKA_CHECKOUT_UCET.md` — корзина, чекаут, личный кабинет
- `08_STRANKA_TRUST_OBSAH_PODPORA.md` — Licence, COA, O nás, Prodejny, Podpora, Blog, Pruvodce

И SEO-блок в `../kratom-seo-tz/`:

- `03_TZ_KOMERCNI_STRANKY.md`
- `04_TZ_TRUST_LOKAL_TRANSAKCNI.md`
- `05_TZ_OBSAHOVY_HUB_PRUVODCE_WIKI.md`
- `06_TECHNICKE_SEO_IMPLEMENTACE.md`

---

## 2. Шаги

### 2.1 Маршрут

Добавить в [`routes/web.php`](../routes/web.php):

```php
Route::view('/kratom', 'pages.catalog')->name('catalog');
```

Если нужна динамика — контроллер:

```bash
php artisan make:controller CatalogController
```

И:

```php
Route::get('/kratom', [CatalogController::class, 'index'])->name('catalog');
```

### 2.2 Страница

Создать `resources/views/pages/{name}.blade.php`:

```blade
<x-layouts.app
    title="..."
    description="..."
>
    <x-sections.hero-catalog />
    <x-sections.filters />
    <x-sections.products-grid :products="$products" />

    @push('schema')
        {{-- JSON-LD, см. ТЗ Tech SEO --}}
    @endpush
</x-layouts.app>
```

> ВАЖНО: для JSON-LD в Blade — оборачиваем в `@verbatim … @endverbatim`,
> иначе `@context`/`@type` будут трактованы как Blade-директивы.

### 2.3 Секции

Если секция новая — создать в `resources/views/components/sections/{name}.blade.php`. Если переиспользуется существующая — просто подключить.

Стили секции — в `resources/scss/sections/_{name}.scss` (или дополнить `_sections.scss`, если блок крошечный) + подключить в [`app.scss`](../resources/scss/app.scss).

### 2.4 Компоненты

Любой повторяющийся блок (карточка, badge, плейсхолдер) — в `components/ui/`. Стили в `resources/scss/components/_{name}.scss`. Добавить в `app.scss`.

### 2.5 Картинки/ассеты

**Не вставляем картинки до их генерации.** Везде, где должен быть финальный ассет — ставим `<x-ui.placeholder>` с `hint`-путём:

```blade
<x-ui.placeholder
    shape="square"
    label="Product hero"
    hint="/assets/products/{slug}/hero-square.avif"
    icon="leaf"
/>
```

Когда ассет готов — кладём в `public/assets/...`, в компоненте заменяем placeholder на `<img>`/`<picture>`.

Naming — см. [`docs/ASSETS.md`](ASSETS.md) и `../vivadzen-design-tz/02_ASSETS_A_NANO_BANANA.md` §8 (asset registry).

### 2.6 SEO-блок

Минимальный набор для любой публичной страницы:

```blade
<x-layouts.app title="..." description="...">
    @push('head')
        <meta property="og:title" content="..." />
        <meta property="og:description" content="..." />
        <meta property="og:image" content="..." />
        <link rel="canonical" href="https://vivadzen.com/..." />
    @endpush

    @push('schema')
        {{-- JSON-LD соответствующего типа: Product, BreadcrumbList, Article, FAQPage, LocalBusiness --}}
    @endpush

    {{-- ... --}}
</x-layouts.app>
```

### 2.7 Добавить в styleguide (опционально)

Если появились новые компоненты — отразить их в [`resources/views/pages/styleguide.blade.php`](../resources/views/pages/styleguide.blade.php).

### 2.8 Документация

- Новый UI-компонент → запись в [`docs/COMPONENTS.md`](COMPONENTS.md)
- Новый цвет / breakpoint / spacing → запись в [`docs/STYLEGUIDE.md`](STYLEGUIDE.md)
- Новый ассет / категория ассетов → запись в [`docs/ASSETS.md`](ASSETS.md)

---

## 3. Проверка перед коммитом

```bash
npm run build              # должен пройти без warnings
php artisan view:clear     # на всякий
php artisan route:list     # ваш роут виден
```

Открыть страницу в браузере: `/styleguide`, `/{ваша страница}`. На странице должно быть:

- [ ] Header c правильным `:transparent`
- [ ] AgeStrip
- [ ] Footer с badges Akreditovaná / PML / 18+
- [ ] Все CTA с touch-target ≥ 44 px (Apple HIG)
- [ ] Все картинки либо реальные, либо `<x-ui.placeholder>`
- [ ] Тексты на чешском
- [ ] WCAG-контраст ≥ 4.5:1
- [ ] Heading-hierarchy: H1 единственный, H2/H3 в порядке
- [ ] JSON-LD schema (если применимо к типу страницы)

---

## 4. PML-комплаенс

Категорически нельзя:

- Обещания эффектов в маркетинговой копи или отзывах («поможет с тревогой», «снимает боль», «лучше антидепрессанта»)
- Скрывать 18+ предупреждение
- Скрывать ссылки на лицензию / лабораторию

На product page — обязательные warning-блоки копируются **дословно** из `06_STRANKA_PRODUKT.md` §7.
