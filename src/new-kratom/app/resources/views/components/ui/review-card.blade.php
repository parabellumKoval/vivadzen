@props([
    'quote' => '',
    'name' => '',
    'date' => '',
    'chip' => null,
    'rating' => 5,
])

<article class="review-card">
    <div class="review-card__stars" aria-label="Hodnocení {{ $rating }} z 5">
        {!! str_repeat('★', (int) $rating) . str_repeat('☆', 5 - (int) $rating) !!}
    </div>

    <blockquote class="review-card__quote">«{{ $quote }}»</blockquote>

    <footer class="review-card__footer">
        <div class="review-card__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
        <div>
            <div class="review-card__name">{{ $name }}</div>
            @if($date)<div class="review-card__date">{{ $date }}</div>@endif
            @if($chip)
                <x-ui.badge variant="tag" class="review-card__chip">{{ $chip }}</x-ui.badge>
            @endif
        </div>
    </footer>
</article>
