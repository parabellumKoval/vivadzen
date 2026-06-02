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

<x-layouts.app :title="__('site.account.nav.forum_topics')" :announcement="false">
    <x-account.shell active="forum_topics">
        <header class="account__head">
            <div class="account__head-text">
                <h1 class="account__title">{{ __('site.account.nav.forum_topics') }}</h1>
                <p class="account__head-hint">Rychlý přehled diskuzí, které jste založili.</p>
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
            highlight="topics"
        />

        @if($topics->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="message-square" :size="40" />
                <p class="account__empty-title">Zatím jste nezaložili žádné téma</p>
                <a href="{{ Locale::url('/forum/nove-tema') }}" class="btn btn--primary btn--md">Založit téma</a>
            </div>
        @else
            <ul class="account__forum-list">
                @foreach($topics as $topic)
                    <li class="account__forum-card" x-show="!deletedTopics[{{ $topic->id }}]">
                        <header class="account__forum-card-head">
                            <span class="account__forum-card-emoji" aria-hidden="true">{{ $topic->emoji }}</span>
                            <div class="account__forum-card-title-wrap">
                                @if($topic->status === 'approved')
                                    <a href="{{ Locale::url('/forum/tema/' . $topic->slug) }}" class="account__forum-card-title">
                                        {{ $topic->title }}
                                    </a>
                                @else
                                    <span class="account__forum-card-title is-disabled">{{ $topic->title }}</span>
                                @endif
                                <div class="account__forum-card-meta">
                                    @if($topic->category)
                                        <span>{{ $topic->category->icon }} {{ $topic->category->label }}</span>
                                        <span class="account__forum-card-sep">·</span>
                                    @endif
                                    <span>{{ $topic->approved_posts_count }} odpovědí</span>
                                    <span class="account__forum-card-sep">·</span>
                                    <span>{{ $topic->created_at->isoFormat('LL') }}</span>
                                </div>
                            </div>
                            <span class="account__badge account__badge--{{ $topic->status === 'approved' ? 'ok' : ($topic->status === 'rejected' ? 'danger' : 'warn') }}">
                                {{ $topic->status }}
                            </span>
                        </header>

                        <p class="account__forum-card-body">{{ Str::limit($topic->body, 220) }}</p>

                        <footer class="account__forum-card-foot">
                            @if($topic->status === 'approved')
                                <a class="account__btn-icon" href="{{ Locale::url('/forum/tema/' . $topic->slug) }}">
                                    <x-ui.icon name="arrow-right" :size="14" />
                                    <span>{{ __('site.account.forum.open_topic') }}</span>
                                </a>
                            @endif
                            <button type="button" class="account__btn-icon"
                                    @click="openEditTopic({ id: {{ $topic->id }}, title: @js($topic->title), body: @js($topic->body) })">
                                <x-ui.icon name="edit" :size="14" />
                                <span>{{ __('site.account.forum.edit') }}</span>
                            </button>
                            <button type="button" class="account__btn-icon account__btn-icon--danger"
                                    @click="deleteTopic({ id: {{ $topic->id }}, title: @js($topic->title) })">
                                <x-ui.icon name="trash" :size="14" />
                                <span>{{ __('site.account.forum.delete') }}</span>
                            </button>
                        </footer>
                    </li>
                @endforeach
            </ul>

            <div class="account__pagination">
                {{ $topics->links() }}
            </div>
        @endif

        <x-account.editor-modal />
    </x-account.shell>
</x-layouts.app>
