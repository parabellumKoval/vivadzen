{{--
    /forum/tema/{slug} — страница темы.

    Alpine-компонент forumTopic строит дерево комментариев из плоского
    списка (поле parentId) и реализует:
      • ответы (с лимитом глубины — MAX_DEPTH в forum-topic.js),
      • реакции (эмодзи) на комментариях,
      • оценки (up/down),
      • редактирование своих комментариев и тела темы,
      • виртуального «current user» (id=1) — пока без бэкенда.

    Дерево разворачивается в плоский список (sortedFlat) с полем depth,
    отступы — через CSS variable --depth. Так избегаем рекурсивных
    Alpine-шаблонов и сохраняем единый x-for.
--}}
@php
    use App\Support\Locale;
    $usersById = collect($users)->keyBy('id')->all();
    $authorLevel = $levels[$author['level']] ?? null;
    $coverUrl = $topic['coverUrl'] ?? null;
@endphp

<x-layouts.app
    :title="$topic['title'] . ' — Forum Vivadzen'"
    :description="\Illuminate\Support\Str::limit(strip_tags($topic['body']), 160)"
>
    <div
        class="forum"
        x-data="forumTopic({
            topic: @js($topic),
            author: @js($author),
            initialComments: @js($comments),
            users: @js($usersById),
            levels: @js($levels),
            categories: @js($categories),
            reactions: @js($reactions),
            members: @js($members),
            currentUser: @js($currentUser),
            endpoints: @js($endpoints)
        })"
    >
        {{-- ── Topic head ─────────────────────────────────────────── --}}
        <section
            class="forum-topic-hero"
            :class="topic.cover && 'has-cover'"
            :data-cover="topic.cover || ''"
            @if($coverUrl) style="background-image: linear-gradient(180deg, rgba(15, 36, 25, 0.45) 0%, rgba(15, 36, 25, 0.88) 100%), url('{{ $coverUrl }}'); background-size: cover; background-position: center;" @endif
            aria-labelledby="forum-topic-title"
        >
            <div class="forum-topic-hero__overlay"></div>
            <div class="container forum-topic-hero__inner">
                <nav class="forum-crumbs t-on-dark-2" aria-label="Drobečková navigace">
                    <a href="{{ Locale::url('/forum') }}">Forum</a>
                    <span aria-hidden="true">/</span>
                    <span class="forum-crumbs__cat">{{ $categories[$topic['category']]['icon'] ?? '' }} {{ $categories[$topic['category']]['label'] ?? '' }}</span>
                </nav>

                <div class="forum-topic-hero__head">
                    <span class="forum-topic-hero__emoji" aria-hidden="true">{{ $topic['emoji'] }}</span>
                    <div>
                        <h1 id="forum-topic-title" class="forum-topic-hero__title t-display-sm t-on-dark">{{ $topic['title'] }}</h1>
                        <div class="forum-topic-hero__meta t-on-dark-2">
                            <span>{{ $topic['replies'] }} komentářů</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $topic['views'] }} zobrazení</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $topic['reputation'] }} pts</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Topic body + comments ─────────────────────────────── --}}
        <section class="forum-section forum-section--paper">
            <div class="container forum-topic-grid">
                <div class="forum-topic-grid__main">

                    {{-- Original post --}}
                    <article class="forum-post forum-post--op">
                        <header class="forum-post__head">
                            <x-forum.avatar :user="$author" size="md" :show-level="true" :levels="$levels" />
                            <div class="forum-post__byline">
                                <a class="forum-post__author" href="{{ Locale::url('/forum/uzivatel/' . $author['slug']) }}">{{ $author['name'] }}</a>
                                <x-forum.level-badge :level="$authorLevel" />
                                <div class="forum-post__times">
                                    <time datetime="{{ $topic['createdAt'] }}">{{ \Carbon\Carbon::parse($topic['createdAt'])->isoFormat('D. MMM YYYY, H:mm') }}</time>
                                    @if(! empty($topic['updatedAt']) && $topic['updatedAt'] !== $topic['createdAt'])
                                        <span class="forum-post__edited" x-show="!topicWasEdited">· upraveno</span>
                                    @endif
                                    <span class="forum-post__edited" x-show="topicWasEdited" x-cloak>· upraveno právě teď</span>
                                </div>
                            </div>

                            <div class="forum-post__head-actions">
                                <button
                                    type="button"
                                    class="forum-post__act"
                                    x-show="canEditTopic && !editingTopic"
                                    @click="startEditTopic()"
                                    title="Upravit téma"
                                >
                                    <x-ui.icon name="edit" :size="16" /> Upravit
                                </button>
                            </div>
                        </header>

                        <div class="forum-post__body" x-show="!editingTopic">
                            <template x-for="(p, i) in topicParagraphs" :key="i">
                                <p x-text="p"></p>
                            </template>
                        </div>

                        <div class="forum-post__edit" x-show="editingTopic" x-cloak>
                            <textarea class="field__input field__input--textarea" rows="6" x-model="topicDraft"></textarea>
                            <div class="forum-post__edit-actions">
                                <button type="button" class="btn btn--text btn--sm" @click="cancelEditTopic()">Zrušit</button>
                                <button type="button" class="btn btn--primary btn--sm" @click="saveTopic()">Uložit</button>
                            </div>
                        </div>
                    </article>

                    {{-- Comments header --}}
                    <header class="forum-comments__head">
                        <h2 class="forum-comments__title">Komentáře <span class="forum-comments__count" x-text="`(${comments.length})`"></span></h2>
                        <div class="forum-comments__sort">
                            <label class="rvp-select">
                                <span class="sr-only">Řazení</span>
                                <select class="rvp-select__field" x-model="sort">
                                    <option value="top">Nejlepší</option>
                                    <option value="new">Nejnovější</option>
                                    <option value="old">Nejstarší</option>
                                </select>
                                <span class="rvp-select__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
                            </label>
                        </div>
                    </header>

                    {{-- Reply to topic --}}
                    @if($currentUser)
                        <form class="forum-replybox" @submit.prevent="submitReply(null, newReply);">
                            <x-forum.avatar :user="$currentUser" size="sm" />
                            <div class="forum-replybox__body">
                                <textarea
                                    class="field__input field__input--textarea forum-replybox__input"
                                    rows="3"
                                    placeholder="Napište svůj komentář…"
                                    x-model="newReply"
                                    @focus="newReplyFocused = true"
                                ></textarea>
                                <div class="forum-replybox__actions" x-show="newReplyFocused || newReply.length > 0">
                                    <span class="forum-replybox__hint" x-text="formError || 'Buďte věcní — komunita ocení nuance, ne hluk.'"></span>
                                    <div class="forum-replybox__btns">
                                        <button type="button" class="btn btn--text btn--sm" @click="newReply = ''; newReplyFocused = false;">Zrušit</button>
                                        <button type="submit" class="btn btn--primary btn--sm" :disabled="newReply.trim().length < 2 || submittingReply">
                                            <span x-show="!submittingReply">Odeslat</span>
                                            <span x-show="submittingReply" x-cloak>Odesílání…</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="forum-replybox forum-replybox--guest">
                            <div class="forum-replybox__body">
                                <strong>Chcete odpovědět?</strong>
                                <p class="forum-replybox__hint">Přihlaste se a komentář se uloží do vašeho forum profilu.</p>
                                <button type="button" class="btn btn--primary btn--sm" @click="$dispatch('auth:open', { view: 'login' })">Přihlásit se</button>
                            </div>
                        </div>
                    @endif

                    {{-- Threaded comments — flat list with depth indentation --}}
                    <ol class="forum-comments" role="list">
                        <template x-for="c in sortedFlat" :key="c.id">
                            <li
                                class="forum-comment"
                                :id="`post-${c.id}`"
                                :class="[c.depth > 0 && 'is-child', c.collapsed && 'is-collapsed']"
                                :style="`--depth: ${c.depth}`"
                            >
                                {{-- Thread connector line for nested replies --}}
                                <template x-if="c.depth > 0">
                                    <span class="forum-comment__thread" aria-hidden="true"></span>
                                </template>

                                <div class="forum-comment__inner">
                                    <header class="forum-comment__head">
                                        <a class="favatar favatar--sm"
                                           :href="userHref(commentAuthor(c).slug)"
                                           :style="`--favatar-bg: ${commentAuthor(c).avatarColor}`">
                                            <span class="favatar__initials" x-text="initials(commentAuthor(c).name)"></span>
                                        </a>

                                        <div class="forum-comment__byline">
                                            <a class="forum-comment__author"
                                               :href="userHref(commentAuthor(c).slug)"
                                               x-text="commentAuthor(c).name"></a>
                                            <span class="flevel flevel--xs"
                                                  :style="`--flevel-color: ${authorLevel(c).color}`"
                                                  :title="`Úroveň: ${authorLevel(c).name}`">
                                                <span class="flevel__icon" aria-hidden="true" x-text="authorLevel(c).icon"></span>
                                            </span>
                                            <span class="forum-comment__time" x-text="formatRelative(c.createdAt)"></span>
                                            <span class="forum-comment__edited" x-show="c.updatedAt || c._editedNow">· upraveno</span>
                                        </div>

                                        <button
                                            type="button"
                                            class="forum-comment__collapse"
                                            @click="c.collapsed = !c.collapsed"
                                            :aria-expanded="(!c.collapsed).toString()"
                                            :title="c.collapsed ? 'Rozbalit' : 'Sbalit'"
                                            x-text="c.collapsed ? '[+]' : '[—]'"
                                        ></button>
                                    </header>

                                    <div class="forum-comment__body" x-show="!c.collapsed && editingId !== c.id">
                                        <template x-for="(p, i) in paragraphs(c.body)" :key="i">
                                            <p x-text="p"></p>
                                        </template>
                                    </div>

                                    <div class="forum-comment__edit" x-show="!c.collapsed && editingId === c.id" x-cloak>
                                        <textarea class="field__input field__input--textarea" rows="3" x-model="editDraft"></textarea>
                                        <div class="forum-post__edit-actions">
                                            <button type="button" class="btn btn--text btn--sm" @click="cancelEdit()">Zrušit</button>
                                            <button type="button" class="btn btn--primary btn--sm" @click="saveEdit(c)">Uložit</button>
                                        </div>
                                    </div>

                                    {{-- Actions: vote + reactions + reply + edit --}}
                                    <footer class="forum-comment__actions" x-show="!c.collapsed">
                                        <div class="forum-vote" role="group" aria-label="Hodnocení komentáře">
                                            <button type="button" class="forum-vote__btn forum-vote__btn--up"
                                                    :class="c._vote === 1 && 'is-on'"
                                                    @click="vote(c, 1)" aria-label="Plus">▲</button>
                                            <span class="forum-vote__score" :class="c.score >= 0 ? 'is-pos' : 'is-neg'" x-text="c.score"></span>
                                            <button type="button" class="forum-vote__btn forum-vote__btn--down"
                                                    :class="c._vote === -1 && 'is-on'"
                                                    @click="vote(c, -1)" aria-label="Mínus">▼</button>
                                        </div>

                                        <div class="forum-reactions" role="group" aria-label="Reakce">
                                            <template x-for="emo in Object.entries(c.reactions)" :key="emo[0]">
                                                <button type="button" class="forum-reaction"
                                                        :class="(c._userReactions || []).includes(emo[0]) && 'is-on'"
                                                        @click="toggleReaction(c, emo[0])">
                                                    <span aria-hidden="true" x-text="emo[0]"></span>
                                                    <span x-text="emo[1]"></span>
                                                </button>
                                            </template>
                                            <div class="forum-reaction-picker" x-data="{ open: false }" @click.outside="open = false">
                                                <button type="button" class="forum-reaction forum-reaction--add" @click="open = !open" title="Přidat reakci">
                                                    + 🙂
                                                </button>
                                                <div class="forum-reaction-picker__pop" x-show="open" x-cloak x-transition>
                                                    <template x-for="emo in reactionsPalette" :key="emo">
                                                        <button type="button" class="forum-reaction-picker__btn"
                                                                @click="toggleReaction(c, emo); open = false;"
                                                                x-text="emo"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="forum-comment__rowend">
                                            <button type="button" class="forum-post__act"
                                                    x-show="canReply(c)"
                                                    @click="openReply(c.id)">
                                                <x-ui.icon name="arrow-left" :size="14" /> Odpovědět
                                            </button>
                                            <button type="button" class="forum-post__act"
                                                    x-show="canEditComment(c) && editingId !== c.id"
                                                    @click="startEdit(c)">
                                                <x-ui.icon name="edit" :size="14" /> Upravit
                                            </button>
                                        </div>
                                    </footer>

                                    {{-- Inline reply box --}}
                                    <form class="forum-replybox forum-replybox--inline"
                                          x-show="replyingTo === c.id" x-cloak
                                          @submit.prevent="submitReply(c.id, replyDraft);">
                                        <textarea class="field__input field__input--textarea forum-replybox__input"
                                                  rows="2"
                                                  placeholder="Odpovědět na komentář…"
                                                  x-model="replyDraft"
                                                  x-init="$nextTick(() => $el.focus())"></textarea>
                                        <div class="forum-replybox__btns">
                                            <button type="button" class="btn btn--text btn--sm" @click="replyingTo = null; replyDraft = ''">Zrušit</button>
                                            <button type="submit" class="btn btn--primary btn--sm" :disabled="replyDraft.trim().length < 2">Odpovědět</button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        </template>

                        <template x-if="sortedFlat.length === 0">
                            <li class="forum-empty">
                                <p>Zatím žádné komentáře. Buďte první!</p>
                            </li>
                        </template>
                    </ol>
                </div>

                {{-- ── Sidebar ───────────────────────────────────── --}}
                <aside class="forum-topic-aside">
                    <div class="forum-aside-card">
                        <h3 class="forum-aside-card__head">O autorovi</h3>
                        <div class="forum-aside-card__author">
                            <x-forum.avatar :user="$author" size="lg" :show-level="true" :levels="$levels" />
                            <div>
                                <a class="forum-aside-card__name" href="{{ Locale::url('/forum/uzivatel/' . $author['slug']) }}">{{ $author['name'] }}</a>
                                <x-forum.level-badge :level="$authorLevel" />
                                <p class="forum-aside-card__bio">{{ $author['bio'] ?? '—' }}</p>
                            </div>
                        </div>
                        <dl class="forum-aside-card__stats">
                            <div><dt>Příspěvků</dt><dd>{{ $author['posts'] }}</dd></div>
                            <div><dt>Témat</dt><dd>{{ $author['topics'] }}</dd></div>
                            <div><dt>Reputace</dt><dd>{{ $author['reputation'] }}</dd></div>
                        </dl>
                    </div>

                    <div class="forum-aside-card">
                        <h3 class="forum-aside-card__head">Účastníci ({{ count($members) }})</h3>
                        <ul class="forum-aside-card__members" role="list">
                            @foreach($members as $m)
                                @if($m)
                                    <li>
                                        <x-forum.avatar :user="$m" :href="Locale::url('/forum/uzivatel/' . $m['slug'])" size="sm" />
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <div class="forum-aside-card">
                        <h3 class="forum-aside-card__head">Pravidla diskuze</h3>
                        <ul class="forum-aside-card__rules">
                            <li>Žádné reklamy a odkazy na neoprávněné prodejce.</li>
                            <li>Respektujte ostatní — kritizujte myšlenku, ne člověka.</li>
                            <li>Zdroje a COA jsou vždy plus.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</x-layouts.app>
