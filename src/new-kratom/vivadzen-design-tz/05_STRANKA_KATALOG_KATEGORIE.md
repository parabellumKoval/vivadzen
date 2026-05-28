# VIVADZEN — ДИЗАЙН ТЗ
## Файл 5/9 — Каталог, категории, штаммы, поиск

> Глубокое ТЗ на семейство связанных страниц:
> - **§1** `/kratom` — главный каталог (флагман)
> - **§2** `/kratom/{barva}` — цветовые категории (zeleny, bily, cerveny, zluty)
> - **§3** `/kratom/{forma}` — формы (prasek, extrakt, nano)
> - **§4** `/kratom/{odruda}` — strain-хабы (maeng-da, sumatra, borneo, thai, bali, slon)
> - **§5** `/hledat?q=…` — поиск
> - **§6** Общие компоненты — фильтры, сортировки, пагинация, «Připravujeme»
>
> Все эти страницы используют **общий шаблон листинга**, но каждая имеет свой уникализирующий блок (intro + SEO-текст + cross-link секцию) для anti-cannibalization (см. SEO TZ `02_ARCHITEKTURA_PERELINKOVKA_VAHY.md`).
>
> Файл рассчитан на **2–3 артефакта в Claude**: (a) каталог-template универсал, (b) одна цветовая категория для проверки текста, (c) один strain-hub.

---

## §0. ОБЩИЙ ШАБЛОН ЛИСТИНГА

> Применяется ко всем 4 типам страниц (§1–§4). Различия — только в hero + intro + SEO-секции.

### 0.1 Структура сверху вниз (универсал)

```
┌─────────────────────────────────────────────────┐
│  S1  AnnouncementBar (опц.)                      │  amber, ⚡ EXPRESS
├─────────────────────────────────────────────────┤
│  S2  Header                                      │  sticky
├─────────────────────────────────────────────────┤
│  S3  18+ / PML strip                             │  forest.900
├─────────────────────────────────────────────────┤
│  S4  Breadcrumbs                                 │  cream.100, h=48
├─────────────────────────────────────────────────┤
│                                                 │
│  S5  HERO secondary (с H1)                       │  forest.700, h=320–400
│                                                 │
├─────────────────────────────────────────────────┤
│  S6  Quick chip-row (filters)                    │  cream.100, h=64, sticky
├─────────────────────────────────────────────────┤
│                                                 │
│  S7  Main: FilterSidebar + ProductGrid           │  cream.50 bg
│       (desktop split 280 + grid)                 │  high content area
│                                                 │
├─────────────────────────────────────────────────┤
│  S8  «Připravujeme» placeholder section          │  cream.200 bg
├─────────────────────────────────────────────────┤
│  S9  Cross-link block (related cats/strains)     │  cream.100
├─────────────────────────────────────────────────┤
│  S10 SEO long-form text                          │  cream.100, max 720 px
├─────────────────────────────────────────────────┤
│  S11 FAQ accordion                               │  cream.50
├─────────────────────────────────────────────────┤
│  S12 Newsletter band                             │  cream.200
├─────────────────────────────────────────────────┤
│  S13 Footer                                      │  forest.900
└─────────────────────────────────────────────────┘
```

### 0.2 Container и padding

- Max-width 1280, padding-horizontal 64 desktop / 32 mobile.
- Padding-vertical секций: 96 desktop / 64 mobile (меньше, чем на главной — это утилитарная страница).
- Между «hero» и «filter-row» — **без** vertical gap (hero растягивается вниз до filter-row).

---

## §1. `/kratom` — главный каталог (флагман)

> Корень всего PML-каталога. Самая weight-heavy страница после `/`. Ранжируется по «kratom», «kratom obchod», «koupit kratom», «kratom prodej».

### 1.1 SEO-цель

| Атрибут | Значение |
|---|---|
| URL | `https://vivadzen.com/kratom` |
| Primary kw | kratom (CZ market: ~12 000/mes) |
| Secondary | koupit kratom, kratom obchod, kratom prodej, kratom skladem |
| Search intent | commercial + transactional |
| Заголовок (H1) | «Kratom — prášek a extrakt, laboratorně testovaný» |
| Title | «Kratom — koupit prášek a extrakt | Lab-testováno | Vivadzen» (62 chars) |
| Meta description | «Specializovaný PML e-shop kratomu pod licencí MZ ČR. Každá šarže testovaná v akreditované laboratoři VŠCHT Praha. Doručení do 180 min v Praze.» (158 chars) |
| Canonical | self (`/kratom`) |

### 1.2 S5 — Hero secondary

