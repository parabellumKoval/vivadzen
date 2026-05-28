# VIVADZEN — SEO ТЗ
## Файл 3/7 — ТЗ коммерческих страниц (полное, постранично)

> Все on-page элементы — на чешском (продакшн-готовые). Комментарии — на русском.
> Для повторяющихся типов (товарные карточки, цветные категории, strain-хабы) даю **полный шаблон ТЗ + матрицу различий по каждому URL** — это профессиональный формат сдачи (23 копии одной структуры не нужны; копирайтер берёт шаблон + строку матрицы).
> **Комплаенс-фильтр (см. файл 0, §3) применяется к КАЖДОЙ строке текста безусловно.**

---

# 3.1. ГЛАВНАЯ `/` — вес 10

**Цель:** бренд + доверие + распределение веса + локальный сигнал. Не продающая «портянка», а быстрый навигационный/trust-хаб.

**Целевые ключи:** Vivadzen, kratom s licencí, kratom Praha, kratom eshop (бренд+trust, без агрессивного коммерч. промиса).

- **Title (≤60):** `Vivadzen — kratom s licencí MZ ČR | prodejny Praha`
- **Meta description (≤155):** `Specializovaný e-shop s kratomem provozovaný licencovanou kamennou prodejnou v Praze. Laboratorně testované šarže, doručení po celé ČR, osobní odběr. Pouze 18+.`
- **H1:** `Vivadzen — specializovaný kratom e-shop s licencí MZ ČR`
- **Структура (H2):**
  1. H2 `Kratom od licencovaného prodejce` — 2–3 предложения о статусе PML, лицензии, 18+. Ссылка → `/licence`.
  2. H2 `Naše kategorie` — сетка: Kratom prášek, Kratom extrakt, Zelený, Bílý, Červený, Žlutý, Podle odrůdy (Maeng Da…). Каждая ссылка с анкором-названием.
  3. H2 `Proč Vivadzen` — 4 trust-блока: Licence MZ ČR / Laboratorní testy každé šarže / Dvě kamenné prodejny v Praze / Doručení po celé ČR a osobní odběr. Ссылки → `/licence`, `/laboratorni-testy`, `/prodejny`, `/doprava-a-platba`.
  4. H2 `Kratom v Praze` — 2–3 предложения + ссылка → `/kratom-praha`, `/prodejny`.
  5. H2 `Poradna a fakta o kratomu` — 3 карточки → `/pruvodce/co-je-kratom`, `/pruvodce/kratom-zakon-2026`, `/pruvodce/jak-poznat-kvalitni-kratom`.
  6. H2 `Časté dotazy` — 4–5 FAQ (см. ниже) с FAQPage schema.
- **Объём текста:** 600–900 слов суммарно по блокам, без «воды», без effect-claims.
- **Обязательные вхождения (естественно):** «kratom», «licence», «laboratorně testováno», «Praha», «18 let», «psychomodulační látka», «Vivadzen».
- **Внутренние ссылки:** ≥ 10 (категории + 4 trust + poradna). Анкоры — по смыслу.
- **CTA:** «Prohlédnout kratom» → `/kratom` (hero, первичный).
- **Бейдж 18+** в шапке (постоянно) + строка PML-статуса.
- **FAQ (cs):**
  - `Je nákup kratomu u Vivadzen legální?` → краткий ответ про лицензию + ссылка на /licence.
  - `Musím být starší 18 let?` → да, двойная верификация, ссылка /overeni-veku.
  - `Kam doručujete?` → по всей ЧР через kurýra + osobní odběr Praha, ссылка /doprava-a-platba.
  - `Jsou produkty laboratorně testované?` → да, COA по šarже, ссылка /laboratorni-testy.
  - `Kde jsou vaše kamenné prodejny?` → 2× Praha, ссылка /prodejny.
- **Schema:** `Organization` + `WebSite` (sitelinks searchbox) + `FAQPage`. (JSON-LD — файл 6.)
- **Изображения:** hero (магазин/упаковка, не «эффектные» лайфстайл-обещания), `alt="Vivadzen kratom prodejna Praha"`. Логотип в Organization.logo.
- **Тех. заметки:** LCP-элемент = hero; критический CSS инлайн; без тяжёлых слайдеров.

---

# 3.2. `/kratom` — товарный pillar (бывший /catalog) — вес 9

