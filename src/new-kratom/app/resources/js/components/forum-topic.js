import { initials, formatDate, formatRelative, paragraphs, topicHref, userHref } from './forum-utils.js';

const MAX_DEPTH = 4;

export default ({
    topic,
    author,
    initialComments = [],
    users = {},
    levels = {},
    categories = {},
    reactions = [],
    members = [],
    currentUser = null,
    endpoints = {},
}) => ({
    topic: { ...topic },
    author,
    users,
    levels,
    categories,
    reactionsPalette: reactions,
    members,
    currentUser,
    endpoints,

    comments: initialComments.map((c) => ({
        ...c,
        _vote: c.userVote || 0,
        _userReactions: c.userReactions || [],
        collapsed: false,
        _editedNow: false,
    })),

    sort: 'top',
    newReply: '',
    newReplyFocused: false,
    replyingTo: null,
    replyDraft: '',
    editingId: null,
    editDraft: '',
    editingTopic: false,
    topicDraft: '',
    topicWasEdited: false,
    submittingReply: false,
    formError: '',

    initials,
    formatDate,
    formatRelative,
    paragraphs,
    topicHref,
    userHref,

    init() {
        if (this.currentUser) {
            this.users[this.currentUser.id] = this.currentUser;
        }
    },

    get topicParagraphs() {
        return paragraphs(this.topic.body);
    },

    get canEditTopic() {
        return this.currentUser && this.topic.authorId === this.currentUser.id;
    },

    get sortedFlat() {
        const byId = new Map(this.comments.map((c) => [c.id, c]));
        const childrenOf = new Map();
        for (const c of this.comments) {
            const p = c.parentId ?? null;
            if (!childrenOf.has(p)) childrenOf.set(p, []);
            childrenOf.get(p).push(c);
        }

        const sortFn = (a, b) => {
            switch (this.sort) {
                case 'new':
                    return new Date(b.createdAt) - new Date(a.createdAt);
                case 'old':
                    return new Date(a.createdAt) - new Date(b.createdAt);
                case 'top':
                default:
                    return b.score - a.score;
            }
        };

        const out = [];
        const visit = (parentId, depth) => {
            const list = (childrenOf.get(parentId) || []).slice().sort(sortFn);
            for (const c of list) {
                out.push({ ...c, depth: Math.min(depth, MAX_DEPTH) });
                if (!c.collapsed) visit(c.id, depth + 1);
            }
        };
        visit(null, 0);

        return out.map((c) => Object.assign(byId.get(c.id), { depth: c.depth }));
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    async request(url, options = {}) {
        const res = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        });

        if (res.status === 401) {
            this.openAuth();
            throw new Error('auth');
        }

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Nepodařilo se uložit změnu.');
        }

        return data;
    },

    openAuth() {
        document.dispatchEvent(new CustomEvent('auth:open', { detail: { view: 'login' } }));
    },

    commentAuthor(c) {
        return this.users[c.authorId] || { name: 'Neznámý', avatarColor: '#5C6A63', level: 'sprout', slug: '' };
    },

    authorLevel(c) {
        const u = this.commentAuthor(c);
        return this.levels[u.level] || { name: '—', icon: '·', color: 'var(--c-ink-300)' };
    },

    canReply(c) {
        return !!this.currentUser && !this.topic.isLocked && c.depth < MAX_DEPTH - 1;
    },

    canEditComment(c) {
        return this.currentUser && c.authorId === this.currentUser.id;
    },

    applyPostPayload(c, payload) {
        Object.assign(c, {
            body: payload.body,
            score: payload.score,
            reactions: payload.reactions || {},
            updatedAt: payload.updatedAt,
            _vote: payload.userVote || 0,
            _userReactions: payload.userReactions || [],
            _editedNow: payload._editedNow || c._editedNow,
        });
    },

    async vote(c, delta) {
        if (!this.currentUser) return this.openAuth();
        try {
            const data = await this.request(`${this.endpoints.postUpdateBase}/${c.id}/hlas`, {
                method: 'POST',
                body: JSON.stringify({ value: delta }),
            });
            this.applyPostPayload(c, data.data);
        } catch (e) {
            if (e.message !== 'auth') this.formError = e.message;
        }
    },

    async toggleReaction(c, emoji) {
        if (!this.currentUser) return this.openAuth();
        try {
            const data = await this.request(`${this.endpoints.postUpdateBase}/${c.id}/reakce`, {
                method: 'POST',
                body: JSON.stringify({ emoji }),
            });
            this.applyPostPayload(c, data.data);
        } catch (e) {
            if (e.message !== 'auth') this.formError = e.message;
        }
    },

    openReply(commentId) {
        if (!this.currentUser) return this.openAuth();
        this.replyingTo = commentId;
        this.replyDraft = '';
    },

    async submitReply(parentId, body) {
        if (!this.currentUser) return this.openAuth();
        const text = (body || '').trim();
        if (text.length < 2 || this.submittingReply) return;

        this.submittingReply = true;
        this.formError = '';
        try {
            const data = await this.request(this.endpoints.reply, {
                method: 'POST',
                body: JSON.stringify({ body: text, parent_id: parentId }),
            });

            this.comments.push({
                ...data.data,
                _vote: data.data.userVote || 0,
                _userReactions: data.data.userReactions || [],
                collapsed: false,
                _editedNow: false,
            });

            if (data.topic) this.topic = { ...this.topic, ...data.topic };
            if (parentId) {
                this.replyDraft = '';
                this.replyingTo = null;
            } else {
                this.newReply = '';
                this.newReplyFocused = false;
            }
        } catch (e) {
            if (e.message !== 'auth') this.formError = e.message;
        } finally {
            this.submittingReply = false;
        }
    },

    startEdit(c) {
        this.editingId = c.id;
        this.editDraft = c.body;
    },

    cancelEdit() {
        this.editingId = null;
        this.editDraft = '';
    },

    async saveEdit(c) {
        const text = this.editDraft.trim();
        if (text.length < 2) return;
        try {
            const data = await this.request(`${this.endpoints.postUpdateBase}/${c.id}`, {
                method: 'PUT',
                body: JSON.stringify({ body: text }),
            });
            this.applyPostPayload(c, { ...data.data, _editedNow: true });
            this.cancelEdit();
        } catch (e) {
            if (e.message !== 'auth') this.formError = e.message;
        }
    },

    startEditTopic() {
        this.editingTopic = true;
        this.topicDraft = this.topic.body;
    },

    cancelEditTopic() {
        this.editingTopic = false;
        this.topicDraft = '';
    },

    async saveTopic() {
        const text = this.topicDraft.trim();
        if (text.length < 10) return;
        try {
            const data = await this.request(this.endpoints.topicUpdate, {
                method: 'PUT',
                body: JSON.stringify({ body: text }),
            });
            this.topic = { ...this.topic, ...data.data };
            this.topicWasEdited = true;
            this.editingTopic = false;
            this.topicDraft = '';
        } catch (e) {
            if (e.message !== 'auth') this.formError = e.message;
        }
    },
});
