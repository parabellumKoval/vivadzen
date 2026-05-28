# VIVADZEN — ДИЗАЙН ТЗ
## Файл 2/7 — Каталог компонентов

> Все компоненты — атомы и молекулы интерфейса. Дизайнер/разработчик собирает страницы (файл 3) из этого набора. Каждый компонент ссылается на токены файла 1. Где не указано — defaults из файла 1.

Список компонентов в этом файле:
- §1 Header (top-bar + main nav + mobile)
- §2 Footer (с trust-блоками VŠCHT/PML-licence)
- §3 ProductCard (мини, с переключателем 25/50 g)
- §4 ProductGallery (товар, страница)
- §5 ProductVariantSelector (полная версия 25/50 g + подписка)
- §6 SubscriptionToggle (купить разово vs подписка)
- §7 LegalSafetyBlock (обязательный § 33e блок на товаре)
- §8 COABlock (лаб. таблица для конкретной šarже + кнопка PDF)
- §9 AgeGate (18+ overlay, без вреда SEO)
- §10 AgeBadge + PML-строка (постоянный микро-комплаенс)
- §11 BreadcrumbBar
- §12 FilterSidebar / FilterTopBar
- §13 PriceBlock (с фасовкой, со скидкой подписки)
- §14 TrustBar (lab/licence/2 prodejny/doručení)
- §15 GoogleReviewsWidget (агрегат, рейтинг, последние)
- §16 InternalReviewCard (отзыв пользователя + рейтинг + фото)
- §17 ProductQuestionCard (Q&A под товаром)
- §18 PhotoReviewGallery
- §19 CategoryGlyphCard (как «Kratom / Energy & relief» из скриншота 3)
- §20 FAQAccordion
- §21 CheckoutStepper (поэтапный чекаут — шапка)
- §22 PaymentMethodCard (radio с лого Visa/MC/Apple/Google)
- §23 DeliveryMethodCard (messenger / express 180 / osobní odběr)
- §24 SavedMethodChip (сохранённый способ оплаты/доставки)
- §25 AccountSidebar (sidebar личного кабинета)
- §26 SupportContactForm (страница поддержки)
- §27 NewsletterStockAlert (Upozornit при наскладнění)
- §28 InfoBanner / AnnouncementBar (Express 180 dnes)
- §29 ToastNotification + Modal/Drawer
- §30 EmptyState

---

## §1. HEADER

### Структура (desktop, ≥1024 px)

```
┌────────────────────────────────────────────────────────────────────┐
│  TOP-BAR  (h=36, bg forest.900, text cream.100@80%, body.sm)        │
│  📍 Praha — osobní odběr · 🚚 Express 180 min Praha/Ostrava ·       │
│  🔒 Licencovaný PML prodejce                          [CS/EN] 🌐    │
├────────────────────────────────────────────────────────────────────┤
│  MAIN NAV  (h=80, bg forest.700)                                    │
│  [Logo Vivadzen white]   Kratom ▾   Předplatné   Průvodce ▾         │
│                                          Laboratoř   Prodejny       │
│                          [search 🔍 ─────────────]  ❤  👤  🛒(2)    │
└────────────────────────────────────────────────────────────────────┘
```

- Логотип: `/assets/brand/logo-vivadzen-white.svg`, высота 32 px.
- Nav: Inter SemiBold 15/20, cream.100; hover: цвет grass.500 + 2 px underline.
- Dropdown `Kratom ▾`: мега-меню в 3 колонки (Podle barvy / Podle odrůdy / Podle formy) + 4-я визуальная колонка (фото + CTA «Vše skladem →»).
- Поиск: pill input, h=44, bg cream.100@8%, border cream.100@20%, плейсхолдер «Hledat odrůdu…».
- Иконки справа: heart (wishlist), user, cart с бейджем-счётчиком (terracotta.500, h=18, белый текст).
- Cart-бейдж счётчик — на пилюле сверху-справа от иконки.
- Sticky при скролле ≥80 px (только Main nav, top-bar исчезает); h=64, лёгкая тень `shadow.md`.

### Mobile (<768 px)

```
┌────────────────────────────────┐
│  ☰  [Logo]              🔍 🛒(2) │   h=64, bg forest.700
└────────────────────────────────┘
  (под шапкой бегущая полоса top-bar шириной строки, делим на 3 пуллы)
```

- Burger открывает off-canvas меню справа (slide-in, 320 px width), bg forest.700.
- В off-canvas: основные пункты + аккордеон-категории + CTA «Express dnes do 180 min →» (терракотовая кнопка).
- TopBar становится единой бегущей строкой (auto-marquee 25–30 sec; pause on hover/touch). НЕ слайдер — он плохо доступен.

---

## §2. FOOTER

Полная высота ~600 px desktop / автовысота mobile. bg forest.900, text cream.100@80%.

### Колонки (desktop)