**Цель:** головной коммерческий хаб; ранжирование по «kratom / kratom koupit / kratom eshop»; раздаёт вес во все категории и товары.

- **Title (≤60):** `Kratom — prášek a extrakt s licencí, lab. testy | Vivadzen`
- **Meta description (≤155):** `Kratom od licencovaného prodejce: zelený, bílý, červený a žlutý prášek i extrakt. Každá šarže laboratorně testovaná. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** `Kratom prášek a extrakt`
- **Структура:**
  - Интро 80–120 слов: что это за раздел, статус PML/лицензия, 18+, ссылка → `/licence`, `/laboratorni-testy`. Без обещаний эффекта.
  - H2 `Kratom podle barvy` — карточки 4 цветов (ссылки на категории).
  - H2 `Kratom podle formy` — Prášek / Extrakt / Nano (ссылки).
  - H2 `Kratom podle odrůdy` — Maeng Da, Sumatra, Thajský, Elephant, Bali, Borneo (ссылки на strain-хабы).
  - H2 `Skladem` — сетка 8 live товаров (карточки: фото, название, фасовка 25/50 g, цена, бейдж «Skladem», «Laboratorní test»).
  - H2 `Připravujeme` — сетка placeholder-товаров с бейджем «Momentálně nedostupné» + «Upozornit při naskladnění» (без цены/корзины; см. файл 6 по комплаенсу).
  - H2 `Jak vybírat kratom` — 100–150 слов, ссылки → `/pruvodce/barvy-kratomu`, `/pruvodce/jak-poznat-kvalitni-kratom`.
  - H2 `Časté dotazy` (5 FAQ + schema).
- **Объём:** 900–1300 слов (интро + блоки + FAQ; описания категорий по 2–3 предложения).
- **Вхождения:** «kratom», «kratom prášek», «kratom extrakt», «zelený/bílý/červený/žlutý kratom», «laboratorně testováno», «licence», «18 let».
- **Сортировка/фильтры:** фильтр по цвету/форме/штамму/наличию/цене/фасовке. Все фильтрованные состояния → `rel=canonical` на `/kratom` (правило в файле 6); SEO-ценные срезы (цвет) имеют отдельные статические URL-категории, а не параметры.
- **Внутр. ссылки:** ≥ 30 (все категории + strain + товары + 4 trust + 2 гайда).
- **FAQ (cs):** `Jaký kratom si vybrat podle barvy?`, `Jaký je rozdíl mezi práškem a extraktem?`, `Co znamená laboratorně testovaná šarže?`, `Proč jsou některé produkty nedostupné?`, `Jak probíhá ověření věku 18+?`.
- **Schema:** `CollectionPage` + `BreadcrumbList` + `ItemList` (товары) + `FAQPage`.
- **Изображения:** превью товаров, `alt="{Název produktu} kratom prášek 25 g"`.

---

# 3.3. `/kratom-prasek` — тип «порошок» — вес 8

- **Title:** `Kratom prášek — zelený, bílý, červený | laboratorně testováno`
- **Meta description:** `Kratom prášek od licencovaného e-shopu Vivadzen. Jemně mletý i nano prášek, balení 25 g a 50 g, laboratorně ověřené šarže. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** `Kratom prášek`
- **Контент-блоки:**
  - Интро 100–150 слов: что такое kratom prášek как форма (помол, jemný/nano), фасовка 25/50 g, статус PML/лицензия. **Без эффектов.**
  - H2 `Prášek podle barvy` (ссылки на цветные категории, фильтрованные по форме=prášek).
  - H2 `Nano kratom` — описание помола Rurut Nano (ссылка на товар), фактологически.
  - H2 `Skladem v prášku` — сетка порошковых live-товаров.
  - H2 `Jak poznat kvalitní kratom prášek` — 120 слов, ссылка → `/pruvodce/jak-poznat-kvalitni-kratom`, `/laboratorni-testy`.
  - H2 FAQ (5).
- **Объём:** 800–1100 слов.
- **Вхождения:** «kratom prášek», «jemný/nano prášek», «25 g», «50 g», «mletí», «šarže», «laboratorní test».
- **Внутр. ссылки:** ≥ 15.
- **FAQ:** `Jaký je rozdíl mezi jemným a nano práškem?`, `Jak se kratom prášek skladuje?` (→ /pruvodce/skladovani-kratomu), `Jaká balení nabízíte?`, `Je prášek laboratorně testovaný?`, `Jaký prášek pro začátek?` (нейтрально, без дозировки-рекомендации — отсылка к гайду).
- **Schema:** CollectionPage + BreadcrumbList + ItemList + FAQPage.

