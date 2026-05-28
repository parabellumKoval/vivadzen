# VIVADZEN — DESIGN TZ
## Файл 6/9 — Карточка товара (Product Detail Page)

> Самая «нагруженная» страница. Здесь живут все обязательные комплаенс-блоки PML, COA-таблица, модификации 25/50 g, отзывы с фото, Q&A, подписка. Это шаблон для **всех 8 live товаров** + апгрейдится с тем же шаблоном при наполнении placeholder.

---

## 1. СТРУКТУРА СТРАНИЦЫ (сверху вниз)

```
[Header]
[Breadcrumbs: Domů › Kratom › Zelený kratom › Zelená Maeng Da]
[Section 1: Hero товара — галерея + покупка] ← above the fold critical
[Section 2: Tab navigation sticky (Popis · Laboratorní test · Návod · Bezpečnostní informace · Recenze · Otázky)]
[Section 3: Popis produktu]
[Section 4: Laboratorní test této šarže] ← обязательный COA-блок
[Section 5: Návod k použití]
[Section 6: Důležité bezpečnostní a spotřebitelské informace] ← обязательный legal block
[Section 7: Doprava a platba]
[Section 8: Recenze a hodnocení (с фото)]
[Section 9: Otázky a odpovědi (Q&A interní)]
[Section 10: Související produkty]
[Section 11: Footer-CTA mini]
[Footer]

[Sticky mobile bottom bar при скролле ниже hero: цена + Do košíku]
```

---

## 2. SECTION 1 — HERO ТОВАРА (Above the fold)

### 2.1. Layout (desktop)
- Container: `container`
- Background: `paper`
- Padding-top: 32 px (после breadcrumbs)
- Grid: 2 колонки 55/45 — галерея слева, buy-box справа
- Mobile: галерея сверху, buy-box ниже

### 2.2. Галерея (левая колонка)

**Структура:**
- Большое фото 1:1, max-width 600 px
- Под ним — thumbnails (4–6 фото) горизонтально 80×80 px
- Thumbnail active — border 2 px `forest`
- Hover на thumb — preview меняется
- Клик на главное фото → lightbox (full-screen zoom)
- Опц. иконка `camera` на главном фото → «Fotoreview» (модалка со всеми фотоотзывами)

**Бейджи поверх главного фото (top-left stack):**
- «Skladem» (lime pilule)
- «Lab-testováno · šarže VD-2026-014» (outlined lime)

**Опц. правый верх:**
- Wishlist `heart` button
- Share `share` button (открывает small share-popover: link, FB, X, copy)

### 2.3. Buy-box (правая колонка)

```
┌────────────────────────────────────────────────┐
│  ● Red vein · Borneo            [icon shield-check] LICENCE MZ ČR  │ ← eyebrow + license chip
│  Červená Maeng Da Kratom Prášek                ← H1 Playfair regular 36 px
│                                                 │
│  ★★★★★ 4,9 · 124 recenzí · 8 otázek          ← link к секциям ниже (scroll-to)
│                                                 │
│  Mitragynin 1,42 % · jemně mletý · šarže VD-2026-014  ← metadata
│  ──────────────────────────────────────────────│
│                                                 │
│  BALENÍ                                         │ ← label
│  [25 g  290 Kč] [50 g  490 Kč]                  │ ← сегментированный переключатель (см. ниже)
│                                                 │
│  POČET     [−] 1 [+]                            │ ← stepper
│                                                 │
│  490 Kč                                         │ ← price-lg (live updates)
│  (9,80 Kč / g)                                  │ ← unit price small, ink-soft
│                                                 │
│  [   PŘIDAT DO KOŠÍKU   →   ]                   │ ← primary CTA amber glow, height 56 px, full width
│  [   Předplatné -10 %    ↻  ]                   │ ← secondary outline с иконкой repeat
│                                                 │
│  ──────────────────────────────────────────────│
│  [icon truck]  Doručení po ČR · Express 180 min Praha & Ostrava  │ ← trust line
│  [icon shield-check]  Bezpečná platba · 14 dní na vrácení        │
│  [icon store]  Osobní odběr Praha — zdarma, do 60 minut          │
│  ──────────────────────────────────────────────│
│  [Acceptované platby — иконки: Visa MC ApplePay GooglePay QR Dobírka Převod]
│                                                 │
│  [BADGE 18+ компактный]                          │
│  Není určeno osobám mladším 18 let.             │ ← warning, body-sm, ink-soft
└────────────────────────────────────────────────┘
```

