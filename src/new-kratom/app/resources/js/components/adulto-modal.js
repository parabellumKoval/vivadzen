// ADULTO age-verification guide modal. Opens on the global `adulto:open`
// custom event so any view (checkout, cart, /pruvodce) can trigger it without
// importing this module directly.
export default () => ({
    open: false,

    init() {
        window.addEventListener('adulto:open', () => {
            this.open = true;
        });
    },

    close() {
        this.open = false;
    },
});
