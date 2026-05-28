# VIVADZEN — ДИЗАЙН ТЗ
## Файл 2/9 — Ассеты, Nano Banana, SVG, удаление фона

> Это **practical manual** по визуальному «сырью» для всего сайта. Что фотографируем, что генерируем AI, что берём из официальных press-kit, и **готовая библиотека промптов** под Nano Banana (Gemini 2.5 / 3.1 / 3 Pro Image).
>
> Файл парный к `01_DESIGN_SYSTEM.md` (там цвета/типографика, тут — то, чем эти токены наполняются).

---

## §1. ПРИНЦИПЫ — что генерируем AI, что нет

### 1.1 Никогда не генерируем AI

| Категория | Почему | Откуда брать |
|---|---|---|
| **Фотографии товаров** (packshot, hero пакета) | Юридически и для доверия PML — нужна реальная фотография реальной партии. AI-генерация = риск ввести покупателя в заблуждение | Студийная съёмка реальных пакетов 25/50 g. Минимум 4 ракурса + макро порошка |
| **Фото prodejny / интерьера** | LocalBusiness и trust требуют реальных фото магазинов | Профессиональная съёмка обеих кам. точек |
| **Логотип Vivadzen** | Бренд должен быть **векторным** (SVG/AI), а Nano Banana отдаёт PNG | Заказать дизайнеру в Figma → экспорт SVG + 1× PNG + 2× PNG |
| **Логотипы способов оплаты** (Visa, Mastercard, Apple Pay, Google Pay) | Лицензионные требования брендов — нужны официальные SVG | См. §6 этого файла — ссылки на brand kits |
| **Логотипы курьерских служб** (Zásilkovna, PPL, Česká pošta) | То же — брендбук обязателен | Запросить SVG у партнёра / скачать с press-section |
| **Сертификаты / штампы ISO / лицензии** | Это реальные документы | Сканы реальных документов, обработка в Photoshop |
| **Лица людей в отзывах** (если используем фото клиентов) | Согласие на использование = реальная подпись клиента | Лучше — инициалы + Lucide-аватар + цветной фон (см. файл 3 §16) |
| **Графики / таблицы данных COA** | Это данные, не картинки | Сверстать на странице из HTML/CSS (см. файл 3 §6) |

### 1.2 Генерируем AI (Nano Banana и аналоги)

| Категория | Цель | Промпты |
|---|---|---|
| **Hero photo** на главной | Атмосферная иллюстрация бренда — листья, ботаника, тёплое освещение | §3.1 |
| **Lifestyle photo** для CategoryGlyphCard | Иконография категорий — кружок 96×96 с символом цвета (зелёный, белый, красный, жёлтый лист) | §3.2 |
| **Декоративные ботанические паттерны** для фоновой текстуры hero | Лёгкий SVG-style паттерн с листьями | §3.3 (или Claude direct SVG) |
| **Hero для блог-статей** | Аутентичная фотография по теме поста | §3.4 |
| **Иллюстрации для гайдов** | Mood-фото для `/pruvodce/*` | §3.5 |
| **Фон страницы prodejny** (если нет фото) | Временная заглушка с атмосферой | §3.6 |
| **404 / Empty state / Maintenance** | Декоративная иллюстрация | §3.7 |
| **Newsletter / email header** | Атмосферный баннер | §3.8 |
| **OG-картинки** (open graph для соцсетей) | 1200×630 баннеры с заголовками | §3.9 — но с осторожностью, текст AI рендерит плохо до Pro версии |

### 1.3 SVG (векторные иконки и иллюстрации)

Nano Banana **не отдаёт SVG**. Это PNG-модель. Откуда брать векторы:

| Тип | Источник | Как использовать |
|---|---|---|
| **UI-иконки** (cart, search, user, X, chevron, check, alert) | **Lucide Icons** через `lucide-react` или CDN — это де-факто стандарт, подключается в Claude artifacts из коробки | `import { ShoppingCart } from "lucide-react"` |
| **Категорийные symbol-иконки** (лист зелёный/белый/красный для CategoryGlyphCard) | **Claude генерирует SVG напрямую** — просим в чате «дай SVG листа кратома, viewBox 96 96, fill currentColor, плоский, обведённый» | Просто скопировать SVG-код в проект |
| **Декоративные иллюстрации** (sun/moon, leaves, hands) | Claude direct SVG ИЛИ post-vectorization (VectoSolve, Vector Magic) | Если нужна сложная — рисуем PNG в Banana, потом векторизация |
| **Логотипы бренда** | Дизайнер в Figma → SVG | Никогда AI |
| **Логотипы платёжек** | Press-kits (см. §6) | Никогда AI |
| **Логотип партнёра-лаборатории** (VŠCHT) | Запросить у VŠCHT или скачать с их сайта | Реальный логотип |

