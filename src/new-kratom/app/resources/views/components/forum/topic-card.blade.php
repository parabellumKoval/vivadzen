@props([
    'topic' => [],
    'author' => null,
])

@php
    use App\Support\Locale;
    $href = Locale::url('/forum/tema/' . $topic['slug']);
    $cover = $topic['cover'] ?? null;
    $coverUrl = $topic['coverUrl'] ?? null;
@endphp

<a href="{{ $href }}" class="forum-card" aria-label="{{ $topic['title'] }}">
    <div
        class="forum-card__cover forum-card__cover--{{ $cover ?? 'default' }}"
        @if($coverUrl) style="background-image: linear-gradient(180deg, rgba(15, 36, 25, 0.10), rgba(15, 36, 25, 0.55)), url('{{ $coverUrl }}'); background-size: cover; background-position: center;" @endif
    >
        <span class="forum-card__emoji" aria-hidden="true">{{ $topic['emoji'] }}</span>
        @if($topic['isHot'] ?? false)
            <span class="forum-card__pill forum-card__pill--hot" aria-label="Populární">🔥 HOT</span>
        @endif
    </div>

    <div class="forum-card__body">
        <h3 class="forum-card__title">{{ $topic['title'] }}</h3>

        @if($author)
            <div class="forum-card__author">
                <x-forum.avatar :user="$author" size="xs" />
                <span class="forum-card__author-name">od {{ $author['name'] }}</span>
            </div>
        @endif
    </div>
</a>
