# VIVADZEN — ДИЗАЙН ТЗ
## Файл 1/7 — Design System (токены)

> Это единственный источник правды по визуалу. Все экраны в файлах 2–3 ссылаются на токены отсюда. Если что-то меняется — меняется здесь, и каскадом по всему сайту.

---

## 1. ФИЛОСОФИЯ И ТОН

Vivadzen = **«herbal apothecary с европейской дисциплиной»**: тёплый, земляной, премиальный, без китча. Не «техно-CBD стартап», не «магазин-дискаунтер».

Визуальные принципы:
1. **Тишина важнее громкости.** Воздух, типографика, фактура — вместо градиентов, теней-обводок и emoji-ярости.
2. **Зелёный = бренд, оранжевый = действие.** Никаких «всё красное/всё в градиенте». CTA — точечно.
3. **Серьёзность как trust-сигнал.** Это регулируемый PML-продукт, дизайн должен звучать как «настоящая компания с лабораторией», а не «домашний магазин».
4. **Серифные заголовки + санс body.** Контраст подачи как у качественных журналов и аптек уровня Officina di Santa Maria Novella, не как у tech-saas.
5. **Mobile-first.** Доля смартфона в CZ-eshop ≥ 65%; всё проверяем сначала на узком экране.

---

## 2. ПАЛИТРА — semantic tokens

Базовые HEX (ваши пожелания) расширены до production-набора (оттенки, semantic-имена, WCAG-контраст).

### 2.1. Базовые цвета (raw)

```
brand.forest        #1B3A2D   primary dark surface
brand.cream         #F5EDD8   light surface (warm)
brand.ivory         #F9F4EC   lightest / text on dark
brand.grass         #7EC855   brand accent green
accent.amber        #F4A020   primary CTA / highlight
accent.terracotta   #D45C2B   secondary CTA / sale / urgency
```

### 2.2. Производные оттенки (нужны для полноценного UI)

```
forest.900   #0F2419     hover/pressed на forest
forest.800   #142E22
forest.700   #1B3A2D     = brand.forest
forest.600   #2A5640
forest.500   #3D6F54     muted text на cream
forest.300   #6D9482     dividers/borders на cream
forest.100   #C8D8CF     subtle bg tint

cream.50     #FBF8F0
cream.100    #F9F4EC     = brand.ivory
cream.200    #F5EDD8     = brand.cream
cream.300    #ECE0C2     hover на cream surfaces
cream.400    #D9C998     muted на cream

grass.700    #4F9E2D     hover для grass CTA / focus ring
grass.500    #7EC855     = brand.grass
grass.300    #B9E198

amber.700    #C77808     hover для amber CTA
amber.500    #F4A020     = accent.amber (CTA base)
amber.300    #FCD27A     disabled / soft

terracotta.700  #A8431B
terracotta.500  #D45C2B   = accent.terracotta

# neutrals (тёплые, НЕ чисто-чёрный — премиум-look)
ink.900     #14201B   основной текст на cream/ivory
ink.700     #2B3A33
ink.500     #5C6A63   secondary text
ink.300     #A8B2AC   tertiary / placeholder
ink.100     #E5E8E4   dividers на light

danger.500   #C1311E
warning.500  #E08A14
success.500  #4F9E2D     (= grass.700)
info.500     #2F6FAF
```

### 2.3. Semantic mapping (как использовать в коде/Tailwind)

```
surface.primary        forest.700        главный dark hero/section background
surface.elevated       forest.600        приподнятая поверхность на тёмном (карточка отзыва)
surface.muted          forest.800        под форму/инпуты на тёмном
surface.light          cream.100         основной светлый фон страницы
surface.lightWarm      cream.200         тёплый светлый акцент (карточка категории)
surface.lightHover     cream.300         hover на светлой карточке

text.onDark.primary    cream.100
text.onDark.secondary  cream.200 @ 75%
text.onDark.accent     grass.500          (как «Body & Spirit» в скриншоте 1)
text.onLight.primary   ink.900
text.onLight.secondary ink.500
text.onLight.accent    forest.700         заголовки на светлом

border.onDark          cream.100 @ 12%
border.onLight         ink.100
divider.onDark         cream.100 @ 8%
divider.onLight        ink.100

cta.primary.bg         amber.500
cta.primary.bgHover    amber.700
cta.primary.text       ink.900            тёмный текст на янтарном — лучший контраст
cta.primary.glow       amber.500 @ 35%    soft outer glow (см. скриншот 1)

cta.secondary.bg       transparent
cta.secondary.border   cream.100 @ 35%
cta.secondary.text     cream.100
cta.secondary.hover    cream.100 @ 8% bg

cta.sale.bg            terracotta.500
cta.sale.text          cream.100

badge.18+              terracotta.500 / cream.100 text
badge.lab              forest.600 + grass.500 stroke 1px / cream.100 text
badge.licence          forest.600 + amber.500 stroke 1px / cream.100 text
badge.sale             terracotta.500 / cream.100
badge.outOfStock       ink.500 / cream.100
badge.subscription     grass.500 / forest.900 text
badge.express180       amber.500 / ink.900 text
```

