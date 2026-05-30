// =============================================================
// Lab Batch — entrance reveal + animated counters
// =============================================================

const REDUCED = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

function animateCounter(el) {
    const target = parseFloat(el.dataset.countTo || '0');
    const decimals = parseInt(el.dataset.decimals || '0', 10);
    const duration = 1400;

    if (REDUCED || !Number.isFinite(target)) {
        el.textContent = formatNumber(target, decimals);
        return;
    }

    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        const value = target * eased;
        el.textContent = formatNumber(value, decimals);
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

function formatNumber(value, decimals) {
    return value.toLocaleString('cs-CZ', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function init() {
    const items = document.querySelectorAll('[data-anim]');
    if (!items.length) return;

    // Fallback: no IO support — reveal immediately.
    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => {
            el.classList.add('is-in');
            el.querySelectorAll('[data-count-to]').forEach(animateCounter);
        });
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                const el = entry.target;
                el.classList.add('is-in');
                el.querySelectorAll('[data-count-to]').forEach(animateCounter);
                io.unobserve(el);
            }
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );

    items.forEach((el) => io.observe(el));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

export default init;
