# VIVADZEN — SEO ТЗ
## Файл 4/7 — ТЗ trust / локальных / транзакционных страниц

> Это E-E-A-T-ядро. Для YMYL-тематики (вещества/здоровье) именно эти страницы дают Google структурный сигнал доверия, который конкуренты не перебивают бюджетом. Прорабатываем их сильнее, чем конкуренты прорабатывают блог.

---

# 4.1. `/licence` — ЛИЦЕНЗИЯ MZ ČR — вес 7 (входящих ≥ 40)

**Цель:** доказать легальность; ранжироваться по «kratom s licencí / licencovaný kratom eshop / kratom licence MZ»; быть trust-якорем для всего сайта и для Google по YMYL.

- **Title (≤60):** `Licence MZ ČR pro prodej kratomu | Vivadzen`
- **Meta (≤155):** `Vivadzen prodává kratom na základě povolení Ministerstva zdravotnictví ČR dle zák. č. 167/1998 Sb. Specializovaný e-shop napojený na kamenné prodejny v Praze. 18+.`
- **H1:** `Licence pro prodej kratomu (psychomodulační látky)`
- **Структура (H2):**
  1. `Proč je licence povinná` — режим PML с 12.11.2025, зак. č. 167/1998 Sb. в ред. č. 321/2024 Sb., повеление MZ ČR. Ссылка → `/pruvodce/kratom-zakon-2026`.
  2. `Naše povolení` — тип повеления, орган (MZ ČR), что повеление непереводимо и привязано к конкретной деятельности; **скан/реквизиты повеления** (номер, дата) — изображение + текстовая расшифровка (для индексации).
  3. `Specializovaný e-shop + kamenná prodejna` — закон требует спец-eshop, привязанный к лицензированной каменной точке; у нас 2 в Праге. Ссылка → `/prodejny`.
  4. `Co licence znamená pro zákazníka` — гарантия легальной покупки, контроль качества, тесты, 18+. Ссылки → `/laboratorni-testy`, `/overeni-veku`.
  5. `Jak ověřit, že prodejce má licenci` — образоват. блок (как покупателю проверить любого продавца) — это link-bait/траст-магнит. Ссылка → `/pruvodce/jak-poznat-kvalitni-kratom`.
  6. FAQ (5).
- **Объём:** 700–1000 слов. Внутр. ссылки ≥ 6 (исходящие). Входящие — со всех коммерческих (footer trust-блок + контекст).
- **Вхождения:** «licence», «povolení Ministerstva zdravotnictví», «psychomodulační látka», «zák. č. 167/1998 Sb.», «specializovaný e-shop», «kamenná prodejna Praha», «18 let».
- **FAQ (cs):** `Má Vivadzen licenci na prodej kratomu?`, `Jaký zákon prodej kratomu upravuje?`, `Je nákup kratomu v ČR legální?`, `Proč musí mít e-shop kamennou prodejnu?`, `Jak si licenci ověřím?`.
- **Schema:** `WebPage` + `FAQPage` + `BreadcrumbList`; ссылка из `Organization` (`hasCredential`/`Organization.knowsAbout`) — детали файл 6.
- **Заметка:** не публиковать персональные/чувствительные данные сверх необходимого; реквизиты повеления — по согласованию с юристом. Скан повеления = `ImageObject` с расшифровкой текстом рядом (картинку Google не читает).

---

# 4.2. `/laboratorni-testy` — ЛАБОРАТОРНЫЕ ТЕСТЫ / COA — вес 7 (входящих ≥ 40)

**Цель:** уникальный дифференциатор; ранжироваться по «kratom laboratorní testy / COA / čistota / těžké kovy»; служить доказательной базой для каждой карточки.

- **Title (≤60):** `Laboratorní testy kratomu — COA každé šarže | Vivadzen`
- **Meta (≤155):** `Každá šarže kratomu od Vivadzen prochází laboratorním testem: obsah mitragyninu, mikrobiologie, těžké kovy. Protokoly (COA) dostupné u produktů. Licence MZ ČR, 18+.`
- **H1:** `Laboratorní testy kratomu — protokoly COA`
- **Структура:**
  1. `Proč testujeme každou šarži` — фактология качества/безопасности (без эффектов): идентичность, čistota, kontaminace.
  2. `Co protokol obsahuje` — список параметров: obsah mitragyninu, 7-hydroxymitragynin, mikrobiologie, těžké kovy, vlhkost, identita Mitragyna speciosa, datum, šarže.
  3. `Jak číst COA` — образоват. блок (как покупателю читать любой COA) — траст/линк-магнит. Ссылка → `/encyklopedie/mitragynin`.
  4. `Protokoly podle šarží` — таблица/листинг: šarže ID → продукт → ключевые значения → ссылка на PDF/страницу šarže (фаза 2: отдельные `/laboratorni-testy/{sarze}` с indexable текстовой расшифровкой + ImageObject PDF).
  5. `Testy a zákon` — связь с обязательной маркировкой §33e и режимом PML. Ссылка → `/pruvodce/kratom-zakon-2026`, `/licence`.
  6. FAQ (5).