---

# 3.4. `/kratom-extrakt` — тип «экстракт» — вес 7

- **Title:** `Kratom extrakt 10 ml — zelený, laboratorně testovaný | Vivadzen`
- **Meta description:** `Kratom extrakt od licencovaného prodejce Vivadzen. Zelený extrakt 10 ml, laboratorně ověřená šarže, údaje o obsahu alkaloidů. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** `Kratom extrakt`
- **Блоки:** интро (что такое экстракт как форма — концентрация, отличие от порошка фактологически, без промиса) → H2 `Skladem` (товар Kratom Extrakt 10ml) → H2 `Extrakt vs prášek` (фактическое сравнение формы, ссылка на /kratom-prasek и гайд) → H2 `Laboratorní testy extraktu` (ссылка /laboratorni-testy) → FAQ.
- **Объём:** 600–900 слов. Ссылки ≥ 10.
- **FAQ:** `Čím se liší extrakt od prášku?`, `Jaké balení extraktu nabízíte?`, `Je extrakt laboratorně testovaný?`, `Spadá extrakt také pod zákon o PML?` (да — ссылка /pruvodce/kratom-zakon-2026).
- **Schema:** CollectionPage + ItemList + FAQPage + BreadcrumbList.

---

# 3.5. ШАБЛОН ТЗ: ЦВЕТНЫЕ КАТЕГОРИИ (zelený / bílý / červený / žlutý) — вес 7–8

**Применяется к 4 URL.** Общая структура одна; уникализация — через матрицу ниже (каждая страница имеет уникальный интро-текст про обработку/сушку/жилку именно этого цвета — это фактология, не эффекты).

**Общий каркас:**
- **Title:** `{Cvet} kratom — prášek skladem, laboratorně testováno | Vivadzen`
- **Meta:** `{Cvet} kratom prášek od licencovaného e-shopu. {Уник. факт о цвете}. Balení 25 g a 50 g, laboratorní testy šarží. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** `{Cvet} kratom`
- **Структура:**
  - Интро 120–180 слов: чем определяется цвет (стадия сбора/сушка/ферментация жилки листа — **фактология**), регион, обработка. **БЕЗ** «stimuluje/relaxuje/dodává…». Допустимо: «Barva souvisí se způsobem sušení a zpracování listů Mitragyna speciosa.»
  - H2 `{Cvet} kratom skladem` — сетка товаров этого цвета (фильтр).
  - H2 `Odrůdy v {cvet} variantě` — ссылки на strain-хабы (Maeng Da, Sumatra…) релевантные цвету.
  - H2 `Jak se {cvet} kratom zpracovává` — 120–160 слов фактологии о сушке/помоле; ссылка → `/pruvodce/barvy-kratomu`.
  - H2 `Kvalita a laboratorní testy` — 80 слов, ссылки → `/laboratorni-testy`, `/licence`.
  - H2 `Časté dotazy` (4–5).
- **Объём:** 800–1100 слов. Внутр. ссылки ≥ 15.
- **Вхождения:** «{cvet} kratom», «{cvet} kratom prášek», «Mitragyna speciosa», «sušení/zpracování», «laboratorní test», «25 g / 50 g».
- **Запрещено:** любые сравнения «силы/эффекта» цветов как промис. Разрешено: «Čím se barvy liší» с отсылкой в гайд (там — нейтрально + дисклеймер).
- **Schema:** CollectionPage + BreadcrumbList + ItemList + FAQPage.
- **Канонизация:** каждый цвет — самостоятельный canonical; не конфликтует с strain-хабом (взаимные контекстные ссылки).

**Матрица уникализации цветных категорий:**

