# VIVADZEN — ДИЗАЙН ТЗ
## Файл 4/9 — Главная страница `/`

> Глубокое ТЗ на одну страницу — `/` (homepage). Композиция, размеры, тексты на чешском, SEO-блок, поведение mobile, A11y, перформанс.
>
> Файл рассчитан на **один артефакт в Claude**. Прикладываем + Image 1 (hero), Image 2 (reviews), Image 3 (categories) + дайджест из `01_DESIGN_SYSTEM.md` § «TOKEN-BRIEF» + дайджест из `03_GLOBALNI_KOMPONENTY.md` § «COMPONENTS-BRIEF».

---

## §1. ЦЕЛЬ И ОЖИДАНИЯ

**Что покупатель должен почувствовать за 3 секунды:**
1. «Это **легальный** kratom-shop в Чехии» — мгновенный triple-signal: 18+, лицензия, лаборатория.
2. «Это **премиум**, не клиника и не «trippy» интернет-магазин» — Playfair italic + cream/forest палитра + amber CTA.
3. «Это **локально**» — Praha, две prodejny, EXPRESS 180 min.

**Что покупатель должен сделать:**
1. Кликнуть «Prohlédnout kratom →» (главное CTA hero) → `/kratom` — **primary conversion path**.
2. ИЛИ «Naše prodejny v Praze» → `/prodejny` — **trust path** (для скептиков).
3. ИЛИ scroll вниз → bestsellers → product page → checkout.

**SEO-цель:** ранжироваться по «kratom», «kratom praha», «kratom obchod», + бренд «vivadzen». См. SEO TZ `03_TZ_KOMERCNI_STRANKY.md` §1.

---

## §2. РАСКЛАДКА — 11 СЕКЦИЙ СВЕРХУ ВНИЗ

```
┌─────────────────────────────────────────────────┐
│  S0  AnnouncementBar (опц., если активен)       │  cream BG + ⚡ EXPRESS 180 min link
├─────────────────────────────────────────────────┤
│  S1  Header                                      │  sticky, на forest.800 при скролле
├─────────────────────────────────────────────────┤
│  S2  Age 18+ / PML strip                         │  thin row, forest.900
├─────────────────────────────────────────────────┤
│                                                 │
│  S3  HERO  (Image 1 reference)                   │  forest.700 bg, 700–900 px высота
│                                                 │
├─────────────────────────────────────────────────┤
│  S4  TrustBar 4-up                               │  forest.800 bg, 96–120 px высота
├─────────────────────────────────────────────────┤
│                                                 │
│  S5  Categories (Image 3 reference)              │  cream.100 bg, ~520 px высота
│                                                 │
├─────────────────────────────────────────────────┤
│                                                 │
│  S6  Bestsellers carousel                        │  cream.50/transparent bg, ~720 px
│                                                 │
├─────────────────────────────────────────────────┤
│                                                 │
│  S7  «Proč Vivadzen» trust section               │  forest.700 bg, 4 col, ~640 px
│                                                 │
├─────────────────────────────────────────────────┤
│  S8  Google Reviews widget                       │  cream.100 bg, 360 px
├─────────────────────────────────────────────────┤
│                                                 │
│  S9  «Trusted by Thousands» (Image 2 reference)  │  forest.700 bg, ~600 px
│                                                 │
├─────────────────────────────────────────────────┤
│  S10 Content hub promo (3 guides)                │  cream.100, ~480 px
├─────────────────────────────────────────────────┤
│  S11 FAQ accordion                               │  cream.50, expandable
├─────────────────────────────────────────────────┤
│  S12 Newsletter band                             │  cream.200, ~280 px
├─────────────────────────────────────────────────┤
│  S13 Footer                                      │  forest.900, 4-col + bottom strip
└─────────────────────────────────────────────────┘
```

Контейнер всех секций — `max-width: 1280` (см. файл 01 § layout). Внутренний padding `64px desktop / 32px mobile`. Вертикальные паддинги секций — `120 desktop / 72 mobile` (см. spacing scale).

---

## §3. S3 — HERO (главная секция, Image 1 reference)

### 3.1 Визуальная композиция

