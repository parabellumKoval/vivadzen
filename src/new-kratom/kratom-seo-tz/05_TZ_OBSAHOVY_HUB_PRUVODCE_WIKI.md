# VIVADZEN — SEO ТЗ
## Файл 5/7 — ТЗ контент-хаба (pillar, мини-вики, гайды, блог, FAQ)

> Это слой, который вызывает доверие Google и пользователей и тянет верх воронки. Для YMYL-тематики экспертный, ответственный, юридически чистый контент с дисклеймерами ранжируется выше «промис-портянок» конкурентов.
>
> **Сквозное правило комплаенса для всего хаба:** контент **информационно-образовательный**, не маркетинговый. Допустимо нейтрально излагать «co je popsáno v odborné literatuře» с обязательным дисклеймером и без призывов к употреблению/дозировок-рекомендаций. Раздел рисков обязателен и пишется ответственно (это не «формальность», а реальный E-E-A-T- и этический фактор).

---

# 5.0. `/pruvodce` — ХАБ-СТРАНИЦА ПУТЕВОДИТЕЛЯ — L1, вес 9

**Цель:** топический pillar-хаб, раздающий вес во все гайды; ранжируется по «kratom průvodce / informace o kratomu».

- **Title (≤60):** `Kratom — průvodce, fakta a legislativa | Vivadzen`
- **Meta (≤155):** `Ověřený průvodce kratomem: co to je, druhy a barvy, zákon 2026 a psychomodulační látky, rizika a bezpečnost, kvalita a laboratorní testy. Informačně, s licencí MZ ČR.`
- **H1:** `Průvodce kratomem`
- **Структура:** интро 100 слов (что здесь, дисклеймер, 18+) → сетка карточек ко всем гайдам и энц-страницам с краткими описаниями и ссылками → блок «Nakupujte u licencovaného prodejce» (1 CTA → /kratom, ссылки → /licence, /laboratorni-testy).
- **Объём:** 500–700 слов. Исходящих ссылок ≥ 12 (на все дочерние гайды + коммерч. мост).
- **Schema:** `CollectionPage` + `BreadcrumbList`.

---

# 5.1. `/pruvodce/co-je-kratom` — ФЛАГМАНСКИЙ PILLAR — L2, вес 9

**Цель:** главный информационный магнит; ранжироваться по «co je kratom / kratom» (инфо-интент); собирать ссылки; быть «темой-зонтиком», линкующей во все под-темы и в коммерцию.

- **Title (≤60):** `Co je kratom? Mitragyna speciosa, fakta a zákon | Vivadzen`
- **Meta (≤155):** `Co je kratom (Mitragyna speciosa): původ, druhy, barvy, alkaloidy, právní status v ČR 2026 a bezpečnost. Ověřený informační průvodce od licencovaného prodejce. 18+.`
- **H1:** `Co je kratom`
- **Структура (pillar — длинный, разбит на якорные секции с TOC):**
  1. `Co je kratom` — определение, Mitragyna speciosa, čeleď, kde roste (фактология).
  2. `Původ a tradice` — происхождение/история (краткий блок) → ссылка `/encyklopedie/historie-kratomu`.
  3. `Alkaloidy v kratomu` — mitragynin, 7-hydroxymitragynin (химия, нейтрально) → ссылка `/encyklopedie/mitragynin`.
  4. `Druhy a odrůdy` — обзор → ссылка `/pruvodce/druhy-kratomu`.
  5. `Barvy kratomu` — обзор отличий (фактология сушки/жилки, **без эффект-промиса**) → ссылка `/pruvodce/barvy-kratomu`.
  6. `Formy kratomu` — prášek/extrakt/nano → ссылки `/kratom-prasek`, `/kratom-extrakt`.
  7. `Kratom a zákon v ČR (2026)` — PML, 18+, лицензия (резюме) → ссылка `/pruvodce/kratom-zakon-2026`, `/licence`.
  8. `Rizika a bezpečnost` — обязательный честный блок → ссылка `/pruvodce/kratom-rizika-a-bezpecnost`.
  9. `Jak poznat kvalitní kratom` → ссылка `/pruvodce/jak-poznat-kvalitni-kratom`, `/laboratorni-testy`.
  10. `Kde koupit legálně` — 1 коммерческий CTA → `/kratom` (+ `/licence`).
  11. `Časté dotazy` — 6–8 FAQ + schema.
