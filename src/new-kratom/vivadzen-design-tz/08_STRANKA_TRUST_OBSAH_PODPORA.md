# VIVADZEN — DESIGN TZ
## Файл 8/9 — Trust, контент, поддержка, легал

> Финал. Здесь — Licence, COA-хаб, Pruvodce-хаб + статьи, Blog, FAQ-хаб, **Podpora** (отдельная клиентская страница как вы просили), Kontakt, O nás, Prodejny, легал-страницы.

---

## 1. STRÁNKA `/licence` — Лицензия (Trust-флагман)

### 1.1. Цель
Самая важная trust-страница. Её видят клиенты, конкуренты, журналисты, Google. Она физически доказывает легитимность.

### 1.2. Layout
- Header + breadcrumbs «Domů › Licence»
- Hero, потом 5 секций
- Background mix: hero `forest`, contentовые секции `paper`/`cream`

### 1.3. Hero
- Background `forest`
- Padding 80 / 56
- Grid 60/40
- Левая часть:
  - Eyebrow `lime`: `AUTORIZOVANÝ PRODEJCE PML`
  - H1 Playfair regular color `paper`: «Licencovaný kratom e-shop dle zákona č. 167/1998 Sb.»
  - Subtitle color `paper-soft-on-dark`: «Vivadzen provozujeme pod licencí Ministerstva zdravotnictví ČR. Toto je jediný způsob, jakým lze v České republice legálně prodávat psychomodulační látky.»
  - 2 chip-бейджа: «Licence MZ ČR» + «Specializovaný e-shop dle §33d»
- Правая часть: декоративный бейдж/печать (Nano Banana или SVG-компонент)

### 1.4. Section 1 — Co je licence MZ ČR
- Background `paper`
- H2: «Co je licence MZ ČR pro PML»
- Body 200–300 слов: что такое лицензия, какие требования, почему она важна
- Bullet list с конкретными требованиями:
  - Spojení s autorizovanou kamennou prodejnou
  - Ověření věku 18+ (online + při převzetí)
  - Označení dle §33e
  - Pravidelné kontroly MZ ČR
  - Akreditované laboratorní testy

### 1.5. Section 2 — Naše licence
- Background `cream`
- Большая карточка (Vivadzen-license card):
  ```
  ┌─────────────────────────────────────┐
  │ POVOLENÍ K PRODEJI PSYCHOMODULAČNÍCH │
  │            LÁTEK (PML)                │
  │                                       │
  │ Číslo povolení: ___                  │
  │ Vydáno: ___                          │
  │ Vydal: Ministerstvo zdravotnictví ČR │
  │ Platnost: ___                        │
  │                                       │
  │ Provozovna #1: {adresa}              │
  │ Provozovna #2: {adresa}              │
  │ E-shop: vivadzen.{cz|com}            │
  │                                       │
  │ IČO: ___ · DIČ: ___                  │
  └─────────────────────────────────────┘
  ```
  - Background `paper`, border 2 px `lime`, radius `rounded-xl`, padding 32, shadow-card
- CTA: «Stáhnout kopii povolení (PDF)» (если можно публиковать → согласовать с юристом)

### 1.6. Section 3 — Co to znamená pro vás
- Background `paper`
- Grid 3 колонки (icon + title + body):
  - `shield-check` lime — Garantovaná legálnost
  - `flask-conical` lime — Lab-testovaná šarže
  - `store` lime — Kamenná prodejna v Praze
- Под grid — info: «Bez licence MZ ČR nelze v ČR legálně prodávat kratom. Pokud váš dodavatel licenci nemá, porušuje zákon a nemůže zaručit kvalitu ani bezpečnost.»

### 1.7. Section 4 — Kontroly a transparentnost
- Background `cream`
- Текст об инспекциях MZ ČR, отчётности, прозрачности

### 1.8. Section 5 — FAQ
- 6 вопросов о лицензии (из SEO-файла 4.1)

### 1.9. SEO
- Title/H1/meta — см. SEO-файл 4.1
- Schema: WebPage + Organization (с licenseNumber если можно)

---

## 2. STRÁNKA `/laboratorni-testy` — COA-хаб

### 2.1. Hero
- Background `forest`
- H1 Playfair regular: «Laboratorní testy — všechny šarže»
- Subtitle: «Každá šarže prochází nezávislými laboratorními testy ve VŠCHT Praha, akreditované laboratoři dle normy ISO 17025.»
- 3 chips: «ISO 17025» / «VŠCHT Praha» / «8 aktivních šarží»

