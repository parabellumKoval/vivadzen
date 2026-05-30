{{--
    /forum/uzivatel/{slug} — профиль пользователя форума.

    Показываем: аватар, имя, уровень + прогресс до следующего, био,
    статистику, и две вкладки: «Темы» и «Ответы». Ответы пока тоже
    из фикстур (Alpine генерирует синтетический список из тем).
--}}
@php
    use App\Support\Locale;
    use Illuminate\Support\Str;

    // Прогресс до следующего уровня.
    $orderedLevels = array_keys($levels);
    $currentIdx    = array_search($user['level'], $orderedLevels, true);
    $nextKey       = $orderedLevels[$currentIdx + 1] ?? null;
    $nextLevel     = $nextKey ? $levels[$nextKey] : null;
    $progress = $nextLevel
        ? min(100, round(($user['reputation'] - $level['min']) / max(1, ($nextLevel['min'] - $level['min'])) * 100))
        : 100;
@endphp

<x-layouts.app
    :title="$user['name'] . ' — Forum Vivadzen'"
    :description="Str::limit($user['bio'] ?? ('Profil uživatele ' . $user['name'] . ' v komunitě Vivadzen.'), 160)"
    :announcement="false"
>
    <div
        class="forum"
        x-data="forumUser({
            user: @js($user),
            level: @js($level),
            nextLevel: @js($nextLevel),
            progress: @js($progress),
            topics: @js($topics),
            replies: @js($replies),
            categories: @js($categories)
        })"
    >
        <section class="forum-section forum-section--dark">
            <div class="container forum-user__head">
                <x-forum.avatar :user="$user" size="xl" :show-level="true" :levels="$levels" />

                <div class="forum-user__head-body">
                    <h1 class="forum-user__name t-display-sm t-on-dark">{{ $user['name'] }}</h1>
                    <div class="forum-user__row">
                        <x-forum.level-badge :level="$level" size="md" />
                        <span class="forum-user__joined">Členem od {{ \Carbon\Carbon::parse($user['joinedAt'])->isoFormat('MMMM YYYY') }}</span>
                    </div>

                    @if(! empty($user['bio']))
                        <p class="forum-user__bio t-on-dark-2">{{ $user['bio'] }}</p>
                    @endif

                    @if($nextLevel)
                        <div class="forum-user__progress" aria-label="Postup k další úrovni">
                            <div class="forum-user__progress-bar">
                                <span class="forum-user__progress-fill" style="width: {{ $progress }}%"></span>
                            </div>
                            <p class="forum-user__progress-text">
                                {{ $user['reputation'] }} / {{ $nextLevel['min'] }} pts do úrovně
                                <strong>{{ $nextLevel['icon'] }} {{ $nextLevel['name'] }}</strong>
                            </p>
                        </div>
                    @else
                        <p class="forum-user__progress-text">Maximální úroveň 👑</p>
                    @endif
                </div>

                <dl class="forum-user__stats">
                    <div><dt>Reputace</dt><dd>{{ number_format($user['reputation']) }}</dd></div>
                    <div><dt>Příspěvků</dt><dd>{{ number_format($user['posts']) }}</dd></div>
                    <div><dt>Témat</dt><dd>{{ number_format($user['topics']) }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="forum-section forum-section--paper">
            <div class="container">
                {{-- Tabs --}}
                <div class="forum-user__tabs" role="tablist" aria-label="Obsah uživatele">
                    <button type="button" class="forum-user__tab"
                            :class="tab === 'topics' && 'is-active'"
                            @click="tab = 'topics'" role="tab" :aria-selected="tab === 'topics'">
                        Témata <span class="forum-user__tab-count" x-text="topics.length"></span>
                    </button>
                    <button type="button" class="forum-user__tab"
                            :class="tab === 'replies' && 'is-active'"
                            @click="tab = 'replies'" role="tab" :aria-selected="tab === 'replies'">
                        Odpovědi <span class="forum-user__tab-count" x-text="syntheticReplies.length"></span>
                    </button>
                </div>

                {{-- Topics --}}
                <div x-show="tab === 'topics'">
                    <template x-if="topics.length > 0">
                        <ul class="forum-list" role="list">
                            <template x-for="t in topics" :key="t.id">
                                <li class="forum-row">
                                    <span class="forum-row__emoji" aria-hidden="true" x-text="t.emoji"></span>
                                    <div class="forum-row__main">
                                        <div class="forum-row__titlewrap">
                                            <a class="forum-row__title" :href="topicHref(t.slug)" x-text="t.title"></a>
                                        </div>
                                        <div class="forum-row__meta">
                                            <span class="forum-row__cat" x-text="categoryLabel(t.category)"></span>
                                            <span class="forum-row__sep">·</span>
                                            <span x-text="t.replies + ' komentářů'"></span>
                                            <span class="forum-row__sep">·</span>
                                            <span x-text="formatDate(t.createdAt)"></span>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="topics.length === 0">
                        <p class="forum-empty">Tento uživatel zatím nevytvořil žádné téma.</p>
                    </template>
                </div>

                {{-- Replies --}}
                <div x-show="tab === 'replies'" x-cloak>
                    <ul class="forum-replies" role="list">
                        <template x-for="r in syntheticReplies" :key="r.id">
                            <li class="forum-reply">
                                <header class="forum-reply__head">
                                    <a class="forum-reply__topic" :href="topicHref(r.topicSlug)" x-text="r.topicTitle"></a>
                                    <time class="forum-reply__date" x-text="formatDate(r.at)"></time>
                                </header>
                                <p class="forum-reply__body" x-text="r.excerpt"></p>
                                <footer class="forum-reply__foot">
                                    <span class="forum-reply__score">▲ <span x-text="r.score"></span></span>
                                    <a class="forum-reply__link" :href="topicHref(r.topicSlug)">Otevřít diskuzi →</a>
                                </footer>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
