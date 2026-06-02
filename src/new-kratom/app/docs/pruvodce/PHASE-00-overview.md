# PHASE 00 — SEO-стратегия и архитектура раздела

> Эту фазу **не нужно реализовывать кодом**. Это спецификация: что мы строим
> и зачем. Все остальные фазы ссылаются сюда.

---

## 1. Цель раздела `/pruvodce`

Собирать органический трафик из Google.cz/Seznam по **информационным**
запросам про кратом в чешской языковой нише, **не конкурируя** с
коммерческими страницами `/kratom/*` и не нарушая чешское законодательство
о PML (psychomodulační látky, zák. 167/1998 Sb. и его новая редакция 2026).

Раздел подаётся как **сухая wiki-энциклопедия** — стиль Wikipedia /
ScienceDirect, без обложек на карточках каталога, без маркетинговой лексики,
без призывов к употреблению, без дозировок.

---

## 2. Информационная архитектура

### 2.1 Категории (4)

| slug | Заголовок (cs) | Тематика | Кол-во статей |
|------|----------------|----------|---------------|
| `botanika-a-veda` | Botanika a věda | Mitragyna speciosa как растение, химия, фармакология | 10 |
| `historie-a-kultura` | Historie a kultura | Историческое и этноботаническое использование в ЮВА | 5 |
| `legislativa-cr` | Legislativa ČR | Закон 2026, PML, надзор, лицензирование | 8 |
| `kvalita-a-bezpecnost` | Kvalita a bezpečnost | COA, контаминанты, методы анализа, стандарты | 7 |

### 2.2 URL-схема

```
/pruvodce                                   — landing (hero + 4 категории + 6 «doporučené» статей)
/pruvodce/{kategorie}                       — каталог категории (карточки без обложек, по позиции)
/pruvodce/{kategorie}/{slug}                — статья
```

**Локализация:** дефолт `cs` без префикса, остальные через `{locale}/pruvodce/...`,
но контент в БД сейчас только на чешском. (Мультиязычие — задача отдельной
итерации; на этом этапе только `cs`.)

**Каноникал:** на каждой статье — `<link rel="canonical">` на свой
абсолютный URL. На `/pruvodce` без UTM. На категории — без `?page`.

### 2.3 Перелинковка (внутренняя)

Три уровня связей:

1. **Хлебные крошки:** `Domů → Průvodce → {Kategorie} → {Článek}`.
2. **«Související články»** в конце статьи — ручной список 3–6 связей,
   хранится в `wiki_article_related` (admin курирует).
3. **«Klíčové pojmy»** (glosář-ссылки) — автоматически: если в теле статьи
   встречается заголовок другой статьи из категории `Botanika a věda` →
   вставляется ссылка через job/observer (фаза 04).

**Запрет:** ссылки из тела статьи на `/kratom/*` (коммерческие). Допустима
одна ненавязчивая плашка «Hledáte konkrétní výrobek? Najdete jej v
katalogu →» в самом низу, после FAQ.

---

## 3. SEO-карта 30 статей

Каждая статья = 1 primary keyword + 2–4 secondary. В админке primary key
показывается в списке статей колонкой «🎯 Klíčové slovo», secondary —
бейджами на форме редактирования (см. PHASE-03).

### 3.1 Botanika a věda (10) — `kategorie_id=1`

| # | slug | Primary keyword (cs) | Заголовок (H1) |
|---|------|---------------------|----------------|
| 1 | `co-je-kratom` | co je kratom | Co je kratom — botanická definice a původ |
| 2 | `mitragyna-speciosa` | mitragyna speciosa | Mitragyna speciosa: botanický popis rostliny |
| 3 | `mitragynin` | mitragynin | Mitragynin: hlavní alkaloid kratomu |
| 4 | `7-hydroxymitragynin` | 7-hydroxymitragynin | 7-hydroxymitragynin: stopový alkaloid kratomu |
| 5 | `alkaloidy-kratomu` | alkaloidy kratomu | Alkaloidní profil kratomu: přes 40 sloučenin |
| 6 | `kratom-rostlina-zivotni-cyklus` | kratom rostlina | Kratom jako rostlina: životní cyklus a morfologie |
| 7 | `kde-roste-kratom` | kde roste kratom | Přirozený areál Mitragyna speciosa |
| 8 | `barvy-zil-kratomu` | barvy kratomu rozdíly | Barvy žil kratomu: botanický a chemický základ |
| 9 | `fermentace-kratomu` | kratom fermentace | Fermentace kratomového listu: proces a chemie |
| 10 | `mesh-velikost-kratomu` | kratom mesh velikost | Granulometrie kratomového prášku: mesh a mikrometry |

