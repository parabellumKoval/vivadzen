// Header — sticky scroll-aware + mobile drawer.
export default () => ({
    scrolled: false,
    drawerOpen: false,

    init() {
        this.onScroll();
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
    },

    onScroll() {
        this.scrolled = window.scrollY > 80;
    },

    toggleDrawer() {
        this.drawerOpen = !this.drawerOpen;
        document.body.classList.toggle('no-scroll', this.drawerOpen);
    },

    closeDrawer() {
        this.drawerOpen = false;
        document.body.classList.remove('no-scroll');
    },
});