| URL | Уник. фактологический фокус интро (cs, без эффектов) | FAQ-акцент | Live-товары на странице |
|---|---|---|---|
| `/kratom/zeleny-kratom` | sběr listů ve střední zralosti, kratší sušení; barva žilky zelená | `Co znamená zelená žilka listu?` | Zelený Rurut Nano, Zelená Sumatra, Zelený thajský, Zelená Maeng Da, Kratom Extrakt |
| `/kratom/bily-kratom` | mladší listy, delší sušení bez přímého slunce, světlá žilka | `Proč je bílý kratom světlejší?` | Bílá Maeng Da, Bílý slon |
| `/kratom/cerveny-kratom` | déle/odlišně sušené nebo krátce fermentované zralé listy | `Jak vzniká červená barva?` | Červená Maeng Da |
| `/kratom/zluty-kratom` | specifický proces sušení/míchání; **placeholder-категория** до наличия | `Čím se liší žlutý a zlatý kratom?` | пока placeholder-товары + «Upozornit» |

> `/kratom/zluty-kratom`: пока нет live-товаров — страница индексируется как информационно-категорийная (≥600 слов уникальной фактологии о жёлтом/золотом + энц. контент + lead-форма). Без `Offer` в schema до лицензии.

---

# 3.6. ШАБЛОН ТЗ: STRAIN-ХАБЫ (Maeng Da / Sumatra / Thajský / Elephant / Bali / Borneo) — вес 6–7

**Применяется к 6 URL.** Strain-хаб = обзор штамма (происхождение, регион, особенность обработки) + агрегация товаров этого штамма во всех цветах.

**Каркас:**
- **Title:** `{Strain} kratom — odrůda, původ a produkty | Vivadzen`
- **Meta:** `{Strain} kratom: původ z {регион}, charakteristika odrůdy a laboratorně testované produkty od licencovaného e-shopu Vivadzen. Doručení po ČR. 18+.`
- **H1:** `{Strain} kratom`
- **Структура:**
  - Интро 150–220 слов: что такое штамм/название, регион происхождения, особенность обработки/листа — **фактология/ботаника/гео**, без эффектов.
  - H2 `{Strain} skladem` — сетка релевантных live-товаров (если есть).
  - H2 `{Strain} v jednotlivých barvách` — ссылки на цветные категории + связанные товары.
  - H2 `Původ a zpracování odrůdy {Strain}` — 150 слов фактологии; ссылка → `/pruvodce/druhy-kratomu`, `/encyklopedie/mitragyna-speciosa`.
  - H2 `Laboratorní testy` → `/laboratorni-testy`.
  - H2 FAQ (4).
- **Объём:** 600–1000 слов. Ссылки ≥ 8.
- **Schema:** CollectionPage + BreadcrumbList + ItemList + FAQPage.

**Матрица strain-хабов:**

| URL | Уник. фокус (cs, факт/гео) | Статус | Live-товары | FAQ-акцент |
|---|---|---|---|---|
| `/kratom/maeng-da` | nejznámější označení odrůdy; vyrábí se v bílé/zelené/červené variantě | live | Bílá MD, Červená MD, Zelená MD | `Co znamená Maeng Da?` |
| `/kratom/sumatra` | původ ostrov Sumatra (Indonésie); charakteristika regionu | live | Zelená Sumatra | `Odkud pochází Sumatra kratom?` |
| `/kratom/thajsky` | tradiční oblast pěstování Thajsko/jihovýchodní Asie | live | Zelený thajský | `Co je thajský kratom?` |
| `/kratom/elephant` | označení podle velikosti listů; «bílý slon» = White Elephant | live | Bílý slon | `Proč se říká „slon“?` |
| `/kratom/bali` | indonéská oblast Bali/Borneo, klasická odrůda | **placeholder** | — (lead-форма) | `Připravujeme — co je Bali kratom?` |
| `/kratom/borneo` | ostrov Borneo (Kalimantan), Indonésie | **placeholder** | — (lead-форма) | `Připravujeme — co je Borneo kratom?` |

---

# 3.7. ПОЛНЫЙ ШАБЛОН ТЗ: ТОВАРНАЯ КАРТОЧКА (8 live товаров) — вес 6

**Применяется к 8 URL** (URL уже существуют — сохраняем, дорабатываем on-page). Уникализация — матрица в §3.8.

### Структура товарной страницы
- **URL:** существующий (напр. `/zelena-maeng-da-kratom-prasek-25g`). ⚠️ Рекомендация: фасовка 25/50 g — **варианты в селекторе на ОДНОМ URL**, не два URL. Если сейчас URL содержит `-25g` — оставить как canonical, 50 g сделать вариантом без отдельного индексируемого URL (см. файл 6). Дубль `-50g` → canonical на основной.
- **Title (≤60):** `{Název produktu} 25/50 g — laboratorně testováno | Vivadzen`
  - напр. `Zelená Maeng Da kratom prášek 25/50 g | Vivadzen`
