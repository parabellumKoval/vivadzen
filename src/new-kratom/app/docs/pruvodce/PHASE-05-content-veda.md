# PHASE 05 — Контент: Botanika a věda (статьи 1–10)

> Зависит от: PHASE-04 (фронт работает, статьи отображаются).
> Время реализации: 2–3 часа.
> Объём: 10 статей по 600–1000 слов каждая, итог ~8000 слов.

## Что нужно сделать

1. Создать сидер `WikiContentVedaSeeder` — он создаёт 10 статей категории
   `botanika-a-veda` через `WikiArticle::updateOrCreate(['slug' => ...])`.
2. Каждая статья — отдельный приватный метод сидера, возвращающий массив
   данных. Тело статьи (`body`) — HTML с H2, параграфами, ul, в конце —
   `<h2>Často kladené otázky</h2>` (3–5 пар) и `<h2>Reference</h2>`.
3. После запуска сидера — установить ручную перелинковку:
   на каждую из 10 статей повесить 3–4 `related` (см. блок перелинковки
   ниже).
4. Подключить сидер в `DatabaseSeeder` (см. PHASE-01).
5. Запустить сидер, проверить страницы на фронте.

## Команды

```bash
cd src/new-kratom/app
php artisan db:seed --class=WikiContentVedaSeeder
# Проверить:
php artisan tinker --execute='echo App\Models\WikiArticle::published()->count();'
# Ожидаем: 10
```

---

## 1. Шаблон сидера