**Правило большого пальца:** если в иконке нет текста и она простая — Lucide или Claude SVG. Если сложная композиция (несколько элементов, ботанические рисунки) — Nano Banana PNG + опц. векторизация.

### 1.4 Что нельзя путать

- **Nano Banana = PNG** (raster). Любой «svg-промпт» к Nano Banana → она проигнорирует и выдаст PNG.
- **Claude как модель = умеет SVG-код** (text-to-SVG). Спрашиваем у Claude напрямую (не у Nano Banana), и он генерирует разметку.
- **Background removal** ≠ AI-генерация. Это пост-обработка (см. §5).

---

## §2. СТРУКТУРА ПАПКИ АССЕТОВ

Единая папка `/vivadzen-assets/` с подкаталогами. Имена файлов в **kebab-case ASCII** (никаких пробелов, кириллицы, диакритики), чтобы избежать проблем в URL и Git.

```
vivadzen-assets/
├── brand/                          # Бренд-айдентика (от дизайнера)
│   ├── logo-vivadzen-primary.svg            # Основной (горизонталь, на светлом)
│   ├── logo-vivadzen-primary-dark.svg       # Для светлых фонов
│   ├── logo-vivadzen-primary-light.svg      # Для тёмных фонов (forest)
│   ├── logo-vivadzen-mark.svg               # Только знак (для фавикона/мобиль.шапки)
│   ├── logo-vivadzen-mark.png               # 512×512 PNG для соцсетей
│   ├── favicon-32.png
│   ├── favicon-16.png
│   ├── favicon.svg                          # SVG favicon (современные браузеры)
│   ├── apple-touch-icon-180.png
│   ├── og-default-1200x630.png              # Дефолтный Open Graph
│   ├── manifest-icon-192.png
│   └── manifest-icon-512.png
│
├── products/                       # Фото товаров (студийная съёмка)
│   ├── kratom-extrakt-zeleny-10ml/
│   │   ├── 01-front.jpg                     # Главное фото пакета лицом
│   │   ├── 02-back.jpg                      # Этикетка/состав
│   │   ├── 03-side.jpg                      # Сбоку (для 25g/50g сравнение)
│   │   ├── 04-macro.jpg                     # Макро жидкости/порошка
│   │   ├── 05-lifestyle.jpg                 # На столе / в руке
│   │   └── 06-batch.jpg                     # Печать ŠARŽE крупным планом
│   ├── zeleny-rurut-nano/
│   │   └── ...тот же набор
│   ├── zelena-sumatra/
│   ├── bila-maeng-da/
│   ├── bily-slon/
│   ├── zeleny-thajsky/
│   ├── cervena-maeng-da/
│   └── zelena-maeng-da/
│
├── payments/                       # Платёжные логотипы (из брендкитов)
│   ├── visa.svg
│   ├── visa-monochrome.svg
│   ├── mastercard.svg
│   ├── mastercard-monochrome.svg
│   ├── apple-pay.svg
│   ├── apple-pay-white.svg
│   ├── google-pay.svg
│   ├── google-pay-monochrome.svg
│   ├── comgate.svg                          # Если используем ComGate-гейт
│   └── qr-platba.svg                        # Логотип QR-платежи (cnb.cz)
│
├── delivery/                       # Логотипы курьеров
│   ├── zasilkovna.svg
│   ├── ppl.svg
│   ├── ceska-posta.svg
│   ├── dpd.svg                              # Если подключим
│   └── messenger-vivadzen.svg               # Свой курьер (отдельный знак)
│
├── trust/                          # Trust-аттрибуты
│   ├── badge-akreditovana-lab.svg           # Дизайн файл 3 §2.2 «AKREDITOVANÁ LABORATOŘ»
│   ├── badge-pml-licence.svg                # «AUTORIZOVANÝ PRODEJCE PML»
│   ├── badge-18-plus.svg                    # «18+» бейдж
│   ├── vscht-praha-logo.svg                 # От лаборатории
│   ├── iso-17025.svg                        # Стандартный логотип ISO
│   ├── mz-cr-logo.png                       # С официального сайта (если разрешено)
│   ├── coa-watermark.svg                    # Водяной знак для PDF
│   └── ssl-trust-seal.svg                   # SSL-печать (Sectigo/DigiCert от провайдера)
│
├── icons/                          # Кастомные SVG (то, чего нет в Lucide)
│   ├── leaf-green.svg                       # Лист зелёный для CategoryGlyphCard
│   ├── leaf-white.svg
│   ├── leaf-red.svg
│   ├── leaf-yellow.svg
│   ├── strain-maeng-da.svg
│   ├── strain-borneo.svg
│   ├── strain-thajsky.svg
│   ├── strain-sumatra.svg
│   ├── form-prasek.svg                      # Порошок
│   ├── form-extrakt.svg                     # Капли
│   ├── express-lightning.svg                # Молния для EXPRESS 180
│   └── lab-flask.svg                        # Колба для trust-секции
│
├── ai-generated/                   # Всё что сгенерировано Nano Banana
│   ├── hero/
│   │   ├── home-hero-leaves-warm-light-1600x1000.png
│   │   ├── home-hero-leaves-warm-light-800x1000-mobile.png
│   │   └── category-pillar-kratom-hero-1600x600.png
│   ├── lifestyle/
│   │   ├── category-green-circle-96.png     # Для CategoryGlyphCard
│   │   ├── category-white-circle-96.png
│   │   ├── category-red-circle-96.png
│   │   ├── category-yellow-circle-96.png
│   │   ├── category-extrakt-circle-96.png
│   │   ├── category-predplatne-circle-96.png
│   │   └── prodejny-praha-warm-1200x800.png
│   ├── blog-heroes/
│   │   ├── co-je-kratom-1600x900.png
│   │   ├── kratom-zakon-2026-1600x900.png
│   │   ├── jak-poznat-kvalitni-1600x900.png
│   │   ├── zeleny-vs-bily-vs-cerveny-1600x900.png
│   │   └── ...по слугам гайдов
│   ├── og-images/
│   │   ├── og-home-1200x630.png
│   │   └── og-category-green-1200x630.png
│   └── empty-states/
│       ├── empty-cart-1024.png
│       ├── empty-wishlist-1024.png
│       └── 404-1024.png
│
└── decorative/                     # Декор (паттерны, разделители)
    ├── botanical-pattern-light-1600.png     # Для светлых секций (opacity 4–6%)
    ├── botanical-pattern-dark-1600.png      # Для forest-фонов
    ├── divider-leaves.svg                   # Разделитель секций (от Claude)
    ├── grain-noise.png                      # Лёгкий film grain для hero (opacity 8–12%)
    └── ornament-flourish.svg                # Орнамент-завитки (от Claude)
```

