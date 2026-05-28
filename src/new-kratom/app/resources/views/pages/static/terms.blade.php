@php use App\Support\Locale; @endphp

<x-layouts.app
    title="{{ __('site.pages.podmínky') }} | Vivadzen"
    description="Obchodní podmínky e-shopu Vivadzen — práva a povinnosti, doručení, vrácení, ochrana spotřebitele."
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.pages.podmínky')"
        title="Obchodní podmínky"
        lead="Platné od 1. 1. 2026. Účinnost těchto podmínek pro každý nákup uskutečněný na vivadzen.com."
    />

    <section class="static-section">
        <div class="container container--narrow">
            <article class="prose">
                <h2>1. Úvodní ustanovení</h2>
                <p>Tyto obchodní podmínky upravují vztahy mezi prodávajícím (Vivadzen s.r.o., IČO 00000000, sídlo Praha) a kupujícím při nákupu zboží prostřednictvím e-shopu na adrese vivadzen.com.</p>

                <h2>2. Uzavření kupní smlouvy</h2>
                <p>Kupní smlouva vzniká odesláním objednávky kupujícím a jejím potvrzením prodávajícím. Kupující je povinen uvést pravdivé údaje a potvrdit věk 18+. Vivadzen prodává psychomodulační látky (PML) podle zák. č. 167/1998 Sb. výhradně osobám starším 18 let.</p>

                <h2>3. Cena a platba</h2>
                <p>Ceny uvedené na webu jsou konečné, včetně DPH (21 %). Platba je možná online kartou, QR kódem, bankovním převodem nebo na dobírku. Platební údaje jsou tokenizované u poskytovatele platebních služeb, čísla karet u nás neukládáme.</p>

                <h2>4. Doručení</h2>
                <p>Doručujeme po celé ČR kurýrem (1–3 prac. dny), v Praze a Ostravě Express 180 min, nebo osobní odběr na prodejnách v Praze. Při doručení proběhne ověření věku 18+. Detailní podmínky viz <a href="{{ Locale::url('/doruceni') }}">Doručení a platba</a>.</p>

                <h2>5. Odstoupení od smlouvy</h2>
                <p>Kupující má právo odstoupit od smlouvy do 14 dní bez udání důvodu. Zboží musí být nepoškozené, v původním obalu a hygienicky neporušené. Postup viz <a href="{{ Locale::url('/reklamace') }}">Vrácení a reklamace</a>.</p>

                <h2>6. Reklamace a záruka</h2>
                <p>Záruční doba je 24 měsíců. V případě vady má kupující právo na reklamaci. Posouzení do 30 dnů.</p>

                <h2>7. Ochrana osobních údajů</h2>
                <p>Vivadzen zpracovává osobní údaje v souladu s GDPR. Detailní zásady ve <a href="{{ Locale::url('/ochrana-osobnich-udaju') }}">Ochraně osobních údajů</a>.</p>

                <h2>8. Závěrečná ustanovení</h2>
                <p>Tyto obchodní podmínky se řídí právem České republiky. Případné spory řeší věcně a místně příslušný soud v ČR.</p>

                <p class="prose__signature">Vivadzen s.r.o. · Platné od 1. 1. 2026</p>
            </article>
        </div>
    </section>
</x-layouts.app>
