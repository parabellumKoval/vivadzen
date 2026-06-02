// Sliding review carousel. Cards stay mounted so transitions are handled by classes, not display toggles.
export default () => ({
    activeIndex: 0,
    pageIndex: 0,
    pageTotal: 1,
    itemsPerPage: 3,
    trackWidth: 0,
    gap: 0,
    resizeHandler: null,
    resizeObserver: null,

    init() {
        this.$nextTick(() => {
            this.updateState();
            this.resizeHandler = () => this.updateState();
            window.addEventListener('resize', this.resizeHandler, { passive: true });

            if ('ResizeObserver' in window && this.$refs.track) {
                this.resizeObserver = new ResizeObserver(() => this.updateState());
                this.resizeObserver.observe(this.$refs.track);
            }
        });
    },

    destroy() {
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }

        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
    },

    next() {
        this.goTo(this.activeIndex + 1, { loop: true });
    },

    prev() {
        this.goTo(this.activeIndex - 1, { loop: true });
    },

    goTo(index, { loop = false } = {}) {
        this.updateState();
        this.activeIndex = loop ? this.wrapIndex(index) : this.clampIndex(index);
        this.pageIndex = this.activeIndex;
    },

    wrapIndex(index) {
        const max = this.maxIndex();
        if (max <= 0) return 0;
        const span = max + 1;
        return ((index % span) + span) % span;
    },

    isActive(index) {
        return index >= this.activeIndex && index < this.activeIndex + this.itemsPerPage;
    },

    slotFor(index) {
        return index - this.activeIndex;
    },

    cardClass(index) {
        const slot = this.slotFor(index);
        const active = this.isActive(index);

        return {
            active,
            'is-before': slot < 0,
            'is-after': slot >= this.itemsPerPage,
            'is-edge-start': active && slot === 0,
            'is-edge-end': active && slot === this.itemsPerPage - 1,
        };
    },

    cardStyle(index) {
        const slot = this.slotFor(index);
        const active = this.isActive(index);
        const width = this.cardWidth();
        const x = slot * (width + this.gap);

        return {
            width: width > 0 ? `${width}px` : `${100 / this.itemsPerPage}%`,
            transform: `translate3d(${x}px, ${active ? 0 : 14}px, 0) scale(${active ? 1 : 0.985})`,
            zIndex: active ? String(10 + this.itemsPerPage - Math.abs(slot)) : '1',
        };
    },

    updateState() {
        const count = this.cardCount();
        this.itemsPerPage = Math.min(this.resolveItemsPerPage(), count || 1);
        this.measureTrack();
        this.pageTotal = Math.max(1, count - this.itemsPerPage + 1);
        this.activeIndex = this.clampIndex(this.activeIndex);
        this.pageIndex = this.activeIndex;
    },

    resolveItemsPerPage() {
        if (window.matchMedia('(max-width: 720px)').matches) return 1;
        if (window.matchMedia('(max-width: 1024px)').matches) return 2;
        return 3;
    },

    cardCount() {
        const track = this.$refs.track;
        return track ? track.children.length : 0;
    },

    measureTrack() {
        const track = this.$refs.track;

        if (!track) {
            return;
        }

        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap || '0');

        this.trackWidth = track.clientWidth;
        this.gap = Number.isFinite(gap) ? gap : 0;
    },

    cardWidth() {
        const width = this.trackWidth || this.$refs.track?.clientWidth || 0;

        if (width <= 0) {
            return 0;
        }

        return (width - this.gap * (this.itemsPerPage - 1)) / this.itemsPerPage;
    },

    clampIndex(index) {
        return Math.max(0, Math.min(index, this.maxIndex()));
    },

    maxIndex() {
        return Math.max(0, this.cardCount() - this.itemsPerPage);
    },
});
