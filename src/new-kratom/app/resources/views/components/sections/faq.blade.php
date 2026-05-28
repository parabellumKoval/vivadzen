{{-- S11 FAQ — 04_HOMEPAGE.md §13 + FAQPage schema --}}
@php
    $items = [
        [
            'question' => 'Je kratom v Česku legální?',
            'answer'   => '<p>Ano. Od 12. 11. 2025 je kratom regulován jako psychomodulační látka podle zák. č. 167/1998 Sb. (novelizován č. 321/2024 Sb.). Prodej je omezen na licencované provozovny — Vivadzen je autorizovaný prodejce s licencí Ministerstva zdravotnictví ČR.</p>',
        ],
        [
            'question' => 'Jak Vivadzen testuje šarže?',
            'answer'   => '<p>Každá šarže prochází nezávislým testem v akreditované laboratoři VŠCHT Praha podle normy ISO 17025. Testujeme obsah mitragyninu, 7-hydroxymitragyninu, čistotu, mikrobiologii a obsah těžkých kovů. COA je dostupný ke stažení u každého produktu.</p>',
        ],
        [
            'question' => 'Jak rychle dorazí moje objednávka?',
            'answer'   => '<p>V Praze a Ostravě nabízíme EXPRESS doručení do 180 minut (příplatek 299 Kč). Po celé ČR — 1–2 pracovní dny prostřednictvím Zásilkovny, PPL nebo České pošty. Osobní odběr v prodejnách Praha-Vinohrady a Praha-Karlín — zdarma.</p>',
        ],
        [
            'question' => 'Jaké jsou platební možnosti?',
            'answer'   => '<p>Akceptujeme platební karty (Visa, Mastercard), Apple Pay, Google Pay, QR-platbu, dobírku a převod na účet. Všechny online platby probíhají přes SSL-šifrované spojení s 3D Secure ověřením.</p>',
        ],
        [
            'question' => 'Mohu objednat bez registrace?',
            'answer'   => '<p>Ano. Nabízíme jak objednávku jako host (rychlý 3-krokový checkout), tak vytvoření účtu s benefity — uložené dodací adresy, historie objednávek, snadné předplatné a sledování objednávek.</p>',
        ],
        [
            'question' => 'Co je COA a jak ho najdu?',
            'answer'   => '<p>COA (Certificate of Analysis) je oficiální laboratorní protokol konkrétní šarže. Najdete ho u každého produktu pod záložkou «Laboratorní testy» a také v sekci <a href="/laboratorni-testy">/laboratorni-testy</a> filtrované podle šarže. Ke stažení v PDF.</p>',
        ],
    ];
@endphp

<section class="section section--cream-50 faq" aria-labelledby="faq-title">
    <div class="container">
        <div class="faq__grid">
            <div>
                <p class="t-overline section-head__eyebrow--soft">ČASTÉ DOTAZY</p>
                <h2 id="faq-title" class="t-heading-xl t-on-light-accent mt-3">Vše, co potřebujete vědět</h2>
                <p class="t-body-md t-on-light-2 mt-4">Pokud nenajdete odpověď, napište nám na podporu.</p>
                <div class="mt-6">
                    <x-ui.button href="/podpora" variant="text" icon="arrow-right">Kontaktovat podporu</x-ui.button>
                </div>
            </div>

            <x-ui.accordion :items="$items" />
        </div>
    </div>
</section>

@push('schema')
    <script type="application/ld+json">
    {
      "{{ '@' }}context": "https://schema.org",
      "{{ '@' }}type": "FAQPage",
      "mainEntity": [
        @foreach($items as $i)
          {
            "{{ '@' }}type": "Question",
            "name": @json($i['question']),
            "acceptedAnswer": {
              "{{ '@' }}type": "Answer",
              "text": @json(strip_tags($i['answer']))
            }
          }@if(!$loop->last),@endif
        @endforeach
      ]
    }
    </script>
@endpush
