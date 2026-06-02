@php use App\Support\Locale; @endphp

<x-layouts.app
    title="Průvodce kratomem — wiki encyklopedie | Vivadzen"
    description="Věcný průvodce kratomem (Mitragyna speciosa): botanika, chemie, legislativa ČR 2026, kvalita a bezpečnost. Bez marketingu, bez doporučení k užívání."
    :canonical="url(Locale::url('/pruvodce'))"
>
    <x-ui.breadcrumbs :items="[
        ['label' => 'Domů', 'href' => Locale::url('/')],
        ['label' => 'Průvodce'],
    ]" />

    <section class="pruvodce-hero pruvodce-hero--cream">
        <div class="container container--narrow">
            <p class="pruvodce-hero__eyebrow">Wiki encyklopedie</p>
            <h1 class="pruvodce-hero__title">Průvodce kratomem</h1>
            <p class="pruvodce-hero__lead">
                Strukturovaný a věcný přehled toho, co je kratom, jak jeho rostlina
                vypadá, jaké alkaloidy obsahuje, jak je v ČR od roku 2026 regulován
                a jak se posuzuje jeho kvalita. Bez marketingu a bez doporučení.
            </p>
        </div>
    </section>

    <section class="pruvodce-section">
        <div class="container">
            <h2 class="pruvodce-section__title">Hlavní oblasti</h2>
            <div class="pruvodce-grid pruvodce-grid--4">
                @foreach ($categories as $cat)
                    <a class="pruvodce-cat-card pruvodce-cat-card--{{ $cat->accent ?: 'cream' }}"
                       href="{{ Locale::url('/pruvodce/'.$cat->slug) }}">
                        <x-pruvodce.cat-icon :slug="$cat->slug" />

                        @if ($cat->eyebrow)
                            <p class="pruvodce-cat-card__eyebrow">{{ $cat->eyebrow }}</p>
                        @endif
                        <h3 class="pruvodce-cat-card__title">{{ $cat->title }}</h3>
                        @if ($cat->description)
                            <p class="pruvodce-cat-card__desc">{{ $cat->description }}</p>
                        @endif
                        <span class="pruvodce-cat-card__meta">{{ $cat->articles_count }} článků →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($featured->isNotEmpty())
        <section class="pruvodce-section pruvodce-section--alt">
            <div class="container">
                <h2 class="pruvodce-section__title">Naposledy publikováno</h2>
                <div class="pruvodce-grid pruvodce-grid--3">
                    @foreach ($featured as $a)
                        <x-pruvodce.wiki-card :article="$a" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="pruvodce-section pruvodce-section--note">
        <div class="container container--narrow">
            <p class="pruvodce-note">
                Tento průvodce je informační a nepředstavuje doporučení k užívání.
                Kratom je v České republice od roku 2026 regulován jako
                <a href="{{ Locale::url('/pruvodce/legislativa-cr/psychomodulacni-latky') }}">psychomodulační látka</a>
                ve smyslu zákona 167/1998 Sb.
            </p>
        </div>
    </section>
</x-layouts.app>