```
┌────────────┬─────────────┬─────────────┬───────────────┐
│ Vivadzen   │ Nakupování  │ Informace   │ Trust         │
│  [logo]    │  Kratom     │  O nás      │  [VŠCHT]      │
│  about     │  Předplatné │  Licence    │  ISO 17025    │
│  IČO/DIČ   │  Doprava    │  Lab testy  │  [PML lic.]   │
│  Praha     │  Reklamace  │  Zákon 2026 │  MZ ČR        │
│  prodejny  │  Recenze    │  FAQ        │               │
│  social    │             │  Blog       │  [Heuréka]    │
│            │             │  Podpora    │  reviews      │
└────────────┴─────────────┴─────────────┴───────────────┘
─── divider 1px cream.100@10% ───
[Pouze pro osoby starší 18 let]  [Psychomodulační látka dle zák. č. 167/1998 Sb.]
[Tento výrobek není potravinou, doplňkem stravy ani léčivem.]
─── divider ───
© 2026 Vivadzen s.r.o.   Obchodní podmínky · Ochrana soukromí · Cookies
```

### Trust-колонка (детально — это компонент `FooterTrustStack`)

Две карточки одна под другой, bg forest.800, padding 20, radius.lg, border 1px cream.100@10%:

**Карточка A — Akreditovaná laboratoř:**
```
[VŠCHT logo SVG 48 px height]
AKREDITOVANÁ LABORATOŘ           ← overline, grass.500
Testování čistoty a obsahu látek
v akreditované laboratoři
VŠCHT Praha dle normy ISO 17025.
[lucide:external-link →]         ← ссылка на /laboratorni-testy
```

**Карточка B — Autorizovaný prodejce PML:**
```
[MZ ČR seal SVG 48 px]
AUTORIZOVANÝ PRODEJCE PML        ← overline, amber.500
Činnost pod přímým dohledem
a licencí Ministerstva zdravotnictví
České republiky.
[lucide:external-link →]         ← ссылка на /licence
```

### Mobile

Колонки складываются в аккордеон (свёрнуты по умолчанию). Trust-карточки — ниже всех колонок, друг под другом, на всю ширину.

---

## §3. ProductCard (МИНИ) — критичный компонент

Используется в каталоге, на главной, в «související», в «продолжаются продажи». Один из главных носителей бренда → проработка детальная.

### Состав (по вашему брифу)

```
┌──────────────────────────────────────┐
│                                      │
│        [Фото товара 1:1]             │   высота 240–280 (desktop), 200 (mobile)
│   (badge 18+ слева сверху,           │
│    badge subscription/sale справа)   │
│                                      │
├──────────────────────────────────────┤
│ ● Red vein · Borneo                  │   eyebrow строка, cap.s, ink.500
│                                      │
│ Červená Maeng Da                     │   product title, heading.md, ink.900
│                                      │
│ Mitragynin 1.42% · jemně mletý       │   spec line, body.sm, forest.700
│                                      │
│ ┌──────────────┬──────────────┐      │   variant toggle pill
│ │  25 g  349 Kč│  50 g  649 Kč│      │   (selected = forest.700 fill, cream.100 text)
│ └──────────────┴──────────────┘      │   (other  = transparent, forest.700 text + 1px border)
│                                      │
│ ★★★★★ 4.9 (128)        [+ do košíku]│   rating + CTA pill amber.500
└──────────────────────────────────────┘
```

### Детально

- Container: bg cream.100, radius.lg (16), padding 20 desktop / 16 mob, hover `translateY(-2px) shadow.lg`. Без border. Cursor pointer весь блок (клик ведёт на товар, но кнопка корзины не триггерит навигацию).
- **Фото:** real photo, aspect 1:1, lazy-load кроме первой строки (там eager + `fetchpriority=high`). Padding вокруг фото (cream фон ≠ край фото).
- **Eyebrow «● Red vein · Borneo»:**
  - `●` — кружок цвета вены ø 10 px:
    - Red vein → fill `#C03A2D` (тёплый красный, не danger)
    - Green vein → fill `#5FA63B`
    - White vein → fill `#E8DDC1` + border 1px ink.300
    - Yellow vein → fill `#F4A020`
  - Текст «Red vein · Borneo» — `body.sm`, ink.500, разделитель `·` ink.300, UPPERCASE: НЕТ (CZ-диакритика).
  - Если штамма нет (например extrakt) — выводим только цвет, разделитель убираем.
- **Title:** `heading.md` (22/20), ink.900, max 2 строки + ellipsis. Без UPPERCASE.
- **Spec line «Mitragynin 1.42% · jemně mletý»:**
  - `body.sm`, forest.700.
  - Шаблон: `Mitragynin {X}% · {помол}`. Если COA не готов — `Šarže VD-XXX · {помол}` (без процента, чтобы не врать).
- **Variant toggle 25 g / 50 g:**
  - Это **сегментированный pill** на 2 кнопки, full-width внутри карточки.
  - Selected: bg `forest.700`, text `cream.100`, ничего сверху.
  - Other: bg `transparent`, text `forest.700`, border 1px `forest.300`.
  - Цена внутри сегмента (формат «25 g 349 Kč»). При смене сегмента карточка не перезагружается — JS обновляет цену и SKU для кнопки.
  - **Если есть скидка по подписке** (см. §6) — над кнопкой `+ do košíku` добавляется микро-строка «−10% s předplatným» (caption, grass.700).
