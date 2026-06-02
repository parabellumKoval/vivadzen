<?php

namespace Database\Seeders;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

/**
 * Контент wiki, категория «Legislativa ČR» (8 статей).
 *
 * Formulace přísně neutrální: «zákon stanoví», «podle zákona»,
 * žádná doporučení, žádné výzvy k jednání. Při prvním odkazu plné
 * jméno aktu (zákon č. 167/1998 Sb., o návykových látkách, v účinné
 * novele od 1. ledna 2026, PML-režim).
 */
class WikiContentLegislativaSeeder extends Seeder
{
    public function run(): void
    {
        $category = WikiCategory::where('slug', 'legislativa-cr')->firstOrFail();

        $articles = [
            $this->kratomZakonCesko2026(),
            $this->psychomodulacniLatky(),
            $this->pravniStatusKratomuCr(),
            $this->kratomStatusEu(),
            $this->vekovaHraniceKratom(),
            $this->licencovaniPmlCr(),
            $this->dovozKratomuCr(),
            $this->suklMzCrDohled(),
        ];

        $created = [];
        foreach ($articles as $position => $a) {
            $created[$a['slug']] = WikiArticle::updateOrCreate(
                ['slug' => $a['slug']],
                array_merge($a, [
                    'wiki_category_id' => $category->id,
                    'position' => ($position + 1) * 10,
                    'status' => 'published',
                    'published_at' => now()->subDays(15 - $position),
                ]),
            );
        }

        $links = [
            'kratom-zakon-cesko-2026' => ['psychomodulacni-latky', 'pravni-status-kratomu-cr', 'vekova-hranice-kratom', 'licencovani-pml-cr', 'sukl-mz-cr-dohled'],
            'psychomodulacni-latky' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr', 'kratom-status-eu'],
            'pravni-status-kratomu-cr' => ['kratom-zakon-cesko-2026', 'psychomodulacni-latky', 'vekova-hranice-kratom'],
            'kratom-status-eu' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr', 'dovoz-kratomu-cr'],
            'vekova-hranice-kratom' => ['kratom-zakon-cesko-2026', 'pravni-status-kratomu-cr'],
            'licencovani-pml-cr' => ['kratom-zakon-cesko-2026', 'dovoz-kratomu-cr', 'sukl-mz-cr-dohled'],
            'dovoz-kratomu-cr' => ['licencovani-pml-cr', 'sukl-mz-cr-dohled', 'kratom-indonesie'],
            'sukl-mz-cr-dohled' => ['kratom-zakon-cesko-2026', 'licencovani-pml-cr', 'pravni-status-kratomu-cr'],
        ];

        foreach ($links as $slug => $relatedSlugs) {
            $article = $created[$slug] ?? null;
            if (!$article) {
                continue;
            }

            $ids = WikiArticle::whereIn('slug', $relatedSlugs)
                ->where('id', '!=', $article->id)
                ->pluck('id')
                ->all();

            $pivot = [];
            foreach ($ids as $i => $id) {
                $pivot[$id] = ['position' => $i];
            }
            $article->related()->sync($pivot);
        }
    }

    // ──────────── статьи ────────────

    private function kratomZakonCesko2026(): array
    {
        return [
            'slug' => 'kratom-zakon-cesko-2026',
            'title' => 'Nová regulace kratomu v ČR od 2026: kompletní přehled',
            'excerpt' => 'Od 1. ledna 2026 spadá kratom v ČR pod kategorii psychomodulačních látek (PML) podle novely zákona č. 167/1998 Sb. Přehled klíčových změn.',
            'seo_keyword' => 'kratom zákon česko 2026',
            'seo_secondary_keywords' => ['kratom 2026 česko', 'kratom novela 2026', 'kratom regulace', 'kratom PML'],
            'seo_meta_title' => 'Kratom v ČR od 2026 — PML-režim podle 167/1998 Sb. | Vivadzen Průvodce',
            'seo_meta_description' => 'Nová regulace kratomu v ČR od 1. ledna 2026: zařazení mezi psychomodulační látky (PML) podle novely zákona č. 167/1998 Sb. Přehled změn.',
            'reading_time_minutes' => 7,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>K <strong>1. lednu 2026</strong> vstoupila v účinnost novela <em>zákona č. 167/1998 Sb., o návykových látkách</em>, která zavádí kategorii <strong>psychomodulačních látek (PML)</strong> a zařazuje do ní kratom (<em>Mitragyna speciosa</em>) i jeho hlavní alkaloidy. Tento článek shrnuje znění novely a její základní dopady na trh i spotřebitele tak, jak je popisuje aktuální právní úprava.</p>

<h2>Pozadí novely</h2>
<p>Před novelou nebyl kratom v ČR zařazen mezi návykové látky podle <em>zákona č. 167/1998 Sb., o návykových látkách</em>, ani podle nařízení vlády č. 463/2013 Sb. Trh fungoval v režimu obecné spotřebitelské regulace — bez specifické licence, bez věkového omezení uvedeného v právních předpisech a bez zvláštního dohledu státních orgánů.</p>
<p>V průběhu let 2022–2025 probíhala v Parlamentu ČR debata o regulačním rámci pro skupinu rostlinných a syntetických látek, které nesplňují kritéria klasických návykových látek, ale podle dostupných farmakologických dat mají psychotropní účinek. Výsledkem je <strong>novela zákona č. 167/1998 Sb.</strong> přijatá v roce 2025 a účinná od 1. ledna 2026.</p>
<p>Novela definuje novou kategorii <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulačních látek (PML)</a> jako paralelní k existujícím kategoriím návykových látek.</p>

<h2>Klíčové změny od 1. 1. 2026</h2>
<ol>
    <li><strong>Zařazení kratomu jako PML.</strong> Novela uvádí kratom a jeho hlavní alkaloidy (mitragynin, 7-hydroxymitragynin) na seznam PML.</li>
    <li><strong>Licenční režim pro distribuci.</strong> Prodej, dovoz a velkoobchod podléhá <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">licenci pro zacházení s PML</a>, kterou vydává Ministerstvo zdravotnictví ČR.</li>
    <li><strong>Věkové omezení.</strong> Zákon stanoví minimální věk kupujícího <a href="/pruvodce/legislativa-cr/vekova-hranice-kratom">18 let</a>.</li>
    <li><strong>Požadavky na značení a balení.</strong> Balení musí obsahovat informaci o obsahu hlavních alkaloidů, dávku v jednotce výrobku a informaci o omezení pro nezletilé.</li>
    <li><strong>Omezení reklamy.</strong> Reklama směrovaná na nezletilé je zakázána; obecná reklama podléhá pravidlům podle <em>zákona č. 40/1995 Sb., o regulaci reklamy</em>.</li>
    <li><strong>Sankce.</strong> Porušení povinnosti distributora se trestá pokutou; nezákonná distribuce bez licence je trestným činem.</li>
</ol>

<h2>Co změna znamená pro distribuci a prodej</h2>
<p>Distributoři, dovozci a maloobchodní prodejci kratomu jsou podle nového znění zákona povinni:</p>
<ul>
    <li>získat licenci pro zacházení s PML před zahájením činnosti,</li>
    <li>vést evidenci skladových zásob a prodejů ve struktuře předepsané prováděcí vyhláškou,</li>
    <li>uchovávat certifikáty analýzy (COA) pro každou šarži,</li>
    <li>ověřovat věk kupujícího při prodeji.</li>
</ul>
<p>Detailní procesní pravidla popisují prováděcí vyhlášky vydávané Ministerstvem zdravotnictví ČR.</p>

<h2>Co změna znamená pro spotřebitele (informativně)</h2>
<p>Z hlediska spotřebitele přináší novela tři hlavní změny:</p>
<ul>
    <li>kratom je dostupný pouze u prodejců s platnou PML-licencí,</li>
    <li>prodej osobám mladším 18 let je zakázán,</li>
    <li>balení obsahuje informaci o obsahu hlavních alkaloidů a o omezeních.</li>
</ul>
<p>Z hlediska osobního držení zákon nezavádí kvantitativní limit pro spotřebitele odlišný od obecných ustanovení o návykových látkách. Detailněji v článku <a href="/pruvodce/legislativa-cr/pravni-status-kratomu-cr">Právní status kratomu v ČR</a>.</p>

<h2>Časová osa implementace</h2>
<ul>
    <li><strong>1. 1. 2026</strong> — účinnost základní novely (PML-režim).</li>
    <li><strong>1. 7. 2026</strong> — předpokládaná účinnost prováděcí vyhlášky ministerstva zdravotnictví; přesné znění bylo v době přípravy tohoto článku ještě upřesňováno.</li>
    <li><strong>do konce 2026</strong> — přechodné období pro existující prodejce na získání PML-licence (přesné znění přechodných ustanovení je v prováděcí vyhlášce).</li>
</ul>

<h2>Často kladené otázky</h2>
<h3>Od kdy přesně novela platí?</h3>
<p>Základní část novely je účinná od 1. ledna 2026. Některá ustanovení (zejména v oblasti evidence a značení) jsou prováděna vyhláškou, jejíž účinnost je stanovena na 1. července 2026.</p>
<h3>Mění se status kratomu z volného prodeje na PML?</h3>
<p>Ano. Před 1. 1. 2026 platil obecný spotřebitelský režim; od 1. 1. 2026 je kratom zařazen mezi psychomodulační látky podle 167/1998 Sb.</p>
<h3>Co je prováděcí vyhláška a kdy vstupuje v účinnost?</h3>
<p>Prováděcí vyhláška je sekundární právní akt, který upřesňuje technické požadavky novely (značení, evidence, postupy). Vyhláška k novele 167/1998 Sb. má účinnost od 1. července 2026.</p>
<h3>Týká se novela i extraktů a koncentrátů?</h3>
<p>Ano. Zákon zařazuje mezi PML <em>Mitragyna speciosa</em> i přípravky obsahující její hlavní alkaloidy (mitragynin, 7-hydroxymitragynin), tedy i extrakty a koncentráty.</p>

<h2>Reference</h2>
<ul>
    <li>Zákon č. 167/1998 Sb., o návykových látkách, v platném znění (novela účinná od 1. 1. 2026).</li>
    <li>Sbírka zákonů ČR — Sbírka mezinárodních smluv, ročník 2025.</li>
    <li>Důvodová zpráva k novele zákona č. 167/1998 Sb. (Poslanecká sněmovna PČR).</li>
</ul>
HTML,
        ];
    }

