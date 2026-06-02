# PHASE 07 — Контент: Kvalita a bezpečnost (статьи 21–30) + финал

> Зависит от: PHASE-06 (legal-статьи в БД, чтобы можно было ссылаться на
> зákon 2026 из статей COA / těžké kovy).
> Время реализации: 2–3 часа.
> Объём: 7 статей + пере-sync перелинковки + удаление старой `guide.blade.php`.

## Что нужно сделать

1. Создать сидер `WikiContentKvalitaSeeder` — 7 статей категории
   `kvalita-a-bezpecnost`.
2. Пере-запустить **все три контентных сидера** (`Veda`, `HistoriePravo`,
   `Kvalita`) — тогда `$links` финализирует перелинковку между всеми 30
   статьями.
3. Удалить старую `pages/static/guide.blade.php` и метод
   `PageController::guide()` (он больше не вызывается).
4. Проверить sitemap / canonical / JSON-LD.
5. Финальный коммит.

---

## 1. Брифы статей — Kvalita a bezpečnost (7)

### 24 — `coa-kratom-jak-cist`
- **Title:** Certificate of Analysis (COA) u kratomu: jak číst protokol
- **SEO:** `coa kratom` | secondary: `certificate of analysis kratom`, `kratom analýza protokol`, `kratom laboratorní zpráva`
- **H2:** Co je COA / Hlavní sekce protokolu (aktivní látky, kovy, mykotoxiny, PAU, mikrobiologie) / Co je číslo šarže (lot) a proč je důležité / Jak interpretovat výsledky (status V, Vn, N) / Vztah COA k regulaci od 2026
- **FAQ:** Musí mít každá šarže COA? Kdo testy provádí (akreditovaná laboratoř)? Jak ověřit pravost COA?
- **Reference:** ISO/IEC 17025; v ČR akreditace ČIA.
- ⚠️ Эта статья — главный хаб качества. На неё ссылаются все остальные 6 + landing «laboratorní testy».

### 25 — `tezke-kovy-kratom`
- **Title:** Těžké kovy v kratomu: limity, testování, normy EU
- **SEO:** `těžké kovy kratom` | secondary: `kratom olovo`, `kratom kadmium`, `kratom rtuť`, `kratom ICP-MS`
- **H2:** Které kovy se sledují (Pb, Cd, Hg, As, Ni) / Limity podle EU 2023/915 a USP / Jak se dostávají do listu (půdní akumulace) / Metoda měření (ICP-MS)
- **FAQ:** Jaký je limit olova v rostlinných surovinách? Je rtuť častý problém? Proč se hlídá nikl?
- **Reference:** Nařízení EU 2023/915, USP &lt;232&gt;.

### 26 — `mykotoxiny-kratom`
- **Title:** Mykotoxiny v rostlinných práších: kontaminace a kontrola
- **SEO:** `mykotoxiny kratom` | secondary: `aflatoxiny kratom`, `ochratoxin kratom`, `plísně v kratomu`
- **H2:** Co jsou mykotoxiny / Hlavní třídy (aflatoxiny B1/B2/G1/G2, OTA) / Vznik při sušení a skladování / Limity dle EU 2023/915
- **FAQ:** Jaký je limit pro aflatoxin B1? Lze mykotoxiny zničit varem? Jak často se testuje?
- **Reference:** EU 2023/915, EFSA 2020.

### 27 — `mikrobiologie-kratom`
- **Title:** Mikrobiologická bezpečnost kratomu: testy a limity
- **SEO:** `kratom mikrobiologie` | secondary: `kratom bakterie`, `kratom salmonella`, `kratom E. coli`
- **H2:** Co se sleduje (TAMC, TYMC, E. coli, Salmonella) / Limity podle EuPh 5.1.4 / Příčiny kontaminace (sklizeň, sušení, skladování) / Metoda detekce
- **FAQ:** Lze kratom sterilizovat ozářením? Co znamená TAMC? Je salmonella v kratomu častý nález?
- **Reference:** EuPh 5.1.4, USP &lt;2021&gt;.

