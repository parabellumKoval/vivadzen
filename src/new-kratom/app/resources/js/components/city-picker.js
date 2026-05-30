// City autocomplete for the address form. Hits /ucet/mesta?q=… which is
// backed by Meilisearch (with a SQL LIKE fallback if Meili is down).
// Selecting a row writes both city_id and a display label into the bound
// form fields.

export default ({ onSelect, initialId = null, initialLabel = '' } = {}) => ({
    query: initialLabel,
    cityId: initialId,
    cityLabel: initialLabel,
    options: [],
    open: false,
    loading: false,
    activeIndex: -1,
    debounceTimer: null,
    onSelect,

    setInitial(id, label) {
        this.cityId = id;
        this.cityLabel = label || '';
        this.query = this.cityLabel;
    },

    async search() {
        const q = (this.query || '').trim();
        if (q.length < 1) {
            this.options = [];
            this.open = false;
            return;
        }
        this.loading = true;
        try {
            const { data } = await window.axios.get('/ucet/mesta', { params: { q, limit: 12 } });
            this.options = data.data || [];
            this.open = this.options.length > 0;
            this.activeIndex = this.options.length > 0 ? 0 : -1;
        } catch (e) {
            this.options = [];
            this.open = false;
        } finally {
            this.loading = false;
        }
    },

    onInput() {
        // Typing invalidates a previous selection until the user picks again.
        this.cityId = null;
        if (typeof this.onSelect === 'function') this.onSelect(null, '');
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.search(), 150);
    },

    pick(option) {
        this.cityId = option.id;
        this.cityLabel = option.full_label;
        this.query = option.full_label;
        this.open = false;
        if (typeof this.onSelect === 'function') this.onSelect(option.id, option.full_label);
    },

    onFocus() {
        if (this.options.length > 0) this.open = true;
    },

    onBlur() {
        // Delay so click on a row registers before the menu closes.
        setTimeout(() => { this.open = false; }, 150);
    },

    onKeydown(event) {
        if (!this.open) return;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeIndex = Math.min(this.activeIndex + 1, this.options.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeIndex = Math.max(this.activeIndex - 1, 0);
        } else if (event.key === 'Enter') {
            if (this.activeIndex >= 0 && this.options[this.activeIndex]) {
                event.preventDefault();
                this.pick(this.options[this.activeIndex]);
            }
        } else if (event.key === 'Escape') {
            this.open = false;
        }
    },
});