### 2.1 Naming convention (строго)

- **Только латиница, цифры, дефис, точка.** Никаких пробелов, кириллицы, диакритики (`č`, `š`, `ž`).
- Пример: ✅ `zelena-maeng-da/01-front.jpg`, ❌ `Zelená Maeng Da/Фото 1.JPG`.
- Размер в имени для AI-генерированных: `home-hero-leaves-warm-light-1600x1000.png` (помогает быстро находить).
- Версии: `logo-vivadzen-primary-v2.svg` если меняли, **не перезаписываем v1**.
- Цветовые варианты: `-dark`, `-light`, `-mono` суффиксы.

### 2.2 Где это хранится

- **Источник правды:** Git LFS или облачное хранилище (Google Drive / Dropbox), структура выше.
- **Продакшн:** `/public/assets/` в Next.js проекте, тот же layout, **публичные URL** `/assets/products/zelena-maeng-da/01-front.jpg`.
- **Артефакты в claude.ai:** в `<img src="..." />` всегда **placeholder-paths** из этой структуры. Когда отдаём арт в продакшн — пути уже подходят, замена 1-в-1.

### 2.3 Размеры и форматы

| Тип | Master (source) | Web (deliver) | Note |
|---|---|---|---|
| Фото товара главное | TIFF / PNG, 3000×3750 px | JPG 1200×1500 @85% + WebP @80% + AVIF @70% | 4:5 ratio |
| Фото товара galleria | TIFF / PNG, 3000×3000 | JPG 1200×1200 + WebP/AVIF | 1:1 |
| Hero-фото | PSD/PNG 3200×2000 | JPG 1600×1000 + 2× WebP | + mobile 800×1000 vertical |
| Иконка категории | SVG (если возможно) | SVG, fallback PNG 192×192 | Lucide / Claude SVG |
| Платёжный лого | SVG из brand kit | SVG inline | Не растрировать |
| OG картинка | PNG 1200×630 | PNG 1200×630 | Не сжимать сильно — для соцсетей |
| Favicon | SVG + PNG | SVG + 16/32/180/192/512 PNG | Стандартный набор |

