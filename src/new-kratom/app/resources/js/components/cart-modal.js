// Cart modal — listens to `cart:updated` from the global cart store
// and mirrors the snapshot into local reactive state. Opens on
// `cart:open` events (dispatched after add-to-cart) or on header
// cart-link click. Mutations call the global cartStore.
import { cartStore } from '../cart.js';

export default () => ({
    open: false,
    items: [],
    count: 0,
    subtotal: 0,
    discount: 0,
    total: 0,
    promo: null,
    promoCode: '',
    promoError: false,
    busyKey: null,

    init() {
        this.sync(cartStore);

        document.addEventListener('cart:updated', (e) => {
            this.sync(e.detail || {});
        });

        document.addEventListener('cart:open', () => {
            this.open = true;
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.open = false;
        });

        // Intercept clicks on the header cart link → open modal instead.
        document.addEventListener('click', (e) => {
            const link = e.target.closest('[data-cart-link]');
            if (!link) return;
            // Allow opening real cart page via Cmd/Ctrl+click or middle-click.
            if (e.metaKey || e.ctrlKey || e.button === 1) return;
            e.preventDefault();
            this.open = true;
        });
    },

    sync(snapshot) {
        this.items = snapshot.items || [];
        this.count = snapshot.count || 0;
        this.subtotal = snapshot.subtotal || 0;
        this.discount = snapshot.discount || 0;
        this.total = snapshot.total || 0;
        this.promo = snapshot.promo || null;
    },

    format(value) {
        return cartStore.formatPrice(value || 0);
    },

    async increment(key) {
        const item = this.items.find((i) => i.key === key);
        if (!item) return;
        this.busyKey = key;
        await cartStore.update(key, item.qty + 1);
        this.busyKey = null;
    },

    async decrement(key) {
        const item = this.items.find((i) => i.key === key);
        if (!item) return;
        this.busyKey = key;
        await cartStore.update(key, Math.max(0, item.qty - 1));
        this.busyKey = null;
    },

    async remove(key) {
        this.busyKey = key;
        await cartStore.remove(key);
        this.busyKey = null;
    },

    async changeSize(key, size) {
        this.busyKey = key;
        await cartStore.update(key, undefined, size);
        this.busyKey = null;
    },

    async applyPromo() {
        if (!this.promoCode.trim()) return;
        this.promoError = false;
        const ok = await cartStore.applyPromo(this.promoCode.trim());
        if (!ok) this.promoError = true;
        else this.promoCode = '';
    },

    async removePromo() {
        await cartStore.removePromo();
    },
});
