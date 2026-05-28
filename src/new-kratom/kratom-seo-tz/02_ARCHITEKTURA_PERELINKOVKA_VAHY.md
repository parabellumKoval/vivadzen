# VIVADZEN — SEO ТЗ
## Файл 2/7 — Архитектура сайта, перелинковка, распределение весов

---

## 1. ПОЛНОЕ ДЕРЕВО URL (целевая структура)

```
/                                         L0  Домашняя (бренд + распределяющий хаб)
│
├── /kratom                               L1  Товарный pillar (бывший /catalog; настроить 301 /catalog → /kratom*)
│   ├── /kratom-prasek                     L2  Тип: порошок
│   ├── /kratom-extrakt                    L2  Тип: экстракт
│   ├── /kratom/zeleny-kratom              L2  Цвет: зелёный
│   ├── /kratom/bily-kratom                L2  Цвет: белый
│   ├── /kratom/cerveny-kratom             L2  Цвет: красный
│   ├── /kratom/zluty-kratom               L2  Цвет: жёлтый/золотой (placeholder-категория)
│   ├── /kratom/maeng-da                   L3  Strain-хаб
│   ├── /kratom/sumatra                    L3  Strain-хаб
│   ├── /kratom/thajsky                    L3  Strain-хаб
│   ├── /kratom/elephant                   L3  Strain-хаб
│   ├── /kratom/bali                       L3  Strain-хаб (placeholder)
│   ├── /kratom/borneo                     L3  Strain-хаб (placeholder)
│   └── /kratom/{slug-produktu}            L4  Товарные карточки (8 live) + L5 (15 placeholder)
│
├── /kratom-praha                          L2  Локальный коммерческий лендинг
├── /prodejny                              L3  Хаб магазинов
│   ├── /prodejny/praha-{lokalita-1}       L4  Точка №1 (LocalBusiness)
│   └── /prodejny/praha-{lokalita-2}       L4  Точка №2 (LocalBusiness)
│
├── /licence                               L3  Trust: лицензия MZ ČR
├── /laboratorni-testy                     L3  Trust: COA / лаб. протоколы
│   └── /laboratorni-testy/{sarze-id}      L5  Страница конкретной šarže (опц., фаза 2)
│
├── /pruvodce                              L1  Информационный pillar-хаб «Kratom — průvodce»
│   ├── /pruvodce/co-je-kratom             L2  Pillar-гайд
│   ├── /pruvodce/kratom-zakon-2026        L2  Легислативный pillar
│   ├── /pruvodce/barvy-kratomu            L3
│   ├── /pruvodce/druhy-kratomu            L3
│   ├── /pruvodce/jak-poznat-kvalitni-kratom L3
│   ├── /pruvodce/kratom-rizika-a-bezpecnost L3 (E-E-A-T critical)
│   ├── /pruvodce/kratom-fakta-a-myty      L4
│   ├── /pruvodce/kratom-priprava-caj      L4
│   └── /pruvodce/skladovani-kratomu       L5
│
├── /encyklopedie                          L3  Мини-вики хаб
│   ├── /encyklopedie/mitragyna-speciosa   L4
│   ├── /encyklopedie/mitragynin           L4
│   └── /encyklopedie/historie-kratomu     L5
│
├── /blog                                  L3  Блог-листинг
│   └── /blog/{slug}                       L5  Статьи (новости регуляции, šarže-апдейты)
│
├── /recenze                               L4  Агрегатор отзывов (Review schema)
├── /doprava-a-platba                      L2  Транзакц. (dobírka/QR/převod/messenger)
├── /o-nas                                 L3  Бренд/E-E-A-T (история 2 магазинов)
├── /kontakt                               L3  Контакты + Organization schema
├── /overeni-veku                          L4  18+ объяснение (не сам gate)
├── /reklamace                             L6  Возврат/рекламация
├── /obchodni-podminky                     L6  Оферта
├── /ochrana-soukromi                      L6  GDPR
└── /caste-dotazy                          L3  Глобальный FAQ-хаб (FAQPage schema)
```

> **Решение по URL цветных категорий:** оставляем вложенность `/kratom/zeleny-kratom` (вы уже на ней — сохраняем equity, ставим 301 со старых `/catalog/...`). Тип-страницы (`/kratom-prasek`, `/kratom-extrakt`) делаем верхнего уровня — это сильнее для головных ключей «kratom prášek».

---

## 2. РЕДИРЕКТЫ (обязательно при перестройке)

