@props([
    'product' => [],
])

<section class="section section--paper popis-section" id="popis" aria-labelledby="popis-title">
    <div class="container container--narrow">
        <h2 id="popis-title" class="t-heading-xl t-on-light-accent">Popis produktu</h2>

        <div class="popis-body">
            @php
                $descriptionHtml = (string) ($product['description'] ?? '');
                $isHtml = $descriptionHtml !== strip_tags($descriptionHtml);
            @endphp
            @if($isHtml)
                {{-- HTML из WYSIWYG-редактора: пропускаем через allow-list тэгов. --}}
                <div class="rich-text">{!! strip_tags($descriptionHtml, '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h2><h3><h4><blockquote><img><figure><figcaption>') !!}</div>
            @else
                <p>{{ $descriptionHtml }}</p>
            @endif
            <p>
                Více informací o této odrůdě najdete v sekci
                <a href="{{ \App\Support\Locale::url('/kratom/' . $product['color']) }}">{{ $product['colorLabel'] }} kratom</a>
                a v strain-hubu
                <a href="{{ \App\Support\Locale::url('/kratom/' . $product['strain']) }}">{{ $product['strainLabel'] }}</a>.
                Botanickou klasifikaci listu najdete v našem <a href="{{ \App\Support\Locale::url('/pruvodce') }}">průvodci Mitragyna speciosa</a>.
            </p>
        </div>

        <aside class="popis-meta">
            <p class="popis-meta__title">Označení a původ</p>
            <dl class="popis-meta__list">
                <div><dt>Druh</dt><dd>Mitragyna speciosa</dd></div>
                <div><dt>Region původu</dt><dd>{{ $product['origin'] }}</dd></div>
                <div><dt>Zpracování</dt><dd>tradiční sušení s krátkou fermentací</dd></div>
                <div><dt>Šarže</dt><dd>{{ $product['batch'] }}</dd></div>
                <div><dt>Datum testu</dt><dd>{{ $product['testedAt'] }}</dd></div>
                <div><dt>Trvanlivost</dt><dd>24 měsíců od data výroby</dd></div>
                <div><dt>Skladování</dt><dd>sucho, chlad, mimo dosah osob mladších 18 let</dd></div>
            </dl>
        </aside>
    </div>
</section>