- **Meta description (≤155):** `{Název} — kratom prášek od licencovaného e-shopu. Balení 25 g a 50 g, laboratorně testovaná šarže, údaj o obsahu mitragyninu. Doručení po ČR, osobní odběr Praha. 18+.`
- **H1:** `{Název produktu}` (точное коммерч. название)
- **Блоки страницы:**
  1. Галерея (фото упаковки/порошка; alt: `{Název} kratom prášek balení 25 g`).
  2. Покупка: селектор 25 g / 50 g, цена за вариант, наличие «Skladem», кнопка «Do košíku», бейдж 18+, иконки оплат (dobírka/QR/převod/online), «Osobní odběr Praha».
  3. H2 `Popis produktu` — 180–280 слов **фактологии**: цвет жилки, штамм, регион происхождения, способ обработки/помол, аромат/текстура, фасовка. **Без эффектов и без дозировок-рекомендаций.**
  4. H2 `Laboratorní test této šarže` — таблица: obsah mitragyninu (%), 7-OH (если в COA), čistota, mikrobiologie, těžké kovy, datum testu, číslo šarže; ссылка → `/laboratorni-testy` (+ конкретная šarže-страница, фаза 2). Это наш главный дифференциатор.
  5. H2 `Označení a původ` — Mitragyna speciosa, регион, что соответствует обязательной маркировке §33e (без претензий-эффектов).
  6. H2 `Doprava, platba a osobní odběr` — кратко + ссылка → `/doprava-a-platba`.
  7. H2 `Právní informace` — статус PML, 18+, ссылка → `/licence`, `/pruvodce/kratom-zakon-2026`. Дисклеймер: не пища/не лекарство/не БАД.
  8. H2 `Související produkty` — 3–4 карточки (тот же цвет/штамм).
  9. H2 `Recenze` — отзывы (Review schema) + ссылка → `/recenze`.
  10. H2 `Časté dotazy k produktu` (3–4 FAQ + schema).
- **Объём текста:** 450–700 слов (без таблицы COA и отзывов).
- **Вхождения (естественно):** точное название, «kratom prášek», цвет, штамм, «25 g», «50 g», «laboratorně testováno», «mitragynin», «Mitragyna speciosa», «18 let».
- **Внутр. ссылки:** ≥ 4 (категория цвета, strain-хаб, /laboratorni-testy, /doprava-a-platba) + связанные товары + breadcrumbs.
- **Schema:** `Product` (name, image, sku, brand=Vivadzen, category) + `Offer` ×2 (25 g / 50 g: price, priceCurrency=CZK, availability=InStock, itemCondition=NewCondition) + `AggregateRating`/`Review` (если есть отзывы) + `BreadcrumbList` + `FAQPage`. **Никаких health-свойств в schema.** Полные JSON-LD — файл 6.
- **Комплаенс на карточке:** обязательны 18+ бейдж, PML-строка, дисклеймер, ссылка на лицензию; запрещены любые «účinky/pomáhá/energie» в описании и в alt.
- **CRO-заметки:** trust-иконки рядом с кнопкой (Licence MZ / Lab test / 2× prodejna Praha); COA-таблица «свёрнута, но видна» — это уникальный конверсионный + E-E-A-T-актив, которого нет у конкурентов в карточке.

### 3.8. Матрица 8 live-товаров (уникализация описаний)