| Старый URL | Новый URL | Код |
|---|---|---|
| `/catalog` | `/kratom` | 301 |
| `/catalog/zeleny-kratom` | `/kratom/zeleny-kratom` | 301 |
| `/catalog/{любой-цвет}` | `/kratom/{цвет}` | 301 |
| любой `?p=`, дубли с параметрами | canonical на чистый URL | rel=canonical |

Карта 301 сдаётся одним списком разработчику, проверяется в GSC «Coverage» 4 недели.

---

## 3. МОДЕЛЬ ПЕРЕЛИНКОВКИ (PageRank Sculpting)

### 3.1. Принципы
1. **Хабовая модель**: L0/L1 раздают вес вниз; L4–L5 поднимают вес контекстными ссылками обратно в коммерческие L2–L3.
2. **Trust-инъекция**: страницы `/licence` и `/laboratorni-testy` получают ссылку **с каждой** коммерческой и категорийной страницы (футер-блок trust + контекстная ссылка в теле). Это критично для YMYL.
3. **Тематические силосы**: коммерческий силос (`/kratom*`) и информационный силос (`/pruvodce*`, `/encyklopedie*`) **связаны мостами** (pillar-гайд линкует в категории, категории линкуют в релевантный гайд) — но не размазаны хаотично.
4. **Анкоры — разнообразные, по смыслу, без переспама** точного вхождения. Шаблоны анкоров — ниже.

### 3.2. Карта ссылочных потоков (обязательные связи)

| С страницы | На страницу | Анкор (cs, пример) | Где |
|---|---|---|---|
| `/` | `/kratom` | «Prohlédnout kratom» / «Náš kratom» | hero CTA + меню |
| `/` | `/licence` | «Prodej s licencí MZ ČR» | trust-полоса |
| `/` | `/laboratorni-testy` | «Laboratorně testované šarže» | trust-полоса |
| `/` | `/kratom-praha` | «Kratom v Praze a osobní odběr» | блок «Prodejny» |
| `/` | `/pruvodce/co-je-kratom` | «Průvodce kratomem» | блок «Poradna» |
| `/kratom` | каждая категория (цвет/тип) | «Zelený kratom», «Kratom prášek»… | сетка категорий |
| `/kratom` | каждый strain-хаб | «Maeng Da», «Sumatra»… | блок «Podle odrůdy» |
| `/kratom` | `/licence`, `/laboratorni-testy` | trust-анкоры | trust-блок |
| категория цвета | релевантные товары | название товара | сетка товаров |
| категория цвета | соответствующий strain-хаб | «Maeng Da v zelené variantě» | контекст |
| категория цвета | `/pruvodce/barvy-kratomu` | «Čím se liší barvy kratomu» | инфо-блок |
| strain-хаб | товары этого штамма (все цвета) | название товара | сетка |
| strain-хаб | `/pruvodce/druhy-kratomu` | «Více o odrůdách kratomu» | контекст |
| товар | 3–4 связанных товара | название товара | «Související» |
| товар | своя цветная категория + strain-хаб | breadcrumbs + контекст | хлеб. крошки |
| товар | `/laboratorni-testy` (+ конкретная šarže) | «Laboratorní protokol této šarže» | блок качества |
| товар | `/doprava-a-platba` | «Doprava, dobírka a osobní odběr» | блок доставки |
| pillar `/pruvodce/co-je-kratom` | все L3 гайды + категории | контекстные | тело |
| любой гайд | `/kratom` или релевантная категория | «Kde koupit kvalitní kratom» (1 CTA) | конец статьи |
| `/pruvodce/kratom-zakon-2026` | `/licence`, `/overeni-veku` | «Naše licence», «Ověření věku 18+» | тело |
| `/laboratorni-testy` | `/kratom` | «Produkty s ověřenými testy» | CTA |
| footer (глобально) | `/licence`,`/laboratorni-testy`,`/overeni-veku`,`/obchodni-podminky`,`/ochrana-soukromi`,`/kontakt`,`/caste-dotazy` | служебные анкоры | footer |

### 3.3. Правила анкоров
- Точное вхождение ключа — **макс. 1 ссылка** на целевую страницу с одного документа.
- Остальные ссылки — разбавленные/брендовые/«читай-далее».
- Глубина клика от `/` до любого товара — **≤ 3 клика** (`/` → категория → товар).
- Каждая L4/L5 страница имеет **≥ 2 входящие** внутренние ссылки из контента (не только меню/футер).
- Никаких циклических farm-блоков «100 ссылок в подвале».