### 2.4. Детали buy-box

**Eyebrow с цветным кружком:**
- Кружок vein 14 px (чуть крупнее, чем в mini-карточке) + текст «Red vein · Borneo»
- Inter 500, 13 px, ink-soft, tracking 0.04em
- Справа той же строки — chip «Licence MZ ČR» (outlined `lime`, иконка `shield-check`)

**H1:**
- Playfair Display regular 36 px desktop / 28 px mobile (display-style для важности, но НЕ italic — это товарная страница, нужна функциональная серьёзность)
- Color `ink`

**Рейтинг-линия:**
- 5 звёзд `star` amber, размер 18 px
- Текст 14 px Inter 500: «4,9 · 124 recenzí · 8 otázek»
- Цифры — линки скролл-к-секции (recenzí → Section 8, otázek → Section 9)

**Metadata:**
- Inter 500, 14 px, color `ink-soft`
- «Mitragynin 1,42 % · jemně mletý · šarže VD-2026-014»
- Цифры — `tabular-nums`
- «šarže VD-2026-014» — link к COA блоку (Section 4)

**Переключатель балний 25 g / 50 g:**
- Сегментированная кнопка на всю ширину buy-box
- 2 опции одинаковой ширины, height 56 px
- Каждая опция:
  - Верхняя строка: «25 g» Inter 600 16 px
  - Нижняя: «290 Kč» Inter 500 14 px color (active) / `ink-soft` (inactive)
- Active: фон `forest`, текст `paper`
- Inactive: фон `cream-soft`, border 1 px `border-light`, текст `ink`
- При смене — большая price-lg + unit price + кнопка Купить меняются (без перезагрузки)
- Hover на inactive: фон `cream-deep`

**Stepper количества:**
- 3-element layout: [−] [input 56 px] [+]
- Height 48 px
- [−][+] — Button.icon outline, иконки `minus`/`plus`
- Disabled [−] на 1
- Max — 99 (или ваш бизнес-лимит)

**Price-lg:**
- Inter 700, 36 px desktop / 28 px mobile
- Color `ink`
- `tabular-nums`
- Если есть скидка: рядом старая цена `price-old` (line-through, mist)

**Unit price:**
- Inter 400, 13 px, color `ink-soft`
- Формат: «(9,80 Kč / g)» — для порошка
- Для extract: «(... Kč / ml)»

**Primary CTA «Přidat do košíku»:**
- Button.primary large (height 56 px)
- Шрифт Inter 600 16 px
- Текст + иконка `arrow-right`
- Glow `glow-amber`
- При click — анимация: кнопка превращается в [✓ Přidáno] на 1.2 секунды, потом возвращается; одновременно — toast cart-added в правом верхнем углу, число в иконке корзины +1
- При sold out → disabled + кнопка меняется на «Upozornit při naskladnění» (outlined)

**Secondary CTA «Předplatné -10%»:**
- Button.outline, height 48 px
- Иконка `repeat` слева + «Předplatné −10 %»
- Click → бот.шит подписки (см. §11)

**Trust-линии:**
- 3 строки, каждая icon + текст
- Icon 18 px, color `lime-deep`
- Текст Inter 500 14 px ink

**Иконки платежей:**
- Все SVG из `/payments/` (Visa, MC, Apple Pay, Google Pay) + кастомные icon QR/Dobírka/Bank
- Каждая 28×18 px (стандартные пропорции платёжных logo), gap 8 px
- Цветные на fundación `cream-soft` chip (если фон самой страницы тоже paper, иначе можно прямо)

**18+ badge:**
- Компонент Badge.18plus
- Рядом: «Není určeno osobám mladším 18 let.» (Inter 500, 13 px, `ink-soft`)

### 2.5. Sticky mobile bottom bar (только mobile, появляется при скролле ниже hero)
- Sticky bottom, фон `paper`, border-top `border-light`, shadow `shadow-elevated`
- Контент:
  ```
  [фото 40×40] Červená Maeng Da     490 Kč    [Přidat →]
                25 g | 50 g
  ```