| # | URL (slug) | Название (H1) | Цвет | Штамм | Форма | Уник. фактологический фокус Popis (cs) | Категория-линк | Strain-линк |
|---|---|---|---|---|---|---|---|---|
| 1 | `/kratom-extrakt-10ml-zeleny` | Kratom Extrakt 10 ml (zelený) | zelený | — | extrakt 10 ml | koncentrovaná forma, balení 10 ml, údaje COA, odlišení od prášku fakticky | /kratom-extrakt | — |
| 2 | `/zeleny-rurut-nano-kratom-prasek-25g` | Zelený Rurut Nano kratom prášek | zelený | Rurut | nano prášek | velmi jemné mletí (nano), zelená žilka, původ; balení 25/50 g | /kratom/zeleny-kratom | — |
| 3 | `/zelena-sumatra-kratom-prasek-25g` | Zelená Sumatra kratom prášek | zelený | Sumatra | prášek | původ ostrov Sumatra, zpracování, zelená žilka | /kratom/zeleny-kratom | /kratom/sumatra |
| 4 | `/bila-maeng-da-kratom-prasek-25g` | Bílá Maeng Da kratom prášek | bílý | Maeng Da | prášek | mladší listy, delší sušení, světlá žilka, Maeng Da | /kratom/bily-kratom | /kratom/maeng-da |
| 5 | `/bily-slon-kratom-prasek-25g` | Bílý slon kratom prášek | bílý | Elephant | prášek | White Elephant – velké listy, bílá žilka, zpracování | /kratom/bily-kratom | /kratom/elephant |
| 6 | `/zeleny-thajsky-kratom-prasek-25g` | Zelený thajský kratom prášek | zelený | Thai | prášek | tradiční thajská oblast, zelená žilka, zpracování | /kratom/zeleny-kratom | /kratom/thajsky |
| 7 | `/cervena-maeng-da-kratom-prasek-25g` | Červená Maeng Da kratom prášek | červený | Maeng Da | prášek | déle/odlišně sušené zralé listy, červená žilka, Maeng Da | /kratom/cerveny-kratom | /kratom/maeng-da |
| 8 | `/zelena-maeng-da-kratom-prasek-25g` | Zelená Maeng Da kratom prášek | zelený | Maeng Da | prášek | zralost ve střední fázi, zelená žilka, Maeng Da | /kratom/zeleny-kratom | /kratom/maeng-da |

> Каждое описание (блок 3) пишется уникально на основе колонки «фокус» — никакого дублирования между карточками (Google штрафует дубль-описания товаров). Минимум 60% уникального текста на карточку.

---

# 3.9. ШАБЛОН ТЗ: PLACEHOLDER-СТРАНИЦЫ (~15 будущих, без лицензии) — вес 3

**Юридически безопасная реализация (см. файл 0 §4 и файл 6).** Это **энциклопедически-каталожная запись штамма/варианта**, НЕ оферта.

- **URL:** `/kratom/{slug-varianty}` или `/encyklopedie/odrudy/{slug}` (рекомендую первый — копит вес каталога).
- **Title:** `{Název odrůdy} kratom — odrůda a charakteristika | Vivadzen`
- **Meta:** `{Název odrůdy} kratom: původ, zpracování a charakteristika odrůdy. Informační stránka licencovaného e-shopu Vivadzen. Dostupnost: připravujeme. 18+.`
- **H1:** `{Název odrůdy} kratom`
- **Контент (≥ 350–500 слов, уникально):** происхождение/регион, ботаника штамма, способ обработки/сушки/жилка, как соотносится с цветовой классификацией, что такое COA и почему мы тестируем. **Без цены, без кнопки корзины, без эффектов.**
- **Статусный блок:** бейдж `Momentálně nedostupné — připravujeme` + форма «Upozornit při naskladnění» (e-mail lead, double opt-in, GDPR).
- **Внутр. ссылки:** ≥ 2 (родительская цветная категория + relevant strain-хаб + `/laboratorni-testy`).
- **Schema:** `ItemPage`/`Article` + `BreadcrumbList`. **БЕЗ `Product`/`Offer` до получения лицензии** (после лицензии → апгрейд шаблона до §3.7 + `Product`+`Offer`+`OutOfStock`/`InStock`).
- **Индексация:** `index,follow` при ≥350 слов уникального контента; иначе `noindex,follow` до наполнения.
- **Кандидаты-15 (примерный список — финал за вами):** Bílá Bali, Zelená Bali, Červená Bali, Bílý Borneo, Zelený Borneo, Červený Borneo, Žlutá Maeng Da, Zlatý kratom, Zelený Jongkong, Zelený Kapuas Hulu, Červená Sumatra, Bílá Sumatra, Kratom kapsle, Kratom tablety, Stonky a žíly (Stem & Vein). (Под каждый — отдельный энц. текст по штамму/региону/обработке.)

> Эта схема даёт ~90% SEO-выгоды (индексация, возраст, перелинковка, lead-сбор) при минимальном правовом риске. Финальное «листинг ок/не ок» подтверждает ваш юрист по zák. č. 167/1998 Sb. — я заложил наиболее защищённую архитектуру, не выдавая юридическую гарантию, которой у меня нет.

→ Переходим к файлу 4 (trust / локальные / транзакционные страницы).
