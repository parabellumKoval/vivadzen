// Cart page Alpine component — drives the /kosik view.
import { cartStore } from '../cart.js';

export default (initial) => ({
    items: initial.items || [],
    subtotal: initial.subtotal || 0,
    discount: initial.discount || 0,
    total: initial.total || 0,
    promo: initial.promo || null,
    promoCode: '',
    promoError: '',
    busyKey: null,

    init() {
        cartStore.apply(initial);
        document.addEventListener('cart:updated', (e) => {
            const s = e.detail;
            this.items = s.items;
            this.subtotal = s.subtotal;
            this.discount = s.discount;
            this.total = s.total;
            this.promo = s.promo;
        });
    },

    format(value) {
        return cartStore.formatPrice(value);
    },

    async increment(key) {
        const it = this.items.find((i) => i.key === key);
        if (!it) return;
        this.busyKey = key;
        await cartStore.update(key, it.qty + 1);
        this.busyKey = null;
    },

    async decrement(key) {
        const it = this.items.find((i) => i.key === key);
        if (!it || it.qty <= 1) return;
        this.busyKey = key;
        await cartStore.update(key, it.qty - 1);
        this.busyKey = null;
    },

    async changeSize(key, size) {
        this.busyKey = key;
        await cartStore.update(key, undefined, size);
        this.busyKey = null;
    },

    async remove(key) {
        this.busyKey = key;
        await cartStore.remove(key);
        this.busyKey = null;
    },

    async applyPromo() {
        this.promoError = '';
        if (!this.promoCode.trim()) return;
        const ok = await cartStore.applyPromo(this.promoCode.trim());
        if (!ok) {
            this.promoError = 'invalid';
        } else {
            this.promoCode = '';
        }
    },

    async removePromo() {
        await cartStore.removePromo();
    },
});
