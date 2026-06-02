{{-- S9 «Trusted by Thousands» — реальные отзывы о товарах, слайдер --}}
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
                <x-ui.stars :rating="5" :size="18" class="stars--on-dark" />
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