- **Rating:**
  - 5 звёзд amber.500 (filled до значения, остальные cream.300 outline).
  - «4.9 (128)» — body.sm ink.500.
  - Если отзывов < 5 — выводим «Nový» вместо рейтинга (terracotta.500 caption).
- **CTA `+ do košíku`:**
  - Pill, h=36, bg amber.500, text ink.900, иконка `lucide:plus` слева.
  - При клике: иконка → `lucide:check` 600 ms, кнопка остаётся, toast «Přidáno do košíku».
- **Badges (слева сверху на фото):**
  - 18+ — обязательный микро-бейдж (terracotta.500 / cream.100, h=22, padding 4×8, radius.pill, caption). Виден всегда.
  - `Skladem` / `Brzy` / `Vyprodáno` — справа сверху. Skladem = grass.500/forest.900. Brzy/Vyprodáno = ink.500/cream.100.
  - `−10%` при акции — terracotta.500/cream.100.
  - `Předplatné` (если поддерживает подписку) — grass.500/forest.900 badge.

### Состояния
- **InStock:** обычный.
- **OutOfStock:** фото 50% opacity, бейдж «Brzy» / «Vyprodáno», вместо «do košíku» кнопка «Upozornit» (см. §27).
- **Placeholder (нет лицензии):** фото остаётся, бейдж «Připravujeme», цены НЕТ, variant toggle НЕТ, кнопка «Upozornit při naskladnění» — единственное CTA. На клик весь блок ведёт на инфо-страницу штамма (не товара).

### Mini-card variant — «compact» (для строки «Související 3–4 шт.» и блока «Skladem»)

То же самое, но фото 200×200, без variant-toggle (показываем 25 g по умолчанию), без spec-line (только mitragynin), title 1 строка ellipsis. Применяется только в плотных сетках на товаре.

---

## §4. ProductGallery (страница товара)

- Слева (desktop) или сверху (mobile): главная картинка 1:1, ≥600 px desktop, swipe mobile.
- Под/справа: 4–6 thumbnails (vertical desktop, horizontal mobile), активный с border 2px grass.500.
- Zoom on hover (desktop) — light-box clic.
- Badge 18+ постоянно в верхнем левом углу фото.
- Lazy для всех кроме первого; LCP-кандидат = первая.

---

## §5. ProductVariantSelector (полный, страница товара)

Это расширенный вариант сегментированной кнопки из §3. На странице товара показываем явно с ценой за грамм:

```
┌──────────────────────────────────────────────────────────────┐
│ Balení                                                       │
│                                                              │
│  ┌─────────────────────┐    ┌─────────────────────┐          │
│  │ 25 g                │    │ 50 g          POPULAR│          │
│  │ 349 Kč              │    │ 649 Kč               │          │
│  │ 13.96 Kč / g        │    │ 12.98 Kč / g · −7%   │          │
│  └─────────────────────┘    └─────────────────────┘          │
│  (selected = bg forest.700  ( bg cream.200, border forest.300)│
│   text cream.100)                                            │
└──────────────────────────────────────────────────────────────┘
```

- Цена за грамм — динамически из price/weight, чтобы было видно выгоду 50 g.
- Бейдж `POPULAR` или `−7%` — terracotta.500 угловой ribbon.
- Под селектором — микро-строка «Skladem · Expedice do 24 h» (success.500 dot + body.sm).

---

## §6. SubscriptionToggle (Купить разово vs Předplatné)

Под селектором варианта, **полная ширина**, аккордеон-стиль:

```
┌──────────────────────────────────────────────────────────────┐
│ ◉  Jednorázový nákup                              649 Kč     │
│    Klasická objednávka, žádné závazky.                       │
├──────────────────────────────────────────────────────────────┤
│ ○  Předplatné — ušetříte 10 %               584 Kč /měsíc   │
│    Doručení každých 14 / 30 / 60 dní · zrušíte kdykoli       │
│    ┌─────┐ ┌─────┐ ┌─────┐                                   │
│    │14 dní│ │30 dní│ │60 dní│  (chip-выбор частоты)          │
│    └─────┘ └─────┘ └─────┘                                   │
└──────────────────────────────────────────────────────────────┘
```

- Default — Jednorázový. Радио активного — `grass.500` filled.
- При выборе подписки: внутри карточки появляется выбор частоты (3 chip-кнопки), новая цена с прочёркнутой старой, бейдж «Ušetříte −65 Kč».
- Под блоком — мелкая строка «Předplatitelé mají přednostní zásoby a osobní COA reporty». ↗ ссылка → /predplatne.

---

## §7. LegalSafetyBlock (обязательный § 33e блок на странице товара) — критический

Это **обязательный регуляторный блок**. Текст идёт verbatim (вы прислали). Дизайн — академический/«вкладыш в упаковке», подчёркивает регуляторный характер, не маркетинговый.

### Структура

