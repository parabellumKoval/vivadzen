// Product detail buy-box: 25/50 g toggle, qty stepper, computed price, real add-to-cart.
import { cartStore } from '../cart.js';

export default ({ prices = { 25: 0, 50: 0 }, defaultSize = 50, unit = 'g', slug = '' } = {}) => ({
    size: defaultSize,
    qty: 1,
    prices,
    unit,
    slug,
    ctaLabel: window.__cartStrings?.add_to_cart || 'Přidat do košíku',
    busy: false,

    get unitPrice() {
        return this.prices[this.size] || 0;
    },

    get totalPrice() {
        return (this.unitPrice * this.qty).toLocaleString('cs-CZ');
    },

    get pricePerUnit() {
        if (!this.unitPrice) return 0;
        return (this.unitPrice / this.size).toFixed(1).replace('.', ',');
    },

    setSize(value) {
        this.size = value;
    },

    async addToCart() {
        if (this.busy || !this.slug) return;
        this.busy = true;
        const ok = await cartStore.add(this.slug, this.size, this.qty);
        const original = this.ctaLabel;
        if (ok) {
            this.ctaLabel = '✓ ' + (window.__cartStrings?.added || 'Added');
        }
        setTimeout(() => {
            this.ctaLabel = original;
            this.busy = false;
        }, 1500);
    },
});