    private function psychomodulacniLatky(): array
    {
        return [
            'slug' => 'psychomodulacni-latky',
            'title' => 'Psychomodulační látky (PML): kategorie podle 167/1998 Sb.',
            'excerpt' => 'Psychomodulační látky (PML) jsou nová kategorie zavedená novelou zákona č. 167/1998 Sb. účinnou od 1. 1. 2026. Definice, rozdíl od návykových látek, doplňování seznamu.',
            'seo_keyword' => 'psychomodulační látky',
            'seo_secondary_keywords' => ['PML zákon', 'psychomodulační látka definice', 'PML 167/1998'],
            'seo_meta_title' => 'Psychomodulační látky (PML) — definice a režim 167/1998 Sb. | Vivadzen Průvodce',
            'seo_meta_description' => 'Psychomodulační látky (PML) podle zákona č. 167/1998 Sb.: definice, rozdíl od návykových látek, seznam látek a srovnání s mezinárodní regulací.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Psychomodulační látka (PML)</strong> je podle novely <em>zákona č. 167/1998 Sb., o návykových látkách</em>, účinné od 1. 1. 2026, kategorie zahrnující látky, které mají dokumentovaný psychotropní účinek na centrální nervový systém, ale nesplňují kritéria pro zařazení mezi <em>návykové látky</em> ve smyslu § 2 téhož zákona ani mezi <em>léčivé přípravky</em> podle <em>zákona č. 378/2007 Sb., o léčivech</em>.</p>
<p>Kategorie PML byla zavedena jako právní rámec pro skupinu rostlinných a syntetických sloučenin, jejichž výskyt na trhu dlouhodobě vybočoval z dosavadních regulačních režimů. Kratom je jednou z prvních látek na seznamu PML.</p>

<h2>Co PML znamená</h2>
<p>Zařazení látky mezi PML přináší podle zákona následující obecná pravidla:</p>
<ul>
    <li>distribuci může provádět pouze osoba s <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">licencí pro zacházení s PML</a>,</li>
    <li>prodej osobám mladším 18 let je zakázán (viz <a href="/pruvodce/legislativa-cr/vekova-hranice-kratom">věkové omezení</a>),</li>
    <li>balení musí obsahovat informaci o obsahu hlavních účinných látek,</li>
    <li>reklama směrovaná na nezletilé je zakázána,</li>
    <li>dovoz, vývoz i tranzit podléhá oznamovací povinnosti vůči Ministerstvu zdravotnictví ČR.</li>
</ul>
<p>Konkrétní procesní detaily (forma evidence, technické náležitosti značení) jsou v prováděcích vyhláškách.</p>

<h2>Rozdíl od návykových látek § 2</h2>
<p>Klíčový rozdíl spočívá v <strong>míře omezení distribuce</strong>:</p>
<ul>
    <li><strong>Návyková látka</strong> (§ 2 zák. 167/1998 Sb.) — typicky neprochází běžným spotřebitelským trhem. S látkou mohou zacházet pouze lékárny, výzkumné instituce a další subjekty podle samostatných povolení. Distribuce mimo tento okruh je trestným činem.</li>
    <li><strong>Psychomodulační látka (PML)</strong> — distribuce je možná v komerční síti, ale pod licencí MZ ČR, s evidencí a omezeními (věk, značení, reklama).</li>
</ul>
<p>PML je tedy svým charakterem středně přísný režim mezi obecným spotřebitelským trhem a režimem návykových látek.</p>

<h2>Seznam látek a způsob doplňování</h2>
<p>Seznam látek zařazených pod PML je veden Ministerstvem zdravotnictví ČR. Doplnění nové látky probíhá:</p>
<ol>
    <li>na základě podnětu Státního ústavu pro kontrolu léčiv (SÚKL) nebo Národní protidrogové centrály Policie ČR,</li>
    <li>po posouzení odborné komise MZ ČR,</li>
    <li>vydáním prováděcí vyhlášky nebo nařízení vlády.</li>
</ol>
<p>K 1. 1. 2026 jsou na seznamu zejména kratom (<em>Mitragyna speciosa</em>) a jeho hlavní alkaloidy. Další látky jsou předmětem hodnocení.</p>

<h2>Vztah k EU regulaci</h2>
<p>Kategorie PML je čistě národní právní konstrukce. Evropská unie nemá k roku 2026 harmonizovanou regulaci pro skupinu látek, kterou ČR pokrývá pojmem PML; jednotlivé členské státy zvolily různé přístupy (úplný zákaz, novel food režim, žádná specifická úprava). Srovnání popisuje článek <a href="/pruvodce/legislativa-cr/kratom-status-eu">Status kratomu v zemích EU</a>.</p>

<h2>Často kladené otázky</h2>
<h3>Proč byla kategorie PML zavedena?</h3>
<p>Důvodová zpráva uvádí, že stávající rozdělení mezi „návykové látky" a obecný spotřebitelský trh neumožňovalo přiměřenou regulaci skupiny látek s dokumentovaným psychotropním účinkem, které nejsou klasickými narkotiky. PML je odpovědí na tuto regulační mezeru.</p>
<h3>Kdo látky do seznamu zařazuje?</h3>
<p>Ministerstvo zdravotnictví ČR po posouzení odborné komise. Podněty mohou podat SÚKL, NPC PČR nebo další státní orgány.</p>
<h3>Liší se to od USA Schedule?</h3>
<p>Ano. Americký systém Controlled Substances Act zná pět tříd (Schedule I–V), všechny s rysem narkotik. PML je samostatná kategorie bez přímého ekvivalentu v americkém právu — bližší je například francouzský model <em>plantes à effets psychotropes</em>.</p>
<h3>Mohou se na seznam PML dostat i syntetické látky?</h3>
<p>Ano. Zákon nerozlišuje rostlinný a syntetický původ; rozhodující je funkční kritérium psychotropního účinku bez splnění kritérií pro návykovou látku.</p>

<h2>Reference</h2>
<ul>
    <li>§ 2 a § 3 zákona č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
    <li>Důvodová zpráva k novele zákona č. 167/1998 Sb. (Poslanecká sněmovna PČR, 2025).</li>
    <li>Sbírka zákonů ČR, ročník 2025.</li>
</ul>
HTML,
        ];
    }