- **Объём:** 2000–2800 слов (это pillar). Каждая секция 150–300 слов, ведёт в углублённую страницу (hub-and-spoke).
- **Вхождения (естественно, без переспама):** «kratom», «Mitragyna speciosa», «mitragynin», «psychomodulační látka», «druhy/barvy kratomu», «zákon 2026», «18 let», «laboratorní test». Плотность ключа ≤ 1.5%.
- **Тон:** энциклопедический, нейтральный, с дисклеймером в начале и конце. **Без** «pomáhá/dodává/zlepšuje». Эффекты — только как «v odborné literatuře jsou popisovány…» + сразу дисклеймер и переход к рискам.
- **Внутр. ссылки:** ≥ 12 исходящих (все спицы + коммерч. мост ≤ 2 CTA, ненавязчиво).
- **FAQ (cs):** `Co je kratom?`, `Je kratom v ČR legální?`, `Co jsou psychomodulační látky?`, `Jaký je rozdíl mezi barvami?`, `Od kolika let lze kratom koupit?`, `Je kratom návykový?` (честно → блок рисков), `Co je mitragynin?`, `Jak poznat kvalitní kratom?`.
- **Schema:** `Article`/`MedicalWebPage`→ лучше `Article` + `FAQPage` + `BreadcrumbList`. `author`=реальный odborný garant (Person, с описанием экспертизы) — критично для E-E-A-T. `dateModified` актуальная.
- **Link-bait потенциал:** этот pillar — главный кандидат на внешние ссылки (форумы/медиа цитируют «co je kratom + zákon»). Сделать максимально полным и нейтральным.

---

# 5.2. `/pruvodce/kratom-zakon-2026` — ЛЕГИСЛАТИВНЫЙ PILLAR — L2, вес 8

**Цель:** перехватить быстрорастущий кластер 2026 («kratom zákon 2026 / je kratom legální / psychomodulační látky / PML / regulace»); стать цитируемым источником (входящие ссылки) и trust-якорем.

- **Title (≤60):** `Kratom a zákon 2026 — psychomodulační látky v ČR | Vivadzen`
- **Meta (≤155):** `Kratom v ČR 2026: zařazení mezi psychomodulační látky (zák. č. 167/1998 Sb.), licence MZ ČR, hranice 18+, povinné testy. Přehledně a aktuálně od licencovaného prodejce.`
- **H1:** `Kratom a zákon v ČR 2026`
- **Структура:**
  1. `Stručně: je kratom legální?` — короткий прямой ответ (featured-snippet формат: «Ano, ale…»).
  2. `Časová osa regulace` — 3.9.2025 (zařazení), 12.11.2025 (platnost), 30.1.2026 (první povolení pro online) — datace, нейтрально, со ссылкой на первоисточники (MZ ČR / Sbírka).
  3. `Co jsou psychomodulační látky (PML)` — определение категории.
  4. `Pravidla pro prodejce` — лицензия, спец-eshop + каменная точка, запрет рекламы/health-claims, маркировка §33e.
  5. `Pravidla pro zákazníky` — 18+, двойная верификация, где легально купить.
  6. `Co to znamená pro vás u Vivadzen` — наша лицензия/тесты → ссылки `/licence`, `/laboratorni-testy`, `/overeni-veku`.
  7. `Časté omyly` — мифы о «zákazu» (нейтрально).
  8. FAQ (6).
  9. **Расширенный правовой дисклеймер** (не юр. совет, законы меняются, ссылка на актуальные источники).
