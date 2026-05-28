# STYLEGUIDE — Design tokens

Все визуальные значения проекта живут **только** в SCSS-токенах. Этот документ — карта, какой токен в какой ситуации применять. Если приходит запрос «сделай цвет X» — открываем этот файл первым.

> Первоисточник — `../vivadzen-design-tz/01_DESIGN_SYSTEM.md`. Этот файл — практическая выжимка для разработчика.

---

## 1. Куда и как подключать

```scss
// app.scss — единственный entry
@use 'tokens' as *;     // даёт доступ к Sass-переменным и mixins
                        // (CSS-переменные :root объявляются автоматически)
```

В компонентах используем **CSS-переменные** (а не `$forest-700` напрямую) — так темизация и runtime-tweaks остаются возможны:

```scss
// ✅ ХОРОШО
.btn--primary {
    background: var(--cta-primary-bg);
    color: var(--cta-primary-text);
}

// ❌ ПЛОХО — захардкоженный HEX
.btn--primary {
    background: #F4A020;
}

// ❌ ПЛОХО — Sass-переменная мимо CSS-слоя (теряем темизацию)
.btn--primary {
    background: $amber-500;
}
```

Исключение: `@mixin`, `@function`, breakpoint-расчёты — там Sass-переменные уместны.

---

## 2. Палитра

### Семантический слой (использовать в 95% случаев)

| Token | Назначение |
|-------|-----------|
| `--surface-primary` (`forest.700`) | Основной dark hero / section background |
| `--surface-elevated` (`forest.600`) | Карточка/elevation на тёмном |
| `--surface-muted` (`forest.800`) | Под формой/input на тёмном |
| `--surface-light` (`cream.100`) | Основной светлый фон страницы |
| `--surface-light-warm` (`cream.200`) | Тёплый акцент (карточка категории) |
| `--surface-light-hover` (`cream.300`) | Hover на светлой карточке |
| `--text-on-dark-primary` (`cream.100`) | Текст на тёмном |
| `--text-on-dark-secondary` | 75% непрозрачности — secondary |
| `--text-on-dark-accent` (`grass.500`) | Двух-цветный hero, акценты |
| `--text-on-light-primary` (`ink.900`) | Body на cream |
| `--text-on-light-secondary` (`ink.500`) | Muted на cream |
| `--text-on-light-accent` (`forest.700`) | H1/H2 на светлом |
| `--cta-primary-bg` (`amber.500`) | Главный CTA |
| `--cta-primary-bg-hover` (`amber.700`) | Hover главного CTA |
| `--cta-primary-text` (`ink.900`) | Контрастный тёмный текст на янтаре |

### Сырой слой (для редких кастомных кейсов)

`--c-forest-{900..100}`, `--c-cream-{50..400}`, `--c-grass-{700,500,300}`, `--c-amber-{700,500,300}`, `--c-terracotta-{700,500}`, `--c-ink-{900..100}`, `--c-danger`, `--c-warning`, `--c-success`, `--c-info`.

### Правила

- **grass.500** — _только_ как акцент (короткие фрагменты заголовка, иконки, hover). Не для длинного body.
- **terracotta.500** — _только_ для urgency / sale / 18+. Не использовать для primary CTA.
- **amber.500** — основной CTA. На тёмном фоне всегда сопровождается `--shadow-glow`.
- **Двух-цветный hero heading** (см. файл TZ §3.3): line 1 = `cream.100`, line 2 = `grass.500 italic`. См. класс `.t-two-tone` в `_typography.scss`.

### WCAG

Проверено в ТЗ §2.4. Ключевые комбо:
- cream.100 на forest.700 → 11.8:1 ✅
- ink.900 на amber.500 → 7.9:1 ✅
- grass.500 на forest.700 → 4.7:1 ✅ (large text only)
- ink.500 на cream.100 → 4.6:1 ✅

---

## 3. Типографика

### Шрифты

| Family | Веса | Когда |
|--------|------|-------|
| **Playfair Display** | 400, 700, 400i, 700i | Display и Heading XL/LG, emotional headings, hero |
| **Inter** | 400, 500, 600, 700 | Всё UI: nav, кнопки, body, FAQ, product card name |

> Подключаются через Google Fonts в [layout app.blade.php](../resources/views/components/layouts/app.blade.php) (`preconnect` + `display=swap`).

### Шкала (CSS-переменные)