```
┌──────────────────────────────────────────────────────────────┐
│  ⚠ Důležité bezpečnostní a spotřebitelské informace          │
│                                                              │
│  Není určeno osobám mladším 18 let!                          │
│  Ukládat mimo dosah osob mladších 18 let!                    │
│  Užívání tohoto výrobku může poškodit Vaše zdraví.           │
│  Dbejte informací pro spotřebitele.                          │
│                                                              │
│  ─── divider ───                                             │
│                                                              │
│  Charakter výrobku                                           │
│  • Tento výrobek má psychoaktivní účinky.                    │
│  • Tento výrobek není potravinou.                            │
│  • Tento výrobek není léčivým přípravkem a nebyl klinicky    │
│    testován.                                                 │
│  • Pokud si nejste jisti, zda je tento výrobek pro vás       │
│    vhodný, konzultujte jeho užití s lékařem.                 │
│  • Není určeno osobám mladším 18 let. Ukládat mimo dosah     │
│    osob mladších 18 let.                                     │
│  • Užívejte v souladu s návodem k použití. Nepřekračujte     │
│    doporučenou denní dávku.                                  │
│                                                              │
│  Účinky                                                      │
│  Při doporučeném dávkování má tento výrobek povzbuzující     │
│  účinky. Při překročení doporučeného dávkování má tento      │
│  výrobek tlumivé účinky.                                     │
│  Užití tohoto výrobku může ovlivnit bdělost, koordinaci…     │
│  […полный текст…]                                            │
│                                                              │
│  Upozornění                                                  │
│  • Neužívejte tento výrobek při, bezprostředně před a v…     │
│  […полный текст…]                                            │
│                                                              │
│  Návod k použití                                             │
│  1. Odměřte 3 g prášku. Doporučená jednorázová dávka 3 g.    │
│  2. Rozmíchejte ve 100–250 ml vody a vypijte.                │
│  3. Maximální doporučená denní dávka 10 g.                   │
│  4. Skladujte v suchu, chladu a mimo dosah dětí.             │
└──────────────────────────────────────────────────────────────┘
```

### Стилизация

- Container: bg `cream.200` (тёплая бежевая бумага, отделяет от маркет-блоков), padding 24–32, radius.lg, border 1px forest.300.
- Header `⚠ Důležité…` — heading.md, ink.900, иконка `lucide:alert-triangle` terracotta.500.
- Подзаголовки секций (`Charakter výrobku`, `Účinky`, `Upozornění`, `Návod`) — heading.sm Inter SemiBold, ink.900, всё, тёмный.
- Body — Inter Regular 15/24, ink.900 (контраст AAA).
- Списки — буллет `−` или `•`, отступ 16 от текста.
- Дивайдеры — 1px ink.100, vertical-margin 20.
- **По умолчанию свёрнут ниже первой секции** (предупреждение видно всегда; полный текст за «Zobrazit více ↓»). Это снижает фрустрацию пользователя, но НЕ скрывает обязательную информацию (она доступна одним кликом, всегда в DOM — SEO-чистый паттерн).
- На мобиле — раскрыт верхний предупреждающий блок, остальное collapse.

### Положение на странице товара

Сразу под основным блоком покупки (gallery + price + variant + CTA), но **перед** «Související produkty» и «Recenze». Это и регуляторно правильно, и пользователь видит юр. инфу до отзывов.

---

## §8. COABlock (Laboratorní test této šarže) — ключевой trust-блок

По вашему брифу:

```
┌──────────────────────────────────────────────────────────────┐
│  [icon flask grass.500]  Laboratorní test této šarže         │  heading.md
│  Šarže  VD-2026-014                                          │  body.sm ink.500
│  ────────────────────────────────────────────────────────    │
│                                                              │
│  PARAMETR                       HODNOTA          STATUS      │
│  ───────────────────────────────────────────────────────     │
│  Obsah mitragyninu              1,42 %           [PASS]      │
│  Obsah 7-hydroxymitragyninu     0,008 %          [PASS]      │
│  Čistota                        99,1 %           [PASS]      │
│  Mikrobiologie                  Vyhovuje         [PASS]      │
│                                  ČSN ISO 21527                │
│  Těžké kovy (Pb, Cd, Hg, As)    < 0,3 ppm        [PASS]      │
│  Datum testu                    12. 03. 2026     VŠCHT Praha │
│                                                              │
│  ────────────────────────────────────────────────────────    │
│  Plné COA s razítkem laboratoře.                             │
│                                                              │
│  [📥 Stáhnout COA (PDF)]                                      │
└──────────────────────────────────────────────────────────────┘
```

### Стилизация

- Container: bg `forest.700`, text cream.100, padding 24–32, radius.xl, тонкий 1px cream.100@12% border.
- Header: иконка `lucide:flask-conical` или `lucide:beaker` grass.500, heading.md cream.100.
- «Šarže VD-2026-014» — body.sm cream.200@80%, моноширинная для номера (`font-mono`).
- Таблица:
  - Стиль «лабораторный»: divider 1px cream.100@10% между строк.
  - PARAMETR — Inter Regular 14/22, cream.200@80%.
  - HODNOTA — Inter SemiBold 15/22, cream.100, выравнивание number (.42 — вертикально по десятичной).
  - STATUS — pill h=24, bg grass.500, text forest.900, label «PASS» Inter Bold 11/14 UPPERCASE.
  - VŠCHT Praha — body.sm cream.200@80%.