- **Объём:** 1800–2400 слов. Ссылки ≥ 8 (внутр.) + цитирование официальных источников (внешние авторитетные — повышают E-E-A-T; rel без nofollow на gov-источники).
- **Обновление:** при любом изменении регуляции — немедленно (`dateModified`). Это «живой» документ.
- **FAQ:** `Je kratom v roce 2026 legální v ČR?`, `Co je psychomodulační látka?`, `Od kolika let lze kratom koupit?`, `Musí mít e-shop licenci?`, `Smí se kratom inzerovat s účinky?` (нет — объяснить, и это объясняет, почему мы пишем фактологично — оборачиваем комплаенс в доверие), `Hrozí zákaz kratomu?`.
- **Schema:** `Article` + `FAQPage` + `BreadcrumbList`, `author`=garant, `citation` на офиц. источники.

> Стратегически: эта страница превращает наш комплаенс-«ограничитель» в маркетинговое преимущество — мы прямо объясняем пользователю, почему наши тексты честные, а у конкурентов «маркетинговые сказки». Это и есть обгон смыслом.

---

# 5.3. `/pruvodce/barvy-kratomu` — L3, вес 6

**Цель:** «kratom barvy / rozdíl mezi barvami / jaký kratom je nejlepší/nejsilnější» — высокий инфо-объём, который конкуренты берут промисом. Берём нейтрально.

- **Title:** `Barvy kratomu — čím se zelený, bílý a červený liší | Vivadzen`
- **Meta:** `Zelený, bílý, červený a žlutý kratom: čím se barvy liší podle sušení a zpracování listů Mitragyna speciosa. Informační průvodce, bez marketingových tvrzení. 18+.`
- **H1:** `Barvy kratomu — v čem je rozdíl`
- **Структура:** интро + дисклеймер → H2 по каждому цвету (фактология обработки/жилки/региона, нейтрально; то, что в литературе — обобщённо + дисклеймер) → H2 `Je některá barva „nejsilnější“?` (честный нейтральный ответ: зависит от šarže/обработки, поэтому смотрите COA → ссылка /laboratorni-testy) → ссылки на 4 цветные категории → FAQ (4).
- **Объём:** 1200–1800 слов. Ссылки ≥ 8.
- **Schema:** Article + FAQPage + BreadcrumbList.
- **Заметка:** запрос «nejsilnější kratom» — переформулируем ответом «сила = обоснованный лаб. показатель mitragyninu конкретной šarže, а не цвет» → уводим в /laboratorni-testy. Это легально и одновременно усиливает наш COA-USP.

---

# 5.4. `/pruvodce/druhy-kratomu` — L3, вес 6

- **Title:** `Druhy a odrůdy kratomu — Maeng Da, Bali, Borneo… | Vivadzen`
- **Meta:** `Přehled odrůd kratomu (Maeng Da, Bali, Borneo, Sumatra, Thai, Elephant): původ, oblasti pěstování a zpracování. Informační průvodce od licencovaného prodejce. 18+.`
- **H1:** `Druhy a odrůdy kratomu`
- **Структура:** интро → H2 по штаммам (происхождение/регион/обработка, фактология) с ссылками на соответствующие strain-хабы и энц. `mitragyna-speciosa` → FAQ (4).
- **Объём:** 1200–1700 слов. Ссылки ≥ 8 (на все strain-хабы + энц.).
- **Schema:** Article + FAQPage + BreadcrumbList.

---

# 5.5. `/pruvodce/jak-poznat-kvalitni-kratom` — L3, вес 6 (траст/линк-магнит)

**Цель:** «kvalitní kratom / jak poznat / ověřený prodejce / čistota» + конверсия (доверие → /kratom). Один из главных link-bait.