### 3.2 Historie a kultura (5) — `kategorie_id=2`

| # | slug | Primary keyword | Заголовок |
|---|------|----------------|-----------|
| 11 | `historie-kratomu` | historie kratomu | Historie kratomu: od jihovýchodní Asie po Evropu |
| 12 | `kratom-thajsko-historie` | kratom thajsko | Kratom v Thajsku: tradice, zákazy a návrat |
| 13 | `kratom-indonesie` | kratom indonésie | Indonésie jako světový dodavatel kratomu |
| 14 | `kratom-malajsie-ketum` | kratom malajsie | Kratom v Malajsii: ketum a místní kontext |
| 15 | `etnobotanika-kratom` | etnobotanika kratom | Etnobotanika kratomu v jihovýchodní Asii |

### 3.3 Legislativa ČR (8) — `kategorie_id=3`

| # | slug | Primary keyword | Заголовок |
|---|------|----------------|-----------|
| 16 | `kratom-zakon-cesko-2026` | kratom zákon česko 2026 | Nová regulace kratomu v ČR od 2026: kompletní přehled |
| 17 | `psychomodulacni-latky` | psychomodulační látky | Psychomodulační látky (PML): kategorie podle 167/1998 Sb. |
| 18 | `pravni-status-kratomu-cr` | kratom legální česko | Právní status kratomu v ČR: jak je regulován |
| 19 | `kratom-status-eu` | kratom status eu | Status kratomu v zemích EU: srovnání |
| 20 | `vekova-hranice-kratom` | kratom 18 let | Věková hranice pro kratom v ČR: minimum 18 let |
| 21 | `licencovani-pml-cr` | kratom licence česko | Licencování distribuce PML v ČR |
| 22 | `dovoz-kratomu-cr` | kratom dovoz česko | Dovoz kratomu do ČR: celní a regulační aspekty |
| 23 | `sukl-mz-cr-dohled` | sukl kratom | Role SÚKL, MZ ČR a dalších orgánů v dohledu nad PML |

### 3.4 Kvalita a bezpečnost (7) — `kategorie_id=4`

| # | slug | Primary keyword | Заголовок |
|---|------|----------------|-----------|
| 24 | `coa-kratom-jak-cist` | coa kratom | Certificate of Analysis (COA) u kratomu: jak číst protokol |
| 25 | `tezke-kovy-kratom` | těžké kovy kratom | Těžké kovy v kratomu: limity, testování, normy EU |
| 26 | `mykotoxiny-kratom` | mykotoxiny kratom | Mykotoxiny v rostlinných práších: kontaminace a kontrola |
| 27 | `mikrobiologie-kratom` | kratom mikrobiologie | Mikrobiologická bezpečnost kratomu: testy a limity |
| 28 | `hplc-icp-ms-kratom` | hplc kratom | HPLC a ICP-MS: laboratorní metody pro analýzu kratomu |
| 29 | `skladovani-kratomu` | skladování kratomu | Skladování kratomového prášku: stabilita alkaloidů |
| 30 | `kratom-extrakt-vs-prasek` | rozdíl kratom extrakt prášek | Kratom extrakt vs prášek: technologie výroby |

---

## 4. Anti-cannibalization чек-лист

Эти ключи **запрещены** в Pruvodce — они принадлежат коммерческим страницам:

| Ключ | Куда уходит трафик |
|------|---------------------|
| `koupit kratom`, `kratom koupit`, `kratom e-shop` | `/kratom` (catalog) |
| `zeleny kratom`, `bily kratom`, `cerveny kratom` | `/kratom/{barva}` (taxonomy) |
| `maeng da`, `bali kratom`, `borneo kratom` | `/kratom/{strain}` |
| `kratom extrakt koupit`, `kratom prášek koupit` | `/kratom/extrakt`, `/kratom/prasek` |
| `kratom cena`, `kratom levně` | `/kratom` (homepage + catalog) |
| `kratom akce`, `kratom sleva` | без посадки, не таргетим |
| `nejlepsi kratom`, `kratom doporuceni` | `/kratom` |
| `kratom davkovani`, `jak uzivat kratom` | **никуда, не таргетим** (правовой риск 2026) |
| `kratom ucinky`, `kratom efekty` | **никуда** (medical claims) |

