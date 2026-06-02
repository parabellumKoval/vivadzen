<?php

namespace Database\Seeders;

use App\Models\WikiArticle;
use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

/**
 * Контент wiki, категория «Historie a kultura» (5 статей).
 *
 * Stříz neutralní, historicko-etnobotanický, бо без CTA, bez doporučení.
 */
class WikiContentHistorieSeeder extends Seeder
{
    public function run(): void
    {
        $category = WikiCategory::where('slug', 'historie-a-kultura')->firstOrFail();

        $articles = [
            $this->historieKratomu(),
            $this->kratomThajskoHistorie(),
            $this->kratomIndonesie(),
            $this->kratomMalajsieKetum(),
            $this->etnobotanikaKratom(),
        ];

        $created = [];
        foreach ($articles as $position => $a) {
            $created[$a['slug']] = WikiArticle::updateOrCreate(
                ['slug' => $a['slug']],
                array_merge($a, [
                    'wiki_category_id' => $category->id,
                    'position' => ($position + 1) * 10,
                    'status' => 'published',
                    'published_at' => now()->subDays(20 - $position),
                ]),
            );
        }

        $links = [
            'historie-kratomu' => ['co-je-kratom', 'kratom-thajsko-historie', 'kratom-indonesie', 'etnobotanika-kratom'],
            'kratom-thajsko-historie' => ['historie-kratomu', 'etnobotanika-kratom', 'kratom-malajsie-ketum'],
            'kratom-indonesie' => ['kde-roste-kratom', 'historie-kratomu', 'dovoz-kratomu-cr'],
            'kratom-malajsie-ketum' => ['historie-kratomu', 'kratom-thajsko-historie', 'etnobotanika-kratom'],
            'etnobotanika-kratom' => ['historie-kratomu', 'kratom-malajsie-ketum', 'kratom-thajsko-historie', 'kde-roste-kratom'],
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

    private function historieKratomu(): array
    {
        return [
            'slug' => 'historie-kratomu',
            'title' => 'Historie kratomu: od jihovýchodní Asie po Evropu',
            'excerpt' => 'Kratom (Mitragyna speciosa) je v jihovýchodní Asii dokumentován po staletí. Vědecky jej popsal Korthals v roce 1839, v Evropě se rozšířil po roce 2000.',
            'seo_keyword' => 'historie kratomu',
            'seo_secondary_keywords' => ['kratom historie', 'kratom vznik', 'historie mitragyna speciosa'],
            'seo_meta_title' => 'Historie kratomu — od jihovýchodní Asie po Evropu | Vivadzen Průvodce',
            'seo_meta_description' => 'Historie kratomu: tradiční použití v JV Asii, první evropské zmínky (Korthals 1839), zákazy 20. století, globalizace po roce 2000.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Strom <em>Mitragyna speciosa</em> je v jihovýchodní Asii historicky dokumentován po staletí, evropská vědecká literatura jej popisuje od roku 1839. Ve 20. století začaly některé asijské státy uplatňovat zákazy (Thajsko 1943, Malajsie 1952). Po roce 2000 se kratom rozšířil v Severní Americe a v Evropě, kde se v poslední dekádě stal předmětem regulační debaty.</p>

<h2>Předkoloniální použití v jihovýchodní Asii</h2>
<p>Tradiční dokumenty z jižního Thajska a poloostrovní Malajsie zmiňují žvýkání čerstvého listu <em>Mitragyna speciosa</em> mezi farmáři, dělníky na rýžových polích a kaučukových plantážích. Cílem byla zmírnění únavy při dlouhé fyzické práci v tropickém klimatu. List byl zpracováván i jako odvar — část etnobotanické literatury popisuje použití při horečnatých stavech či jako tradiční prostředek vesnické medicíny.</p>
<p>V Indonésii (na Borneu a Sumatře) byl strom v polokulturní formě vysazován kolem vesnic. Etnobotanická pozorování z konce 19. století (Korthals, později Burkill) zaznamenávají využití listu jako stimulantu a součásti místních zvyků. Detailní popis tradičních praktik v JV Asii poskytuje článek <a href="/pruvodce/historie-a-kultura/etnobotanika-kratom">Etnobotanika kratomu v jihovýchodní Asii</a>.</p>

<h2>První evropské zmínky (Korthals 1839)</h2>
<p>Vědecký popis druhu publikoval nizozemský botanik <strong>Pieter Willem Korthals</strong> v roce 1839 v práci <em>Observationes de Naucleis Indicis</em>, vydané v Bataviaasch Genootschap van Kunsten en Wetenschappen. Korthals popsal morfologii listu, kvety i kulové hlavičky květenství a druh zařadil do rodu <em>Mitragyna</em>.</p>
<p>V druhé polovině 19. století navštěvovali britští botanici (zvláště v rámci Royal Botanic Gardens, Kew) plantáže v Britské Malajsii a sbírali herbářový materiál. Prvním vědcem, který izoloval hlavní alkaloid <a href="/pruvodce/botanika-a-veda/mitragynin">mitragynin</a>, byl <strong>Edmund Field</strong> v roce 1921. Strukturu mitragyninu definitivně potvrdili Zacharias et al. v roce 1965.</p>

<h2>20. století — výzkum a první zákazy</h2>
<p>Thajsko zavedlo zákaz kratomu v roce <strong>1943</strong> (tzv. Kratom Act, B.E. 2486). Politické důvody jsou popisovány v historických pracích Tanguaye (2011): kratom byl v té době lacinějším substitutem opia, jehož zdanění tvořilo významnou část státních příjmů. Detailní rozbor poskytuje článek <a href="/pruvodce/historie-a-kultura/kratom-thajsko-historie">Kratom v Thajsku: tradice, zákazy a návrat</a>.</p>
<p>Malajsie v roce 1952 zařadila kratom (lokálně <em>ketum</em>) na seznam kontrolovaných látek podle <em>Poisons Act 1952</em>. V téže době pokračoval základní fytochemický výzkum v Evropě a Japonsku — popis dalších alkaloidů (speciogynin, paynantheine) zajistili Beckett a kol. v 60. letech.</p>
<p>V druhé polovině 20. století zůstával kratom prakticky neznámý mimo region. Globální vědecká pozornost rostla až s identifikací <a href="/pruvodce/botanika-a-veda/7-hydroxymitragynin">7-hydroxymitragyninu</a> (Takayama 2002).</p>

<h2>Globalizace po roce 2000</h2>
<p>Po roce 2000 začal kratom přicházet do Severní Ameriky a Evropy především jako rostlinný prášek dodávaný drobnými dovozci. V USA byl předmětem opakovaných diskusí o regulaci (DEA scheduling 2016, který nakonec nebyl realizován). V Evropě zůstával v řadě zemí bez specifické regulace, zatímco Polsko, Litva a Lotyšsko zavedly úplný zákaz.</p>
<p>V <strong>České republice</strong> se kratom objevuje v širším obchodu orientačně kolem roku 2014. Po několika letech volného trhu připravila ČR novelu <em>zákona č. 167/1998 Sb., o návykových látkách</em>, která od 1. ledna 2026 zařazuje kratom do nové kategorie psychomodulačních látek (PML). Aktuální stav popisuje <a href="/pruvodce/legislativa-cr/kratom-zakon-cesko-2026">Nová regulace kratomu v ČR od 2026</a>.</p>

<h2>Často kladené otázky</h2>
<h3>Kdy se kratom poprvé objevil v Evropě?</h3>
<p>Jako vědecky popsaný druh v roce 1839 (Korthals). Jako spotřební produkt v širším obchodě zhruba od počátku 21. století.</p>
<h3>Kdo kratom popsal vědecky?</h3>
<p>Pieter Willem Korthals v roce 1839. Hlavní alkaloid mitragynin izoloval Edmund Field v roce 1921.</p>
<h3>Kdy se objevil v ČR?</h3>
<p>V širším obchodním měřítku orientačně kolem roku 2014. Přesné datum prvního dovozu není v dostupných pramenech jednoznačně dokumentováno.</p>
<h3>Existovala v historii nějaká „kratomová kultura" mimo JV Asii?</h3>
<p>Ne v dokumentovaném smyslu. Mimo původní areál byl kratom prakticky neznámý až do druhé poloviny 20. století.</p>

<h2>Reference</h2>
<ul>
    <li>Korthals P. W. (1839). <em>Observationes de Naucleis Indicis</em>. Verhandelingen van het Bataviaasch Genootschap van Kunsten en Wetenschappen.</li>
    <li>Tanguay P. (2011). Kratom in Thailand: Decriminalisation and Community Control? <em>Series on Legislative Reform of Drug Policies</em>, No. 13. Transnational Institute.</li>
    <li>Cinosi E. et al. (2015). Following "the Roots" of Kratom. <em>BioMed Research International</em>, 2015, 968786.</li>
</ul>
HTML,
        ];
    }

    private function kratomThajskoHistorie(): array
    {
        return [
            'slug' => 'kratom-thajsko-historie',
            'title' => 'Kratom v Thajsku: tradice, zákazy a návrat',
            'excerpt' => 'Thajsko zakázalo kratom v roce 1943 (Kratom Act, B.E. 2486). V roce 2018 byl povolen pro lékařské použití, v roce 2021 dekriminalizován pro běžné použití.',
            'seo_keyword' => 'kratom thajsko',
            'seo_secondary_keywords' => ['kratom thajsko zákon', 'kratom thajsko legalizace', 'kratom act 1943'],
            'seo_meta_title' => 'Kratom v Thajsku — historie tradice, zákazu a návratu | Vivadzen Průvodce',
            'seo_meta_description' => 'Historie kratomu v Thajsku: tradiční role v jižních provinciích, zákaz 1943, lékařské povolení 2018 a dekriminalizace 2021. Současný regulační stav.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Thajsko představuje dokumentačně nejbohatší historický kontext kratomu. Strom je v jižních provinciích součástí tradičního zemědělského života po staletí. Po desetiletích zákazu (1943–2021) Thajsko v roce 2021 dekriminalizovalo držení a domácí pěstování listu pro běžné použití.</p>

<h2>Tradiční role v jižním Thajsku</h2>
<p>V provinciích <strong>Surat Thani</strong>, <strong>Nakhon Si Thammarat</strong> a <strong>Songkhla</strong> byl kratom (thajsky <em>kratom</em> nebo <em>thom</em>) běžnou součástí každodenního života farmářů a dělníků na kaučukových plantážích. Tradiční praxí bylo žvýkání čerstvého listu — popisováno jako prostředek proti únavě při dlouhých směnách v tropickém klimatu. Etnografické práce (Tanguay 2011, Charoenratana 2022) uvádějí, že kratom byl integrován do venkovských komunitních setkání a obřadů, ne do rekreační kultury měst.</p>
<p>Vzhledem k povaze tradičního použití (čerstvý list, ne koncentrovaný extrakt) byla expozice alkaloidům řádově nižší než u moderních koncentrátů. Detailněji v článku <a href="/pruvodce/historie-a-kultura/etnobotanika-kratom">Etnobotanika kratomu v jihovýchodní Asii</a>.</p>

<h2>Zákaz 1943 (Kratom Act, B.E. 2486)</h2>
<p>V roce 1943 přijala thajská vláda <strong>Kratom Act, B.E. 2486</strong>, kterým bylo zakázáno pěstování stromu, držení listu i jeho prodej. Historické analýzy (Tanguay 2011) identifikují jako klíčový důvod ekonomický tlak: kratom byl lacinějším substitutem opia, jehož státní monopol tvořil významný zdroj příjmů vlády. Politické zdůvodnění zákazu zmiňovalo veřejné zdraví, ale archivní materiály ukazují primárně fiskální motivaci.</p>
<p>Zákaz byl v praxi obtížně vymahatelný — v jižních provinciích pokračovalo tradiční použití i přes formální nelegalitu, a stromy přežívaly v polokulturních porostech kolem vesnic.</p>

<h2>Postupné uvolnění (2018 lékařské použití, 2021 dekriminalizace)</h2>
<p>V <strong>roce 2018</strong> přijalo Thajsko novelu zákona o narkoticích, která umožnila lékařské použití kratomu pod dohledem ministerstva zdravotnictví. Šlo o součást širší reformy, která povolila i lékařské použití konopí. Kratom byl v rámci této novely klasifikován jako léčebná rostlina s omezením distribuce přes registrovaná zařízení.</p>
<p>V <strong>roce 2021</strong> následovala plnohodnotná <strong>dekriminalizace</strong> — kratom byl vyřazen ze seznamu narkotik kategorie V a držení listu pro osobní použití přestalo být trestným činem. Pěstování domácích stromů bylo povoleno bez licence (do několika kusů na domácnost), komerční prodej zůstal regulován.</p>

<h2>Současný stav</h2>
<p>K roku 2025–2026 platí v Thajsku tento režim:</p>
<ul>
    <li>Držení čerstvého listu pro osobní použití je legální.</li>
    <li>Domácí pěstování několika stromů je povoleno.</li>
    <li>Prodej a komerční distribuce vyžaduje registraci u thajského FDA.</li>
    <li>Export do třetích zemí podléhá zvláštním povolením.</li>
    <li>Reklama na kratom směrovaná na nezletilé je zakázána.</li>
</ul>
<p>Thajsko je dnes vedle Indonésie významným producentem kratomu, byť jeho exportní objem zůstává řádově nižší. Více v článku <a href="/pruvodce/historie-a-kultura/kratom-indonesie">Indonésie jako světový dodavatel kratomu</a>.</p>

<h2>Často kladené otázky</h2>
<h3>Proč Thajsko zakázalo kratom v roce 1943?</h3>
<p>Historické analýzy poukazují na ekonomické důvody — kratom byl lacinějším substitutem opia, jehož státní monopol tvořil významný zdroj rozpočtových příjmů. Veřejné zdraví bylo uvedeno jako sekundární zdůvodnění.</p>
<h3>Kdo o návrat usiloval?</h3>
<p>Reforma byla podpořena akademickou obcí (zejména výzkumníky z thajských univerzit) a komunitními skupinami z jižních provincií. Politicky byla součástí širší reformy drogové politiky vedené ministerstvem spravedlnosti.</p>
<h3>Je dnes kratom v Thajsku legální?</h3>
<p>Ano, od roku 2021 je dekriminalizovaný pro osobní použití a domácí pěstování. Komerční prodej zůstává regulován.</p>
<h3>Lze si přivézt kratom z Thajska do EU?</h3>
<p>Z hlediska thajského práva ano (s export-licencí), z hlediska cílového státu EU závisí na národní regulaci. V ČR po 1. 1. 2026 platí <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">režim PML pro dovoz</a>.</p>

<h2>Reference</h2>
<ul>
    <li>Tanguay P. (2011). Kratom in Thailand: Decriminalisation and Community Control? <em>Series on Legislative Reform of Drug Policies</em>, No. 13. Transnational Institute.</li>
    <li>Charoenratana S. et al. (2022). Attitudes towards Kratom Use, Decriminalization and the Sustainable Development Goals in Thailand. <em>International Journal of Drug Policy</em>, 99, 103473.</li>
    <li>Singh D. et al. (2016). Traditional and non-traditional uses of <em>Mitragyna speciosa</em>. <em>Journal of Ethnopharmacology</em>, 192, 24–34.</li>
</ul>
HTML,
        ];
    }

    private function kratomIndonesie(): array
    {
        return [
            'slug' => 'kratom-indonesie',
            'title' => 'Indonésie jako světový dodavatel kratomu',
            'excerpt' => 'Indonésie pokrývá 70–90 % světové produkce kratomu, převážně z Kalimantanu a Sumatry. Plánovaný zákaz vývozu se opakovaně odkládá.',
            'seo_keyword' => 'kratom indonésie',
            'seo_secondary_keywords' => ['kratom borneo', 'kratom sumatra', 'kratom indonesie dovoz'],
            'seo_meta_title' => 'Kratom v Indonésii — světový dodavatel z Kalimantanu | Vivadzen Průvodce',
            'seo_meta_description' => 'Indonésie jako světový dodavatel kratomu: geografie produkce (Kalimantan, Sumatra), ekonomický význam pro farmáře, debata o zákazu vývozu.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>Indonésie je k roku 2025 dominantním světovým producentem kratomu — pokrývá odhadem <strong>70–90 % světové dodávky</strong>. Centrem produkce je provincie <strong>Západní Kalimantan</strong> na ostrově Borneo, doplněná o oblasti na <strong>Sumatře</strong> a v menší míře na <strong>Jávě</strong>. Indonésie debatuje o regulaci vývozu od roku 2019; konkrétní termíny zákazu byly opakovaně odloženy.</p>

<h2>Geografie produkce</h2>
<h3>Kalimantan</h3>
<p>Indonéská část Bornea je geograficky a klimaticky ideální oblastí pro <em>Mitragyna speciosa</em>: trvale vlhké tropické klima, nížiny kolem řek Kapuas a Mahakam, hluboké aluviální půdy. Tradičně byl strom součástí venkovské krajiny v okolí vesnic — kombinace polokulturního výsadku a sběru z planých populací.</p>
<p>Od počátku 21. století se v <strong>Západním Kalimantanu</strong> (oblasti Kapuas Hulu, Sintang, Melawi) rozvinul organizovaný export kratomového listu. Sběr i prvotní zpracování (sušení, mletí) zajišťují malí farmáři, kteří dodávají do regionálních konsolidačních středisek.</p>
<h3>Sumatra</h3>
<p>Druhým významným regionem je Sumatra, zejména provincie Aceh a Severní Sumatra. Produkce je rozmělněnější než na Borneu a opírá se více o tradiční vesnické porosty. Komerční objem je řádově nižší než u Bornea.</p>
<h3>Jáva</h3>
<p>Jáva přispívá menším objemem (řádově jednotky procent celkové indonéské produkce), zejména z polokulturních porostů ve východní části ostrova. Více o přirozeném areálu v článku <a href="/pruvodce/botanika-a-veda/kde-roste-kratom">Přirozený areál Mitragyna speciosa</a>.</p>

<h2>Ekonomický význam pro místní farmáře</h2>
<p>Sběr a prvotní zpracování kratomového listu poskytuje příjem statisícům rodin v Západním Kalimantanu. Lokální analýzy (Singh 2019, FAO 2021) odhadují, že:</p>
<ul>
    <li>v některých vesnicích Kapuas Hulu pochází 30–60 % rodinného příjmu z kratomu,</li>
    <li>průměrný farmář dodává řádově desítky až stovky kilogramů sušeného listu měsíčně,</li>
    <li>regionální exportní hodnota se odhaduje v desítkách až nižších stovkách milionů USD ročně.</li>
</ul>
<p>Tento ekonomický rozměr je hlavním politickým argumentem proti rychlému zákazu — alternativní zemědělské plodiny (kaučuk, palmový olej) mají v dané oblasti nižší výnosy.</p>

<h2>Plánovaný zákaz vývozu (debata 2019–2024)</h2>
<p>Indonéská národní agentura pro kontrolu narkotik (BNN) navrhla v roce 2019 zařazení kratomu na seznam narkotik kategorie I, což by efektivně znamenalo zákaz pěstování i vývozu. Ministerstvo zemědělství a regionální vlády Kalimantanu se proti tomuto návrhu postavily s odkazem na ekonomické dopady.</p>
<p>Termín plánovaného zákazu byl opakovaně odložen — naposledy o 5 let od původně plánovaného účinku. K roku 2025 je status kratomu v indonéském právu stále nedořešený: kratom není zařazen mezi narkotika, ale ministerstvo zdravotnictví připravuje samostatnou regulaci pro export.</p>

<h2>Logistika a regulace exportu</h2>
<p>Vývoz kratomu z Indonésie probíhá z přístavů Pontianak (Kalimantan), Belawan (Severní Sumatra) a Jakarta. Pro zahraniční dovozce (včetně subjektů v <a href="/pruvodce/legislativa-cr/dovoz-kratomu-cr">České republice</a>) jsou klíčové tyto dokumenty:</p>
<ul>
    <li>indonéská <em>exportní licence</em> vydávaná ministerstvem obchodu,</li>
    <li>phytosanitární certifikát z indonéské karanténní agentury,</li>
    <li>certifikát původu (Form A nebo Form D),</li>
    <li>certifikát analýzy (COA) vzorku šarže.</li>
</ul>
<p>Tyto požadavky jsou obecné celní dokumenty a samy o sobě neřeší shodu s regulací cílové země.</p>

<h2>Často kladené otázky</h2>
<h3>Proč Indonésie dominuje produkci?</h3>
<p>Tři důvody: dosud volný právní režim, ideální klima Bornea a ekonomická závislost statisíců rodin v Kalimantanu.</p>
<h3>Plánuje Indonésie zákaz vývozu?</h3>
<p>Debata probíhá od roku 2019, ale termín byl opakovaně odložen. K roku 2025 zůstává vývoz legální při splnění standardních exportních dokumentů.</p>
<h3>Jaký je podíl Bornea na celkové produkci?</h3>
<p>Odhady se liší podle zdroje, ale Borneo (zejména Západní Kalimantan) tvoří většinu indonéské produkce — odhadem 70–85 %.</p>
<h3>Existuje certifikační systém pro indonéský kratom?</h3>
<p>Národní certifikace dosud zavedena nebyla. Zahraniční dovozci pracují s privátními laboratorními protokoly a vlastními audity dodavatelů.</p>

<h2>Reference</h2>
<ul>
    <li>Singh D. et al. (2019). Kratom (<em>Mitragyna speciosa</em>) use in Malaysia and Thailand. <em>Drug and Alcohol Review</em>, 38(1), 81–88.</li>
    <li>FAO (2021). Non-Wood Forest Products in Southeast Asia: Country Reports — Indonesia. Food and Agriculture Organization.</li>
    <li>Cinosi E. et al. (2015). Following "the Roots" of Kratom. <em>BioMed Research International</em>, 2015, 968786.</li>
</ul>
HTML,
        ];
    }

    private function kratomMalajsieKetum(): array
    {
        return [
            'slug' => 'kratom-malajsie-ketum',
            'title' => 'Kratom v Malajsii: ketum a místní kontext',
            'excerpt' => 'V Malajsii je kratom lokálně známý jako ketum nebo biak-biak. Pěstování i držení podléhají Poisons Act 1952, debata o legalizaci je otevřená.',
            'seo_keyword' => 'kratom malajsie',
            'seo_secondary_keywords' => ['ketum', 'kratom malajsie zákon', 'biak-biak'],
            'seo_meta_title' => 'Kratom v Malajsii — ketum a místní regulační kontext | Vivadzen Průvodce',
            'seo_meta_description' => 'Kratom v Malajsii: lokální pojmenování ketum, regulace podle Poisons Act 1952, tradiční použití a aktuální debata o legalizaci.',
            'reading_time_minutes' => 5,
            'body' => <<<'HTML'
<h2>Stručně</h2>
<p>V Malajsii se <em>Mitragyna speciosa</em> tradičně označuje jako <strong>ketum</strong>, méně často <em>biak-biak</em>. Strom je domácí na poloostrově Malajsie a na malajsijské části Bornea. Od roku 1952 je předmětem regulace podle <em>Poisons Act 1952</em>; debata o legalizaci pokračuje, ale k roku 2025 nedošlo k zásadní změně.</p>

<h2>Místní pojmenování „ketum"</h2>
<p>Slovo <em>ketum</em> je v malajštině a v některých dialektech používaných v severních státech Malajsie (Kedah, Penang, Kelantan) označením pro list <em>Mitragyna speciosa</em>. V některých oblastech se používá také výraz <em>biak-biak</em>. Etymologie slova ketum není v dostupných pramenech jednoznačně doložená.</p>
<p>V historických materiálech britské koloniální správy (Burkill, 1935: <em>A Dictionary of the Economic Products of the Malay Peninsula</em>) je rostlina popisována jako součást tradiční vesnické farmacie a zemědělství.</p>

<h2>Zákonné omezení od 1952 (Poisons Act)</h2>
<p>V <strong>roce 1952</strong> zařadila Federace Malajska kratom mezi kontrolované látky podle <em>Poisons Act 1952</em>. Pěstování, držení i distribuce listu vyžaduje povolení a porušení může být sankcionováno pokutou i odnětím svobody. Konkrétní výše sankcí byly v průběhu let aktualizovány.</p>
<p>Na rozdíl od Thajska, kde byl v roce 2021 zaveden plnohodnotný režim dekriminalizace, malajsijská regulace zůstává restriktivní. V některých severních státech (zejména v Kelantanu) ovšem existuje rozsáhlé tradiční využití, které policie a regulační orgány v praxi tolerují.</p>

<h2>Současná debata o legalizaci</h2>
<p>Od roku 2018 probíhá v Malajsii akademická i politická debata o případné reformě regulace kratomu. Argumenty pro liberalizaci:</p>
<ul>
    <li>tradiční role v jižních a severních provinciích,</li>
    <li>ekonomický potenciál pro malé farmáře (analogie indonéského modelu),</li>
    <li>výsledky thajské reformy 2021 (politické a sociální dopady).</li>
</ul>
<p>Argumenty proti:</p>
<ul>
    <li>obavy z přechodu z tradičního čerstvého listu na koncentrované extrakty,</li>
    <li>obtížnost mezinárodní harmonizace s ASEAN sousedy,</li>
    <li>tlak některých náboženských autorit.</li>
</ul>
<p>K roku 2025 nebyla žádná novela přijata; status quo Poisons Act 1952 zůstává v platnosti.</p>

<h2>Tradiční použití</h2>
<p>Etnografické práce (Hassan 2013, Singh 2014) popisují tradiční použití ketumu v severních a východních státech (Kelantan, Terengganu, Kedah). Stejně jako v jižním Thajsku jde především o žvýkání čerstvého listu mezi farmáři a rybáři jako prostředek proti únavě.</p>
<p>V některých vesnicích byl odvar z listu používán i v rámci tradiční vesnické farmacie — tato praxe je nicméně omezena na konkrétní lokality a nelze ji zobecnit na celou Malajsii. Bližší kontext popisuje <a href="/pruvodce/historie-a-kultura/etnobotanika-kratom">Etnobotanika kratomu v jihovýchodní Asii</a>.</p>

<h2>Často kladené otázky</h2>
<h3>Proč se v Malajsii říká „ketum"?</h3>
<p>Jde o lokální malajské pojmenování listu <em>Mitragyna speciosa</em>. Přesná etymologie není v dostupných pramenech doložena.</p>
<h3>Je kratom v Malajsii nelegální?</h3>
<p>Ano, od roku 1952 je předmětem regulace podle Poisons Act. Pěstování, držení i distribuce vyžadují povolení.</p>
<h3>Existuje šedý trh?</h3>
<p>V severních státech (Kelantan, Kedah) je tradiční použití místně rozšířené i přes formální nelegalitu. Etnografické studie (Singh 2014) odhadují, že desítky tisíc obyvatel pravidelně užívají list i v podmínkách právního zákazu.</p>
<h3>Liší se malajsijský a thajský přístup?</h3>
<p>Ano. Thajsko v roce 2021 dekriminalizovalo držení listu pro osobní použití, Malajsie udržuje restriktivní režim podle Poisons Act 1952. Více v článku <a href="/pruvodce/historie-a-kultura/kratom-thajsko-historie">Kratom v Thajsku</a>.</p>

<h2>Reference</h2>
<ul>
    <li>Singh D. et al. (2014). Kratom (<em>Mitragyna speciosa</em>): Patterns of use and prevalence among rural communities in Malaysia. <em>Journal of Ethnopharmacology</em>, 154(2), 367–372.</li>
    <li>Hassan Z. et al. (2013). From kratom to mitragynine and its derivatives. <em>Neuroscience &amp; Biobehavioral Reviews</em>, 37(2), 138–151.</li>
    <li>Burkill I. H. (1935). <em>A Dictionary of the Economic Products of the Malay Peninsula</em>. Crown Agents for the Colonies, London.</li>
</ul>
HTML,
        ];
    }

    private function etnobotanikaKratom(): array
    {
        return [
            'slug' => 'etnobotanika-kratom',
            'title' => 'Etnobotanika kratomu v jihovýchodní Asii',
            'excerpt' => 'Etnobotanická literatura popisuje kratom jako tradiční zemědělský stimulant v jižním Thajsku, Malajsii a Indonésii. Tradice se liší od moderního rekreačního použití.',
            'seo_keyword' => 'etnobotanika kratom',
            'seo_secondary_keywords' => ['kratom tradice', 'kratom kultura asie', 'kratom žvýkání listu'],
            'seo_meta_title' => 'Etnobotanika kratomu v jihovýchodní Asii | Vivadzen Průvodce',
            'seo_meta_description' => 'Etnobotanika kratomu: tradiční role v zemědělství JV Asie, sociální kontext, srovnání s betelem a kavou. Rozdíl od moderního použití.',
            'reading_time_minutes' => 6,
            'body' => <<<'HTML'
<h2>Co je etnobotanika</h2>
<p>Etnobotanika je interdisciplinární obor na pomezí botaniky, antropologie a etnografie, který zkoumá <strong>vztah lidských společenství k rostlinám</strong> — jejich tradiční využití, kulturní role, klasifikační systémy a předávání znalostí. V kontextu kratomu se etnobotanická literatura zabývá především dokumentací tradičního použití v jihovýchodní Asii před zavedením moderních regulací.</p>

<h2>Tradiční role v zemědělství JV Asie</h2>
<p>Etnografické práce (Tanguay 2011, Singh 2014, Charoenratana 2022) shodně popisují, že v jižním <a href="/pruvodce/historie-a-kultura/kratom-thajsko-historie">Thajsku</a>, severní <a href="/pruvodce/historie-a-kultura/kratom-malajsie-ketum">Malajsii</a> a v některých částech <a href="/pruvodce/historie-a-kultura/kratom-indonesie">indonéského Bornea</a> bylo tradičním způsobem použití <strong>žvýkání několika čerstvých listů</strong> denně mezi farmáři a dělníky na kaučukových plantážích.</p>
<p>Cílem byla zmírnění únavy při dlouhých pracovních směnách v tropickém klimatu. Vzhledem k tomu, že tradiční forma spočívala v žvýkání čerstvého listu (a nikoliv v koncentrovaném extraktu), byla expozice alkaloidům řádově nižší než u moderních koncentrovaných produktů. Tato kvantitativní odlišnost je pro pochopení historického kontextu zásadní.</p>

<h2>Sociální kontext</h2>
<p>Tradiční použití kratomu nebylo v etnografické literatuře dokumentováno jako rekreační. Charoenratana (2022) v rozhovorech s obyvateli jižního Thajska zaznamenává následující charakteristiky tradičního použití:</p>
<ul>
    <li><strong>Pracovní</strong>, ne rekreační — list se používal v souvislosti s polní prací.</li>
    <li><strong>Mužský</strong> — etnografická data zaznamenávají převahu mužských uživatelů (zemědělců a dělníků); ženy se k užívání hlásily jen okrajově.</li>
    <li><strong>Komunitní</strong> — žvýkání listu probíhalo často při společenské konverzaci, na rozdíl od izolovaného moderního použití koncentrátů.</li>
    <li><strong>Integrované do běžného dne</strong>, ne při speciálních příležitostech (na rozdíl od některých rituálních rostlin).</li>
</ul>
<p>Sociologická data ze severní Malajsie (Singh 2014) ukazují podobný vzorec — kratom byl integrován do vesnického života, ne do městské subkultury.</p>

<h2>Srovnání s jinými lokálními rostlinami</h2>
<p>V regionálním kontextu se kratom srovnává především s těmito tradičními rostlinami:</p>
<h3>Betel (<em>Areca catechu</em>)</h3>
<p>Žvýkání arekového oříšku s listy pepřovníku betelového (<em>Piper betle</em>) je v JV Asii historicky výrazně rozšířenější než kratom — týká se stovek milionů lidí napříč Indií, Bangladéšem, JV Asií i Tichomořím. Sociální role je podobně komunitní, ale věkové i pohlavní rozšíření je širší.</p>
<h3>Kava (<em>Piper methysticum</em>)</h3>
<p>Kava je tradiční rostlina Tichomoří (Fiji, Vanuatu, Tonga), nikoliv JV Asie. Z hlediska sociální role je její použití však obdobné — komunitní, rituální i běžné. Srovnání kratomu a kavy se občas objevuje v etnobotanické literatuře jako příklad regionálně specifických stimulantů.</p>
<h3>Camellia sinensis (čaj)</h3>
<p>Čaj má v JV Asii souběžnou tradici, ale historicky byl rozšířenější ve městech a obchodu. Kratom zůstal především vesnickou rostlinou.</p>

<h2>Etnobotanická dokumentace versus moderní použití</h2>
<p>Zásadní je nezaměňovat etnobotanicky doložené tradiční použití (žvýkání čerstvého listu při polní práci) s moderním globálním fenoménem konzumace koncentrovaných extraktů, který nemá v tradiční kultuře JV Asie přímý ekvivalent. Etnobotanická literatura tento rozdíl opakovaně zdůrazňuje (Tanguay 2011, Singh 2019).</p>

<h2>Často kladené otázky</h2>
<h3>Pili lidé kratom jako čaj?</h3>
<p>Občas ano — především jako vesnický odvar v severní Malajsii a jižním Thajsku. Dominantní tradiční formou bylo však žvýkání čerstvého listu, ne pití nálevu.</p>
<h3>Užívaly kratom ženy?</h3>
<p>Etnografická data zaznamenávají převahu mužských uživatelů (farmáři, dělníci). Ženské užívání bylo okrajové a obvykle mimo komunitní setkání.</p>
<h3>Bylo to běžné, nebo výjimečné?</h3>
<p>V některých lokalitách jižního Thajska a severní Malajsie velmi běžné — desítky procent dospělé mužské populace ve vesnici. V jiných regionech (Jáva, městské oblasti) prakticky neznámé.</p>
<h3>Existují podobné rostliny v české tradici?</h3>
<p>Ne s totožnou alkaloidovou strukturou. Z hlediska sociální role komunitních stimulantů by snad bylo možné srovnat tradiční vesnické použití mátového či zázvorového nápoje, ale farmakologická paralela neexistuje.</p>

<h2>Reference</h2>
<ul>
    <li>Tanguay P. (2011). Kratom in Thailand: Decriminalisation and Community Control? <em>Series on Legislative Reform of Drug Policies</em>, No. 13. Transnational Institute.</li>
    <li>Singh D. et al. (2019). Kratom (<em>Mitragyna speciosa</em>) use in Malaysia and Thailand. <em>Drug and Alcohol Review</em>, 38(1), 81–88.</li>
    <li>Charoenratana S. et al. (2022). Attitudes towards Kratom Use, Decriminalization and the Sustainable Development Goals in Thailand. <em>International Journal of Drug Policy</em>, 99, 103473.</li>
</ul>
HTML,
        ];
    }
}