### 28 — `hplc-icp-ms-kratom`
- **Title:** HPLC a ICP-MS: laboratorní metody pro analýzu kratomu
- **SEO:** `hplc kratom` | secondary: `ICP-MS kratom`, `kratom analýza metoda`, `kratom alkaloidy stanovení`
- **H2:** HPLC pro alkaloidy (mitragynin, 7-OH) / ICP-MS pro těžké kovy / Příprava vzorku / Limit of quantification (LOQ) a jeho význam
- **FAQ:** Proč ne GC-MS? Jaký je LOQ pro mitragynin? Lze měřit obsah doma?
- **Reference:** Wang 2014, USP &lt;233&gt;.

### 29 — `skladovani-kratomu`
- **Title:** Skladování kratomového prášku: stabilita alkaloidů
- **SEO:** `skladování kratomu` | secondary: `kratom uchování`, `kratom stabilita`, `kratom expirace`
- **H2:** Optimální podmínky (suché, chladné, tma) / Rozklad alkaloidů (oxidace mitragyninu) / Typická expirace (12–24 měsíců) / Obaly (PE doypack, hliníkové sáčky)
- **FAQ:** Lze kratom mrazit? Co znamená «best before»? Změní se barva při stárnutí?
- **Reference:** Sengnon 2021, Brown 2017.

### 30 — `kratom-extrakt-vs-prasek`
- **Title:** Kratom extrakt vs prášek: technologie výroby
- **SEO:** `rozdíl kratom extrakt prášek` | secondary: `kratom extrakt výroba`, `kratom prášek výroba`
- **H2:** Prášek (mletí sušeného listu) / Extrakt (vodní/alkoholová extrakce, sušení) / Koncentrace alkaloidů (1× vs 10×, 20×) / Standardizace a její limity
- **FAQ:** Co znamená «10× extrakt»? Je extrakt regulován stejně? Lze extrakt vyrobit doma?
- **Reference:** Hassan 2013, Kruegel 2018.
- ⚠️ ВАЖНО: статья не должна звучать как «купите наш extrakt». Никаких сравнений по цене/качеству продуктов магазина — только технология.

---

## 2. Финальная перелинковка для всех 30 статей

В `WikiContentKvalitaSeeder` после создания статей добавь полную карту
ссылок и пере-sync. То же самое сделай в `WikiContentVedaSeeder` и
`WikiContentHistoriePravoSeeder` (`updateOrCreate` + sync — идемпотентно).

Полный массив (склей из PHASE-05, PHASE-06 и нового блока ниже):

```php
$kvalitaLinks = [
    'coa-kratom-jak-cist' => ['tezke-kovy-kratom', 'mykotoxiny-kratom', 'mikrobiologie-kratom', 'hplc-icp-ms-kratom', 'kratom-zakon-cesko-2026'],
    'tezke-kovy-kratom' => ['coa-kratom-jak-cist', 'hplc-icp-ms-kratom', 'mykotoxiny-kratom'],
    'mykotoxiny-kratom' => ['coa-kratom-jak-cist', 'mikrobiologie-kratom', 'skladovani-kratomu'],
    'mikrobiologie-kratom' => ['coa-kratom-jak-cist', 'mykotoxiny-kratom', 'skladovani-kratomu'],
    'hplc-icp-ms-kratom' => ['mitragynin', '7-hydroxymitragynin', 'tezke-kovy-kratom', 'coa-kratom-jak-cist'],
    'skladovani-kratomu' => ['mesh-velikost-kratomu', 'mykotoxiny-kratom', 'fermentace-kratomu', 'kratom-extrakt-vs-prasek'],
    'kratom-extrakt-vs-prasek' => ['fermentace-kratomu', 'mesh-velikost-kratomu', 'skladovani-kratomu', 'alkaloidy-kratomu'],
];
```

После этого пере-запусти **все три** контентных сидера (любая идемпотентна
из-за `updateOrCreate`):

```bash
php artisan db:seed --class=WikiContentVedaSeeder
php artisan db:seed --class=WikiContentHistorieSeeder
php artisan db:seed --class=WikiContentLegislativaSeeder
php artisan db:seed --class=WikiContentKvalitaSeeder
```