### 2.2. Stats row
- 4 stats:
  - 100 % — Testovaných šarží
  - 25+ — Parametrů na test
  - 14 — Dní průměrná doba testu
  - 8 — Aktivních šarží

### 2.3. Section «Co testujeme»
- Background `paper`
- 5 карточек (icon + title + body):
  - Obsah mitragyninu — Inter 600 + Mitragynin %
  - 7-hydroxymitragynin — alkaloid, malé množství
  - Čistota
  - Mikrobiologie (ČSN ISO 21527)
  - Těžké kovy (Pb, Cd, Hg, As)

### 2.4. Section «Aktivní šarže»
- Background `cream`
- Big table со всеми активными шаржами:
  ```
  ŠARŽE         PRODUKT              MITRAGYNIN  DATUM      COA
  VD-2026-014   Červená Maeng Da     1,42 %      12.03.2026 [PDF] [→]
  VD-2026-013   Zelená Maeng Da      1,38 %      12.03.2026 [PDF] [→]
  …
  ```
- Click na шаржу → `/laboratorni-testy/{id}` (отдельная страница каждой шарже)
- Фильтр сверху: «Produkt ▾ | Datum ▾»

### 2.5. Section «Akreditovaná laboratoř VŠCHT Praha»
- Background `paper`
- Grid 2 cols: text + photo lab-style (Nano Banana 3.5)
- Текст: о VŠCHT, ISO 17025, методиках

### 2.6. Per-šarже страница `/laboratorni-testy/{id}`
- Hero: H1 «Šarže VD-2026-014»
- Sub: «Produkt: Červená Maeng Da · Datum testu: 12.03.2026»
- Полная COA-таблица (как в Section 4 product page)
- Embed PDF preview (опц., с fallback кнопкой Stáhnout)
- «Tato šarže je dostupná v produktech: [list product cards]»
- Schema: Article + про šarже

---

## 3. STRÁNKA `/o-nas` — О нас + Odborný garant

### 3.1. Hero
- Background `forest`
- Eyebrow `lime`: `PŘÍBĚH ZNAČKY`
- H1 Playfair italic: «Vivadzen — důvěra postavená na licenci a laboratoři»
- Subtitle: «Spojili jsme dvě kamenné prodejny v Praze do jednoho moderního e-shopu. Provozujeme pod licencí MZ ČR.»

### 3.2. Section «Naše cesta»
- Timeline-стиль:
  - 2020 — otevření první prodejny
  - 2023 — druhá prodejna
  - 2025 — získání licence MZ ČR po legislativní změně
  - 2026 — start e-shopu Vivadzen
- Каждая точка — icon `circle` + год + текст

### 3.3. Section «Odborný garant»
- Background `cream`
- Card layout:
  ```
  [фото 240×240 круглая, lab-стиль]    {Jméno garanta}
                                         {Pozice / titul}
                                         
                                         Krátká bio: 2–3 odstavce o
                                         vzdělání, zkušenostech v
                                         oboru, proč Vivadzen.
                                         
                                         [LinkedIn] [E-mail]
  ```
- Это **критично** для E-E-A-T (см. SEO-файл 5.0).

### 3.4. Section «Hodnoty»
- 4 карточки: Licence · Kvalita · Transparentnost · Odpovědnost

### 3.5. Section «Naše prodejny v Praze»
- Teaser-link на `/prodejny`

### 3.6. SEO
- Title: см. SEO-файл 4.7
- Schema: AboutPage + Person (garant)

---

## 4. STRÁNKA `/prodejny` — Магазины (LocalBusiness)

### 4.1. Hero
- Background `forest`
- H1: «Naše prodejny v Praze»
- Subtitle: «Osobní odběr, poradenství a kratom přímo z prodejny.»

### 4.2. Карта
- Embed Google Maps с 2 маркерами
- Радиус rounded-xl, height 400 desktop / 280 mobile

### 4.3. 2 cards (по точке)
```
[фото prodejny 16:9]
Prodejna Praha {město část}                          [Status: Otevřeno]
{Ulice ...}, Praha {1/2/...}

[icon clock]  Po–Pá  10:00–19:00
              So     10:00–14:00
              Ne     zavřeno
[icon phone]  +420 ...
[icon mail]   {prodejna}@vivadzen.{cz}

[Detail prodejny →]   [Trasa →]   [Zavolat]
```

### 4.4. Per-prodejna страница `/prodejny/{slug}`
- Hero photo
- Все contacts, hours, ID/DIČ
- Google Reviews embed (для этой точки)
- LocalBusiness schema (см. SEO-файл 6 §7.7)
- Block «K vyzvednutí dostupné produkty» — 8 mini-карточек
- «Cesta MHD / autem» — instructions

