// Catalog filter Alpine store — live filters without page reload.
//
// Wires together chip-row (single-vein/strain quick filter),
// filter-sidebar (multi-select checkboxes), and product-grid (visibility).
// State is global so the sidebar + chip-row + grid stay in sync.
//
// Filter keys map to data-attributes on each product wrapper div.

const catalogFilter = () => ({
    chip: 'all',          // chip-row single-value filter
    colors: [],           // sidebar barva žilky (multi)
    strains: [],          // sidebar odrůda (multi)
    forms: [],            // sidebar forma (multi)
    inStock: false,
    visibleCount: 0,
    totalCount: 0,

    init() {
        this.recount();
    },

    setChip(value) {
        this.chip = value || 'all';
        // chips may map onto color or strain — treat both
        this.recount();
    },

    clear() {
        this.chip = 'all';
        this.colors = [];
        this.strains = [];
        this.forms = [];
        this.inStock = false;
        this.recount();
    },

    matches(p) {
        // Chip filter: value matches either color OR strain OR form
        if (this.chip && this.chip !== 'all') {
            const c = this.chip;
            if (p.color !== c && p.strain !== c && p.form !== c) return false;
        }
        if (this.colors.length && !this.colors.includes(p.color)) return false;
        if (this.strains.length && !this.strains.includes(p.strain)) return false;
        if (this.forms.length && !this.forms.includes(p.form)) return false;
        return true;
    },

    recount() {
        // Read all product wrapper divs and compute visible count
        const nodes = document.querySelectorAll('[data-product-card]');
        let visible = 0;
        nodes.forEach((el) => {
            const ok = this.matches({
                color: el.dataset.color || '',
                strain: el.dataset.strain || '',
                form: el.dataset.form || '',
            });
            if (ok) visible++;
        });
        this.visibleCount = visible;
        this.totalCount = nodes.length;
    },
});

export default catalogFilter;
