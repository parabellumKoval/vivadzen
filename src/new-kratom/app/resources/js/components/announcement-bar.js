// AnnouncementBar — closeable, помнит закрытие 7 дней через localStorage.
const STORAGE_KEY = 'vivadzen.announce.closedAt';
const TTL_MS = 7 * 24 * 60 * 60 * 1000;

export default () => ({
    visible: true,

    init() {
        const closedAt = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
        if (closedAt && Date.now() - closedAt < TTL_MS) {
            this.visible = false;
        }
    },

    close() {
        this.visible = false;
        localStorage.setItem(STORAGE_KEY, Date.now().toString());
    },
});