- Высота 64 px
- Тап на «Přidat» — добавление + toast + бейдж корзины
- Тап на товар → scroll-to-top

---

## 3. SECTION 2 — TAB NAVIGATION (sticky)

После скролла ниже hero — тонкая sticky навигация по секциям страницы.

- Background `paper`, border-bottom `border-light`, blur backdrop
- Высота 56 px
- Container `container`
- 6 ссылок (anchor scroll):
  ```
  Popis · Laboratorní test · Návod · Bezpečnostní informace · Recenze (124) · Otázky (8)
  ```
- Active tab (по scroll-position) — underline `amber` 2 px + bold
- Mobile: horizontal scroll, fade на краях

---

## 4. SECTION 3 — POPIS PRODUKTU

- Container `container-narrow` (max 720 — длинный текст)
- Background `paper`
- Padding 64 vertical
- H2 Playfair regular 32 px: «Popis produktu»
- Body 180–280 слов фактологии:
  - Štamm: Maeng Da
  - Цвет vein + регион (Borneo)
  - Обработка/сушка
  - Помол (jemně / nano / hrubě)
  - Аромат, цвет, текстура
  - Фасовка 25 g / 50 g
  - НЕТ эффектов, НЕТ дозировок-рекомендаций (это в Návod, §5, где это требует закон)
- Внутри текста — 2–3 внутренних линка: на категорию (Červený kratom), на strain-хаб (Maeng Da), на /encyklopedie/mitragyna-speciosa
- Под текстом — info-карточка «Označení a původ»:
  ```
  Mitragyna speciosa
  Region původu: Borneo, Indonésie
  Zpracování: tradiční sušení s krátkou fermentací
  Šarže: VD-2026-014
  Datum testu: 12. 03. 2026
  Trvanlivost: 24 měsíců od data výroby
  Skladování: sucho, chlad, mimo dosah osob mladších 18 let
  ```
  - Background `cream-soft`, radius `rounded-lg`, padding 24
  - Каждая строка: label Inter 500 14 px `ink-soft` + value Inter 500 15 px `ink`

---

## 5. SECTION 4 — LABORATORNÍ TEST TÉTO ŠARŽE (COA блок — обязательный, как вы дали)

### 5.1. Layout
- Background `cream`
- Container `container-narrow`
- Padding 80 vertical

### 5.2. Структура (точно по вашему ТЗ)

```
┌──────────────────────────────────────────────────────────────┐
│  [icon flask-conical, 32 px, lime-deep]                       │
│  AKREDITOVANÁ LABORATOŘ VŠCHT PRAHA                          │ ← eyebrow lime-deep
│  Laboratorní test této šarže                                 │ ← H2 Playfair regular
│                                                                │
│  Šarže  VD-2026-014                                          │ ← Inter 600 18 px
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ PARAMETR              │ HODNOTA        │ STATUS          ││ ← table header, eyebrow
│  ├──────────────────────────────────────────────────────────┤│
│  │ Obsah mitragyninu     │ 1,42 %         │ ✓ PASS          ││ ← lime PASS chip
│  │ Obsah 7-hydroxymitra. │ 0,008 %        │ ✓ PASS          ││
│  │ Čistota               │ 99,1 %         │ ✓ PASS          ││
│  │ Mikrobiologie         │ Vyhovuje       │ ✓ PASS          ││
│  │                       │ ČSN ISO 21527  │                 ││
│  │ Těžké kovy (Pb,Cd,Hg,As) │ < 0,3 ppm   │ ✓ PASS          ││
│  │ Datum testu           │ 12. 03. 2026   │ VŠCHT Praha     ││
│  └──────────────────────────────────────────────────────────┘│
│                                                                │
│  Plné COA s razítkem laboratoře.                              │ ← caption
│  [📄 Stáhnout COA (PDF)]                                       │ ← Button.outline + icon download
│  [→ Všechny šarže]                                            │ ← link to /laboratorni-testy
└──────────────────────────────────────────────────────────────┘
```

### 5.3. Стилизация таблицы
- Background таблицы `paper`
- Border 1 px `border-light` для всей таблицы и между строками
- Header row: background `forest`, text `paper`, Inter 600 12 px uppercase, padding 16
- Data rows: padding 14, hover `cream-soft`
- Колонки 50/30/20 ширин
- Числа: `tabular-nums`
- PASS chip: пилюля `lime` 15% bg + `lime-deep` text + iconcheck слева, height 24 px