- Кнопка PDF: btn.secondary (outline cream.100@35%, text cream.100), иконка `lucide:download` слева.
- На мобиле таблица в 2 колонки `параметр / значение`, статус-pill справа.

### Дополнительно

- Ссылка ниже «Co je COA a jak ho číst →» ведёт на гайд `/pruvodce/jak-cist-coa` (если хотите завести отдельную страницу) или на `/laboratorni-testy`.
- Если COA ещё не загружен: показываем placeholder «COA pro tuto šarži je v procesu zveřejnění — bude doplněno do {date}» (без скрытия блока — это сигнал процессуальности, не дефект).

---

## §9. AgeGate (18+ overlay)

Критическая реализация — **не редирект, не cloaking**. Контент в DOM всегда; overlay поверх через CSS.

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│                  [Vivadzen logo white]                       │
│                                                              │
│                  Je vám 18 let nebo více?                    │
│                                                              │
│   Tento web nabízí výrobky určené pouze dospělým osobám      │
│   (psychomodulační látky dle zák. č. 167/1998 Sb.).          │
│   Vstupem potvrzujete, že je vám 18 let nebo více.           │
│                                                              │
│      ┌────────────────────┐    ┌────────────────────┐        │
│      │   Ano, je mi 18+   │    │  Ne, opustit web   │        │
│      └────────────────────┘    └────────────────────┘        │
│                                                              │
│        amber.500 / ink.900       transparent / cream.100      │
│                                                              │
│   Informace o ochraně mladistvých · /licence · /overeni-veku │
└──────────────────────────────────────────────────────────────┘
                  bg forest.900 @ 92% opacity full-viewport
                  contents позади видны blurred (не скрываются)
```

- Поверх всего, position:fixed.
- При «Ne» → переход на нейтральную страницу (например google.cz) или `/overeni-veku` с объяснением.
- При «Ano» → cookie `age_verified=1` (1 год), overlay убирается с fade 200 ms.
- Bot detection: User-Agent google/bingbot/seznambot → overlay не рендерим (нужно ТОЛЬКО для рекомендованных Googlebot/Bingbot/SeznamBot). Это **не cloaking** в нарушающем смысле — мы показываем БОТУ то же, что показали бы пользователю после клика «Ano». Это юридически приемлемая практика и не наказывается Google (см. файл SEO 06 §6).
- Контент за overlay — **рендерится server-side, HTTP 200**, не зависит от age-gate.

---

## §10. AgeBadge + PML-row

Постоянная микро-комплаенс-плашка. На каждой коммерческой странице — горизонтальная полоска под header (h=32, bg cream.200, text ink.700):

```
🔞 18+ · Psychomodulační látka dle zák. č. 167/1998 Sb.
```

- Slim, не отвлекает, но всегда видна. На странице товара — также в карточках товара (см. §3).
- На странице товара — отдельно увеличенный alert над `Do košíku`:
  «**Není určeno osobám mladším 18 let. Ukládat mimo dosah osob mladších 18 let.**»
  bg cream.200, border-left 4px terracotta.500, padding 16, иконка `lucide:shield-alert`.

---

## §11. BreadcrumbBar

```
Domů  /  Kratom  /  Zelený kratom  /  Zelená Maeng Da prášek
```
- body.sm, ink.500; активный (последний) — ink.900 SemiBold, без ссылки.
- Разделитель «/» ink.300.
- На мобиле — горизонтальный скролл если длинно, без переноса.
- Schema BreadcrumbList JSON-LD (см. файл SEO 06 §7.5).

---

## §12. FilterSidebar (desktop) / FilterTopBar (mobile)

### Desktop

Sidebar слева в каталоге, ширина 280, sticky под шапкой.

Группы:
- **Barva** (checkbox с цветовым кружком как в §3): Zelený / Bílý / Červený / Žlutý.
- **Odrůda:** Maeng Da, Bali, Borneo, Sumatra, Thai, Elephant, Rurut, …
- **Forma:** Prášek / Extrakt / Nano.
- **Balení:** 25 g / 50 g.
- **Mitragynin %** (range slider): 0.8–2.0%.
- **Dostupnost:** ☑ Skladem · ☐ Připravujeme.
- **Cena** (range slider): min–max Kč.
- **Hodnocení:** 5★ / 4+ / 3+.

Активные фильтры — chip-pill сверху над сеткой товаров (см. ниже).

### Mobile

«Filtr» pill-button в верху сетки; открывает bottom-sheet drawer 90% высоты. Внутри те же группы аккордеоном.

### Active filter chips

Над сеткой ряд chip’ов: `Zelený ✕`, `Maeng Da ✕`, … `Vyčistit vše`. Bg cream.200, text forest.700, x = ink.500. Click ✕ — снимает фильтр.

---

## §13. PriceBlock (страница товара, под gallery)

```
Mitragynin 1.42 % · jemně mletý          ← spec line, body.sm forest.700
Zelená Maeng Da prášek                   ← heading.xl Playfair Bold
● Green vein · Maeng Da · Indonésie      ← eyebrow body.sm ink.500

