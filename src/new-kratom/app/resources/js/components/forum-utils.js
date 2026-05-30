// Shared helpers used by all forum Alpine components.

const LOCALE = (document.documentElement.lang || 'cs').slice(0, 2);
const LOCALE_PREFIX = (() => {
    const seg = window.location.pathname.split('/').filter(Boolean)[0];
    return seg && ['en', 'ru', 'uk'].includes(seg) ? `/${seg}` : '';
})();

export function initials(name = '') {
    return name
        .split(' ')
        .map((w) => w.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

export function topicHref(slug) {
    return `${LOCALE_PREFIX}/forum/tema/${slug}`;
}

export function userHref(slug) {
    return `${LOCALE_PREFIX}/forum/uzivatel/${slug}`;
}

export function formatDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleDateString(LOCALE === 'cs' ? 'cs-CZ' : LOCALE, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export function formatRelative(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;

    const diffMs = Date.now() - d.getTime();
    const sec = Math.round(diffMs / 1000);
    const min = Math.round(sec / 60);
    const hr = Math.round(min / 60);
    const day = Math.round(hr / 24);

    if (sec < 60) return 'právě teď';
    if (min < 60) return `před ${min} min`;
    if (hr < 24) return `před ${hr} h`;
    if (day < 7) return `před ${day} dny`;
    return formatDate(iso);
}

export function paragraphs(text = '') {
    return text.split(/\n+/).map((p) => p.trim()).filter(Boolean);
}