### 5.4. Кнопки под таблицей
- «Stáhnout COA (PDF)» — Button.outline + icon `download` слева
- «Všechny šarže →» — link `forest`, hover underline → /laboratorni-testy

### 5.5. Мобильная адаптация таблицы
- На очень узких экранах — карточный режим: каждая параметр-строка как отдельная мини-карточка с label + value + status в столбик

---

## 6. SECTION 5 — NÁVOD K POUŽITÍ

> Это разрешено законом (это **обязательная** инструкция применения для PML), не маркетинг.

- Background `paper`
- Container `container-narrow`
- Padding 64 vertical
- H2 Playfair regular: «Návod k použití»

**Структура (точно по вашему ТЗ):**

```
1  [number badge 32 px lime soft]   Odměřte 3 g prášku.
                                     Doporučená jednorázová dávka činí 3 g.

2  [number badge]                    Rozmíchejte ve 100–250 ml vody a vypijte.

3  [number badge]                    Maximální doporučená denní dávka činí 10 g.

4  [number badge]                    Skladujte v suchu, chladu a mimo dosah dětí.
```

- Каждый шаг: numbered badge (кружок `lime-deep` solid, число `paper`, 32 px) + текст справа
- Шрифт текста: Inter 500 16 px `ink`, line-height 1.55
- Gap между шагами: `space-6` (24 px)
- Под шагами — note блок (`cream-soft` фон, `lime-deep` left-border 3 px, padding 16):
  - icon `info` lime-deep + текст:
  - «Postupujte výhradně dle uvedeného návodu. Nepřekračujte doporučenou denní dávku. Tento výrobek by se neměl užívat každý den — mezi jednotlivými užitími by měla být přestávka 3 dny.»

---

## 7. SECTION 6 — DŮLEŽITÉ BEZPEČNOSTNÍ A SPOTŘEBITELSKÉ INFORMACE (обязательный блок)

> Этот блок выводится **дословно**, как вы его указали. Это легально требуемый текст для PML, не маркетинг. По объёму он большой — структурируем визуально, но никаких сокращений.

### 7.1. Layout
- Background `forest-deep` (выделение — серьёзный безопасный блок)
- Container `container-narrow`
- Padding 80 vertical
- Цвет текста: `paper-soft-on-dark` (основной); ключевые предупреждения — `amber`

### 7.2. Структура

**Top warning bar (большой, акцентный):**
```
┌─────────────────────────────────────────────────────────────┐
│  [BADGE 18+ компонент, 56 px, terracotta]                    │
│  Není určeno osobám mladším 18 let!                          │ ← H3 Playfair italic, color paper
│  Ukládat mimo dosah osob mladších 18 let!                    │ ← body-lg, color paper-soft-on-dark
└─────────────────────────────────────────────────────────────┘
```
- Border 2 px `terracotta`
- Padding 24
- Radius `rounded-xl`
- Background transparent

**Eyebrow секции:**
- `lime` uppercase: `DŮLEŽITÉ INFORMACE PRO SPOTŘEBITELE`

**H2 секции:**
- Playfair regular, color `paper`: «Důležité bezpečnostní a spotřebitelské informace»

**Lead-параграф (color `paper`, body-lg, italic Playfair):**
```
Užívání tohoto výrobku může poškodit Vaše zdraví.
Dbejte informací pro spotřebitele.
```
- Это центральная фраза. Размещаем outlined, акцентно: фон transparent, border-left 4 px `amber`, padding-left 24, italic

**Список «Obecné informace» (5 bullets):**
- Каждый bullet: иконка `info` 16 px lime + текст Inter 400 15 px color `paper-soft-on-dark`
- Текст (дословно по вашему ТЗ):
  1. Tento výrobek má psychoaktivní účinky. Tento výrobek není potravinou.
  2. Tento výrobek není léčivým přípravkem a nebyl klinicky testován.
  3. Pokud si nejste jisti, zda je tento výrobek pro vás vhodný, konzultujte jeho užití s lékařem.
  4. Není určeno osobám mladším 18 let. Ukládat mimo dosah osob mladších 18 let.
  5. Užívejte v souladu s návodem k použití. Nepřekračujte doporučenou denní dávku.

