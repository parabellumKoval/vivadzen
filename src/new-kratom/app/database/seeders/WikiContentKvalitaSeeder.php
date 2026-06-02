<?php

namespace Database\Seeders;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

/**
 * Контент wiki, категория «Kvalita a bezpečnost» (7 статей).
 *
 * Technický analytický styl: laboratorní metody, limity podle EU/USP,
 * postupy. Žádné srovnání produktů, žádné komerční formulace.
 */
class WikiContentKvalitaSeeder extends Seeder
{
    public function run(): void
    {
        $category = WikiCategory::where('slug', 'kvalita-a-bezpecnost')->firstOrFail();

        $articles = [
            $this->coaKratomJakCist(),
            $this->tezkeKovyKratom(),
            $this->mykotoxinyKratom(),
            $this->mikrobiologieKratom(),
            $this->hplcIcpMsKratom(),
            $this->skladovaniKratomu(),
            $this->kratomExtraktVsPrasek(),
        ];

        $created = [];
        foreach ($articles as $position => $a) {
            $created[$a['slug']] = WikiArticle::updateOrCreate(
                ['slug' => $a['slug']],
                array_merge($a, [
                    'wiki_category_id' => $category->id,
                    'position' => ($position + 1) * 10,
                    'status' => 'published',
                    'published_at' => now()->subDays(7 - $position),
                ]),
            );
        }

        $links = [
            'coa-kratom-jak-cist' => ['tezke-kovy-kratom', 'mykotoxiny-kratom', 'mikrobiologie-kratom', 'hplc-icp-ms-kratom', 'kratom-zakon-cesko-2026'],
            'tezke-kovy-kratom' => ['coa-kratom-jak-cist', 'hplc-icp-ms-kratom', 'mykotoxiny-kratom'],
            'mykotoxiny-kratom' => ['coa-kratom-jak-cist', 'mikrobiologie-kratom', 'skladovani-kratomu'],
            'mikrobiologie-kratom' => ['coa-kratom-jak-cist', 'mykotoxiny-kratom', 'skladovani-kratomu'],
            'hplc-icp-ms-kratom' => ['mitragynin', '7-hydroxymitragynin', 'tezke-kovy-kratom', 'coa-kratom-jak-cist'],
            'skladovani-kratomu' => ['mesh-velikost-kratomu', 'mykotoxiny-kratom', 'fermentace-kratomu', 'kratom-extrakt-vs-prasek'],
            'kratom-extrakt-vs-prasek' => ['fermentace-kratomu', 'mesh-velikost-kratomu', 'skladovani-kratomu', 'alkaloidy-kratomu'],
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

    private function coaKratomJakCist(): array
    {
        return [
            'slug' => 'coa-kratom-jak-cist',
            'title' => 'Certificate of Analysis (COA) u kratomu: jak číst protokol',
            'excerpt' => 'Certificate of Analysis (COA) je laboratorní protokol jedné šarže kratomu. Obsahuje obsah alkaloidů, těžké kovy, mykotoxiny a mikrobiologii.',
            'seo_keyword' => 'coa kratom',
            'seo_secondary_keywords' => ['certificate of analysis kratom', 'kratom analýza protokol', 'kratom laboratorní zpráva'],
            'seo_meta_title' => 'COA u kratomu — jak číst laboratorní protokol šarže | Vivadzen Průvodce',
            'seo_meta_description' => 'Certificate of Analysis (COA) u kratomu: hlavní sekce protokolu, význam čísla šarže, status výsledků a vztah k regulaci PML od 2026.',
            'reading_time_minutes' => 7,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p><strong>Certificate of Analysis (COA)</strong> je formální laboratorní protokol, který popisuje výsledky analýzy jedné konkrétní šarže kratomového prášku nebo extraktu. Vystavuje jej akreditovaná laboratoř a v České republice je COA jedním z dokumentů, které distributor s <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">PML-licencí</a> uchovává jako součást evidence šarže podle <em>zákona č. 167/1998 Sb.</em> v účinné novele od 1. 1. 2026.</p>

<h2>Co je COA</h2>
<p>COA je standardizovaný dokument, který identifikuje zkoušený materiál (číslo šarže, datum výroby, dodavatel) a uvádí výsledky laboratorních zkoušek včetně:</p>
<ul>
    <li>obsahu hlavních <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">alkaloidů</a> (mitragynin, 7-hydroxymitragynin, případně další),</li>
    <li>obsahu vybraných <a href="/pruvodce/kvalita-a-bezpecnost/tezke-kovy-kratom">těžkých kovů</a> (Pb, Cd, Hg, As, případně Ni),</li>
    <li>obsahu <a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a> (aflatoxiny B1, B2, G1, G2; ochratoxin A),</li>
    <li><a href="/pruvodce/kvalita-a-bezpecnost/mikrobiologie-kratom">mikrobiologických parametrů</a> (TAMC, TYMC, E. coli, salmonella),</li>
    <li>obsahu polycyklických aromatických uhlovodíků (PAU), pokud je relevantní pro daný typ zpracování,</li>
    <li>granulometrie a obsahu vody.</li>
</ul>
<p>COA neobsahuje doporučení k použití ani „hodnocení kvality" — jde o měřený stav vzorku v konkrétním čase.</p>

<h2>Hlavní sekce protokolu</h2>
<h3>Hlavička</h3>
<p>Obsahuje identifikaci laboratoře (název, adresa, akreditační číslo dle ISO/IEC 17025, v ČR akreditace ČIA), identifikaci klienta, číslo šarže, datum příjmu vzorku a datum vystavení protokolu.</p>
<h3>Aktivní látky</h3>
<p>Uvádí obsah hlavních alkaloidů, typicky vyjádřený jako procento sušiny nebo mg/g. Pro kratom je standardem stanovení <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a> a <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragyninu</a> metodou <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">HPLC-DAD nebo HPLC-MS/MS</a>.</p>
<h3>Těžké kovy</h3>
<p>Stanovení provedené metodou ICP-MS, výsledek vyjádřený v mg/kg. Standardně sledované prvky: olovo (Pb), kadmium (Cd), rtuť (Hg), arsen (As), případně nikl (Ni).</p>
<h3>Mykotoxiny</h3>
<p>HPLC nebo LC-MS/MS analýza s detekcí aflatoxinů (B1, B2, G1, G2 a jejich součet) a ochratoxinu A. Výsledek v μg/kg.</p>
<h3>Mikrobiologie</h3>
<p>TAMC (celkový počet aerobních mikroorganismů), TYMC (celkový počet kvasinek a plísní), specifické patogeny (E. coli, Salmonella spp.). Hodnoty v CFU/g.</p>
<h3>Závěr</h3>
<p>Souhrnný status („vyhovuje" / „nevyhovuje") vůči referenční normě uvedené v hlavičce protokolu.</p>

<h2>Co je číslo šarže (lot) a proč je důležité</h2>
<p><strong>Číslo šarže</strong> (lot, batch number) je unikátní identifikátor konkrétního objemu materiálu vyrobeného za jednoho výrobního cyklu (typicky jedno pole, jeden den sušení, jedna fermentační dávka). Šarže je klíčovým spojovacím prvkem mezi COA a fyzickým produktem:</p>
<ul>
    <li>každá šarže má samostatné COA,</li>
    <li>distributor s <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">PML-licencí</a> musí v evidenci dohledat COA podle čísla šarže,</li>
    <li>při <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">dovozu</a> je číslo šarže součástí celní deklarace.</li>
</ul>
<p>Bez čísla šarže nelze COA jednoznačně přiřadit k fyzickému materiálu — taková dokumentace je pro účely 167/1998 Sb. nedostatečná.</p>

<h2>Jak interpretovat výsledky (status V, Vn, N)</h2>
<p>Některé laboratoře používají zkrácený statusový kód:</p>
<ul>
    <li><strong>V (vyhovuje)</strong> — hodnota je pod legislativním limitem,</li>
    <li><strong>Vn (vyhovuje s poznámkou)</strong> — hodnota je pod limitem, ale blíží se mu nebo je nad obvyklou hodnotou; v poznámce je rozšířený komentář,</li>
    <li><strong>N (nevyhovuje)</strong> — hodnota překročila legislativní limit.</li>
</ul>
<p>Vždy je nutné vědět, vůči jaké normě se výsledek hodnotí — limity pro kratom jako PML mohou vycházet z odlišného právního základu než limity pro doplňky stravy (například limit kadmia v rostlinných surovinách podle <em>nařízení EU 2023/915</em> versus farmakopejní limit podle USP &lt;232&gt;).</p>

<h2>Vztah COA k regulaci od 2026</h2>
<p>Podle novely <em>zákona č. 167/1998 Sb.</em> účinné od 1. 1. 2026 je COA jedním z dokumentů, které musí mít distributor PML k dispozici pro každou šarži. <a href="/pruvodce/legislativa-cr/sukl-mz-cr-dohled">SÚKL a Česká obchodní inspekce</a> mohou COA požadovat při kontrole. Bez COA je distribuce v rozporu s evidenční povinností podle § 3 a § 5 zákona.</p>

<h2>Často kladené otázky</h2>
<h3>Musí mít každá šarže COA?</h3>
<p>Ano. V režimu PML podle 167/1998 Sb. je COA součástí povinné evidence šarže. Distributor bez COA nesplňuje evidenční povinnost.</p>
<h3>Kdo testy provádí?</h3>
<p>Akreditovaná zkušební laboratoř podle <em>normy ČSN EN ISO/IEC 17025</em>. V ČR akreditaci uděluje Český institut pro akreditaci (ČIA). Akreditační číslo je uvedeno v hlavičce protokolu.</p>
<h3>Jak ověřit pravost COA?</h3>
<p>Tři kroky: (1) ověřit akreditační číslo laboratoře v rejstříku ČIA, (2) kontaktovat laboratoř s číslem protokolu k potvrzení vystavení, (3) porovnat parametry vzorku v COA s fyzickým balením (čísle šarže, datum).</p>
<h3>Je COA stejné jako etiketa?</h3>
<p>Není. Etiketa je informace pro spotřebitele na obalu; COA je laboratorní protokol uložený u distributora. Některé údaje (obsah hlavních alkaloidů) mohou být odvozeny z COA na etiketu.</p>

<h2>Reference</h2>
<ul>
    <li>ČSN EN ISO/IEC 17025:2018. Všeobecné požadavky na kompetenci zkušebních a kalibračních laboratoří.</li>
    <li>Český institut pro akreditaci (ČIA). Rejstřík akreditovaných subjektů.</li>
    <li>Zákon č. 167/1998 Sb., o návykových látkách, v platném znění (novela účinná od 1. 1. 2026).</li>
</ul>
HTML,
        ];
    }

    private function tezkeKovyKratom(): array
    {
        return [
            'slug' => 'tezke-kovy-kratom',
            'title' => 'Těžké kovy v kratomu: limity, testování, normy EU',
            'excerpt' => 'V kratomu se sleduje olovo, kadmium, rtuť, arsen a nikl. Limity stanoví nařízení EU 2023/915 a USP <232>. Měření probíhá metodou ICP-MS.',
            'seo_keyword' => 'těžké kovy kratom',
            'seo_secondary_keywords' => ['kratom olovo', 'kratom kadmium', 'kratom rtuť', 'kratom ICP-MS'],
            'seo_meta_title' => 'Těžké kovy v kratomu — limity EU a metoda ICP-MS | Vivadzen Průvodce',
            'seo_meta_description' => 'Těžké kovy v kratomu: sledované prvky (Pb, Cd, Hg, As, Ni), limity podle nařízení EU 2023/915 a USP <232>, půdní akumulace a metoda ICP-MS.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Kratom jako každý rostlinný materiál může obsahovat <strong>těžké kovy</strong> pocházející z půdy, vody nebo z procesu sušení. V analytické praxi se sleduje pět hlavních prvků: olovo (Pb), kadmium (Cd), rtuť (Hg), arsen (As) a nikl (Ni). Limity stanoví především <em>nařízení Komise (EU) 2023/915 o maximálních úrovních kontaminantů v potravinách</em> a farmakopejní standardy <em>USP &lt;232&gt;</em> pro elementární nečistoty.</p>

<h2>Které kovy se sledují</h2>
<h3>Olovo (Pb)</h3>
<p>Vstupuje do rostliny z půdy, atmosférického spadu (zejména v oblastech s historickou kontaminací benzinem s tetraethylolovem) a při zpracování. Patří mezi nejčastěji nadlimitní prvky u sušených rostlin.</p>
<h3>Kadmium (Cd)</h3>
<p>Akumuluje se v listech z kontaminovaných půd, zejména v okolí fosfátových hnojiv a důlních činností. Kadmium je v rostlinných materiálech sledováno přísně.</p>
<h3>Rtuť (Hg)</h3>
<p>Méně častý problém u rostlinných surovin, ale sleduje se z důvodu možné kontaminace v okolí spaloven a chemických závodů. V <a href="/pruvodce/historie-a-kultura/kratom-indonesie">indonéské produkci</a> může souviset s těžbou zlata v okolí Kalimantanu.</p>
<h3>Arsen (As)</h3>
<p>Přírodní výskyt v některých půdách (zejména v aluviálních), může se akumulovat v rostlinách. Sleduje se anorganický arsen, který má vyšší toxicitu než organický.</p>
<h3>Nikl (Ni)</h3>
<p>Sleduje se primárně jako kontaminant z kovových povrchů strojů na zpracování (mlecí zařízení, dopravníky). U surového listu bývá nikl v nízkých koncentracích, u průmyslově zpracovaného prášku může být vyšší.</p>

<h2>Limity podle EU 2023/915 a USP</h2>
<p>Pro orientaci uvádíme limity, které se v praxi používají jako reference; konkrétní zařazení kratomu v české právní úpravě se odvíjí od <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">PML-režimu podle 167/1998 Sb.</a>, který může stanovit vlastní limity ve své prováděcí vyhlášce.</p>
<h3>Nařízení EU 2023/915 (potravinový rámec, orientačně pro rostlinné suroviny)</h3>
<ul>
    <li>Olovo (Pb): 3,0 mg/kg pro doplňky stravy obsahující rostliny,</li>
    <li>Kadmium (Cd): 1,0–3,0 mg/kg podle typu rostlinné suroviny,</li>
    <li>Rtuť (Hg): 0,1 mg/kg pro doplňky stravy,</li>
    <li>Arsen anorganický: 1,0 mg/kg pro doplňky stravy.</li>
</ul>
<h3>USP &lt;232&gt; (farmakopejní limity elementárních nečistot, orální cesta)</h3>
<ul>
    <li>Olovo (Pb): 5 μg/g (PDE 5 μg/den),</li>
    <li>Kadmium (Cd): 5 μg/g,</li>
    <li>Rtuť (Hg): 30 μg/g,</li>
    <li>Arsen (As): 15 μg/g,</li>
    <li>Nikl (Ni): 200 μg/g.</li>
</ul>
<p>Konkrétní hodnoty se aktualizují; v <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">COA</a> by mělo být uvedeno, vůči jakému znění normy je výsledek hodnocen.</p>

<h2>Jak se dostávají do listu</h2>
<p>Hlavní cesty kontaminace:</p>
<ul>
    <li><strong>Půdní akumulace</strong> — strom čerpá z půdy stopové prvky včetně kovů. Půdy kolem důlních činností, v okolí silnic a v zaplavovaných oblastech mohou mít vyšší obsah Pb, Cd, As.</li>
    <li><strong>Voda</strong> — závlaha kontaminovanou povrchovou vodou.</li>
    <li><strong>Atmosférický spad</strong> — v okolí spaloven, chemických závodů a frekventovaných silnic.</li>
    <li><strong>Zpracování</strong> — kontakt s kovovými povrchy mlecích a balicích linek (nikl, případně chrom).</li>
</ul>

<h2>Metoda měření (ICP-MS)</h2>
<p><strong>ICP-MS</strong> (Inductively Coupled Plasma Mass Spectrometry) je referenční metoda pro stanovení těžkých kovů v rostlinných surovinách. Postup:</p>
<ol>
    <li>navážka 0,2–0,5 g sušiny,</li>
    <li>mikrovlnný rozklad v kyselině dusičné (HNO<sub>3</sub>) s peroxidem vodíku,</li>
    <li>doplnění demineralizovanou vodou na referenční objem,</li>
    <li>měření ICP-MS s vnitřním standardem (typicky <sup>103</sup>Rh, <sup>115</sup>In),</li>
    <li>kvantifikace proti kalibrační řadě.</li>
</ol>
<p>Mez stanovitelnosti (LOQ) je u většiny prvků pod 0,01 mg/kg, což je o řád pod legislativními limity. Více v článku <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">HPLC a ICP-MS — laboratorní metody pro analýzu kratomu</a>.</p>

<h2>Často kladené otázky</h2>
<h3>Jaký je limit olova v rostlinných surovinách?</h3>
<p>Podle nařízení EU 2023/915 typicky 3,0 mg/kg pro doplňky stravy obsahující rostliny. Farmakopejní limit podle USP &lt;232&gt; je 5 μg/g.</p>
<h3>Je rtuť častý problém?</h3>
<p>U rostlinných surovin obecně méně častý. V indonéských oblastech sousedících s neformální těžbou zlata může být problém větší, proto je rtuť standardně sledována.</p>
<h3>Proč se hlídá nikl?</h3>
<p>Nikl je indikátorem kontaminace ze zpracování (mlecí stroje, kovové povrchy). Vysoká hodnota signalizuje technologický problém, ne primárně kontaminaci suroviny.</p>
<h3>Lze odstranit těžké kovy z hotového prášku?</h3>
<p>Z praktického hlediska nikoliv. Kovy jsou pevně vázány v rostlinné matrici. Klíčová je kontrola na vstupu (volba surovinového zdroje, ne dodatečné „čištění").</p>

<h2>Reference</h2>
<ul>
    <li>Nařízení Komise (EU) 2023/915 o maximálních úrovních některých kontaminantů v potravinách.</li>
    <li>USP &lt;232&gt;. Elemental Impurities — Limits. United States Pharmacopeia.</li>
    <li>USP &lt;233&gt;. Elemental Impurities — Procedures. United States Pharmacopeia.</li>
</ul>
HTML,
        ];
    }

    private function mykotoxinyKratom(): array
    {
        return [
            'slug' => 'mykotoxiny-kratom',
            'title' => 'Mykotoxiny v rostlinných práších: kontaminace a kontrola',
            'excerpt' => 'Mykotoxiny (aflatoxiny B1, B2, G1, G2 a ochratoxin A) vznikají činností plísní při sušení a skladování. Limity stanoví nařízení EU 2023/915.',
            'seo_keyword' => 'mykotoxiny kratom',
            'seo_secondary_keywords' => ['aflatoxiny kratom', 'ochratoxin kratom', 'plísně v kratomu'],
            'seo_meta_title' => 'Mykotoxiny v kratomu — aflatoxiny, OTA a limity EU | Vivadzen Průvodce',
            'seo_meta_description' => 'Mykotoxiny v rostlinných práších: hlavní třídy (aflatoxiny B1/B2/G1/G2, OTA), vznik při sušení a skladování, limity podle nařízení EU 2023/915.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p><strong>Mykotoxiny</strong> jsou sekundární metabolity některých plísní (zejména rodů <em>Aspergillus</em>, <em>Penicillium</em> a <em>Fusarium</em>), které mohou vznikat při nesprávném sušení nebo skladování rostlinných surovin. U kratomu se v analytické praxi sledují především <strong>aflatoxiny</strong> a <strong>ochratoxin A</strong>. Limity stanoví <em>nařízení Komise (EU) 2023/915</em>.</p>

<h2>Co jsou mykotoxiny</h2>
<p>Mykotoxiny jsou nízkomolekulární organické sloučeniny, které plísně produkují v reakci na stres nebo jako součást konkurenční strategie. V rostlinných surovinách představují závažný kontaminant, protože:</p>
<ul>
    <li>jsou <strong>termostabilní</strong> — varem ani běžným tepelným zpracováním je nelze degradovat,</li>
    <li>mají vysoký <strong>toxikologický potenciál</strong> i v mikroskopických koncentracích,</li>
    <li>jejich přítomnost je <strong>nerovnoměrná</strong> — v jedné šarži mohou koncentrace lokálně kolísat o řády,</li>
    <li>nejsou vizuálně detekovatelné — kontaminace bez viditelných známek plísně je možná.</li>
</ul>

<h2>Hlavní třídy</h2>
<h3>Aflatoxiny</h3>
<p>Produkt plísní <em>Aspergillus flavus</em> a <em>Aspergillus parasiticus</em>. V analytické praxi se sledují čtyři varianty:</p>
<ul>
    <li><strong>aflatoxin B1</strong> — nejtoxičtější, IARC ho zařadila do skupiny 1 (lidský karcinogen),</li>
    <li><strong>aflatoxin B2</strong> — strukturní analog B1,</li>
    <li><strong>aflatoxin G1</strong>, <strong>aflatoxin G2</strong> — méně toxické varianty.</li>
</ul>
<p>Pro hodnocení se uvádí jak hodnota B1 samostatně, tak suma B1+B2+G1+G2.</p>
<h3>Ochratoxin A (OTA)</h3>
<p>Produkt plísní rodu <em>Aspergillus</em> a <em>Penicillium</em>, zejména <em>Aspergillus ochraceus</em> a <em>Penicillium verrucosum</em>. Sleduje se u všech sušených rostlinných surovin, zejména u produktů s delším skladováním.</p>
<h3>Další (méně relevantní pro kratom)</h3>
<p>Zearalenon, fumonisiny a deoxynivalenol jsou primárně sledovány u obilovin a v kratomu nebývají dominantním problémem.</p>

<h2>Vznik při sušení a skladování</h2>
<p>Mykotoxiny v kratomu vznikají typicky:</p>
<ul>
    <li>při <strong>pomalém sušení</strong> v období dešťů, kdy vlhkost listu zůstává nad 15 % po několik dní,</li>
    <li>při <strong>nesprávné <a href="/pruvodce/botanika-a-veda/fermentace-kratomu">fermentaci</a></strong> bez kontroly teploty a vlhkosti,</li>
    <li>při skladování v nevhodných podmínkách (vlhko, kolísavá teplota, kontaminované pytle),</li>
    <li>při dlouhé přepravě v nevětraných kontejnerech.</li>
</ul>
<p>Prevence je vždy účinnější než následná analýza — proto je důležitá <a href="/pruvodce/kvalita-a-bezpecnost/skladovani-kratomu">správná logistika a skladování</a> v celém řetězci od plantáže po prodejce.</p>

<h2>Limity dle EU 2023/915</h2>
<p>Nařízení Komise (EU) 2023/915 stanoví následující limity pro rostlinné suroviny (orientačně; konkrétní zařazení kratomu v české úpravě závisí na <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">PML-režimu</a>):</p>
<ul>
    <li><strong>Aflatoxin B1</strong>: 2,0 μg/kg (pro většinu rostlinných surovin a koření),</li>
    <li><strong>Suma B1+B2+G1+G2</strong>: 4,0 μg/kg,</li>
    <li><strong>Ochratoxin A</strong>: 10–20 μg/kg podle typu suroviny (koření 15 μg/kg, sušené ovoce 10 μg/kg).</li>
</ul>
<p>Tyto limity vycházejí z farmakologického hodnocení EFSA a jsou jednotné pro celou EU.</p>

<h2>Často kladené otázky</h2>
<h3>Jaký je limit pro aflatoxin B1?</h3>
<p>Podle nařízení EU 2023/915 typicky 2,0 μg/kg pro rostlinné suroviny a koření. Některé specifické kategorie mohou mít odlišný limit.</p>
<h3>Lze mykotoxiny zničit varem?</h3>
<p>Nikoliv. Aflatoxiny i ochratoxin A jsou termostabilní a běžným tepelným zpracováním (var, sušení) je nelze degradovat. Prevence spočívá v zabránění jejich vzniku, ne v následné degradaci.</p>
<h3>Jak často se testuje?</h3>
<p>Standardem je test pro každou šarži vstupní suroviny a pro každou šarži balené k distribuci. Periodicita je součástí evidenční povinnosti distributora.</p>
<h3>Jaký je rozdíl mezi viditelnou plísní a mykotoxinem?</h3>
<p>Plíseň je živý organismus; mykotoxin je její chemický produkt. Mykotoxin může být přítomen i po odstranění viditelné plísně. Naopak viditelná plíseň nemusí vždy produkovat mykotoxin (záleží na druhu a podmínkách).</p>

<h2>Reference</h2>
<ul>
    <li>Nařízení Komise (EU) 2023/915 o maximálních úrovních některých kontaminantů v potravinách.</li>
    <li>EFSA Panel on Contaminants in the Food Chain (2020). Risk assessment of aflatoxins in food. <em>EFSA Journal</em>, 18(3), 6040.</li>
    <li>IARC Monographs on the Evaluation of Carcinogenic Risks to Humans, Volume 100F (2012). Aflatoxins.</li>
</ul>
HTML,
        ];
    }

    private function mikrobiologieKratom(): array
    {
        return [
            'slug' => 'mikrobiologie-kratom',
            'title' => 'Mikrobiologická bezpečnost kratomu: testy a limity',
            'excerpt' => 'V kratomu se sleduje TAMC, TYMC, E. coli a salmonella podle EuPh 5.1.4. Limity, příčiny kontaminace a běžné detekční metody.',
            'seo_keyword' => 'kratom mikrobiologie',
            'seo_secondary_keywords' => ['kratom bakterie', 'kratom salmonella', 'kratom E. coli'],
            'seo_meta_title' => 'Mikrobiologie kratomu — TAMC, TYMC a limity EuPh | Vivadzen Průvodce',
            'seo_meta_description' => 'Mikrobiologická bezpečnost kratomu: sledované parametry (TAMC, TYMC, E. coli, Salmonella), limity podle EuPh 5.1.4 a USP <2021>, příčiny kontaminace.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Mikrobiologická bezpečnost rostlinných surovin se hodnotí podle čtyř hlavních parametrů: <strong>TAMC</strong> (celkový počet aerobních mikroorganismů), <strong>TYMC</strong> (celkový počet kvasinek a plísní), a přítomnost specifických patogenů <em>Escherichia coli</em> a <em>Salmonella</em> spp. Referenčním rámcem je <em>Evropský lékopis (Ph. Eur.) kapitola 5.1.4</em> a <em>USP &lt;2021&gt;</em>.</p>

<h2>Co se sleduje</h2>
<h3>TAMC (Total Aerobic Microbial Count)</h3>
<p>Celkový počet aerobních mikroorganismů (bakterií) v jednom gramu vzorku, vyjádřeno v CFU/g (colony-forming units per gram). Indikuje obecnou mikrobiologickou čistotu materiálu.</p>
<h3>TYMC (Total Yeast and Mold Count)</h3>
<p>Celkový počet kvasinek a plísní v jednom gramu vzorku, v CFU/g. Vysoké TYMC souvisí s rizikem vzniku <a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a> — proto je TYMC důležitým preventivním ukazatelem.</p>
<h3>Specifické patogeny</h3>
<ul>
    <li><strong><em>Escherichia coli</em></strong> — indikátor fekální kontaminace; v rostlinných surovinách k distribuci je typicky vyžadována nepřítomnost ve definovaném množství vzorku (např. 1 g),</li>
    <li><strong><em>Salmonella</em> spp.</strong> — patogenní bakterie; standardně se vyžaduje nepřítomnost v 10 g vzorku.</li>
</ul>
<p>Nepovinně se podle účelu materiálu testují i další patogeny (<em>Staphylococcus aureus</em>, <em>Pseudomonas aeruginosa</em>, <em>Bacillus cereus</em>).</p>

<h2>Limity podle EuPh 5.1.4</h2>
<p>Evropský lékopis stanoví limity podle kategorie použití suroviny. Pro <strong>orální použití u herbálních surovin</strong> (kategorie B) jsou orientační limity:</p>
<ul>
    <li>TAMC: ≤ 10<sup>7</sup> CFU/g (tedy maximálně 10 milionů CFU na gram),</li>
    <li>TYMC: ≤ 10<sup>5</sup> CFU/g (tedy maximálně 100 tisíc CFU na gram),</li>
    <li>nepřítomnost <em>Salmonella</em> v 25 g,</li>
    <li>nepřítomnost <em>E. coli</em> v 1 g.</li>
</ul>
<p>Pro herbální suroviny s předpokládanou kvalitnější úpravou (kategorie A) jsou limity přísnější (TAMC ≤ 10<sup>5</sup> CFU/g). Konkrétní zařazení kratomu do kategorie závisí na účelu použití uvedeném v dokumentaci.</p>

<h2>Příčiny kontaminace</h2>
<p>Hlavní zdroje mikrobiologické kontaminace v řetězci kratomu:</p>
<ul>
    <li><strong>Sklizeň</strong> — kontakt s půdou, hmyzem, ptactvem,</li>
    <li><strong>Sušení</strong> — sušení v nečistém prostředí, nedostatečné větrání, kontakt s kontaminovanými povrchy,</li>
    <li><strong><a href="/pruvodce/botanika-a-veda/fermentace-kratomu">Fermentace</a></strong> — pokud probíhá v nevyhovujících podmínkách, podporuje rozvoj plísní,</li>
    <li><strong>Skladování a přeprava</strong> — vlhko, vysoká teplota, kontaminované pytle,</li>
    <li><strong>Sekundární kontaminace při balení</strong> — kontakt s personálem, nedostatečně dezinfikované nástroje.</li>
</ul>
<p>Prevence kontaminace je primárně otázkou hygienické disciplíny v celé výrobní řadě, ne pouze finální analýzy.</p>

<h2>Metoda detekce</h2>
<p>Standardní mikrobiologická analýza využívá kultivaci na selektivních agarech:</p>
<ul>
    <li><strong>TAMC</strong> — Tryptic Soy Agar (TSA), inkubace 30–35 °C, 3–5 dní,</li>
    <li><strong>TYMC</strong> — Sabouraud Dextrose Agar (SDA), inkubace 20–25 °C, 5–7 dní,</li>
    <li><strong><em>E. coli</em></strong> — MacConkey Agar / EMB s konfirmací biochemickými testy,</li>
    <li><strong><em>Salmonella</em></strong> — selektivní obohacení (RV bujón) + výsev na XLD/Hektoen agar + sérologická konfirmace.</li>
</ul>
<p>Moderní laboratoře využívají i molekulární metody (PCR, MALDI-TOF) pro rychlejší identifikaci.</p>

<h2>Často kladené otázky</h2>
<h3>Lze kratom sterilizovat ozářením?</h3>
<p>Některé typy gamma-ozáření jsou používány pro mikrobiologickou dekontaminaci koření a rostlinných surovin. Vliv na obsah <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">alkaloidů</a> však není u kratomu dostatečně studován; v EU navíc podléhá ozáření potravin samostatné regulaci (směrnice 1999/2/ES, 1999/3/ES).</p>
<h3>Co znamená TAMC?</h3>
<p>Total Aerobic Microbial Count — celkový počet aerobních (kyslík vyžadujících) mikroorganismů v jednom gramu vzorku, vyjádřeno v CFU/g.</p>
<h3>Je salmonella v kratomu častý nález?</h3>
<p>Při dodržení standardní hygieny ne. Případy záchytu se objevují u suroviny zpracované v nevyhovujících podmínkách. Standardem je test na nepřítomnost v 25 g vzorku.</p>
<h3>Jak souvisí TYMC s mykotoxiny?</h3>
<p>TYMC měří přítomnost plísní; samotná přítomnost neznamená přítomnost <a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a>, ale vysoké TYMC zvyšuje riziko jejich tvorby. Oba parametry se posuzují společně.</p>

<h2>Reference</h2>
<ul>
    <li>European Pharmacopoeia, kap. 5.1.4. Microbiological quality of non-sterile pharmaceutical preparations and substances for pharmaceutical use.</li>
    <li>USP &lt;2021&gt;. Microbial Enumeration Tests — Nutritional and Dietary Supplements. United States Pharmacopeia.</li>
    <li>USP &lt;2023&gt;. Microbial Procedures for Absence of Specified Microorganisms — Nutritional and Dietary Supplements.</li>
</ul>
HTML,
        ];
    }

    private function hplcIcpMsKratom(): array
    {
        return [
            'slug' => 'hplc-icp-ms-kratom',
            'title' => 'HPLC a ICP-MS: laboratorní metody pro analýzu kratomu',
            'excerpt' => 'HPLC stanovuje obsah alkaloidů (mitragynin, 7-OH-MG), ICP-MS měří těžké kovy. Příprava vzorku, LOQ a vzájemné srovnání metod.',
            'seo_keyword' => 'hplc kratom',
            'seo_secondary_keywords' => ['ICP-MS kratom', 'kratom analýza metoda', 'kratom alkaloidy stanovení'],
            'seo_meta_title' => 'HPLC a ICP-MS pro kratom — laboratorní metody analýzy | Vivadzen Průvodce',
            'seo_meta_description' => 'HPLC pro stanovení mitragyninu a 7-OH-MG, ICP-MS pro těžké kovy. Příprava vzorku, mez stanovitelnosti (LOQ) a srovnání s alternativními metodami.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Dvě referenční metody pro analýzu kratomu v akreditovaných laboratořích jsou <strong>HPLC</strong> (vysoceúčinná kapalinová chromatografie) pro stanovení <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">alkaloidů</a> a <strong>ICP-MS</strong> (hmotnostní spektrometrie s indukčně vázaným plazmatem) pro <a href="/pruvodce/kvalita-a-bezpecnost/tezke-kovy-kratom">stanovení těžkých kovů</a>. Obě metody jsou popsány v USP a v ISO normách jako standardní postupy.</p>

<h2>HPLC pro alkaloidy</h2>
<p>HPLC umožňuje separaci jednotlivých alkaloidů a jejich kvantifikaci. Pro kratom je standardním cílem stanovení <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a> a <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragyninu</a>; rozšířené metody pokrývají i paynantheine, speciogynin a speciociliatine.</p>
<h3>Detektor</h3>
<ul>
    <li><strong>HPLC-DAD</strong> (diode array detector, UV-VIS) — jednodušší, levnější, vhodné pro rutinní stanovení mitragyninu (LOQ řádově 0,01 %).</li>
    <li><strong>HPLC-MS/MS</strong> (tandemová hmotnostní spektrometrie) — řádově citlivější, identifikace na základě hmotnostního spektra, vhodné pro stopové alkaloidy včetně 7-OH-MG (LOQ jednotky ng/g).</li>
</ul>
<h3>Stacionární a mobilní fáze</h3>
<p>Standardně se používá reverzní fáze (C18 kolona, 100–150 mm × 2,1–4,6 mm, částice 1,7–3,5 μm). Mobilní fáze je gradient acetonitril/voda s přídavkem mravenčí kyseliny nebo amonium-formiátu pro úpravu pH.</p>
<h3>Příprava vzorku</h3>
<p>Standardní postup: navážka 0,1–0,5 g sušeného prášku, extrakce do methanolu nebo směsi methanol/voda za pomoci ultrazvuku, filtrace (0,22 μm), nástřik 1–10 μl. Pro extrakty se přizpůsobuje ředění podle očekávaného obsahu.</p>

<h2>ICP-MS pro těžké kovy</h2>
<p>ICP-MS je referenční metoda pro stanovení stopových koncentrací prvků. Principem je ionizace vzorku v argonovém plazmatu při ~7000 K a měření ionů hmotnostním spektrometrem podle poměru hmotnosti k náboji (m/z).</p>
<h3>Příprava vzorku</h3>
<ol>
    <li>Navážka 0,2–0,5 g sušiny do teflonové nádobky.</li>
    <li>Mikrovlnný rozklad v HNO<sub>3</sub> (6–8 ml) s H<sub>2</sub>O<sub>2</sub> (1–2 ml) při teplotě ~200 °C, tlaku ~40 bar.</li>
    <li>Po vychladnutí doplnění demineralizovanou vodou na referenční objem (typicky 25–50 ml).</li>
    <li>Filtrace nebo odstředění před nástřikem do ICP-MS.</li>
</ol>
<h3>Vnitřní standardy</h3>
<p>Pro korekci matricových efektů se přidávají vnitřní standardy — typicky <sup>103</sup>Rh, <sup>115</sup>In, <sup>209</sup>Bi v koncentraci jednotky až desítky μg/l.</p>
<h3>Měřené izotopy</h3>
<p>Standardně se sleduje <sup>208</sup>Pb (olovo), <sup>111</sup>Cd (kadmium), <sup>202</sup>Hg (rtuť), <sup>75</sup>As (arsen) a <sup>60</sup>Ni (nikl). Při potřebě jsou doplněna další (<sup>52</sup>Cr, <sup>121</sup>Sb, <sup>137</sup>Ba).</p>

<h2>Limit of quantification (LOQ) a jeho význam</h2>
<p><strong>LOQ</strong> je nejnižší koncentrace analytu, kterou lze daným analytickým postupem kvantifikovat s definovanou přesností a správností (typicky relativní směrodatná odchylka ≤ 10 %). U metody musí být LOQ řádově nižší než platný legislativní limit — jinak metoda neposkytuje spolehlivé hodnocení.</p>
<p>Příklady typických LOQ:</p>
<ul>
    <li>HPLC-DAD pro mitragynin: 0,01 % hmotnostně (řádově 0,1 mg/g),</li>
    <li>HPLC-MS/MS pro 7-OH-MG: jednotky ng/g vzorku,</li>
    <li>ICP-MS pro olovo: 0,005 mg/kg (tedy 0,005 μg/g) — tři řády pod legislativním limitem 3 mg/kg.</li>
</ul>

<h2>Srovnání s alternativními metodami</h2>
<ul>
    <li><strong>GC-MS</strong> (plynová chromatografie) je pro alkaloidy kratomu méně vhodná — molekuly jsou termolabilní a vyžadovaly by derivatizaci. Pro stopové studie se používá, ale není standardem.</li>
    <li><strong>UV-VIS spektrofotometrie</strong> může poskytnout sumární obsah alkaloidů, ale bez separace jednotlivých látek. Pro účely <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">COA</a> není dostatečná.</li>
    <li><strong>NMR</strong> umožňuje strukturní identifikaci, používá se ve výzkumu, ale ne pro rutinní šaržovou kontrolu.</li>
    <li><strong>AAS</strong> (atomová absorpční spektrometrie) je starší metoda pro těžké kovy, dnes z velké části nahrazena ICP-MS kvůli nižší citlivosti.</li>
</ul>

<h2>Často kladené otázky</h2>
<h3>Proč ne GC-MS?</h3>
<p>Alkaloidy kratomu jsou tepelně labilní a v plynové chromatografii by se rozkládaly. Pro spolehlivé stanovení se používá HPLC.</p>
<h3>Jaký je LOQ pro mitragynin?</h3>
<p>U HPLC-DAD řádově 0,01 % hmotnostně, u HPLC-MS/MS jednotky ng/g. Konkrétní hodnota záleží na konkrétní metodě a přístroji.</p>
<h3>Lze měřit obsah doma?</h3>
<p>Spolehlivě nikoliv. Kvantitativní HPLC a ICP-MS vyžadují přístroje za stovky tisíc až miliony korun, kalibraci a metrologickou návaznost. Orientační kvalitativní testy (např. barevné reakce na alkaloidy) mají velmi omezenou výpovědní hodnotu.</p>
<h3>Stačí jediná metoda pro celé COA?</h3>
<p>Ne. Komplexní COA vyžaduje kombinaci metod: HPLC pro alkaloidy, ICP-MS pro kovy, LC-MS/MS pro mykotoxiny a kultivační metody pro mikrobiologii. Akreditovaná laboratoř má jejich validované postupy.</p>

<h2>Reference</h2>
<ul>
    <li>Wang M. et al. (2014). High-performance liquid chromatographic analysis of mitragynine and 7-hydroxymitragynine. <em>Journal of Pharmaceutical and Biomedical Analysis</em>, 90, 32–39.</li>
    <li>USP &lt;233&gt;. Elemental Impurities — Procedures. United States Pharmacopeia.</li>
    <li>ČSN EN ISO/IEC 17025:2018. Všeobecné požadavky na kompetenci zkušebních a kalibračních laboratoří.</li>
</ul>
HTML,
        ];
    }

    private function skladovaniKratomu(): array
    {
        return [
            'slug' => 'skladovani-kratomu',
            'title' => 'Skladování kratomového prášku: stabilita alkaloidů',
            'excerpt' => 'Kratomový prášek je stabilní 12–24 měsíců v suchu, chladu a tmě. Mitragynin oxiduje na 7-hydroxymitragynin za přístupu vzduchu a světla.',
            'seo_keyword' => 'skladování kratomu',
            'seo_secondary_keywords' => ['kratom uchování', 'kratom stabilita', 'kratom expirace'],
            'seo_meta_title' => 'Skladování kratomu — stabilita alkaloidů a optimální podmínky | Vivadzen Průvodce',
            'seo_meta_description' => 'Skladování kratomového prášku: optimální podmínky (suché, chladné, tma), rozklad mitragyninu, typická expirace 12–24 měsíců, vhodné obaly.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Kratomový prášek je při správném skladování v <strong>suchu, chladu a tmě</strong> stabilní typicky <strong>12–24 měsíců</strong>. Hlavními degradačními procesy jsou oxidace alkaloidů (zejména konverze <a href="/pruvodce/botanika-a-veda/mitragynin">mitragyninu</a> na <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragynin</a>), růst mikroorganismů při zvýšené vlhkosti a vznik <a href="/pruvodce/kvalita-a-bezpecnost/mykotoxiny-kratom">mykotoxinů</a>.</p>

<h2>Optimální podmínky</h2>
<p>Pro maximální stabilitu alkaloidů a mikrobiologickou bezpečnost se v praxi doporučují tyto skladovací parametry:</p>
<ul>
    <li><strong>Teplota:</strong> 5–20 °C (chladné prostředí zpomaluje oxidační reakce),</li>
    <li><strong>Relativní vlhkost:</strong> &lt; 60 % (nad touto hodnotou roste riziko růstu plísní),</li>
    <li><strong>Světlo:</strong> tma nebo nepřímé osvětlení (UV složka urychluje rozklad alkaloidů),</li>
    <li><strong>Kyslík:</strong> minimalizovat přístup vzduchu (vakuové balení, sáčky s nízkou propustností),</li>
    <li><strong>Kontaminace:</strong> oddělené skladování od silných pachů (cibule, pražená káva) — prášek pohlcuje aroma.</li>
</ul>
<p>Komerční sklady distributorů s <a href="/pruvodce/legislativa-cr/licencovani-pml-cr">PML-licencí</a> mají kontrolu teploty a vlhkosti jako součást licenčních požadavků.</p>

<h2>Rozklad alkaloidů</h2>
<p>Hlavní degradační reakcí kratomu je <strong>oxidace mitragyninu</strong> kyslíkem na 7-hydroxymitragynin a další oxidační produkty. Faktory, které proces urychlují:</p>
<ul>
    <li>přístup vzduchu (otevřený obal),</li>
    <li>UV záření (přímé sluneční světlo),</li>
    <li>zvýšená teplota (nad 25 °C),</li>
    <li>přítomnost stopových kovů (železo, měď z prachu nebo z obalu).</li>
</ul>
<p>Z analytického hlediska se posun alkaloidního profilu projevuje snížením relativního obsahu mitragyninu a nárůstem 7-OH-MG. U dlouho skladovaného materiálu může klesnout i celkový alkaloidní obsah (oxidace na další degradační produkty).</p>

<h2>Typická expirace (12–24 měsíců)</h2>
<p>Při dodržení optimálních podmínek je kratomový prášek mikrobiologicky a chemicky stabilní:</p>
<ul>
    <li>v zatavené metalizované folii s ochrannou atmosférou (N<sub>2</sub>): typicky <strong>18–24 měsíců</strong>,</li>
    <li>ve standardním PE doypacku se ZIP uzávěrem: typicky <strong>12–18 měsíců</strong>,</li>
    <li>v papírovém pytli (skladová surovina): typicky <strong>6–12 měsíců</strong>.</li>
</ul>
<p>Expirační doba je v EU vyznačována jako „best before" (minimální trvanlivost) — po jejím překročení materiál není automaticky nepoužitelný, ale výrobce již nezaručuje deklarovaný profil. U <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">COA</a> je vždy uvedeno datum analýzy, které je vstupem pro stanovení expirace.</p>

<h2>Obaly</h2>
<h3>PE doypack se ZIP uzávěrem</h3>
<p>Standardní spotřebitelské balení. Výhodou je uživatelská přístupnost, nevýhodou částečná propustnost pro kyslík a vlhkost. Pro delší skladování se kombinuje s vnitřním sáčkem s alupropojením.</p>
<h3>Hliníkový sáček (Mylar / aluminizovaná folie)</h3>
<p>Bariérový obal s minimální propustností pro kyslík, vlhkost i světlo. Vhodný pro dlouhodobé skladování. Často se kombinuje s plněním v ochranné atmosféře dusíku.</p>
<h3>Skleněná tmavá nádoba</h3>
<p>Vhodná pro malé množství. Sklo je inertní materiál a tmavé sklo omezuje fotodegradaci. Nevýhodou je hmotnost a křehkost při manipulaci.</p>

<h2>Často kladené otázky</h2>
<h3>Lze kratom mrazit?</h3>
<p>Z hlediska stability alkaloidů ano — nízká teplota zpomaluje oxidaci. Z praktického hlediska je důležité prášek před otevřením nechat zahřát na pokojovou teplotu uvnitř obalu, aby se na něm nesrážela vzdušná vlhkost při kontaktu s teplým vzduchem.</p>
<h3>Co znamená „best before"?</h3>
<p>Minimální trvanlivost — datum, do kterého výrobce garantuje deklarované parametry. Po překročení materiál nemusí být nepoužitelný, ale není zaručena shoda s původní specifikací.</p>
<h3>Změní se barva při stárnutí?</h3>
<p>Ano. Čerstvě mletý prášek má sytě zelený odstín (pro „zelený" typ); stárnutím a oxidací posouvá k hnědozelené nebo světle hnědé. Změna barvy je vizuálním indikátorem oxidace, ale neumožňuje kvantifikovat zbývající obsah alkaloidů — k tomu je nutná <a href="/pruvodce/kvalita-a-bezpecnost/hplc-icp-ms-kratom">HPLC analýza</a>.</p>
<h3>Jak rozpoznat materiál, který byl uložen nevhodně?</h3>
<p>Vizuální známky: lepivost, viditelné plísně, štiplavý nebo zatuchlý zápach, výrazná změna barvy. V takovém případě materiál nesplňuje mikrobiologické limity a nelze ho považovat za bezpečný — viz článek <a href="/pruvodce/kvalita-a-bezpecnost/mikrobiologie-kratom">Mikrobiologická bezpečnost kratomu</a>.</p>

<h2>Reference</h2>
<ul>
    <li>Sengnon N. et al. (2021). Phytochemical comparison among different colour-vein cultivars of <em>Mitragyna speciosa</em>. <em>Molecules</em>, 26(17), 5141.</li>
    <li>Brown P. N. et al. (2017). A botanical, phytochemical and ethnomedicinal review of the genus <em>Mitragyna</em>. <em>Journal of Ethnopharmacology</em>, 202, 302–325.</li>
    <li>ICH Q1A(R2). Stability Testing of New Drug Substances and Products. International Council for Harmonisation.</li>
</ul>
HTML,
        ];
    }

    private function kratomExtraktVsPrasek(): array
    {
        return [
            'slug' => 'kratom-extrakt-vs-prasek',
            'title' => 'Kratom extrakt vs prášek: technologie výroby',
            'excerpt' => 'Prášek vzniká mletím sušeného listu, extrakt vodní nebo alkoholovou extrakcí a následným sušením. Označení 10× a 20× popisuje koncentrační poměr.',
            'seo_keyword' => 'rozdíl kratom extrakt prášek',
            'seo_secondary_keywords' => ['kratom extrakt výroba', 'kratom prášek výroba', 'kratom 10x extrakt'],
            'seo_meta_title' => 'Kratom extrakt vs prášek — technologie výroby a koncentrace | Vivadzen Průvodce',
            'seo_meta_description' => 'Kratom prášek vs extrakt: technologie výroby (mletí vs extrakce), význam označení 10× a 20×, standardizace a její limity.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Z technologického pohledu se kratomové produkty dělí na dvě hlavní skupiny: <strong>prášek</strong> (sušený a namletý list bez další úpravy) a <strong>extrakt</strong> (koncentrovaná frakce alkaloidů získaná extrakcí rozpouštědlem a následným odpařením). Označení „10×" nebo „20×" popisuje koncentrační poměr — kolik kilogramů suroviny bylo zpracováno na 1 kilogram konečného produktu. Z hlediska české regulace spadají oba typy pod stejný <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">PML-režim</a>.</p>

<h2>Prášek (mletí sušeného listu)</h2>
<p>Výroba kratomového prášku je technologicky jednodušší proces:</p>
<ol>
    <li>Sklizeň zralého listu z plantáže.</li>
    <li>Třídění podle barvy žilky a fáze zralosti (více v článku <a href="/pruvodce/botanika-a-veda/barvy-zil-kratomu">Barvy žil kratomu</a>).</li>
    <li>Sušení v zastíněném větraném prostoru (případně řízená <a href="/pruvodce/botanika-a-veda/fermentace-kratomu">fermentace</a>).</li>
    <li>Mletí — typicky kladívkový nebo nárazový mlýn s následnou sítovou klasifikací.</li>
    <li>Balení do vhodných obalů s ohledem na <a href="/pruvodce/kvalita-a-bezpecnost/skladovani-kratomu">skladovací stabilitu</a>.</li>
</ol>
<p>Konečný produkt obsahuje celou rostlinnou matrici včetně celulózy, polyfenolů a stopových složek. Obsah <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">alkaloidů</a> v prášku odpovídá obsahu v původním listu — typicky 0,5–1,8 % hmotnosti.</p>

<h2>Extrakt (vodní/alkoholová extrakce, sušení)</h2>
<p>Extrakt vzniká koncentrací alkaloidů z prášku. Typický technologický postup:</p>
<ol>
    <li><strong>Extrakce</strong> — kontakt rozemletého listu s rozpouštědlem (voda, ethanol, methanol nebo jejich směs). Extrakce probíhá za míchání, případně s ultrazvukem nebo zahříváním.</li>
    <li><strong>Filtrace</strong> — oddělení nerozpustného rostlinného materiálu (vlákniny, lignin).</li>
    <li><strong>Odpaření rozpouštědla</strong> — vakuové odpařování nebo rozprašovací sušení (spray drying) za sníženého tlaku, aby teplota nepřesáhla 60 °C (zabránění tepelné degradaci alkaloidů).</li>
    <li><strong>Mletí a balení</strong> — výsledný koncentrát se mlecí na jemný prášek.</li>
</ol>
<p>Podle volby rozpouštědla se získá produkt s odlišnou skladbou — vodní extrakce preferuje rozpustné polární alkaloidy, ethanolová extrakce zahrne i méně polární sloučeniny. Methanol není v EU pro spotřebitelské produkty povolen jako reziduální rozpouštědlo.</p>

<h2>Koncentrace alkaloidů (1× vs 10×, 20×)</h2>
<p>Označení <strong>„10× extrakt"</strong> nebo <strong>„20× extrakt"</strong> popisuje surovinový poměr — kolik kilogramů sušeného listu bylo zpracováno na 1 kilogram konečného extraktu:</p>
<ul>
    <li>10× extrakt: 10 kg surového prášku → 1 kg extraktu,</li>
    <li>20× extrakt: 20 kg surového prášku → 1 kg extraktu.</li>
</ul>
<p>Tento poměr <strong>není totožný s poměrem účinných látek</strong>. Skutečný obsah hlavních alkaloidů v extraktu závisí na účinnosti extrakce — typicky není 100 %. U deklarace „10× extrakt" je proto skutečný obsah mitragyninu nižší než desetinásobek obsahu v původním prášku; reálný násobek bývá 5–8× podle technologie. Spolehlivě jej uvádí pouze <a href="/pruvodce/kvalita-a-bezpecnost/coa-kratom-jak-cist">COA konkrétní šarže</a>.</p>

<h2>Standardizace a její limity</h2>
<p><strong>Standardizovaný extrakt</strong> je takový, u kterého výrobce garantuje konkrétní obsah hlavní účinné látky (např. „45% mitragynin"). Standardizace má v technologii rostlinných extraktů několik úrovní:</p>
<ul>
    <li><strong>Surovinová standardizace</strong> — výrobce kontroluje pouze poměr suroviny a extraktu (1:10, 1:20). Nezaručuje konkrétní obsah aktivních látek.</li>
    <li><strong>Standardizace na hlavní marker</strong> — garantován obsah jednoho alkaloidu (typicky mitragyninu) v procentech.</li>
    <li><strong>Full-spectrum standardizace</strong> — garantován obsah více alkaloidů a poměr mezi nimi.</li>
</ul>
<p>Limity standardizace u kratomu:</p>
<ul>
    <li>žádná standardizace nezaručuje plnou reprodukovatelnost — alkaloidní profil suroviny variuje (viz <a href="/pruvodce/botanika-a-veda/alkaloidy-kratomu">Alkaloidní profil kratomu</a>),</li>
    <li>standardizace na jeden marker (mitragynin) ignoruje příspěvek 7-OH-MG i ostatních alkaloidů,</li>
    <li>extrakty s velmi vysokou koncentrací (např. „99% mitragynin") jsou de facto izolované jednotlivé alkaloidy, ne extrakty rostliny — a regulačně se mohou hodnotit jinak.</li>
</ul>

<h2>Často kladené otázky</h2>
<h3>Co znamená „10× extrakt"?</h3>
<p>Surovinový poměr — 10 kilogramů surového prášku bylo použito k výrobě 1 kilogramu extraktu. Skutečný násobek koncentrace mitragyninu bývá nižší (typicky 5–8× kvůli neúplné extrakční výtěžnosti).</p>
<h3>Je extrakt regulován stejně jako prášek?</h3>
<p>V ČR ano. Podle novely zákona č. 167/1998 Sb. účinné od 1. 1. 2026 jsou <em>Mitragyna speciosa</em> i přípravky obsahující její hlavní alkaloidy zařazeny mezi PML — tedy i extrakty a koncentráty. Detailněji v článku <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">Nová regulace kratomu v ČR od 2026</a>.</p>
<h3>Lze extrakt vyrobit doma?</h3>
<p>Z technologického hlediska je principiálně možná jednoduchá extrakce vodou nebo alkoholem, ale bez analytické kontroly nelze garantovat konečný obsah alkaloidů ani <a href="/pruvodce/kvalita-a-bezpecnost/mikrobiologie-kratom">mikrobiologickou bezpečnost</a>. Z hlediska české regulace navíc spadá výroba PML pod licenci podle § 3 zák. 167/1998 Sb.</p>
<h3>Jaký je rozdíl mezi extraktem a tinkturou?</h3>
<p>Tinktura je kapalný roztok extrakčního přípravku, typicky v alkoholu. Extrakt je sušený koncentrát. Z technologického pohledu je tinktura mezistupněm — z ní lze odpařením rozpouštědla získat sušený extrakt.</p>

<h2>Reference</h2>
<ul>
    <li>Hassan Z. et al. (2013). From kratom to mitragynine and its derivatives. <em>Neuroscience &amp; Biobehavioral Reviews</em>, 37(2), 138–151.</li>
    <li>Kruegel A. C., Grundmann O. (2018). The medicinal chemistry and neuropharmacology of kratom. <em>Neuropharmacology</em>, 134, 108–120.</li>
    <li>European Pharmacopoeia, monografie Extracta. Obecné monografie pro rostlinné extrakty.</li>
</ul>
HTML,
        ];
    }
}
