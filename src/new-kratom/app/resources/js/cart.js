// =============================================================
// VIVADZEN — Cart client logic
//
// Backend: session-based cart via /kosik/* endpoints.
// Each mutation returns the full cart snapshot which is reused
// to update header badge + cart drawer / page.
// =============================================================

const csrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const post = async (url, body = {}) => {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    return res.json().catch(() => ({}));
};

const get = async (url) => {
    const res = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });
    return res.json().catch(() => ({}));
};

const formatPrice = (value) =>
    new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 })
        .format(value);

const updateHeaderBadge = (count) => {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = count;
        if (count > 0) {
            el.removeAttribute('hidden');
        } else {
            el.setAttribute('hidden', '');
        }
    });
};

const showToast = (message) => {
    let toast = document.getElementById('cart-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cart-toast';
        toast.className = 'cart-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('is-visible');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('is-visible'), 2800);
};

export const cartStore = {
    count: 0,
    items: [],
    subtotal: 0,
    discount: 0,
    total: 0,
    promo: null,

    apply(snapshot) {
        if (!snapshot) return;
        this.count = snapshot.count || 0;
        this.items = snapshot.items || [];
        this.subtotal = snapshot.subtotal || 0;
        this.discount = snapshot.discount || 0;
        this.total = snapshot.total || 0;
        this.promo = snapshot.promo || null;
        updateHeaderBadge(this.count);
        document.dispatchEvent(new CustomEvent('cart:updated', { detail: snapshot }));
    },

    async refresh() {
        const data = await get('/kosik/data');
        this.apply(data);
    },

    async add(slug, size, qty = 1) {
        const data = await post('/kosik/pridat', { slug, size, qty });
        if (data && data.ok) {
            this.apply(data.cart);
            showToast(window.__cartStrings?.added || 'Added to cart');
            document.dispatchEvent(new CustomEvent('cart:open'));
            return true;
        }
        return false;
    },

    async update(key, qty, size) {
        const body = { key };
        if (qty !== undefined) body.qty = qty;
        if (size !== undefined) body.size = size;
        const data = await post('/kosik/aktualizovat', body);
        if (data && data.ok) this.apply(data.cart);
    },

    async remove(key) {
        const data = await post('/kosik/odebrat', { key });
        if (data && data.ok) this.apply(data.cart);
    },

    async applyPromo(code) {
        const data = await post('/kosik/promo', { code, action: 'apply' });
        if (data && data.ok) {
            this.apply(data.cart);
            return true;
        }
        if (data && data.cart) this.apply(data.cart);
        return false;
    },

    async removePromo() {
        const data = await post('/kosik/promo', { action: 'remove' });
        if (data && data.ok) this.apply(data.cart);
    },

    formatPrice,
};

// Bootstrap: wire up [data-add-to-cart] buttons.
export const initCart = (initialSnapshot) => {
    if (initialSnapshot) cartStore.apply(initialSnapshot);

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-add-to-cart]');
        if (!btn) return;
        e.preventDefault();
        const slug = btn.dataset.slug;
        const size = parseInt(btn.dataset.size || '25', 10);
        const qty = parseInt(btn.dataset.qty || '1', 10);
        if (!slug) return;
        btn.disabled = true;
        const original = btn.textContent;
        btn.dataset.originalText = original;
        const ok = await cartStore.add(slug, size, qty);
        btn.disabled = false;
        if (ok) {
            btn.classList.add('is-added');
            setTimeout(() => btn.classList.remove('is-added'), 1500);
        }
    });
};