    private function pravniStatusKratomuCr(): array
    {
        return [
            'slug' => 'pravni-status-kratomu-cr',
            'title' => 'Právní status kratomu v ČR: jak je regulován',
            'excerpt' => 'Do 1. 1. 2026 byl kratom v ČR v režimu volného prodeje. Od 1. 1. 2026 je zařazen mezi psychomodulační látky (PML) podle 167/1998 Sb.',
            'seo_keyword' => 'kratom legální česko',
            'seo_secondary_keywords' => ['kratom legální', 'je kratom legální v česku', 'kratom právní status'],
            'seo_meta_title' => 'Právní status kratomu v ČR — režim PML od 2026 | Vivadzen Průvodce',
            'seo_meta_description' => 'Právní status kratomu v ČR: režim před a po 1. 1. 2026, povinnosti distributorů, omezení pro spotřebitele a sankce za porušení.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Právní status kratomu v ČR se k 1. lednu 2026 zásadně mění. Do té doby byla rostlina i její přípravky v režimu volného prodeje bez specifické regulace; od 1. 1. 2026 zákon zařazuje kratom mezi <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulační látky (PML)</a> podle novely <em>zákona č. 167/1998 Sb., o návykových látkách</em>.</p>

<h2>Status do 1. 1. 2026 (volný prodej)</h2>
<p>Před účinností novely platil pro kratom obecný spotřebitelský režim podle:</p>
<ul>
    <li><em>zákona č. 634/1992 Sb., o ochraně spotřebitele</em>,</li>
    <li><em>zákona č. 110/1997 Sb., o potravinách a tabákových výrobcích</em> (pro označení a deklaraci),</li>
    <li><em>zákona č. 40/1995 Sb., o regulaci reklamy</em>.</li>
</ul>
<p>Kratom nebyl výslovně regulován jako návyková látka, neměl licenční režim a v právních předpisech nebyl uveden minimální věk kupujícího. Některé prodejny dobrovolně omezovaly prodej na osoby nad 18 let, ale šlo o smluvní pravidlo, nikoliv o zákonnou povinnost.</p>

<h2>Status od 1. 1. 2026 (PML)</h2>
<p>Účinností novely se kratom přesouvá do nového právního režimu. Klíčové body popisuje samostatný článek <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">Nová regulace kratomu v ČR od 2026</a>; ve stručnosti:</p>
<ul>
    <li>distribuce, dovoz a velkoobchod podléhá <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">licenci pro zacházení s PML</a>,</li>
    <li>prodej osobám mladším 18 let je zakázán,</li>
    <li>balení musí obsahovat informaci o obsahu hlavních alkaloidů,</li>
    <li>reklama směrovaná na nezletilé je zakázána.</li>
</ul>

<h2>Co se mění v praxi</h2>
<h3>Pro distributory a prodejce</h3>
<p>Subjekty obchodující s kratomem jsou podle zákona povinny získat PML-licenci, vést evidenci skladu i prodejů a uchovávat certifikáty analýzy. Konkrétní procesní požadavky upřesňuje prováděcí vyhláška účinná od 1. 7. 2026; do té doby běží přechodné období pro existující subjekty.</p>
<h3>Pro dovozce</h3>
<p>Dovoz kratomu ze třetích zemí podléhá oznamovací povinnosti vůči Ministerstvu zdravotnictví a obecným celním předpisům. Detailně v článku <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">Dovoz kratomu do ČR</a>.</p>
<h3>Pro spotřebitele</h3>
<p>Z hlediska držení a osobního použití zákon nezavádí kvantitativní limit odlišný od obecných ustanovení. Hlavní změna pro spotřebitele je <strong>dostupnost pouze u prodejců s PML-licencí</strong> a povinné ověření věku 18+.</p>

<h2>Sankce za porušení</h2>
<p>Zákon rozlišuje:</p>
<ul>
    <li><strong>Správní delikt distributora</strong> — porušení povinnosti vést evidenci, řádně značit balení nebo omezit prodej nezletilým. Sankcí je pokuta podle zákona o správních deliktech.</li>
    <li><strong>Trestný čin nedovolené distribuce</strong> — uvedení PML na trh bez licence. Sankcí je trest odnětí svobody, peněžitý trest a možnost propadnutí věci.</li>
    <li><strong>Prodej osobám mladším 18 let</strong> — správní delikt s pokutou; opakované porušení může vést k odebrání licence.</li>
</ul>
<p>Konkrétní sazby pokut jsou v zákoně a aktualizovány novelami zákona o správních deliktech.</p>

<h2>Často kladené otázky</h2>
<h3>Je kratom dnes (k 1. 6. 2026) legální?</h3>
<p>Ano, ale ve specifickém právním režimu. Distribuce vyžaduje PML-licenci, prodej je omezen na osoby 18+ a podléhá informačním povinnostem podle 167/1998 Sb.</p>
<h3>Je to droga?</h3>
<p>Ve smyslu zákona není kratom <em>návyková látka</em> podle § 2 zák. 167/1998 Sb. Je <em>psychomodulační látka (PML)</em>, což je samostatná kategorie zavedená novelou.</p>
<h3>Co znamená „PML"?</h3>
<p>Psychomodulační látka. Detailní definice v článku <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">Psychomodulační látky (PML) podle 167/1998 Sb.</a>.</p>
<h3>Týká se PML-režim i extraktů?</h3>
<p>Ano. Zákon zařazuje mezi PML <em>Mitragyna speciosa</em> a její hlavní alkaloidy, tedy i přípravky obsahující koncentrovaný mitragynin nebo 7-hydroxymitragynin.</p>

<h2>Reference</h2>
<ul>
    <li>Zákon č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
    <li>Zákon č. 634/1992 Sb., o ochraně spotřebitele, v platném znění.</li>
    <li>Důvodová zpráva k novele zákona č. 167/1998 Sb. (Poslanecká sněmovna PČR, 2025).</li>
</ul>
HTML,
        ];
    }