| Token | Desktop | Mobile (≤768px) |
|-------|---------|----------|
| `--fs-display-xl` | 56 | 40 |
| `--fs-display-lg` | 44 | 32 |
| `--fs-display-md` | 36 | 28 |
| `--fs-heading-xl` | 32 | 26 |
| `--fs-heading-lg` | 26 | 22 |
| `--fs-heading-md` | 22 | 20 |
| `--fs-heading-sm` | 18 | 17 |
| `--fs-body-lg`    | 18 | 17 |
| `--fs-body-md`    | 16 | 15 |
| `--fs-body-sm`    | 14 | 13 |
| `--fs-caption`    | 12 | 12 |
| `--fs-overline`   | 11 | 11 |

### Утилитарные классы

Чтобы не дублировать описания шрифта в каждом компоненте, есть готовые `.t-*` классы в `resources/scss/base/_typography.scss`:

`.t-display-xl`, `.t-display-lg`, `.t-display-md`, `.t-heading-xl`, `.t-heading-lg`, `.t-heading-md`, `.t-heading-sm`, `.t-body-lg`, `.t-body-md`, `.t-body-sm`, `.t-caption`, `.t-overline`, `.t-metric-lg`, `.t-metric-sm`, `.t-two-tone` (+ `.t-two-tone__line-1`, `.t-two-tone__line-2`).

Плюс цветовые: `.t-on-dark`, `.t-on-dark-2`, `.t-on-dark-accent`, `.t-on-light`, `.t-on-light-2`, `.t-on-light-accent`, `.t-amber`, `.t-terra`.

### Правила

- Никаких **ВСЕ-ЗАГЛАВНЫХ** кроме `caption`/`overline`/button label — CZ-диакритика плохо читается в caps.
- `text-wrap: balance` на h1/h2/h3 (см. `_reset.scss`).
- Двух-цветный hero — фирменный приём, только на главной/pillar.

---

## 4. Сетка и отступы

### Контейнеры

- `.container` — `max-width: 1280`, padding фолбэк 16/24/40/64 px.
- `.container--wide` — 1440 (для hero).
- `.container--narrow` — 720 (для текста).

### Spacing (8-grid)

CSS-переменные: `--sp-1` (4) … `--sp-40` (160). Полный набор: `4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96, 120, 160`.

| Контекст | Desktop | Mobile |
|----------|---------|--------|
| Между секциями (`.section`) | 120 | 80 |
| Внутри секции между блоками | 24–32 | 16–24 |
| Внутри карточки | 20–24 | 16 |

Утилиты: `.gap-{2..16}`, `.mt-{2..16}`.

---

## 5. Радиусы / тени / motion

- **Радиусы:** `--r-xs` (4) → `--r-pill` (9999). CTA → `pill`. Карточки → `--r-lg`/`--r-xl`.
- **Тени:** `--shadow-{sm,md,lg,xl}`, `--shadow-glow` для amber-CTA, `--shadow-focus` для a11y.
- **Motion:** `--dur-fast` (120ms), `--dur-base` (200ms), `--dur-slow` (320ms). Easing: `--ease-standard`, `--ease-entrance`, `--ease-exit`.
- `prefers-reduced-motion` — глобально отключает анимации в `_reset.scss`.

---

## 6. Breakpoints

CSS: `--bp-sm` (480), `--bp-md` (768), `--bp-lg` (1024), `--bp-xl` (1280).

Sass mixin для @media:

```scss
@use '../tokens' as *;

.foo {
    padding: var(--sp-4);

    @include bp-up(md) {
        padding: var(--sp-8);
    }
}
```

---

## 7. Иконки

Inline-SVG через компонент `<x-ui.icon name="..." />`. Все иконки в `resources/views/components/ui/icon.blade.php`. Чтобы добавить новую — расширить массив `$icons`.

> **Не миксовать** filled + outline в одной секции — всегда 2px stroke outline.

---

## 8. PML-комплаенс при стилизации

- В копи: **никаких** обещаний эффектов («поможет с тревогой», «снимет боль»).
- На каждой странице: footer должен показывать `Akreditovaná lab.` + `Autorizovaný prodejce PML` + `18+`.
- AgeStrip — между header'ом и hero, не sticky.
- AnnouncementBar — closeable, помнит закрытие 7 дней (`localStorage`).

---

## 9. Чек-лист добавления нового цвета/токена

1. Открыть [tokens/_colors.scss](../resources/scss/tokens/_colors.scss).
2. Добавить как Sass-переменную **и** CSS custom property (`--c-…`).
3. Если есть семантика — добавить `--surface-…` / `--text-…` алиас.
4. Добавить swatch на `/styleguide`.
5. Если меняется WCAG-контраст — отметить в таблице ТЗ §2.4 и в этом файле в разделе 2.