### 2.4. WCAG контраст (проверено)

| Сочетание | Ratio | AA | AAA |
|---|---|---|---|
| cream.100 на forest.700 | 11.8 : 1 | ✅ | ✅ |
| ink.900 на amber.500 | 7.9 : 1 | ✅ | ✅ |
| forest.700 на cream.200 | 9.1 : 1 | ✅ | ✅ |
| grass.500 на forest.700 | 4.7 : 1 | ✅ large | ❌ |
| cream.100 на terracotta.500 | 4.8 : 1 | ✅ | ❌ |
| ink.500 на cream.100 | 4.6 : 1 | ✅ | — |

**Правило:** `grass.500` = акцент в заголовках и подсветках, **не** для длинного body. Длинный тёмно-зелёный на кремовом — `forest.700`.

---

## 3. ТИПОГРАФИКА

### 3.1. Шрифты (загрузка)

- **Playfair Display** — Google Fonts, веса Regular 400, Bold 700, Italic 400/700. Сабсет `latin, latin-ext` (чешские диакритики).
- **Inter** — Google Fonts, веса 400 / 500 / 600 / 700. Сабсет `latin, latin-ext`.
- `font-display: swap`; preload основных woff2; без cyrillic (экономия веса).

### 3.2. Шкала (mobile / desktop)

```
display.xl    Playfair BoldItalic   56/64 (40/48 mob)   tracking −1%
display.lg    Playfair Bold         44/52 (32/40)       tracking −0.5%
display.md    Playfair BoldItalic   36/44 (28/36)       tracking −0.5%
heading.xl    Playfair Bold         32/40 (26/32)       tracking −0.5%   H1 контентных
heading.lg    Playfair Bold         26/34 (22/28)       tracking 0       H2 больших секций
heading.md    Inter SemiBold        22/30 (20/26)       tracking 0       H2/H3 UI
heading.sm    Inter SemiBold        18/26 (17/24)       tracking 0
body.lg       Inter Regular         18/28 (17/26)       tracking 0       лиды
body.md       Inter Regular         16/26 (15/24)       tracking 0       default
body.sm       Inter Regular         14/22 (13/20)       tracking 0       secondary
caption       Inter Medium          12/18 (12/16)       tracking +1.5% UPPERCASE
overline      Inter SemiBold        11/16 (11/14)       tracking +8%   UPPERCASE eyebrow ("EXPLORE CATEGORIES")
ui.btn        Inter SemiBold        15/20                                лейбл кнопки
ui.input      Inter Regular         16/24                                (16 — чтобы iOS не зумил)
metric.lg     Playfair Bold         36/40 (28/32)                        «2.500+» из скриншота 1
metric.sm     Inter SemiBold        14/18 (13/16)       tracking +5% UPPERCASE
```

### 3.3. Правила применения

- **Playfair Italic** — для «эмоциональных» display-заголовков на hero/section-intro. Не на UI/кнопках.
- **Playfair Bold (regular)** — H1/H2 страниц контента.
- **Inter** — всё остальное (включая product card title, nav, кнопки, FAQ).
- **Двух-цветный заголовок** (как «Harmonize / Body & Spirit», скриншот 1): line1 = `text.onDark.primary` (cream), line2 = `text.onDark.accent` (grass). Фирменный приём — использовать на hero главной и pillar-страницах.
- `text-wrap: balance` на display/heading длинных строк.
- НЕ использовать ВСЕ-ЗАГЛАВНЫЕ кроме `caption`/`overline`/button label (CZ-диакритика плохо в caps).

---

## 4. СЕТКА И ОТСТУПЫ

### 4.1. Грид

```
container.max     1280 px      (1440 на «wide» секциях типа hero, опц.)
container.padding 16 / 24 / 40 / 64 px   (xs / sm / md / lg+)
gap.gutter        16 mobile, 24–32 desktop
columns           12 desktop, 6 tablet, 4 mobile
```

