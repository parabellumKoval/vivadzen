// Catalog filter Alpine store — live filters without page reload.
//
// Wires together chip-row (single-vein/strain quick filter),
// filter-sidebar / filter-modal (multi-select tag filters), and product-grid (visibility).
// State is global so the sidebar + chip-row + grid stay in sync.
//
// Filter keys map to data-attributes on each product wrapper div.

const MITRAGYNIN_MIN = 1.0;
const MITRAGYNIN_MAX = 12.0;

// Map from sidebar state-array key → data-attribute on the product wrapper
const FACET_TO_ATTR = {
    colors: 'color',
    strains: 'strain',
    forms: 'form',
};

const catalogFilter = () => ({
    chip: 'all',          // chip-row single-value filter
    colors: [],           // sidebar barva žilky (multi)
    strains: [],          // sidebar odrůda (multi)
    forms: [],            // sidebar forma (multi)
    availability: [],     // skladem / předobjednávka (multi)
    inStock: false,
    mitragyninMin: MITRAGYNIN_MIN,
    mitragyninMax: MITRAGYNIN_MAX,
    modalOpen: false,
    visibleCount: 0,
    totalCount: 0,

    init() {
        this.recount();
    },

    setChip(value) {
        this.chip = value || 'all';
        this.recount();
    },

    clear() {
        this.chip = 'all';
        this.colors = [];
        this.strains = [];
        this.forms = [];
        this.availability = [];
        this.inStock = false;
        this.mitragyninMin = MITRAGYNIN_MIN;
        this.mitragyninMax = MITRAGYNIN_MAX;
        this.recount();
    },

    openModal() {
        this.modalOpen = true;
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        this.modalOpen = false;
        document.body.style.overflow = '';
    },

    toggle(group, value) {
        const arr = this[group];
        if (!Array.isArray(arr)) return;
        const i = arr.indexOf(value);
        if (i >= 0) arr.splice(i, 1);
        else arr.push(value);
        this.recount();
    },

    isOn(group, value) {
        const arr = this[group];
        return Array.isArray(arr) && arr.includes(value);
    },

    onMitragyninMin(event) {
        const v = parseFloat(event.target.value);
        if (!Number.isFinite(v)) return;
        const clamped = Math.max(MITRAGYNIN_MIN, Math.min(v, MITRAGYNIN_MAX));
        this.mitragyninMin = Math.min(clamped, this.mitragyninMax - 0.05);
        this.recount();
    },

    onMitragyninMax(event) {
        const v = parseFloat(event.target.value);
        if (!Number.isFinite(v)) return;
        const clamped = Math.max(MITRAGYNIN_MIN, Math.min(v, MITRAGYNIN_MAX));
        this.mitragyninMax = Math.max(clamped, this.mitragyninMin + 0.05);
        this.recount();
    },

    get activeCount() {
        let n = 0;
        if (this.chip !== 'all') n++;
        n += this.colors.length + this.strains.length + this.forms.length + this.availability.length;
        if (this.mitragyninMin > MITRAGYNIN_MIN || this.mitragyninMax < MITRAGYNIN_MAX) n++;
        return n;
    },

    // Apply every filter EXCEPT the named group. Used by countFor().
    matchesExcept(p, excludeGroup) {
        if (this.chip && this.chip !== 'all') {
            const c = this.chip;
            if (p.color !== c && p.strain !== c && p.form !== c) return false;
        }
        if (excludeGroup !== 'colors' && this.colors.length && !this.colors.includes(p.color)) return false;
        if (excludeGroup !== 'strains' && this.strains.length && !this.strains.includes(p.strain)) return false;
        if (excludeGroup !== 'forms' && this.forms.length && !this.forms.includes(p.form)) return false;

        if (p.mitragynin !== undefined && p.mitragynin !== '' && p.mitragynin !== null) {
            const m = parseFloat(p.mitragynin);
            if (Number.isFinite(m)) {
                if (m < this.mitragyninMin || m > this.mitragyninMax) return false;
            }
        }
        return true;
    },

    // Faceted count: products matching the current filter state with `group`
    // overridden to be «only this value». This is the standard «how many
    // products would I see if I picked this option?» count shown next to
    // each facet option.
    countFor(group, value) {
        const attr = FACET_TO_ATTR[group];
        const nodes = document.querySelectorAll('[data-product-card]');
        let n = 0;
        nodes.forEach((el) => {
            const p = {
                color: el.dataset.color || '',
                strain: el.dataset.strain || '',
                form: el.dataset.form || '',
                mitragynin: el.dataset.mitragynin || '',
            };
            if (!this.matchesExcept(p, group)) return;
            if (attr && p[attr] !== value) return;
            n++;
        });
        return n;
    },

    matches(p) {
        if (this.chip && this.chip !== 'all') {
            const c = this.chip;
            if (p.color !== c && p.strain !== c && p.form !== c) return false;
        }
        if (this.colors.length && !this.colors.includes(p.color)) return false;
        if (this.strains.length && !this.strains.includes(p.strain)) return false;
        if (this.forms.length && !this.forms.includes(p.form)) return false;

        if (p.mitragynin !== undefined && p.mitragynin !== '' && p.mitragynin !== null) {
            const m = parseFloat(p.mitragynin);
            if (Number.isFinite(m)) {
                if (m < this.mitragyninMin || m > this.mitragyninMax) return false;
            }
        }
        return true;
    },

    recount() {
        const nodes = document.querySelectorAll('[data-product-card]');
        let visible = 0;
        nodes.forEach((el) => {
            const ok = this.matches({
                color: el.dataset.color || '',
                strain: el.dataset.strain || '',
                form: el.dataset.form || '',
                mitragynin: el.dataset.mitragynin || '',
            });
            if (ok) visible++;
        });
        this.visibleCount = visible;
        this.totalCount = nodes.length;
    },
});

export default catalogFilter;