**Подзаголовок H3 «Účinky» (Playfair regular, color `paper`):**

Body (color `paper-soft-on-dark`, дословно):
```
Při doporučeném dávkování má tento výrobek povzbuzující účinky.
Při překročení doporučeného dávkování má tento výrobek tlumivé účinky.

Užití tohoto výrobku může ovlivnit bdělost, koordinaci pohybů, koordinaci
řeči, rovnováhu, smyslové vnímání, vnímání bolesti, spánek, náladu
a funkci imunitního systému.

Při dlouhodobém užívání vysokých dávek je možný vznik závislosti.
Tento výrobek by se neměl užívat každý den, mezi jednotlivými užitími
by měla být přestávka 3 dny.

Dlouhodobé účinky na lidské zdraví nejsou dostatečně popsány, obzvláště
jde-li o užívání jiné, než je orální užití samotné usušené rostliny.
```

**Подзаголовок H3 «Upozornění» (Playfair regular, color `amber`):**

Warning callout box (border 2 px `amber`, padding 24, radius `rounded-lg`, background transparent):
- Иконка `alert-triangle` 24 px `amber` в начале
- Текст color `paper`, Inter 400 15 px, list:
  1. Neužívejte tento výrobek při, bezprostředně před a v době kratší než 8 hodin před řízením motorového vozidla nebo vykonáváním činnosti, u kterých je vyžadována zvýšená pozornost, schopnost soustředění a koordinace pohybů. Neřiďte motorové vozidlo a nevykonávejte tyto činnosti ani po uplynutí této doby, pokud se cítíte být pod vlivem tohoto výrobku.
  2. Tento výrobek není určen osobám mladším 18 let.
  3. Neužívejte tento výrobek spolu s dalšími psychoaktivními látkami, alkoholem, nikotinem, léky, ani při těhotenství nebo kojení, nebo pokud trpíte duševním onemocněním nebo tělesným onemocněním s poruchou funkce ledvin, jater, srdce a cév.
  4. Existují podezření na otravu kratomem a následné úmrtí.

> ⚠️ **Все эти тексты — копия дословная**, никакой переписки. Кнопка «Read more / Collapsed» НЕ нужна — это legal-обязательный блок, должен быть видим. Можно сделать его collapsable аккордеоном (default open), но не скрывать.

---

## 8. SECTION 7 — DOPRAVA A PLATBA

- Background `cream`
- Container `container`
- Padding 64 vertical
- Grid: 2 колонки 50/50 desktop, stacked mobile

**Левая колонка — Doručení:**
- H3: «Doručení»
- Карточки доставок (3 шт., каждая — icon + title + sub + цена):
  - [icon truck] Standardní doručení po ČR — 1–3 pracovní dny — od 89 Kč (или zdarma od 1 200 Kč)
  - [icon zap amber] Express 180 min — Praha & Ostrava — do 3 hodin — 290 Kč (или ваша цена)
  - [icon store] Osobní odběr Praha — do 60 minut — zdarma
- Bullet: «Kurýr ověří váš věk 18+ při převzetí dle zákona č. 167/1998 Sb.»
- Под карточками: «Více o doručení →» → /doprava-a-platba

**Правая колонка — Platba:**
- H3: «Platba»
- Сетка иконок 2×3:
  - [icon credit-card] Online kartou (Visa, MC, Apple/Google Pay)
  - [icon qr-code] QR platba
  - [icon banknote] Bankovní převod
  - [icon hand-coins] Dobírka při převzetí
  - [icon store] Při osobním odběru
  - [icon shield-check] 3D Secure, SSL šifrování
- Заголовок «Bezpečné platby» Inter 600 16 px
- Под сеткой: «Zaručujeme bezpečnost vašich platebních údajů. Citlivá data nikdy neukládáme.»

---

## 9. SECTION 8 — RECENZE A HODNOCENÍ

> Совмещаем Google Reviews (общий рейтинг с вашего GBP) + внутренние отзывы + **фотоотзывы**.

### 9.1. Layout
- Background `paper`
- Container `container`
- Padding 80 vertical