### 4.5. SEO
- См. SEO-файл 4.4

---

## 5. STRÁNKA `/kontakt` — Контакт

### 5.1. Layout
- Background `paper`
- Grid 60/40

### 5.2. Левая колонка — Form
- H2: «Napište nám»
- Form:
  - Jméno*
  - E-mail*
  - Telefon (опц.)
  - Tema (select): Otázka k produktu / Reklamace / Spolupráce / Tisk / Jiné
  - Číslo objednávky (опц., if applicable)
  - Zpráva* (textarea)
  - GDPR checkbox*
  - Submit
- После submit — toast «Děkujeme, ozveme se do 24 hodin.»

### 5.3. Правая колонка — Contacts
```
[icon mail]    info@vivadzen.{cz}
               Reagujeme do 24 hodin.

[icon phone]   +420 ...
               Po–Pá 10:00–18:00

[icon message-circle]   Live chat
                        Po–Pá 10:00–18:00

[icon map-pin] 2 prodejny v Praze
               [Zobrazit prodejny →]
```

### 5.4. Под формой — Section «Časté témata»
- 4 link-карточки: «Reklamace» · «Sledování zásilky» · «Předplatné» · «Vrácení zboží»

---

## 6. STRÁNKA `/podpora` — Поддержка (отдельная, как вы просили)

### 6.1. Цель
Это **не FAQ** — это интерактивная точка входа для решения проблем клиента. Структурированная по темам, ведёт либо к ответу, либо к форме обращения.

### 6.2. Hero
- Background `forest`
- H1 Playfair italic: «Jsme tu pro vás»
- Subtitle: «Pomůžeme vám s objednávkou, předplatným nebo otázkami k produktům. Odpovídáme do 24 hodin.»
- Большой search-input: «Co potřebujete vyřešit?» (search-action через FAQ)

### 6.3. 4 quick-action cards (после hero)
```
┌─────────────────────┐
│ [icon truck large]   │
│ Otázky k doručení    │
│ Sledování · zpoždění │
│ změna adresy         │
│ [Zobrazit →]         │
└─────────────────────┘
```
- Темы: Doručení · Platby a fakturace · Předplatné · Vrácení a reklamace · Otázky k produktu · Účet a přihlášení · Něco jiného
- Click — открывает соответствующую категорию ниже

### 6.4. Категории (раздел секций)
Для каждой темы — H2 + список аккордеонов-вопросов + кнопка «Tato odpověď mi nepomohla — napsat nám»

**Topic 1: Doručení a sledování**
- Jak sledovat zásilku?
- Jak změnit doručovací adresu?
- Co když nejsem doma při dodání?
- Express 180 min — co když nestihnete?
- ...

**Topic 2: Platby a fakturace**
- Kde najdu fakturu?
- Co když platba nedorazila?
- Vrácení peněz po reklamaci...

**Topic 3: Předplatné**
- Jak pozastavit/zrušit?
- Jak změnit interval?
- Co když chci vynechat jeden cyklus?

**Topic 4: Vrácení a reklamace**
- Jak vrátit zboží?
- Reklamace formulář
- Lhůty

**Topic 5: Otázky k produktu**
- Mám otázku k šarži / COA
- Mám otázku k odrůdě
- (Ссылка на product page Q&A)

**Topic 6: Účet**
- Obnovení hesla
- Změna e-mailu
- Smazání účtu

### 6.5. Внизу секций — «Stále potřebujete pomoc?»
- Большой block с формой обращения (similar to Kontakt §5.2) + опции «Chat» / «E-mail» / «Telefon»
- В форме поле «Tema» pre-fills из контекста (с какой темы пришёл)

### 6.6. Statusová stránka (опц., stretch)
- Маленький компонент в footer: «Status služeb: ✓ vše funguje» (или предупреждение об инциденте)

### 6.7. SEO
- Title: `Podpora a pomoc | Vivadzen`
- Meta: `Pomůžeme vám s objednávkou, předplatným, doručením nebo otázkami k produktům. Live chat, e-mail i telefon. Odpovídáme do 24 hodin.`
- Schema: ContactPage + FAQPage

---

## 7. STRÁNKA `/caste-dotazy` — FAQ Хаб

### 7.1. Hero
- Background `cream`
- H1: «Časté dotazy»
- Subtitle: «Odpovědi na nejčastější otázky o kratomu, legislativě, objednávkách a Vivadzen.»
- Search input

### 7.2. Категории (tabbed):
- O kratomu a zákonu
- Objednávky a doručení
- Platby a fakturace
- Předplatné
- Účet a registrace
- Bezpečnost a 18+

