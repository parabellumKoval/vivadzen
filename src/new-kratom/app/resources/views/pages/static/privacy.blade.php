@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.gdpr') }} | Vivadzen"
    description="Ochrana osobních údajů a GDPR ve Vivadzen — jaké údaje zpracováváme, proč a jak je chráníme."
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.pages.gdpr')"
        title="Ochrana osobních údajů"
        lead="Zpracování v souladu s GDPR. Vaše údaje neukládáme bez šifrování a nikdy je neprodáváme."
    />

    <section class="static-section">
        <div class="container container--narrow">
            <article class="prose">
                <h2>1. Správce osobních údajů</h2>
                <p>Vivadzen s.r.o., IČO 00000000, sídlo Praha, ČR. Kontakt pro ochranu osobních údajů: <a href="mailto:gdpr@vivadzen.com">gdpr@vivadzen.com</a>.</p>

                <h2>2. Jaké údaje zpracováváme</h2>
                <ul>
                    <li><strong>Identifikační údaje:</strong> jméno, příjmení, e-mail, telefon</li>
                    <li><strong>Adresní údaje:</strong> doručovací adresa, fakturační adresa</li>
                    <li><strong>Údaje o objednávce:</strong> historie nákupů, COA reporty, status objednávek</li>
                    <li><strong>Technické údaje:</strong> IP adresa, user-agent, cookies</li>
                </ul>

                <h2>3. Účel zpracování</h2>
                <p>Údaje zpracováváme za účelem plnění kupní smlouvy (doručení zboží, fakturace), zákonných povinností (evidence PML, ochrana spotřebitele) a oprávněného zájmu (zlepšování služeb, marketing pouze se souhlasem).</p>

                <h2>4. Doba uchovávání</h2>
                <p>Údaje uchováváme po dobu nezbytně nutnou: údaje o objednávkách 10 let (zákonná povinnost), kontaktní údaje do odvolání souhlasu, marketingové údaje 3 roky od posledního nákupu.</p>

                <h2>5. Vaše práva</h2>
                <ul>
                    <li>Právo na přístup k údajům</li>
                    <li>Právo na opravu nesprávných údajů</li>
                    <li>Právo na výmaz (právo být zapomenut)</li>
                    <li>Právo na přenositelnost údajů</li>
                    <li>Právo vznést námitku</li>
                    <li>Právo podat stížnost u ÚOOÚ</li>
                </ul>

                <h2>6. Předávání údajů</h2>
                <p>Údaje předáváme pouze nezbytným zpracovatelům: kurýrní služby (Messenger, Zásilkovna, PPL), platební partneři (ComGate, GoPay), e-mailové služby (Mailchimp). Údaje nikdy neprodáváme třetím stranám.</p>

                <h2>7. Zabezpečení</h2>
                <p>Veškerá komunikace probíhá přes HTTPS s TLS 1.3. Hesla ukládáme jako bcrypt hash, čísla karet neukládáme vůbec (pouze tokeny platebních partnerů).</p>

                <h2>8. Cookies</h2>
                <p>Detailní zásady použití cookies naleznete na <a href="{{ Locale::url('/cookies') }}">stránce o cookies</a>.</p>

                <p class="prose__signature">Vivadzen s.r.o. · Platné od 1. 1. 2026</p>
            </article>
        </div>
    </section>
</x-layouts.app>
