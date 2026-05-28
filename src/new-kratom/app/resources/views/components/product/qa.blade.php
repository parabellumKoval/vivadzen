@props([
    'product' => [],
])

@php
    use App\Support\Locale;

    $questions = $product['questions'] ?? [];
    $questionsCount = $product['questionsCount'] ?? 0;
    $apiBase = Locale::url('/api/product/' . $product['slug']);
@endphp

<section
    class="section section--cream qa-section"
    id="otazky"
    aria-labelledby="qa-title"
    x-data="productQuestions({
        slug: @js($product['slug']),
        apiBase: @js($apiBase),
        seed: @js($questions),
        total: @js((int) $questionsCount),
    })"
>
    <div class="container container--narrow">
        <header class="qa-section__head">
            <div>
                <h2 id="qa-title" class="t-heading-xl t-on-light-accent">Otázky a odpovědi</h2>
                <p class="qa-section__sub"><span x-text="total"></span> dotazů · odpovídáme do 24 hodin</p>
            </div>
            <x-ui.button variant="primary" icon="arrow-right" x-on:click="openAskModal()">
                Položit otázku
            </x-ui.button>
        </header>

        <ul class="qa-list" x-show="items.length > 0">
            <template x-for="item in items" :key="item.id">
                <li class="qa-card">
                    <div class="qa-card__question-block">
                        <span class="qa-card__icon" aria-hidden="true">Q</span>
                        <div class="qa-card__question-body">
                            <p class="qa-card__meta">
                                <strong x-text="item.author"></strong>
                                <span class="qa-card__meta-dot">·</span>
                                <time x-text="item.date"></time>
                            </p>
                            <p class="qa-card__question" x-text="item.question"></p>
                        </div>
                    </div>

                    <div class="qa-card__answer-block" x-show="item.answer">
                        <span class="qa-card__icon qa-card__icon--answer" aria-hidden="true">A</span>
                        <div class="qa-card__answer-body">
                            <p class="qa-card__meta qa-card__meta--answer">
                                <strong x-text="item.answered_by || 'Tým Vivadzen'"></strong>
                                <span class="badge badge--subscription">Oficiální odpověď</span>
                            </p>
                            <p class="qa-card__answer" x-text="item.answer"></p>
                        </div>
                    </div>

                    <footer class="qa-card__foot">
                        <button type="button" class="qa-card__helpful" @click="markHelpful(item)" :disabled="item._busy">
                            <x-ui.icon name="check" :size="14" />
                            Užitečné (<span x-text="item.helpful"></span>)
                        </button>
                    </footer>
                </li>
            </template>
        </ul>

        <p class="qa-empty" x-show="!loading && items.length === 0">
            Zatím žádné dotazy. Položte první otázku!
        </p>

        <div class="qa-more" x-show="hasMore && items.length > 0">
            <x-ui.button variant="outline-light" x-on:click="loadMore()" ::disabled="loading">
                <span x-show="!loading">Načíst další dotazy</span>
                <span x-show="loading">Načítání…</span>
            </x-ui.button>
        </div>
    </div>

    {{-- Ask-question modal --}}
    <div
        class="rv-modal"
        x-cloak
        :class="modalOpen && 'is-open'"
        @keydown.escape.window="modalOpen = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="question-modal-title"
    >
        <div class="rv-modal__backdrop" @click="modalOpen = false"></div>

        <div class="rv-modal__panel">
            <header class="rv-modal__head">
                <h3 id="question-modal-title" class="rv-modal__title">Položit otázku k produktu {{ $product['name'] }}</h3>
                <button type="button" class="rv-modal__close" @click="modalOpen = false" aria-label="Zavřít">
                    <x-ui.icon name="x" />
                </button>
            </header>

            <form class="rv-modal__body" @submit.prevent="submitQuestion()">
                <div class="rv-modal__row rv-modal__row--two">
                    <label class="field">
                        <span class="field__label">Jméno *</span>
                        <input class="field__input" type="text" x-model.trim="form.author_name" required maxlength="120" />
                    </label>
                    <label class="field">
                        <span class="field__label">E-mail (nezveřejní se)</span>
                        <input class="field__input" type="email" x-model.trim="form.author_email" maxlength="190" />
                    </label>
                </div>

                <label class="field">
                    <span class="field__label">Vaše otázka *</span>
                    <textarea class="field__input field__input--textarea" x-model.trim="form.question" rows="5" minlength="5" maxlength="2000" required></textarea>
                    <span class="field__hint"><span x-text="form.question.length"></span> / 2000</span>
                </label>

                <p class="rv-modal__error" x-show="error" x-text="error"></p>
                <p class="rv-modal__success" x-show="success" x-text="success"></p>

                <footer class="rv-modal__foot">
                    <button type="button" class="btn btn--outline-light" @click="modalOpen = false">Zrušit</button>
                    <button type="submit" class="btn btn--primary" :disabled="submitting">
                        <span x-show="!submitting">Odeslat dotaz</span>
                        <span x-show="submitting">Odesílání…</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>
</section>
