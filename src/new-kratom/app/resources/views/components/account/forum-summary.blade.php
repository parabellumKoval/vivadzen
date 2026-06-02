@props([
    'forumUser' => [],
    'level' => null,
    'nextLevel' => null,
    'progress' => 0,
    'highlight' => null,   // 'topics' | 'posts' — visually highlights the matching stat
])

<section class="account__card account__forum-summary">
    <div class="account__forum-summary-head">
        <div class="account__forum-summary-level">
            @if($level)
                <span class="account__forum-summary-icon" style="background: {{ $level['color'] }};">{{ $level['icon'] }}</span>
                <div>
                    <p class="account__forum-summary-eyebrow">{{ __('site.account.nav.forum_topics') === 'Moje témata' ? 'Vaše úroveň' : __('site.account.forum.reputation') }}</p>
                    <p class="account__forum-summary-level-name">{{ $level['name'] }}</p>
                </div>
            @endif
        </div>

        <dl class="account__forum-summary-stats">
            <div @class(['is-highlight' => $highlight === 'reputation'])>
                <dt>{{ __('site.account.forum.reputation') }}</dt>
                <dd>{{ number_format($forumUser['reputation'] ?? 0) }}</dd>
            </div>
            <div @class(['is-highlight' => $highlight === 'topics'])>
                <dt>{{ __('site.account.forum.topics_count') }}</dt>
                <dd>{{ number_format($forumUser['topics'] ?? 0) }}</dd>
            </div>
            <div @class(['is-highlight' => $highlight === 'posts'])>
                <dt>{{ __('site.account.forum.posts_count') }}</dt>
                <dd>{{ number_format($forumUser['posts'] ?? 0) }}</dd>
            </div>
        </dl>
    </div>

    @if($nextLevel)
        <div class="account__forum-summary-progress" aria-label="Postup k další úrovni">
            <div class="account__forum-summary-progress-bar">
                <span class="account__forum-summary-progress-fill" style="width: {{ $progress }}%"></span>
            </div>
            <p class="account__forum-summary-progress-text">
                {{ number_format($forumUser['reputation'] ?? 0) }} / {{ number_format($nextLevel['min']) }}
                <span class="account__forum-summary-progress-label">
                    {{ __('site.account.forum.next_level') }}
                    <strong>{{ $nextLevel['icon'] }} {{ $nextLevel['name'] }}</strong>
                </span>
            </p>
        </div>
    @endif
</section>