- **Title:** `Jak poznat kvalitní kratom — licence, COA, čistota | Vivadzen`
- **Meta:** `Jak poznat kvalitní a bezpečný kratom: licence prodejce, laboratorní testy (COA), původ, balení a označení dle zákona. Praktický kontrolní seznam. 18+.`
- **H1:** `Jak poznat kvalitní kratom`
- **Структура:** интро → чек-лист (H2): `Licence prodejce` → /licence; `Laboratorní protokol (COA)` → /laboratorni-testy; `Transparentní původ`; `Označení dle zákona §33e`; `Kamenná prodejna`; `Na co si dát pozor` (red flags — нейтрально, образоват.) → FAQ (4) → 1 CTA `/kratom`.
- **Объём:** 1200–1600 слов. Ссылки ≥ 8.
- **Schema:** Article (`HowTo` можно, если формат шагов) + FAQPage + BreadcrumbList.
- **Link-bait:** «kontrolní seznam» — формат, который охотно ссылают форумы/медиа. Сделать скачиваемый чек-лист (PDF) как лид-магнит.

---

# 5.6. `/pruvodce/kratom-rizika-a-bezpecnost` — L3, вес 6 (E-E-A-T critical)

**Цель:** ответственный, честный блок про риски/зависимость/взаимодействия. Это **критично** для E-E-A-T в YMYL и этически правильно: не преуменьшать риски, не давать «как обойти». Google для веществ ранжирует сайты, демонстрирующие заботу о пользователе.

- **Title:** `Kratom — rizika a bezpečnost | Vivadzen`
- **Meta:** `Objektivní přehled rizik kratomu: možná návykovost, nežádoucí účinky, kombinace s alkoholem a léky, řízení. Informace pro odpovědné rozhodování. 18+.`
- **H1:** `Kratom — rizika a bezpečnost`
- **Структура:** интро + дисклеймер → H2 `Možná návykovost a abstinenční příznaky` (честно, не преуменьшая) → `Nežádoucí účinky popsané v literatuře` → `Kombinace s alkoholem, léky a jinými látkami` (предупреждающе) → `Řízení a stroje` → `Komu se vyhnout` (těhotenství/kojení, mladiství — 18+, zdravotní stav → konzultace s lékařem) → `Kdy vyhledat pomoc` (нейтральная отсылка к проф. помощи без сенсационизма) → FAQ (4).
- **Объём:** 1200–1800 слов. Тон — спокойный, фактологический, заботливый; **никаких** инструкций по «усилению эффекта» или «обходу» чего-либо.
- **Внутр. ссылки:** → `/pruvodce/kratom-zakon-2026`, `/encyklopedie/mitragynin`, `/pruvodce/co-je-kratom`. Без коммерческого CTA (доверие важнее продажи здесь).
- **Schema:** Article + FAQPage + BreadcrumbList; `author`=garant.
- **Заметка:** именно наличие сильной, честной risk-страницы (которой нет у промис-конкурентов) — один из главных сигналов, почему Google поднимет домен в YMYL. Это и SEO, и правильно по сути.

---

# 5.7. `/pruvodce/kratom-fakta-a-myty` — L4, вес 5 (перехват «kratom účinky» — нейтрально)

**Цель:** ранжироваться по высокочастотному «kratom účinky» и «kratom dávkování», но контентом, который развенчивает мифы и подаёт факты нейтрально + дисклеймер. Легальный способ забрать трафик, который конкуренты берут нарушением.

- **Title:** `Kratom — fakta a mýty | Vivadzen`
- **Meta:** `Fakta a mýty o kratomu: co o účincích uvádí odborná literatura, časté omyly, proč nedáváme dávkovací doporučení a co říká zákon. Informačně, 18+.`
- **H1:** `Kratom — fakta a mýty`
- **Структура:** интро + жёсткий дисклеймер → H2 `Co o kratomu uvádí odborná literatura` (нейтральное изложение, без обещаний, формулировки «studie/literatura uvádějí…», «účinky se liší dle jedince a šarže», сразу ссылка на риски) → H2 `Časté mýty` (Q&A формат: «Mýtus: …» / «Fakt: …») → H2 `Proč u nás nenajdete dávkovací návody` (объяснение комплаенса = доверие, ссылка /pruvodce/kratom-zakon-2026) → H2 `Dávkování — co říká literatura vs. co říká zákon` (только мета-уровень: закон не позволяет рекомендации, литература описывает диапазоны — без конкретных «примите X g» как руководства) → FAQ (5).
- **Объём:** 1400–1900 слов. Ссылки → /pruvodce/kratom-rizika-a-bezpecnost, /pruvodce/co-je-kratom, /pruvodce/kratom-zakon-2026.
- **Schema:** Article + FAQPage + BreadcrumbList; `author`=garant.