**Файл:** `src/new-kratom/app/database/seeders/WikiContentVedaSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

/**
 * Контент wiki, категория «Botanika a věda» (10 статей).
 *
 * Каждая статья = updateOrCreate по slug, чтобы повторные запуски
 * сидера актуализировали контент, но не дублировали записи.
 */
class WikiContentVedaSeeder extends Seeder
{
    public function run(): void
    {
        $category = WikiCategory::where('slug', 'botanika-a-veda')->firstOrFail();

        $articles = [
            $this->coJeKratom(),
            $this->mitragynaSpeciosa(),
            $this->mitragynin(),
            $this->sedmHydroxymitragynin(),
            $this->alkaloidyKratomu(),
            $this->kratomRostlina(),
            $this->kdeRosteKratom(),
            $this->barvyZilKratomu(),
            $this->fermentaceKratomu(),
            $this->meshVelikost(),
        ];

        $created = [];
        foreach ($articles as $position => $a) {
            $created[$a['slug']] = WikiArticle::updateOrCreate(
                ['slug' => $a['slug']],
                array_merge($a, [
                    'wiki_category_id' => $category->id,
                    'position' => ($position + 1) * 10,
                    'status' => 'published',
                    'published_at' => now()->subDays(30 - $position),
                ]),
            );
        }

        // Ручная перелинковка «Související články» (3–4 на каждую)
        $links = [
            'co-je-kratom' => ['mitragyna-speciosa', 'kde-roste-kratom', 'alkaloidy-kratomu', 'historie-kratomu'],
            'mitragyna-speciosa' => ['co-je-kratom', 'kratom-rostlina-zivotni-cyklus', 'kde-roste-kratom', 'fermentace-kratomu'],
            'mitragynin' => ['7-hydroxymitragynin', 'alkaloidy-kratomu', 'barvy-zil-kratomu'],
            '7-hydroxymitragynin' => ['mitragynin', 'alkaloidy-kratomu', 'hplc-icp-ms-kratom'],
            'alkaloidy-kratomu' => ['mitragynin', '7-hydroxymitragynin', 'barvy-zil-kratomu', 'fermentace-kratomu'],
            'kratom-rostlina-zivotni-cyklus' => ['mitragyna-speciosa', 'kde-roste-kratom', 'barvy-zil-kratomu'],
            'kde-roste-kratom' => ['mitragyna-speciosa', 'kratom-indonesie', 'kratom-thajsko-historie'],
            'barvy-zil-kratomu' => ['fermentace-kratomu', 'alkaloidy-kratomu', 'mitragynin'],
            'fermentace-kratomu' => ['barvy-zil-kratomu', 'mesh-velikost-kratomu', 'alkaloidy-kratomu'],
            'mesh-velikost-kratomu' => ['fermentace-kratomu', 'skladovani-kratomu', 'kratom-extrakt-vs-prasek'],
        ];

        foreach ($links as $slug => $relatedSlugs) {
            $article = $created[$slug] ?? null;
            if (!$article) continue;

            // related может ссылаться на ещё не существующие (фазы 06/07) —
            // подтягиваем только те, что уже в БД, остальные доберёт PHASE-07.
            $ids = WikiArticle::whereIn('slug', $relatedSlugs)
                ->where('id', '!=', $article->id)
                ->pluck('id')
                ->all();

            $pivot = [];
            foreach ($ids as $i => $id) { $pivot[$id] = ['position' => $i]; }
            $article->related()->sync($pivot);
        }
    }

    // ──────────── статьи ────────────

    private function coJeKratom(): array
    {
        return [
            'slug' => 'co-je-kratom',
            'title' => 'Co je kratom — botanická definice a původ',
            'excerpt' => 'Kratom je tropický strom Mitragyna speciosa z čeledi mořenovitých (Rubiaceae), původem z jihovýchodní Asie. Jeho listy obsahují přes 40 alkaloidů.',
            'seo_keyword' => 'co je kratom',
            'seo_secondary_keywords' => ['kratom rostlina', 'mitragyna speciosa', 'kratom definice'],
            'seo_meta_title' => 'Co je kratom — botanická definice, původ a obsah | Vivadzen Průvodce',
            'seo_meta_description' => 'Kratom (Mitragyna speciosa) je tropický strom z čeledi Rubiaceae původem z jihovýchodní Asie. Botanická definice, původ a obsah alkaloidů.',
            'body' => '
<h2>Definice</h2>
<p><strong>Kratom</strong> je obchodní a hovorové označení pro listy stromu <em>Mitragyna speciosa</em> Korthals, případně pro suchý prášek z těchto listů. Z botanického pohledu jde o stálezelený strom z čeledi mořenovitých (<em>Rubiaceae</em>), tedy stejné čeledi, do které patří i káva (<em>Coffea</em>) a chinovník (<em>Cinchona</em>).</p>
<p>Dospělé stromy dosahují výšky 4–16 metrů, v některých případech až 25 metrů. Listy jsou eliptické, dlouhé 14–20 cm, s výraznou žilkou, jejíž barva (zelená, bílá nebo červená) se v komerční praxi používá k označení typu suroviny.</p>

<h2>Původ a rozšíření</h2>
<p>Přirozený areál <em>Mitragyna speciosa</em> zahrnuje tropické oblasti jihovýchodní Asie: především <strong>Indonésii</strong> (zejména ostrovy Borneo, Sumatra a Jáva), <strong>Malajsii</strong>, <strong>Thajsko</strong>, <strong>Myanmar</strong> a části <strong>Papuy Nové Guineje</strong>. Strom vyžaduje vlhké tropické klima, půdy s dobrým odvodněním a roční úhrn srážek nad 1500 mm.</p>

<h2>Co kratom obsahuje</h2>
<p>Listy obsahují více než 40 identifikovaných indolových a oxindolových alkaloidů. Dominantní z nich je <strong>mitragynin</strong> (typicky 60–66 % celkového obsahu alkaloidů), v menším množství je přítomen <strong>7-hydroxymitragynin</strong>, dále speciogynin, paynantheine, mitraphylline a další. Celkový obsah alkaloidů v suchém listu se obvykle pohybuje mezi 0,5 a 1,8 % hmotnosti.</p>

<h2>Vztah ke kávě</h2>
<p>Příbuznost s kávou v rámci čeledi <em>Rubiaceae</em> je čistě botanická — kávovník i kratomovník mají podobné stavby listu a květenství, ale jejich alkaloidní profily se zásadně liší (káva: kofein, kratom: indolové alkaloidy).</p>

<h2>Často kladené otázky</h2>
<h3>Je kratom rostlina, nebo chemická látka?</h3>
<p>Kratom je rostlinný materiál (sušený list). Slovem se však často označuje i prášek vyrobený mletím listu nebo extrakt obsahující koncentrované alkaloidy.</p>
<h3>Patří kratom mezi maca, ženšen nebo kávu?</h3>
<p>Z botanického hlediska je kratom příbuzný kávě (čeleď Rubiaceae). Maca a ženšen patří do jiných čeledí (brukvovité, resp. aralkovité).</p>
<h3>Jaký je rozdíl mezi kratomem a mitragyninem?</h3>
<p>Kratom je rostlinný materiál. Mitragynin je konkrétní alkaloid, hlavní složka rostliny. Více v článku <a href="/pruvodce/botanika-a-veda/mitragynin">Mitragynin: hlavní alkaloid kratomu</a>.</p>

<h2>Reference</h2>
<ul>
    <li>Hassan Z. et al. (2013). From kratom to mitragynine and its derivatives. <em>Neuroscience &amp; Biobehavioral Reviews</em>.</li>
    <li>Cinosi E. et al. (2015). Following "the Roots" of Kratom (Mitragyna speciosa). <em>BioMed Research International</em>.</li>
</ul>
',
        ];
    }

    private function mitragynaSpeciosa(): array { return [/* ... аналогично ... */]; }
    private function mitragynin(): array { return [/* ... */]; }
    private function sedmHydroxymitragynin(): array { return [/* ... */]; }
    private function alkaloidyKratomu(): array { return [/* ... */]; }
    private function kratomRostlina(): array { return [/* ... */]; }
    private function kdeRosteKratom(): array { return [/* ... */]; }
    private function barvyZilKratomu(): array { return [/* ... */]; }
    private function fermentaceKratomu(): array { return [/* ... */]; }
    private function meshVelikost(): array { return [/* ... */]; }
}
```