    private function kratomStatusEu(): array
    {
        return [
            'slug' => 'kratom-status-eu',
            'title' => 'Status kratomu v zemích EU: srovnání',
            'excerpt' => 'Členské státy EU regulují kratom odlišně: úplný zákaz (Polsko, Litva, Lotyšsko), režim novel food (Belgie, Itálie) nebo bez specifické regulace (Německo, Rakousko).',
            'seo_keyword' => 'kratom status eu',
            'seo_secondary_keywords' => ['kratom evropa', 'kratom polsko', 'kratom německo', 'kratom regulace eu'],
            'seo_meta_title' => 'Kratom v EU — srovnání regulací podle členských států | Vivadzen Průvodce',
            'seo_meta_description' => 'Srovnání regulace kratomu v EU: zakazující státy (Polsko, Litva, Lotyšsko), režim novel food (Belgie, Itálie) i státy bez specifické úpravy.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Evropská unie k roku 2026 nemá harmonizovanou regulaci kratomu. Členské státy zvolily tři základní přístupy: <strong>úplný zákaz</strong>, režim <strong>novel food</strong> (regulace jako nového typu potraviny) a <strong>žádná specifická regulace</strong>. Český režim psychomodulačních látek (PML) je v evropském kontextu samostatným modelem.</p>

<h2>Země s úplným zákazem</h2>
<p>Mezi členské státy, které kratom zařadily mezi kontrolované látky a v praxi zakázaly jeho držení i distribuci, patří:</p>
<ul>
    <li><strong>Polsko</strong> — od roku 2009 je <em>Mitragyna speciosa</em> na seznamu kontrolovaných látek podle polského zákona o protidrogové prevenci; držení i prodej jsou trestné.</li>
    <li><strong>Litva</strong> — od roku 2008 zařazen mezi psychotropní látky.</li>
    <li><strong>Lotyšsko</strong> — od roku 2009 obdobně.</li>
    <li><strong>Dánsko</strong> — kratom je regulován jako léčivý přípravek podle dánské lékové agentury; volný prodej není povolen.</li>
    <li><strong>Švédsko</strong> — mitragynin je od roku 2011 klasifikován jako kontrolovaná látka.</li>
    <li><strong>Finsko</strong> — kratom je regulován v rámci finského zákona o omamných látkách.</li>
</ul>

<h2>Země s regulací jako novel food</h2>
<p>Evropská komise vede registr <em>novel food</em> — potravin, které nebyly v EU významně konzumovány před rokem 1997. Kratom je v tomto registru veden jako rostlina, jejíž použití jako potraviny vyžaduje samostatné schválení podle nařízení EU 2015/2283.</p>
<p>Některé státy přistoupily k uplatnění tohoto rámce v praxi:</p>
<ul>
    <li><strong>Belgie</strong> — kratom je regulován jako novel food, distribuce bez autorizace je sankcionována.</li>
    <li><strong>Itálie</strong> — obdobně, ale s méně důsledným vymáháním.</li>
    <li><strong>Irsko</strong> — používá rámec novel food spolu s národní regulací.</li>
</ul>

<h2>Země bez specifické regulace</h2>
<p>V některých členských státech kratom k roku 2026 nemá samostatnou regulaci a obchoduje se v rámci obecných spotřebitelských předpisů:</p>
<ul>
    <li><strong>Německo</strong> — kratom není zařazen na seznam kontrolovaných látek (BtMG). Distribuce probíhá v rámci obecné regulace; jednotlivé spolkové země mohou aplikovat lokální omezení.</li>
    <li><strong>Nizozemsko</strong> — kratom není kontrolovanou látkou podle Opiumwet; obchoduje se v specializovaných obchodech.</li>
    <li><strong>Rakousko</strong> — kratom není v seznamech NPSG; obecná spotřebitelská regulace.</li>
    <li><strong>Slovensko</strong> — k roku 2026 není kratom samostatně regulován; situace se ale připravuje k legislativnímu projednání po vzoru ČR.</li>
</ul>

<h2>Pozice EU komise</h2>
<p>Evropská komise dosud nepřijala harmonizační legislativu specificky pro kratom. EFSA (Evropský úřad pro bezpečnost potravin) vydala v roce 2023 stanovisko, ve kterém doporučuje opatrnost při zacházení s kratomem v rámci potravinového řetězce a poukazuje na nedostatek dat pro plné posouzení rizika.</p>
<p>Evropský monitorovací úřad pro drogy (EMCDDA, od 2024 EUDA) zařadil kratom do svého monitorovacího systému New Psychoactive Substances (NPS) a vydává pravidelné zprávy o jeho výskytu na evropském trhu.</p>

<h2>Český režim PML v evropském kontextu</h2>
<p>Český režim <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulačních látek (PML)</a> je v evropském kontextu samostatným modelem, který stojí mezi úplným zákazem a obecným spotřebitelským trhem. Z evropských sousedů je nejblíže polskému přístupu (zákaz) a německému (žádná specifická regulace) — ČR volí střední cestu s licenčním režimem.</p>

<h2>Často kladené otázky</h2>
<h3>Mohu si přivézt kratom z Nizozemska do ČR?</h3>
<p>Z hlediska nizozemského práva je vývoz volný. Z hlediska českého práva podléhá dovoz kratomu od 1. 1. 2026 oznamovací povinnosti podle <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">PML-režimu</a>; soukromý dovoz bez licence není v souladu se zákonem.</p>
<h3>Co znamená novel food status?</h3>
<p>Jde o regulační režim EU podle nařízení 2015/2283, který se vztahuje na potraviny bez významné historie konzumace v EU před rokem 1997. Uvedení takové potraviny na trh vyžaduje samostatné schválení Evropskou komisí.</p>
<h3>Plánuje EU jednotnou regulaci?</h3>
<p>Diskuse o harmonizaci probíhá na úrovni EUDA a EFSA, ale k roku 2026 nebyl předložen závazný legislativní návrh. Změny jsou možné v horizontu 2027–2030.</p>
<h3>Liší se status kratomu na Slovensku?</h3>
<p>K roku 2026 ano. Slovensko nemá specifickou regulaci pro kratom, ale připravuje obdobnou novelu s ohledem na český vzor.</p>

<h2>Reference</h2>
<ul>
    <li>EFSA Scientific Committee (2023). Statement on the safety of <em>Mitragyna speciosa</em> products in food. <em>EFSA Journal</em>, 21(5).</li>
    <li>Veltri C., Grundmann O. (2019). Current perspectives on the impact of Kratom use. <em>Substance Abuse and Rehabilitation</em>, 10, 23–31.</li>
    <li>Nařízení Evropského parlamentu a Rady (EU) 2015/2283 o nových potravinách.</li>
</ul>
HTML,
        ];
    }

