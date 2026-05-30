// /forum — index page: search + category filter + sort + pagination over
// the topic list. All client-side (фикстуры в HTML через @js).
import { initials, formatDate, topicHref, userHref } from './forum-utils.js';

const PER_PAGE = 8;

export default ({ topics = [], users = {}, levels = {}, categories = {} }) => ({
    topics,
    users,
    levels,
    categories,

    query: '',
    category: 'all',
    sort: 'recent',
    page: 1,

    initials,
    formatDate,
    topicHref,
    userHref,

    userById(id) {
        return this.users[id] || null;
    },

    categoryLabel(key) {
        const cat = this.categories[key];
        return cat ? `${cat.icon} ${cat.label}` : key;
    },

    get filtered() {
        const q = this.query.trim().toLowerCase();
        const cat = this.category;
        let list = this.topics.filter((t) => {
            if (cat !== 'all' && t.category !== cat) return false;
            if (!q) return true;
            return (
                t.title.toLowerCase().includes(q) ||
                t.body.toLowerCase().includes(q)
            );
        });

        // Pinned always on top.
        list = [...list].sort((a, b) => {
            if (a.isPinned !== b.isPinned) return a.isPinned ? -1 : 1;
            switch (this.sort) {
                case 'new':
                    return new Date(b.createdAt) - new Date(a.createdAt);
                case 'hot':
                    return b.replies - a.replies;
                case 'top':
                    return (b.reputation || 0) - (a.reputation || 0);
                case 'recent':
                default:
                    return new Date(b.lastReply.at) - new Date(a.lastReply.at);
            }
        });

        return list;
    },

    get totalPages() {
        return Math.max(1, Math.ceil(this.filtered.length / PER_PAGE));
    },

    get paged() {
        const start = (this.page - 1) * PER_PAGE;
        return this.filtered.slice(start, start + PER_PAGE);
    },

    setCategory(key) {
        this.category = key;
        this.page = 1;
    },

    search() {
        this.page = 1;
    },

    nextPage() {
        if (this.page < this.totalPages) {
            this.page += 1;
            window.scrollTo({ top: window.scrollY - 0, behavior: 'instant' });
        }
    },

    prevPage() {
        if (this.page > 1) this.page -= 1;
    },
});
