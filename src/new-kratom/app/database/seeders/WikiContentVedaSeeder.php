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

        // Ручная перелинковка «Související články» (3–4 на каждую).
        // related может ссылаться на ещё не существующие статьи (фазы 06/07) —
        // подтягиваем только те, что уже в БД, остальные доберёт PHASE-07.
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
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Kratom</strong> je obchodní a hovorové označení pro listy stromu <em>Mitragyna speciosa</em> Korthals, případně pro suchý prášek získaný jejich mletím. Z botanického pohledu jde o stálezelený strom z čeledi mořenovitých (<em>Rubiaceae</em>), tedy stejné čeledi, do které patří i káva (<em>Coffea</em>) a chinovník (<em>Cinchona</em>).</p>
<p>Dospělé stromy dosahují výšky 4–16 metrů, ve výjimečných případech až 25 metrů. Listy jsou eliptické, dlouhé 14–20 cm, s výraznou střední žilkou. Barva této žilky (zelená, bílá nebo červená) se v komerční praxi používá jako označení typu suroviny, ačkoliv se jedná o zjednodušení botanického a technologického pozadí (více v článku <a href="/pruvodce/botanika-a-veda/barvy-zil-kratomu">Barvy žil kratomu</a>).</p>

<h2>Původ a rozšíření</h2>
<p>Přirozený areál <em>Mitragyna speciosa</em> zahrnuje tropické oblasti jihovýchodní Asie: především <strong>Indonésii</strong> (zejména ostrovy Borneo, Sumatra a Jáva), <strong>Malajsii</strong>, <strong>Thajsko</strong>, <strong>Myanmar</strong> a části <strong>Papuy Nové Guineje</strong>. Strom vyžaduje vlhké tropické klima, půdy s dobrým odvodněním a roční úhrn srážek nad 1500 mm.</p>
<p>Mimo přirozený areál se kratom pěstuje jen okrajově a převážně v botanických sbírkách nebo skleníkových podmínkách. Pro komerční produkci listu je zásadní stabilní teplota nad 20 °C po celý rok, kterou mírné pásmo nenabízí.</p>

<h2>Co kratom obsahuje</h2>
<p>Listy obsahují více než 40 identifikovaných indolových a oxindolových alkaloidů. Dominantní z nich je <strong>mitragynin</strong> (typicky 60–66 % celkového obsahu alkaloidů), v menším množství je přítomen <strong>7-hydroxymitragynin</strong>, dále speciogynin, paynantheine, mitraphylline, speciociliatine a další. Celkový obsah alkaloidů v suchém listu se obvykle pohybuje mezi 0,5 a 1,8 % hmotnosti.</p>
<p>Vedle alkaloidů obsahuje list i flavonoidy, polyfenoly, terpenoidní glykosidy a saponiny. Zastoupení jednotlivých sloučenin se liší podle původu rostliny, stáří listu i způsobu sušení a fermentace.</p>

<h2>Vztah ke kávě a dalším Rubiaceae</h2>
<p>Příbuznost s kávou v rámci čeledi <em>Rubiaceae</em> je čistě botanická — kávovník i kratomovník mají podobnou stavbu listu a květenství (sférická hlavička drobných květů), ale jejich alkaloidní profily se zásadně liší. Kávovník akumuluje purinové alkaloidy (kofein), zatímco kratomovník indolové alkaloidy odvozené od tryptaminu. Tato příbuznost je proto významná z hlediska systematiky, nikoliv chemie či farmakologie.</p>

<h2>Často kladené otázky</h2>
<h3>Je kratom rostlina, nebo chemická látka?</h3>
<p>Kratom je rostlinný materiál — sušený list stromu <em>Mitragyna speciosa</em>. Slovem se však často označuje i prášek vyrobený mletím listu nebo extrakt s koncentrovanými alkaloidy.</p>
<h3>Patří kratom mezi maca, ženšen nebo kávu?</h3>
<p>Z botanického hlediska je kratom příbuzný kávě (čeleď <em>Rubiaceae</em>). Maca patří do brukvovitých (<em>Brassicaceae</em>), ženšen do aralkovitých (<em>Araliaceae</em>) — jde tedy o nepříbuzné rostliny.</p>
<h3>Jaký je rozdíl mezi kratomem a mitragyninem?</h3>
<p>Kratom je rostlinný materiál (list nebo prášek). Mitragynin je jeden konkrétní alkaloid, hlavní složka rostliny. Více v článku <a href="/pruvodce/botanika-a-veda/mitragynin">Mitragynin: hlavní alkaloid kratomu</a>.</p>
<h3>Roste kratom v Evropě?</h3>
<p>V přírodních podmínkách Evropy nikoliv. Strom potřebuje stálou tropickou teplotu a vysokou vzdušnou vlhkost, které střední Evropa neposkytuje.</p>

<h2>Reference</h2>
<ul>
    <li>Hassan Z. et al. (2013). From kratom to mitragynine and its derivatives: physiological and behavioural effects. <em>Neuroscience &amp; Biobehavioral Reviews</em>, 37(2), 138–151.</li>
    <li>Cinosi E. et al. (2015). Following "the Roots" of Kratom (<em>Mitragyna speciosa</em>): The Evolution of an Enhancer from a Traditional Use to Increase Work and Productivity in Southeast Asia. <em>BioMed Research International</em>, 2015, 968786.</li>
    <li>Brown P. N., Lund J. A., Murch S. J. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em> korth. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
