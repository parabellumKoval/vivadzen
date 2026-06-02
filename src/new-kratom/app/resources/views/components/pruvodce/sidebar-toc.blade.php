{{-- Боковое меню статей раздела. Подсвечивает текущую статью.
     Используется на странице /pruvodce/{category}/{article}. --}}
@props([
    'category' => null,
    'articles' => collect(),
    'currentSlug' => '',
])

@php use App\Support\Locale; @endphp

<aside class="pruvodce-toc" aria-label="Obsah sekce {{ $category?->title }}">
    <p class="pruvodce-toc__eyebrow">{{ $category?->eyebrow ?? 'Obsah sekce' }}</p>
    <a class="pruvodce-toc__cat" href="{{ Locale::url('/pruvodce') }}">
        <x-pruvodce.cat-icon :slug="$category?->slug" :size="24" />
        <span class="pruvodce-toc__cat-text">{{ $category?->title }}</span>
    </a>

    <ul class="pruvodce-toc__list">
        @foreach($articles as $a)
            @php $isActive = $a->slug === $currentSlug; @endphp
            <li class="pruvodce-toc__item{{ $isActive ? ' is-active' : '' }}">
                @if($isActive)
                    <span class="pruvodce-toc__link" aria-current="page">{{ $a->title }}</span>
                @else
                    <a class="pruvodce-toc__link"
                       href="{{ Locale::url('/pruvodce/'.$category->slug.'/'.$a->slug) }}">{{ $a->title }}</a>
                @endif
            </li>
        @endforeach
    </ul>
</aside>
