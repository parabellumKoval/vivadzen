# PHASE 06 — Контент: Historie + Legislativa (статьи 11–20)

> Зависит от: PHASE-05 (статьи Botaniky уже в БД, чтобы перелинковка
> между ними и legal/historie работала).
> Время реализации: 3–4 часа (правовые статьи требуют осторожной формулировки).
> Объём: 13 статей.

## ВАЖНО: правовая аккуратность

Статьи раздела **Legislativa ČR** должны:
- Использовать формулировки «zákon stanoví», «podle zákona», ne «musíte»,
  ne «doporučujeme dodržovat».
- Цитировать **точное название** правового акта при первом упоминании:
  *zákon č. 167/1998 Sb., o návykových látkách*, в редакции
  *podle novely účinné od 1. ledna 2026 (PML-režim)*.
- Не давать никаких советов — только описывать состояние нормы.
- Не указывать конкретные KÚ, MZ или SÚKL контакты — только их роли
  (контакты быстро меняются).

Если в правовой норме на момент написания (1.6.2026) есть неопределённости —
писать прямо: «přesné znění je v současnosti specifikováno v prováděcí
vyhlášce, která vstupuje v účinnost 1. července 2026».

## Что нужно сделать

1. Создать два сидера (либо один комбинированный):
   - `WikiContentHistorieSeeder` — 5 статей категории `historie-a-kultura`.
   - `WikiContentLegislativaSeeder` — 8 статей категории `legislativa-cr`.
2. Подключить оба в `DatabaseSeeder`.
3. Расширить перелинковку: при создании каждой статьи синхронизировать её
   `related()` со списком из 3–5 пересекающихся статей (некоторые из
   PHASE-05 уже есть).
4. Прогнать сидеры, проверить страницы.

Команды как в PHASE-05:
```bash
php artisan db:seed --class=WikiContentHistorieSeeder
php artisan db:seed --class=WikiContentLegislativaSeeder
```

---

## 1. Брифы статей — Historie a kultura (5)

Стиль: исторический, нейтральный, со ссылками на даты и научно-исторические
источники. Ничего о «легализации в будущем».

### 11 — `historie-kratomu`
- **Title:** Historie kratomu: od jihovýchodní Asie po Evropu
- **SEO:** `historie kratomu` | secondary: `kratom historie`, `kratom vznik`
- **H2:** Předkoloniální použití v JV Asii / První evropské zmínky (Korthals 1839) / 20. století — výzkum a první zákazy (Thajsko 1943) / Globalizace po roce 2000
- **FAQ:** Kdy se kratom poprvé objevil v Evropě? Kdo ho popsal vědecky? Kdy se objevil v ČR (orientačně cca 2014)?
- **Reference:** Korthals 1839, Tanguay 2011.

### 12 — `kratom-thajsko-historie`
- **Title:** Kratom v Thajsku: tradice, zákazy a návrat
- **SEO:** `kratom thajsko` | secondary: `kratom thajsko zákon`, `kratom thajsko legalizace`
- **H2:** Tradiční role v jižním Thajsku / Zákaz 1943 (Kratom Act, B.E. 2486) / Postupné uvolnění (2018 lékařské použití, 2021 dekriminalizace) / Současný stav
- **FAQ:** Proč Thajsko zakázalo kratom v roce 1943? Kdo o návrat usiloval? Je dnes kratom v Thajsku legální (ano, od 2021, ale s podmínkami)?
- **Reference:** Tanguay 2011, Charoenratana 2022.

### 13 — `kratom-indonesie`
- **Title:** Indonésie jako světový dodavatel kratomu
- **SEO:** `kratom indonésie` | secondary: `kratom borneo`, `kratom sumatra`, `kratom indonesie dovoz`
- **H2:** Geografie produkce (Kalimantan, Sumatra) / Ekonomický význam pro místní farmáře / Plánovaný zákaz vývozu (debata 2019–2024, odložení) / Logistika a regulace exportu
- **FAQ:** Proč Indonésie dominuje? Plánuje zákaz vývozu? Jaký je podíl Borneja?
- **Reference:** Singh 2019, FAO 2021.

### 14 — `kratom-malajsie-ketum`
- **Title:** Kratom v Malajsii: ketum a místní kontext
- **SEO:** `kratom malajsie` | secondary: `ketum`, `kratom malajsie zákon`
- **H2:** Místní pojmenování «ketum» / Zákonné omezení od 1952 (Poison Act) / Současná debata o legalizaci / Tradiční použití
- **FAQ:** Proč se v Malajsii říká «ketum»? Je nelegální? Existuje šedý trh?
- **Reference:** Singh 2014, Hassan 2013.