- **Объём:** 700–1100 слов. Исходящие ссылки ≥ 5; входящие — с каждой карточки/категории.
- **Вхождения:** «laboratorní test», «protokol», «COA», «obsah mitragyninu», «mikrobiologie», «těžké kovy», «šarže», «čistota».
- **FAQ:** `Co je COA?`, `Jaké parametry testujete?`, `Kde najdu protokol ke konkrétnímu produktu?`, `Jak často testujete?`, `Co znamená obsah mitragyninu v %?`.
- **Schema:** WebPage + FAQPage + BreadcrumbList; страницы šarže — `Dataset`/`Article` + `ImageObject` (PDF-скан) + текст-расшифровка.
- **Критично:** PDF/скан без текстового дубликата Google не индексирует — обязательна **текстовая таблица значений рядом со сканом**.

---

# 4.3. `/kratom-praha` — ЛОКАЛЬНЫЙ КОММЕРЧЕСКИЙ ЛЕНДИНГ — вес 8

**Цель:** «kratom Praha» (коммерч.-локальный) + мост к самовывозу/магазинам. Один из сильнейших активов (у конкурентов Прага-страницы слабые/тонкие).

- **Title (≤60):** `Kratom Praha — prodejny a osobní odběr | Vivadzen`
- **Meta (≤155):** `Kratom v Praze od licencovaného prodejce Vivadzen: dvě kamenné prodejny, osobní odběr, doručení po Praze i celé ČR. Laboratorně testované šarže. 18+.`
- **H1:** `Kratom Praha`
- **Структура:**
  1. Интро 120 слов: licencovaný prodejce kratomu se 2 prodejnami v Praze, osobní odběr, doručení. Без эффектов.
  2. H2 `Naše prodejny v Praze` — 2 карточки с адресами/часами/картой → `/prodejny/...`.
  3. H2 `Osobní odběr v Praze` — как работает, оплата на месте, 18+ верификация при выдаче. Ссылка → `/doprava-a-platba`.
  4. H2 `Doručení v Praze a po ČR` — kurýr/messenger, способы оплаты. Ссылка → `/doprava-a-platba`.
  5. H2 `Kratom skladem` — сетка live-товаров (как на /kratom, сокращённо).
  6. H2 `Proč u nás` — licence + lab testy (ссылки).
  7. FAQ (4–5, локальные).
- **Объём:** 700–1000 слов уникального (НЕ копия /kratom). Ссылки ≥ 12.
- **Вхождения:** «kratom Praha», «kamenná prodejna Praha», «osobní odběr Praha», «doručení Praha», «licence», «18 let», названия районов точек.
- **FAQ:** `Kde koupit kratom v Praze?`, `Máte osobní odběr v Praze?`, `Jak rychle doručíte v Praze?`, `Mohu zaplatit na prodejně?`, `Ověřujete věk na prodejně?`.
- **Schema:** `WebPage` + `BreadcrumbList` + `FAQPage`; LocalBusiness — на под-страницах магазинов (4.4).
- **Локальное SEO:** связать с Google Business Profile обеих точек (NAP консистентность с `/prodejny`).

---

# 4.4. `/prodejny` + `/prodejny/praha-{lokalita}` ×2 — вес 6

**`/prodejny` (хаб):**
- **Title:** `Kamenné prodejny kratomu v Praze | Vivadzen`
- **Meta:** `Dvě kamenné prodejny Vivadzen v Praze: adresy, otevírací doba, osobní odběr a platba na místě. Licencovaný prodej kratomu, 18+.`
- **H1:** `Naše kamenné prodejny v Praze`
- **Контент:** 2 карточки (адрес, часы, телефон, карта, фото, «jak se k nám dostat»), блок osobní odběr, ссылка → `/kratom-praha`, `/doprava-a-platba`, `/licence`. 500–700 слов.
- **Schema:** `ItemList` of `LocalBusiness`.

