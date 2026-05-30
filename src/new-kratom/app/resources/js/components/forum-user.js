// /forum/uzivatel/{slug} — профиль пользователя. Две вкладки: «Темы»
// (приходят из контроллера) и «Ответы» (синтетический список из
// excerpt'ов last-reply, где автор совпадает; без бэкенда).
import { formatDate, topicHref } from './forum-utils.js';

export default ({ user, level, nextLevel, progress, topics = [], replies = [], categories = {} }) => ({
    user,
    level,
    nextLevel,
    progress,
    topics,
    replies,
    categories,

    tab: 'topics',

    formatDate,
    topicHref,

    categoryLabel(key) {
        const cat = this.categories[key];
        return cat ? `${cat.icon} ${cat.label}` : key;
    },

    get syntheticReplies() {
        return this.replies.sort((a, b) => new Date(b.at) - new Date(a.at));
    },
});
