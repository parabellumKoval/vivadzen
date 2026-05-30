// Phone input mask — Alpine data factory.
// Mirrors src/kratom/components/Form/Phone (Vue) so behaviour stays consistent.
// Use via the <x-form.phone> Blade component.

export const PHONE_MASKS = {
    ua: { dialCode: '+380', mask: '+380 (##) ###-##-##', maxDigits: 9, placeholder: '+380 (__) ___-__-__' },
    cz: { dialCode: '+420', mask: '+420 ### ### ###',    maxDigits: 9, placeholder: '+420 ___ ___ ___' },
    sk: { dialCode: '+421', mask: '+421 ### ### ###',    maxDigits: 9, placeholder: '+421 ___ ___ ___' },
    pl: { dialCode: '+48',  mask: '+48 ### ### ###',     maxDigits: 9, placeholder: '+48 ___ ___ ___' },
    de: { dialCode: '+49',  mask: '+49 #### ### ####',   maxDigits: 11, placeholder: '+49 ____ ___ ____' },
    at: { dialCode: '+43',  mask: '+43 ### ### ####',    maxDigits: 10, placeholder: '+43 ___ ___ ____' },
    gb: { dialCode: '+44',  mask: '+44 #### ### ####',   maxDigits: 11, placeholder: '+44 ____ ___ ____' },
    fr: { dialCode: '+33',  mask: '+33 # ## ## ## ##',   maxDigits: 9, placeholder: '+33 _ __ __ __ __' },
    it: { dialCode: '+39',  mask: '+39 ### ### ####',    maxDigits: 10, placeholder: '+39 ___ ___ ____' },
    es: { dialCode: '+34',  mask: '+34 ### ### ###',     maxDigits: 9, placeholder: '+34 ___ ___ ___' },
    nl: { dialCode: '+31',  mask: '+31 ## ### ####',     maxDigits: 9, placeholder: '+31 __ ___ ____' },
    hu: { dialCode: '+36',  mask: '+36 ## ### ####',     maxDigits: 9, placeholder: '+36 __ ___ ____' },
};

function digitsOnly(value) {
    return (value || '').toString().replace(/\D/g, '');
}

function countHashes(mask) {
    return (mask || '').split('').filter((c) => c === '#').length;
}

function applyMask(digits, mask) {
    if (!digits) return '';
    if (!mask) return digits;
    let out = '';
    let i = 0;
    for (const ch of mask) {
        if (ch === '#') {
            if (i >= digits.length) break;
            out += digits[i++];
        } else {
            out += ch;
        }
    }
    // Drop trailing separators that come right after the last typed digit.
    return out.replace(/[\s\-()]+$/, '');
}

function normalizeDigits(value, cfg, { skipStrip = false } = {}) {
    let digits = digitsOnly(value);
    if (!cfg) return digits;
    const dial = digitsOnly(cfg.dialCode);
    if (!skipStrip && dial && digits.startsWith(dial)) {
        digits = digits.slice(dial.length);
    }
    const limit = cfg.maxDigits || countHashes(cfg.mask);
    return limit ? digits.slice(0, limit) : digits;
}

export default ({ initial = '', region = 'cz' } = {}) => ({
    region: String(region || 'cz').toLowerCase(),
    digits: '',
    display: '',
    focused: false,

    get config() {
        return PHONE_MASKS[this.region] || PHONE_MASKS.cz;
    },

    get placeholder() {
        return this.config.placeholder || this.config.mask || '';
    },

    get maxLength() {
        return this.config.mask?.length || 19;
    },

    init() {
        this.sync(initial, { skipStrip: false });
    },

    sync(value, opts = {}) {
        this.digits = normalizeDigits(value, this.config, opts);
        this.display = applyMask(this.digits, this.config.mask);
    },

    onInput(event) {
        // Reuse the input's current value so users can paste full E.164 numbers.
        this.sync(event.target.value);
        // Reflect masked value back into the input — Alpine x-model already does
        // this via this.display, but we keep the cursor at end on typing.
        event.target.value = this.display;
    },

    onFocus() {
        this.focused = true;
    },

    onBlur() {
        this.focused = false;
    },

    changeRegion(next) {
        this.region = String(next || 'cz').toLowerCase();
        this.sync(this.digits, { skipStrip: true });
    },
});
