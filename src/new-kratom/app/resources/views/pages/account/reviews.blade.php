@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.reviews.title')" :announcement="false">
    <x-account.shell active="reviews">
        <header class="account__head">
            <h1 class="account__title">{{ __('site.account.reviews.title') }}</h1>
            <p class="account__head-hint">{{ __('site.account.reviews.head_hint') }}</p>
        </header>

        @if($reviews->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="star" :size="40" />
                <p class="account__empty-title">{{ __('site.account.reviews.empty_title') }}</p>
                <p class="account__empty">{{ __('site.account.reviews.empty') }}</p>
                <a href="{{ \App\Support\Locale::url('/ucet/objednavky') }}" class="btn btn--outline-light btn--md">
                    {{ __('site.account.reviews.empty_cta') }}
                </a>
            </div>
        @else
            <ul class="account__reviews">
                @foreach($reviews as $review)
                    <li class="account__review">
                        <div class="account__review-head">
                            <div class="account__stars" aria-label="{{ $review->rating }}/5">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-ui.icon name="star" :size="16" class="{{ $i <= $review->rating ? 'is-on' : '' }}" />
                                @endfor
                            </div>
                            <span class="account__badge account__badge--{{ $review->status === 'approved' ? 'ok' : ($review->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ __('site.account.reviews.'.($review->status === 'approved' ? 'approved' : ($review->status === 'rejected' ? 'rejected' : 'pending'))) }}
                            </span>
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
                            <span class="account__review-date">{{ $review->created_at->isoFormat('LL') }}</span>
                            @if($review->product)
                                <a class="account__action" href="{{ Locale::url('/kratom/'.$review->product->slug) }}">
                                    {{ __('site.account.reviews.view_product') }}
                                </a>
                            @endif
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