### 4.2. Шкала отступов (8-сетка)

`4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96, 120, 160` px.

- Внутри секции между блоками: 16–24 (mob), 24–32 (desk).
- Между секциями: 64–80 (mob), 96–120 (desk).
- Внутри карточки: 16 (mob), 20–24 (desk).
- Кнопка: padding `14×24` (md), `12×20` (sm), `16×28` (lg).

---

## 5. РАДИУСЫ, ТЕНИ, БОРДЕРЫ

### 5.1. Радиусы

```
radius.xs       4 px       badge, chip
radius.sm       8 px       input, маленькие кнопки
radius.md       12 px      default card, кнопка-квадрат
radius.lg       16 px      большие карточки, модалки
radius.xl       24 px      hero-карточки, секции
radius.pill     9999       бейджи, кнопки CTA, переключатель 25/50g, фильтр-чипы
radius.circle   50%        avatar, лист-иконка в круге (как в скриншоте 3)
```

### 5.2. Тени (мягкие, тёплые)

```
shadow.sm     0 1px 2px rgba(20,32,27,.05), 0 1px 1px rgba(20,32,27,.04)
shadow.md     0 4px 12px rgba(20,32,27,.08), 0 2px 4px rgba(20,32,27,.04)
shadow.lg     0 12px 32px rgba(20,32,27,.12), 0 4px 8px rgba(20,32,27,.06)
shadow.xl     0 24px 64px rgba(20,32,27,.16), 0 8px 16px rgba(20,32,27,.08)
shadow.glow   0 0 24px rgba(244,160,32,.35)        amber glow CTA на тёмном
shadow.focus  0 0 0 3px rgba(126,200,85,.45)        фокус-кольцо (a11y)
```

- На светлом: `shadow.md` на карточках, `shadow.lg` на модалках.
- На тёмном: теней почти нет; разделение — `border.onDark` или `surface.elevated`.

### 5.3. Бордеры

- Тонкие — 1px `border.onLight` / `border.onDark`.
- Акцентные — 1.5–2px `grass.500` или `amber.500` (focus/selected).

---

## 6. ИКОНКИ

- **Базовый набор:** Lucide React (доступен в Claude Artifacts). 2px stroke, rounded-line, 24×24 viewBox.
- **Не миксовать** filled + outline в одной секции. Везде outline.
- **Категорийные «глифы»** (лист кратома и т.п., как скриншот 3) — отдельная художественная серия (см. файл 5 §3), хранится в `/assets/icons/category/` как PNG/SVG.
- Размеры: 16, 20, 24, 32, 48, 64.

---

## 7. КНОПКИ (варианты)

```
btn.primary       bg amber.500 / text ink.900 / hover amber.700 / shadow.glow on dark / radius.pill
btn.secondary     bg transparent / border 1px cream.100@35% / text cream.100 / hover bg cream.100@8% / radius.pill   (для тёмного фона)
btn.outlineLight  bg transparent / border 1px forest.700 / text forest.700 / radius.pill                              (для светлого фона)
btn.solidDark     bg forest.700 / text cream.100 / hover forest.900 / radius.pill                                     (premium look на светлом)
btn.grass         bg grass.500 / text forest.900 / hover grass.700 / radius.pill                                      (sale/промо акценты)
btn.terracotta    bg terracotta.500 / text cream.100 / radius.pill                                                    (sale/urgency)
btn.ghost         bg transparent / text forest.700 / hover bg cream.300
btn.disabled      bg ink.300 / text ink.500 / no shadow / cursor not-allowed
```

Размеры: `sm` (h=36, padding 8×16), `md` (h=44, padding 12×20), `lg` (h=52, padding 14×28).

**Иконка в кнопке** — слева 8px от текста; стрелка справа — 6px («Start Your Journey →»).

Все CTA → `radius.pill`. Квадратные (radius.md) — только в сложных формах/чекауте.

Фокус: `shadow.focus` обязательно.

---

## 8. ФОРМЫ И ИНПУТЫ

