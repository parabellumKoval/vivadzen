export default ({ categories = {}, emojis = [], currentUser = null, endpoint = '/forum/nove-tema' }) => ({
    categories,
    emojis,
    currentUser,
    endpoint,

    form: {
        title: '',
        body: '',
        emoji: emojis[0] || '🔥',
        category: Object.keys(categories).find((k) => k !== 'all') || 'beginners',
    },

    coverFile: null,
    coverPreview: null,
    submitting: false,
    submitted: false,
    showPreview: false,
    error: '',

    get nonAllCategories() {
        const out = {};
        for (const [k, v] of Object.entries(this.categories)) {
            if (k !== 'all') out[k] = v;
        }
        return out;
    },

    get canSubmit() {
        return this.currentUser && this.form.title.trim().length >= 4 && this.form.body.trim().length >= 20 && this.form.category;
    },

    get bodyExcerpt() {
        const t = this.form.body.trim();
        return t.length > 220 ? t.slice(0, 220) + '…' : t || 'Tělo tématu se zobrazí v náhledu.';
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    onCover(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        if (this.coverPreview) URL.revokeObjectURL(this.coverPreview);
        this.coverFile = file;
        this.coverPreview = URL.createObjectURL(file);
    },

    removeCover() {
        if (this.coverPreview) URL.revokeObjectURL(this.coverPreview);
        this.coverFile = null;
        this.coverPreview = null;
    },

    async submit() {
        if (!this.currentUser) {
            document.dispatchEvent(new CustomEvent('auth:open', { detail: { view: 'login' } }));
            return;
        }
        if (!this.canSubmit || this.submitting) return;

        this.submitting = true;
        this.error = '';

        try {
            const fd = new FormData();
            fd.append('title', this.form.title.trim());
            fd.append('body', this.form.body.trim());
            fd.append('emoji', this.form.emoji);
            fd.append('category', this.form.category);
            if (this.coverFile) fd.append('cover', this.coverFile);

            const res = await fetch(this.endpoint, {
                method: 'POST',
                body: fd,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await res.json().catch(() => ({}));
            if (res.status === 401) {
                document.dispatchEvent(new CustomEvent('auth:open', { detail: { view: 'login' } }));
                return;
            }
            if (!res.ok) {
                this.error = data.message || 'Téma se nepodařilo vytvořit.';
                return;
            }

            this.submitted = true;
            window.location.href = data.redirect || '/forum';
        } catch (e) {
            this.error = 'Chyba sítě. Zkuste to znovu.';
        } finally {
            this.submitting = false;
        }
    },
});