    private function vekovaHraniceKratom(): array
    {
        return [
            'slug' => 'vekova-hranice-kratom',
            'title' => 'Věková hranice pro kratom v ČR: minimum 18 let',
            'excerpt' => 'Podle novely zákona č. 167/1998 Sb. účinné od 1. 1. 2026 je zákonná věková hranice pro nákup kratomu 18 let. Pravidla ověření věku a sankce.',
            'seo_keyword' => 'kratom 18 let',
            'seo_secondary_keywords' => ['kratom věk', 'kratom mladiství zákon', 'věková hranice PML'],
            'seo_meta_title' => 'Věková hranice pro kratom v ČR — 18 let podle 167/1998 Sb. | Vivadzen Průvodce',
            'seo_meta_description' => 'Věková hranice pro nákup kratomu v ČR: minimum 18 let podle § 24 zák. 167/1998 Sb. Ověření věku online i kamenné prodejny, sankce za porušení.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Podle novely <em>zákona č. 167/1998 Sb., o návykových látkách</em>, účinné od 1. 1. 2026, stanoví zákon minimální věk kupujícího kratomu na <strong>18 let</strong>. Prodej osobám mladším 18 let je zakázán a sankcionován jako správní delikt. Pravidla pro ověření věku jsou upravena v § 24 zákona a v prováděcí vyhlášce.</p>

<h2>Zákonná hranice 18 let pro PML</h2>
<p>Věkové omezení 18 let platí obecně pro celou kategorii <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulačních látek (PML)</a>. Stejná hranice se v českém právu používá u alkoholických nápojů (<em>zákon č. 65/2017 Sb., o ochraně zdraví před škodlivými účinky návykových látek</em>) a u tabákových výrobků.</p>
<p>Zákon výslovně zakazuje:</p>
<ul>
    <li>prodej kratomu osobě mladší 18 let,</li>
    <li>poskytnutí kratomu osobě mladší 18 let (i bezúplatné),</li>
    <li>nabízení kratomu prostřednictvím reklamy směrované na nezletilé.</li>
</ul>

<h2>Jak se ověřuje při prodeji</h2>
<h3>Kamenné prodejny</h3>
<p>Prodejce s PML-licencí je při prodeji povinen ověřit věk kupujícího podle dokladu totožnosti (občanský průkaz, cestovní pas, řidičský průkaz). Při pochybnostech o věku má zákon stanovenou povinnost doklad vyžádat — bez něj prodej není možný. Tento postup je obdobný jako u prodeje alkoholu nebo tabáku.</p>
<h3>Online prodej</h3>
<p>Pro online prodej PML zákon vyžaduje použití některého z následujících mechanismů ověření věku:</p>
<ul>
    <li>ověření přes Bank ID (Nutí Identity) nebo jiný kvalifikovaný prostředek elektronické identifikace,</li>
    <li>ověření přes platební metodu, která sama vyžaduje plnoletost (typicky platební karta vydaná na jméno),</li>
    <li>doručení vyžadující předložení dokladu kurýrem.</li>
</ul>
<p>Pouhé samodeklarované zatržení „je mi 18 let" zákon nepovažuje za dostatečné ověření. Konkrétní technické požadavky upravuje prováděcí vyhláška.</p>

<h2>Sankce za prodej nezletilým</h2>
<p>Prodej kratomu osobě mladší 18 let je správním deliktem podle zákona č. 167/1998 Sb. Sankce zahrnují:</p>
<ul>
    <li>pokutu v řádu desítek tisíc až nižších stovek tisíc korun (přesné rozpětí v zákoně),</li>
    <li>při opakovaném porušení odebrání PML-licence,</li>
    <li>v případě prodeje s vědomím o nezletilosti odpovědnost podle <em>zákona č. 40/2009 Sb., trestního zákoníku</em> (ohrožení výchovy dítěte).</li>
</ul>
<p>Sankce se vztahují primárně na prodejce; spotřebitel mladší 18 let, který kratom získal, není zákonem sankcionován ve smyslu trestní odpovědnosti, jeho jednání však může být postihováno v rámci rodičovské odpovědnosti či OSPOD.</p>

<h2>Mezinárodní srovnání hranic</h2>
<ul>
    <li><strong>USA (státy s regulací KCPA)</strong> — typicky 18 nebo 21 let, podle federálního státu.</li>
    <li><strong>Thajsko</strong> — od dekriminalizace 2021 platí věková hranice 18 let pro komerční prodej.</li>
    <li><strong>Polsko</strong> — kratom je zakázán bez ohledu na věk.</li>
    <li><strong>Německo</strong> — bez specifické věkové regulace pro kratom; obecná občanskoprávní pravidla.</li>
</ul>
<p>Hranice 18 let je v mezinárodním kontextu nejčastější.</p>

<h2>Často kladené otázky</h2>
<h3>Co když má kupující 17 let?</h3>
<p>Prodej je zakázán. Prodejce s PML-licencí je povinen ověřit věk a v případě nezletilosti odmítnout prodej.</p>
<h3>Je možné získat kratom přes rodiče?</h3>
<p>Zákon zakazuje nejen prodej, ale i <em>poskytnutí</em> kratomu osobě mladší 18 let, a to i bezúplatně. Rodič, který kratom poskytne nezletilému, se dopouští správního deliktu.</p>
<h3>Jaká je sankce za prodej mladistvému?</h3>
<p>Pokuta podle zákona o správních deliktech; při opakovaném porušení odebrání licence. Přesné sazby jsou v zákoně a aktualizují se novelami.</p>
<h3>Vztahuje se hranice 18 let i na samosběr nebo darování?</h3>
<p>Ano. Zákon nerozlišuje formu, ve které je kratom poskytnut. Poskytnutí osobě mladší 18 let je zakázáno.</p>

<h2>Reference</h2>
<ul>
    <li>§ 24 zákona č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
    <li>Zákon č. 65/2017 Sb., o ochraně zdraví před škodlivými účinky návykových látek.</li>
    <li>Zákon č. 40/2009 Sb., trestní zákoník, v platném znění.</li>
</ul>
HTML,
        ];
    }