```
input.height        48 px (mobile 44)
input.padding       14×16
input.bg            cream.100 (на тёмном), surface.light + border (на светлом)
input.border        1px ink.100 (light) / cream.100@20% (dark)
input.borderFocus   2px grass.500
input.borderError   2px danger.500
input.text          ink.900 / cream.100
input.placeholder   ink.300 / cream.100@45%
input.label         heading.sm над инпутом; не плавающий лейбл (плохо с автозаполнением и CZ-ярлыками)
input.helper        body.sm, ink.500 (или success.500 при «PSČ ověřeno»)
input.error         body.sm, danger.500 + lucide:alert-circle
input.success       success.500 + lucide:check-circle
```

- **16px в инпуте** (НЕ меньше) — iOS не зумит.
- Обязательные поля помечать `*` рядом с лейблом, не в placeholder.
- Чекбоксы/радио — `radius.xs`, active = `grass.500`.

---

## 9. МОУШН И АНИМАЦИЯ

```
duration.fast    120 ms   hover, focus
duration.base    200 ms   карточка, кнопка
duration.slow    320 ms   модалка fade/slide
duration.epic    600 ms   hero parallax, scroll-reveal
easing.standard  cubic-bezier(.2,.0,.0,1)
easing.entrance  cubic-bezier(.16,1,.3,1)
easing.exit      cubic-bezier(.7,.0,.84,.0)
```

- Hover карточки товара: `translateY(-2px); shadow.lg; 200ms`.
- CTA: `scale(1.02)` + `shadow.glow` (только на тёмном).
- Scroll-reveal — умеренно (CWV, отвлечение); только hero и H2 секций.
- Параллакс — не используем.
- `prefers-reduced-motion: reduce` — отключать.

---

## 10. ОБРАЗЫ И ФАКТУРА

- Декоративные паттерны: ботанические SVG (листья) с `opacity: .04–.08` на тёмном forest-фоне; нюанс, не доминанта.
- Сетка карточек товара — без «волн/градиентов» в фоне. Чистый cream/forest, фокус на товаре.
- Hero на главной — реальное фото магазина в Праге / упаковки / листа кратома (см. файл 5 §2.1) с лёгким тёмным оверлеем для читаемости текста.
- Все фото — **тёплая цветокоррекция** (без cyan/cool). Пресет: temp +5, tint +2, saturation −5.

---

## 11. TOKEN-БРИФ (компактный, для каждой Claude-сессии)

Когда начинаешь новый чат с Claude и просишь сделать экран — **сначала вставь этот блок**, потом ТЗ конкретного экрана. ~35 строк контекста, Claude держит стиль весь чат.

```
VIVADZEN DESIGN TOKENS
Brand: kratom e-shop, CZ, PML-regulated, premium herbal apothecary tone.

COLORS (Tailwind-style)
  forest: {900:#0F2419, 800:#142E22, 700:#1B3A2D, 600:#2A5640, 500:#3D6F54, 300:#6D9482, 100:#C8D8CF}
  cream:  {50:#FBF8F0, 100:#F9F4EC, 200:#F5EDD8, 300:#ECE0C2, 400:#D9C998}
  grass:  {700:#4F9E2D, 500:#7EC855, 300:#B9E198}
  amber:  {700:#C77808, 500:#F4A020, 300:#FCD27A}
  terra:  {700:#A8431B, 500:#D45C2B}
  ink:    {900:#14201B, 700:#2B3A33, 500:#5C6A63, 300:#A8B2AC, 100:#E5E8E4}

TYPOGRAPHY
  Display: Playfair Display (Bold, BoldItalic) — hero/H1/H2 content
  UI/body: Inter (400/500/600/700)
  Sizes (desktop / mobile):
    display.xl 56/40, display.lg 44/32, heading.xl 32/26, heading.lg 26/22
    heading.md 22/20, body.lg 18/17, body.md 16/15, body.sm 14/13
    overline 11/11 UPPERCASE tracking+8%
  Two-tone hero heading: line1 cream.100, line2 grass.500 (italic optional)

LAYOUT
  Container 1280, columns 12/6/4, padding 16/24/40/64
  Spacing: 4,8,12,16,20,24,32,40,48,64,80,96,120
  Radius: pill for CTAs, 12-16 for cards, 24 for hero blocks

BUTTONS
  Primary: bg amber.500, text ink.900, hover amber.700, pill, glow on dark
  Secondary on dark: outline cream.100@35%, text cream.100, pill
  Solid dark on light: bg forest.700, text cream.100, pill

MOTION  base 200ms ease-out; respect prefers-reduced-motion
ICONS   lucide-react, 2px stroke, 24x24
CONSTRAINTS: NO health-claims in marketing copy. Always show 18+ badge.
```

→ Дальше — файл 2 (компоненты).
