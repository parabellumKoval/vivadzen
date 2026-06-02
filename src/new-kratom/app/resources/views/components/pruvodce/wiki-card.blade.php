@php use App\Support\Locale; @endphp
@props(['article', 'showCategory' => true])

<a class="pruvodce-card"
   href="{{ Locale::url('/pruvodce/'.$article->category->slug.'/'.$article->slug) }}">
    @if ($showCategory)
        <p class="pruvodce-card__eyebrow">{{ $article->category->title }}</p>
    @endif
    <h3 class="pruvodce-card__title">{{ $article->title }}</h3>
    @if ($article->excerpt)
        <p class="pruvodce-card__desc">{{ $article->excerpt }}</p>
    @endif
    @if ($article->reading_time_minutes)
        <p class="pruvodce-card__meta">{{ $article->reading_time_minutes }} min čtení</p>
    @endif
</a>