**Каждая под-страница точки `/prodejny/praha-{lokalita}`:**
- **Title:** `Prodejna kratomu Praha — {lokalita/ulice} | Vivadzen`
- **Meta:** `Kamenná prodejna kratomu Vivadzen v Praze ({lokalita}): adresa, otevírací doba, osobní odběr, platba na místě. Licencovaný prodej, 18+.`
- **H1:** `Prodejna kratomu Praha — {lokalita}`
- **Контент 350–500 слов уникально:** точный адрес, NAP, часы, как добраться (MHD/parkování), что доступно (osobní odběr, платежи), фото витрины/входа (`alt`), 18+ верификация на месте. Ссылки → `/kratom-praha`, `/doprava-a-platba`.
- **Schema:** `LocalBusiness` (или `Store`): name, address (PostalAddress), geo, openingHoursSpecification, telephone, image, priceRange, areaServed=Praha. Консистентный NAP с GBP.
- **Локально:** каждая точка = отдельный Google Business Profile; embed Google Map; собирать отзывы на GBP.

> Две уникальные под-страницы — это НЕ doorway (разные реальные адреса/контент). Региональные города без физточки — не делаем тонкими лендингами (см. файл 7).

---

# 4.5. `/doprava-a-platba` — вес 4 (входящих ≥ 30: footer + все товары)

- **Title (≤60):** `Doprava a platba — dobírka, QR, převod | Vivadzen`
- **Meta (≤155):** `Doručení kratomu po celé ČR kurýrem, osobní odběr v Praze. Platba dobírkou, QR kódem, bankovním převodem nebo online. Ověření věku 18+ při převzetí.`
- **H1:** `Doprava a platba`
- **Структура:**
  1. H2 `Doručení po celé ČR` — kurýr/messenger, сроки, как работает, 18+ верификация курьером при выдаче (двойная верификация по закону).
  2. H2 `Osobní odběr v Praze` — 2 точки, оплата на месте, 18+ на месте → `/prodejny`.
  3. H2 `Způsoby platby` — Dobírka / QR platba / Bankovní převod / Online platba / Platba při osobním odběru. Подзаголовки H3 по каждому (по 2–3 предложения, чтобы взять long-tail «kratom dobírka», «kratom platba QR»).
  4. H2 `Balení a doručení PML` — соответствие требованиям (нейтрально).
  5. FAQ (5).
- **Объём:** 500–700 слов. Вхождения: «doručení po celé ČR», «dobírka», «QR platba», «bankovní převod», «online platba», «osobní odběr Praha», «ověření věku 18 let».
- **FAQ:** `Doručujete po celé ČR?`, `Mohu platit dobírkou?`, `Jak funguje platba QR kódem?`, `Ověřuje kurýr věk?` (да — закон), `Mohu zaplatit při osobním odběru?`.
- **Schema:** WebPage + FAQPage; (опц. `Organization.makesOffer`/shipping в Offer на товарах).

---

# 4.6. `/o-nas` — БРЕНД / E-E-A-T — вес 6

**Цель:** «Experience/Expertise/Trust» сигнал; история слияния 2 магазинов; named people (для E-E-A-T рекомендуется реальный ответственный человек/odborný garant).

- **Title:** `O nás — licencovaný prodejce kratomu v Praze | Vivadzen`
- **Meta:** `Vivadzen vznikl spojením dvou pražských kamenných prodejen kratomu. Licence MZ ČR, laboratorně testované šarže, odpovědný a transparentní přístup. 18+.`
- **H1:** `O Vivadzen`
- **Структура:** история (2 магазина → 1 e-shop), миссия (ответственный/прозрачный продажа PML), команда/odborný garant (имя, роль, опыт — реальный человек = E-E-A-T), ценности (licence, testy, 18+), ссылки → `/licence`, `/laboratorni-testy`, `/prodejny`, `/kontakt`. 600–900 слов.
- **Schema:** `AboutPage` + `Organization` (founder/employee, foundingDate, location), при наличии — `Person` для garanta.

---

# 4.7. `/kontakt` — вес 6