**Loader:** Next.js `<Image />` (auto WebP/AVIF + responsive `srcSet`). Для статических артефактов — `<picture><source>` руками.

---

## §3. БИБЛИОТЕКА ПРОМПТОВ К NANO BANANA

> Все промпты на **английском** (модель лучше понимает) + параметры. Запускаем через Gemini API (Google AI Studio / Vertex AI) с моделью `gemini-2.5-flash-image-preview` (Nano Banana), `gemini-3.1-flash-image` (Nano Banana 2) или `gemini-3-pro-image` (Nano Banana Pro — лучший рендер + распознаёт текст).
>
> Для production-качества: **Nano Banana Pro** (≈10× дороже, ≈$0.40/изображение, но 4K, легибельный текст, лучшая консистентность).

### 3.0 Style anchor (вставляем в каждый промпт)

```
STYLE: editorial natural photography, warm afternoon light, soft shadows,
muted earthy palette (forest green #1B3A2D, cream #F5EDD8, amber #F4A020),
shallow depth of field, 35mm film grain, premium organic apothecary aesthetic,
no synthetic shine, no oversaturation, no neon, no stock-photo look,
real organic textures, hint of dust motes in light, slight imperfection.
```

Этот блок — **универсальный bottom-glue** для visual consistency. Менять только если конкретный image требует — например, иконки в круге → flat illustration style, не editorial photo.

### 3.1 Home Hero — основной баннер

**Промпт:**
```
A close-up artistic photograph of fresh kratom leaves (Mitragyna speciosa,
broad oval green leaves with pronounced veins) resting on a worn wooden
apothecary table. Brass measuring spoon and a small linen pouch nearby.
Warm golden hour light streaming from upper-left window casting long
soft shadows. Background gradient from dark forest green to deep emerald,
softly blurred. Composition: rule of thirds with leaves on lower-left,
empty negative space on right for text overlay.

[+ STYLE anchor §3.0]

Aspect ratio: 16:10. Resolution: 1600x1000.
```

**Параметры API:**
- Model: `gemini-3-pro-image` (для hero — стоит лучшее качество)
- Aspect: `16:10` или `16:9`
- Output: 1600×1000 px
- Запросить 4 вариации, выбрать лучшую вручную

**Альтернативный промпт** (если хотим интерьер prodejny):
```
A warm interior shot of a small artisan apothecary shop in Prague's Old Town.
Wooden shelves lined with amber glass jars containing botanical powders.
Soft afternoon light filtering through a tall arched window. Single hanging
brass pendant lamp. Brick wall partially visible. No people. No signage with
text. Vintage wooden counter with a precision scale. Mood: trustworthy,
established, quietly premium.

[+ STYLE anchor]
Aspect 16:10. 1600x1000.
```

**Мобильная версия** — генерируем отдельно с aspect `4:5`, размер 800×1000:
```
[Тот же промпт] [+ STYLE]
Vertical composition. Leaves filling upper 60% of frame. Negative space at
bottom for text. Aspect ratio 4:5. Resolution 800x1000.
```

### 3.2 Category Glyphs — 6–7 иконок-круг

Каждый — отдельный промпт. Размер квадрат 512×512, потом обрезается в круг 96 CSS.

**Зелёный кратом:**
```
A single fresh kratom leaf, deep emerald green with golden vein,
photographed top-down on a soft cream-colored backdrop. Studio
soft-box lighting, slight cast shadow. Leaf positioned center,
slightly tilted 15 degrees. Crisp focus on veins.

[+ STYLE anchor § minus "shallow depth of field" — we want sharp]
Aspect ratio 1:1. Resolution 1024x1024. Background uniform cream
(#F5EDD8) — flat for easy cutout.
```

**Белый кратом:**
```
[Тот же шаблон] — но «leaf with pale silver-white veins and lighter green,
slightly more mature appearance, more rounded shape, white-vein variant».
```

**Красный кратом:**
```
[Тот же] — «mature kratom leaf with reddish-bronze veins, deeper olive
green, slightly weathered edges, hint of red on the central rib».
```

**Жёлтый кратом:**
```
[Тот же] — «kratom leaf with golden-yellow vein and edge tint, partially
sun-dried appearance, warm autumn tones, still organic and natural».
```

**Maeng Da (отдельная категория-штамм):**
```
A small artisan glass jar filled with fine kratom powder, golden-amber
afternoon light. Jar label is blank (no text). Lid slightly ajar, hint of
powder dust. Cream background. Composition centered.

[+ STYLE anchor]
1:1, 1024x1024. Background flat cream.
```