### 7.3. Под каждой категорией — аккордеоны
- 8–15 вопросов на категорию
- Каждый — ответ 50–100 слов + link на профильную страницу для деталей

### 7.4. SEO
- Title/H1/meta — см. SEO-файл 4.10
- Schema: FAQPage (на каждый раздел / общий)

---

## 8. STRÁNKA `/pruvodce` — Контент-хаб

### 8.1. Hero
- Background `forest`
- Eyebrow `lime`: `OVĚŘENÉ INFORMACE`
- H1 Playfair italic: «Průvodce světem kratomu»
- Subtitle: «Co je kratom, jaký je rozdíl mezi barvami, co říká zákon a jak poznat kvalitní produkt — bez marketingových frází.»

### 8.2. Featured pillar block
- Large card: «Pilíř průvodce: Co je kratom»
- Background `cream`, padding 40, radius 2xl
- 16:9 image + H3 + subtitle + CTA «Číst kompletní průvodce →»

### 8.3. Grid гайдов (12 карточек)
- 3 ряда по 4 колонки desktop
- MiniArticleCard:
  - Картинка hero статьи (Nano Banana по 3.7)
  - Eyebrow с категорией (LEGISLATIVA / KVALITA / BEZPEČNOST / ZÁKLADY)
  - H4 название
  - Body 2 строки превью
  - «Číst více →»
- Карточки на:
  - Co je kratom (pillar)
  - Kratom a zákon 2026 (legislativ-pillar)
  - Barvy kratomu
  - Druhy a odrůdy
  - Jak poznat kvalitní kratom
  - Kratom — rizika a bezpečnost
  - Fakta a mýty
  - Kratom čaj — tradiční příprava
  - Skladování kratomu
  - (entity pages) Mitragyna speciosa / Mitragynin / Historie

### 8.4. Bottom — CTA в коммерцию
- Banner: «Připraveni vybrat? Skladem 8 produktů, každá šarže s COA.» → CTA `/kratom`

---

## 9. ШАБЛОН СТАТЬИ `/pruvodce/{slug}` И `/encyklopedie/{slug}`

### 9.1. Layout
- Header + breadcrumbs
- Background `paper`
- Container `container-narrow` (max 760, оптимально для long-read)
- Padding 64 vertical

### 9.2. Hero статьи
- Eyebrow uppercase с категорией (color `terracotta`)
- H1 Playfair regular 44 px / 32 mobile
- Под H1: meta (Author photo small + name + «odborný garant» + date «12. 03. 2026 · aktualizováno 15. 03. 2026»)
- Hero image 16:9 (Nano Banana 3.7)
- Reading time: «10 min čtení» chip

### 9.3. Содержимое статьи
- Body Inter 400 17 px desktop / 16 mobile, line-height 1.7
- Color `ink`
- Max width 70 ch
- H2 Playfair regular 28 px, margin-top 48
- H3 Inter 600 22 px, margin-top 32
- Links: underline thin, `forest`, hover `lime-deep`
- Blockquote: border-left 4 px `lime`, italic, padding-left 20
- Code (если есть): rare on this site
- Lists: bullets с indent

### 9.4. TOC (sticky на desktop, справа от текста)
- На страницах ≥ 1500 слов
- Sticky card с anchor-links на H2
- Active section highlighted
- Mobile: collapsable «Obsah článku» в начале статьи

### 9.5. Внутренние блоки

**Pull-quote (для выделения важных фраз):**
- Background `cream-soft`, border-left 4 px `lime`, padding 24, italic Playfair, large

**Info-callout:**
- Background `info` 10% + border `info`, icon `info`, padding 20

**Warning-callout (для secций risk):**
- Background `warning` 10% + border `amber`, icon `alert-triangle`

**Disclaimer-блок:**
- В конце статьи, фон `mist` 10%, padding 24, шрифт меньше, цвет `ink-soft`

### 9.6. CTA внутри статьи (ненавязчиво)
- Раз в статье — inline card (commercial bridge): «Nakupujete u licencovaného prodejce. Zobrazit kratom →»

### 9.7. Footer статьи
- Author block (фото 64×64 + bio короткое + link на /o-nas)
- «Tento článek vám pomohl? [👍 95 lidí]» (likes)
- Share buttons (FB / X / copy link)
- «Související články» — 3 cards

### 9.8. Schema
- Article + Person (author) + Publisher (Organization) + BreadcrumbList + FAQPage если есть FAQ

---

## 10. STRÁNKA `/blog` + `/blog/{slug}`