    private function licencovaniPmlCr(): array
    {
        return [
            'slug' => 'licencovani-pml-cr',
            'title' => 'Licencování distribuce PML v ČR',
            'excerpt' => 'Distribuce psychomodulačních látek v ČR vyžaduje licenci vydanou Ministerstvem zdravotnictví podle § 3 a § 5 zák. 167/1998 Sb. Podmínky, postup a sankce.',
            'seo_keyword' => 'kratom licence česko',
            'seo_secondary_keywords' => ['licence kratom', 'kratom prodej oprávnění', 'PML licence', 'licence MZ ČR'],
            'seo_meta_title' => 'Licence pro distribuci PML v ČR — kratom podle 167/1998 Sb. | Vivadzen Průvodce',
            'seo_meta_description' => 'Licence pro distribuci psychomodulačních látek (PML) v ČR: kdo ji vydává, podmínky pro získání, evidenční povinnosti a sankce za neoprávněnou distribuci.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Od 1. ledna 2026 vyžaduje distribuce psychomodulačních látek (PML), tedy i kratomu, <strong>licenci pro zacházení s PML</strong> vydávanou <strong>Ministerstvem zdravotnictví ČR</strong>. Právní základ poskytují § 3 a § 5 <em>zákona č. 167/1998 Sb., o návykových látkách</em>, v platném znění; technické a procesní detaily upravuje prováděcí vyhláška účinná od 1. července 2026.</p>

<h2>Co je licence k zacházení s PML</h2>
<p>Licence k zacházení s PML je správní oprávnění, které opravňuje držitele k jedné nebo více z následujících činností:</p>
<ul>
    <li>dovoz PML do ČR,</li>
    <li>vývoz PML z ČR,</li>
    <li>velkoobchodní distribuce na území ČR,</li>
    <li>maloobchodní prodej konečnému spotřebiteli,</li>
    <li>výroba přípravků obsahujících PML.</li>
</ul>
<p>Licence může být udělena pro jednu z těchto činností nebo pro jejich kombinaci. Subjekt může požádat o tzv. <em>omezenou licenci</em> pro konkrétní rozsah činností (např. pouze maloobchod, bez práva dovozu).</p>

<h2>Kdo licenci vydává</h2>
<p>Licenci vydává <strong>Ministerstvo zdravotnictví ČR</strong> na základě žádosti. V rámci řízení se vyjadřují i další orgány:</p>
<ul>
    <li><a href="/pruvodce/legislativa-cr/sukl-mz-cr-dohled">Státní ústav pro kontrolu léčiv (SÚKL)</a> — odborné stanovisko k bezpečnostním požadavkům,</li>
    <li>Hygienická stanice — kontrola skladovacích podmínek,</li>
    <li>Národní protidrogová centrála Policie ČR — bezpečnostní prověření žadatele.</li>
</ul>
<p>Konkrétní procesní lhůty a požadavky upravuje prováděcí vyhláška.</p>

<h2>Podmínky pro získání</h2>
<p>Zákon stanoví následující obecné podmínky pro získání PML-licence:</p>
<h3>Žadatel (právnická nebo fyzická osoba)</h3>
<ul>
    <li>doložení bezúhonnosti statutárního orgánu i odpovědné osoby,</li>
    <li>doklad o profesní způsobilosti odpovědné osoby,</li>
    <li>doložení daňové bezdlužnosti.</li>
</ul>
<h3>Skladové prostory</h3>
<ul>
    <li>uzamykatelný skladový prostor s evidencí přístupu,</li>
    <li>kontrola teploty a vlhkosti pro uchování PML,</li>
    <li>oddělení od jiných výrobků (zejména pro velkoobchod).</li>
</ul>
<h3>Evidence</h3>
<ul>
    <li>vedení šaržové evidence příjmů, výdejů a stavů,</li>
    <li>uchovávání <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">certifikátů analýzy (COA)</a> pro každou šarži,</li>
    <li>archivace dokumentace po dobu nejméně 5 let.</li>
</ul>
<h3>Odpovědná osoba</h3>
<p>Zákon vyžaduje, aby každý držitel licence měl určenou odpovědnou osobu s odborným vzděláním (typicky farmaceutickým, chemickým nebo příbuzným) a praxí v oboru. Tato osoba odpovídá za dodržování zákona uvnitř organizace.</p>

<h2>Sankce za neoprávněnou distribuci</h2>
<p>Distribuce PML bez platné licence je podle zákona:</p>
<ul>
    <li><strong>trestným činem</strong> podle § 283 zákona č. 40/2009 Sb., trestního zákoníku, ve znění novely 2025, kterou byly do skutkové podstaty doplněny PML; sankcí je trest odnětí svobody, peněžitý trest, propadnutí věci a zákaz činnosti,</li>
    <li>doprovázeno <strong>správní sankcí</strong> uložení pokuty osobám zúčastněným na neoprávněné distribuci.</li>
</ul>
<p>Pro existující prodejce, kteří v době nabytí účinnosti novely (1. 1. 2026) již obchodují s kratomem, stanoví zákon přechodné období pro získání licence; konkrétní délku období upravuje prováděcí vyhláška.</p>

<h2>Často kladené otázky</h2>
<h3>Musí mít licenci i e-shop?</h3>
<p>Ano. Zákon nerozlišuje formu distribuce — licence je vyžadována pro každý prodej PML, tedy i pro online prodej. E-shop musí navíc splňovat zvláštní požadavky na <a href="/pruvodce/legislativa-cr/vekova-hranice-kratom">ověření věku 18+</a>.</p>
<h3>Co je „omezená licence"?</h3>
<p>Omezená licence je licence vydaná pouze pro konkrétní rozsah činností (např. maloobchod, ale ne dovoz). Žadatel může vymezit, které činnosti chce vykonávat, a licence se vydá pouze pro tyto.</p>
<h3>Liší se licence pro dovoz a pro prodej?</h3>
<p>Ano. <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">Dovoz PML</a> má samostatný režim, který kromě licence vyžaduje oznámení každé jednotlivé dodávky a součinnost s Celní správou. Prodej (velkoobchod i maloobchod) má samostatná procesní pravidla.</p>
<h3>Lze licenci převést na jiný subjekt?</h3>
<p>Licence není převoditelná. Při změně právní formy nebo prodeji podniku musí nový subjekt podat samostatnou žádost.</p>

<h2>Reference</h2>
<ul>
    <li>§ 3 a § 5 zákona č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
    <li>§ 283 zákona č. 40/2009 Sb., trestního zákoníku, v platném znění (novela 2025).</li>
    <li>Důvodová zpráva k novele zákona č. 167/1998 Sb. (Poslanecká sněmovna PČR, 2025).</li>
</ul>
HTML,
        ];
    }