### 15 — `etnobotanika-kratom`
- **Title:** Etnobotanika kratomu v jihovýchodní Asii
- **SEO:** `etnobotanika kratom` | secondary: `kratom tradice`, `kratom kultura asie`
- **H2:** Co je etnobotanika / Tradiční role v zemědělství JV Asie (žvýkání čerstvého listu) / Sociální kontext (komunitní setkání, ne rekreační „droga") / Srovnání s jinými lokálními rostlinami (betel, kava)
- **FAQ:** Pili lidé kratom jako čaj? Užívaly ho ženy? Bylo to běžné nebo výjimečné?
- **Reference:** Charoenratana 2022, Tanguay 2011.

---

## 2. Брифы статей — Legislativa ČR (8)

### 16 — `kratom-zakon-cesko-2026`
- **Title:** Nová regulace kratomu v ČR od 2026: kompletní přehled
- **SEO:** `kratom zákon česko 2026` | secondary: `kratom 2026 česko`, `kratom novela 2026`, `kratom regulace`
- **H2:** Pozadí novely (zákon č. 167/1998 Sb., PML-režim) / Klíčové změny od 1.1.2026 / Co změna znamená pro distribuci a prodej / Co změna znamená pro spotřebitele (informativně) / Časová osa implementace
- **FAQ:** Od kdy přesně novela platí? Mění se status kratomu z volného prodeje na PML? Co je prováděcí vyhláška a kdy vstupuje v účinnost?
- **Reference:** Sbírka zákonů 167/1998 Sb. v platném znění; tisková zpráva MZ ČR (orientačně, bez konkrétní URL).
- ⚠️ Эта статья — основной хаб category. На неё ссылаются 5+ других legal-статей и landing.

### 17 — `psychomodulacni-latky`
- **Title:** Psychomodulační látky (PML): kategorie podle 167/1998 Sb.
- **SEO:** `psychomodulační látky` | secondary: `PML zákon`, `psychomodulační látka definice`
- **H2:** Co je PML (definice ze zákona) / Rozdíl od návykových látek § 2 / Seznam látek a způsob doplňování / Vztah k EU regulaci
- **FAQ:** Proč byla kategorie PML zavedena? Kdo látky do seznamu zařazuje? Liší se to od USA Schedule?
- **Reference:** § 2 zák. 167/1998 Sb.

### 18 — `pravni-status-kratomu-cr`
- **Title:** Právní status kratomu v ČR: jak je regulován
- **SEO:** `kratom legální česko` | secondary: `kratom legální`, `je kratom legální v česku`
- **H2:** Status do 1.1.2026 (volný prodej) / Status od 1.1.2026 (PML) / Co se mění v praxi pro prodejce, dovozce a uživatele (popis, ne návod) / Sankce za porušení
- **FAQ:** Je kratom dnes (1.6.2026) legální? Je to droga? Co to znamená «PML»?
- **Reference:** zák. 167/1998 Sb.

### 19 — `kratom-status-eu`
- **Title:** Status kratomu v zemích EU: srovnání
- **SEO:** `kratom status eu` | secondary: `kratom evropa`, `kratom polsko`, `kratom německo`
- **H2:** Země s úplným zákazem (Polsko, Litva, Lotyšsko) / Země s regulací jako novel food (Belgie, Itálie) / Země bez specifické regulace (Německo, Nizozemsko, Rakousko) / Pozice EU komise
- **FAQ:** Mohu si přivézt kratom z Nizozemska? Co znamená novel food status? Plánuje EU jednotnou regulaci?
- **Reference:** EFSA 2023, Veltri &amp; Grundmann 2019.

### 20 — `vekova-hranice-kratom`
- **Title:** Věková hranice pro kratom v ČR: minimum 18 let
- **SEO:** `kratom 18 let` | secondary: `kratom věk`, `kratom mladiství zákon`
- **H2:** Zákonná hranice 18 let pro PML / Jak se ověřuje při prodeji (online vs kamenné prodejny) / Sankce za prodej nezletilým / Mezinárodní srovnání hranic
- **FAQ:** Co když má kupující 17? Je možné získat kratom přes rodiče? Jaká je sankce za prodej mladistvému?
- **Reference:** § 24 zák. 167/1998 Sb.

### 21 — `licencovani-pml-cr`
- **Title:** Licencování distribuce PML v ČR
- **SEO:** `kratom licence česko` | secondary: `licence kratom`, `kratom prodej oprávnění`, `PML licence`
- **H2:** Co je licence k zacházení s PML / Kdo ji vydává (MZ ČR) / Podmínky pro získání (sklad, evidence, osoba) / Sankce za neoprávněnou distribuci
- **FAQ:** Musí mít licenci i e-shop? Co je „omezená licence"? Liší se licence pro dovoz vs prodej?
- **Reference:** § 3 a § 5 zák. 167/1998 Sb.

### 22 — `dovoz-kratomu-cr`
- **Title:** Dovoz kratomu do ČR: celní a regulační aspekty
- **SEO:** `kratom dovoz česko` | secondary: `kratom dovoz`, `kratom celní deklarace`
- **H2:** Celní kódy pro rostlinné prášky (HS 1211) / Dokumentace při dovozu / Vztah k PML licenci / Hygienické požadavky (mikrobiologie, kovy)
- **FAQ:** Mohu si přivézt kratom z Indonésie soukromě? Co je phytosanitární certifikát? Kontroluje to Celní správa?
- **Reference:** zák. 17/2012 Sb. o Celní správě, nař. EU 2017/625.

### 23 — `sukl-mz-cr-dohled`
- **Title:** Role SÚKL, MZ ČR a dalších orgánů v dohledu nad PML
- **SEO:** `sukl kratom` | secondary: `MZ ČR kratom`, `kratom státní dohled`
- **H2:** SÚKL — Státní ústav pro kontrolu léčiv (role) / MZ ČR — koordinační role / Celní správa — kontrola dovozu / ČOI a SZPI — kontrola na trhu / NPC Policie ČR — vyšetřování porušení
- **FAQ:** Vydává SÚKL licence na kratom? Co dělá NPC? Kdo kontroluje obsah COA?
- **Reference:** zák. 167/1998 Sb., kompetenční zákon č. 2/1969 Sb.

---

## 3. Перелинковка для PHASE-06

В сидере добавляй массив `$links` аналогично PHASE-05. Ниже — рекомендации
(slug → 3–5 related slugs). Если related-slug ещё нет в БД (Kvalita-фаза
не запущена) — sync пропустит несуществующие, после PHASE-07 пере-сидируй
PHASE-06 чтобы добить ссылки.

```php
$links = [
    // Historie
    'historie-kratomu' => ['co-je-kratom', 'kratom-thajsko-historie', 'kratom-indonesie', 'etnobotanika-kratom'],
    'kratom-thajsko-historie' => ['historie-kratomu', 'etnobotanika-kratom', 'kratom-malajsie-ketum'],
    'kratom-indonesie' => ['kde-roste-kratom', 'historie-kratomu', 'dovoz-kratomu-cr'],
    'kratom-malajsie-ketum' => ['historie-kratomu', 'kratom-thajsko-historie', 'etnobotanika-kratom'],
    'etnobotanika-kratom' => ['historie-kratomu', 'kratom-malajsie-ketum', 'kratom-thajsko-historie', 'kde-roste-kratom'],

    // Legislativa
    'kratom-zakon-cesko-2026' => ['psychomodulacni-latky', 'pravni-status-kratomu-cr', 'vekova-hranice-kratom', 'licencovani-pml-cr', 'sukl-mz-cr-dohled'],
    'psychomodulacni-latky' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr', 'kratom-status-eu'],
    'pravni-status-kratomu-cr' => ['kratom-zakon-cesko-2026', 'psychomodulacni-latky', 'vekova-hranice-kratom'],
    'kratom-status-eu' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr', 'dovoz-kratomu-cr'],
    'vekova-hranice-kratom' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr'],
    'licencovani-pml-cr' => ['kratom-zakon-cesko-2026', 'dovoz-kratomu-cr', 'sukl-mz-cr-dohled'],
    'dovoz-kratomu-cr' => ['licencovani-pml-cr', 'sukl-mz-cr-dohled', 'kratom-indonesie'],
    'sukl-mz-cr-dohled' => ['kratom-zakon-cesko-2026', 'licencovani-pml-cr', 'pravni-status-kratomu-cr'],
];
```

---

## 4. Проверка (Definition of Done)

```bash
php artisan db:seed --class=WikiContentHistorieSeeder
php artisan db:seed --class=WikiContentLegislativaSeeder

php artisan tinker --execute='
foreach (["historie-a-kultura", "legislativa-cr"] as $slug) {
    $c = App\Models\WikiCategory::where("slug", $slug)->first();
    echo $slug . ": " . $c->publishedArticles()->count() . " published\n";
}
'
# Ожидаем:
# historie-a-kultura: 5 published
# legislativa-cr: 8 published
```

Открыть `/pruvodce/legislativa-cr/kratom-zakon-cesko-2026` — это
центральная статья нового закона, проверить:
- breadcrumbs работают
- блок «Související články» содержит 5 ссылок
- внутренние ссылки в теле статьи на `psychomodulacni-latky` и
  `pravni-status-kratomu-cr` ведут на реальные страницы
- JSON-LD в исходнике содержит правильную дату публикации.

Коммит:
```
git add -A && git commit -m "pruvodce-phase-06: content seed — Historie (5) + Legislativa CR (8)"
```

Дальше — PHASE-07 (Kvalita a bezpečnost + финальная перелинковка).