**Extrakt 10ml (для категории/товара extrakt):**
```
A small amber glass dropper bottle, 10ml size, with brass-toned dropper cap.
Soft cream-colored background. Center composition. Reflections natural but
subtle. No text on label. Dappled sun light from upper-right.

[+ STYLE]
1:1, 1024x1024.
```

**Předplatné (для подписки):**
```
A small linen pouch tied with twine, gently weathered, resting on a cream
linen surface. Beside it: a tiny brass key. Symbolizes recurring delivery
and care. Top-down view. Soft natural light.

[+ STYLE]
1:1, 1024x1024.
```

**Post-processing для всех category icons:**
1. Удаляем фон через remove.bg (см. §5).
2. Кропаем в круг через CSS (`clip-path: circle(50%)`).
3. Кладём на cream.200 фон (см. файл 1).

### 3.3 Декоративный botanical pattern

**Промпт:**
```
A subtle seamless pattern of small kratom leaves and botanical elements
(seeds, twigs, tiny flowers) scattered organically. Color: very pale
cream-on-cream contrast. Style: flat editorial illustration, line-drawn,
not photographic, very low contrast, suitable as background texture.
Density: sparse — at most 25% of canvas covered. Repeating tile-friendly.

NOT a tightly packed pattern. NO bright colors. NO text. NO logos.

Aspect 1:1. 1600x1600. Background flat cream #F5EDD8.
```

**Альтернатива через Claude (SVG):**
> Просим Claude напрямую:
> «Сгенерируй SVG seamless pattern с лёгкими ботаническими элементами (листья, веточки) для фона. viewBox 200×200. Все элементы — stroke `currentColor` opacity 0.08, без fill. Никаких прямых линий — только органические кривые. Должен тайлиться без шва.»
>
> Claude вернёт готовый SVG, который тайлим в CSS `background-repeat`. Это **легче и чище**, чем PNG из Nano Banana.

### 3.4 Hero для блог-статей `/pruvodce/*`

Универсальный шаблон **«editorial hero»** под каждую статью. Размер 1600×900 (16:9).

**Шаблон промпта:**
```
[topic-specific scene description]

[+ STYLE anchor]
Editorial blog hero composition: subject offset to left or right, generous
negative space for headline overlay. Aspect 16:9. 1600x900.
```

**Конкретные посты:**

`/pruvodce/co-je-kratom` (что такое кратом — pillar):
```
Botanical close-up of Mitragyna speciosa branches with multiple leaves,
showing the tropical Southeast Asian origin. Natural rainforest backdrop
softly blurred. Warm humid afternoon light.

[+ STYLE]
1600x900.
```

`/pruvodce/kratom-zakon-2026` (закон):
```
A traditional notary desk: weathered leather-bound book, antique brass scale
of justice in soft focus background, single sheet of cream parchment with
quill pen on top. Czech Old Town apartment interior, low autumn light.
Mood: serious, official, archival.

[+ STYLE]
1600x900.
```

`/pruvodce/jak-poznat-kvalitni-kratom` (как выбирать):
```
A scientist's hand (only hand visible, gloves) holding up a vial of fine
powder against soft window light, examining quality. Lab notebook visible
on table. Macro detail of texture. Clean, careful, expert mood.

[+ STYLE]
1600x900.
```

`/pruvodce/zeleny-vs-bily-vs-cerveny`:
```
Three small wooden bowls of kratom powder arranged in a triangle:
left bowl pale white-cream powder, middle deeper green-olive, right
reddish-brown. Top-down photo, warm wooden surface. Composition
slightly asymmetric for editorial feel.

[+ STYLE]
1600x900.
```

`/pruvodce/davkovani-kratomu` (дозировка — ОСТОРОЖНО с PML legal):
```
A precision digital scale displaying "3.0 g" of kratom powder on the pan.
Side view, soft light. Wooden table. No human hands. Clean, clinical,
educational tone.

[+ STYLE]
1600x900.
```
> ⚠️ **Legal:** в тексте статьи дозировка приводится **строго цитатой из §33e mandatory text** (см. файл 6 §7). Картинка — иллюстративная.

`/pruvodce/mitragynin-co-to-je` (мирагининин — фарма):
```
A microscope view of plant cellular structure, abstract organic shapes,
soft green and amber tones. Scientific yet beautiful. No text overlays.

[+ STYLE]
1600x900.
```

