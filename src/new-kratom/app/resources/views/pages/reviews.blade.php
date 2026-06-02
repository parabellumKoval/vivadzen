{{--
    /recenze — все отзывы о товарах Vivadzen.
    Фильтр/сортировка (в т.ч. по конкретному товару), написание отзыва (модалка),
    блок Google-отзывов.
--}}
@php
    use App\Support\Locale;

    $googleReviewUrl = config('services.google.review_url');
    $googleReviewsUrl = config('services.google.reviews_url');
    $googleRating = config('services.google.rating');
    $googleCount = config('services.google.reviews_count');
@endphp

<x-layouts.app
    title="Recenze kratomu Vivadzen — hodnocení zákazníků"
    description="Ověřené recenze a hodnocení kratomu Vivadzen od reálných zákazníků. Filtrujte podle produktu, hodnocení a fotografií. Napište vlastní recenzi."
>
    <div
        x-data="reviewsPage({ apiBase: @js(Locale::url('/api/reviews')), products: @js($products) })"
    >
        {{-- Hero --}}
        <section class="section section--dark reviews-hero bg-botanical" aria-labelledby="reviews-hero-title">
            <div class="container reviews-hero__inner">
                <p class="t-overline section-head__eyebrow--grass">RECENZE A HODNOCENÍ</p>
                <h1 id="reviews-hero-title" class="t-display-md t-on-dark mt-3">Co o nás říkají zákazníci</h1>
                <p class="reviews-hero__lead">
                    Reálné recenze ověřených kupců napříč celým sortimentem. Každá šarže má vlastní COA —
                    a zde najdete zkušenosti lidí, kteří kratom Vivadzen už vyzkoušeli.
                </p>
                <div class="reviews-hero__rating">
                    <x-ui.stars :rating="5" :size="20" class="stars--on-dark" />
                    <span>4,9 z 5 — 2 500+ hodnocení</span>
                </div>
                <div class="reviews-hero__cta">
                    <x-ui.button variant="primary" size="lg" icon="arrow-right" x-on:click="openWriteModal()">
                        Napsat recenzi
                    </x-ui.button>
                </div>
            </div>
        </section>

        {{-- Filters + list --}}
        <section class="section section--paper reviews-page" aria-label="Seznam recenzí">
            <div class="container">
                <div class="reviews-page__toolbar">
                    <div class="reviews-page__filters">
                        <label class="rvp-select">
                            <span class="sr-only">Produkt</span>
                            <select class="rvp-select__field" x-model="productSlug" @change="applyFilters()">
                                <option value="">Všechny produkty</option>
                                <template x-for="p in products" :key="p.slug">
                                    <option :value="p.slug" x-text="p.name"></option>
                                </template>
                            </select>
                            <span class="rvp-select__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
                        </label>

                        <label class="rvp-select">
                            <span class="sr-only">Řazení</span>
                            <select class="rvp-select__field" x-model="sort" @change="applyFilters()">
                                <option value="newest">Nejnovější</option>
                                <option value="highest">Nejlépe hodnocené</option>
                                <option value="lowest">Nejhůře hodnocené</option>
                                <option value="photos">S fotografií</option>
                            </select>
                            <span class="rvp-select__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
                        </label>
                    </div>

                    <div class="chip-row__scroll reviews-page__chips">
                        <button type="button" class="chip" :class="!verified && !withPhotos && !ratingFilter && !productSlug && 'chip--active'" @click="resetFilters()">Vše</button>
                        <button type="button" class="chip" :class="verified && 'chip--active'" @click="toggleVerified()">Ověření kupci</button>
                        <button type="button" class="chip" :class="withPhotos && 'chip--active'" @click="togglePhotos()">S fotografií</button>
                        <template x-for="n in [5, 4, 3, 2, 1]" :key="n">
                            <button type="button" class="chip" :class="ratingFilter === n && 'chip--active'" @click="setRatingFilter(n)"><span x-text="n + '★'"></span></button>
                        </template>
                    </div>
                </div>

                <p class="reviews-page__count" x-show="!loading || items.length > 0">
                    <span x-text="total"></span> recenzí
                </p>

                <ul class="rvp-list" x-show="items.length > 0">
                    <template x-for="r in items" :key="r.id">
                        <li class="rvp-card">
                            <a class="rvp-card__product" :href="r.product?.url" x-show="r.product">
                                <img class="rvp-card__product-img" :src="r.product?.image" :alt="r.product?.name" loading="lazy" width="56" height="56" x-show="r.product?.image" />
                                <span class="rvp-card__product-text">
                                    <span class="rvp-card__product-name" x-text="r.product?.name"></span>
                                    <span class="rvp-card__product-price" x-show="r.product?.price">od <span x-text="r.product?.price"></span> Kč</span>
                                </span>
                            </a>

                            <header class="rvp-card__head">
                                <span class="stars rvp-card__stars" :aria-label="r.rating + ' z 5'">
                                    <template x-for="i in 5" :key="i">
                                        <svg class="icon stars__icon" :class="i <= r.rating && 'is-on'" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </template>
                                </span>
                                <time class="rvp-card__date" x-text="r.date"></time>
                            </header>

                            <p class="rvp-card__text" x-text="r.body"></p>

                            <div class="rvp-card__photos" x-show="r.images && r.images.length > 0">
                                <template x-for="(img, i) in r.images" :key="i">
                                    <a class="rvp-card__photo" :href="img" target="_blank" rel="noopener">
                                        <img :src="img" :alt="'Fotografie recenze ' + r.author" loading="lazy" />
                                    </a>
                                </template>
                            </div>

                            <footer class="rvp-card__foot">
                                <span class="rvp-card__author">
                                    <span x-text="r.author"></span>
                                    <span class="rvp-card__verified" x-show="r.verified">· Ověřený kupec</span>
                                </span>
                                <button type="button" class="review__helpful" @click="markHelpful(r)" :disabled="r._busy">
                                    👍 <span x-text="r.helpful"></span>
                                </button>
                            </footer>
                        </li>
                    </template>
                </ul>

                <p class="reviews-empty" x-show="!loading && items.length === 0">
                    Pro zvolený filtr zatím nejsou žádné recenze. Buďte první!
                </p>
                <p class="reviews-loading" x-show="loading && items.length === 0">Načítání…</p>

                <div class="reviews-more" x-show="hasMore && items.length > 0">
                    <x-ui.button variant="primary" x-on:click="loadMore()" ::disabled="loading">
                        <span x-show="!loading">Načíst další recenze</span>
                        <span x-show="loading">Načítání…</span>
                    </x-ui.button>
                </div>
            </div>
        </section>

        {{-- Write review CTA block --}}
        <section class="section section--cream-50 reviews-write" aria-labelledby="reviews-write-title">
            <div class="container reviews-write__inner">
                <div>
                    <h2 id="reviews-write-title" class="t-heading-lg t-on-light-accent">Máte zkušenost s naším kratomem?</h2>
                    <p class="reviews-write__lead">Podělte se o svůj názor — pomůžete ostatním zákazníkům s výběrem. Recenzi po krátké kontrole zveřejníme.</p>
                </div>
                <x-ui.button variant="primary" size="lg" icon="arrow-right" x-on:click="openWriteModal()">
                    Napsat recenzi o produktu
                </x-ui.button>
            </div>
        </section>

        {{-- Google reviews --}}
        <section class="section section--cream greviews" aria-label="Hodnocení Google">
            <div class="container">
                <div class="greviews__inner">
                    <p class="t-overline section-head__eyebrow--soft">HODNOCENÍ NA GOOGLE</p>
                    <h2 class="t-heading-md t-on-light-accent">Recenze o obchodu jako celku</h2>
                    <div class="greviews__score">
                        <x-ui.stars :rating="5" :size="18" />
                        <strong>{{ $googleRating }} z 5</strong>
                        <span class="t-on-light-2">· {{ $googleCount }} hodnocení</span>
                    </div>
                    <p class="greviews__hint">
                        Chcete napsat recenzi o obchodu Vivadzen jako celku? Přejděte na Google a podělte se o svou zkušenost.
                    </p>
                    <div class="greviews__actions">
                        <x-ui.button href="{{ $googleReviewUrl }}" target="_blank" rel="noopener" variant="primary" icon="arrow-right">
                            Napsat recenzi na Google
                        </x-ui.button>
                        <x-ui.button href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener" variant="text" icon="arrow-right">
                            Více recenzí na Google
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Write-review modal --}}
        <div
            class="rv-modal"
            x-cloak
            :class="modalOpen && 'is-open'"
            @keydown.escape.window="modalOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reviews-modal-title"
        >
            <div class="rv-modal__backdrop" @click="modalOpen = false"></div>

            <div class="rv-modal__panel">
                <header class="rv-modal__head">
                    <h3 id="reviews-modal-title" class="rv-modal__title">Napsat recenzi</h3>
                    <button type="button" class="rv-modal__close" @click="modalOpen = false" aria-label="Zavřít">
                        <x-ui.icon name="x" />
                    </button>
                </header>

                <form class="rv-modal__body" @submit.prevent="submitReview()">
                    <label class="field">
                        <span class="field__label">Produkt *</span>
                        <select class="field__input" x-model="form.product" required>
                            <option value="">— vyberte produkt —</option>
                            <template x-for="p in products" :key="p.slug">
                                <option :value="p.slug" x-text="p.name"></option>
                            </template>
                        </select>
                    </label>

                    <div class="rv-modal__row">
                        <label class="rv-modal__label">Vaše hodnocení *</label>
                        <div class="rv-modal__rating" role="radiogroup" aria-label="Hodnocení 1 až 5 hvězd">
                            <template x-for="n in 5" :key="n">
                                <button
                                    type="button"
                                    class="rv-modal__star"
                                    :class="n <= form.rating && 'is-on'"
                                    role="radio"
                                    :aria-checked="n === form.rating"
                                    :aria-label="n + ' z 5'"
                                    @click="form.rating = n"
                                >
                                    <svg class="icon" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="rv-modal__row rv-modal__row--two">
                        <label class="field">
                            <span class="field__label">Jméno *</span>
                            <input class="field__input" type="text" x-model.trim="form.author_name" required maxlength="120" />
                        </label>
                        <label class="field">
                            <span class="field__label">E-mail (nezveřejní se)</span>
                            <input class="field__input" type="email" x-model.trim="form.author_email" maxlength="190" />
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">Recenze *</span>
                        <textarea class="field__input field__input--textarea" x-model.trim="form.body" rows="5" minlength="10" maxlength="4000" required></textarea>
                        <span class="field__hint"><span x-text="form.body.length"></span> / 4000</span>
                    </label>

                    <div class="rv-modal__row">
                        <label class="rv-modal__label">Fotografie (až 3, max 5 MB / soubor)</label>
                        <input
                            class="rv-modal__file"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            @change="handlePhotos($event)"
                        />
                        <div class="rv-modal__photos" x-show="form.photos.length > 0">
                            <template x-for="(p, i) in form.photoPreviews" :key="i">
                                <div class="rv-modal__photo">
                                    <img :src="p" alt="" />
                                    <button type="button" class="rv-modal__photo-remove" @click="removePhoto(i)" aria-label="Odebrat fotografii">×</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p class="rv-modal__error" x-show="error" x-text="error"></p>
                    <p class="rv-modal__success" x-show="success" x-text="success"></p>

                    <footer class="rv-modal__foot">
                        <button type="button" class="btn btn--outline-light" @click="modalOpen = false">Zrušit</button>
                        <button type="submit" class="btn btn--primary" :disabled="submitting">
                            <span x-show="!submitting">Odeslat recenzi</span>
                            <span x-show="submitting">Odesílání…</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
