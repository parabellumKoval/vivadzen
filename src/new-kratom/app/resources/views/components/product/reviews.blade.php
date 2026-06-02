@props([
    'product' => [],
])

@php
    use App\Support\Locale;

    $reviews = $product['reviews'] ?? [];
    $rating = $product['rating'] ?? 0;
    $reviewsCount = $product['reviewsCount'] ?? 0;
    $verifiedCount = $product['verifiedReviewsCount'] ?? (int) round($reviewsCount * 0.54);
    $dist = $product['ratingDistribution'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $distTotal = max(1, array_sum($dist));
    $apiBase = Locale::url('/api/product/' . $product['slug']);
@endphp

<section
    class="section section--paper reviews-section"
    id="recenze"
    aria-labelledby="reviews-title"
    x-data="productReviews({
        slug: @js($product['slug']),
        apiBase: @js($apiBase),
        seed: @js($reviews),
        ratingAverage: @js((float) $rating),
        reviewsCount: @js((int) $reviewsCount),
    })"
>
    <div class="container">
        <h2 id="reviews-title" class="sr-only">Recenze a hodnocení</h2>

        <div class="reviews-top">
            <div class="reviews-score">
                <p class="reviews-score__num t-display-md">{{ str_replace('.', ',', (string) $rating) }}</p>
                <x-ui.stars class="reviews-score__stars" :rating="$rating" :size="22" />
                <p class="reviews-score__count">{{ $reviewsCount }} recenzí</p>
                <p class="reviews-score__verified">{{ $verifiedCount }} ověřených kupců</p>

                <div class="reviews-badges">
                    <span class="reviews-badge">Google Reviews — 4,9</span>
                    <span class="reviews-badge">Heuréka — 95 %</span>
                </div>
            </div>

            <div class="reviews-distribution">
                @for($star = 5; $star >= 1; $star--)
                    @php
                        $count = $dist[$star] ?? 0;
                        $pct = $distTotal > 0 ? round(($count / $distTotal) * 100) : 0;
                    @endphp
                    <button type="button" class="reviews-bar" @click="setRatingFilter({{ $star }})">
                        <span class="reviews-bar__label">{{ $star }}★</span>
                        <span class="reviews-bar__track"><span class="reviews-bar__fill" style="width: {{ $pct }}%"></span></span>
                        <span class="reviews-bar__pct">{{ $pct }} %</span>
                    </button>
                @endfor

                <div class="mt-6">
                    <x-ui.button variant="primary" icon="arrow-right" x-on:click="openWriteModal()">
                        Napsat recenzi
                    </x-ui.button>
                </div>
            </div>
        </div>

        <div class="reviews-filters">
            <label class="sort-dropdown">
                <span class="sr-only">Řazení</span>
                <select class="sort-dropdown__select" x-model="sort" @change="applyFilters()">
                    <option value="newest">Nejnovější</option>
                    <option value="highest">Nejlépe hodnocené</option>
                    <option value="lowest">Nejhůře hodnocené</option>
                    <option value="photos">S fotografií</option>
                </select>
                <span class="sort-dropdown__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
            </label>
            <div class="chip-row__scroll">
                <button type="button" class="chip" :class="!verified && !withPhotos && !ratingFilter && 'chip--active'" @click="resetFilters()">Vše</button>
                <button type="button" class="chip" :class="verified && 'chip--active'" @click="toggleVerified()">Ověření kupci</button>
                <button type="button" class="chip" :class="withPhotos && 'chip--active'" @click="togglePhotos()">S fotografií</button>
                <template x-for="n in [5, 4, 3, 2, 1]" :key="n">
                    <button type="button" class="chip" :class="ratingFilter === n && 'chip--active'" @click="setRatingFilter(n)"><span x-text="n + '★'"></span></button>
                </template>
            </div>
        </div>

        <ul class="reviews-list" x-show="items.length > 0">
            <template x-for="r in items" :key="r.id">
                <li class="review">
                    <header class="review__head">
                        <span class="stars review__stars" :aria-label="r.rating + ' z 5'">
                            <template x-for="i in 5" :key="i">
                                <svg class="icon stars__icon" :class="i <= r.rating && 'is-on'" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </template>
                        </span>
                        <span class="review__author">
                            <span x-text="r.author"></span>
                            <span class="review__verified" x-show="r.verified">· Ověřený kupec</span>
                        </span>
                        <time class="review__date" x-text="r.date"></time>
                    </header>
                    <p class="review__text" x-text="r.body"></p>

                    <div class="review__photos" x-show="r.images && r.images.length > 0">
                        <template x-for="(img, i) in r.images" :key="i">
                            <a class="review__photo" :href="img" target="_blank" rel="noopener">
                                <img :src="img" :alt="'Fotografie recenze ' + r.author" loading="lazy" />
                            </a>
                        </template>
                    </div>

                    <footer class="review__foot">
                        <button type="button" class="review__helpful" @click="markHelpful(r)" :disabled="r._busy">
                            👍 Doporučuje <span x-text="r.helpful"></span>
                        </button>
                    </footer>
                </li>
            </template>
        </ul>

        <p class="reviews-empty" x-show="!loading && items.length === 0">
            Zatím žádné recenze. Buďte první!
        </p>

        <p class="reviews-loading" x-show="loading && items.length === 0">Načítání…</p>

        <div class="reviews-more" x-show="hasMore && items.length > 0">
            <x-ui.button variant="outline-light" x-on:click="loadMore()" ::disabled="loading">
                <span x-show="!loading">Načíst další recenze</span>
                <span x-show="loading">Načítání…</span>
            </x-ui.button>
        </div>
    </div>

    {{-- Write-review modal --}}
    <div
        class="rv-modal"
        x-cloak
        :class="modalOpen && 'is-open'"
        @keydown.escape.window="modalOpen = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="review-modal-title"
    >
        <div class="rv-modal__backdrop" @click="modalOpen = false"></div>

        <div class="rv-modal__panel">
            <header class="rv-modal__head">
                <h3 id="review-modal-title" class="rv-modal__title">Napsat recenzi na {{ $product['name'] }}</h3>
                <button type="button" class="rv-modal__close" @click="modalOpen = false" aria-label="Zavřít">
                    <x-ui.icon name="x" />
                </button>
            </header>

            <form class="rv-modal__body" @submit.prevent="submitReview()">
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
</section>