`/pruvodce/historie-kratomu` (история):
```
A vintage botanical illustration aesthetic: old parchment paper with
a hand-drawn kratom leaf and Latin "Mitragyna speciosa" label visible
but blurry/decorative. Aged paper texture. Coffee-stained corner.

[+ STYLE]
1600x900.
```

`/pruvodce/kratom-a-cesti-pestitele` (поставщики):
```
A wide shot of an organic tropical plantation at sunrise. Workers in
distance (silhouettes, no faces). Mist rising. Sustainable agriculture
mood. NOT industrial.

[+ STYLE]
1600x900.
```

### 3.5 Иллюстрации внутри гайдов (in-line)

Меньшего размера (800×600), 1–2 на статью.

**Шаблоны:**

«Сравнение трёх жилок» (для статьи о цветных категориях):
```
Three single kratom leaves arranged left-to-right on cream linen:
one with white-silver vein, one with golden-green, one with red-bronze.
Top-down, evenly lit. Educational composition.

[+ STYLE]
4:3, 800x600.
```

«Шаги заваривания» (для статьи о применении):
```
A wooden surface with kratom-related items: a small fabric tea pouch,
ceramic mug, kitchen scale, glass of water, all arranged as a step-by-step
flat lay. Soft side light.

[+ STYLE]
4:3, 800x600.
```

### 3.6 Заглушка фон prodejny (пока нет реальных фото)

```
Empty interior of a small artisan retail shop in Prague Vinohrady:
exposed brick wall, wooden floor, hanging Edison bulb light, simple
wooden display table. Late afternoon golden light through window.
No products visible, no signage. Atmospheric placeholder.

[+ STYLE]
3:2, 1500x1000.
```

> ⚠️ Это **временная** заглушка с пометкой «Ilustrativní foto — fotografujeme prodejnu». **Заменить на реальное фото** через 2–4 недели после открытия.

### 3.7 Empty states / 404 / Maintenance

**Empty cart:**
```
A single small empty wooden bowl on a cream linen napkin, top-down view.
Soft natural light. Minimal composition. No text overlays.

[+ STYLE — flat illustration style preferred over photography]
1:1, 1024x1024.
```

**Empty wishlist:**
```
A small empty wooden tray with a folded piece of cream paper next to it.
Soft natural light. Symbolizes "not yet chosen".

[+ STYLE]
1:1, 1024x1024.
```

**404:**
```
A weathered wooden door slightly ajar, glimpse of nothing behind it.
Atmospheric, slightly mysterious but not sinister. Cream walls visible.
Soft side light.

[+ STYLE]
1:1, 1024x1024.
```

### 3.8 Newsletter / Email header

```
A wide editorial composition: kratom branches gently spilling from a brass
vase, beside an open cream-colored envelope. Warm window light. Composition
strongly horizontal for email-letterbox header.

[+ STYLE]
3:1, 1500x500.
```

---

## §6. ЛОГОТИПЫ ПЛАТЁЖЕК — официальные источники

> Все нужны как **SVG** (или вектор-PDF) из официальных brand kits. Не растрировать руками.

### 6.1 Visa

- **Brand resources:** https://merchantsignageresources.visa.com — раздел "Acceptance Marks". 
- **Прямые скачивания:** SVG / EPS / AI / PNG для трёх вариантов:
  - Visa Blue Logo (на светлом)
  - Visa White Logo (на тёмном)
  - Visa Monochrome (моно для дискретного отображения)
- **Лицензия:** требует регистрации как мерчанту. Использование — только если карта Visa реально принимается.
- **Файл у нас:** `payments/visa.svg`, `payments/visa-monochrome.svg`

### 6.2 Mastercard

- **Brand center:** https://brand.mastercard.com/brandcenter.html — Acceptance Marks → Download.
- Доступны цвета: full color (красно-оранжевый Венн), monochrome (моно).
- **Лицензия:** через мерчант-аккаунт (acquiring bank).
- **Файл:** `payments/mastercard.svg`

### 6.3 Apple Pay

- **Marketing guidelines:** https://developer.apple.com/apple-pay/marketing/
- Доступны: Apple Pay logo (тёмный + светлый), button assets (для CTA).
- **Лицензия:** Apple Pay Identity Guidelines — строгие правила clear space, минимальный размер.
- **Особенность:** для CTA на checkout нужен **именно Apple-Pay Button** (не просто лого), сертифицированной формы.
- **Файлы:** `payments/apple-pay.svg`, `payments/apple-pay-white.svg`, + button asset.

### 6.4 Google Pay