Любой H1 или title в wiki не должен содержать `koupit`, `cena`, `nejlepší`,
`doporučujeme`, `prodej`. Если pattern-сканер админки находит — сигнал ⚠️ в
UI (фаза 03).

---

## 5. Структура одной статьи (контентный шаблон)

Каждая wiki-статья должна следовать одному скелету:

```
H1: {заголовок}
Lead (1–2 предложения, нейтрально, без CTA, ~160 знаков ≈ meta description)

H2: Definice / Stručně  (определение в 2–4 абзацах)
H2: {2–4 тематических раздела}  — например для «mitragynin»:
    Struktura a chemie
    Výskyt v rostlině
    Mechanismus účinku (vědecký popis)
    Výzkum a literatura
H2: Související pojmy  (4–8 буллетов со ссылками на другие wiki-статьи)
H2: Často kladené otázky  (3–5 пар вопрос/ответ, по 1–2 предложения)
H2: Reference  (1–3 ссылки на PubMed / WHO / český legislativní zdroj)

[footer auto: «Související články» — ручной список из admin]
```

**Длина:** 600–1200 слов на статью. Не больше — это wiki, не лонгрид.
**Стиль:** настоящее время, безличные конструкции, ноль превосходных степеней.

---

## 6. Технический стек разделa

- **БД:** 3 таблицы (`wiki_categories`, `wiki_articles`, `wiki_article_related`).
- **Admin API:** `Admin\WikiCategoryController`, `Admin\WikiArticleController`
  под префиксом `/admin-api/pruvodce/*` (см. PHASE-02).
- **Admin UI:** Nuxt 3 страницы `pages/pruvodce/index.vue`, `[id].vue`,
  `categories/index.vue`, + пункт меню `Průvodce` под `Lab-tests` в
  `layouts/default.vue`.
- **Public frontend:** Blade — новый контроллер `PruvodceController` ИЛИ
  методы в существующем `PageController`. Решение: завести **отдельный
  `PruvodceController`** (по аналогии с `ForumController`) — расход маленький,
  чистота выше.
- **SCSS:** новый `_pruvodce.scss` под `resources/scss/pages/`, импортится в
  основной `app.scss`. Карточки без обложек, плотная серо-кремовая палитра.

---

## 7. Что считается «выполнено» (Definition of Done)

После фаз 01–04:
- `/pruvodce` отдаёт landing с 4 категориями (даже если в БД 0 статей).
- В админке `/pruvodce` страница со списком статей + кнопкой «Nový článek».
- Можно создать статью с категорией, slug, body (TipTap), seo_keyword,
  meta_title, meta_description, обложкой опционально, статус draft/published.
- Опубликованная статья видна на `/{kategorie}/{slug}`, есть breadcrumbs,
  есть блок «Související články».
- Sitemap.xml включает все опубликованные wiki-статьи (если sitemap-генератор
  настроен в проекте — проверить).

После фаз 05–07:
- В БД 30 опубликованных статей, все категории заполнены, перелинковка
  между статьями внутри одной категории и через glosář-ссылки (мин. 3
  внутренних ссылки на статью).

---

## 8. Замечания, которые легко забыть

- **Старая `pages/static/guide.blade.php`** — после фазы 04 её view не
  рендерится через роут (он переезжает на `PruvodceController`), но файл
  оставляем на 1 спринт для отката. После фазы 07 — удалить + git rm.
- **`Locale::url()`** — все внутренние ссылки в blade-шаблонах оборачиваем
  через `Locale::url('/pruvodce/...')`, иначе сломаются мультиязычные
  префиксы.
- **Sanctum guard** в admin API — все маршруты wiki под `auth:sanctum`,
  иначе кеш-warmer и публичный фронт смогут вызвать админ-endpoints.
- **TipTap** хранит body как HTML — на стороне фронта пропускаем через
  `{!! $article->body !!}` (как в `forum/topic.blade.php`). На записи —
  серверная санитизация через `HtmlPurifier` (`mews/purifier` есть в проекте?
  если нет — добавить, см. PHASE-02).