---

## 2. Брифы остальных 9 статей

Каждая статья на 600–1000 слов, в формате того же скелета:
- **Lead** (1–2 предложения, ≤ 160 знаков → meta_description).
- **H2 Definice / Stručně.**
- **H2 × 2–4 тематических раздела.**
- **H2 Často kladené otázky** (3–5 пар, по 1–2 предложения).
- **H2 Reference** (1–3 источника).

### Бриф 2 — `mitragyna-speciosa`
- **Title:** Mitragyna speciosa: botanický popis rostliny
- **SEO keyword:** `mitragyna speciosa` | secondary: `mitragyna speciosa rostlina`, `kratom strom`
- **H2 sekce:** Taxonomie a zařazení / Morfologie (kořen, kmen, list, květ) / Růstové podmínky / Pěstování versus divoký výskyt
- **FAQ:** Kdo rostlinu poprvé popsal (Pieter Korthals, 1839)? Kolik subspecies se rozlišuje? Lze pěstovat v Evropě?
- **Reference:** Korthals 1839 — Verhandelingen, Sukrong 2007 — Botanical identification of M. speciosa.

### Бриф 3 — `mitragynin`
- **Title:** Mitragynin: hlavní alkaloid kratomu
- **SEO keyword:** `mitragynin` | secondary: `mitragynin struktura`, `mitragynin účinek`, `mitragynin obsah`
- **H2 sekce:** Chemická struktura (indolový alkaloid, C23H30N2O4) / Biosyntéza v rostlině / Mechanismus na molekulární úrovni (parciální agonista μ-opioidního receptoru — pouze popis z vědecké literatury, žádná doporučení) / Obsah v listu podle původu a barvy žíly
- **FAQ:** Jak se mitragynin liší od kofeinu? Jaká je polovina rozpadu? Kdy byl poprvé izolován (Field, 1921)?
- **Reference:** Field 1921, Kruegel &amp; Grundmann 2018 — Neuropharmacology of kratom.

### Бриф 4 — `7-hydroxymitragynin`
- **Title:** 7-hydroxymitragynin: stopový alkaloid kratomu
- **SEO keyword:** `7-hydroxymitragynin` | secondary: `7-OH-mitragynin`, `7-hydroxymitragynin účinek`
- **H2 sekce:** Definice (≤ 2 % alkaloidního obsahu) / Vztah k mitragyninu (oxidační produkt) / Vědecký pohled na potenci (relativní afinita k receptorům) / Analytická detekce (HPLC-MS, LOQ)
- **FAQ:** Vzniká 7-OH-MG během skladování? V jakém poměru bývá k mitragyninu? Proč je významný i ve stopovém množství?
- **Reference:** Takayama 2002, Váradi 2016.

### Бриф 5 — `alkaloidy-kratomu`
- **Title:** Alkaloidní profil kratomu: přes 40 sloučenin
- **SEO keyword:** `alkaloidy kratomu` | secondary: `kratom alkaloidy`, `složení kratomu`, `kratom látky`
- **H2 sekce:** Co jsou alkaloidy (obecná definice) / Hlavní skupiny v kratomu (mitraginiové, oxindolové, paynantheine, speciogynine) / Variabilita podle původu / Význam pro analytiku
- **FAQ:** Kolik alkaloidů bylo identifikováno (40+)? Proč jsou některé jen ve stopách? Mění se profil sušením?
- **Reference:** Brown 2017, León 2009.

### Бриф 6 — `kratom-rostlina-zivotni-cyklus`
- **Title:** Kratom jako rostlina: životní cyklus a morfologie
- **SEO keyword:** `kratom rostlina` | secondary: `kratom strom`, `kratom morfologie`, `kratom květ`
- **H2 sekce:** Klíčení a růst / Vegetační období v tropech (kontinuální vegetace) / Květenství (žluté hlavičky) / Stárnutí stromu (50+ let)
- **FAQ:** Kolik let strom plodí list použitelný pro sušení? Kvete kratom v Evropě? Jak se liší mladý strom od zralého?
- **Reference:** Sukrong 2007, Raffa 2013.

