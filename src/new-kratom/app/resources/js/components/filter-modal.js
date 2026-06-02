// Filter modal — lives at <body> level (via x-teleport) so it escapes
// <main>'s `isolation: isolate` stacking context and can sit above the
// sticky header. State for filters themselves is shared via
// Alpine.store('catalog'); only the open/close flag is local.
//
// Opens on `catalog:open-filter` event (dispatched by the chip-row
// «Filtry» button), closes on Escape, backdrop click, or close button.

export default () => ({
    open: false,

    init() {
        window.addEventListener('catalog:open-filter', () => this.openModal());
    },

    openModal() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        this.open = false;
        document.body.style.overflow = '';
    },
});
