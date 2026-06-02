@php
    use App\Support\Locale;
    use Illuminate\Support\Str;

    $level = $levels[$forumUser['level']] ?? null;
    $orderedLevels = array_keys($levels);
    $currentIdx = array_search($forumUser['level'], $orderedLevels, true);
    $nextKey = $orderedLevels[$currentIdx + 1] ?? null;
    $nextLevel = $nextKey ? $levels[$nextKey] : null;
    $progress = $nextLevel && $level
        ? min(100, (int) round(($forumUser['reputation'] - $level['min']) / max(1, ($nextLevel['min'] - $level['min'])) * 100))
        : 100;
@endphp

<x-layouts.app :title="__('site.account.nav.forum_posts')" :announcement="false">
    <x-account.shell active="forum_posts">
        <header class="account__head">
            <div class="account__head-text">
                <h1 class="account__title">{{ __('site.account.nav.forum_posts') }}</h1>
                <p class="account__head-hint">Najděte rychle, kde a co jste ve fóru psali.</p>
            </div>
            <a href="{{ Locale::url('/forum/uzivatel/' . $forumUser['slug']) }}" class="btn btn--outline-light btn--sm">
                {{ __('site.account.forum.public_profile') }}
            </a>
        </header>

        <x-account.forum-summary
            :forum-user="$forumUser"
            :level="$level"
            :next-level="$nextLevel"
            :progress="$progress"
            highlight="posts"
        />

        @if($posts->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="message-square" :size="40" />
                <p class="account__empty-title">Zatím jste nenapsali žádný příspěvek</p>
                <a href="{{ Locale::url('/forum') }}" class="btn btn--primary btn--md">Otevřít forum</a>
            </div>
        @else
            <ul class="account__forum-list">
                @foreach($posts as $post)
                    <li class="account__forum-card" x-show="!deletedPosts[{{ $post->id }}]">
                        <header class="account__forum-card-head">
                            <span class="account__forum-card-emoji" aria-hidden="true">{{ $post->topic?->emoji }}</span>
                            <div class="account__forum-card-title-wrap">
                                @if($post->topic && $post->topic->status === 'approved')
                                    <a href="{{ Locale::url('/forum/tema/' . $post->topic->slug) }}#post-{{ $post->id }}" class="account__forum-card-title">
                                        {{ $post->topic->title }}
                                    </a>
                                @else
                                    <span class="account__forum-card-title is-disabled">{{ $post->topic?->title ?? '—' }}</span>
                                @endif
                                <div class="account__forum-card-meta">
                                    <span>{{ $post->created_at->isoFormat('LLL') }}</span>
                                    <span class="account__forum-card-sep">·</span>
                                    <span>{{ $post->score }} pts</span>
                                </div>
                            </div>
                            <span class="account__badge account__badge--{{ $post->status === 'approved' ? 'ok' : ($post->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ $post->status }}
                            </span>
                        </header>

                        <p class="account__forum-card-body">{{ Str::limit($post->body, 260) }}</p>

                        <footer class="account__forum-card-foot">
                            @if($post->topic && $post->topic->status === 'approved')
                                <a class="account__btn-icon" href="{{ Locale::url('/forum/tema/' . $post->topic->slug) }}#post-{{ $post->id }}">
                                    <x-ui.icon name="arrow-right" :size="14" />
                                    <span>{{ __('site.account.forum.open_topic') }}</span>
                                </a>
                            @endif
                            <button type="button" class="account__btn-icon"
                                    @click="openEditPost({ id: {{ $post->id }}, body: @js($post->body) })">
                                <x-ui.icon name="edit" :size="14" />
                                <span>{{ __('site.account.forum.edit') }}</span>
                            </button>
                            <button type="button" class="account__btn-icon account__btn-icon--danger"
                                    @click="deletePost({ id: {{ $post->id }} })">
                                <x-ui.icon name="trash" :size="14" />
                                <span>{{ __('site.account.forum.delete') }}</span>
                            </button>
                        </footer>
                    </li>
                @endforeach
            </ul>

            <div class="account__pagination">
                {{ $posts->links() }}
            </div>
        @endif

        <x-account.editor-modal />
    </x-account.shell>
</x-layouts.app>
