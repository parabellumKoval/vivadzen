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

    async deleteAddress(address) {
        const strings = window.__accountStrings || {};
        window.dispatchEvent(new CustomEvent('confirm:open', {
            detail: {
                title: strings.addressDeleteTitle || this.confirmText,
                message: address?.city?.full_label
                    ? `${address.city.full_label}${address.street ? ', ' + address.street : ''}`
                    : '',
                confirm: strings.confirmDelete || 'Delete',
                cancel: strings.cancel || 'Cancel',
                tone: 'danger',
                onConfirm: async () => {
                    await window.axios.delete(`/ucet/adresy/${address.id}`, {
                        headers: { 'X-CSRF-TOKEN': this.csrf() },
                    });
                    this.addresses = this.addresses.filter((a) => a.id !== address.id);
                },
            },
        }));
    },

    async makeDefault(id) {
        try {
            await window.axios.post(`/ucet/adresy/${id}/vychozi`, {}, { headers: { 'X-CSRF-TOKEN': this.csrf() } });
            this.addresses = this.addresses.map((a) => ({ ...a, is_default: a.id === id }));
        } catch (e) { /* noop */ }
    },

    // Star toggle on the card: turns a non-default into default. We don't
    // support "unset" here — the server keeps at least one default if one is
    // requested. Clicking the star on an already-default address is a no-op.
    async toggleDefault(address) {
        if (address.is_default) return;
        await this.makeDefault(address.id);
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

    // ── reviews / forum cards: tracked deletions ────────────
    // Pages drive a Set of soft-deleted ids and hide the matching cards with
    // `x-show`. The actual server delete fires from the confirm modal's
    // `onConfirm`; cancelling does nothing.
    deletedReviews: {},
    deletedTopics: {},
    deletedPosts: {},

    deleteReview(review) {
        const strings = window.__accountStrings || {};
        window.dispatchEvent(new CustomEvent('confirm:open', {
            detail: {
                title: strings.reviewDeleteTitle || 'Delete this review?',
                message: review?.name
                    ? `${strings.reviewDeleteMessage || ''} (${review.name})`.trim()
                    : (strings.reviewDeleteMessage || ''),
                confirm: strings.confirmDelete || 'Delete',
                cancel: strings.cancel || 'Cancel',
                tone: 'danger',
                onConfirm: async () => {
                    await window.axios.delete(`/ucet/recenze/${review.id}`, {
                        headers: { 'X-CSRF-TOKEN': this.csrf() },
                    });
                    this.deletedReviews = { ...this.deletedReviews, [review.id]: true };
                },
            },
        }));
    },

    deleteTopic(topic) {
        const strings = window.__accountStrings || {};
        window.dispatchEvent(new CustomEvent('confirm:open', {
            detail: {
                title: strings.topicDeleteTitle || 'Delete this topic?',
                message: (strings.topicDeleteMessage || '') + (topic?.title ? ` (${topic.title})` : ''),
                confirm: strings.confirmDelete || 'Delete',
                cancel: strings.cancel || 'Cancel',
                tone: 'danger',
                onConfirm: async () => {
                    await window.axios.delete(`/ucet/forum-tema/${topic.id}`, {
                        headers: { 'X-CSRF-TOKEN': this.csrf() },
                    });
                    this.deletedTopics = { ...this.deletedTopics, [topic.id]: true };
                },
            },
        }));
    },

    deletePost(post) {
        const strings = window.__accountStrings || {};
        window.dispatchEvent(new CustomEvent('confirm:open', {
            detail: {
                title: strings.postDeleteTitle || 'Delete this reply?',
                message: strings.postDeleteMessage || '',
                confirm: strings.confirmDelete || 'Delete',
                cancel: strings.cancel || 'Cancel',
                tone: 'danger',
                onConfirm: async () => {
                    await window.axios.delete(`/ucet/forum-prispevek/${post.id}`, {
                        headers: { 'X-CSRF-TOKEN': this.csrf() },
                    });
                    this.deletedPosts = { ...this.deletedPosts, [post.id]: true };
                },
            },
        }));
    },

    // Inline-edit state. We host both editor flavors on the parent
    // accountPage so blade templates can simply call openEditTopic /
    // openEditPost from the row context — without any per-card local state.
    editorOpen: false,
    editorKind: null,       // 'topic' | 'post'
    editorId: null,
    editorTitle: '',
    editorBody: '',
    editorBusy: false,
    editorError: '',

    openEditTopic(topic) {
        this.editorKind = 'topic';
        this.editorId = topic.id;
        this.editorTitle = topic.title || '';
        this.editorBody = topic.body || '';
        this.editorError = '';
        this.editorOpen = true;
    },

    openEditPost(post) {
        this.editorKind = 'post';
        this.editorId = post.id;
        this.editorTitle = '';
        this.editorBody = post.body || '';
        this.editorError = '';
        this.editorOpen = true;
    },

    closeEditor() {
        if (this.editorBusy) return;
        this.editorOpen = false;
    },

    async saveEditor() {
        const id = this.editorId;
        const isTopic = this.editorKind === 'topic';
        const url = isTopic ? `/ucet/forum-tema/${id}` : `/ucet/forum-prispevek/${id}`;
        const payload = isTopic
            ? { title: this.editorTitle, body: this.editorBody }
            : { body: this.editorBody };
        this.editorBusy = true;
        this.editorError = '';
        try {
            await window.axios.put(url, payload, { headers: { 'X-CSRF-TOKEN': this.csrf() } });
            // Reload to surface server-side recomputed fields (slug, summary
            // counts, last_post_at). The page is small and this avoids a
            // bunch of bespoke client-side patching.
            window.location.reload();
        } catch (err) {
            this.editorError = err.response?.data?.message
                || (err.response?.data?.errors && Object.values(err.response.data.errors).flat().join(', '))
                || 'Error';
            this.editorBusy = false;
        }
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