★★★★★ 4.9  (128 hodnocení)               ← link на блок отзывов
                                          ─ link kotva к Reviews

────────  divider 1px ink.100  ────────

649 Kč        ← display.lg Playfair Bold, ink.900
−7% z 699 Kč  ← body.sm ink.500 strike-through, опц.
12.98 Kč/g    ← body.sm ink.500

[Variant Selector — §5]
[Subscription Toggle — §6]

[Do košíku — btn.primary lg, full-width]   ← amber.500
[Express 180 min — btn.terracotta md]      ← опционально, если регион поддерживает

[Skladem · Expedice do 24 h]               ← success.500 + dot
[Doručíme zítra do Prahy do 12:00]         ← body.sm ink.500
```

---

## §14. TrustBar (горизонтальная под PriceBlock)

```
┌────────────────────────────────────────────────────────────────┐
│ 🧪 Lab. testováno   🏛 Licence MZ ČR   🏪 Praha 2× prodejna     │
│ Každá šarže VŠCHT    Zák. č. 167/1998   Osobní odběr na místě   │
└────────────────────────────────────────────────────────────────┘
```
- 3–4 столбца, иконка + 2 строки текста, divider 1px между.
- bg forest.700, text cream.100; или bg cream.200 на светлой странице.
- На мобиле — 2×2 grid.

---

## §15. GoogleReviewsWidget (агрегат)

Виджет Google Business Profile отзывов. На главной + на /recenze.

```
┌──────────────────────────────────────────────────────────────┐
│ [G logo]  Recenze Google                                     │
│                                                              │
│ ★★★★★  4.8  ·  321 recenzí                                   │
│                                                              │
│ ▶ Slider горизонтальный: 4 карточки на десктоп, 1.2 mobile  │
│  ┌──────────────────────┐  ┌──────────────────────┐          │
│  │ [Avatar Jan N.]      │  │ [Avatar Petra K.]    │          │
│  │ ★★★★★  2 dny zpět    │  │ ★★★★☆  1 týden zpět  │          │
│  │ "Skvělý obchod..."   │  │ "Rychlé doručení..." │          │
│  │ Číst na Google ↗     │  │ Číst na Google ↗     │          │
│  └──────────────────────┘  └──────────────────────┘          │
│                                                              │
│  Zobrazit všechny recenze →                                  │
└──────────────────────────────────────────────────────────────┘
```

- Интеграция через Google Places API → серверный кэш ≤ 24 h (не дёргать на каждый рендер).
- Аватары — реальные G-avatars (если доступны API), иначе инициалы в кружке (forest.600 + cream.100).
- Карточка отзыва: bg cream.100 (на светлой стр.) или forest.600 (на тёмной), radius.lg, padding 20.
- «Zobrazit všechny» ведёт на `https://g.co/...` ваш профиль.
- Schema: AggregateRating Organization (если используется Google reviews как репутация бренда).

---

## §16. InternalReviewCard (внутренний отзыв пользователя)

```
┌──────────────────────────────────────────────────────────────┐
│ [Avatar Petra K.]   ★★★★★         15. 3. 2026                │
│ Ověřený nákup · Zelená Maeng Da 25 g                         │
│                                                              │
│ "Aroma jemné, balení bylo v pořádku. Rychlé doručení         │
│  do Prahy 6 do 24 h, kurýr ověřil věk. Obsah COA souhlasí    │
│  s tím, co je na webu."                                      │
│                                                              │
│ 📷  📷  📷    ← клик → фотогалерея отзыва                    │
│                                                              │
│ 👍 12   Užitečné?   Odpovědět                                │
└──────────────────────────────────────────────────────────────┘
```

- Container: bg cream.100, radius.lg, padding 20.
- «Ověřený nákup» — бейдж grass.500/forest.900 (только если есть order ID на этого юзера = реальный покупатель — критически важно для доверия и schema Review).
- Body — Inter Regular 15/24 ink.900.
- Фотографии — thumbnails 64×64 radius.sm, клик → lightbox.
- 👍 — счётчик «полезности», JS-вариант.
- Под отзывом — возможный «Odpověď od Vivadzen» (отступ слева 24, бейдж «✓ Vivadzen» forest.600).

### Модерация
- Пользователь оставляет отзыв только если есть подтверждённый order ID (по email или из аккаунта).
- Премодерация (1–2 дня) — фильтр на CZ-инвективы и health-claims (юзер может написать «pomohlo mi…» — это не наш маркетинговый текст, но в идеале модератор не пропускает явно медицинские утверждения о лечении; помечать как «zkušenost zákazníka, není zdravotní doporučení»).

---

## §17. ProductQuestionCard (Q&A под товаром)

Похожий контейнер, но другая семантика:

```
┌──────────────────────────────────────────────────────────────┐
│ [👤 Marek (host)]              22. 2. 2026                   │
│                                                              │
│ Otázka                                                       │
│ "Je k dispozici větší balení 100 g?"                         │
│                                                              │
│ ───────────────────────────────────────────────────────      │
│ [✓ Vivadzen]                                                 │
│ Odpověď                                                      │
│ "Aktuálně nabízíme 25 g a 50 g. Zákonné limity balení..."    │
│                                                              │
│ 👍 5    Užitečné?    [Položit dotaz →]                        │
└──────────────────────────────────────────────────────────────┘
```