</ul>
HTML,
        ];
    }

    private function mitragynaSpeciosa(): array
    {
        return [
            'slug' => 'mitragyna-speciosa',
            'title' => 'Mitragyna speciosa: botanický popis rostliny',
            'excerpt' => 'Mitragyna speciosa Korthals (1839) je stálezelený strom z čeledi Rubiaceae. Botanický popis: taxonomie, morfologie, růstové podmínky a pěstování.',
            'seo_keyword' => 'mitragyna speciosa',
            'seo_secondary_keywords' => ['mitragyna speciosa rostlina', 'kratom strom', 'mitragyna speciosa popis'],
            'seo_meta_title' => 'Mitragyna speciosa — botanický popis rostliny | Vivadzen Průvodce',
            'seo_meta_description' => 'Botanický popis Mitragyna speciosa: taxonomie, morfologie kořene, kmene, listů a květů, růstové podmínky v jihovýchodní Asii.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><em>Mitragyna speciosa</em> Korthals je tropický stálezelený strom z čeledi mořenovitých (<em>Rubiaceae</em>), rodu <em>Mitragyna</em>. Druh poprvé popsal nizozemský botanik <strong>Pieter Willem Korthals</strong> v roce 1839 na základě herbářového materiálu sebraného během expedice po Jávě a Sumatře. Lidově je rostlina v <a href="/pruvodce/botanika-a-veda/co-je-kratom">jihovýchodní Asii</a> známa jako kratom, ketum (Malajsie), biak-biak nebo thom (Thajsko).</p>

<h2>Taxonomie a zařazení</h2>
<p>Systematické zařazení druhu:</p>
<ul>
    <li>Říše: <em>Plantae</em> (rostliny)</li>
    <li>Oddělení: <em>Magnoliophyta</em> (krytosemenné)</li>
    <li>Třída: <em>Magnoliopsida</em> (vyšší dvouděložné)</li>
    <li>Řád: <em>Gentianales</em> (hořcotvaré)</li>
    <li>Čeleď: <em>Rubiaceae</em> (mořenovité)</li>
    <li>Rod: <em>Mitragyna</em></li>
    <li>Druh: <em>M. speciosa</em></li>
</ul>
<p>Rod <em>Mitragyna</em> zahrnuje přibližně 7–10 druhů rozšířených v tropech Asie a Afriky. Pouze <em>M. speciosa</em> je významným zdrojem indolových alkaloidů typu mitragyninu; ostatní druhy (např. <em>M. parvifolia</em> v Indii nebo <em>M. inermis</em> v Africe) mají odlišné fytochemické profily.</p>
<p>Z hlediska poddruhů se v komerční literatuře někdy uvádí dělení podle původu (např. „borneo", „bali", „malay"), z botanického pohledu se však jedná o ekotypy téhož druhu, nikoliv o formálně popsané subspecies.</p>

<h2>Morfologie</h2>
<h3>Kořen a kmen</h3>
<p>Strom vytváří hluboký kůlový kořen s rozsáhlou postranní soustavou, která umožňuje přežívat období sucha. Kmen je rovný, válcovitý, dosahuje průměru 50–90 cm u dospělých jedinců. Borka je šedohnědá, hladká u mladých stromů, později podélně rozpukaná.</p>
<h3>List</h3>
<p>Listy jsou jednoduché, vstřícné, krátce řapíkaté, eliptické až vejčité. Velikost zralého listu se pohybuje mezi 14 a 20 cm na délku a 7–12 cm na šířku. Žilnatina je zpeřená, výrazná střední žilka prosvítá na rubu listu. Barva střední žilky se mění s věkem listu (od bělavě zelené přes sytě zelenou po načervenalou) a je základem komerčního dělení suroviny.</p>
<h3>Květ</h3>
<p>Květenství tvoří kulovité hlavičky o průměru 2–4 cm, sestavené z desítek drobných žlutých až žlutozelených pětičetných květů. Tato stavba je charakteristická pro celou čeleď <em>Rubiaceae</em>. Květy jsou opylovány hmyzem, plodem je drobná tobolka s několika semeny.</p>

<h2>Růstové podmínky</h2>
<p>Druh vyžaduje stabilní tropické klima:</p>
<ul>
    <li>Průměrná roční teplota: 24–28 °C</li>
    <li>Roční úhrn srážek: nad 1500 mm s rovnoměrným rozložením</li>
    <li>Vzdušná vlhkost: trvale nad 70 %</li>
    <li>Půda: hluboká, propustná, mírně kyselá (pH 5,5–6,5), bohatá na humus</li>
    <li>Nadmořská výška: 0–800 m n. m.</li>
</ul>
<p>Strom je citlivý na trvale podmáčené stanoviště (hnijí kořeny) a na mráz, který v Evropě prakticky vylučuje pěstování ve volné půdě.</p>

<h2>Pěstování versus divoký výskyt</h2>
<p>V <a href="/pruvodce/botanika-a-veda/kde-roste-kratom">přirozeném areálu</a> roste <em>Mitragyna speciosa</em> jak planě (v nížinných lesích kolem řek), tak v polokulturní formě na vesnických pozemcích. Komerční plantáže existují především v Indonésii (zejména na Borneu), kde se sazenice množí semeny nebo řízky. Plantáž začíná dávat využitelnou listovou hmotu po 2–3 letech, plnou produktivity dosahuje po 5–7 letech a dále plodí desítky let.</p>

<h2>Často kladené otázky</h2>
<h3>Kdo rostlinu poprvé popsal?</h3>
<p>Pieter Willem Korthals, nizozemský botanik, v roce 1839 v práci <em>Observationes de Naucleis Indicis</em>. Druhové jméno <em>speciosa</em> znamená „nápadná" nebo „výrazná".</p>
<h3>Kolik se rozlišuje subspecies?</h3>
<p>Formální subspecies nejsou taxonomicky popsány. Komerční názvy jako „borneo" nebo „bali" označují regionální ekotypy téhož druhu.</p>
<h3>Lze pěstovat v Evropě?</h3>
<p>Ve volné půdě nikoliv (mráz). Ve sklenících botanických zahrad ano, ale květu a produkce listové hmoty se zpravidla nedosahuje.</p>
<h3>Liší se botanicky od kávovníku?</h3>
<p>Oba druhy patří do čeledi <em>Rubiaceae</em>, ale do jiných rodů a tribů. Kávovník (<em>Coffea</em>) má jiné květenství (úžlabní svazečky) a jiný alkaloidní profil (kofein vs. mitragynin).</p>

<h2>Reference</h2>
<ul>
    <li>Korthals P. W. (1839). <em>Observationes de Naucleis Indicis</em>. Verhandelingen van het Bataviaasch Genootschap van Kunsten en Wetenschappen.</li>
    <li>Sukrong S. et al. (2007). Molecular analysis of the genus <em>Mitragyna</em> existing in Thailand based on rDNA ITS sequences and its application to identify a narcotic species: <em>Mitragyna speciosa</em>. <em>Biological &amp; Pharmaceutical Bulletin</em>, 30(7), 1284–1288.</li>
    <li>Raffa R. B. (Ed.) (2014). <em>Kratom and Other Mitragynines: The Chemistry and Pharmacology of Opioids from a Non-Opium Source</em>. CRC Press.</li>
</ul>
HTML,
        ];
    }

    private function mitragynin(): array
    {
        return [
            'slug' => 'mitragynin',
            'title' => 'Mitragynin: hlavní alkaloid kratomu',
            'excerpt' => 'Mitragynin je indolový alkaloid se sumárním vzorcem C23H30N2O4, tvořící 60–66 % alkaloidního obsahu kratomu. Vědecký popis struktury a biosyntézy.',
            'seo_keyword' => 'mitragynin',
            'seo_secondary_keywords' => ['mitragynin struktura', 'mitragynin účinek', 'mitragynin obsah'],
            'seo_meta_title' => 'Mitragynin — struktura, biosyntéza a obsah v listu | Vivadzen Průvodce',
            'seo_meta_description' => 'Mitragynin: hlavní indolový alkaloid kratomu (C23H30N2O4). Chemická struktura, biosyntéza v rostlině, mechanismus účinku a obsah v listu.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Mitragynin</strong> je indolový alkaloid odvozený od korynantheinového skeletu, který se vyskytuje v listech stromu <em>Mitragyna speciosa</em>. Jde o kvantitativně dominantní složku rostliny — tvoří typicky <strong>60–66 % celkového obsahu alkaloidů</strong> v suchém listu. Poprvé byl izolován v roce 1921 britským chemikem Edmundem Fieldem v Royal Botanic Gardens, Kew.</p>

<h2>Chemická struktura</h2>
<p>Sumární vzorec: <strong>C<sub>23</sub>H<sub>30</sub>N<sub>2</sub>O<sub>4</sub></strong>, molární hmotnost 398,5 g/mol. Strukturně se jedná o tetracyklický indolový alkaloid s konfigurací <em>9-methoxy-corynantheidol</em>. Klíčové strukturní prvky:</p>
<ul>
    <li>indolové jádro (benzen kondenzovaný s pyrrolem),</li>
    <li>kondenzovaný piperidinový a tetrahydropyranový kruh,</li>
    <li>methoxyskupina na uhlíku C9 (na rozdíl od příbuzných alkaloidů yohimbinového typu),</li>
    <li>esterová skupina (metylester karboxylové kyseliny) v poloze C16.</li>
</ul>
<p>V rostlině se vyskytuje jako jediný stereoizomer (přírodní (−)-mitragynin). Syntetické racemáty mají odlišné fyzikální vlastnosti.</p>

<h2>Biosyntéza v rostlině</h2>
<p>Biosyntéza vychází z aminokyseliny <strong>tryptofan</strong>, která je dekarboxylována na tryptamin. Ten kondenzuje se sekologaninem (terpenoidním aldehydem odvozeným od geraniolu) za vzniku strictosidinu — společného prekurzoru desítek indolových alkaloidů u <em>Rubiaceae</em> a <em>Apocynaceae</em>. Z strictosidinu vede řada enzymatických kroků (cytochrom P450, methyltransferázy) k mitragyninu.</p>
<p>Tato biosyntetická dráha je sdílená s alkaloidy jako je <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">yohimbin nebo ajmalicin</a>, což vysvětluje strukturní podobnosti.</p>

<h2>Mechanismus na molekulární úrovni</h2>
<p>Vědecká literatura popisuje mitragynin jako <strong>parciálního agonistu μ-opioidního receptoru (MOR)</strong> s odlišnou signalizací než klasické opioidy: preferenčně aktivuje G-proteinovou dráhu a v menší míře dráhu β-arrestinovou. Mitragynin dále vykazuje afinitu k δ- a κ-opioidním receptorům a k adrenergním α2-receptorům.</p>
<p>Zdůrazněme, že tento popis odpovídá výsledkům in vitro a preklinických studií a slouží výhradně k pochopení farmakologického profilu sloučeniny — nikoliv jako jakékoliv doporučení k užívání.</p>

<h2>Obsah v listu podle původu a barvy žíly</h2>
<p>Obsah mitragyninu v suchém listu kolísá podle původu, stáří listu a způsobu zpracování:</p>
<ul>
    <li>Indonésie (Borneo, Sumatra): 0,8–1,5 % hmotnosti (z toho mitragynin 60–66 %).</li>
    <li>Malajsie: 0,5–1,1 %.</li>
    <li>Thajsko (historická data): 0,3–1,0 %.</li>
</ul>
<p>Podle <a href="/pruvodce/botanika-a-veda/barvy-zil-kratomu">barvy žíly</a> bývá zjednodušeně uváděn vyšší obsah mitragyninu u „bílých" a „zelených" listů a relativní posun směrem k 7-hydroxymitragyninu u „červených" fermentovaných listů. Variabilita mezi vzorky téhož označení je však značná.</p>

<h2>Vztah k 7-hydroxymitragyninu</h2>
<p>Mitragynin podléhá oxidaci, jejímž produktem je <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragynin</a> — strukturní analog s hydroxylovou skupinou na uhlíku C7. Tento metabolit má v in vitro testech několikanásobně vyšší afinitu k MOR než mateřská sloučenina, přestože jeho obsah v listu nepřesahuje 2 % alkaloidního profilu.</p>

<h2>Často kladené otázky</h2>
<h3>Jak se mitragynin liší od kofeinu?</h3>
<p>Strukturně jde o zcela odlišné třídy: kofein je purinový alkaloid (xanthin), mitragynin je indolový alkaloid odvozený od tryptaminu. Liší se i cílovými receptory — kofein působí na adenosinové receptory, mitragynin převážně na opioidní.</p>
<h3>Jaký je biologický poločas mitragyninu?</h3>
<p>Studie u lidí (Trakulsrichai 2015) uvádějí poločas eliminace přibližně <strong>9–24 hodin</strong>, s velkou interindividuální variabilitou danou metabolickými enzymy CYP3A4 a CYP2D6.</p>
<h3>Kdy byl mitragynin poprvé izolován?</h3>
<p>V roce 1921 Edmundem Fieldem v Royal Botanic Gardens, Kew (Londýn). Strukturu definitivně potvrdili Zacharias et al. v roce 1965.</p>
<h3>Je mitragynin v ČR regulován?</h3>
<p>Ano. Od roku 2026 spadá kratom a jeho hlavní alkaloidy pod kategorii <a href="/pruvodce/legislativa-cr/psychomodulacni-latky">psychomodulačních látek (PML)</a> podle novely zákona 167/1998 Sb.</p>

<h2>Reference</h2>
<ul>
    <li>Field E. (1921). Mitragynine and mitraversine, two new alkaloids from species of Mitragyne. <em>Journal of the Chemical Society, Transactions</em>, 119, 887–891.</li>
    <li>Kruegel A. C., Grundmann O. (2018). The medicinal chemistry and neuropharmacology of kratom: A preliminary discussion of a promising medicinal plant and analysis of its potential for abuse. <em>Neuropharmacology</em>, 134, 108–120.</li>
    <li>Trakulsrichai S. et al. (2015). Pharmacokinetics of mitragynine in man. <em>Drug Design, Development and Therapy</em>, 9, 2421–2429.</li>
</ul>
HTML,
        ];
    }

    private function sedmHydroxymitragynin(): array
    {
        return [
            'slug' => '7-hydroxymitragynin',
            'title' => '7-hydroxymitragynin: stopový alkaloid kratomu',
            'excerpt' => '7-hydroxymitragynin (7-OH-MG) je oxidační derivát mitragyninu, který v listu tvoří pod 2 % alkaloidního profilu. Vědecký pohled na strukturu, vznik a detekci.',
            'seo_keyword' => '7-hydroxymitragynin',
            'seo_secondary_keywords' => ['7-OH-mitragynin', '7-hydroxymitragynin účinek', '7-OH MG'],
            'seo_meta_title' => '7-hydroxymitragynin — struktura, vznik a detekce | Vivadzen Průvodce',
            'seo_meta_description' => '7-hydroxymitragynin (7-OH-MG): stopový alkaloid kratomu. Vztah k mitragyninu, oxidační vznik, afinita k receptorům, analytická detekce metodou HPLC-MS.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>7-hydroxymitragynin</strong> (zkracovaný jako 7-OH-MG nebo 7-OH) je indolový alkaloid kratomu strukturně odvozený od <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a> hydroxylací na uhlíku C7. V suchém listu se vyskytuje ve stopovém množství — typicky pod <strong>2 % celkového alkaloidního obsahu</strong>, což odpovídá řádově desítkám až stovkám miligramů na kilogram surového prášku.</p>
<p>Přesto je 7-OH-MG ve vědecké literatuře označován za farmakologicky významnou složku kratomu kvůli své vysoké afinitě k μ-opioidnímu receptoru, mnohonásobně převyšující afinitu mateřského mitragyninu.</p>

<h2>Vztah k mitragyninu</h2>
<p>7-OH-MG vzniká oxidací mitragyninu — buď enzymaticky v rostlině (přítomnost cytochromu P450 v listech), nebo neenzymaticky během sušení a skladování suroviny, zejména při kontaktu s atmosférickým kyslíkem a UV zářením. V čerstvém listu bývá poměr 7-OH-MG/mitragynin nižší než ve starším nebo nesprávně skladovaném prášku.</p>
<p>Strukturně se obě sloučeniny liší pouze přítomností hydroxylové skupiny na uhlíku C7 indolinového jádra (C<sub>23</sub>H<sub>30</sub>N<sub>2</sub>O<sub>5</sub>, molární hmotnost 414,5 g/mol). Tato drobná modifikace mění konformaci molekuly a její afinitu k cílovému receptoru.</p>

<h2>Vědecký pohled na potenci</h2>
<p>In vitro studie (Váradi et al. 2016) uvádějí, že 7-OH-MG vykazuje vůči μ-opioidnímu receptoru afinitu řádově srovnatelnou s morfinem a 10–17× vyšší než mitragynin. Funkční eseje (cAMP, [<sup>35</sup>S]GTPγS) ukazují, podobně jako u mitragyninu, parciální agonismus s preferencí G-proteinové dráhy oproti dráze β-arrestinové.</p>
<p>Tento popis je čistě vědecký a slouží k pochopení farmakologického profilu — žádná z citovaných studií není terapeutickým doporučením.</p>

<h2>Analytická detekce</h2>
<p>Pro stanovení 7-OH-MG v rostlinném materiálu i biologických matricích se standardně používá:</p>
<ul>
    <li><strong>HPLC-MS/MS</strong> (kapalinová chromatografie s tandemovou hmotnostní detekcí) — referenční metoda v laboratořích pro <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">stanovení alkaloidního profilu kratomu</a>,</li>
    <li>UHPLC-DAD pro screening,</li>
    <li>GC-MS po derivatizaci (méně časté kvůli termolabilitě).</li>
</ul>
<p>Typické meze stanovitelnosti (LOQ) jsou v jednotkách ng/ml v plazmě a v jednotkách μg/g v rostlinném materiálu. Validovaná metoda je popsána např. v Lu et al. (2009).</p>

<h2>Často kladené otázky</h2>
<h3>Vzniká 7-OH-MG během skladování?</h3>
<p>Ano. Mitragynin je za přístupu kyslíku a světla pomalu oxidován na 7-OH-MG. Z tohoto důvodu se doporučuje <a href="/pruvodce/kvalita-a-bezpecnost/skladovani-kratomu">skladování v neprůhledných obalech</a> bez přístupu vzduchu.</p>
<h3>V jakém poměru bývá k mitragyninu?</h3>
<p>V kvalitně sušeném listu typicky 0,1–2 % alkaloidního profilu, zatímco mitragynin tvoří 60–66 %. V přepočtu na hmotnost listu jde o nízké jednotky μg/g.</p>
<h3>Proč je významný i ve stopovém množství?</h3>
<p>Díky řádově vyšší afinitě k μ-opioidnímu receptoru se může i nízká koncentrace podílet na celkovém farmakologickém profilu kratomu. Toto je opakovaný motiv vědeckých prací o kratomu (Kruegel &amp; Grundmann 2018).</p>
<h3>Lze 7-OH-MG odlišit od mitragyninu vizuálně?</h3>
<p>Nikoliv. Obě sloučeniny jsou pevné krystalické látky podobné barvy. Spolehlivá identifikace vyžaduje hmotnostní spektrometrii nebo NMR.</p>

<h2>Reference</h2>
<ul>
    <li>Takayama H. (2004). Chemistry and pharmacology of analgesic indole alkaloids from the Rubiaceous plant, <em>Mitragyna speciosa</em>. <em>Chemical &amp; Pharmaceutical Bulletin</em>, 52(8), 916–928.</li>
    <li>Váradi A. et al. (2016). Mitragynine/Corynantheidine Pseudoindoxyls As Opioid Analgesics with Mu Agonism and Delta Antagonism, Which Do Not Recruit β-Arrestin-2. <em>Journal of Medicinal Chemistry</em>, 59(18), 8381–8397.</li>
    <li>Lu S. et al. (2009). Detection of mitragynine and its 9-hydroxy metabolite in human urine. <em>Journal of Analytical Toxicology</em>, 33(8), 418–422.</li>
</ul>
HTML,
        ];
    }

    private function alkaloidyKratomu(): array
    {
        return [
            'slug' => 'alkaloidy-kratomu',
            'title' => 'Alkaloidní profil kratomu: přes 40 sloučenin',
            'excerpt' => 'Kratom obsahuje více než 40 identifikovaných alkaloidů. Dominantní mitragynin a 7-OH-MG, dále speciogynin, paynantheine, mitraphylline a další.',
            'seo_keyword' => 'alkaloidy kratomu',
            'seo_secondary_keywords' => ['kratom alkaloidy', 'složení kratomu', 'kratom látky'],
            'seo_meta_title' => 'Alkaloidní profil kratomu — přehled 40+ sloučenin | Vivadzen Průvodce',
            'seo_meta_description' => 'Alkaloidy kratomu: přehled hlavních skupin (mitraginiové, oxindolové, paynantheinové), variabilita podle původu a význam pro analytiku.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Alkaloidy kratomu</strong> tvoří skupinu více než 40 identifikovaných dusíkatých sekundárních metabolitů, které se hromadí v listech stromu <em>Mitragyna speciosa</em>. Celkový obsah alkaloidů v suchém listu se pohybuje mezi 0,5 a 1,8 % hmotnosti; zbylých více než 98 % tvoří celulóza, hemicelulózy, lignin, voda, flavonoidy, polyfenoly a další necílové složky.</p>

<h2>Co jsou alkaloidy</h2>
<p>Alkaloidy jsou v rostlinné biochemii definovány jako přírodní, nízkomolekulární, dusíkaté heterocyklické sloučeniny biosyntetizované z aminokyselin. Plní v rostlině obrannou funkci proti býložravcům a patogenům. Z lékařského a farmakologického pohledu jsou významnou skupinou bioaktivních látek (od kofeinu přes morfin po chinin).</p>
<p>Alkaloidy kratomu patří mezi <strong>indolové alkaloidy</strong> odvozené od aminokyseliny tryptofanu — sdílejí tedy biosyntetický původ s alkaloidy z rostlin čeledí <em>Apocynaceae</em>, <em>Rubiaceae</em> a <em>Loganiaceae</em> (yohimbin, ajmalicin, vincristin, reserpin).</p>

<h2>Hlavní skupiny v kratomu</h2>
<h3>Mitraginiové alkaloidy (skelet korynantheinu)</h3>
<p>Jádro této skupiny tvoří <a href="/pruvodce/botanika-a-veda/mitragynin">mitragynin</a> (60–66 % obsahu), dále speciogynin (≈ 7 %), paynantheine (8–9 %) a speciociliatine (≈ 1 %). Strukturně se liší konfigurací piperidinového kruhu a polohou methoxy/methylesterových skupin.</p>
<h3>Oxindolové alkaloidy</h3>
<p>Vznikají oxidací indolového jádra na oxindol (laktamovou formu). Patří sem mitraphylline (analog uncarinu z <em>Uncaria tomentosa</em>), isomitraphylline, speciophylline. Obsah typicky pod 1 % alkaloidního profilu.</p>
<h3>Hydroxylované deriváty</h3>
<p>Klíčový je <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragynin</a>, který přes nízký obsah (0,1–2 %) přispívá k farmakologickému profilu kvůli vysoké receptorové afinitě.</p>
<h3>Další stopové alkaloidy</h3>
<p>Corynantheidine, isocorynantheidine, mitragynaline, mitragynalinic acid a desítky dalších, zpravidla v koncentracích pod 0,5 % alkaloidního profilu. Některé jsou specifické pro určité ekotypy.</p>

<h2>Variabilita podle původu</h2>
<p>Poměry alkaloidů kolísají v závislosti na řadě faktorů:</p>
<ul>
    <li><strong>Geografický původ:</strong> indonéské listy mívají vyšší celkový obsah alkaloidů než malajsijské nebo thajské.</li>
    <li><strong>Stáří listu:</strong> mladé listy obsahují relativně méně mitragyninu a více paynantheinu.</li>
    <li><strong>Sušení a <a href="/pruvodce/botanika-a-veda/fermentace-kratomu">fermentace</a>:</strong> oxidační procesy mění poměr mitragynin/7-OH-MG.</li>
    <li><strong>Sezónnost:</strong> období dešťů vs. sucha mírně mění obsah jednotlivých alkaloidů.</li>
</ul>
<p>Z tohoto důvodu nelze udávat jediný „typický" alkaloidní profil — každý vzorek je nutné stanovit analyticky (HPLC nebo LC-MS).</p>

<h2>Význam pro analytiku</h2>
<p>Z laboratorního hlediska je standardem stanovení tří markerů:</p>
<ul>
    <li>mitragynin (kvantitativní indikátor obsahu),</li>
    <li>7-hydroxymitragynin (indikátor oxidačního stáří suroviny),</li>
    <li>celkový alkaloidní obsah (sumární gravimetrické nebo HPLC stanovení).</li>
</ul>
<p>Tyto tři údaje jsou typicky uváděny v <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">Certificate of Analysis (COA)</a> a slouží jako prvotní indikátor kvality vzorku.</p>

<h2>Často kladené otázky</h2>
<h3>Kolik alkaloidů bylo v kratomu identifikováno?</h3>
<p>Přes 40 různých sloučenin; nejnovější přehled (Brown 2017) uvádí 54 strukturně charakterizovaných alkaloidů. Většina z nich je přítomna ve stopovém množství pod 1 % alkaloidního obsahu.</p>
<h3>Proč jsou některé alkaloidy jen ve stopách?</h3>
<p>Jsou meziprodukty biosyntézy nebo oxidační deriváty hlavních alkaloidů. Jejich akumulace je rostlinou regulována podle enzymatické rovnováhy.</p>
<h3>Mění se alkaloidní profil sušením?</h3>
<p>Ano. Oxidační procesy zvyšují podíl 7-OH-MG na úkor mitragyninu. Nesprávně skladovaná surovina může mít zcela posunutý profil.</p>
<h3>Existuje „standardní" alkaloidní profil kratomu?</h3>
<p>Nikoliv. Komise pro fytoanalýzu vyžadují stanovení profilu pro konkrétní šarži, ne odkaz na obecnou hodnotu.</p>

<h2>Reference</h2>
<ul>
    <li>Brown P. N., Lund J. A., Murch S. J. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em>. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
    <li>León F. et al. (2009). Phytochemical characterization of the leaves of <em>Mitragyna speciosa</em>. <em>Phytochemistry Letters</em>, 2(2), 71–76.</li>
    <li>Flores-Bocanegra L. et al. (2020). The chemistry of kratom: alkaloids and analogues. <em>Journal of Natural Products</em>, 83(7), 2165–2177.</li>
</ul>
HTML,
        ];
    }

    private function kratomRostlina(): array
    {
        return [
            'slug' => 'kratom-rostlina-zivotni-cyklus',
            'title' => 'Kratom jako rostlina: životní cyklus a morfologie',
            'excerpt' => 'Mitragyna speciosa je tropický strom s kontinuální vegetací, kvete žlutými hlavičkami a dospělosti dosahuje po 5–7 letech. Životní cyklus a morfologie.',
            'seo_keyword' => 'kratom rostlina',
            'seo_secondary_keywords' => ['kratom strom', 'kratom morfologie', 'kratom květ'],
            'seo_meta_title' => 'Kratom jako rostlina — životní cyklus a morfologie | Vivadzen Průvodce',
            'seo_meta_description' => 'Životní cyklus stromu Mitragyna speciosa: klíčení, vegetační období v tropech, květenství, stárnutí a délka produktivity.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p>Z botanického pohledu je <em>Mitragyna speciosa</em> dlouhověký tropický strom s <strong>kontinuální vegetací</strong>, tedy bez výrazného období vegetačního klidu. V přirozeném prostředí jihovýchodní Asie strom roste, kvete i tvoří plody prakticky po celý rok, přičemž intenzita jednotlivých fází se mění podle ročního období dešťů.</p>

<h2>Klíčení a růst</h2>
<p>Semena <em>M. speciosa</em> jsou drobná (přibližně 1 mm), s krátkou klíčivostí — řádově dny až týdny po sběru. Z tohoto důvodu se v praxi často množí řízky nebo vegetativně. Klíčení probíhá při teplotách 25–30 °C v rašelinno-pískovém substrátu při vysoké vzdušné vlhkosti.</p>
<p>Mladý strom přirůstá v ideálních podmínkách 60–150 cm ročně. Po 2–3 letech dosahuje výšky 3–4 m a začíná tvořit listovou hmotu vhodnou pro experimentální sběr. Plné produktivity (stabilní hmotnost listu, rozvinutá koruna) dosahuje strom mezi 5. a 7. rokem.</p>

<h2>Vegetační období v tropech</h2>
<p>Strom nemá výrazný stav vegetačního klidu, který známe u rostlin mírného pásma. Listy opadávají postupně po dosažení stáří 6–9 měsíců a jsou nahrazovány novými. To znamená, že na jednom stromě jsou zároveň listy v různé fázi zralosti — důležitý faktor při sběru.</p>
<p>V období dešťů (typicky listopad–březen v indonéské části areálu) je strom v intenzivním růstu, v období sušším mírně zpomaluje. Sběr listů se přizpůsobuje této dynamice.</p>

<h2>Květenství</h2>
<p>Květenství <em>M. speciosa</em> tvoří kulovité žluté až žlutozelené hlavičky o průměru 2–4 cm. Tato stavba — desítky drobných pětičetných květů sestavených do kuželovité hlávky — je typická pro rod <em>Mitragyna</em> a pomáhá při botanické identifikaci, zvláště při odlišení od příbuzných rodů.</p>
<p>Květenství je opylováno hmyzem (převážně drobnými blanokřídlými). Plodem je nepatrná tobolka s množstvím semen rozšiřovaných větrem. V plantážních podmínkách je sběr semen sezónně koordinován s ošetřením matečných stromů.</p>

<h2>Stárnutí a délka produktivity</h2>
<p>Plně dospělý strom dosahuje výšky 10–16 m, výjimečně až 25 m. Životnost stromu v přirozeném prostředí přesahuje 50 let, v plantážních podmínkách obvykle 30–40 let, kdy je nahrazen novým výsadcem kvůli klesající kvalitě listu i obtížnější sklizni.</p>
<p>S věkem stromu se mění poměr alkaloidů — starší stromy mívají stabilnější obsah <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a>, ale celkové množství listové hmoty roste pomaleji než u mladších stromů.</p>

<h2>Často kladené otázky</h2>
<h3>Kolik let strom plodí list použitelný pro sušení?</h3>
<p>Od 2.–3. roku do 30.–40. roku, v přirozeném prostředí i déle. Plné produktivity dosahuje mezi 5. a 7. rokem.</p>
<h3>Kvete kratom v Evropě?</h3>
<p>Ve volné půdě nikoliv. Ve sklenících botanických zahrad výjimečně ano, ale plodů se zpravidla nedosáhne kvůli absenci přirozených opylovačů.</p>
<h3>Jak se liší mladý strom od zralého?</h3>
<p>Mladý strom má relativně vyšší podíl paynantheinu a nižší podíl mitragyninu. Listová žilkatura je méně výrazná a list je tenčí.</p>
<h3>Jaký je rozdíl mezi <em>Mitragyna speciosa</em> a <em>Mitragyna parvifolia</em>?</h3>
<p>Oba druhy patří do stejného rodu, ale liší se areálem (parvifolia v Indii) i alkaloidním profilem — <em>M. parvifolia</em> neobsahuje mitragynin v komerčně relevantním množství.</p>

<h2>Reference</h2>
<ul>
    <li>Sukrong S. et al. (2007). Molecular analysis of the genus <em>Mitragyna</em>. <em>Biological &amp; Pharmaceutical Bulletin</em>, 30(7), 1284–1288.</li>
    <li>Raffa R. B. (Ed.) (2014). <em>Kratom and Other Mitragynines</em>. CRC Press.</li>
    <li>Brown P. N. et al. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em>. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
</ul>
HTML,
        ];
    }

    private function kdeRosteKratom(): array
    {
        return [
            'slug' => 'kde-roste-kratom',
            'title' => 'Přirozený areál Mitragyna speciosa',
            'excerpt' => 'Přirozený areál Mitragyna speciosa zahrnuje Indonésii (Borneo, Sumatra, Jáva), Malajsii, jižní Thajsko, Myanmar a část Papuy Nové Guineje.',
            'seo_keyword' => 'kde roste kratom',
            'seo_secondary_keywords' => ['kratom původ', 'kratom geografie', 'kratom areál'],
            'seo_meta_title' => 'Kde roste kratom — přirozený areál Mitragyna speciosa | Vivadzen Průvodce',
            'seo_meta_description' => 'Mapa přirozeného výskytu kratomu: Indonésie, Malajsie, Thajsko, Myanmar a Papua-Nová Guinea. Klimatické a půdní požadavky, důvody dominance Indonésie.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p>Přirozený areál <em>Mitragyna speciosa</em> zahrnuje tropické nížiny <strong>jihovýchodní Asie</strong> v pásu mezi 10° severní a 5° jižní zeměpisné šířky. Strom se zde vyskytuje jak planě, tak v polokulturní formě na vesnických pozemcích a na komerčních plantážích. Komerční produkce listu se dnes z drtivé většiny soustředí v Indonésii.</p>

<h2>Hlavní oblasti</h2>
<h3>Indonésie</h3>
<p>Dominantní producent kratomu na světě (odhad 70–90 % světové produkce). Klíčové oblasti:</p>
<ul>
    <li><strong>Borneo</strong> (provincie Západní Kalimantan, Východní Kalimantan) — nejvýznamnější produkční region;</li>
    <li><strong>Sumatra</strong> (Aceh, Severní Sumatra) — historicky druhý nejvýznamnější region;</li>
    <li><strong>Jáva</strong> — především v polokulturních porostech, menší produkce.</li>
</ul>
<p>Indonéský zákon zatím (k roku 2026) povoluje sběr i vývoz kratomu, byť pod regulací ministerstva zemědělství. Více v článku <a href="/pruvodce/historie-a-kultura/kratom-indonesie">Indonésie jako světový dodavatel kratomu</a>.</p>
<h3>Malajsie</h3>
<p>Strom se přirozeně vyskytuje na poloostrově Malajsie i na malajsijské části Bornea (Sabah, Sarawak). Pěstování a sběr jsou však místně regulovány — Malajsie zařadila kratom (lokálně známý jako <em>ketum</em>) na seznam kontrolovaných látek podle Poisons Act 1952.</p>
<h3>Thajsko</h3>
<p>Strom je domácí v jižních provinciích Thajska (Surat Thani, Nakhon Si Thammarat, Songkhla). Po desetiletích zákazu (1943–2021) byl kratom v Thajsku legalizován v roce 2021 a pěstování se vrací do tradičních oblastí.</p>
<h3>Myanmar a další</h3>
<p>Strom se vyskytuje i v jižním Myanmaru, na Filipínách (ostrov Mindanao) a v okrajových oblastech Papuy Nové Guineje, kde však komerční produkce neexistuje.</p>

<h2>Klimatické a půdní požadavky</h2>
<p>Pro přirozený výskyt i komerční produkci jsou rozhodující:</p>
<ul>
    <li>průměrná roční teplota 24–28 °C,</li>
    <li>roční úhrn srážek nad 1500 mm,</li>
    <li>vzdušná vlhkost trvale nad 70 %,</li>
    <li>hluboké, propustné, mírně kyselé půdy (pH 5,5–6,5),</li>
    <li>nadmořská výška 0–800 m.</li>
</ul>
<p>Tato kombinace je omezena na rovníkové tropy s monzunovým klimatem. Mimo tento pás strom přežívá pouze v ochraně skleníků.</p>

<h2>Mapa produkce</h2>
<p>Komerční produkce listu se k roku 2025 koncentruje v této přibližné struktuře:</p>
<ul>
    <li>Indonésie — 75–85 % světové produkce (zejména Západní Kalimantan, Aceh);</li>
    <li>Malajsie — 5–10 % (převážně domácí spotřeba);</li>
    <li>Thajsko — 5–10 % (rychle rostoucí po legalizaci 2021);</li>
    <li>ostatní (Myanmar, Filipíny) — pod 5 %, převážně necertifikováno.</li>
</ul>

<h2>Důvody dominance Indonésie</h2>
<p>Tři hlavní faktory:</p>
<ol>
    <li><strong>Legislativní volnost</strong> — Indonésie kratom dosud (2026) neregulovala jako narkotikum. Plánovaná regulace je v jednání, ale termín se opakovaně odkládá.</li>
    <li><strong>Ekonomika malých farmářů</strong> — sběr listu v Kalimantanu poskytuje příjem statisícům rodin a politicky je tlak na legalitu produkce.</li>
    <li><strong>Optimální klima</strong> — Borneo nabízí nejstabilnější tropické klima v regionu, s minimem klimatických extrémů.</li>
</ol>

<h2>Často kladené otázky</h2>
<h3>Roste kratom planě, nebo se pěstuje?</h3>
<p>Oboje. V přirozeném areálu existuje plané rozšíření v nížinných lesích kolem řek, ale komerční produkce se z 90 % opírá o plantáže nebo polokulturní porosty.</p>
<h3>V kterých zemích je pěstování zakázáno?</h3>
<p>V Thajsku byl pěstování zakázán v letech 1943–2021. Malajsie reguluje sběr přes Poisons Act. Většina ostatních zemí v areálu (Indonésie, Myanmar) pěstování dosud nezakazuje.</p>
<h3>Pěstuje se kratom i mimo jihovýchodní Asii?</h3>
<p>Experimentálně v některých tropických oblastech Latinské Ameriky (Kostarika, Kolumbie), ale bez komerčně významného objemu.</p>
<h3>Lze pěstovat v Číně nebo Africe?</h3>
<p>V jižní Číně (Yunnan, Hainan) okrajově ano, ale klimaticky není ideální. V Africe se příbuzný druh <em>M. inermis</em> vyskytuje, ale neobsahuje mitragynin v komerčně významném množství.</p>

<h2>Reference</h2>
<ul>
    <li>FAO (2019). Non-Wood Forest Products in Southeast Asia: Country Reports. Food and Agriculture Organization of the United Nations.</li>
    <li>Cinosi E. et al. (2015). Following "the Roots" of Kratom. <em>BioMed Research International</em>, 2015, 968786.</li>
    <li>Singh D. et al. (2016). Traditional and non-traditional uses of <em>Mitragyna speciosa</em> in Malaysia and Thailand. <em>Journal of Ethnopharmacology</em>, 192, 24–34.</li>
</ul>
HTML,
        ];
    }

    private function barvyZilKratomu(): array
    {
        return [
            'slug' => 'barvy-zil-kratomu',
            'title' => 'Barvy žil kratomu: botanický a chemický základ',
            'excerpt' => 'Zelená, bílá a červená barva střední žilky listu Mitragyna speciosa odpovídá fázi růstu a způsobu zpracování. Botanické a chemické pozadí.',
            'seo_keyword' => 'barvy kratomu rozdíly',
            'seo_secondary_keywords' => ['bílý kratom', 'zelený kratom', 'červený kratom rozdíl'],
            'seo_meta_title' => 'Barvy žil kratomu — botanický a chemický základ rozdílů | Vivadzen Průvodce',
            'seo_meta_description' => 'Co znamená bílý, zelený a červený kratom: botanický pohled na barvy žil listu, vliv fáze růstu a sušení na poměr alkaloidů.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p>V komerční praxi se kratomový prášek označuje podle barvy <strong>střední žilky listu</strong> — bílá, zelená nebo červená. Toto označení nepředstavuje botanicky odlišné odrůdy, ale především <strong>fázi růstu listu</strong> v okamžiku sběru a navazující způsob sušení či <a href="/pruvodce/botanika-a-veda/fermentace-kratomu">fermentace</a>. Žilka samotná je výrazná podélná cévně-svazkovitá struktura procházející středem listové čepele.</p>

<h2>Co je žilka listu a proč se podle ní rozlišuje typ</h2>
<p>Střední žilka (<em>costa</em>) je hlavním cévním svazkem listu, zajišťuje transport vody a asimilátů. U <em>Mitragyna speciosa</em> má výraznou pigmentaci, která se mění během vývoje listu. Pigmenty zodpovědné za barvu jsou:</p>
<ul>
    <li><strong>chlorofyly</strong> (dominantní u zralého zeleného listu),</li>
    <li><strong>anthokyany</strong> (červené a fialové pigmenty, dominantní u stárnoucích a zralých listů),</li>
    <li><strong>karotenoidy</strong> (žluté, převažují u mladých a stárnoucích listů, kdy mizí chlorofyl).</li>
</ul>
<p>V komerční terminologii je barva žilky vodítkem pro odhad zralosti listu i podmínek zpracování — žádné formální botanické dělení odrůd podle barvy neexistuje.</p>

<h2>Jak vzniká bílá, zelená, červená</h2>
<h3>„Bílá žíla" (white vein)</h3>
<p>Označuje listy sebrané v rané vegetativní fázi, kdy je žilka ještě bledá (převažují karotenoidy nad chlorofylem). Tato surovina je obvykle sušena bez výraznější fermentace, v zastíněném prostoru s minimem UV záření.</p>
<h3>„Zelená žíla" (green vein)</h3>
<p>Listy sebrané ve fázi plné zralosti, kdy je obsah chlorofylu maximální. Standardní sušení v polostínu, s relativně krátkou dobou expozice — proto si surovina zachovává tmavě zelený nádech.</p>
<h3>„Červená žíla" (red vein)</h3>
<p>Listy sebrané v pozdní fázi zralosti, kdy v žilkách roste obsah anthokyanů. Surovina je typicky vystavena delšímu sušení na slunci nebo částečné fermentaci, při níž dochází k oxidaci pigmentů i alkaloidů.</p>

<h2>Co se přitom mění chemicky</h2>
<p>Z chemického hlediska se barva žilky a způsob zpracování projevují <strong>posunem v poměru hlavních alkaloidů</strong>:</p>
<ul>
    <li>„Bílé" a „zelené" listy mívají vyšší relativní obsah <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a>;</li>
    <li>„Červené" listy (zejména fermentované) mívají vyšší podíl <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragyninu</a>, který vzniká oxidací mitragyninu.</li>
</ul>
<p>Tento posun je ale řádově v jednotkách procentních bodů a značně variabilní mezi šaržemi. Spolehlivý profil dává až <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">HPLC analýza</a> konkrétního vzorku.</p>

<h2>Mýty a marketing</h2>
<p>V komerční komunikaci se „barvy" často prezentují jako jasně odlišné produkty s předvídatelným profilem. Vědecká data tento obrázek nepotvrzují:</p>
<ul>
    <li>poměr mitragynin/7-OH-MG kolísá mezi šaržemi téhož „barevného" označení;</li>
    <li>označení „žlutý" nebo „zlatý" kratom je čistě marketingové, žádné botanické pozadí nemá;</li>
    <li>barvu žíly nelze spolehlivě určit z prášku, určuje ji výrobce podle původního listu.</li>
</ul>
<p>Z analytického pohledu má větší vypovídací hodnotu konkrétní COA šarže než její komerční označení.</p>

<h2>Často kladené otázky</h2>
<h3>Je „červená/zelená/bílá" botanický pojem?</h3>
<p>Není. Jde o komerční dělení podle fáze růstu listu a způsobu zpracování. Botanicky se jedná o jednu rostlinu — <em>Mitragyna speciosa</em>.</p>
<h3>Existuje žlutá nebo zlatá žíla?</h3>
<p>Žlutá ani zlatá žíla v přírodě neexistují. Tyto názvy označují směsi nebo zpracování (fermentace zelené suroviny), nemají botanický základ.</p>
<h3>Lze barvu žíly určit z prášku?</h3>
<p>Spolehlivě nikoliv. Po mletí list ztrácí vizuální rozlišovací znaky. Barvu prášku ovlivňuje řada faktorů (oxidace, mletí, vlhkost), nejen typ původní suroviny.</p>
<h3>Liší se barvy zásadně chemicky?</h3>
<p>Liší se relativním poměrem alkaloidů, ale rozdíly jsou v jednotkách procentních bodů. Mezi šaržemi téhož označení bývá variabilita větší než mezi „barvami" obecně.</p>

<h2>Reference</h2>
<ul>
    <li>Singh D. et al. (2016). Traditional and non-traditional uses of <em>Mitragyna speciosa</em>. <em>Journal of Ethnopharmacology</em>, 192, 24–34.</li>
    <li>Sengnon N. et al. (2021). Phytochemical comparison among different colour-vein cultivars of <em>Mitragyna speciosa</em>. <em>Molecules</em>, 26(17), 5141.</li>
    <li>Brown P. N. et al. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em>. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
</ul>
HTML,
        ];
    }

    private function fermentaceKratomu(): array
    {
        return [
            'slug' => 'fermentace-kratomu',
            'title' => 'Fermentace kratomového listu: proces a chemie',
            'excerpt' => 'Fermentace kratomového listu je řízená oxidace, která mění poměr alkaloidů. Etapy procesu, vliv na chemii a variabilita mezi výrobci.',
            'seo_keyword' => 'kratom fermentace',
            'seo_secondary_keywords' => ['fermentace kratomu', 'sušení kratomu', 'kratom oxidace'],
            'seo_meta_title' => 'Fermentace kratomu — proces, etapy a vliv na alkaloidy | Vivadzen Průvodce',
            'seo_meta_description' => 'Fermentace kratomového listu jako řízená oxidace: etapy procesu, vliv na poměr mitragynin/7-OH-MG, variabilita mezi výrobci.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Fermentace kratomového listu</strong> je z technologického pohledu řízená oxidace a enzymatická konverze rostlinné suroviny mezi sběrem a sušením. Cílem je především změnit poměr alkaloidů, druhotně i organoleptické vlastnosti suroviny (barva, vůně). V odborné literatuře se proces někdy popisuje jako „post-harvest processing", protože nejde o klasickou mikrobiální fermentaci, ale o kombinaci enzymatické a oxidační přeměny.</p>

<h2>Co je fermentace listu</h2>
<p>Na rozdíl od kyselé mléčné nebo alkoholové fermentace, kterou zajišťují mikroorganismy, je v případě kratomu fermentace ovlivněna především:</p>
<ul>
    <li>vlastními rostlinnými enzymy (polyfenoloxidázy, peroxidázy),</li>
    <li>vzdušným kyslíkem (chemická oxidace alkaloidů a polyfenolů),</li>
    <li>vlhkostí listu (řídí rychlost enzymatických reakcí),</li>
    <li>teplotou (urychluje oxidaci, ale při překročení 45 °C ničí enzymy).</li>
</ul>
<p>Podobný princip je znám u <strong>fermentace čaje</strong> (přeměna zeleného čaje na černý) — i tam jde primárně o enzymatickou oxidaci, nikoliv o mikrobiální proces.</p>

<h2>Etapy</h2>
<h3>1. Sběr a třídění</h3>
<p>Listy se ručně třídí podle barvy žilky a fáze zralosti. Pro klasický „červený" produkt jsou vybírány zralejší listy s vyšším obsahem anthokyanů.</p>
<h3>2. Vadnutí (withering)</h3>
<p>Listy se nechávají v zastíněném větraném prostoru 6–24 hodin, aby ztratily část vody (z původních ~70 % na ~55 % vlhkosti). V této fázi se aktivují enzymy, ale list ještě nezačíná intenzivně oxidovat.</p>
<h3>3. Vlastní fermentace / oxidace</h3>
<p>Listy jsou uloženy ve vrstvách (5–10 cm) v uzavřených pytlích nebo nádobách na 12–72 hodin. Probíhá enzymatická oxidace polyfenolů a mitragyninu, list mění barvu na tmavší. Teplota se sleduje, aby nepřesáhla 40 °C.</p>
<h3>4. Sušení</h3>
<p>Konečné sušení probíhá na slunci nebo v sušárně při 35–45 °C, dokud obsah vody neklesne pod 10 %. Tím se zastaví enzymatické reakce a stabilizuje se konečný profil. Více v článku <a href="/pruvodce/kvalita-a-bezpecnost/skladovani-kratomu">Skladování kratomového prášku</a>.</p>

<h2>Vliv na poměr alkaloidů</h2>
<p>Klíčový dopad fermentace na chemii kratomu:</p>
<ul>
    <li>Absolutní obsah <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a> klesá o 10–30 % (dochází k jeho oxidaci).</li>
    <li>Obsah <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragyninu</a> roste, někdy 2–5× oproti nefermentovanému listu.</li>
    <li>Mírně se zvyšuje podíl oxindolových alkaloidů (mitraphylline, isomitraphylline).</li>
    <li>Polyfenolický profil se mění — vznikají oxidované deriváty (thearubigininům podobné sloučeniny u čaje).</li>
</ul>
<p>Tento „posun" je hlavním důvodem, proč „červené" varianty bývají popisovány jako relativně silnější — z čistě chemického hlediska to znamená vyšší podíl 7-OH-MG v <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">alkaloidním profilu</a>.</p>

<h2>Variabilita mezi výrobci</h2>
<p>Konkrétní parametry fermentace se mezi výrobci dramaticky liší. Klíčové rozdíly:</p>
<ul>
    <li>doba fermentace (12 vs. 72 hodin),</li>
    <li>teplota a vlhkost prostředí,</li>
    <li>poměr listů v jedné vrstvě (zajistí přístup kyslíku),</li>
    <li>navazující sušení (sluneční vs. ventilátorové).</li>
</ul>
<p>Z tohoto důvodu dvě „červené" suroviny od různých výrobců mohou mít zcela odlišný alkaloidní profil. Spolehlivý údaj poskytne pouze HPLC analýza konkrétní šarže.</p>

<h2>Často kladené otázky</h2>
<h3>Mění fermentace toxicitu suroviny?</h3>
<p>Sama o sobě nikoliv. Fermentační proces nezavádí nové toxické sloučeniny, ale nesprávné podmínky (vysoká vlhkost, dlouhá doba) mohou umožnit růst plísní a vznik <a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a>.</p>
<h3>Lze fermentaci replikovat doma?</h3>
<p>Z technologického pohledu ano, ale bez analytického vybavení nelze ověřit konečný alkaloidní profil ani mikrobiologickou bezpečnost.</p>
<h3>Jak dlouho proces trvá?</h3>
<p>Plná fermentace 12–72 hodin, navazující sušení další 12–48 hodin. Celý cyklus tedy 1–5 dní podle klimatu a metody.</p>
<h3>Liší se fermentace u kratomu a u čaje?</h3>
<p>Princip (enzymatická oxidace) je podobný, ale enzymy a substráty jsou odlišné. Čaj fermentuje katechiny, kratom fermentuje alkaloidy a polyfenoly.</p>

<h2>Reference</h2>
<ul>
    <li>Sengnon N. et al. (2021). Phytochemical comparison among different colour-vein cultivars of <em>Mitragyna speciosa</em>. <em>Molecules</em>, 26(17), 5141.</li>
    <li>Brown P. N. et al. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em>. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
    <li>Singh D. et al. (2016). Traditional and non-traditional uses of <em>Mitragyna speciosa</em>. <em>Journal of Ethnopharmacology</em>, 192, 24–34.</li>
</ul>
HTML,
        ];
    }

    private function meshVelikost(): array
    {
        return [
            'slug' => 'mesh-velikost-kratomu',
            'title' => 'Granulometrie kratomového prášku: mesh a mikrometry',
            'excerpt' => 'Mesh udává hustotu síta v počtu ok na palec. Převod mesh → μm, vliv velikosti částic na rozpustnost a standardy pro rostlinné prášky.',
            'seo_keyword' => 'kratom mesh velikost',
            'seo_secondary_keywords' => ['kratom prášek velikost', 'mesh 80 100 120', 'kratom granulometrie'],
            'seo_meta_title' => 'Mesh velikost kratomu — granulometrie prášku a převod na μm | Vivadzen Průvodce',
            'seo_meta_description' => 'Granulometrie kratomu: co znamená mesh 80, 100, 120, převod na mikrometry a vliv velikosti částic na rozpustnost. Standardy pro rostlinné prášky.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Definice</h2>
<p><strong>Mesh velikost</strong> je v technické praxi měřítko jemnosti prášku, odvozené od hustoty síta. Číslo mesh udává počet ok na lineární palec (1 palec = 25,4 mm) standardizovaného síta — čím vyšší číslo, tím jemnější síto a tím menší částice prášku. Kromě označení mesh se v Evropě běžně používá přímý údaj v <strong>mikrometrech (μm)</strong>.</p>

<h2>Co znamená mesh (US/Tyler stupnice)</h2>
<p>V praxi se používají dvě hlavní stupnice:</p>
<ul>
    <li><strong>US Standard Sieve Series</strong> (ASTM E11) — nejrozšířenější v Severní Americe i pro mezinárodní obchod.</li>
    <li><strong>Tyler Equivalent</strong> — historická britská stupnice, hodnoty velmi blízké US Standard.</li>
</ul>
<p>Obě stupnice udávají počet ok na palec u referenčního síta. Pro praktické účely (specifikace prášků v obchodním styku) jsou zaměnitelné s tolerancí v jednotkách procent.</p>

<h2>Převod mesh → μm</h2>
<p>Orientační převodní tabulka pro stupnici US Standard:</p>
<ul>
    <li>mesh 40 = ~ 420 μm (hrubě mletý prášek)</li>
    <li>mesh 60 = ~ 250 μm</li>
    <li>mesh 80 = ~ 177 μm (středně jemný prášek)</li>
    <li>mesh 100 = ~ 149 μm</li>
    <li>mesh 120 = ~ 125 μm (jemný prášek)</li>
    <li>mesh 140 = ~ 105 μm</li>
    <li>mesh 200 = ~ 74 μm (velmi jemný prášek, mikroprášek)</li>
    <li>mesh 325 = ~ 44 μm (mikronizovaný prášek)</li>
</ul>
<p>Pozn.: údaje udávají horní mez velikosti částic — prášek označený jako „mesh 80" znamená, že prošel sítem 80, tedy částice nejsou větší než ~177 μm. Distribuce velikostí je vždy širší (typicky log-normální).</p>

<h2>Vliv velikosti na rozpustnost a fyziku</h2>
<p>Velikost částic ovlivňuje řadu fyzikálních vlastností prášku:</p>
<ul>
    <li><strong>Měrný povrch:</strong> jemnější prášek má řádově větší specifický povrch, což zrychluje extrakci alkaloidů do rozpouštědla (laboratorní význam pro <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">HPLC analýzu</a>).</li>
    <li><strong>Sypná hustota:</strong> jemnější prášek má často nižší sypnou hustotu (více vzduchu mezi částicemi).</li>
    <li><strong>Tekutost:</strong> nejjemnější prášky (pod 50 μm) mohou tvořit aglomeráty a zhoršovat sypnost.</li>
    <li><strong>Stabilita:</strong> jemnější prášek má větší povrch vystavený kyslíku, což zrychluje oxidaci alkaloidů — proto je <a href="/pruvodce/kvalita-a-bezpecnost/skladovani-kratomu">správné skladování</a> u jemných prášků kritičtější.</li>
</ul>
<p>Tato pozorování jsou čistě fyzikální popis — nejde o doporučení k jakémukoliv užívání.</p>

<h2>Standardy pro rostlinné prášky</h2>
<p>Pro rostlinné prášky existuje několik referenčních standardů popisujících granulometrii:</p>
<ul>
    <li><strong>USP &lt;786&gt;</strong> (United States Pharmacopeia) — metoda stanovení distribuce velikosti částic sítovou analýzou nebo laserovou difrakcí.</li>
    <li><strong>Ph. Eur. 2.9.38</strong> (European Pharmacopoeia) — kompatibilní metodika v EU.</li>
    <li><strong>ASTM E11</strong> — specifikace samotných síte.</li>
</ul>
<p>Tyto standardy nejsou specifické pro kratom, ale obecně platné pro herbální drogy.</p>

<h2>Mletí a klasifikace v praxi</h2>
<p>Mletí rostlinného materiálu se v komerční praxi provádí v několika krocích:</p>
<ol>
    <li>Hrubé drcení sušeného listu (mlýn s noži, výstup ~mesh 20–40).</li>
    <li>Jemné mletí (kladívkový nebo nárazový mlýn, výstup ~mesh 60–120).</li>
    <li>Volitelně mikronizace (proudový mlýn, výstup pod 50 μm).</li>
    <li>Sítová klasifikace — oddělení nadsítné a podsítné frakce.</li>
</ol>
<p>Komerčně nejběžnější jemnosti pro kratomový prášek jsou mesh 80–120 (≈ 125–177 μm). Mikronizovaný prášek (pod 50 μm) je vzácnější a je obvykle vyhrazen pro extrakční technologie.</p>

<h2>Často kladené otázky</h2>
<h3>Co je mesh 80 v mikrometrech?</h3>
<p>Mesh 80 podle US Standard odpovídá síto s velikostí ok přibližně 177 μm. Prášek označený „mesh 80" má částice menší než tato hodnota.</p>
<h3>Je jemnější vždy lepší?</h3>
<p>Z hlediska extrakce ano, z hlediska stability nikoliv — jemnější prášek oxiduje rychleji. Optimum pro běžné použití je mesh 80–120.</p>
<h3>Existuje norma pro rostlinné prášky?</h3>
<p>Ano. USP &lt;786&gt; a Ph. Eur. 2.9.38 popisují metody stanovení distribuce velikosti částic. Specifické limity pro kratom nejsou farmakopeí stanoveny.</p>
<h3>Jak ověřit deklarovanou velikost?</h3>
<p>Sítovou analýzou (nákladnější) nebo laserovou difrakcí v akreditované laboratoři. V <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">COA</a> by mělo být uvedeno, jakou metodou byla velikost stanovena.</p>

<h2>Reference</h2>
<ul>
    <li>USP &lt;786&gt;. Particle Size Distribution Estimation by Analytical Sieving. United States Pharmacopeia.</li>
    <li>Ph. Eur. 2.9.38. Particle-size distribution estimation by analytical sieving. European Pharmacopoeia, 10. vydání.</li>
    <li>ASTM E11-22. Standard Specification for Woven Wire Test Sieve Cloth and Test Sieves. ASTM International.</li>
</ul>
HTML,
        ];
    }
}
