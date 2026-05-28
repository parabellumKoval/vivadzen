// Product reviews — DB-backed list, filters, write-review modal with photos
export default ({ slug, apiBase, seed = [], ratingAverage = 0, reviewsCount = 0 }) => ({
    items: seed,
    page: 1,
    perPage: 6,
    hasMore: reviewsCount > seed.length,
    loading: false,
    sort: 'newest',
    verified: false,
    withPhotos: false,
    ratingFilter: null,
    modalOpen: false,
    submitting: false,
    error: '',
    success: '',
    form: this?.form ?? null,

    init() {
        this.form = this.freshForm();
        this.items = (seed || []).slice(0, this.perPage).map((r) => ({ ...r, _busy: false }));
        this.page = 1;
        this.hasMore = reviewsCount > this.items.length;
    },

    freshForm() {
        return {
            rating: 5,
            author_name: '',
            author_email: '',
            package: '',
            body: '',
            photos: [],
            photoPreviews: [],
        };
    },

    openWriteModal() {
        this.error = '';
        this.success = '';
        this.form = this.freshForm();
        this.modalOpen = true;
    },

    resetFilters() {
        this.verified = false;
        this.withPhotos = false;
        this.ratingFilter = null;
        this.applyFilters();
    },

    toggleVerified() {
        this.verified = !this.verified;
        this.applyFilters();
    },

    togglePhotos() {
        this.withPhotos = !this.withPhotos;
        this.applyFilters();
    },

    setRatingFilter(n) {
        this.ratingFilter = this.ratingFilter === n ? null : n;
        this.applyFilters();
    },

    async applyFilters() {
        this.page = 1;
        this.items = [];
        this.hasMore = true;
        await this.fetchPage(true);
    },

    async loadMore() {
        this.page += 1;
        await this.fetchPage(false);
    },

    async fetchPage(replace) {
        if (this.loading) return;
        this.loading = true;
        try {
            const params = new URLSearchParams({
                page: String(this.page),
                per_page: String(this.perPage),
                sort: this.sort,
            });
            if (this.verified) params.set('verified', '1');
            if (this.withPhotos) params.set('with_photos', '1');
            if (this.ratingFilter) params.set('rating', String(this.ratingFilter));

            const res = await fetch(`${apiBase}/reviews?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            const fetched = (json.data || []).map((r) => ({ ...r, _busy: false }));
            this.items = replace ? fetched : [...this.items, ...fetched];
            const meta = json.meta || {};
            this.hasMore = meta.current_page < meta.last_page;
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    handlePhotos(event) {
        const files = Array.from(event.target.files || []);
        this.form.photos = files.slice(0, 3);
        this.form.photoPreviews.forEach((url) => URL.revokeObjectURL(url));
        this.form.photoPreviews = this.form.photos.map((f) => URL.createObjectURL(f));
    },

    removePhoto(index) {
        URL.revokeObjectURL(this.form.photoPreviews[index]);
        this.form.photos.splice(index, 1);
        this.form.photoPreviews.splice(index, 1);
    },

    async submitReview() {
        if (this.submitting) return;
        this.error = '';
        this.success = '';

        if (!this.form.author_name.trim() || !this.form.body.trim() || !this.form.rating) {
            this.error = 'Vyplňte prosím jméno, hodnocení a text recenze.';
            return;
        }
        if (this.form.body.length < 10) {
            this.error = 'Recenze musí mít alespoň 10 znaků.';
            return;
        }

        this.submitting = true;
        try {
            const fd = new FormData();
            fd.append('author_name', this.form.author_name);
            fd.append('author_email', this.form.author_email);
            fd.append('rating', String(this.form.rating));
            fd.append('package', this.form.package || '');
            fd.append('body', this.form.body);
            this.form.photos.forEach((file) => fd.append('photos[]', file));

            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`${apiBase}/reviews`, {
                method: 'POST',
                body: fd,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                this.error = data.message || 'Nepodařilo se odeslat recenzi.';
                return;
            }

            const data = await res.json();
            this.success = data.message || 'Děkujeme za recenzi!';
            this.form = this.freshForm();
            setTimeout(() => { this.modalOpen = false; this.success = ''; }, 2000);
        } catch (e) {
            this.error = 'Chyba sítě. Zkuste to znovu.';
        } finally {
            this.submitting = false;
        }
    },

    async markHelpful(review) {
        if (review._busy) return;
        review._busy = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/api/review/${review.id}/helpful`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                const data = await res.json();
                review.helpful = data.helpful;
            }
        } finally {
            review._busy = false;
        }
    },
});
