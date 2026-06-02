{{--
    Generic edit modal for forum topics and posts. Hosts on the parent
    `accountPage` Alpine scope (editorOpen/editorKind/editorTitle/editorBody/
    editorBusy/editorError/saveEditor/closeEditor).
--}}
<div
    class="address-modal"
    :class="editorOpen && 'is-open'"
    @keydown.escape.window="closeEditor()"
>
    <div class="address-modal__backdrop" @click="closeEditor()"></div>

    <div
        class="address-modal__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="editor-modal-title"
    >
        <header class="address-modal__head">
            <h2 id="editor-modal-title" class="address-modal__title"
                x-text="editorKind === 'topic'
                    ? (window.__accountStrings?.editTopicTitle || 'Edit topic')
                    : (window.__accountStrings?.editPostTitle || 'Edit reply')"></h2>
            <button type="button" class="address-modal__close" @click="closeEditor()" aria-label="{{ __('site.header.close') }}">
                <x-ui.icon name="x" :size="18" />
            </button>
        </header>

        <form class="address-modal__body account__form" @submit.prevent="saveEditor()">
            <template x-if="editorKind === 'topic'">
                <div class="field">
                    <label class="field__label" for="editor-title">{{ __('site.account.forum.title_label') }}</label>
                    <input id="editor-title" type="text" class="input" x-model="editorTitle" maxlength="160" required />
                </div>
            </template>

            <div class="field">
                <label class="field__label" for="editor-body">{{ __('site.account.forum.body_label') }}</label>
                <textarea id="editor-body" class="input" rows="8" x-model="editorBody" required></textarea>
            </div>

            <p class="field__error" x-show="editorError" x-text="editorError"></p>

            <div class="address-modal__actions">
                <button type="button" class="btn btn--ghost btn--md" @click="closeEditor()" :disabled="editorBusy">
                    {{ __('site.account.forum.cancel') }}
                </button>
                <button type="submit" class="btn btn--primary btn--md" :disabled="editorBusy">
                    {{ __('site.account.forum.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
