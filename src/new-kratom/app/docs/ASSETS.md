# ASSETS — структура и naming

> Источник: `../vivadzen-design-tz/02_ASSETS_A_NANO_BANANA.md`. Здесь — практическая выжимка.

---

## Структура каталога

```
public/assets/
├─ brand/                           # лого, фавикон, OG
│   ├─ logo-vivadzen-primary.svg
│   ├─ logo-vivadzen-mark.svg
│   └─ favicon.svg
│
├─ decorative/                      # фоны, паттерны
│   ├─ botanical-pattern.svg        # ✅ создан
│   └─ ...
│
├─ icons/
│   └─ category/                    # 96×96 PNG / SVG глифы категорий (Nano Banana)
│       ├─ category-green-circle-96.png       (плейсхолдер → glyph-placeholder--green)
│       ├─ category-white-circle-96.png
│       ├─ category-red-circle-96.png
│       ├─ category-yellow-circle-96.png
│       ├─ category-maeng-da-circle-96.png
│       ├─ category-extrakt-circle-96.png
│       └─ category-predplatne-circle-96.png
│
├─ ai-generated/
│   ├─ hero/
│   │   └─ home-hero-leaves-warm-light-1600x1000.avif
│   ├─ guides/
│   │   ├─ cs-co-je-kratom-1200x800.avif
│   │   ├─ cs-legislativa-1200x800.avif
│   │   └─ cs-kvalita-coa-1200x800.avif
│   └─ og-images/
│       └─ og-home-1200x630.png
│
├─ products/                        # студийная съёмка (1:1, 4:5)
│   └─ {slug}/
│       ├─ {slug}-square.avif
│       ├─ {slug}-square.webp
│       ├─ {slug}-portrait.avif
│       └─ ...
│
└─ payments/                        # SVG-лого платёжных систем
    ├─ visa.svg
    ├─ mastercard.svg
    ├─ apple-pay.svg
    ├─ google-pay.svg
    └─ qr-platba.svg
```

---

## Naming-конвенция

```
{slot}-{name}-{descriptor}-{w}x{h}.{ext}
```

Где:
- `slot` — где используется (`hero`, `category`, `og`, `guide`, `product`).
- `name` — slug продукта или темы (`zelena-sumatra`, `co-je-kratom`).
- `descriptor` — короткое описание сцены или роль (`warm-light`, `square`, `circle`).
- `w` × `h` — размер в px (для hero/og/guide).
- `ext` — `avif` основной, `webp` фолбэк, `png` только когда нужна прозрачность с альфа-каналом (лого, иконки).

Примеры:

```
home-hero-leaves-warm-light-1600x1000.avif
category-zeleny-glyph-96x96.png
product-cervena-maeng-da-square-1200x1200.avif
og-home-1200x630.png
guide-cs-legislativa-1200x800.avif
```

---

## Стратегия `<picture>`

Для пользовательского контента (фото, hero, гайды):

```html
<picture>
    <source type="image/avif" srcset="/assets/products/{slug}/{slug}-square.avif" />
    <source type="image/webp" srcset="/assets/products/{slug}/{slug}-square.webp" />
    <img src="/assets/products/{slug}/{slug}-square.jpg"
         alt="..."
         width="1200" height="1200"
         loading="lazy" />
</picture>
```

Для критичных hero — `loading="eager"` + preload в `<head>` (`@push('head')`).

---

## Плейсхолдеры в коде

Пока ассет не сгенерирован, в шаблоне ставим `<x-ui.placeholder>` с подсказкой пути:

```blade
<x-ui.placeholder
    shape="square"
    label="Product photo 1:1"
    hint="/assets/products/cervena-maeng-da/square.avif"
    icon="leaf"
/>
```

Когда ассет готов:
1. Положить файл по пути из `hint`.
2. Заменить `<x-ui.placeholder>` на `<picture>` (или `<img>` для SVG).
3. Удалить TODO-комментарий в Blade-файле.

---

## Где сейчас используются placeholder'ы

| Файл | Что заменить | Целевой путь |
|------|--------------|--------------|
| [sections/hero.blade.php](../resources/views/components/sections/hero.blade.php) | Hero фото | `/assets/ai-generated/hero/home-hero-leaves-warm-light.avif` |
| [sections/content-hub.blade.php](../resources/views/components/sections/content-hub.blade.php) | 3 hero для гайдов | `/assets/ai-generated/guides/cs-{topic}-1200x800.avif` |
| [ui/category-card.blade.php](../resources/views/components/ui/category-card.blade.php) | Глифы категорий | `/assets/icons/category/category-{slug}-circle-96.png` |
| [ui/product-card.blade.php](../resources/views/components/ui/product-card.blade.php) | Фото продукта | `/assets/products/{slug}/{slug}-square.avif` |

---

## Lightweight ассеты, которые лучше делать SVG inline

- Все Lucide-style иконки → в `<x-ui.icon>` (один файл `components/ui/icon.blade.php`).
- Vein dots — чистый CSS (`.vein-dot--red`).
- Payment logos в footer — пока цветные текст-чипы. Когда придут SVG: положить в `public/assets/payments/` и поменять компонент `global/footer.blade.php`.
- Декоративные ботанические паттерны — SVG с `currentColor`, чтобы наследовать цвет от родителя.

---

## Размеры/форматы по слотам

| Слот | Aspect | Размер | Формат |
|------|--------|--------|--------|
| Hero | 4:5 portrait | 1280×1600 | AVIF + WebP |
| Hero mobile bg | 9:16 | 1080×1920 | AVIF |
| Product card | 1:1 | 1200×1200 | AVIF + WebP |
| Product gallery | 4:5 | 1600×2000 | AVIF |
| Category glyph | 1:1 | 96×96 (2x: 192×192) | PNG (alpha) |
| Guide hero | 3:2 | 1200×800 | AVIF + WebP |
| OG-image | 1.91:1 | 1200×630 | PNG |
| Favicon | square | 32, 192, 512 | SVG primary + PNG fallback |