**Десктоп ≥ 1024:**
- Layout: **60/40 split**, текст слева, фото справа.
- Background: `forest.700` (#1B3A2D — основной brand color).
- Текстура: `decorative/botanical-pattern-dark-1600.png` поверх с `opacity: 0.04`, blend `multiply` (см. файл 2 §3.3).
- Container max-width 1280, paddings vertical 120 / horizontal 64.
- Высота: `auto`, но fact min-height ~700 px.

**Мобил ≤ 768:**
- Layout: **stack vertical** — текст сверху, фото сзади как **background с overlay 50% dark forest gradient**.
- Высота: 100vh − header (≈ 90vh).
- Padding 32, текст центрирован влево.

### 3.2 Текстовый блок (слева)

**1. Overline (eyebrow):**
> `OVERLINE` — Inter SemiBold 12 UPPERCASE, letter-spacing +1.4px, `grass.500` (#7EC855).
> Текст: «VIVADZEN — LICENCOVANÝ E-SHOP» 

**2. H1 — двух-цветный display heading (см. Image 1):**

Структура:
```
Line 1: «Kratom s licencí MZ ČR»
         ↑ Playfair Display Bold, NOT italic, color cream.100 (#F9F4EC)
         ↑ size: display.xl (72/76 desktop, 48/52 mobile)
         ↑ letter-spacing: -1.5px
Line 2: «pro dospělé v Praze i online»
         ↑ Playfair Display BoldItalic, color grass.500 (#7EC855)
         ↑ same size as Line 1
         ↑ letter-spacing: -1px (italic compensation)
```

Это **прямая референс-копия паттерна Image 1**: «Harmonize Body & Spirit» — там Line 1 в cream и Line 2 в lime italic. У нас тот же принцип, но текст на чешском и legal-первый.

> **Альтернативный H1** (на случай если первый покажется коммерсанту перегруженным legal):
>
> Line 1: «Kratom z lab-testovaných šarží»
> Line 2: «pro znalce a profesionály»
>
> A/B-тест через 2 месяца после запуска. Дефолт — первый (legal-strong).

**3. Sub-deck:**
> `body.lg` — Inter Regular 18/28, color `cream.200@85%` (rgba 217,213,200, 0.85).
> Текст:
> «Specializovaný e-shop napojený na **dvě kamenné prodejny v Praze**. Každá šarže laboratorně testovaná v akreditované laboratoři **VŠCHT Praha** dle normy ISO 17025. Doručení po celé ČR, EXPRESS 180 minut v Praze a Ostravě.»
>
> Шрина: **max-width 56ch** (~520 px). Без обрезки на длинных экранах.

**4. CTA buttons:**

Два рядом:
```
[ Prohlédnout kratom →  ]  [  Naše prodejny v Praze  ]
   ↑ amber primary             ↑ secondary outline
   ↑ shadow.glow                ↑ border cream.200, no fill
   ↑ link → /kratom            ↑ link → /prodejny
```

Спецификации:
- **Primary:** btn.primary (см. файл 01 §7): bg `amber.500` (#F4A020), text `forest.900`, padding 16×32, radius pill (`9999`), font Inter SemiBold 16, hover `amber.600` + amber glow `shadow.glow` (см. файл 01 §8).
- **Secondary:** btn.secondary on-dark: border 1.5px `cream.200`, text `cream.100`, bg transparent, padding 16×32, radius pill. Hover: bg `cream.100@8%`.
- Gap между кнопками: 16 px.
- Mobile: stack vertical, full-width.

**5. Metric row 3-up** (как в Image 1 «2,500+ / 100% / 150+»):

Под кнопками, отступ `mt-12` (48 px):

```
2 500+                    100 %                       2
HAPPY CUSTOMERS           LAB TESTED                  PRODEJNY V PRAZE
```

Стили:
- **Цифры:** Playfair Display Bold, размер 36/40, color `amber.500` (#F4A020).
- **Подпись:** Inter SemiBold 12, UPPERCASE, letter-spacing +1.4px, color `cream.200@70%`.
- **Layout:** flex row, gap 64 px между столбцами.
- **Mobile:** scrollable row (horizontal scroll on small screens), или 3 столбца уплотнённо.

> Подгоняем числа к реальным:
> - 2 500+ — заявленные zákazníky (если пока меньше — пишем «Stovky spokojených zákazníků»).
> - 100 % — žе все šarже laboratorně testované (это реально, см. SEO).
> - 2 — две prodejny.
> 
> **Не врать. Никогда.** Это магазин с лицензией — каждое число должно быть верифицируемо.

### 3.3 Визуальный блок (справа, desktop only)

**Размер:** ширина 40% от контейнера ≈ 480 px, высота равна высоте текстового блока.

**Содержимое:** hero photo (см. файл 2 §3.1 промпт — листья на тёплом свете).

**Style:**
- aspect 4/5 portrait
- radius: 0 (полная высота секции, без скругления — современный editorial look)
- ИЛИ radius.xl (24 px) если хотим мягче
- shadow: none (фото и так на тёмном фоне)

**Альтернатива** (если фото prodejny готово):
- Реальный кадр тёплого интерьера obchodu Vivadzen, soft warm light, brass jars on shelves.

**Mobile:** фото становится **фоном секции** с overlay `linear-gradient(rgba(27,58,45,0.65), rgba(27,58,45,0.85))`, текст поверх.

### 3.4 Анимация (опц., subtle)

- На load: текст fade-up + 50ms stagger между Line 1, Line 2, sub-deck, CTAs, metrics.
- Параметры из файла 01 §10:
  - duration: 320ms
  - easing: `cubic-bezier(0.4, 0, 0.2, 1)`
- **Prefers-reduced-motion:** disabled — статика.

### 3.5 A11y / SEO для S3

- **H1** — единственный на странице, см. текст в §3.2.
- **CTA** — `<a href="/kratom" role="button">` с aria-label если иконка → text.
- **Image** — `alt="Listy kratomu v teplém přirozeném světle, premium kvalita"`.
- **Decorative pattern** — `aria-hidden="true"` на SVG.

---

## §4. S2 — Age 18+ / PML strip

**Над hero, под header.**

- Высота: 32–40 px.
- Background: `forest.900` (#0E1F18 — самый тёмный).
- Содержимое (центр):
  ```
  🛡  POUZE 18+   ·   AUTORIZOVANÝ PRODEJCE PML (MZ ČR)   ·   AKREDITOVANÁ LAB. VŠCHT
  ```
  - Иконка `shield-check` (Lucide) или своя SVG bypass-shield.
  - Текст: Inter SemiBold 11, UPPERCASE, color `cream.200@90%`, letter-spacing +1.2.
  - Разделители — `·` middle-dot color `cream.200@40%`.
- **Mobile:** только «18+ • PML • LAB» (короче).
- **Поведение:** sticky? — **нет** (не нужно, age-gate уже даёт уверенность). Просто скроллится.

---

## §5. S0 — AnnouncementBar (условный)

> Показывается только если активен EXPRESS, акция или важное сообщение. По умолчанию **скрыт**.

- Высота: 40 px.
- Background: `amber.500` (#F4A020).
- Текст: Inter SemiBold 14, color `forest.900`.
- Контент (пример):
  ```
  ⚡ EXPRESS doručení do 180 minut v Praze a Ostravě  ·  Podrobnosti →
  ```
- Closeable (×) — `localStorage` запоминает что закрыто на 7 дней.

---

## §6. S4 — TrustBar 4-up

**Под hero, перед основными секциями.**

- Background: `forest.800` (#12281F).
- Высота: 96–120 px.
- Layout: 4 столбца, gap 64 px, items vertically centered.
- Padding 24 vertical.

**Каждый столбец:** icon (Lucide, 24×24, color `grass.500`) + text Inter SemiBold 14 (color `cream.200`).

```
🧪 Akreditovaná lab. VŠCHT  |  🛡 Licence MZ ČR (PML)  |  🏪 2 prodejny v Praze  |  ⚡ EXPRESS 180 min
```

- **Hover:** opacity 0.8 на pointer.
- **Каждый icon-text → clickable link** на соответствующую страницу (`/laboratorni-testy`, `/licence`, `/prodejny`, `/doruceni`).
- **Mobile:** horizontal scroll, snap, 4 элемента подряд.

---

## §7. S5 — Categories grid (Image 3 reference)

> Точная **дизайнерская копия** Image 3 — «Find Your Wellness Path».

### 7.1 Спецификация

- Background: `cream.100` (#F9F4EC).
- Padding vertical: 120 / 72.

**Заголовок (3 строки сверху):**
```
EXPLORE CATEGORIES                          ← Overline terracotta.500
Najděte svůj druh kratomu                  ← H2 Playfair Bold forest.700
```
- Overline: Inter SemiBold 12 UPPERCASE, letter-spacing +1.4, color `terracotta.500` (#D45C2B).
- H2: Playfair Display Bold 48/52 desktop / 32/36 mobile, color `forest.700`.
- Центрировано, ширина max 720 px.

**Под H2** — короткий deck (опц., 120–140 слов sub-h2):
> «Specializovaný PML sortiment podle barvy žilky, odrůdy a formy. Každá kategorie odpovídá konkrétnímu profilu mitragyninu a způsobu zpracování.»
- Body.md (16/24), color `forest.700@80%`, max-width 640 px, centered.

### 7.2 Карточки категорий — 7 штук

Layout: flex row, **gap 16 px**, scroll-snap на mobile, фиксированная сетка на desktop (7 столбцов или 4+3 grid).

> Image 3 показывает 7 столбцов в ряд. На нашем сайте — **7 столбцов desktop**, **2 кол × 4 ряда mobile** (последний — full-width).

**Карточки (CategoryGlyphCard, см. файл 3 §19):**

| # | Категория | URL | Title | Subtitle | Icon |
|---|---|---|---|---|---|
| 1 | Zelený kratom | `/kratom/zeleny` | Zelený | Energie & svěžest | `category-green-circle-96.png` |
| 2 | Bílý kratom | `/kratom/bily` | Bílý | Aktivace & ráno | `category-white-circle-96.png` |
| 3 | Červený kratom | `/kratom/cerveny` | Červený | Klid & večer | `category-red-circle-96.png` |
| 4 | Žlutý kratom | `/kratom/zluty` | Žlutý | Vyvážený profil | `category-yellow-circle-96.png` |
| 5 | Maeng Da | `/kratom/maeng-da` | Maeng Da | Klasická odrůda | `category-maeng-da-circle-96.png` |
| 6 | Extrakt 10ml | `/kratom/extrakt` | Extrakt | Tekutá forma | `category-extrakt-circle-96.png` |
| 7 | Předplatné | `/predplatne` | Předplatné | Pravidelně −10 % | `category-predplatne-circle-96.png` |

> ⚠️ **PML legal на subtitle:** «Energie & svěžest», «Aktivace & ráno» — это **визуальная классификация цвета**, **не маркетинговое обещание эффекта**. Безопасные нейтральные дескрипторы. См. SEO TZ § PML compliance.
>
> Альтернатива если юрист потребует ещё нейтральнее: «Bílá žilka», «Zelená žilka» (просто описание цвета жилки) без поведенческих коннотаций.

**Дизайн карточки** (вертикальный):
- Container: 152×196 px (desktop), 100% width × 180 mobile.
- Background: `cream.200` (#F0E8D2) с radius.xl (24 px).
- Padding 20 vertical, 16 horizontal.
- Glyph circle: 96×96, `cream.300` bg, центрировано вверху.
- Title: Inter SemiBold 16, color `forest.900`, centered, margin-top 16.
- Subtitle: Inter Regular 12, color `forest.700@70%`, centered, margin-top 4.
- **Hover** (desktop): bg `cream.300`, transform `translateY(-4px)`, shadow.sm, transition 200ms.
- **Active/click:** bg `cream.200`, scale 0.98 briefly.
- **Focus ring:** 2 px `forest.700` offset 2.

**Под карточками** (центрировано, margin-top 48):
- Кнопка `btn.text` (text-link с амбер-стрелкой):
  > «Zobrazit všechny odrůdy →» → `/kratom/odrudy`
  - Color: `forest.700`, underline на hover.

### 7.3 Mobile-вариант

- **Layout:** 2-col grid, last item full-width.
- Карточки немного выше (220 px) — текст не обрезается.
- Touch-target минимум 44×44 (вся карточка кликабельна).

---

## §8. S6 — Bestsellers carousel

**Под Categories.**

- Background: `cream.50` (off-white) ИЛИ `cream.100` если хотим continuous flow.
- Padding 120 vertical.

### 8.1 Заголовок

```
AKTUÁLNĚ SKLADEM
Naše bestsellery
                                                          [ Zobrazit vše → ]
```

- Header layout: flex `justify-between`, items center.
- Left: Overline `cream.700` / `forest.700@60%` + H2 Playfair Bold 40 forest.700.
- Right: text-link «Zobrazit vše →» → `/kratom?sort=popular`, color `forest.700`, underline on hover.

### 8.2 Carousel

- Layout: horizontal scroll, 4 видимых ProductCard desktop / 1.2 mobile.
- Gap 24 px между карточками.
- **Snap-x mandatory** на mobile (CSS scroll-snap).
- Стрелки-навигация (← →) — справа сверху от карусели, на desktop hover.
- Optional dots под каруселью.

### 8.3 ProductCard (см. файл 3 §3)

Карточка содержит (по приоритету):
- Vein color circle + eyebrow «Red vein · Borneo»
- Product name «Červená Maeng Da» — Playfair Bold 22
- Под названием «Mitragynin 1,42 % · jemně mletý» — Inter Regular 13 muted
- Image area 1:1
- 25 g / 50 g segmented toggle
- Price + price-per-g
- Rating (4.9 ★ · 142 hodnocení)
- amber CTA pill «Přidat do košíku»

**8 товаров на запуске:**
1. Kratom Extrakt 10ml zelený
2. Zelený Rurut Nano (best мнение)
3. Zelená Sumatra
4. Bílá Maeng Da
5. Bílý slon
6. Zelený thajský
7. Červená Maeng Da
8. Zelená Maeng Da

> Выбор «bestsellers» — алгоритмический в продакшн (sales × views × conversion), но для запуска — **manual order** из этих 8 (top 4–6 показываем).

### 8.4 Mobile carousel

- 1.2 карточки видны (поощряет scroll).
- Sticky title сверху.
- Stretchy bounce при достижении конца (iOS-style).

---

## §9. S7 — «Proč Vivadzen» trust section (тёмная)

**Под bestsellers — контраст обратно к forest.**

- Background: `forest.700` с лёгким botanical pattern (см. файл 2 §3.3) opacity 0.04.
- Padding 120 vertical.

### 9.1 Заголовок

```
NAŠE STANDARDY                                            ← terracotta.500 overline
Proč Vivadzen?                                            ← Playfair Bold 48 cream.100
```
Centered, max-width 640 px.

### 9.2 4 столбца

Layout: grid-4 desktop / grid-2 tablet / 1 column mobile.
Gap 48 px.

Каждый столбец:
- **Icon** (Lucide или SVG, 48×48): `grass.500` color.
- **Heading.md** (Inter SemiBold 20): color `cream.100`, margin-top 24.
- **Body.md** (Inter Regular 15/24): color `cream.200@80%`, max-width 280, margin-top 12.

**Столбцы:**

1. 🧪 **Akreditovaná laboratoř VŠCHT Praha**
   > «Každá šarže prochází nezávislým testováním v laboratoři VŠCHT akreditované dle ISO 17025 — obsah mitragyninu, čistota, mikrobiologie, těžké kovy.»

2. 🛡 **Autorizovaný prodejce PML**
   > «Vivadzen působí v režimu psychomodulačních látek (zák. č. 167/1998 Sb., novelizován č. 321/2024 Sb.) pod přímou licencí Ministerstva zdravotnictví ČR.»

3. 🏪 **Dvě kamenné prodejny v Praze**
   > «Vinohrady a Karlín — osobní odběr, ověření věku, konzultace s vyškoleným personálem. Otevřeno 6 dní v týdnu.»

4. ⚡ **EXPRESS doručení 180 minut**
   > «Doručíme do 3 hodin v Praze a Ostravě pomocí vlastních messenger-kurýrů. Příplatek 299 Kč. Po celé ČR — 1–2 dny.»

### 9.3 CTA внизу секции

Centered, margin-top 64:
```
[  Více o naší kvalitě  →  ]
   ↑ btn.secondary on-dark (см. файл 01 §7)
   ↑ link → /laboratorni-testy
```

---

## §10. S8 — Google Reviews widget

**Между двумя trust-секциями — break light.**

- Background: `cream.100`.
- Padding 80 vertical.
- Высота: ~360 px.

### 10.1 Содержимое

Виджет Google Reviews (см. файл 3 §15):
- Heading: «Hodnocení od zákazníků na Google» Inter SemiBold 20, color `forest.700`.
- Logo Google G + текст «4.8 z 5 · 240+ hodnocení» (динамически от Google Places API).
- Карусель 3–4 отзывов из Google: avatar + name + 5-star + квота + дата.
- Кнопка «Více recenzí na Google →» → ссылка на профиль Vivadzen в Google Business.

### 10.2 Источник данных

- **Google Places API** (Place Details endpoint) с полем `reviews`.
- Кэшируем 24 часа в Redis/edge cache.
- Если API timeout → fallback static block (последние 5 отзывов вручную).

---

## §11. S9 — «Trusted by Thousands» (Image 2 reference)

> Точная дизайнерская копия Image 2 — «Trusted by Thousands» с 5 отзывами на forest BG.

### 11.1 Композиция

- Background: `forest.700`.
- Padding 120 vertical.

**Centered top block:**
```
[icon Google translate-style]                             ← bg cream.200 12×12 box
REAL PEOPLE, REAL RESULTS                                 ← Overline grass.500
Trusted by Thousands                                      ← H2 Playfair italic 48 cream.100
★★★★★    4.9 out of 5 — 2,500+ reviews                 ← rating block
```

- Иконка: 32×32 round, bg cream.200, центрирована.
- Overline: Inter SemiBold 12 UPPERCASE letter-spacing +1.4 grass.500.
- H2: Playfair Display BoldItalic — important: italic тут как в Image 2 для «Trusted by Thousands». На чешском оставляем «Důvěřuje nám tisíce zákazníků».
- Rating: ★ amber.500, text cream.200@90% Inter SemiBold 14.

### 11.2 5 карточек отзывов

Layout: grid-5 desktop (gap 24) / horizontal scroll mobile.

**Карточка** (см. файл 3 §16 InternalReviewCard, dark variant):
- Container 220×260, bg `forest.800` (#12281F), radius.xl (24), padding 24.
- ★★★★★ amber.500 row (top).
- Quote (Inter Regular 14/22 cream.200@90%): «Vivadzen má opravdu profesionální přístup, kvalitní šarže a rychlé dodání. Doporučuji.»
- Bottom: avatar (24×24 cream.200 with initials in forest.900) + name + month.

**5 отзывов (примеры):**

| # | Avatar | Name | Date | Chip | Quote |
|---|---|---|---|---|---|
| 1 | M | Martin K. | March 2026 | Zelená Maeng Da | «Profesionální balení, dodání druhý den. Šarže má COA. Důvěryhodný obchod.» |
| 2 | T | Tereza N. | February 2026 | Bílý slon | «Konečně český obchod, který otevřeně publikuje lab-výsledky. Vrátím se.» |
| 3 | J | Jakub H. | February 2026 | Extrakt 10ml | «Vyzkoušel jsem mnoho českých obchodů, Vivadzen je férový a transparentní.» |
| 4 | P | Petra V. | January 2026 | Zelený thajský | «Vynikající kvalita, navíc kamenná prodejna v Praze. Personál odborný.» |
| 5 | D | David B. | January 2026 | Červená Maeng Da | «Vivadzen má top kvalitu, lab-testy publikované, COA otevřeně. Slušná akce.» |

> ⚠️ **PML legal:** в отзывах **запрещены** обещания эффектов («помогло мне с тревогой», «избавило от боли», «лучше антидепрессанта»). Все приведённые отзывы — про **сервис**, **качество**, **транспарентность**, **скорость доставки**. Это безопасно для PML.
>
> Если реальный отзыв содержит эффект-claim — мы либо его не публикуем, либо просим у автора редакцию.

**Color chip под именем** (как в Image 2 — «Lion's Mane Mushroom», «Kanna Sceletium» в зелёных chip'ах):
- Bg `grass.500@20%`, text `grass.500`, padding 4×10, radius.full, Inter SemiBold 11, name of product reviewed.

### 11.3 CTA внизу

Centered:
```
[  Číst všechny recenze  →  ]  → /recenze
   ↑ btn.secondary on-dark
```

---

## §12. S10 — Content hub promo

**После «Trusted by Thousands» — переход к образовательной части.**

- Background: `cream.100`.
- Padding 96 vertical.

### 12.1 Заголовок

```
PRŮVODCE & FAKTA                                          ← overline forest.700@60%
Co stojí za to vědět o kratomu                            ← H2 Playfair Bold 40 forest.700
```

### 12.2 3 карточки гайдов

Layout: grid-3 desktop / 1-column stacked mobile.
Gap 32.

**Карточка** (180×280):
- Image area 1:1 на верху (160 px), `cream.300` placeholder ИЛИ blog hero image (см. файл 2 §3.4).
- Tag chip (eyebrow с цветом темы): «PRŮVODCE», «LEGISLATIVA», «KVALITA».
- Heading: Inter SemiBold 18, color `forest.900`, leading-tight.
- Excerpt (опц.): Inter Regular 14 forest.700@70% — 2 строки.
- «Číst →» link внизу.

**3 темы:**

1. **«Co je kratom — vědecký pohled»**
   - Tag: PRŮVODCE
   - URL: `/pruvodce/co-je-kratom`
   - Excerpt: «Mitragyna speciosa, mitragynin, historie a botanika.»

2. **«Kratom v ČR — legislativa 2026 a co to znamená»**
   - Tag: LEGISLATIVA
   - URL: `/pruvodce/kratom-zakon-2026`
   - Excerpt: «Zákon č. 321/2024 Sb., PML režim a kdo má licenci.»

3. **«Jak poznat kvalitní kratom — laboratorní testy a COA»**
   - Tag: KVALITA
   - URL: `/pruvodce/jak-poznat-kvalitni-kratom`
   - Excerpt: «Co hledat v COA, čistota, mitragynin a šarže.»

### 12.3 CTA в самом низу секции

Centered:
```
[ Všechny průvodce → ]   → /pruvodce
```

---

## §13. S11 — FAQ accordion

**Под content-hub — closing trust + SEO bonus.**

- Background: `cream.50`.
- Padding 96 vertical.

### 13.1 Layout

- 2-col на desktop: left 40% — heading + intro, right 60% — accordion.
- 1-col mobile (stacked).

**Left col:**
```
ČASTÉ DOTAZY                                              ← overline forest.700@60%
Vše, co potřebujete vědět                                 ← H2 Playfair Bold 40 forest.700
Pokud nenajdete odpověď, napište nám →                   ← link → /podpora
```

**Right col:** 6 accordion-items (см. файл 3 §20 FAQAccordion).

### 13.2 FAQ-вопросы (SEO-optimized + PML-safe)

> Эти вопросы дублируются с JSON-LD `FAQPage` schema — см. SEO TZ § Tech 06.

1. **«Je kratom v Česku legální?»**
   > Ano. Od 12. 11. 2025 je kratom regulován jako psychomodulační látka podle zák. č. 167/1998 Sb. (novelizován č. 321/2024 Sb.). Prodej je omezen na licencované provozovny — Vivadzen je autorizovaný prodejce s licencí Ministerstva zdravotnictví ČR.

2. **«Jak Vivadzen testuje šarže?»**
   > Každá šarže prochází nezávislým testem v akreditované laboratoři VŠCHT Praha podle normy ISO 17025. Testujeme obsah mitragyninu, 7-hydroxymitragyninu, čistotu, mikrobiologii a obsah těžkých kovů. COA je dostupný ke stažení u každého produktu.

3. **«Jak rychle dorazí moje objednávka?»**
   > V Praze a Ostravě nabízíme EXPRESS doručení do 180 minut (příplatek 299 Kč). Po celé ČR — 1–2 pracovní dny prostřednictvím Zásilkovny, PPL nebo České pošty. Osobní odběr v prodejnách Praha-Vinohrady a Praha-Karlín — zdarma.

4. **«Jaké jsou platební možnosti?»**
   > Akceptujeme platební karty (Visa, Mastercard), Apple Pay, Google Pay, QR-platbu, dobírku a převod na účet. Všechny online platby probíhají přes SSL-šifrované spojení s 3D Secure ověřením.

5. **«Mohu objednat bez registrace?»**
   > Ano. Nabízíme jak objednávku jako host (rychlý 3-krokový checkout), tak vytvoření účtu s benefity — uložené dodací adresy, historie objednávek, snadné předplatné a sledování objednávek.

6. **«Co je COA a jak ho najdu?»**
   > COA (Certificate of Analysis) je oficiální laboratorní protokol konkrétní šarže. Najdete ho u každého produktu pod záložkou «Laboratorní testy» a také v sekci `/laboratorni-testy` filtrované podle šarže. Ke stažení v PDF.

### 13.3 Accordion стилистика

- Каждый item: padding 24 vertical, border-bottom 1px `cream.300`.
- Question: Inter SemiBold 17 forest.900.
- + (toggle): chevron-down (Lucide), rotates 180° on expand, transition 200ms.
- Answer (open state): Inter Regular 15/24 forest.700, padding-top 12.
- **Default state:** все закрыты.
- **Schema:** JSON-LD `FAQPage` — каждый Q/A. Все answer-text видимы в HTML (для SEO) — accordion только визуально collapses, текст уже в DOM.

---

## §14. S12 — Newsletter band

**Перед footer — soft conversion для не покупающих сейчас.**

- Background: `cream.200`.
- Padding 80 vertical.

### 14.1 Composition

- Centered, max-width 720.
- Heading: Inter SemiBold 22 forest.900: «Aktuální šarže, novinky o regulaci PML a edukace»
- Sub: Inter Regular 15 forest.700@80%: «1× měsíčně, žádný spam. Můžete kdykoliv odhlásit.»
- Form (inline): email-input + button «Přihlásit se».

### 14.2 Form

- Email input: padding 14 vertical / 16 horizontal, border 1.5px `forest.700@20%`, radius pill ИЛИ radius.sm, font Inter Regular 15, placeholder «vase.email@example.cz».
- Button: btn.primary amber (см. файл 01 §7), text «Přihlásit se», padding same height.
- Validation: HTML5 + JS — invalid → border `terracotta.500` + error msg.
- Success: input → green check + text «Děkujeme! Potvrďte přihlášení ve své schránce.»

### 14.3 Trust микро-копи под формой

```
🔒 Vaše údaje chráníme dle GDPR. Žádný spam.
```
- Inter Regular 12 forest.700@60%, centered, margin-top 16.

---

## §15. S13 — Footer

> См. файл 3 §2 — полная спецификация footer'а с обязательными badges.

Краткое напоминание содержимого:

**4 колонки на desktop:**
1. **Brand + popis** + сертификаты row (Akreditovaná lab, PML licence, 18+).
2. **Sortiment** — links на категории.
3. **Pomoc & info** — links на trust, контакты, FAQ.
4. **Newsletter signup** (компактный) + социальные иконки (FB, IG, GMaps).

**Bottom strip (forest.900):**
- Copyright + lawyer-disclaimer + payment-method logos row.

**Mobile:** accordion-collapsed columns.

---

## §16. SEO БЛОК

> Из SEO TZ `03_TZ_KOMERCNI_STRANKY.md` §1 — главная.

### 16.1 Meta tags

```html
<title>Kratom s licencí MZ ČR — laboratorně testovaný | Vivadzen</title>
<meta name="description"
      content="Specializovaný e-shop kratomu pod licencí PML.
               Každá šarže testovaná VŠCHT Praha. 2 prodejny v Praze.
               Doručení do 180 min v Praze a Ostravě." />

<meta property="og:title" content="Vivadzen — Kratom s licencí MZ ČR" />
<meta property="og:description" content="Licencovaný PML prodejce..." />
<meta property="og:image" content="/assets/ai-generated/og-images/og-home-1200x630.png" />
<meta property="og:url" content="https://vivadzen.com/" />
<meta name="twitter:card" content="summary_large_image" />

<link rel="canonical" href="https://vivadzen.com/" />
<link rel="alternate" hreflang="cs-CZ" href="https://vivadzen.com/" />
<link rel="alternate" hreflang="x-default" href="https://vivadzen.com/" />
```

### 16.2 JSON-LD schemas (3 типа на главной)

**Organization:**
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Vivadzen",
  "url": "https://vivadzen.com",
  "logo": "https://vivadzen.com/assets/brand/logo-vivadzen-primary.svg",
  "description": "Licencovaný specializovaný e-shop kratomu...",
  "sameAs": [
    "https://www.facebook.com/vivadzen",
    "https://www.instagram.com/vivadzen"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+420 ...",
    "contactType": "customer service",
    "areaServed": "CZ",
    "availableLanguage": "Czech"
  }
}
```

**LocalBusiness** (для homepage если бренд = магазин):
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Vivadzen — Praha Vinohrady",
  "image": "https://vivadzen.com/assets/...",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "...",
    "addressLocality": "Praha",
    "postalCode": "120 00",
    "addressCountry": "CZ"
  },
  "geo": { "@type": "GeoCoordinates", "latitude": 50.075538, "longitude": 14.4378 },
  "openingHoursSpecification": [...],
  "telephone": "+420 ...",
  "url": "https://vivadzen.com/prodejny/praha-vinohrady"
}
```

**FAQPage** — для секции S11:
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Je kratom v Česku legální?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ano. Od 12. 11. 2025 je kratom regulován jako psychomodulační látka..."
      }
    },
    /* ... 5 more */
  ]
}
```

### 16.3 H1 / heading-hierarchy

- **H1** (единственный): «Kratom s licencí MZ ČR / pro dospělé v Praze i online» (см. §3.2).
- **H2** на каждой основной секции (S5, S6, S7, S9, S10, S11).
- **H3** для подзаголовков внутри `Proč Vivadzen` (S7) — каждый из 4 столбцов.
- Никаких H4 на главной.

### 16.4 Internal links — strategic

Из главной → главные cluster pages (см. SEO TZ `02_ARCHITEKTURA_PERELINKOVKA_VAHY.md`):

- `/kratom` (×2: hero CTA + bestsellers «Zobrazit vše»)
- `/kratom/{color}` × 4 (через CategoryGlyphCard)
- `/kratom/maeng-da` (через CategoryGlyphCard)
- `/kratom/extrakt` (через CategoryGlyphCard)
- `/predplatne` (через CategoryGlyphCard)
- `/laboratorni-testy` (TrustBar + S7 CTA)
- `/licence` (TrustBar + S7)
- `/prodejny` (TrustBar + hero secondary CTA + S7)
- `/doruceni` (TrustBar + AnnouncementBar)
- `/pruvodce/co-je-kratom` (S10)
- `/pruvodce/kratom-zakon-2026` (S10)
- `/pruvodce/jak-poznat-kvalitni-kratom` (S10)
- `/pruvodce` (S10 CTA)
- `/recenze` (S9 CTA)
- `/podpora` (S11 left col link)
- Каждый product page через bestsellers (8 prodaktов).

→ Главная даёт ~20 внутренних ссылок к pillar / category / trust страницам. Это **базовый weight distributor** в нашей архитектуре.

---

## §17. PERFORMANCE

### 17.1 Core Web Vitals targets

| Metric | Target | Strategy |
|---|---|---|
| **LCP** | < 2.0s | Hero image — preload, AVIF first, prerendered HTML (SSR) |
| **FCP** | < 1.5s | Critical CSS inline, fonts preload `Playfair-Bold + Inter-Regular` |
| **CLS** | < 0.05 | All images with explicit `width`/`height`, font-display swap with fallback metrics |
| **INP** | < 200ms | No heavy JS на mount, accordion lazy |
| **TBT** | < 200ms | Carousel в S6 — lazy load на viewport intersect |

### 17.2 Сделать

- **Preload:** hero image AVIF, 2 шрифта (Playfair Bold + Inter Regular).
- **Defer:** Google Reviews API (S8) — после `load`, не на critical path.
- **Lazy:** S6 carousel, S7+ секции — IntersectionObserver triggered animation/load.
- **Image strategy:** `<picture><source type="image/avif"><source type="image/webp"><img></picture>` для всех hero + category icons.
- **SVG inline** для icons (Lucide через JSX или static SVG).
- **Service worker** — кэширует все статические ассеты.

### 17.3 Critical resources на главной

```html
<head>
  <link rel="preload" href="/fonts/PlayfairDisplay-Bold.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/fonts/Inter-Regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/ai-generated/hero/home-hero-leaves-warm-light-1600x1000.avif" as="image" type="image/avif">
  <link rel="preconnect" href="https://maps.googleapis.com">
  <link rel="dns-prefetch" href="https://www.google-analytics.com">
</head>
```

---

## §18. MOBILE-SPECIFIC

### 18.1 Touch targets

Все CTA минимум 44×44 px (Apple HIG). Padding 16 vertical обеспечивает 48+ px.

### 18.2 Scroll анимации

- IntersectionObserver — fade-up для каждой секции на view (50ms stagger).
- **Reduce motion** — отключаем.

### 18.3 Изменения секций

| Section | Desktop | Mobile |
|---|---|---|
| S3 Hero | 60/40 split | Stack, фото фон с overlay |
| S5 Categories | 7-col row | 2-col grid (3+3+1 last full-width) |
| S6 Bestsellers | 4-col carousel | 1.2 carousel scroll-snap |
| S7 Why | 4-col grid | 1-col stacked |
| S9 Trusted | 5-col grid | 1.2 carousel scroll-snap |
| S10 Content | 3-col grid | 1-col stacked |
| S13 Footer | 4-col grid | accordion-stack |

### 18.4 Sticky elements

- **Header** — sticky сверху, fade-in BG `forest.900@95%` при скролле > 80 px.
- **18+ strip** — НЕ sticky, только в hero area.

---

## §19. ACCESSIBILITY (WCAG 2.2 AA)

- **Contrast verified:** все text/bg pairs ≥ 4.5:1 (см. файл 01 §4 — contrast matrix).
- **Focus visible:** все interactive elements — 2px solid `forest.700` ИЛИ `grass.500` ring offset 2.
- **Keyboard nav:** Tab order совпадает с visual flow, skip-to-content link сверху страницы.
- **Screen reader:** все decorative SVG `aria-hidden="true"`, все interactive elements с aria-labels.
- **Heading order:** H1 → H2 → H3, никаких skip-levels.
- **Lang attribute:** `<html lang="cs">`.
- **Alt-text:** все `<img>` с осмысленным alt (см. asset-registry в файле 2 §8.1).
- **Forms:** newsletter — `<label>` с `for`, error-msg с `aria-live="polite"`.
- **Carousel:** клавиатурные controls (← → клавиши), `aria-roledescription="carousel"`, slide labels.

---

## §20. DEFINITION OF DONE — главная

См. файл 9 §3 — общий чек-лист по странице. Specific для homepage:

- [ ] Двух-цветный H1 — Line 1 cream / Line 2 grass italic
- [ ] Hero metric row 3-up (2 500+ / 100% / 2) с амбер-цифрами
- [ ] CTA «Prohlédnout kratom» с amber glow shadow
- [ ] TrustBar 4-up с правильными иконками + clickable links
- [ ] Categories 7-up как Image 3 (cream BG + terracotta overline + 7 круг-карточек)
- [ ] Bestsellers carousel — 4–6 product cards с правильной мини-картой (см. файл 3 §3)
- [ ] «Proč Vivadzen» dark — 4 столбца с tightными trust-копи (без обещаний эффектов)
- [ ] Google Reviews виджет — динамический + fallback
- [ ] «Trusted by Thousands» — 5 review cards на forest, italic H2 как Image 2
- [ ] Content-hub 3 promo-карточки → /pruvodce/*
- [ ] FAQ 6 вопросов + FAQPage schema
- [ ] Newsletter form с GDPR-trust copy
- [ ] Footer с badges Akreditovaná + PML + 18+
- [ ] Все 3 JSON-LD schemas inline (Organization, LocalBusiness, FAQPage)
- [ ] LCP < 2.0s, CLS < 0.05
- [ ] Все CTA с touch-target ≥ 44 px
- [ ] Все contrast ≥ 4.5:1
- [ ] H1 единственный, H2/H3 hierarchical
- [ ] AnnouncementBar опц., скрывается через × + localStorage 7d
- [ ] Mobile breakpoints: S3 stack, S5 2-col, S6 1.2 carousel, S7 1-col, S9 1.2 carousel

---

## §21. ПРОМПТ ДЛЯ CLAUDE (на этот файл)

```
Ты — senior frontend дизайнер. Создай артефакт `vivadzen-homepage.html`
(single-file React + Tailwind CDN + Lucide + Google Fonts: Playfair Display
+ Inter).

В контексте уже есть:
- 01_DESIGN_SYSTEM.md (palette, typography, components base)
- 03_GLOBALNI_KOMPONENTY.md (Header, Footer, ProductCard и т.д.)
- 3 image references (Image 1 hero, Image 2 reviews, Image 3 categories)

Прикладываю файл `04_STRANKA_HOMEPAGE.md` — полная композиция 13 секций.

Задача:
1. Реализуй каждую секцию (S0–S13) согласно §3–§15 этого файла.
2. Используй placeholder-paths из файла 2 для изображений
   (например /assets/ai-generated/hero/home-hero-leaves-warm-light-1600x1000.png),
   без base64.
3. Все тексты на чешском — точно из этого файла (cyrillic не вставлять).
4. CSS variables из 01 design system. Tailwind classes — secondary.
5. JSON-LD schemas (Organization, LocalBusiness, FAQPage) inline в <head>.
6. Lucide icons через CDN — но импорт через `<script type="module">`.
7. A11y: skip-link, aria-labels, focus rings, semantic HTML.
8. Responsive: см. §18, mobile breakpoint 768 px.

Не используй localStorage. Не делай fetch к API.
Newsletter form — UI only, console.log на submit.
Carousel в S6/S9 — vanilla scroll-snap (без JS-библиотек).
Accordion в S11 — `<details>/<summary>` нативно ИЛИ React useState.

Артефакт должен open в браузере и быть прокручиваем без ошибок.
```

> Этот промпт + файл 04 + ассеты-стили из 01 + компоненты из 03 → один Claude-чат → один артефакт `homepage.html` готов для показа заказчику.
