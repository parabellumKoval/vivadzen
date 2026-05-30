@props([
    'review' => [],
])

@php
    $product = $review['product'] ?? [];
    $rating = max(1, min(5, (int) ($review['rating'] ?? 5)));
    $author = $review['author'] ?? '';
    $body = $review['body'] ?? '';
    $date = $review['date'] ?? null;
    $initial = $author !== '' ? mb_strtoupper(mb_substr($author, 0, 1)) : '?';
@endphp

<article {{ $attributes->class('testimonial-card') }}>
    <div class="testimonial-card__top">
        <span class="testimonial-card__stars" aria-label="Hodnocení {{ $rating }} z 5">
            {!! str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) !!}
        </span>
        <span class="testimonial-card__quote-mark" aria-hidden="true">“</span>
    </div>

    <blockquote class="testimonial-card__quote">{{ $body }}</blockquote>

    <footer class="testimonial-card__footer">
        <div class="testimonial-card__author">
            <span class="testimonial-card__avatar" aria-hidden="true">{{ $initial }}</span>
            <span class="testimonial-card__meta">
                <span class="testimonial-card__name">{{ $author }}</span>
                @if($date)<span class="testimonial-card__date">{{ $date }}</span>@endif
            </span>
        </div>

        @if(!empty($product['url']) && !empty($product['name']))
            <a class="testimonial-card__product" href="{{ $product['url'] }}">
                @if(!empty($product['image']))
                    <img class="testimonial-card__product-img" src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" width="52" height="52" />
                @endif
                <span class="testimonial-card__product-text">
                    <span class="testimonial-card__product-name">{{ $product['name'] }}</span>
                    @if(!empty($product['price']))
                        <span class="testimonial-card__product-price">od {{ $product['price'] }} Kč</span>
                    @endif
                </span>
            </a>
        @endif
    </footer>
</article>
