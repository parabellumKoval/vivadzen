// Product Q&A — DB-backed list, ask-question modal
export default ({ slug, apiBase, seed = [], total = 0 }) => ({
    items: seed,
    total,
    page: 1,
    perPage: 5,
    hasMore: total > seed.length,
    loading: false,
    modalOpen: false,
    submitting: false,
    error: '',
    success: '',
    form: null,

    init() {
        this.form = this.freshForm();
        this.items = (seed || []).slice(0, this.perPage).map((q) => ({ ...q, _busy: false }));
        this.page = 1;
        this.hasMore = total > this.items.length;
    },

    freshForm() {
        return {
            author_name: '',
            author_email: '',
            question: '',
        };
    },

    openAskModal() {
        this.error = '';
        this.success = '';
        this.form = this.freshForm();
        this.modalOpen = true;
    },

    async loadMore() {
        if (this.loading) return;
        this.loading = true;
        this.page += 1;
        try {
            const params = new URLSearchParams({
                page: String(this.page),
                per_page: String(this.perPage),
            });
            const res = await fetch(`${apiBase}/questions?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            const fetched = (json.data || []).map((q) => ({ ...q, _busy: false }));
            this.items = [...this.items, ...fetched];
            const meta = json.meta || {};
            this.hasMore = meta.current_page < meta.last_page;
        } finally {
            this.loading = false;
        }
    },

    async submitQuestion() {
        if (this.submitting) return;
        this.error = '';
        this.success = '';

        if (!this.form.author_name.trim() || !this.form.question.trim()) {
            this.error = 'Vyplňte prosím jméno a text dotazu.';
            return;
        }
        if (this.form.question.length < 5) {
            this.error = 'Otázka musí mít alespoň 5 znaků.';
            return;
        }

        this.submitting = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`${apiBase}/questions`, {
                method: 'POST',
                body: JSON.stringify(this.form),
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                this.error = data.message || 'Nepodařilo se odeslat dotaz.';
                return;
            }

            const data = await res.json();
            this.success = data.message || 'Děkujeme za dotaz!';
            this.form = this.freshForm();
            setTimeout(() => { this.modalOpen = false; this.success = ''; }, 2000);
        } catch (e) {
            this.error = 'Chyba sítě. Zkuste to znovu.';
        } finally {
            this.submitting = false;
        }
    },

    async markHelpful(item) {
        if (item._busy) return;
        item._busy = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/api/question/${item.id}/helpful`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                const data = await res.json();
                item.helpful = data.helpful;
            }
        } finally {
            item._busy = false;
        }
    },
});