Каждый sync теперь увидит все 30 статей в БД и проставит полную сеть.

---

## 3. Очистка старого guide

После того как все 30 статей доступны на фронте:

```bash
# 1) Удалить старую view
git rm src/new-kratom/app/resources/views/pages/static/guide.blade.php

# 2) В src/new-kratom/app/app/Http/Controllers/PageController.php — найти
#    метод guide() и удалить его. Также удалить use-импорты, которые
#    больше не нужны (если они там есть).

# 3) В routes/web.php убедиться, что строки `Route::get('/pruvodce', ..., 'page.guide')`
#    нет (она должна была быть удалена в PHASE-04, проверка контрольная).
```

---

## 4. Проверка SEO-инфраструктуры

### 4.1 Canonical
```bash
curl -s https://example.local/pruvodce/botanika-a-veda/co-je-kratom \
  | grep -i 'rel="canonical"'
# Должен быть один canonical с абсолютным URL.
```

### 4.2 JSON-LD
В исходнике страницы статьи должен быть `<script type="application/ld+json">`
с `@type=Article`, `headline`, `datePublished`, `inLanguage=cs`.

### 4.3 Sitemap
Проверить, какой пакет генерирует sitemap (`spatie/laravel-sitemap`?
кастомный?). Если есть — убедиться, что в нём перечислены все
`WikiArticle::published()` + landing + категории. Если нет генератора —
создать `routes/web.php` маршрут `/sitemap-pruvodce.xml` со списком.
(Это можно отдельным мини-таском, не блокер.)

### 4.4 Внутренняя перелинковка
В админке открой статью `coa-kratom-jak-cist` — на вкладке «Související»
должно быть 5 связей. На фронте `/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist`
блок «Související články» содержит эти 5 + ссылки внутри body статьи на
`mitragynin`, `tezke-kovy-kratom` и т.д.

### 4.5 Anti-cannibalization
```bash
php artisan tinker --execute='
foreach (App\Models\WikiArticle::all() as $a) {
    $bad = ["koupit", "cena", "levně", "sleva", "nejlepší"];
    $hay = mb_strtolower($a->title . " " . $a->slug . " " . ($a->seo_keyword ?? ""));
    foreach ($bad as $b) {
        if (str_contains($hay, $b)) {
            echo "⚠️  " . $a->slug . " contains: " . $b . "\n";
        }
    }
}
echo "Done.\n";
'
# Ожидаем: ни одной ⚠️.
```

---

## 5. Definition of Done всего проекта

- ✅ В БД 4 категории, 30 опубликованных статей.
- ✅ Все 30 имеют непустой `seo_keyword`.
- ✅ Ни в одном `slug`/`title` нет коммерческих терминов (см. 4.5).
- ✅ `/pruvodce`, `/pruvodce/{kategorie}`, `/pruvodce/{kategorie}/{slug}` отдают 200.
- ✅ На каждой статье 3–5 «Související články».
- ✅ JSON-LD + canonical на каждой статье.
- ✅ Старая `guide.blade.php` удалена, метод `PageController::guide` удалён.
- ✅ Админка позволяет создавать/редактировать статьи, видно колонку
     «🎯 Klíčové slovo», есть ⚠️ для коммерческих терминов.

Финальный коммит:
```
git add -A && git commit -m "pruvodce-phase-07: content seed — Kvalita (7), final cross-links, cleanup"
```

---

## 6. Что НЕ делаем на этом этапе (откладываем)

- Мультиязычные переводы статей (en/ru/uk) — отдельная итерация после
  верификации SEO-эффекта в `cs`.
- Полноценный Glossary с tooltip-определениями при наведении — на этапе
  достаточно прямых ссылок в body.
- A/B тесты заголовков — пока фиксируем подобранные SEO-варианты.
- RSS-фид `/pruvodce/feed.xml` — низкий приоритет, при необходимости
  добавляется одним эндпоинтом.

Эти задачи стоит вынести в backlog (Linear / GitHub Issues) отдельными
тикетами с тегом `pruvodce-followup`.
