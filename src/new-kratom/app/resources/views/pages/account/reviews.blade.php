@php
    use App\Support\Locale;
    $locale = app()->getLocale();
    $productName = function ($product) use ($locale) {
        if (! $product) return null;
        $name = $product->name;
        if (is_array($name)) {
            return $name[$locale] ?? $name['cs'] ?? $name['en'] ?? reset($name) ?: null;
        }
        return $name;
    };
@endphp

<x-layouts.app :title="__('site.account.reviews.title')" :announcement="false">
    <x-account.shell active="reviews">
        <header class="account__head">
            <div class="account__head-text">
                <h1 class="account__title">{{ __('site.account.reviews.title') }}</h1>
                <p class="account__head-hint">{{ __('site.account.reviews.head_hint') }}</p>
            </div>
        </header>

        @if($reviews->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="star" :size="40" />
                <p class="account__empty-title">{{ __('site.account.reviews.empty_title') }}</p>
                <p class="account__empty-text">{{ __('site.account.reviews.empty') }}</p>
                <a href="{{ Locale::url('/ucet/objednavky') }}" class="btn btn--outline-light btn--md">
                    {{ __('site.account.reviews.empty_cta') }}
                </a>
            </div>
        @else
            <ul class="account__reviews">
                @foreach($reviews as $review)
                    <li class="account__review" x-show="!deletedReviews[{{ $review->id }}]">
                        <div class="account__review-product">
                            @if($review->product)
                                <a href="{{ Locale::url('/kratom/' . $review->product->slug) }}" class="account__review-thumb">
                                    @if($review->product->main_image)
                                        <img src="{{ $review->product->main_image }}" alt="{{ $productName($review->product) }}" loading="lazy" />
                                    @else
                                        <span class="account__review-thumb-placeholder">
                                            <x-ui.icon name="package" :size="22" />
                                        </span>
                                    @endif
                                </a>
                                <div class="account__review-product-meta">
                                    <a href="{{ Locale::url('/kratom/' . $review->product->slug) }}" class="account__review-product-name">
                                        {{ $productName($review->product) }}
                                    </a>
                                    <span class="account__review-date">{{ $review->created_at->isoFormat('LL') }}</span>
                                </div>
                            @else
                                <div class="account__review-thumb">
                                    <span class="account__review-thumb-placeholder">
                                        <x-ui.icon name="package" :size="22" />
                                    </span>
                                </div>
                                <div class="account__review-product-meta">
                                    <span class="account__review-product-name">—</span>
                                    <span class="account__review-date">{{ $review->created_at->isoFormat('LL') }}</span>
                                </div>
                            @endif

                            <span class="account__badge account__badge--{{ $review->status === 'approved' ? 'ok' : ($review->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ __('site.account.reviews.'.($review->status === 'approved' ? 'approved' : ($review->status === 'rejected' ? 'rejected' : 'pending'))) }}
                            </span>
                        </div>

                        <div class="account__review-rating">
                            <x-ui.stars class="account__stars" :rating="$review->rating" :size="16" />
                        </div>

                        <p class="account__review-body">{{ $review->body }}</p>

                        @if($review->images->isNotEmpty())
                            <div class="account__review-photos">
                                @foreach($review->images as $img)
                                    <img src="{{ $img->path }}" alt="" loading="lazy" />
                                @endforeach
                            </div>
                        @endif

                        <div class="account__review-foot">
                            <button type="button"
                                    class="account__btn-icon account__btn-icon--danger"
                                    @click="deleteReview({ id: {{ $review->id }}, name: @js($productName($review->product)) })">
                                <x-ui.icon name="trash" :size="14" />
                                <span>{{ __('site.account.reviews.delete') }}</span>
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="account__pagination">
                {{ $reviews->links() }}
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>