    private function dovozKratomuCr(): array
    {
        return [
            'slug' => 'dovoz-kratomu-cr',
            'title' => 'Dovoz kratomu do ČR: celní a regulační aspekty',
            'excerpt' => 'Dovoz kratomu do ČR podléhá od 1. 1. 2026 PML-režimu (licence MZ ČR) a obecné celní legislativě. Dokumentace, celní kódy a hygienické požadavky.',
            'seo_keyword' => 'kratom dovoz česko',
            'seo_secondary_keywords' => ['kratom dovoz', 'kratom celní deklarace', 'kratom phytosanitární certifikát'],
            'seo_meta_title' => 'Dovoz kratomu do ČR — celní a regulační aspekty | Vivadzen Průvodce',
            'seo_meta_description' => 'Dovoz kratomu do ČR: celní kódy, dokumentace, vztah k PML-licenci a hygienické požadavky podle EU nařízení 2017/625.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Dovoz kratomu do České republiky podléhá od 1. ledna 2026 dvojí úpravě: <strong>PML-režimu</strong> podle novely <em>zákona č. 167/1998 Sb., o návykových látkách</em>, a obecné <strong>celní a fytosanitární legislativě</strong> EU. Dovozce musí být držitelem <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">PML-licence</a> pro dovoz a každou dodávku oznámit Ministerstvu zdravotnictví ČR.</p>

<h2>Celní kódy pro rostlinné prášky (HS 1211)</h2>
<p>Kratomový list a prášek jsou v celním sazebníku EU zařazeny pod kapitolu <strong>1211</strong> — „Rostliny, části rostlin, semena a plody používané především v parfumerii, ve farmacii nebo k insekticidním, fungicidním a podobným účelům". V kombinované nomenklatuře EU se nejčastěji uvádí kód <strong>1211 90</strong> (ostatní rostliny mimo specificky vyjmenované).</p>
<p>Extrakty a koncentráty obsahující hlavní alkaloidy mohou být zařazeny pod kód <strong>1302 19</strong> (rostlinné extrakty) — zařazení závisí na konkrétní formě výrobku a vyžaduje konzultaci s celní správou.</p>

<h2>Dokumentace při dovozu</h2>
<p>Pro dovoz kratomu ze třetích zemí (typicky <a href="/pruvodce/historie-a-kultura/kratom-indonesie">Indonésie</a> nebo Thajsko) vyžadují české a evropské předpisy následující dokumenty:</p>
<ul>
    <li><strong>obchodní fakturu</strong> a balicí list,</li>
    <li><strong>certifikát původu</strong> (Form A nebo obdobný),</li>
    <li><strong>phytosanitární certifikát</strong> vydaný zemí původu podle Mezinárodní úmluvy o ochraně rostlin (IPPC),</li>
    <li><strong>certifikát analýzy (COA)</strong> šarže od akreditované laboratoře,</li>
    <li><strong>PML-licenci dovozce</strong> a kopii oznámení MZ ČR o plánované dodávce,</li>
    <li><strong>celní deklaraci</strong> v systému CELNÍ ÚP CZ.</li>
</ul>
<p>Pro některé kombinace zemí původu a typu výrobku může být vyžadována také konzultace se Státní zemědělskou a potravinářskou inspekcí (SZPI).</p>

<h2>Vztah k PML-licenci</h2>
<p>Dovoz PML je podle § 3 a § 5 zák. 167/1998 Sb. samostatnou kategorií činnosti, která vyžaduje:</p>
<ol>
    <li>platnou PML-licenci s rozsahem zahrnujícím „dovoz",</li>
    <li>předchozí oznámení každé dodávky Ministerstvu zdravotnictví,</li>
    <li>součinnost s Celní správou při kontrole zásilky,</li>
    <li>záznam dovozu v evidenci.</li>
</ol>
<p>Soukromý dovoz pro vlastní spotřebu není v souladu s PML-režimem — zákon nepředpokládá výjimku pro „osobní zásilky" obdobnou té, která existuje u některých potravin nebo doplňků stravy.</p>

<h2>Hygienické požadavky</h2>
<p>Z hlediska bezpečnosti rostlinných produktů se na kratom vztahují obecná pravidla podle <em>nařízení Evropského parlamentu a Rady (EU) 2017/625</em> (úřední kontroly potravin) a podle <em>nařízení (ES) 1881/2006</em> (maximální limity kontaminantů). Konkrétní limity se týkají:</p>
<ul>
    <li><a href="/pruvodce/kvalita-a-bezpecnost/tezke-kovy-kratom">těžkých kovů</a> (olovo, kadmium, arsen, rtuť),</li>
    <li><a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a> (aflatoxiny, ochratoxin A),</li>
    <li><a href="/pruvodce/kvalita-a-bezpecnost/mikrobiologie-kratom">mikrobiologie</a> (celkový počet mikroorganismů, plísně, salmonella).</li>
</ul>
<p>Pro PML se navíc uplatňují specifické požadavky podle prováděcí vyhlášky.</p>

<h2>Často kladené otázky</h2>
<h3>Mohu si přivézt kratom z Indonésie soukromě?</h3>
<p>PML-režim podle 167/1998 Sb. nepředpokládá výjimku pro soukromý dovoz; pro dovoz je třeba PML-licence a oznámení MZ ČR. Soukromý dovoz tedy není v souladu se zákonem.</p>
<h3>Co je phytosanitární certifikát?</h3>
<p>Dokument vydávaný oficiálním fytosanitárním orgánem země původu, kterým se potvrzuje, že rostlinný materiál nebyl napaden karanténními škůdci a nemocemi. Vychází z Mezinárodní úmluvy o ochraně rostlin (IPPC).</p>
<h3>Kontroluje to Celní správa?</h3>
<p>Ano. Celní správa ČR kontroluje obsah celní deklarace a doprovodných dokumentů. U PML může vyžádat součinnost SÚKL a SZPI.</p>
<h3>Jaký je rozdíl mezi dovozem listu a extraktu?</h3>
<p>Z celního hlediska se liší kódem (1211 vs. 1302). Z PML hlediska oba spadají do PML-režimu; extrakty však mohou mít přísnější značení a evidenci kvůli vyššímu obsahu alkaloidů.</p>

<h2>Reference</h2>
<ul>
    <li>Zákon č. 17/2012 Sb., o Celní správě České republiky, v platném znění.</li>
    <li>Nařízení Evropského parlamentu a Rady (EU) 2017/625 o úředních kontrolách.</li>
    <li>Zákon č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
</ul>
HTML,
        ];
    }