---

## 4. РАСПРЕДЕЛЕНИЕ ВЕСОВ (детально)

«Вес» = расчётный приоритет = (позиция в иерархии × объём контента × кол-во входящих внутр. ссылок × коммерческая ценность). Бюджет линковки распределяем так:

| Страница | Вес (1–10) | Входящих внутр. ссылок (цель) | Контент-объём (слов) | Обновление |
|---|---|---|---|---|
| `/` | 10 | — (вершина) | 600–900 | квартал |
| `/kratom` | 9 | ≥ 30 | 900–1300 | месяц (наличие) |
| `/pruvodce/co-je-kratom` (pillar) | 9 | ≥ 25 | 2000–2800 | квартал |
| `/kratom/zeleny-kratom` | 8 | ≥ 15 | 800–1100 | месяц |
| `/kratom/bily-kratom` | 8 | ≥ 15 | 800–1100 | месяц |
| `/kratom/cerveny-kratom` | 8 | ≥ 15 | 800–1100 | месяц |
| `/kratom-prasek` | 8 | ≥ 15 | 800–1100 | месяц |
| `/pruvodce/kratom-zakon-2026` | 8 | ≥ 20 | 1800–2400 | при изменении закона |
| `/kratom-extrakt` | 7 | ≥ 10 | 600–900 | месяц |
| `/kratom/zluty-kratom` | 7 | ≥ 10 | 600–800 | при наличии |
| `/kratom/maeng-da` | 7 | ≥ 12 | 700–1000 | месяц |
| `/kratom-praha` | 8 | ≥ 12 | 700–1000 | квартал |
| `/licence` | 7 | ≥ 40 (со всех ком.) | 700–1000 | при изменении |
| `/laboratorni-testy` | 7 | ≥ 40 (со всех ком.) | 700–1100 | при новой šarže |
| `/kratom/sumatra`,`/thajsky`,`/elephant` | 6 | ≥ 8 | 600–800 | квартал |
| `/kratom/bali`,`/borneo` (placeholder) | 5 | ≥ 6 | 500–700 | при лицензии |
| товары live (8) | 6 | ≥ 4 | 450–700 | при изменении šarže |
| `/pruvodce/*` L3 (4 шт.) | 6 | ≥ 8 | 1200–1800 | полгода |
| `/encyklopedie/*` | 5 | ≥ 5 | 900–1400 | год |
| `/prodejny` + 2 точки | 6 | ≥ 6 | 500–700 | при изменении |
| `/recenze` | 5 | ≥ 6 | динам. + 300 | автоматич. |
| `/blog/*` | 4 | ≥ 3 | 800–1500 | по графику |
| placeholder-товары (15) | 3 | ≥ 2 | 350–500 | при лицензии |
| `/doprava-a-platba` | 4 | ≥ 30 (footer+товары) | 500–700 | при изменении |
| `/caste-dotazy` | 5 | ≥ 20 | 800–1200 | месяц |
| легал-страницы | 2 | footer | по необходимости | при изменении |

> Веса = ориентир для копирайтера/линк-билдера: на странице веса 9 — больше уникального контента, больше входящих контекстных ссылок, чаще обновление; на странице веса 3 — минимум, чтобы индексироваться и не быть thin.

---

## 5. БОРЬБА С КАННИБАЛИЗАЦИЕЙ И THIN-CONTENT

- **Цвет vs strain vs товар:** у каждого свой главный ключ (см. файл 1, §11). Цветная категория НЕ описывает Maeng Da подробно — она линкует на strain-хаб. Strain-хаб НЕ дублирует товарные тексты — он агрегирует и даёт уникальный обзор штамма.
- **Placeholder-страницы:** обязаны иметь уникальный энциклопедический контент 350–500 слов (происхождение штамма/цвета, обработка, COA-заготовка) — иначе `noindex` до наполнения. Тонкая страница без контента = `noindex,follow` до готовности.
- **Фасовка 25 g / 50 g:** это **варианты одного товара** (один URL, селектор веса, один canonical). НЕ плодим отдельные URL под 25/50 г — это дубли. Schema `Offer` × 2 на одной карточке.
- **Параметрические URL** (фильтры, сортировки): `rel=canonical` на чистый URL + правило в файле 6.

→ Переходим к файлу 3 (ТЗ коммерческих страниц).