---

# 5.8. `/pruvodce/kratom-priprava-caj` — L4, вес 4

- **Title:** `Kratom čaj — tradiční příprava a fakta | Vivadzen`
- **Meta:** `Jak se tradičně připravuje kratomový čaj z listů Mitragyna speciosa: postup, voda, čas. Informační průvodce bez tvrzení o účincích. 18+.`
- **H1:** `Kratom čaj — tradiční příprava`
- **Контент:** фактологический процесс приготовления как кулинарно-этнографическая справка (нейтрально, без «для эффекта X»), вкус/аромат, дисклеймер. 800–1100 слов. Ссылки → /kratom-prasek, /pruvodce/co-je-kratom. FAQ (3).
- **Schema:** Article (можно HowTo для шагов приготовления, без health-claims) + BreadcrumbList.

---

# 5.9. `/pruvodce/skladovani-kratomu` — L5, вес 4

- **Title:** `Skladování kratomu — trvanlivost a podmínky | Vivadzen`
- **Meta:** `Jak skladovat kratom prášek a extrakt: vlhkost, světlo, teplota a trvanlivost. Praktický informační průvodce od licencovaného prodejce. 18+.`
- **H1:** `Skladování a trvanlivost kratomu`
- **Контент:** условия хранения (свет/влага/темп.), упаковка, срок — практическая фактология. 600–900 слов. Ссылки → /kratom-prasek, /laboratorni-testy. FAQ (3).
- **Schema:** Article + FAQPage + BreadcrumbList.

---

# 5.10. МИНИ-ВИКИ `/encyklopedie/*` — L4–L5, вес 5

**Цель:** entity-SEO (Mitragyna speciosa, mitragynin, history) — это «знаниевые сущности», которые усиливают тематический авторитет домена и линкуются из всех гайдов/категорий.

### `/encyklopedie/mitragyna-speciosa`
- **Title:** `Mitragyna speciosa — strom, ze kterého je kratom | Vivadzen`
- **Meta:** `Mitragyna speciosa: botanika, čeleď, vzhled listů a žilek, oblasti růstu v jihovýchodní Asii. Encyklopedické informace od licencovaného prodejce kratomu.`
- **H1:** `Mitragyna speciosa`
- Контент: ботаника, ареал, лист/жилка, связь с цветовой классификацией. 900–1400 слов. Ссылки → /pruvodce/co-je-kratom, /pruvodce/barvy-kratomu, strain-хабы. Schema: `Article` (можно `DefinedTerm`/`Thing`) + BreadcrumbList.

### `/encyklopedie/mitragynin`
- **Title:** `Mitragynin a 7-hydroxymitragynin — alkaloidy kratomu | Vivadzen`
- **Meta:** `Co je mitragynin a 7-hydroxymitragynin: hlavní alkaloidy v listech Mitragyna speciosa, jejich obsah a proč se měří v laboratorních testech. Informačně, 18+.`
- **H1:** `Mitragynin a 7-hydroxymitragynin`
- Контент: химия/нахождение/почему измеряют в COA (связь с /laboratorni-testy — фактология, без эффект-промиса). 900–1300 слов. Schema: Article + BreadcrumbList.

