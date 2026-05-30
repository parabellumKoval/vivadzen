{{--
    DRAFT / ARCHIV — НЕ ПОДКЛЮЧЕНО НИГДЕ.

    Сохранённая вёрстка двух старых секций отзывов с главной страницы:
      • S8 «Google Reviews»  — <section class="section section--sm section--cream greviews">
      • S9 «Trusted»         — <section class="section section--dark trusted bg-botanical">

    Сняты с home.blade.php при замене на <x-sections.reviews-showcase />.
    Компоненты x-sections.google-reviews и x-sections.trusted ещё существуют —
    чтобы вернуть старый вид, верни их в home.blade.php. Этот файл — резервная
    копия исходной разметки на случай удаления компонентов.
--}}

{{-- ===== S8 Google Reviews (sections/google-reviews.blade.php) ===== --}}
<section class="section section--sm section--cream greviews" aria-label="Hodnocení Google">
    <div class="container">
        <div class="greviews__inner">
            <p class="t-overline section-head__eyebrow--soft">HODNOCENÍ NA GOOGLE</p>
            <h2 class="t-heading-md t-on-light-accent">Co o nás říkají zákazníci</h2>
            <div class="greviews__score">
                <span class="stars" aria-hidden="true">★★★★★</span>
                <strong>4,8 z 5</strong>
                <span class="t-on-light-2">· 240+ hodnocení</span>
            </div>
            <x-ui.button href="#" variant="text" icon="arrow-right">
                Více recenzí na Google
            </x-ui.button>
        </div>
    </div>
</section>

{{-- ===== S9 Trusted (sections/trusted.blade.php) ===== --}}
@php
    use App\Support\Locale;

    $reviews = \App\Support\Reviews::featured(12);
@endphp

@if(!empty($reviews))
<section class="section section--dark trusted bg-botanical" aria-labelledby="trusted-title">
    <div class="container">
        <header class="trusted__head section-head--center">
            <div class="trusted__icon-bubble"><x-ui.icon name="sparkles" /></div>
            <p class="t-overline section-head__eyebrow--grass">REAL PEOPLE, REAL RESULTS</p>
            <h2 id="trusted-title" class="t-display-md t-on-dark mt-3">Co říkají naši zákazníci</h2>
            <div class="trusted__rating">
                <span class="stars" aria-hidden="true">★★★★★</span>
                <span>4,9 z 5 — 2 500+ hodnocení</span>
            </div>
        </header>

        <div class="trusted__slider" x-data="reviewsSlider()">
            <button type="button" class="trusted__nav trusted__nav--prev" @click="prev()" aria-label="Předchozí recenze">
                <x-ui.icon name="chevron-down" :size="20" />
            </button>

            <div class="trusted__track" x-ref="track">
                @foreach($reviews as $r)
                    <x-ui.testimonial-card :review="$r" />
                @endforeach
            </div>

            <button type="button" class="trusted__nav trusted__nav--next" @click="next()" aria-label="Další recenze">
                <x-ui.icon name="chevron-down" :size="20" />
            </button>
        </div>

        <div class="trusted__cta">
            <x-ui.button :href="Locale::url('/recenze')" variant="secondary" size="lg" icon="arrow-right">
                Číst všechny recenze
            </x-ui.button>
        </div>
    </div>
</section>
@endif