- Background: `forest.700` (#1B3A2D).
- Текстура: botanical pattern overlay opacity 0.04 (см. файл 2 §3.3).
- Padding 80 vertical, 64 horizontal.
- Min-height: 320 px.

**Содержимое (левая колонка 70%):**

```
KATALOG · PML SORTIMENT                                   ← overline grass.500
Kratom — prášek a extrakt                                 ← H1 Playfair Bold 56/60 cream.100
Specializovaný sortiment licencovaného PML prodejce.       ← deck body.lg 18 cream.200@85%
Každá šarže testovaná v akreditované laboratoři.

[ Procházet všechny odrůdy → ]   [ Co je kratom? → ]      ← buttons row
   ↑ btn.primary amber              ↑ btn.text link
```

**Правая колонка (30%, desktop only):**

Mini-info card (cream.200 bg, radius.lg, padding 24):
```
NAŠE STANDARDY
─────────────
Šarží testovaných                  100 %
Aktivních odrůd                       8
Prodejen v Praze                      2
COA dostupných                      ANO
```
- Inter SemiBold 13 UPPERCASE labels (forest.700@70%).
- Inter SemiBold 18 numbers (amber.500 или forest.700).
- Divider 1px forest.700@20%.

**Mobile:** hero stack vertical, info-card → 4 stat row под deck'ом.

### 1.3 S6 — Quick chip-row (sticky)

- Background: `cream.100` (#F9F4EC).
- Sticky на скролле (top: 64 px, под header'ом).
- Высота 64.
- Содержимое: горизонтальная **chip-row** с быстрыми фильтрами по цвету (без перехода на другую страницу — внутри-страничный JS-фильтр):

```
[ Vše ]  [ Zelený ]  [ Bílý ]  [ Červený ]  [ Žlutý ]  [ Maeng Da ]  [ Extrakt ]
   ↑ active state: bg forest.700, text cream.100
   ↑ inactive: bg cream.200, text forest.700, hover cream.300
```

> ⚠️ **Важно:** **эти chip'ы — это внутри-страничный фильтр**, **не** ссылки на отдельные категорийные URL. Для **переходов на отдельные URL** (color/strain pages) у нас есть **S9 cross-link block** ниже.
>
> Это решает SEO-проблему: если бы каждый chip был ссылкой `<a href="/kratom/zeleny">`, мы бы создавали 7 одинаковых паттернов навигации на 7 URL — это keyword cannibalization. А chip-фильтр на одной странице — это UX без SEO-конфликта.

**Mobile:** horizontal scroll, snap, без sticky (это занимает viewport).

### 1.4 S7 — Main content (filter + grid)

#### 1.4.1 Layout

- **Desktop ≥ 1024:** flex split.
  - Left: `FilterSidebar` 280 px, sticky top 128 px (под header + chip-row).
  - Right: `ProductGrid` 3-col flex-1.
  - Gap 32 px.
- **Mobile < 1024:** sidebar — **off-canvas drawer** (right slide-in, см. файл 3 §12).
  - Trigger: «🔍 Filtry (3)» button-pill в верху grid'а, badge показывает кол-во активных.
  - Drawer width 320 px, bg cream.100.

#### 1.4.2 FilterSidebar (см. файл 3 §12)

Группы фильтров (collapsible accordion):

1. **Barva žilky** (checkbox с цветовым кружком, см. mini-card vein circle):
   - 🟢 Zelený (4 produkty)
   - ⚪ Bílý (2)
   - 🔴 Červený (1)
   - 🟡 Žlutý (0 — disabled state)

2. **Odrůda:**
   - Maeng Da (2)
   - Sumatra (1)
   - Thajský (1)
   - Slon (1)
   - Borneo (0 — disabled)
   - Rurut (1)

3. **Forma:**
   - Prášek (7)
   - Extrakt (1)
   - Nano (1)

4. **Balení:**
   - 25 g (8)
   - 50 g (8)

5. **Mitragynin %** (range slider, dual-thumb):
   - Min 0.8 % — Max 2.0 %
   - Default open: 1.0 — 1.6.
   - Step 0.05.

6. **Dostupnost:**
   - ☑ Skladem
   - ☐ Připravujeme (placeholder products)

7. **Cena (Kč):**
   - Range slider 0 — 1000 Kč, step 50.

8. **Hodnocení:**
   - 5★ only
   - 4★ a více
   - 3★ a více

**FilterSidebar header:**
```
FILTRY                              [ Vyčistit ]
─────────────────────────────────────────────────
```
- Heading.md Inter SemiBold 16, color forest.900.
- «Vyčistit» — text-link forest.700, видим только если хотя бы 1 фильтр активен.

**Каждая group accordion:**
- Header — clickable, padding 16 vertical, border-bottom 1px cream.300.
- Group title: Inter SemiBold 14 forest.900.
- Counter в скобках: «(4)» — body.sm forest.700@60%.
- Chevron-down (Lucide), rotate 180° при expand.
- Body: padding 16 vertical, options Inter Regular 14 forest.700.
- **Default:** Barva, Odrůda, Dostupnost — раскрыты. Forma, Balení, Mitragynin, Cena, Hodnocení — свёрнуты.

#### 1.4.3 ProductGrid header

Над сеткой (внутри right col), flex:
- Left: «Zobrazujeme **8 z 23 produktů**» (Inter Regular 14 forest.700@70%).
- Right: sort dropdown.

**Sort dropdown** (см. файл 3 §12 + select component):
```
Doporučené  ▾
```
Options:
- Doporučené (default)
- Cena: nejlevnější
- Cena: nejdražší
- Nejnovější (created_at desc)
- Mitragynin % (nejvyšší)
- Hodnocení (5★ first)

Стили: bg cream.100, border 1px forest.700@20%, padding 8×16, radius.md, Inter Regular 14 forest.900, chevron-down Lucide.

#### 1.4.4 Active filter chips (под sort row)

Если есть активные фильтры:
```
Zelený  ✕     Maeng Da  ✕     Prášek  ✕     Vyčistit vše
```
- Каждый chip: bg cream.200, text forest.700, padding 6×12, radius.full, gap 8 между chip'ами.
- ✕ — Lucide X, размер 12.
- «Vyčistit vše» — text-link terracotta.500.

#### 1.4.5 ProductGrid itself

- Layout: grid 3-col desktop / 2-col tablet / 1-col mobile.
- Gap: 24 px desktop / 16 mobile.
- ProductCard (см. файл 3 §3 — mini card вариант).

**8 товаров на запуске** (см. файл 4 §8.3):
1. Kratom Extrakt 10ml zelený
2. Zelený Rurut Nano
3. Zelená Sumatra
4. Bílá Maeng Da
5. Bílý slon
6. Zelený thajský
7. Červená Maeng Da
8. Zelená Maeng Da

> Если все 8 видны (нет фильтров) → grid 3×3 = 9, последний слот — promo-card «Hledáte něco jiného?» с link на /podpora.

#### 1.4.6 Пагинация

> Решение: **«Načíst další 12»** кнопка (load-more, не infinite scroll).
> Причины: CWV friendly, лучше для SEO (контент в SSR), даёт breadcrumbs состояния.
>
> Если общее количество товаров > 24 — добавить **numbered pagination** ниже как fallback для SEO-crawling (Google не всегда триггерит load-more JS).

```
[  Načíst dalších 12 produktů  ]   ← centered, btn.outline forest, full pill
                                     ↑ показывается только если есть еще
```

**Numbered pagination** (под load-more):
```
←  1  2  [3]  4  5  →
```
- Active page: bg forest.700, text cream.100, radius.md.
- Stránka 3 z 5 (text справа).
- Pre-rendered HTML — все 5 страниц индексируемы.

### 1.5 S8 — «Připravujeme» секция

> Это **placeholder products** — товары, на которые ждём лицензирование. См. SEO TZ `03_TZ_KOMERCNI_STRANKY.md` §3.9.

- Background: `cream.200`.
- Padding 96 vertical.

#### 1.5.1 Header

```
PŘIPRAVUJEME                                              ← overline terracotta.500
Brzy doplníme do sortimentu                               ← H2 Playfair Bold 36 forest.700
Tyto odrůdy procházejí licencováním a laboratorními       ← deck body.md forest.700@70%
testy. Přihlaste se k notifikaci a budete první.
```

- Centered, max-width 720.

#### 1.5.2 Placeholder cards

Grid 3-col desktop / 1-col mobile, gap 24.

**Placeholder ProductCard** (см. файл 3 §3 placeholder variant):
- Image: blurred placeholder ИЛИ silhouette glyph (cream.300 bg).
- Bedge corner: «BRZY» terracotta.500.
- Title + Eyebrow (как у real product).
- Price: hidden.
- CTA: **«Upozornit při naskladnění»** btn.outline forest, full-width.
- При click → email input modal → backend lead capture.

**Примеры placeholder products** (на запуске):
1. Zelená Bali (prášek 25 g)
2. Červený Borneo (prášek 50 g)
3. Žlutý Vietnam (prášek 25 g)
4. Bílá Thai Elephant (prášek 50 g)
5. Extrakt 30ml červený (lim. edition)
6. Sumatra Nano (jemně mleté)

> Эти 6 placeholder'ов **расширяют семантическое покрытие** до получения лицензии — Google ранжирует страницу по этим вариантам сразу, а как только мы получим лицензию на конкретный товар, конвертируем placeholder в live product (URL остаётся, lead'ы получают первое уведомление).

#### 1.5.3 Bulk lead-capture форма (под cards)

```
PŘIDEJTE SE NA SEZNAM                                     ← centered
Dáme vám vědět o všech nových šaržích                    ← H3 Playfair Bold 28
[ váš email...                  ] [ Přihlásit se ]
☑ Souhlasím s GDPR
```

### 1.6 S9 — Cross-link block («Procházejte podle...»)

> Внутренний weight distributor. Ссылает на color- и strain-категории через ON-PAGE block. Это альтернатива sidebar-nav.

- Background: `cream.100`.
- Padding 96 vertical.

#### 1.6.1 Layout — 2 sub-блока

**Sub-block A: «Podle barvy žilky»**

```
PODLE BARVY ŽILKY                                         ← overline
4 kategorie podle profilu mitragyninu                     ← H3 forest.700

[Z]  [B]  [Č]  [Ž]                                        ← 4 круг-карточки horizontal
```

Карточки:
- Zelený kratom → `/kratom/zeleny` (4 produkty)
- Bílý kratom → `/kratom/bily` (2)
- Červený kratom → `/kratom/cerveny` (1)
- Žlutý kratom → `/kratom/zluty` (placeholder)

**Sub-block B: «Podle odrůdy»**

```
PODLE ODRŮDY                                              ← overline
6 odrůd s vlastní genetikou                               ← H3 forest.700

[Maeng Da] [Sumatra] [Thajský] [Slon] [Borneo] [Rurut]    ← strain chip-cards
```

Strain chip-cards:
- Card width 160 px, height 180 px, bg cream.200, radius.lg.
- Top: 1 row mini-image (jar/leaf), 96×96 round.
- Title: Inter SemiBold 16 forest.900.
- Subtitle: «Indonésie · klasická» Inter Regular 12 forest.700@60%.
- Link → `/kratom/{strain}`.

**Mobile:** обе sub-блока — horizontal scroll-snap rows.

### 1.7 S10 — SEO long-form text

> 1100–1500 слов структурированного текста под grid'ом. Помогает ранжироваться по long-tail и информационным запросам.

- Background: `cream.100`.
- Layout: centered single column, max-width 720 px.
- Padding 96 vertical.
- Typography: prose styles, см. ниже.

#### 1.7.1 Структура

```
[Article header]
KATALOG · KOMPLETNÍ INFORMACE                             ← overline
Vše o našem kratom sortimentu                             ← H2 Playfair Bold 36 forest.700

[Lead paragraph — 60 слов]

[H3] Co najdete v našem katalogu?
[…120 слов о товарах + ссылки]

[H3] Podle barvy žilky
[…150 слов — 4 цвета описание, ссылки на color-pages]

[H3] Podle odrůdy a původu
[…200 слов — Indonésie/Borneo/Thajsko + ссылки на strain-pages]

[H3] Forma — prášek, extrakt, nano
[…120 слов]

[H3] Jak vybrat ten správný kratom?
[…200 слов — рекомендации БЕЗ обещаний эффектов]

[H3] Naše standardy kvality
[…180 слов — lab, COA, šarže]

[H3] Doručení a osobní odběr
[…140 слов — express 180, Zásilkovna, Praha]

[H3] Časté dotazy o našem katalogu — ссылка на FAQ S11]

[Disclaimer] — стандартный PML disclaimer block
```

#### 1.7.2 Typography для prose

```css
.prose h3 { font: Playfair Display Bold 28/32; color: forest.700; margin-top: 48; }
.prose p  { font: Inter Regular 16/26; color: forest.700@90%; margin-top: 16; }
.prose ul { padding-left: 24; margin-top: 16; }
.prose li { font: Inter Regular 16/26; color: forest.700@90%; margin-top: 8; }
.prose a  { color: forest.700; text-decoration: underline; text-underline-offset: 3; }
.prose a:hover { color: grass.500; }
.prose strong { font-weight: 600; color: forest.900; }
.prose blockquote { border-left: 3px grass.500; padding-left: 24; font-style: italic; color: forest.700@70%; }
```

#### 1.7.3 Внутренние ссылки в тексте

Strategic anchor text, ≥ 12 internal links к:
- Color pages × 4 (`/kratom/zeleny`, `/kratom/bily`, `/kratom/cerveny`, `/kratom/zluty`)
- Strain pages × 6 (`/kratom/maeng-da`, `/kratom/sumatra`, `/kratom/thajsky`, `/kratom/slon`, `/kratom/borneo`, `/kratom/rurut`)
- Form pages × 2 (`/kratom/prasek`, `/kratom/extrakt`)
- Trust pages × 3 (`/laboratorni-testy`, `/licence`, `/jak-poznat-kvalitni-kratom`)
- Guide pages × 2 (`/pruvodce/co-je-kratom`, `/pruvodce/kratom-zakon-2026`)

> Это **главный internal-PageRank distributor**. См. SEO TZ `02_ARCHITEKTURA_PERELINKOVKA_VAHY.md` § «Cluster-pillar links».

### 1.8 S11 — FAQ accordion

> 5–6 catalog-specific вопросов. Schema FAQPage JSON-LD inline.

Вопросы:

1. **«Jaké odrůdy kratomu nabízíte?»**
   > V našem katalogu najdete kratomové produkty z hlavních pěstebních oblastí: Indonésie (Sumatra, Borneo), Thajsko, Vietnam. Aktuálně máme skladem 8 odrůd v různých barvách žilky — zelená, bílá, červená. Žlutá je v procesu licencování...

2. **«Co znamená barva žilky — zelený, bílý, červený?»**
   > Barva žilky určuje **profil mitragyninu a 7-hydroxymitragyninu** a způsob zpracování listu. Zelený kratom je sušený standardním způsobem, bílý je sklizen z mladších listů, červený prochází fermentací...

3. **«Které odrůdy jsou pro začátečníky?»**
   > Pro nové zákazníky doporučujeme začít s **standardními zelenými odrůdami** — např. Zelená Maeng Da nebo Zelená Sumatra. Jsou to klasické odrůdy se zaznamenanou genetikou a stálým profilem. Vždy konzultujte návod k použití na obalu...

4. **«Jaký je rozdíl mezi práškem a extraktem?»**
   > Prášek je sušený a mletý list — standardní forma. Extrakt je koncentrovaná tekutina (10× a vyšší koncentrace mitragyninu)... Vždy začínejte nižší dávkou.

5. **«Jak se dostanu k laboratorním testům?»**
   > Každý produkt má svůj COA (Certificate of Analysis) ke stažení v PDF. Hledejte tlačítko «Stáhnout COA» na stránce produktu nebo navštivte naši stránku [`/laboratorni-testy`](/laboratorni-testy)...

6. **«Mohu objednat bez registrace?»**
   > Ano. Při dokončení objednávky můžete pokračovat jako host nebo se zaregistrovat. Registrace má výhody — uložené adresy, historie objednávek, snadné předplatné...

### 1.9 JSON-LD schemas

#### 1.9.1 CollectionPage

```json
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Kratom — prášek a extrakt, laboratorně testovaný",
  "description": "Specializovaný PML e-shop kratomu...",
  "url": "https://vivadzen.com/kratom",
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": 8,
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "url": "https://vivadzen.com/kratom/kratom-extrakt-10ml-zeleny"
      },
      // ... остальные 7 товаров
    ]
  },
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "Domů", "item": "https://vivadzen.com/" },
      { "@type": "ListItem", "position": 2, "name": "Kratom", "item": "https://vivadzen.com/kratom" }
    ]
  }
}
```

#### 1.9.2 FAQPage

```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Jaké odrůdy kratomu nabízíte?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "V našem katalogu najdete kratomové produkty..."
      }
    },
    // ... остальные 5 вопросов
  ]
}
```

> ⚠️ **Never include `MerchantReturnPolicy`, `Offer`, or `Product` schemas at the catalog level** — это товарные schemas, они идут на product page. На CollectionPage у нас только `ItemList` + `BreadcrumbList` + `FAQPage`.

---

## §2. `/kratom/{barva}` — цветовые категории

> 4 страницы: `/kratom/zeleny`, `/kratom/bily`, `/kratom/cerveny`, `/kratom/zluty`.

### 2.1 Различия от §1 (`/kratom`)

Используем **тот же template** (§0 + §1), но **изменяем**:

#### 2.1.1 SEO

| URL | H1 | Title | Primary KW |
|---|---|---|---|
| `/kratom/zeleny` | «Zelený kratom — odrůdy, prášek a extrakt» | «Zelený kratom — koupit prášek, šarže testované | Vivadzen» | zelený kratom |
| `/kratom/bily` | «Bílý kratom — odrůdy a profil» | «Bílý kratom — koupit, lab-testováno | Vivadzen» | bílý kratom |
| `/kratom/cerveny` | «Červený kratom — odrůdy a charakteristika» | «Červený kratom — koupit, COA k stažení | Vivadzen» | červený kratom |
| `/kratom/zluty` | «Žlutý kratom — vyvážený profil» | «Žlutý kratom — připravujeme | Vivadzen» | žlutý kratom |

> Note: meta-description у каждой страницы тоже уникален — generic «Specializovaný PML...» только в первой строке, остальное — про конкретный цвет.

#### 2.1.2 Hero (S5)

**Зелёная категория (как пример):**

- **Hero left:**
  ```
  PODLE BARVY ŽILKY                                       ← overline grass.500
  Zelený kratom                                           ← H1 Playfair Bold 56 cream.100
  Klasický profil. Standardní zpracování. Široká nabídka. ← deck body.lg
  [ Procházet zelené odrůdy → ]
  ```

- **Hero right (info card):**
  Mini-info specific к зелёному:
  ```
  ZELENÝ KRATOM
  ─────────────
  Odrůd skladem                4
  Mitragynin profil       1.2 – 1.6 %
  Aktuální šarže               4
  Doporučená denní dávka   3 – 5 g
  ```
  > ⚠️ «Doporučená denní dávka» — это reference к **§ 33e návod к použití**, см. файл 6. Безопасно показывать как cite из обязательного package insert.

- **Background tint:** не чистый forest.700, а с лёгким green-shift — например, `forest.700` основной + 4% gradient `grass.500@4%` сверху-справа.

**Цветовая дифференциация по 4 страницам:**

| Color page | Hero gradient hint | Overline color |
|---|---|---|
| Zelený | `grass.500@4%` зелёный nuance | `grass.500` |
| Bílý | clean `forest.700` без оттенка + cream highlight | `cream.300` |
| Červený | `terracotta.500@4%` тёплый shift | `terracotta.500` |
| Žlutý | `amber.500@4%` золотой shift | `amber.500` |

#### 2.1.3 ProductGrid

- Auto-filter by color (default selected chip в S6 → «Zelený» active).
- Default sort: doporučené.
- 4 продукта на запуске для зелёного → grid 3-col не заполнит fully → ставим 1 placeholder в 4-м слоте.

#### 2.1.4 S9 — Cross-link block (изменения)

**Sub-block A: «Ostatní barvy»** (вместо «Podle barvy»):
- Текущая категория (Zelený) — **disabled/non-clickable**.
- Остальные 3 (Bílý, Červený, Žlutý) — кликабельны.

**Sub-block B: «Odrůdy v této barvě»** (вместо «Podle odrůdy»):
- Только strain'ы, доступные в этом цвете:
  - Zelená Maeng Da → `/kratom/zelena-maeng-da` (product page, не strain hub)
  - Zelená Sumatra → ...
  - Zelený thajský → ...
  - Zelený Rurut → ...

> Это создаёт **siloed cluster**: главный каталог → color category → product. Plus боковая ссылка на strain hub (если есть конкретный strain в этом цвете и отдельный hub для него).

#### 2.1.5 S10 — SEO text (color-specific)

900–1200 слов, структура:

```
[Lead — 80 слов о категории зелёного кратома]

[H3] Co je zelený kratom?
[150 слов — botanika + жилка + способ зпрацование]

[H3] Profil mitragyninu zeleného kratomu
[180 слов — без обещаний эффектов, только данные + ссылки на конкретные COA]

[H3] Odrůdy zeleného kratomu, které nabízíme
[200 слов — 4 strain'а описание + ссылки на product pages]

[H3] Jak se zelený kratom liší od bílého a červeného?
[180 слов — сравнение + cross-link на другие color pages]

[H3] Forma — prášek vs extrakt vs nano
[100 слов — какие формы у нас в зелёном]

[H3] Naše standardy pro zelený kratom
[120 слов — lab testing, COA, šarže]

[H3] Související odrůdy a barvy
[60 слов + chip-row links]

[Disclaimer PML standard]
```

#### 2.1.6 FAQ (color-specific)

3–4 вопроса именно про этот цвет:
- «Čím se zelený kratom liší od ostatních barev?»
- «Jak skladovat zelený kratom?»
- «Jaký zelený kratom doporučujete pro začátečníky?» (без обещаний эффектов!)
- «Mám zelený kratom v lednici?» (нет — komnatne teplo)

### 2.2 Дифференциация на 4 страницах — мини-блок «O barvě»

> Уникализирующий блок размещаем **между S5 hero и S6 chip-row** (НЕ в SEO-тексте, а в основном content area), чтобы пользователь сразу видел разницу.

Layout: 60/40 split desktop.

**Left col (text 60%):**
```
[H2] O zeleném kratomu
[3 abzac'a по 60–80 слов каждый — botanika, жилка, profil]
```

**Right col (image 40%):**
- Фото листа конкретного цвета (см. файл 2 §3.2 — category glyphs, но crop 4:3 instead 1:1).
- Caption: «Zelený kratom — Mitragyna speciosa, jak vypadá list před zpracováním».

**Background:** `cream.200`, radius.xl 32, padding 48 — карточка-блок.

Этот блок **отличает 4 страницы драматически** даже при одинаковом template — Google видит уникальный 240-словный текст + уникальное изображение + уникальный H2. Это **anti-cannibalization key**.

---

## §3. `/kratom/{forma}` — формы (prasek, extrakt, nano)

### 3.1 URL и SEO

| URL | H1 | Primary KW |
|---|---|---|
| `/kratom/prasek` | «Kratomový prášek — všechny odrůdy» | kratom prášek |
| `/kratom/extrakt` | «Kratomové extrakty — koncentrované tekutiny» | kratom extrakt |
| `/kratom/nano` | «Kratom Nano — jemně mletý» | kratom nano |

### 3.2 Template

> Тот же §0/§1 template, но:
> - **Auto-filter by form** (chip «Prášek» / «Extrakt» / «Nano» в S6 active by default).
> - **Hero** — info-card stat показывает только эту форму:
>   ```
>   PRÁŠEK
>   ─────
>   Odrůd skladem        6
>   Balení               25 g / 50 g
>   Mitragynin profil    1.0 – 1.6 %
>   ```
> - **Mini-block «O formě»** под hero (аналог §2.2):
>   - «Co je kratomový prášek» — botanika, помол, разлика jemné/hrubé/nano.
> - **S10 SEO text** про форму (700–900 слов).
> - **S9 Cross-link** — связывает с цветами и strain'ами этой формы.

### 3.3 Особенность для `/kratom/extrakt`

> Extrakt — это специальная категория с **более строгими PML-предупреждениями** (концентрированная форма).

- Под hero добавляем **«Důležité bezpečnostní upozornění»** карточку (см. файл 3 § Legal-Safety-Block):
  ```
  ⚠️ EXTRAKT je koncentrovaná forma kratomu.
  Obsah mitragyninu je 10× a vyšší než u prášku.
  Vždy začínejte minimální dávkou (1–2 kapky).
  Konzultujte návod k použití na obalu.
  ```
  - bg `terracotta.500@10%`, border-left 4px `terracotta.500`, padding 24, radius.lg.
  - Иконка `alert-triangle` (Lucide) `terracotta.500` 24×24.

---

## §4. `/kratom/{odruda}` — strain-хабы

### 4.1 URL и SEO

| URL | H1 | Primary KW |
|---|---|---|
| `/kratom/maeng-da` | «Maeng Da — klasická odrůda kratomu» | maeng da kratom |
| `/kratom/sumatra` | «Sumatra kratom — z indonéských plantáží» | sumatra kratom |
| `/kratom/borneo` | «Borneo kratom — odrůdy a charakteristika» | borneo kratom |
| `/kratom/thajsky` | «Thajský kratom — Mitragyna speciosa z Thajska» | thajský kratom |
| `/kratom/bali` | «Bali kratom — odrůdy z Indonésie» | bali kratom |
| `/kratom/slon` | «Slon kratom — Elephant variety» | slon kratom |
| `/kratom/rurut` | «Rurut kratom — speciální odrůda» | rurut kratom |

### 4.2 Особенности strain-hub'а

Strain-hub имеет **более информационный характер** чем color page (color = классификация, strain = ботаническая odrůda с origin story). Расширения:

#### 4.2.1 Hero info-card

Вместо stat-only — origin block:

```
ODRŮDA · MAENG DA
──────────────────
Země původu      Thajsko / Indonésie
Tradiční použití před 1942 (lokálně)
Mitragynin profil    1.2 – 1.7 %
Naše šarže           4 aktivní
```

#### 4.2.2 Расширенный «O odrůdě» блок

Под hero — большая ботаническая карточка (как §2.2, но в 2 раза больше):

- **Left col (60%):** 4–5 параграфов, 350–450 слов:
  - Происхождение и история
  - Ботанические характеристики
  - Способ выращивания (плантация vs дикорастущая)
  - Профиль мирагининна
  - Доступные цвета этой одруды

- **Right col (40%):**
  - Image: фото плантации / листа крупным планом (см. файл 2 §3.4 — blog hero shoot).
  - Caption.
  - Mini-fact list под фото.

#### 4.2.3 Strain-specific filter (S6)

Chip-row показывает доступные **цвета** этой одруды (вместо общих цветов):

```
[ Vše Maeng Da ]  [ Zelená MD ]  [ Bílá MD ]  [ Červená MD ]
```

#### 4.2.4 S9 Cross-link

**Sub-block A: «V této odrůdě»** (цвета):
- Zelená Maeng Da → product page
- Bílá Maeng Da → product page
- (если бы была Červená Maeng Da → product page)

**Sub-block B: «Ostatní odrůdy»**:
- Sumatra, Borneo, Thajský, Bali, Slon, Rurut → strain hubs

#### 4.2.5 S10 SEO text (strain-specific, longer)

1200–1600 слов, более информационный (это strain hub — образовательный контент):

```
[Lead 100 слов]

[H3] Co je Maeng Da?
[Botanic + history — 250 слов]

[H3] Profil mitragyninu Maeng Da
[Konkrétní data + comparison s ostatními — 200 слов]

[H3] Jak se Maeng Da liší od jiných odrůd?
[Comparative — 200 слов + cross-links na ostatní strain hubs]

[H3] Naše Maeng Da odrůdy
[Detail product list — 250 слов + product page links]

[H3] Lab-testy Maeng Da
[Naše standardy — 180 слов + COA links]

[H3] Související články a průvodce
[Cross-link na content hub — 100 слов]

[Disclaimer]
```

### 4.3 Strain-specific JSON-LD

В дополнение к `CollectionPage` — добавляем `Article` schema для strain hub (потому что это hybrid страница — каталог + образовательная):

```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "Maeng Da — klasická odrůda kratomu",
  "image": "https://vivadzen.com/assets/...",
  "datePublished": "2026-01-15",
  "dateModified": "2026-03-12",
  "author": {
    "@type": "Person",
    "name": "{garant Vivadzen}",
    "url": "https://vivadzen.com/o-nas#garant"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Vivadzen",
    "logo": { "@type": "ImageObject", "url": "https://vivadzen.com/assets/brand/logo-vivadzen-primary.svg" }
  },
  "articleBody": "[full text of S10]"
}
```

---

## §5. `/hledat?q=…` — Поиск

### 5.1 Структура страницы

Используем тот же template §0, но:

- **Hero S5:** компактнее (h=240), без info-card.
  - Overline: «VÝSLEDKY VYHLEDÁVÁNÍ»
  - H1: «Hledáte: «{query}»»
  - Sub: «Nalezeno {N} produktů a {M} článků»
- **S6 chip-row:** показывает только **smart-filters** (Cena, Skladem) — не color/strain (это уже в результатах).
- **S7 grid:** result product cards.
- **S8 «Připravujeme»:** показываем placeholder если в results <= 3 совпадения.
- **S9 «Snad jste hledali»:** suggestion chips с typo-correction.
- **S10 SEO text:** **нет** (search pages — `noindex`).
- **S11 FAQ:** нет.

### 5.2 No-results state

Если `0` совпадений:

```
┌─────────────────────────────────────────────┐
│                                             │
│   [Иконка search-X 64×64 cream.300]         │
│                                             │
│   Pro «{query}» nenacházíme žádné výsledky  │
│                                             │
│   Zkuste:                                   │
│   • Jinou odrůdu — «Maeng Da», «Sumatra»    │
│   • Barvu — «zelený», «bílý», «červený»     │
│   • Procházet [všechny produkty →]          │
│                                             │
│   Nebo nám [napište →] a poradíme.          │
│                                             │
└─────────────────────────────────────────────┘
```

- Centered, max-width 480, padding 64.
- Background: cream.200, radius.xl 32.

### 5.3 SEO для search

```html
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="https://vivadzen.com/kratom"> <!-- canonicalize to main catalog -->
```

> Search-страницы **не** должны индексироваться (генерируют дубли). Canonical → главный каталог.

---

## §6. ОБЩИЕ КОМПОНЕНТЫ — ДЕТАЛИ

> Дополнения к файлу 3 для категорийных страниц.

### 6.1 Sticky chip-row S6

CSS:
```css
.sticky-chips {
  position: sticky;
  top: 64px; /* под header */
  z-index: 50;
  backdrop-filter: blur(8px);
  background: rgba(249, 244, 236, 0.95); /* cream.100 @ 95% */
  border-bottom: 1px solid var(--color-cream-300);
  padding: 12px 0;
}
```

JS behavior:
- При клике на chip → URL param update (`?color=zeleny`) без full page reload (SPA-like).
- ProductGrid update via JS filter (если данные на странице — все 8 товаров в SSR-HTML, фильтр CSS via `data-color` attribute).
- ИЛИ серверный rerender (если товаров много) — рекомендуется при ≥ 50 товаров в каталоге.

### 6.2 FilterSidebar mobile (off-canvas)

CSS:
```css
.filter-drawer {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 320px;
  background: var(--color-cream-100);
  transform: translateX(100%);
  transition: transform 320ms ease-out;
  z-index: 100;
  overflow-y: auto;
  padding: 24;
}

.filter-drawer[data-open="true"] {
  transform: translateX(0);
}

.filter-overlay {
  position: fixed;
  inset: 0;
  background: rgba(20, 32, 27, 0.4); /* forest.900 @ 40% */
  z-index: 90;
  opacity: 0;
  pointer-events: none;
  transition: opacity 200ms;
}

.filter-overlay[data-open="true"] {
  opacity: 1;
  pointer-events: auto;
}
```

Header drawer:
```
FILTRY (3)                              [ × ]
─────────────────────────────────────────────
[Vyčistit vše]                  [Použít (8)]
```
- ✕ — close.
- «Použít (8)» — show count of matching products live, apply on click.

### 6.3 «Připravujeme» card variant

Visual difference от live product:

```
┌────────────────────────────┐
│  ┌──────────────────────┐  │
│  │                      │  │
│  │   [blur placeholder] │  │  ← image area, opacity 0.6
│  │                      │  │
│  │   [BRZY badge top-r] │  │  ← terracotta.500
│  │                      │  │
│  └──────────────────────┘  │
│                            │
│  ● Bílý kratom · Thai      │  ← eyebrow, normal
│  Bílá Thai Elephant         │  ← title, normal
│  Mitragynin — TBD           │  ← spec line, muted
│                            │
│  [ Upozornit při skladu ]  │  ← btn.outline forest, full width
└────────────────────────────┘
```

Стили:
- Image: blurred placeholder PNG (см. файл 2 §3.7) + bg `cream.300`.
- BRZY badge: bg `terracotta.500`, text `cream.100`, position absolute top-right, padding 4×10, radius.full, Inter SemiBold 11 UPPERCASE.
- Title: same as live, ink.900.
- Spec: italic, color forest.700@60%.
- Button: btn.outline forest, full-width, text «Upozornit při naskladnění».
- На click → modal с email-input (см. файл 3 § Newsletter).

### 6.4 Loading states

**Skeleton placeholder для grid** (при slow connection / pagination load):

- Cards с pulsing animation:
  ```css
  .skeleton {
    background: linear-gradient(90deg,
      var(--color-cream-200) 0%,
      var(--color-cream-300) 50%,
      var(--color-cream-200) 100%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s infinite;
  }
  @keyframes skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
  ```

Показываем для:
- Image area (200×200 ratio)
- Title line (60% width)
- Spec line (40% width)
- Price (30% width)
- Button area

### 6.5 Empty state grid

Если все фильтры применены и **0 совпадений**:

```
┌────────────────────────────────────────────┐
│                                            │
│   [Иконка filter-x 48×48 cream.400]         │
│                                            │
│   Žádné produkty neodpovídají filtrům      │
│                                            │
│   Zkuste rozšířit hledání:                 │
│                                            │
│   [ Vyčistit všechny filtry ]              │
│                                            │
└────────────────────────────────────────────┘
```

Centered, padding 64 vertical, bg cream.100, radius.xl.

---

## §7. PERFORMANCE — каталог

### 7.1 LCP target

- Hero S5 — preloaded.
- Grid first 6 cards — image `loading="eager"` для first row, `loading="lazy"` для следующих.
- Лazy-загружаем S8, S9, S10 (IntersectionObserver).

### 7.2 Filter UX optimization

- Filter changes — `requestAnimationFrame` debounced, не каждый клик re-fetch.
- URL params sync — `history.replaceState` (не push) для chip changes; `pushState` только для значительных изменений (новая категория).
- Persisted state — `localStorage` сохраняет last-used filters, восстанавливает на следующий visit (опц., через explicit «Запомнить?» pop-up за accessibility).

### 7.3 Pagination strategy

- **Default:** load-more кнопка (UX friendly).
- **Fallback:** numbered pagination (SEO-friendly, JS-less).
- Оба — SSR-pre-rendered.
- URL для pagination: `/kratom?page=2`, canonical = same.
- `rel="next"` / `rel="prev"` link tags для crawlers.

---

## §8. A11Y — каталог

- **Filter sidebar mobile:** trap focus в drawer, ESC закрывает.
- **Chip-row:** keyboard nav arrow-keys между chip'ами.
- **Sort dropdown:** native `<select>` для best a11y (или ARIA combobox если кастом).
- **Product grid:** keyboard tab order совпадает с visual flow.
- **«Načíst další»:** button focus возвращается на первый новый item после load.
- **Filter counter** в drawer: `aria-live="polite"` обновление.

---

## §9. ПРОМПТ ДЛЯ CLAUDE — каталог-template

```
Ты — senior frontend дизайнер. Создай **универсальный template артефакт**
`vivadzen-catalog-template.html` — single-file React + Tailwind CDN +
Lucide + Google Fonts (Playfair Display + Inter).

Это **template для всех category pages**: /kratom, /kratom/zeleny,
/kratom/maeng-da и т.д. — один HTML файл с props/state для customizing
hero текстов и фильтров.

В контексте уже:
- 01_DESIGN_SYSTEM.md
- 03_GLOBALNI_KOMPONENTY.md (Header, Footer, ProductCard, FilterSidebar)
- 02_ASSETS_A_NANO_BANANA.md (image paths)
- Image 3 reference (categories grid)

Прикладываю файл `05_STRANKA_KATALOG_KATEGORIE.md` — полная спецификация
4 типов страниц.

Задача — реализуй для конкретной страницы **`/kratom` главный каталог**
(§1 этого файла):

1. S1–S13 секции (см. §0.1).
2. Hero S5 — split 70/30 с info card.
3. Sticky chip-row S6 — 7 chips, JS-фильтр.
4. Main S7 — FilterSidebar 280 + ProductGrid 3-col.
5. Use **placeholder data** для 8 товаров (см. §1.4.5).
6. FilterSidebar — 8 групп (§1.4.2), mobile = off-canvas drawer.
7. Sort dropdown + active filter chips.
8. Load-more button + numbered pagination.
9. «Připravujeme» S8 — 6 placeholder cards.
10. Cross-link S9 — color + strain blocks.
11. SEO text S10 — placeholder 800 слов на чешском (можешь сократить
    до 200 слов с пометкой "[lorem ipsum]" если хочешь).
12. FAQ S11 — 6 вопросов из §1.8.
13. Newsletter S12.
14. JSON-LD CollectionPage + FAQPage inline в <head>.
15. Mobile breakpoints (см. §6.2 для drawer).

Все тексты — чешский (НЕ кириллица).
Не делай fetch к API — все данные inline.
Filter chips, sort, drawer — vanilla JS state без библиотек.
A11y: skip-link, focus rings, aria-labels.

Артефакт должен open в браузере, фильтровать товары JS-ом без reload.
```

> Этот промпт + 05 + 01 + 03 + ассеты → один Claude-чат → артефакт `catalog.html` готов.
>
> Затем — **второй артефакт** для color category (`/kratom/zeleny`) с дельтами из §2.
>
> Третий — strain hub (`/kratom/maeng-da`) с дельтами из §4.
>
> Все три деплоятся одним template-движком в production (Next.js dynamic route).

---

## §10. DEFINITION OF DONE — каталог-family

### 10.1 Главный каталог `/kratom`

- [ ] Hero с двух-цветным H1 «Kratom — prášek a extrakt» + info card 4 stat
- [ ] Sticky chip-row с 7 chips (Vše / 4 цвета / Maeng Da / Extrakt)
- [ ] FilterSidebar 8 групп (Barva / Odrůda / Forma / Balení / Mitragynin / Dostupnost / Cena / Hodnocení)
- [ ] Mobile drawer с trap focus + counter live update
- [ ] ProductGrid 3-col с 8 товарами + правильные mini cards (см. файл 3 §3)
- [ ] Sort dropdown с 6 опциями
- [ ] Active filter chips row + Vyčistit vše
- [ ] Load-more кнопка + numbered pagination fallback
- [ ] «Připravujeme» секция с 6 placeholder cards + lead capture
- [ ] Cross-link block: 4 color cards + 6 strain chip-cards
- [ ] SEO text 1100–1500 слов на чешском с ≥ 12 internal links
- [ ] FAQ 6 вопросов + FAQPage schema
- [ ] Newsletter band
- [ ] JSON-LD CollectionPage + ItemList + BreadcrumbList + FAQPage
- [ ] LCP < 2.0s, filter changes < 100ms

### 10.2 Color categories `/kratom/{barva}`

- [ ] Hero с color-shifted gradient + color-specific overline
- [ ] H1 уникальный для каждого из 4 цветов
- [ ] Info card специфичная stat (mitragynin profile, šarží скоро)
- [ ] Mini-block «O barvě» 60/40 с уникальным текстом 240 слов + image
- [ ] Default chip selected = current color
- [ ] Filter auto-applied на color
- [ ] Cross-link: 3 ostatní barvy + strain'ы в этом цвете
- [ ] SEO text 900–1200 слов уникальный для каждой страницы
- [ ] FAQ 3–4 color-specific
- [ ] JSON-LD CollectionPage с custom URL

### 10.3 Strain hubs `/kratom/{odruda}`

- [ ] H1 уникальный + origin info card
- [ ] Большой «O odrůdě» блок 350–450 слов + plantation image
- [ ] Chip-row показывает доступные цвета этой одруды
- [ ] Cross-link: 6 ostatní strain'ов + цвета в этой одруде
- [ ] SEO text 1200–1600 слов informational
- [ ] JSON-LD CollectionPage + Article (dual schema)
- [ ] Author byline garant Vivadzen
- [ ] FAQ 3–4 strain-specific

### 10.4 Search `/hledat`

- [ ] noindex meta + canonical к /kratom
- [ ] H1 dynamic «Hledáte: «{query}»»
- [ ] No-results state с suggestions
- [ ] Suggested products row («Snad jste hledali»)
- [ ] Простой filter — только Cena, Skladem
- [ ] Без S10 SEO text, без S11 FAQ
