// Account dashboard — shared Alpine component mounted on the account shell.
// Pages use the subset they need: avatar upload (profile), address CRUD
// (addresses), and the order-detail modal (orders).
const emptyAddress = () => ({
    id: null,
    city_id: null,
    city_label: '',
    phone: '',
    street: '',
    is_default: false,
});

export default () => ({
    // ── avatar ──────────────────────────────────────────────
    avatarUrl: null,
    avatarBusy: false,

    initAvatar(url) {
        this.avatarUrl = url || null;
    },

    async uploadAvatar(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        this.avatarBusy = true;
        const body = new FormData();
        body.append('avatar', file);
        try {
            const { data } = await window.axios.post('/ucet/avatar', body, {
                headers: { 'X-CSRF-TOKEN': this.csrf() },
            });
            this.avatarUrl = data.avatar_url;
            this.dispatchAvatar();
        } catch (e) {
            // validation or size error — keep current avatar
        } finally {
            this.avatarBusy = false;
            event.target.value = '';
        }
    },

    async removeAvatar() {
        this.avatarBusy = true;
        try {
            await window.axios.delete('/ucet/avatar', { headers: { 'X-CSRF-TOKEN': this.csrf() } });
            this.avatarUrl = null;
            this.dispatchAvatar();
        } finally {
            this.avatarBusy = false;
        }
    },

    dispatchAvatar() {
        // Let the header swap its avatar without a full reload.
        const img = document.querySelector('.header__avatar');
        if (img && this.avatarUrl) img.src = this.avatarUrl;
    },

    // ── addresses ───────────────────────────────────────────
    addresses: [],
    form: emptyAddress(),
    formOpen: false,
    editing: null,
    addrBusy: false,
    addrErrors: {},

    initAddresses(list) {
        this.addresses = Array.isArray(list) ? list : [];
    },

    addrError(field) {
        const e = this.addrErrors[field];
        return Array.isArray(e) ? e[0] : e;
    },

    newAddress() {
        this.form = emptyAddress();
        this.editing = null;
        this.addrErrors = {};
        this.formOpen = true;
    },

    editAddress(address) {
        this.form = {
            id: address.id,
            city_id: address.city_id,
            city_label: address.city?.full_label || '',
            phone: address.phone || '',
            street: address.street || '',
            is_default: !!address.is_default,
        };
        this.editing = address.id;
        this.addrErrors = {};
        this.formOpen = true;
    },

    async saveAddress() {
        this.addrBusy = true;
        this.addrErrors = {};
        const url = this.editing ? `/ucet/adresy/${this.editing}` : '/ucet/adresy';
        const method = this.editing ? 'put' : 'post';
        const payload = {
            city_id: this.form.city_id,
            street: this.form.street,
            phone: this.form.phone,
            is_default: this.form.is_default,
        };
        try {
            const { data } = await window.axios[method](url, payload, {
                headers: { 'X-CSRF-TOKEN': this.csrf() },
            });
            this.upsertAddress(data.data);
            this.formOpen = false;
        } catch (err) {
            if (err.response?.status === 422) this.addrErrors = err.response.data.errors || {};
        } finally {
            this.addrBusy = false;
        }
    },

    async deleteAddress(id) {
        if (!window.confirm(this.confirmText)) return;
        try {
            await window.axios.delete(`/ucet/adresy/${id}`, { headers: { 'X-CSRF-TOKEN': this.csrf() } });
            this.addresses = this.addresses.filter((a) => a.id !== id);
        } catch (e) { /* noop */ }
    },

    async makeDefault(id) {
        try {
            await window.axios.post(`/ucet/adresy/${id}/vychozi`, {}, { headers: { 'X-CSRF-TOKEN': this.csrf() } });
            this.addresses = this.addresses.map((a) => ({ ...a, is_default: a.id === id }));
        } catch (e) { /* noop */ }
    },

    upsertAddress(saved) {
        // Re-sort so the (possibly new) default floats to the top.
        if (saved.is_default) {
            this.addresses = this.addresses.map((a) => ({ ...a, is_default: false }));
        }
        const idx = this.addresses.findIndex((a) => a.id === saved.id);
        if (idx >= 0) this.addresses.splice(idx, 1, saved);
        else this.addresses.push(saved);
        this.addresses.sort((a, b) => Number(b.is_default) - Number(a.is_default) || a.id - b.id);
    },

    get confirmText() {
        return window.__accountConfirmDelete || 'Delete?';
    },

    // ── order detail modal ──────────────────────────────────
    order: null,
    orderOpen: false,
    orderLoading: false,

    async openOrder(publicId) {
        this.orderOpen = true;
        this.orderLoading = true;
        this.order = null;
        try {
            const { data } = await window.axios.get(`/ucet/objednavky/${publicId}`);
            this.order = data.data;
        } catch (e) {
            this.orderOpen = false;
        } finally {
            this.orderLoading = false;
        }
    },

    closeOrder() {
        this.orderOpen = false;
    },

    statusLabel(status) {
        return (window.__accountStatuses || {})[status] || status;
    },

    // ── helpers ─────────────────────────────────────────────
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
});