- **Title:** `Kontakt — Vivadzen kratom Praha`
- **Meta:** `Kontakt na licencovaný kratom e-shop Vivadzen: e-mail, telefon, adresy prodejen v Praze, IČO. Doručení po ČR, osobní odběr. 18+.`
- **H1:** `Kontakt`
- **Контент:** все каналы, IČO/DIČ, юр. данные, адреса 2 точек, форма (GDPR-consent), часы. Ссылки → `/prodejny`, `/reklamace`.
- **Schema:** `Organization` + `ContactPoint` + `PostalAddress` (×2).

---

# 4.8. `/overeni-veku` — 18+ (страница-объяснение, НЕ сам gate) — вес 4

- **Title:** `Ověření věku 18+ — proč a jak | Vivadzen`
- **Meta:** `Kratom je psychomodulační látka dostupná pouze osobám starším 18 let. Vysvětlujeme dvojí ověření věku: při objednávce i při převzetí. Licence MZ ČR.`
- **H1:** `Ověření věku 18+`
- **Контент:** почему 18+ (закон), как работает двойная верификация (при заказе + при выдаче курьером/на точке), что нужно (доклад тотожности), отказ при непредъявлении. Ссылки → `/licence`, `/pruvodce/kratom-zakon-2026`, `/doprava-a-platba`. 400–600 слов.
- **Schema:** WebPage + FAQPage (3 FAQ).
- ⚠️ Важно: **сам age-gate (модалка) не должен блокировать Googlebot** — реализация в файле 6 (cookie-gate, контент в DOM, не cloaking).

---

# 4.9. `/recenze` — АГРЕГАТОР ОТЗЫВОВ — вес 5

- **Title:** `Recenze a zkušenosti zákazníků | Vivadzen kratom`
- **Meta:** `Ověřené recenze zákazníků e-shopu Vivadzen — hodnocení produktů, doručení a osobního odběru. Licencovaný prodej kratomu, laboratorní testy, 18+.`
- **H1:** `Recenze zákazníků`
- **Контент:** агрегированные отзывы (модерация), фильтр по товару, интеграция Heuréka/Zboží/Google (CZ-стандарт доверия). Без побуждения хвалить эффект — отзывы о сервисе/доставке/упаковке; пользовательские эффект-упоминания модерировать осторожно (UGC ≠ маркетинг продавца, но лучше префильтр). Ссылка → `/kratom`.
- **Schema:** `Review`/`AggregateRating` только при реальных отзывах (никаких фейковых — Google штрафует и это репутационный риск).

---

# 4.10. `/caste-dotazy` — ГЛОБАЛЬНЫЙ FAQ-ХАБ — вес 5 (входящих ≥ 20)

**Цель:** собрать «вопросные» long-tail + featured snippets + внутр. перелинковка.

- **Title:** `Časté dotazy o kratomu, licenci a doručení | Vivadzen`
- **Meta:** `Odpovědi na časté dotazy: legálnost kratomu v ČR, licence MZ ČR, ověření věku 18+, laboratorní testy, doručení a platba. Licencovaný e-shop Vivadzen.`
- **H1:** `Časté dotazy`
- **Структура:** разделы H2: `Legálnost a zákon`, `Licence a kvalita`, `Věk 18+`, `Produkty a šarže`, `Doprava a platba`, `Prodejny Praha`. Каждый вопрос — H3, ответ 40–70 слов, **каждый ответ линкует на профильную страницу** (zákon→/pruvodce/kratom-zakon-2026, licence→/licence, testy→/laboratorni-testy и т.д.). 800–1200 слов.
- **Schema:** `FAQPage` (агрегированный) + BreadcrumbList.
- **Анти-каннибализация:** этот хаб не дублирует профильные страницы целиком — короткие ответы + ссылка «více». Глубокие FAQ остаются на профильных страницах.

---

# 4.11. ЛЕГАЛ-СТРАНИЦЫ — вес 2 (footer)

`/obchodni-podminky`, `/ochrana-soukromi` (GDPR), `/reklamace`, `/cookies`:
- Title по шаблону `{Název} | Vivadzen`, meta короткий, H1 = название.
- `/reklamace` стоит проработать чуть глубже (long-tail «kratom reklamace/vrácení», 400+ слов, FAQ 3, ссылка → /kontakt, /doprava-a-platba) — вес 6 фактически по полезности.
- Schema: WebPage. Индексируются (`index,follow`), но из sitemap можно исключить (низкий приоритет), оставив в footer.

→ Переходим к файлу 5 (контент-хаб: pillar, мини-вики, гайды, блог).
