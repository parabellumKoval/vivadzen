// =============================================================
// Generic Alpine carousel — slot-based, items-per-page responsive.
// Used by any section that needs a card slider (bestsellers, related, и т.д.).
// Children inside x-ref="track" автоматически позиционируются.
// =============================================================
export default (config = {}) => ({
    itemsPerPageDesktop: config.desktop ?? 3,
    itemsPerPageTablet: config.tablet ?? 2,
    itemsPerPageMobile: config.mobile ?? 1,
    tabletBreakpoint: config.tabletBreakpoint ?? 1024,
    mobileBreakpoint: config.mobileBreakpoint ?? 720,

    activeIndex: 0,
    pageIndex: 0,
    pageTotal: 1,
    itemsPerPage: 3,
    trackWidth: 0,
    gap: 0,
    cards: [],
    resizeHandler: null,
    resizeObserver: null,
    mutationObserver: null,

    init() {
        this.$nextTick(() => {
            this.refreshCards();
            this.updateState();
            this.applyCards();

            this.resizeHandler = () => {
                this.updateState();
                this.applyCards();
            };
            window.addEventListener('resize', this.resizeHandler, { passive: true });

            if ('ResizeObserver' in window && this.$refs.track) {
                this.resizeObserver = new ResizeObserver(() => {
                    this.updateState();
                    this.applyCards();
                });
                this.resizeObserver.observe(this.$refs.track);
            }

            // если контент карусели дорендеривается (loops/async), пересчитаем
            if ('MutationObserver' in window && this.$refs.track) {
                this.mutationObserver = new MutationObserver(() => {
                    this.refreshCards();
                    this.updateState();
                    this.applyCards();
                });
                this.mutationObserver.observe(this.$refs.track, { childList: true });
            }
        });
    },

    destroy() {
        if (this.resizeHandler) window.removeEventListener('resize', this.resizeHandler);
        if (this.resizeObserver) this.resizeObserver.disconnect();
        if (this.mutationObserver) this.mutationObserver.disconnect();
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
        this.applyCards();
    },

    wrapIndex(index) {
        const max = this.maxIndex();
        if (max <= 0) return 0;
        const span = max + 1; // количество позиций
        return ((index % span) + span) % span;
    },

    refreshCards() {
        const track = this.$refs.track;
        this.cards = track ? Array.from(track.children) : [];
        // подготовка слоя: всем картам общий класс позиционирования
        this.cards.forEach(card => card.classList.add('carousel__item'));
    },

    isActive(index) {
        return index >= this.activeIndex && index < this.activeIndex + this.itemsPerPage;
    },

    slotFor(index) {
        return index - this.activeIndex;
    },

    resolveItemsPerPage() {
        if (window.matchMedia(`(max-width: ${this.mobileBreakpoint}px)`).matches) return this.itemsPerPageMobile;
        if (window.matchMedia(`(max-width: ${this.tabletBreakpoint}px)`).matches) return this.itemsPerPageTablet;
        return this.itemsPerPageDesktop;
    },

    cardCount() {
        return this.cards.length;
    },

    measureTrack() {
        const track = this.$refs.track;
        if (!track) return;
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap || '0');
        this.trackWidth = track.clientWidth;
        this.gap = Number.isFinite(gap) ? gap : 0;
    },

    cardWidth() {
        const width = this.trackWidth || this.$refs.track?.clientWidth || 0;
        if (width <= 0) return 0;
        return (width - this.gap * (this.itemsPerPage - 1)) / this.itemsPerPage;
    },

    updateState() {
        const count = this.cardCount();
        this.itemsPerPage = Math.min(this.resolveItemsPerPage(), count || 1);
        this.measureTrack();
        this.pageTotal = Math.max(1, count - this.itemsPerPage + 1);
        this.activeIndex = this.clampIndex(this.activeIndex);
        this.pageIndex = this.activeIndex;
    },

    clampIndex(index) {
        return Math.max(0, Math.min(index, this.maxIndex()));
    },

    maxIndex() {
        return Math.max(0, this.cardCount() - this.itemsPerPage);
    },

    applyCards() {
        const width = this.cardWidth();
        this.cards.forEach((card, index) => {
            const slot = this.slotFor(index);
            const active = this.isActive(index);
            const x = slot * (width + this.gap);

            card.style.width = width > 0 ? `${width}px` : `${100 / this.itemsPerPage}%`;
            card.style.transform = `translate3d(${x}px, ${active ? 0 : 14}px, 0) scale(${active ? 1 : 0.985})`;
            card.style.zIndex = active ? String(10 + this.itemsPerPage - Math.abs(slot)) : '1';

            card.classList.toggle('is-active', active);
            card.classList.toggle('is-before', slot < 0);
            card.classList.toggle('is-after', slot >= this.itemsPerPage);
            card.classList.toggle('is-edge-start', active && slot === 0);
            card.classList.toggle('is-edge-end', active && slot === this.itemsPerPage - 1);

            if (active) {
                card.removeAttribute('aria-hidden');
                card.removeAttribute('inert');
            } else {
                card.setAttribute('aria-hidden', 'true');
                card.setAttribute('inert', '');
            }
            card.dataset.slot = String(slot);
        });

        this.applyDots();
    },

    applyDots() {
        // Точки переключаем напрямую через DOM — Alpine :class иногда не
        // успевает пересчитать computed style без принудительного reflow.
        // ВАЖНО: используем $root (корень компонента), а не $el — внутри
        // обработчика клика на дотe $el указывает на сам button.
        const root = this.$root || (this.$refs.track && this.$refs.track.closest('.carousel'));
        if (!root) return;
        const doApply = () => {
            const dots = root.querySelectorAll('.carousel__dot-btn');
            if (!dots.length) return;
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === this.pageIndex);
            });
        };
        doApply();
        if (typeof this.$nextTick === 'function') {
            this.$nextTick(doApply);
        }
    },
});
