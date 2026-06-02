// Universal confirm dialog. Mounted once in the app layout; any code path
// triggers it by dispatching a `confirm:open` event with detail describing
// the prompt and `onConfirm`. The onConfirm callback may be async; the
// confirm button is disabled until it settles.
export default () => ({
    open: false,
    busy: false,
    title: '',
    message: '',
    confirmLabel: 'OK',
    cancelLabel: 'Cancel',
    tone: 'default',
    _onConfirm: null,

    init() {
        window.addEventListener('confirm:open', (e) => this.show(e.detail || {}));
    },

    show(opts) {
        this.title = opts.title || 'Are you sure?';
        this.message = opts.message || '';
        this.confirmLabel = opts.confirm || 'Confirm';
        this.cancelLabel = opts.cancel || 'Cancel';
        this.tone = opts.tone === 'danger' ? 'danger' : 'default';
        this._onConfirm = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
        this.busy = false;
        this.open = true;
    },

    async confirm() {
        if (this.busy) return;
        const cb = this._onConfirm;
        if (!cb) { this.close(); return; }
        this.busy = true;
        try {
            await cb();
            this.close();
        } catch (e) {
            // Caller is responsible for surfacing the error elsewhere.
            this.close();
        }
    },

    cancel() {
        if (this.busy) return;
        this.close();
    },

    close() {
        this.open = false;
        this.busy = false;
        this._onConfirm = null;
    },
});