- **Brand assets:** https://developers.google.com/pay/api/web/guides/brand-guidelines (Web) и аналогично Android.
- Доступны: Google Pay logo (color), monochrome, button.
- **Лицензия:** Google Pay API Brand Guidelines — минимальный размер 48 px, clear space.
- **Файлы:** `payments/google-pay.svg`, `payments/google-pay-monochrome.svg`

### 6.5 ComGate / GoPay (czech payment gateways)

- **ComGate:** https://www.comgate.cz/ke-stazeni — раздел «Loga ke stažení» (если выберем как гейт).
- **GoPay:** https://help.gopay.com/ → brand assets.
- Доступны: лого гейта + логотипы поддерживаемых способов (Apple/Google Pay, mojeplatba и т.д.) — удобный one-stop, если используем их виджет.

### 6.6 QR-platba (банковский QR в Чехии)

- **Стандарт:** ČBA / NBÚ.
- **Лого:** https://qr-platba.cz/pro-vyvojare/ — SVG логотип «QR platba» доступен.
- **Файл:** `payments/qr-platba.svg`

### 6.7 Курьерские службы (delivery)

| Партнёр | Источник лого | Лицензия |
|---|---|---|
| **Zásilkovna / Packeta** | https://www.zasilkovna.cz → нижний футтер → "Loga ke stažení" | Только для существующих клиентов |
| **PPL** | https://www.ppl.cz/Default.aspx?id=78 — нижний футтер → "Loga" | Free download |
| **Česká pošta** | https://www.ceskaposta.cz → media | По запросу |
| **DPD** | https://www.dpd.com/cz/cs/o-dpd/ke-stazeni | Brand guidelines + assets |

**Файлы:** `delivery/{name}.svg` — все SVG, **без модификаций** (резинка-правила) кроме изменения цвета на mono-вариант если разрешено.

---

## §7. SVG ОТ CLAUDE — практические промпты

> Когда нужен векторный graphic без Nano Banana — просим Claude напрямую. Claude хорошо генерирует SVG-разметку.

### 7.1 Cвет-кружки vein для product mini-card

> Уже в файле 3 (`03_GLOBALNI_KOMPONENTY.md` §3), просто инлайн-CSS, не SVG.

### 7.2 Botanical divider

**Промпт к Claude:**
> «Сгенерируй SVG-разделитель секций для веб-сайта. Дизайн: горизонтальная линия с маленьким декоративным элементом по центру — стилизованный лист (lineart, без заливки). Длина 240, высота 24, viewBox 240×24. Stroke `currentColor`, opacity 0.4. Чистый, элегантный, как в editorial-magazine.»

Claude вернёт что-то вроде:
```svg
<svg viewBox="0 0 240 24" xmlns="http://www.w3.org/2000/svg">
  <line x1="0" y1="12" x2="100" y2="12" stroke="currentColor" stroke-width="1" opacity="0.4"/>
  <path d="M105,12 Q115,4 120,12 Q125,20 135,12" fill="none" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  <line x1="140" y1="12" x2="240" y2="12" stroke="currentColor" stroke-width="1" opacity="0.4"/>
</svg>
```

### 7.3 Иконка «лист кратома» (для CategoryGlyphCard альтернатива photo)

**Промпт:**
> «SVG-иконка листа кратома. Плоский стиль, обводка, без заливки. viewBox 96×96, центрировано. Длинный овальный лист с центральной жилкой и 5–7 боковыми жилками. Stroke `currentColor` width 2, fill `currentColor` opacity 0.1. Тонкий, элегантный. Никаких градиентов.»

### 7.4 Иконка «лаборатория» для trust badge

**Промпт:**
> «SVG-иконка лабораторной колбы Erlenmeyer для trust-бейджа. viewBox 64×64. Колба + 3 пузырька жидкости. Outlined style. Stroke `currentColor` width 1.5, fill none, кроме капель — fill `currentColor`. Сбоку — мелкая отметка «ISO 17025» в SVG-тексте (текст 6pt, всё ещё читаемый).»

### 7.5 Бейдж «18+» в круге

**Промпт:**
> «SVG-бейдж «18+». Круг 64×64, обводка stroke `currentColor` width 2, fill none. Внутри — текст «18+» Inter Bold 28pt, fill `currentColor`. Должен работать в любом цвете через currentColor.»

### 7.6 Логотип VŠCHT / лаборатории (если разрешено и есть SVG)

Не генерируем AI — запрашиваем у партнёра официальный SVG. Если партнёр не даёт SVG, делаем сами в Illustrator с **их письменного разрешения**, или используем PNG их сайта (если они не дают SVG).