    private function suklMzCrDohled(): array
    {
        return [
            'slug' => 'sukl-mz-cr-dohled',
            'title' => 'Role SÚKL, MZ ČR a dalších orgánů v dohledu nad PML',
            'excerpt' => 'Dohled nad PML v ČR zajišťuje Ministerstvo zdravotnictví, SÚKL, Celní správa, ČOI, SZPI a Národní protidrogová centrála PČR — každý v rámci své kompetence.',
            'seo_keyword' => 'sukl kratom',
            'seo_secondary_keywords' => ['MZ ČR kratom', 'kratom státní dohled', 'PML dohled'],
            'seo_meta_title' => 'Dohled nad kratomem v ČR — role SÚKL, MZ ČR a dalších orgánů | Vivadzen Průvodce',
            'seo_meta_description' => 'Které státní orgány v ČR dohlížejí na PML a kratom: Ministerstvo zdravotnictví, SÚKL, Celní správa, ČOI, SZPI, NPC PČR. Kompetence a součinnost.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Dohled nad <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulačními látkami (PML)</a> v ČR je rozdělen mezi několik státních orgánů. Žádný z nich nemá výlučnou kompetenci — systém staví na <strong>součinnosti</strong> resortů. Hlavními aktéry jsou Ministerstvo zdravotnictví, Státní ústav pro kontrolu léčiv (SÚKL), Celní správa, Česká obchodní inspekce (ČOI), Státní zemědělská a potravinářská inspekce (SZPI) a Národní protidrogová centrála Policie ČR (NPC PČR).</p>

<h2>Ministerstvo zdravotnictví (MZ ČR) — koordinační role</h2>
<p>MZ ČR je ústředním orgánem státní správy pro oblast PML podle <em>zákona č. 2/1969 Sb., o zřízení ministerstev</em>. Jeho hlavní kompetence v rámci 167/1998 Sb.:</p>
<ul>
    <li>vydávání <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">licencí pro zacházení s PML</a>,</li>
    <li>vedení evidence držitelů licencí,</li>
    <li>rozhodování o zařazení nových látek na seznam PML,</li>
    <li>vydávání prováděcích vyhlášek k zákonu,</li>
    <li>příjem oznámení o jednotlivých dovozech.</li>
</ul>
<p>MZ ČR koordinuje činnost dalších orgánů a vede meziresortní agendu k PML.</p>

<h2>SÚKL — Státní ústav pro kontrolu léčiv</h2>
<p>SÚKL je organizační složkou MZ ČR specializovanou na kontrolu léčiv. V kontextu PML zajišťuje:</p>
<ul>
    <li>odborné posouzení žádostí o licenci (kvalita skladu, evidence, odpovědné osoby),</li>
    <li>kontrolu šarží PML na obsah deklarovaných alkaloidů,</li>
    <li>posouzení žádostí o zařazení nových látek na seznam PML,</li>
    <li>monitorování bezpečnostních signálů z trhu (např. nálezy kontaminantů).</li>
</ul>
<p>SÚKL <em>nevydává</em> licence (to dělá MZ ČR), ale poskytuje odborné stanovisko, které je pro vydání licence významné.</p>

<h2>Celní správa ČR — kontrola dovozu</h2>
<p>Celní správa kontroluje <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">dovoz a vývoz PML</a> přes vnější hranici EU. Konkrétní úkoly:</p>
<ul>
    <li>kontrola celních deklarací a doprovodných dokumentů,</li>
    <li>fyzická kontrola zásilek na základě rizikové analýzy,</li>
    <li>součinnost s MZ ČR a SÚKL při kontrole shody s PML-licencí,</li>
    <li>zadržení zásilek bez platných dokumentů.</li>
</ul>

<h2>ČOI — Česká obchodní inspekce</h2>
<p>ČOI dohlíží na <strong>maloobchodní úroveň</strong>: na shodu výrobků s deklarovaným označením, na dodržování informačních povinností a na zákaz prodeje osobám mladším 18 let. Pravomoci ČOI v oblasti PML:</p>
<ul>
    <li>kontrolní nákupy (i utajené, zejména ke zjištění prodeje nezletilým),</li>
    <li>kontrola etiket a balení,</li>
    <li>uložení pokuty za správní delikt.</li>
</ul>

<h2>SZPI — Státní zemědělská a potravinářská inspekce</h2>
<p>SZPI dohlíží na rostlinné materiály v rámci úředních kontrol potravin a krmiv podle <em>nařízení EU 2017/625</em>. V kontextu PML je její role nepřímá (PML není potravina), ale uplatňuje se zejména na hranici mezi PML a potravinovými doplňky stravy.</p>

<h2>NPC Policie ČR — vyšetřování porušení</h2>
<p>Národní protidrogová centrála Policie ČR (součást Útvaru kriminálních policejních činností) vyšetřuje trestné činy spojené s neoprávněnou distribucí PML. Konkrétní oblasti:</p>
<ul>
    <li>nelegální dovoz a distribuce bez licence,</li>
    <li>distribuce mladistvým,</li>
    <li>padělání PML-licencí nebo certifikátů,</li>
    <li>součinnost s mezinárodními orgány (Europol, Interpol).</li>
</ul>

<h2>Součinnost a meziresortní koordinace</h2>
<p>Zákon č. 167/1998 Sb. ukládá uvedeným orgánům povinnost <strong>vzájemné součinnosti</strong> a sdílení relevantních informací. V praxi probíhá koordinace zejména:</p>
<ul>
    <li>na úrovni Meziresortní protidrogové komise při Úřadu vlády,</li>
    <li>prostřednictvím Sboru pro kontrolu PML při MZ ČR,</li>
    <li>na úrovni technické spolupráce mezi SÚKL a Celní správou.</li>
</ul>

<h2>Často kladené otázky</h2>
<h3>Vydává SÚKL licence na kratom?</h3>
<p>Nikoliv přímo. Licence vydává Ministerstvo zdravotnictví ČR. SÚKL poskytuje odborné stanovisko v rámci licenčního řízení.</p>
<h3>Co dělá NPC?</h3>
<p>Národní protidrogová centrála vyšetřuje trestné činy spojené s PML — zejména distribuci bez licence, prodej nezletilým a padělání licenčních dokumentů.</p>
<h3>Kdo kontroluje obsah COA?</h3>
<p>Při licenčním řízení a kontrole šarží jsou COA posuzovány SÚKL. Na úrovni trhu může ČOI provádět ověřovací odběry a porovnávat je s deklarovaným COA.</p>
<h3>Jaký je rozdíl mezi ČOI a SZPI?</h3>
<p>ČOI dohlíží na obchod obecně (mimo potraviny a léčiva); SZPI dohlíží na potraviny, krmiva a vinařské produkty. V oblasti kratomu je primárně příslušná ČOI, SZPI vstupuje na hranici s potravinovou regulací.</p>

<h2>Reference</h2>
<ul>
    <li>Zákon č. 167/1998 Sb., o návykových látkách, v platném znění.</li>
    <li>Zákon č. 2/1969 Sb., o zřízení ministerstev a jiných ústředních orgánů státní správy ČR.</li>
    <li>Zákon č. 378/2007 Sb., o léčivech (kompetence SÚKL).</li>
</ul>
HTML,
        ];
    }
}
