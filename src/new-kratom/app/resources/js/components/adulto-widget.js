// ADULTO age-verification widget (https://api.js.m2a.cz/api.js).
//
// Lifecycle:
//  1. Lazily load the upstream script once, sharing the promise across components.
//  2. Mount a `.adulto-cz` placeholder inside `#adulto-widget` so the upstream
//     script can hydrate it. If a `createVerificationWidget` API is exposed on
//     window.Adulto, we use it as a callback-based render. Otherwise we let the
//     widget render itself and observe the DOM for the `uid` input ADULTO
//     populates on success.
//  3. Push the resolved UID into a hidden form input (#age_verification_uid)
//     and re-evaluate the checkout `canSubmit` gate by toggling `verified`.
const SCRIPT_ID = 'adulto-widget-script';
const UID_SELECTORS = [
    'input[name="adultocz-uid"]',
    'input[name="adultocz_verify_uid"]',
    'input[name="age_verification_uid"]',
];

let scriptPromise = null;

function loadScript(scriptUrl) {
    if (scriptPromise) return scriptPromise;

    scriptPromise = new Promise((resolve, reject) => {
        const existing = document.getElementById(SCRIPT_ID);
        if (existing) {
            if (existing.dataset.state === 'loaded') return resolve();
            existing.addEventListener('load', () => resolve(), { once: true });
            existing.addEventListener('error', () => reject(new Error('adulto script failed')), { once: true });
            return;
        }
        const s = document.createElement('script');
        s.id = SCRIPT_ID;
        s.async = true;
        s.src = scriptUrl;
        s.dataset.state = 'loading';
        s.onload = () => { s.dataset.state = 'loaded'; resolve(); };
        s.onerror = () => reject(new Error('adulto script failed'));
        document.head.appendChild(s);
    });

    return scriptPromise;
}

function readUidFromDom() {
    for (const sel of UID_SELECTORS) {
        const el = document.querySelector(sel);
        const value = String(el?.value || '').trim();
        if (value) return value;
    }
    return '';
}

export default (config = {}) => ({
    publicKey: String(config.publicKey || '').trim(),
    scriptUrl: String(config.scriptUrl || 'https://api.js.m2a.cz/api.js').trim(),
    verified: Boolean(config.initialUid),
    uid: String(config.initialUid || ''),
    loading: false,
    error: '',
    observer: null,

    init() {
        if (!this.publicKey) {
            this.error = this.$el.dataset.unavailableLabel || 'ADULTO key missing';
            return;
        }
        this.mountFallback();
        this.loading = true;
        loadScript(this.scriptUrl)
            .then(() => this.startWidget())
            .catch(() => {
                this.error = this.$el.dataset.errorLabel || 'Failed to load ADULTO widget';
            })
            .finally(() => { this.loading = false; });

        // Observe DOM for the UID input ADULTO injects when its self-rendered
        // fallback is in use.
        this.observer = new MutationObserver(() => this.syncUid());
        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['value'],
        });
    },

    destroy() {
        this.observer?.disconnect();
        this.observer = null;
    },

    mountFallback() {
        const container = this.$refs.container;
        if (!container) return;
        if (!container.querySelector('.adulto-cz')) {
            const div = document.createElement('div');
            div.className = 'adulto-cz';
            div.setAttribute('data-sitekey', this.publicKey);
            container.appendChild(div);
        }
    },

    startWidget() {
        const api = window.Adulto;
        const container = this.$refs.container;
        if (api && typeof api.createVerificationWidget === 'function' && container?.id) {
            container.innerHTML = '';
            api.createVerificationWidget({
                elementId: container.id,
                publicKey: this.publicKey,
                siteKey: this.publicKey,
                onSuccess: (uid) => this.acceptUid(uid),
                onError: () => {
                    this.error = this.$el.dataset.errorLabel || 'Verification failed';
                },
            });
            return;
        }
        // Upstream script renders straight into `.adulto-cz` — the observer
        // will pick the UID up.
        this.syncUid();
    },

    syncUid() {
        const value = readUidFromDom();
        if (value && value !== this.uid) {
            this.acceptUid(value);
        }
    },

    acceptUid(value) {
        this.uid = String(value || '').trim();
        this.verified = this.uid.length > 0;
        this.error = '';
        const hidden = document.getElementById('age_verification_uid');
        if (hidden) {
            hidden.value = this.uid;
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    },

    openGuide() {
        window.dispatchEvent(new CustomEvent('adulto:open'));
    },
});