### 10.1. Listing `/blog`
- Hero compact: «Blog Vivadzen» + sub
- Tabs categorií: Legislativa / Kvalita / Novinky / Vzdělávání
- Grid карточек постов (4 в ряд desktop, 2 mobile)
- Каждая карточка: hero image + eyebrow + H4 + date + reading time + author avatar
- Пагинация / lazy-load

### 10.2. Post `/blog/{slug}`
- Тот же шаблон, что в §9 (articles), но:
  - Категория «BLOG» в breadcrumbs
  - Тема в eyebrow
  - В footer — «Více článků v této kategorii»

---

## 11. STRÁNKA `/recenze` — Хаб отзывов

### 11.1. Layout
- Aggregated reviews summary
- Top: общий 4,9 ★ + кол-во + Google badge + Heuréka badge
- Photo-grid фотоотзывов (как на product page §9.3, но shared всех товаров)
- Listing all reviews с фильтрами

### 11.2. SEO
- Title: см. SEO-файл 4.9
- Schema: AggregateRating

---

## 12. LEGAL СТРАНИЦЫ

### 12.1. `/obchodni-podminky` — Условия
- Long-text страница, шаблон из §9 без hero-image
- Содержимое: ваш юрист готовит, я даю шаблон вёрстки
- Структура: H2 секции по пунктам, нумерация
- Date: «Platnost od ___»
- В footer: «Stáhnout PDF» link

### 12.2. `/ochrana-soukromi` — GDPR
- Same layout
- Содержимое — privacy policy (юрист)
- Чёткие секции: Jaké údaje sbíráme · K čemu · Jak chráníme · Vaše práva · Kontakt na DPO

### 12.3. `/cookies` — Cookie policy
- Same layout
- Список используемых cookies (tech / analytics / marketing) с описанием
- Linky на Settings / Withdraw consent

### 12.4. `/reklamace` — Reklamace
- Same layout
- Lhůty, procedura, link na formulář
- «Reklamovat zboží →» (CTA → форма в /podpora)

### 12.5. `/doprava-a-platba` — Доставка и оплата
- Расширенная страница (не только в FAQ)
- Все способы доставки detail (ceny, lhůty, Express 180 min условия)
- Все способы оплаты detail
- Trust-блок (безопасность, GDPR)
- Возврат и обмен
- См. SEO-файл 4.6

### 12.6. `/overeni-veku` — Возрастная верификация
- Как мы проверяем возраст (online + при доставке)
- Что нужно показать курьеру
- Почему это закон
- См. SEO-файл 4.8

---

## 13. ДОПОЛНИТЕЛЬНЫЕ СТРАНИЦЫ

### 13.1. 404 (не найдено)
- Background `forest`
- Illustration: лист с увеличительным стеклом (Nano Banana кастомная)
- H1 Playfair italic: «404 — Stránka nenalezena»
- Body: «Zdá se, že tato stránka neexistuje nebo byla přesunuta.»
- Search input + быстрые ссылки (Kratom · Prodejny · Pruvodce · Podpora)

### 13.2. 500 / Error
- Similar layout, но с другим message + retry-кнопка

### 13.3. Maintenance
- «Provádíme údržbu, vrátíme se brzy.»
- Логотип + ETA

---

## 14. ИНТЕГРАЦИИ И ВНЕШНИЕ СЕРВИСЫ

- **Google Reviews embed:** через Google My Business Reviews widget или собственная подгрузка через Places API → отображение в footer карточек
- **Heuréka:** «Ověřeno zákazníky» widget — встроить на `/`, `/recenze`, product pages footer
- **Live chat:** Tawk.to (бесплатный) или Smartsupp (платный CZ) — но не загружать тяжёлые скрипты, ленивая инициализация
- **Tracking зásilek:** интеграция с Messenger курьерским API + DPD/PPL/Zásilkovna
- **Платёжный гейтвей:** Comgate / GoPay / Stripe (с Apple/Google Pay)
- **Email:** SendGrid / Mailgun для transactional
- **Newsletter:** Mailchimp / Ecomail (CZ)

---

## 15. ОБЩИЕ ЗАМЕЧАНИЯ

- Все trust/content/legal страницы — `index,follow` (см. SEO-файл 6 §3)
- Каждая статья и каждая trust-страница имеет `author` (odborný garant) для E-E-A-T
- На всех страницах — sticky 18+ строка (Global Components §8) и age-gate первый раз
- Накопление UGC (отзывы, Q&A, фото) — постепенно, не симулировать на старте
- Все формы — с inline-валидацией, GDPR checkbox, anti-spam (honeypot, не CAPTCHA для UX)

→ Дальше — файл 09 (короткий cheatsheet, чек-листы Definition of Done для каждой страницы).
