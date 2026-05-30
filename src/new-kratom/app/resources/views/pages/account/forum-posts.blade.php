@php
    use App\Support\Locale;
    $level = $levels[$forumUser['level']] ?? null;
@endphp

<x-layouts.app title="Moje forum příspěvky" :announcement="false">
    <x-account.shell active="forum_posts">
        <header class="account__head">
            <div>
                <h1 class="account__title">Moje forum příspěvky</h1>
                <p class="account__head-hint">Najděte rychle, kde a co jste ve fóru psali.</p>
            </div>
            <a href="{{ Locale::url('/forum/uzivatel/' . $forumUser['slug']) }}" class="btn btn--outline-light btn--sm">Veřejný profil</a>
        </header>

        <section class="account__card account__forum-summary">
            <x-forum.level-badge :level="$level" size="md" />
            <span>{{ number_format($forumUser['reputation']) }} pts</span>
            <span>{{ number_format($forumUser['posts']) }} příspěvků</span>
        </section>

        @if($posts->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="message-square" :size="40" />
                <p class="account__empty-title">Zatím jste nenapsali žádný příspěvek</p>
                <a href="{{ Locale::url('/forum') }}" class="btn btn--primary btn--md">Otevřít forum</a>
            </div>
        @else
            <ul class="account__reviews">
                @foreach($posts as $post)
                    <li class="account__review">
                        <div class="account__review-head">
                            <strong>{{ $post->topic?->emoji }} {{ $post->topic?->title }}</strong>
                            <span class="account__badge account__badge--{{ $post->status === 'approved' ? 'ok' : ($post->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ $post->status }}
                            </span>
                        </div>
                        <p class="account__review-body">{{ \Illuminate\Support\Str::limit($post->body, 220) }}</p>
                        <div class="account__review-foot">
                            <span class="account__review-date">
                                {{ $post->created_at->isoFormat('LLL') }}
                                · {{ $post->score }} pts
                            </span>
                            @if($post->topic && $post->topic->status === 'approved')
                                <a class="account__action" href="{{ Locale::url('/forum/tema/' . $post->topic->slug) }}#post-{{ $post->id }}">Otevřít diskuzi</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="account__pagination">
                {{ $posts->links() }}
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>
