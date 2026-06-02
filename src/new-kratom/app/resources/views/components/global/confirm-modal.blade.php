{{--
    Universal confirm dialog. Mounted once in the app layout; opened by
    dispatching `window.dispatchEvent(new CustomEvent('confirm:open', { detail }))`.

    detail = { title?, message?, confirm?, cancel?, tone?: 'default'|'danger', onConfirm?(): void|Promise }
--}}
<div
    x-data="confirmModal"
    class="confirm-modal"
    :class="open && 'is-open'"
    @keydown.escape.window="cancel()"
>
    <div class="confirm-modal__backdrop" @click="cancel()"></div>

    <div
        class="confirm-modal__panel"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
    >
        <div class="confirm-modal__icon" :class="tone === 'danger' && 'confirm-modal__icon--danger'">
            <template x-if="tone === 'danger'"><x-ui.icon name="trash" :size="22" /></template>
            <template x-if="tone !== 'danger'"><x-ui.icon name="alert-circle" :size="22" /></template>
        </div>

        <h2 id="confirm-modal-title" class="confirm-modal__title" x-text="title"></h2>
        <p class="confirm-modal__message" x-text="message" x-show="message"></p>

        <div class="confirm-modal__actions">
            <button type="button" class="btn btn--ghost btn--md" @click="cancel()" x-text="cancelLabel"></button>
            <button type="button"
                    class="btn btn--md"
                    :class="tone === 'danger' ? 'btn--terracotta' : 'btn--primary'"
                    :disabled="busy"
                    @click="confirm()"
                    x-text="confirmLabel"></button>
        </div>
    </div>
</div>