---

## §8. ОБЯЗАТЕЛЬНЫЙ ЧЕК-ЛИСТ ПЕРЕД ПУБЛИКАЦИЕЙ АССЕТА

- [ ] **Naming в kebab-case ASCII** — никаких диакритик в имени файла
- [ ] **Размер в имени** (для AI-генерированных) — например `home-hero-1600x1000.png`
- [ ] **Source PNG** сохранён в `/ai-generated/` без модификации
- [ ] **Cleaned версия** (cropped + bg removed + colorcorrected) — в `/products/` или соответствующей подпапке
- [ ] **WebP + AVIF** сгенерированы и положены рядом
- [ ] **Альтернативные размеры:**
  - desktop 1×, 2× (retina)
  - mobile (если разное от desktop)
- [ ] **Alt-текст придуман и записан** (в CMS или таблице asset registry — см. ниже)
- [ ] **EXIF почищен** (`exiftool -all= *.jpg`) — privacy + lighter file
- [ ] **Compression проверена** — JPG не > 200 KB для веба (большие герои — до 400 KB OK)
- [ ] **Visual review** — внутренний reviewer (вы сами + 1 человек) подтвердил «не выглядит AI», «не противоречит PML» (без обещаний эффектов, без минор)

### 8.1 Asset registry (рекомендация)

Простая Google Sheet или Notion-таблица:

| filename | location | aspect | size | alt-cs | alt-en | source | prompt-used (if AI) | approved-by | date |
|---|---|---|---|---|---|---|---|---|---|
| home-hero-leaves-warm-light-1600x1000.png | /ai-generated/hero/ | 16:10 | 240KB | Listy kratomu v teplém světle... | Kratom leaves in warm light | Nano Banana Pro | "A close-up artistic..." | LD | 2026-03-12 |
| products/zelena-maeng-da/01-front.jpg | /products/ | 4:5 | 180KB | Zelená Maeng Da — sáček 50 g | Green Maeng Da — 50g pack | Studio shoot | — | LD | 2026-03-15 |

→ источник правды «откуда что взялось», помогает при правках лет спустя.

---

## §9. ИНТЕГРАЦИЯ С CLAUDE (как использовать ассеты в артефактах)

### 9.1 Placeholder-стратегия в артефактах

Когда Claude генерирует артефакт прототипа страницы:
1. **Не вставляет реальные base64-изображения** — это раздувает артефакт + не масштабируется.
2. **Использует placeholder-paths** из этой структуры: `<img src="/assets/products/zelena-maeng-da/01-front.jpg" alt="..." />`.
3. В CSS — fallback `background-color: var(--color-cream-200)` чтобы при отсутствии файла было приемлемо.
4. **Если очень нужен визуал прямо в артефакте** — используем CSS gradient + SVG inline.

### 9.2 Когда нужен реальный визуал в артефакте-демо

Для презентации макета заказчику внутри артефакта — встраиваем PNG как base64 в `<img src="data:image/png;base64,...">`. **Только для 1–2 ключевых изображений**, иначе артефакт превышает лимит размера.

### 9.3 Промпт к Claude для генерации placeholder SVG

> «В этой секции должен быть hero-фото. Пока сделай **inline SVG placeholder**: viewBox 1600×1000, fill forest.700, в центре — outlined leaf shape opacity 0.15, надпись «Hero photo placeholder» Inter 14 muted. Размер контейнера — width 100%, height auto.»

Получаешь приемлемый placeholder, который потом меняется на реальное `<img>` в продакшн коде.

---

## §13. NEXT STEPS — что сделать после прочтения

1. **Создать папку** `vivadzen-assets/` с подкаталогами из §2.
2. **Заказать у дизайнера** логотип Vivadzen (SVG + варианты — см. §2).
3. **Запросить у VŠCHT** их официальный SVG-логотип (для COA-блока).
4. **Скачать** платёжные и курьерские SVG (см. §6).
5. **Записать** Google API key в `.env` для Gemini.
6. **Прогнать** prompts.json batch-скриптом (см. §4.3) — получить весь AI-pool за час.
7. **Снять студию** для товаров и prodejny — пока в процессе можно ставить AI-placeholders.
8. **Внести всё в asset registry** (§8.1).
9. **В каждом Claude-чате**, который касается дизайна страницы, прикладывать **шпаргалку из §10**.

> Дальше — см. файл `00_WORKFLOW.md` § «Поэтапная подача в Claude» для интеграции этого workflow с разработкой страниц.
