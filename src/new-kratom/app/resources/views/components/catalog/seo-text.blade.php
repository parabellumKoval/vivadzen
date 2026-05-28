@props([
    'eyebrow' => 'KATALOG · KOMPLETNÍ INFORMACE',
    'title' => 'Vše o našem kratom sortimentu',
])

<section class="section section--cream seo-text" aria-labelledby="seo-text-title">
    <div class="container container--narrow">
        <header class="seo-text__head">
            <p class="t-overline section-head__eyebrow--soft">{{ $eyebrow }}</p>
            <h2 id="seo-text-title" class="t-heading-xl t-on-light-accent mt-2">{{ $title }}</h2>
        </header>

        <article class="prose">
            {{ $slot }}
        </article>
    </div>
</section>
