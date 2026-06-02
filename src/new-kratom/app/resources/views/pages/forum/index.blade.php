{{--
    /forum — главная страница форума.

    Поля:
      • featured  — карточки сверху (3 темы с обложкой)
      • topTopics — сайдбар «Top topics»
      • topics    — список всех тем (Alpine ведёт пагинацию, поиск, фильтр)
      • categories, users, levels, stats — справочники для UI

    Вся интерактивность (поиск, фильтрация по категориям, пагинация,
    переключение реакций/оценок на превью) — в Alpine.js (forumIndex).
--}}
@php
    use App\Support\Locale;

    // Подготовим index пользователей: id => user (для быстрого
    // доступа в Alpine без дополнительных проходов).
    $usersById = collect($users)->keyBy('id')->all();
@endphp

<x-layouts.app
    title="Forum Vivadzen — komunita kratomistů"
    description="Komunitní fórum o kratomu — odrůdy, příprava, legislativa, recenze. Sdílejte zkušenosti s ověřenými uživateli."
>
    <div
        class="forum"
        style="--forum-paper-left: url('{{ asset('assets/leaf-left.png') }}'); --forum-paper-right: url('{{ asset('assets/leaf-right.png') }}');"
        x-data="forumIndex({
            topics: @js($topics),
            users: @js($usersById),
            levels: @js($levels),
            categories: @js($categories)
        })"
    >
        {{-- ── Hero + Featured (общая сцена с фоновыми изображениями) ── --}}
        <div
            class="forum-stage"
            style="--forum-stage-left: url('{{ asset('assets/forum-left.png') }}'); --forum-stage-right: url('{{ asset('assets/forum-right.png') }}');"
        >
            <section class="forum-hero" aria-labelledby="forum-hero-title">
                <div class="container forum-hero__inner">
                    <div class="forum-hero__copy">
                        <p class="t-overline section-head__eyebrow--grass">FORUM</p>
                        <h1 id="forum-hero-title" class="t-display-md t-on-dark mt-3">Komunita kratomistů</h1>
                        <p class="forum-hero__lead">
                            Sdílejte zkušenosti, ptejte se zkušenějších, čtěte recenze odrůd
                            a sledujte novinky o legislativě. Bez reklam, bez spamu — jen lidi a kratom.
                        </p>
                    </div>

                    <ul class="forum-hero__stats" role="list">
                        <li><strong>{{ number_format($stats['topics']) }}</strong><span>diskuzí</span></li>
                        <li><strong>{{ number_format($stats['posts']) }}</strong><span>příspěvků</span></li>
                        <li><strong>{{ number_format($stats['members']) }}</strong><span>členů</span></li>
                        <li class="is-online"><strong>{{ $stats['online'] }}</strong><span>online</span></li>
                    </ul>
                </div>
            </section>

            {{-- ── Featured + top topics ─────────────────────────────── --}}
            <section class="forum-section forum-section--dark" aria-label="Vybraná témata">
                <div class="container forum-grid">
                    <div class="forum-grid__main">
                        <div class="forum-featured">
                            @foreach($featured as $t)
                                <x-forum.topic-card
                                    :topic="$t"
                                    :author="$usersById[$t['authorId']] ?? null"
                                />
                            @endforeach
                        </div>
                    </div>

                    <aside class="forum-grid__aside" aria-label="Nejlepší témata">
                        <div class="forum-aside">
                            <h2 class="forum-aside__head">TOP TÉMATA</h2>
                            <ol class="forum-aside__list" role="list">
                                @foreach($topTopics as $t)
                                    <li class="forum-aside__item">
                                        <span class="forum-aside__emoji" aria-hidden="true">{{ $t['emoji'] }}</span>
                                        <div class="forum-aside__body">
                                            <a class="forum-aside__title" href="{{ Locale::url('/forum/tema/' . $t['slug']) }}">{{ $t['title'] }}</a>
                                            <span class="forum-aside__meta">
                                                <x-ui.icon name="thumbs-up" :size="12" />
                                                {{ $t['replies'] }} komentářů
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </aside>
                </div>
            </section>

            <svg class="forum-stage__wave" viewBox="0 0 1440 104" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 40C160 70 318 9 505 34c213 29 401 28 594-1 145-21 232-19 341 11v60H0V40z" fill="currentColor"/>
            </svg>
        </div>

        {{-- ── All discussions list ──────────────────────────────── --}}
        <section class="forum-section forum-section--paper" aria-labelledby="forum-all-title">
            <div class="container">
                <header class="forum-listhead">
                    <div class="forum-listhead__heading">
                        <h2 id="forum-all-title" class="forum-listhead__title">VŠECHNY DISKUZE</h2>
                        <p class="forum-listhead__sub">
                            <span x-text="filtered.length"></span> diskuzí
                        </p>
                    </div>

                    <form
                        class="forum-search forum-search--light forum-listhead__search"
                        role="search"
                        @submit.prevent="search()"
                    >
                        <label class="sr-only" for="forum-search">Hledat ve fóru</label>
                        <input
                            id="forum-search"
                            type="search"
                            class="forum-search__input"
                            placeholder="Hledat ve fóru…"
                            x-model.debounce.300ms="query"
                            @input="search()"
                        />
                        <button type="submit" class="forum-search__btn" aria-label="Hledat">
                            <x-ui.icon name="search" :size="18" />
                        </button>
                    </form>

                    <div class="forum-listhead__actions">
                        <x-ui.button href="{{ Locale::url('/forum/nove-tema') }}" variant="primary" icon="plus" iconPosition="left">
                            Vytvořit nové téma
                        </x-ui.button>
                    </div>
                </header>

                {{-- Categories + sort на одной линии --}}
                <div class="forum-toolbar">
                    <div class="chip-row__scroll forum-cats" role="tablist" aria-label="Kategorie">
                        @foreach($categories as $key => $cat)
                            <button
                                type="button"
                                class="chip forum-cat"
                                :class="category === @js($key) && 'chip--active'"
                                @click="setCategory(@js($key))"
                                role="tab"
                            >
                                <span aria-hidden="true">{{ $cat['icon'] }}</span>
                                {{ $cat['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="forum-sort">
                        <label class="rvp-select">
                            <span class="sr-only">Řazení</span>
                            <select class="rvp-select__field" x-model="sort">
                                <option value="recent">Nejnovější aktivita</option>
                                <option value="new">Nově vytvořené</option>
                                <option value="hot">Nejaktivnější</option>
                                <option value="top">Nejlepší</option>
                            </select>
                            <span class="rvp-select__icon"><x-ui.icon name="chevron-down" :size="16" /></span>
                        </label>
                    </div>
                </div>

                {{-- Topic rows --}}
                <ul class="forum-list" role="list">
                    <template x-for="t in paged" :key="t.id">
                        <li class="forum-row" :class="t.isPinned && 'is-pinned'">
                            <div class="forum-row__main">
                                <div class="forum-row__titlewrap">
                                    <a class="forum-row__title" :href="topicHref(t.slug)">
                                        <span class="forum-row__title-emoji" aria-hidden="true" x-text="t.emoji"></span>
                                        <span class="forum-row__title-text" x-text="t.title"></span>
                                    </a>
                                    <template x-if="t.isPinned">
                                        <span class="forum-row__tag forum-row__tag--pin" title="Připnuto">📌</span>
                                    </template>
                                    <template x-if="t.isHot">
                                        <span class="forum-row__tag forum-row__tag--hot">HOT</span>
                                    </template>
                                </div>
                                <div class="forum-row__meta">
                                    <span class="forum-row__cat" x-text="categoryLabel(t.category)"></span>
                                    <span class="forum-row__sep">·</span>
                                    <span x-text="t.replies + ' komentářů'"></span>
                                    <span class="forum-row__sep">·</span>
                                    <span x-text="t.views + ' zobrazení'"></span>
                                </div>
                            </div>

                            <div class="forum-row__reply">
                                <template x-if="userById(t.lastReply.userId)">
                                    <div class="forum-row__reply-by">
                                        <span class="favatar favatar--xs"
                                              :style="`--favatar-bg: ${userById(t.lastReply.userId).avatarColor}`">
                                            <span class="favatar__initials" x-text="initials(userById(t.lastReply.userId).name)"></span>
                                        </span>
                                        <a class="forum-row__reply-name"
                                           :href="userHref(userById(t.lastReply.userId).slug)"
                                           x-text="userById(t.lastReply.userId).name"></a>
                                    </div>
                                </template>
                                <time class="forum-row__reply-date" :datetime="t.lastReply.at" x-text="formatDate(t.lastReply.at)"></time>
                                <p class="forum-row__reply-excerpt" x-text="t.lastReply.excerpt"></p>
                            </div>

                            <div class="forum-row__members" aria-label="Účastníci diskuze">
                                <template x-for="(uid, i) in t.memberIds.slice(0, 3)" :key="uid">
                                    <template x-if="userById(uid)">
                                        <a class="favatar favatar--xs forum-row__member"
                                           :href="userHref(userById(uid).slug)"
                                           :style="`--favatar-bg: ${userById(uid).avatarColor}; --i: ${i}`"
                                           :title="userById(uid).name">
                                            <span class="favatar__initials" x-text="initials(userById(uid).name)"></span>
                                        </a>
                                    </template>
                                </template>
                                <template x-if="t.memberIds.length > 3">
                                    <span class="forum-row__member-more" :title="`+${t.memberIds.length - 3}`">
                                        +<span x-text="t.memberIds.length - 3"></span>
                                    </span>
                                </template>
                            </div>
                        </li>
                    </template>

                    <template x-if="paged.length === 0">
                        <li class="forum-empty">
                            <p>Pro zvolený filtr zatím nejsou žádné diskuze.</p>
                            <x-ui.button href="{{ Locale::url('/forum/nove-tema') }}" variant="primary" icon="plus" iconPosition="left">
                                Vytvořit první téma
                            </x-ui.button>
                        </li>
                    </template>
                </ul>

                <div class="forum-pager" x-show="totalPages > 1">
                    <button type="button" class="btn btn--outline-light btn--sm" :disabled="page === 1" @click="prevPage()">
                        ← Předchozí
                    </button>
                    <span class="forum-pager__info">
                        Strana <span x-text="page"></span> z <span x-text="totalPages"></span>
                    </span>
                    <button type="button" class="btn btn--outline-light btn--sm" :disabled="page === totalPages" @click="nextPage()">
                        Další →
                    </button>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
