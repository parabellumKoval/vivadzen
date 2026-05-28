// MiniProductCard — переключатель 25/50 g, расчёт price-per-g + add-to-cart.
import { cartStore } from '../cart.js';

export default ({ price25 = 0, price50 = 0, slug = '' } = {}) => ({
    size: 25,
    slug,
    prices: { 25: price25, 50: price50 },
    busy: false,
    added: false,

    get price() { return this.prices[this.size]; },

    get pricePerGram() {
        if (!this.price) return 0;
        return (this.price / this.size).toFixed(1);
    },

    setSize(value) { this.size = value; },

    async addToCart() {
        if (this.busy || !this.slug) return;
        this.busy = true;
        const ok = await cartStore.add(this.slug, this.size, 1);
        if (ok) {
            this.added = true;
            setTimeout(() => { this.added = false; }, 1500);
        }
        this.busy = false;
    },
});
