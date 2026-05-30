{{--
    /forum/nove-tema — создание новой темы. Без бэкенда:
    форма «submits» только в локальный state — мы показываем preview
    и редиректим обратно на /forum (как заглушку).
--}}
@php
    use App\Support\Locale;
@endphp

<x-layouts.app
    title="Nové téma — Forum Vivadzen"
    description="Vytvořte nové téma v komunitním fóru o kratomu."
    :announcement="false"
>
    <div
        class="forum forum--narrow"
        x-data="forumNew({
            categories: @js($categories),
            emojis: @js($emojis),
            currentUser: @js($currentUser),
            endpoint: @js($storeEndpoint)
        })"
    >
        <section class="forum-section forum-section--paper">
            <div class="container forum-new__container">
                <nav class="forum-crumbs t-on-light-2" aria-label="Drobečková navigace">
                    <a href="{{ Locale::url('/forum') }}">Forum</a>
                    <span aria-hidden="true">/</span>
                    <span>Nové téma</span>
                </nav>

                <header class="forum-new__head">
                    <h1 class="t-display-sm t-on-light-accent">Vytvořit nové téma</h1>
                    <p class="forum-new__lead">
                        Stručný a popisný název, kontext v těle, kategorie a emodži pro snadnou navigaci.
                    </p>
                </header>

                @guest
                    <div class="forum-new forum-new--guest">
                        <p class="forum-new__lead">Nové téma může založit jen přihlášený člen komunity.</p>
                        <button type="button" class="btn btn--primary" @click="$dispatch('auth:open', { view: 'login' })">Přihlásit se</button>
                    </div>
                @else
                <form class="forum-new" @submit.prevent="submit()">
                    {{-- Cover image (optional, drag-drop / browse) --}}
                    <div class="forum-new__cover">
                        <label class="forum-new__cover-label">
                            <span class="forum-new__cover-title">Pozadí tématu (volitelné)</span>
                            <span class="forum-new__cover-hint">JPG, PNG nebo WebP, max 5 MB.</span>
                        </label>

                        <div class="forum-new__cover-area"
                             :class="coverPreview && 'has-image'"
                             :style="coverPreview ? `background-image: url(${coverPreview})` : null">
                            <template x-if="!coverPreview">
                                <div class="forum-new__cover-empty">
                                    <x-ui.icon name="upload" :size="28" />
                                    <span>Klikněte nebo přetáhněte obrázek</span>
                                </div>
                            </template>
                            <input
                                class="forum-new__cover-input"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="onCover($event)"
                            />
                            <template x-if="coverPreview">
                                <button type="button" class="forum-new__cover-remove" @click.stop.prevent="removeCover()" aria-label="Odebrat obrázek">×</button>
                            </template>
                        </div>
                    </div>

                    {{-- Title --}}
                    <label class="field">
                        <span class="field__label">Název tématu *</span>
                        <input
                            class="field__input"
                            type="text"
                            x-model.trim="form.title"
                            maxlength="120"
                            required
                            placeholder="Např. Maeng Da Green vs White — co pro vás funguje?"
                        />
                        <span class="field__hint"><span x-text="form.title.length"></span> / 120</span>
                    </label>

                    {{-- Emoji + category row --}}
                    <div class="forum-new__row">
                        <div class="field">
                            <span class="field__label">Emoji tématu</span>
                            <div class="forum-emoji" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" class="forum-emoji__current" @click="open = !open">
                                    <span class="forum-emoji__icon" aria-hidden="true" x-text="form.emoji"></span>
                                    <span class="forum-emoji__label">Změnit</span>
                                </button>
                                <div class="forum-emoji__pop" x-show="open" x-cloak x-transition>
                                    <template x-for="e in emojis" :key="e">
                                        <button
                                            type="button"
                                            class="forum-emoji__btn"
                                            :class="form.emoji === e && 'is-on'"
                                            @click="form.emoji = e; open = false"
                                            x-text="e"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <label class="field">
                            <span class="field__label">Kategorie *</span>
                            <select class="field__input" x-model="form.category" required>
                                <template x-for="(cat, key) in nonAllCategories" :key="key">
                                    <option :value="key" x-text="`${cat.icon}  ${cat.label}`"></option>
                                </template>
                            </select>
                        </label>
                    </div>

                    {{-- Body --}}
                    <label class="field">
                        <span class="field__label">Obsah *</span>
                        <textarea
                            class="field__input field__input--textarea"
                            rows="10"
                            x-model="form.body"
                            minlength="20"
                            maxlength="8000"
                            required
                            placeholder="Popište kontext, co vás zajímá, co už víte. Konkrétní otázka pomůže rychlejší a kvalitnější odpovědi."
                        ></textarea>
                        <span class="field__hint"><span x-text="form.body.length"></span> / 8000</span>
                    </label>

                    {{-- Preview --}}
                    <div class="forum-new__preview" x-show="showPreview" x-cloak>
                        <h3 class="forum-new__preview-head">Náhled</h3>
                        <article class="forum-card forum-card--preview">
                            <div class="forum-card__cover"
                                 :class="!coverPreview && 'forum-card__cover--default'"
                                 :style="coverPreview ? `background-image: url(${coverPreview}); background-size: cover; background-position: center;` : null">
                                <span class="forum-card__emoji" aria-hidden="true" x-text="form.emoji"></span>
                            </div>
                            <div class="forum-card__body">
                                <h3 class="forum-card__title" x-text="form.title || 'Název tématu se zobrazí zde'"></h3>
                                <p class="forum-new__preview-excerpt" x-text="bodyExcerpt"></p>
                            </div>
                        </article>
                    </div>

                    {{-- Actions --}}
                    <footer class="forum-new__foot">
                        <button type="button" class="btn btn--text" @click="showPreview = !showPreview">
                            <span x-show="!showPreview">Zobrazit náhled</span>
                            <span x-show="showPreview" x-cloak>Skrýt náhled</span>
                        </button>
                        <div class="forum-new__foot-actions">
                            <a href="{{ Locale::url('/forum') }}" class="btn btn--outline-light">Zrušit</a>
                            <button type="submit" class="btn btn--primary" :disabled="!canSubmit || submitting">
                                <span x-show="!submitting">Vytvořit téma</span>
                                <span x-show="submitting" x-cloak>Vytváření…</span>
                            </button>
                        </div>
                    </footer>

                    <p class="forum-new__notice forum-new__notice--error" x-show="error" x-cloak x-text="error"></p>
                    <p class="forum-new__notice" x-show="submitted" x-cloak>
                        Téma bylo vytvořeno. Přesměrovávám do diskuze…
                    </p>
                </form>
                @endguest
            </div>
        </section>
    </div>
</x-layouts.app>