- Видны все вопросы, даже без ответа — пользователи быстрее задают свой.
- Кнопка «Položit dotaz» открывает inline-форму или модалку. Имя, email (опц.), вопрос. Премодерация.
- Schema: `Question` + `Answer` JSON-LD под товаром (это даёт rich snippet в SERP).

---

## §18. PhotoReviewGallery

- Сетка thumbnails 80×80 на самом верху блока «Recenze» — клик → lightbox со слайдером всех фотоотзывов.
- Бренд-проверка: не публиковать фото с лицами без consent (CZ GDPR).

---

## §19. CategoryGlyphCard (как в скриншоте 3)

Используем для главной — сетка категорий «Find Your Path».

```
┌────────────┐
│            │
│   ╭───╮    │   круг ø 80–100, bg cream.50 + 1px ink.100, shadow.sm
│   │ 🍃│    │   внутри — глиф (см. файл 5 §3)
│   ╰───╯    │
│            │
│  Kratom    │   heading.md, ink.900
│            │
│  Energy &  │   body.sm, ink.500
│  relief    │
└────────────┘
```

- Container: bg cream.200, radius.xl, padding 24, shadow.sm; hover translateY(-2) + shadow.md.
- ⚠ ВАЖНО: текст под карточкой («Energy & relief», «CBD & THC») — это **маркетинговое описание категории**, по нашим правилам PML без health-промиса. Для кратома пишем фактологично: например «Mitragyna speciosa» или «Bali, Maeng Da & další odrůdy». Никаких «Energy & relief» в живой версии для кратома — это для inspiration только.

---

## §20. FAQAccordion

- Bg cream.100, divider 1px ink.100 между вопросами.
- Заголовок вопроса: heading.sm Inter SemiBold, ink.900; иконка `lucide:chevron-down` справа, rotate 180 при open.
- Тело ответа: body.md ink.700, padding 16 sides + 24 bottom.
- Open by default — только первый.
- Schema FAQPage (JSON-LD, см. файл SEO 06 §7.6).

---

## §21. CheckoutStepper (поэтапный)

5 шагов, как вы хотели:

```
1 ✓  Doručení      →    2 ●  Platba    →    3 ○  Souhrn    →    4 ○  Potvrzení
```

- Активный шаг — pill forest.700 с цифрой cream.100; пройденный — grass.500 с галочкой; будущий — cream.300 с цифрой ink.500.
- Линии-коннекторы 2px между шагами; пройденная часть grass.500, остальное ink.100.
- На мобиле — компактный (только current N/Total, прогресс-бар).
- Один шаг = одна страница (`/checkout/doruceni`, `/checkout/platba`, …) ИЛИ один на странице с переключением (SPA). Рекомендую отдельные URL — чище аналитика, лучше abandonment-trecking.

### Шаги
1. **Doručení:** адрес + способ (см. §23) + слот (если express).
2. **Platba:** способ (§22) + промокод + согласие 18+ (чек) + согласие с правилами + согласие с обработкой ПД.
3. **Souhrn:** итог, изменить → возврат к шагам.
4. **Potvrzení:** «Děkujeme!», номер заказа, что дальше (email, SMS, доставка), CTA «K předchozím objednávkám».

### Гостевой vs аккаунт
- На первой странице (Doručení) сверху pill `Mám účet ▾ / Pokračovat jako host`. По умолчанию — выбран «как гость».
- При «Mám účet» — модалка логина в боковой панели; после логина в чекаут подгружаются сохранённые адреса/способы (см. §24).

---

## §22. PaymentMethodCard (radio + лого)

```
┌──────────────────────────────────────────────────────────────┐
│ ◉  Online platba kartou                                      │
│    Visa · Mastercard · Apple Pay · Google Pay                │
│    🔒 Zabezpečeno SSL · 3-D Secure                            │
│    [VISA] [MC] [Apple Pay] [Google Pay]                      │
├──────────────────────────────────────────────────────────────┤
│ ○  QR platba                                                 │
│    Naskenujte QR v bankovní aplikaci                         │
├──────────────────────────────────────────────────────────────┤
│ ○  Bankovní převod                                           │
│    Platba předem na účet; expedujeme po připsání             │
├──────────────────────────────────────────────────────────────┤
│ ○  Dobírka                                                   │
│    Platba kurýrovi při převzetí · +49 Kč                     │
├──────────────────────────────────────────────────────────────┤
│ ○  Platba při osobním odběru                                 │
│    Karta nebo hotově na prodejně v Praze                     │
└──────────────────────────────────────────────────────────────┘
```

- Bg cream.100, divider 1px ink.100, selected — border 2px grass.500 + bg cream.200.
- Лого платёжек: официальные SVG из пресс-китов (см. файл 4). Высота 24 px.
- Под каждым способом — мини-строка «Bezpečně zpracováno {provider}» (для онлайн: «Bezpečně zpracováno ComGate/GoPay» — что у вас).