### Бриф 7 — `kde-roste-kratom`
- **Title:** Přirozený areál Mitragyna speciosa
- **SEO keyword:** `kde roste kratom` | secondary: `kratom původ`, `kratom geografie`
- **H2 sekce:** Hlavní oblasti (Borneo, Sumatra, Jáva, poloostrov Malajsie, jižní Thajsko) / Klimatické a půdní požadavky / Mapa produkce / Důvody dominance Indonésie (otevřený export)
- **FAQ:** Roste kratom planě? V kterých zemích je pěstování zakázáno? Pěstuje se i v Číně nebo Africe?
- **Reference:** FAO 2019, Cinosi 2015.

### Бриф 8 — `barvy-zil-kratomu`
- **Title:** Barvy žil kratomu: botanický a chemický základ
- **SEO keyword:** `barvy kratomu rozdíly` | secondary: `bílý kratom`, `zelený kratom`, `červený kratom rozdíl` (это инфо-вопрос «v čem rozdíl», коммерчесского intent тут нет, но **H1 НЕ должен** содержать "koupit")
- **H2 sekce:** Co je žilka listu a proč se podle ní rozlišuje typ / Jak vzniká bílá, zelená, červená (fáze růstu + sušení) / Co se přitom mění chemicky (poměr mitragynin/7-OH) / Mýty a marketing (proč není rozdíl tak ostrý, jak se uvádí)
- **FAQ:** Je „červená/zelená/bílá" botanický pojem? Existuje žlutá nebo zlatá žíla? Lze barvu žíly určit z prášku?
- **Reference:** Singh 2016, Sengnon 2021.

### Бриф 9 — `fermentace-kratomu`
- **Title:** Fermentace kratomového listu: proces a chemie
- **SEO keyword:** `kratom fermentace` | secondary: `fermentace kratomu`, `sušení kratomu`
- **H2 sekce:** Co je fermentace listu / Etapy (oxidace, enzymatická konverze, sušení) / Vliv na poměr alkaloidů / Variabilita mezi výrobci
- **FAQ:** Mění fermentace toxicitu? Lze fermentaci replikovat doma? Jak dlouho proces trvá?
- **Reference:** Sengnon 2021, Brown 2017.

### Бриф 10 — `mesh-velikost-kratomu`
- **Title:** Granulometrie kratomového prášku: mesh a mikrometry
- **SEO keyword:** `kratom mesh velikost` | secondary: `kratom prášek velikost`, `mesh 80 100 120`
- **H2 sekce:** Co znamená mesh (US/Tyler stupnice) / Převod mesh → μm (tabulka) / Vliv velikosti na rozpustnost a dávkování (popis fyziky, ne doporučení) / Standardy pro rostlinné prášky
- **FAQ:** Co je mesh 80 v μm (≈ 177 μm)? Je jemnější vždy lepší? Existuje norma pro rostlinné prášky?
- **Reference:** USP &lt;786&gt;, EuPh 2.9.38.

---

## 3. Подсказка для записи body

В сидере body — **plain HTML строкой**. Внутри статей используем
**относительные ссылки** между wiki-страницами: `/pruvodce/{kategorie}/{slug}`
(без `Locale::url`, потому что сидер работает на дефолтной локали `cs`).

Если в body встречается ссылка на статью из категорий, которые ещё не
заполнены (Historie, Legislativa, Kvalita) — это нормально: после фаз
06–07 эти ссылки начнут работать.

---

## 4. Проверка (Definition of Done)

```bash
cd src/new-kratom/app
php artisan db:seed --class=WikiContentVedaSeeder

php artisan tinker --execute='
$cat = App\Models\WikiCategory::where("slug", "botanika-a-veda")->first();
$articles = $cat->publishedArticles()->get(["slug", "title", "seo_keyword"]);
foreach ($articles as $a) {
    echo str_pad($a->slug, 35) . " | " . $a->seo_keyword . "\n";
}
echo "\nTotal: " . $articles->count() . "\n";
'
# Ожидаем: 10 статей
```

Открыть на фронте `/pruvodce/botanika-a-veda` — все 10 карточек,
кликнуть на каждую — статья отображается с breadcrumbs и блоком «Související
články» (3–4 ссылки, частично рабочие пока, частично — на будущие статьи
из фаз 06/07).

Коммит:
```
git add -A && git commit -m "pruvodce-phase-05: content seed — Botanika a věda (10 articles)"
```

Дальше — PHASE-06 (Historie + Legislativa).
