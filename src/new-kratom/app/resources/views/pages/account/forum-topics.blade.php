@php
    use App\Support\Locale;
    $level = $levels[$forumUser['level']] ?? null;
@endphp

<x-layouts.app title="Moje forum témata" :announcement="false">
    <x-account.shell active="forum_topics">
        <header class="account__head">
            <div>
                <h1 class="account__title">Moje forum témata</h1>
                <p class="account__head-hint">Rychlý přehled diskuzí, které jste založili.</p>
            </div>
            <a href="{{ Locale::url('/forum/uzivatel/' . $forumUser['slug']) }}" class="btn btn--outline-light btn--sm">Veřejný profil</a>
        </header>

        <section class="account__card account__forum-summary">
            <x-forum.level-badge :level="$level" size="md" />
            <span>{{ number_format($forumUser['reputation']) }} pts</span>
            <span>{{ number_format($forumUser['topics']) }} témat</span>
            <span>{{ number_format($forumUser['posts']) }} příspěvků</span>
        </section>

        @if($topics->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="message-square" :size="40" />
                <p class="account__empty-title">Zatím jste nezaložili žádné téma</p>
                <a href="{{ Locale::url('/forum/nove-tema') }}" class="btn btn--primary btn--md">Založit téma</a>
            </div>
        @else
            <ul class="account__reviews">
                @foreach($topics as $topic)
                    <li class="account__review">
                        <div class="account__review-head">
                            <strong>{{ $topic->emoji }} {{ $topic->title }}</strong>
                            <span class="account__badge account__badge--{{ $topic->status === 'approved' ? 'ok' : ($topic->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ $topic->status }}
                            </span>
                        </div>
                        <p class="account__review-body">{{ \Illuminate\Support\Str::limit($topic->body, 180) }}</p>
                        <div class="account__review-foot">
                            <span class="account__review-date">
                                {{ $topic->category?->icon }} {{ $topic->category?->label }}
                                · {{ $topic->approved_posts_count }} odpovědí
                                · {{ $topic->created_at->isoFormat('LL') }}
                            </span>
                            @if($topic->status === 'approved')
                                <a class="account__action" href="{{ Locale::url('/forum/tema/' . $topic->slug) }}">Otevřít</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="account__pagination">
                {{ $topics->links() }}
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>