### Trust-row под платежами

«🔒 Vaše údaje jsou šifrované · 14 dní na vrácení · GDPR» — body.sm ink.500, divider-точки.

---

## §23. DeliveryMethodCard

```
◉  Doručení kurýrem (Messenger) po ČR
   1–2 pracovní dny · 99 Kč  (zdarma od 1500 Kč)
   Kurýr ověří věk při převzetí.

○  EXPRESS 180 min — Praha & Ostrava                  [bag grass.500]
   Doručení do 180 minut · 299 Kč
   Dostupné po–pá 9:00–18:00 · So 10:00–14:00
   PSČ: __ __ __ __ __    [Ověřit dostupnost]

○  Osobní odběr v Praze
   Prodejna 1 — {adresa}  (Po–Pá 10–19, So 10–14)
   Prodejna 2 — {adresa}  (Po–Pá 10–19, So 10–14)
   Připraveno do 2 hodin po objednávce.
```

- Express-блок — выделен бейджем `EXPRESS 180` grass.500.
- При выборе Express — появляется input PSČ с проверкой по списку поддерживаемых.
- При выборе Osobní odběr — radio под на 2 точки.

---

## §24. SavedMethodChip (сохранённый способ оплаты/доставки)

При входе под аккаунтом, в чекауте над списком способов:

```
Vaše uložené metody
┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐
│ [Visa]              │  │ Dobírka             │  │ + Nová metoda      │
│ •••• 4242           │  │ Praha 6, Bělohorská │  │                    │
│ [Použít]            │  │ [Použít]            │  │                    │
└────────────────────┘  └────────────────────┘  └────────────────────┘
```
- Pill-карточки, bg cream.100, border 1px ink.100, hover grass.500 border.
- Выбор → автозаполнение шагов.

---

## §25. AccountSidebar

Личный кабинет — sticky sidebar (desktop) или table tabs (mobile).

Пункты:
- 📦 Objednávky
- 🔄 Předplatné
- 📍 Adresy
- 💳 Platební metody
- ⭐ Recenze a otázky
- 🔔 Hlídání skladu (placeholder-товары, лиды)
- ⚙ Nastavení
- 🚪 Odhlásit

---

## §26. SupportContactForm (страница `/podpora`)

- Hero: «Pomůžeme. Jednoduše.» (display.lg).
- Поэтапная форма (3 шага):
  1. **Téma** — chip-выбор: «Objednávka», «Produkt», «Doručení», «Reklamace», «Předplatné», «Jiné».
  2. **Detail** — текстовое поле + опционально attach (фото/чек), номер заказа (auto-suggest из аккаунта).
  3. **Kontakt** — email/телефон + согласие GDPR + отправка.
- Боковой блок: «Časté dotazy» (linked) + «Chat na WhatsApp / Telegram» (если хотите live), часы работы поддержки, средн. время ответа («Odpovídáme do 4 h v pracovní době»).
- После отправки — toast + email-подтверждение с тикетом (#VD-S-XXXX).

---

## §27. NewsletterStockAlert (Upozornit при наскладnění)

Для placeholder-товаров (см. файл SEO 03 §3.9).

```
┌──────────────────────────────────────────────────────────────┐
│ 🔔  Připravujeme. Upozorníme vás první.                      │
│                                                              │
│ [E-mail ___________________________________]  [Upozornit →]  │
│                                                              │
│ ☑ Souhlasím se zpracováním e-mailu (GDPR)                    │
└──────────────────────────────────────────────────────────────┘
```
- Bg cream.200, radius.lg, padding 20.
- Double opt-in: после клика — письмо «Potvrďte odběr».

---

## §28. InfoBanner / AnnouncementBar

Один пиксельный баннер сверху страницы для акций/Express:

```
⚡ EXPRESS dnes do 180 min v Praze & Ostravě · Objednejte do 16:00       [✕]
```
- bg amber.500, text ink.900, body.sm SemiBold, padding 10 sides, иконка слева.
- Dismissible (cookie 24 h).
- Не использовать одновременно с TopBar (или TopBar превращается в баннер на этот раз).

---

## §29. ToastNotification + Modal/Drawer

- **Toast**: внизу справа (desktop) или сверху (mobile), bg forest.700, text cream.100, padding 14×20, radius.lg, shadow.lg, fade 200ms, dismiss 4s.
- **Modal**: центр, max-width 480, bg cream.100, radius.xl, padding 32, shadow.xl, overlay forest.900@70%.
- **Drawer**: слева/справа, 400 px width (desktop), full (mobile). Для корзины-предпросмотра.

---

## §30. EmptyState

Корзина пустая, нет результатов поиска, нет заказов:

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│             [иллюстрация SVG 200×200 — лист]                 │
│                                                              │
│             Tady je prozatím prázdno                         │
│                                                              │
│   Začněte prohlížet náš sortiment a najděte ten správný     │
│   kratom pro vás.                                            │
│                                                              │
│             [Prohlédnout kratom →]                           │
└──────────────────────────────────────────────────────────────┘
```

→ Дальше — файл 3 (постраничные ТЗ).