### 9.2. Top-stats блок
```
[Большое число] 4,9        ⠀⠀⠀⠀         [Распределение оценок]
★★★★★                                    5★ ──────────── 89%
124 recenzí                              4★ ──────         8%
67 ověřených kupců                       3★ ─             2%
12 fotek od zákazníků                    2★               1%
                                         1★               0%
                                         [Napsat recenzi →]
[Google Reviews badge: 4,9 — 156 recenzí]
[Heuréka badge: 95 % spokojenosti]
```

- 4,9 — Playfair italic 56 px, color `forest`
- Bars — высота 6 px, фон `cream-deep`, заливка `lime`
- Badges Google / Heuréka — официальные SVG (Google brand kit, Heuréka brand kit)
- CTA «Napsat recenzi» — primary `amber`, открывает review-modal (см. §9.5)

### 9.3. Photo gallery (фотоотзывы)
- Section: «Fotografie od zákazníků (12)»
- Сетка 4–6 thumbnails 120×120 desktop / 2 ряда по 3 mobile
- Click — lightbox с увеличенным фото + текст отзыва + автор
- «Zobrazit všechny fotografie →» link

### 9.4. Filter / Sort
- Sort: «Nejnovější ▾» (default) | «Nejlépe hodnocené» | «Nejhůře hodnocené» | «S fotografií»
- Filter chips: [Vše] [Ověření kupci] [S fotografií] [5★] [4★] [3★] [2★] [1★]
- Toggle: «Pouze recenze produktu / Vše vč. služby»

### 9.5. Список отзывов
- Каждый отзыв — карточка:
  ```
  ★★★★★  ●Pavla N. · Ověřený kupec     před 3 dny
  
  Velmi rychlé doručení, balení odpovídá popisu. Šarže s vlastním
  COA — to jsme jinde neviděli. Profesionální přístup.
  
  [фото 80×80] [фото 80×80]  ← фотоотзыв если есть
  
  Balení: 25 g       Doporučujem 👍 12
  ```
- Card padding 24, radius `rounded-lg`, border 1 px `border-light`, hover `cream-soft`
- Ниже — пагинация 10 на страницу или «Načíst další»

### 9.6. Модалка «Napsat recenzi»
- Открывается при click на CTA
- Поля:
  - Ваш рейтинг (5 кликабельных звёзд)
  - Заголовок (опц., 60 chars)
  - Содержание (max 1000 chars)
  - Загрузить фото (drag-drop, max 4 фото, 5 MB каждое)
  - Подтверждение «Я являюсь покупателем» (если залогинен — auto, если нет — email-верификация)
  - GDPR checkbox
  - Submit
- После submit — review идёт на модерацию (важно по комплаенсу: фильтр effect-claims)

### 9.7. Комплаенс по отзывам
- **Модерация:** фильтр запрещённых заявлений (помогло от X, лечит Y) — публикуются с edit или скрываются. Не подменяем смысл — лучше скрыть «kontroverzní» (см. SEO-файл 4.9).
- Бейдж «Ověřený kupec» только если есть order ID этого пользователя.
- Schema: AggregateRating + Review только реальные.

---

## 10. SECTION 9 — OTÁZKY A ODPOVĚDI (Q&A interní)

> Отдельно от FAQ — это пользовательские вопросы по конкретному товару.

### 10.1. Layout
- Background `cream`
- Container `container-narrow`
- Padding 80 vertical

### 10.2. Содержимое
- H2: «Otázky a odpovědi (8)»
- CTA: «Položit otázku» (Button.outline + icon `message-square-plus`)
- Список вопросов (карточки):
  ```
  ● Tomáš L. — 12. 03. 2026
  Otázka: Liší se šarže VD-2026-014 nějak od předchozí šarže?
  
  ── Odpověď Vivadzen ──
  Šarže VD-2026-014 má obsah mitragyninu 1,42 %, předchozí
  šarže VD-2025-018 měla 1,38 %. Rozdíly v rámci normy. Obě
  prošly stejnými laboratorními testy ve VŠCHT Praha.
  
  [👍 5 lidí to považuje za užitečné]   [Odpovědět]
  ```
- Карточка: фон `paper`, radius `rounded-lg`, padding 24, border-left 3 px `forest` для вопроса, `lime` для ответа
- Hierarchia: вопрос — собирёт ник + дата + текст; ответ Vivadzen — выделен (бейдж «Oficiální odpověď» `lime`)
- Sort: «Nejnovější» / «Nejužitečnější»