### `/encyklopedie/historie-kratomu`
- **Title:** `Historie a původ kratomu | Vivadzen`
- **Meta:** `Historie kratomu: původ v jihovýchodní Asii, tradiční využití a cesta do Evropy. Encyklopedický přehled od licencovaného prodejce. 18+.`
- **H1:** `Historie a původ kratomu`
- Контент: историко-этнографическая справка, нейтрально. 700–1100 слов. Schema: Article + BreadcrumbList.

> Энц-страницы — `Article`/`DefinedTerm`, `author`=garant, плотная взаимная перелинковка (entity-граф). Они почти не требуют обновления (вес стабильный) и сильно укрепляют тематический авторитет.

---

# 5.11. `/blog` + `/blog/{slug}` — L3/L5, вес 4

**Цель:** свежесть, новости регуляции, šarže-апдейты, long-tail; питает pillar-страницы (спицы → pillar).

- **`/blog` (листинг):** Title `Blog o kratomu — novinky, legislativa, kvalita | Vivadzen`; листинг с пагинацией (canonical-логика — файл 6); ссылки на pillar.
- **Шаблон поста:** Title `{Tema} | Vivadzen`, H1=тема, 800–1500 слов, 2–4 внутр. ссылки на профильные pillar/категории, FAQ-блок где уместно, `Article` + `BreadcrumbList`, `author`=garant, `datePublished/Modified`.
- **Контент-план (стартовые 12 тем, приоритет → legislativa и trust):**
  1. `Kratom a zákon 2026: co se od listopadu změnilo` (обновлять) → линк в /pruvodce/kratom-zakon-2026
  2. `Jak poznáte licencovaný kratom e-shop` → /licence
  3. `Co je COA a jak ho číst` → /laboratorni-testy
  4. `Psychomodulační látky vysvětleno jednoduše`
  5. `Zelený vs bílý vs červený — čím se liší (fakta)` → /pruvodce/barvy-kratomu
  6. `Mitragynin: proč se měří jeho obsah` → /encyklopedie/mitragynin
  7. `Kratom v Praze: kde nakoupit legálně` → /kratom-praha
  8. `Maeng Da: co to označení znamená` → /kratom/maeng-da
  9. `Ověření věku 18+ u kratomu: jak to funguje` → /overeni-veku
  10. `Kratom a bezpečnost: na co myslet` (ответственно) → /pruvodce/kratom-rizika-a-bezpecnost
  11. `Skladování kratomu: praktický návod` → /pruvodce/skladovani-kratomu
  12. `Mýty o kratomu, kterým lidé věří` → /pruvodce/kratom-fakta-a-myty
- Частота: 2–4 поста/мес.; при изменении закона — приоритетный апдейт legislativ-постов и pillar.

---

# 5.12. ОБЩИЕ ТРЕБОВАНИЯ КО ВСЕМ КОНТЕНТНЫМ СТРАНИЦАМ

- **E-E-A-T обязательно:** реальный `author` (odborný garant — имя, фото, краткое bio/экспертиза, ссылка на /o-nas), `dateModified`, ссылки на авторитетные первоисточники (MZ ČR, Sbírka zákonů) с корректными rel.
- **Дисклеймер:** короткий — в начале (1 строка), расширенный — в конце (образоват. характер, не юр./мед. совет, 18+, не пища/лекарство/БАД).
- **TOC** (оглавление с якорями) на pillar/длинных гайдах — для UX и снипетов.
- **Без effect-промиса** в title/H1/meta/первом экране любой страницы. Эффекты — только нейтрально в теле «literatura uvádí…» + дисклеймер + риски.
- **Featured snippet оптимизация:** короткий прямой ответ (40–55 слов) сразу под H1 на инфо-страницах (особенно «je kratom legální», «co je kratom», «co je COA»).
- **Внутренние ссылки:** каждый гайд линкует ≥1 раз в коммерцию (ненавязчиво) и ≥3 раз в смежные гайды/энц. (силос + мост).

→ Переходим к файлу 6 (техническое SEO и комплаенс-имплементация).