### 10.3. Модалка «Položit otázku»
- Имя (auto если залогинен)
- Email (для уведомления о ответе)
- Otázka (textarea, max 500 chars)
- Checkbox: «Souhlasím s veřejným zobrazením otázky a odpovědi»
- Submit → модерация → публикация после ответа

---

## 11. ПОДПИСКА (subscription overlay/modal при click на «Předplatné -10%»)

### 11.1. Open from buy-box CTA «Předplatné -10 %»

### 11.2. Модалка (centered, max-width 600)

```
┌──────────────────────────────────────────────────────────┐
│  PŘEDPLATNÉ                                                │
│  Pravidelná dodávka Červené Maeng Da                      │
│                                                            │
│  INTERVAL DODÁNÍ                                           │
│  [● 14 dní]  [○ 30 dní]  [○ 60 dní]      ← radio          │
│                                                            │
│  BALENÍ                                                    │
│  [○ 25 g]  [● 50 g]                                        │
│                                                            │
│  CENA NA ODBĚR                                             │
│  490 Kč → 441 Kč  (-10 %)                                  │
│                                                            │
│  Přednostní informace o nové šarži                        │
│  Kdykoli pozastavte nebo zrušte                           │
│  Stejné laboratorní testy každé šarže                     │
│                                                            │
│  Souhlasím s podmínkami předplatného. [link]              │
│                                                            │
│  [PŘIDAT PŘEDPLATNÉ →]                                     │
│  [Zrušit]                                                  │
└──────────────────────────────────────────────────────────┘
```

- Click «Přidat předplatné» → если не залогинен → редирект на чекаут (там login/гость + чекаут как обычно, но со специальным типом «subscription order»); если залогинен → сразу в чекаут со специальным признаком.

---

## 12. SECTION 10 — SOUVISEJÍCÍ PRODUKTY

- Background `paper`
- Container `container`
- Padding 80
- H2: «Mohlo by vás zaujmout»
- 4 MiniProductCard в ряд desktop, 2 mobile
- Алгоритм: тот же штамм или тот же цвет, исключая текущий товар

---

## 13. SECTION 11 — FOOTER-CTA MINI

- Background `forest`
- Padding 64
- Контент: «Máte otázku? Napište nám — odpovídáme do 24 hodin.» + 2 CTA «Podpora» (primary amber) и «Časté dotazy» (outline)

---

## 14. SEO PRODUCT PAGE (резюме из SEO-файла 3.7)

- **Title:** `{Název produktu} 25/50 g — laboratorně testováno | Vivadzen`
- **Meta:** `{Název} — kratom prášek od licencovaného e-shopu. Balení 25 g a 50 g, laboratorně testovaná šarže, údaj o obsahu mitragyninu. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** Точное название товара
- Schema: Product + Offer ×2 (25/50 g) + AggregateRating + Review + BreadcrumbList + FAQPage (для Q&A блока) — см. SEO-файл 6 §7

---

## 15. ВЗАИМОДЕЙСТВИЯ ВЫСОКОГО ПРИОРИТЕТА

| Действие | Поведение |
|---|---|
| Переключение 25/50 g | Цена, unit price, кнопка корзины обновляются мгновенно (no reload) |
| Click «Přidat do košíku» | Animation на кнопке + toast cart-added + bag badge updates |
| Click «Stáhnout COA (PDF)» | Открывается PDF файл (target="_blank") |
| Click «Napsat recenzi» | Открывается review-modal |
| Hover на фото галереи | Preview обновляется |
| Click на главное фото | Lightbox (fullscreen zoom) |
| Click «Předplatné -10 %» | Subscription modal |
| Scroll past hero (mobile) | Появляется sticky bottom buy-bar |
| Click anchor links (tabs sticky) | Smooth scroll to section |

---

## 16. MOBILE-СПЕЦИФИЧНЫЕ ЗАМЕЧАНИЯ

- Buy-box перемещается **под** галерею
- COA-таблица переключается в card-mode (см. §5.5)
- Sticky bottom buy-bar появляется при скролле ниже hero
- Tabs sticky горизонтально-скроллируемые
- Все модалки → bottom sheet (slide up from bottom)

---

→ Дальше — файл 07 (Checkout, кабинет, логин).
