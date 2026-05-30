// Auth modal — login / register / forgot-password in a single overlay.
// Opens on the global `auth:open` event (dispatched by the header account
// button for guests). Submits via axios with the CSRF token; on success it
// reloads so the server-rendered header reflects the logged-in state.
const endpoints = {
    login: '/auth/login',
    register: '/auth/register',
    forgot: '/auth/forgot-password',
};

export default () => ({
    open: false,
    view: 'login', // login | register | forgot
    loading: false,
    notice: '',
    errors: {},
    form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        remember: false,
        marketing_consent: false,
    },

    init() {
        document.addEventListener('auth:open', (e) => {
            this.switchView(e.detail?.view || 'login');
            this.open = true;
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.open = false;
        });
    },

    switchView(view) {
        this.view = view;
        this.errors = {};
        this.notice = '';
    },

    close() {
        this.open = false;
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    fieldError(field) {
        const e = this.errors[field];
        return Array.isArray(e) ? e[0] : e;
    },

    async post(url, payload) {
        this.loading = true;
        this.errors = {};
        this.notice = '';
        try {
            const { data } = await window.axios.post(url, payload, {
                headers: { 'X-CSRF-TOKEN': this.csrf() },
            });
            return data;
        } catch (err) {
            const res = err.response;
            if (res && res.status === 422) {
                this.errors = res.data.errors || {};
            } else {
                this.errors = { email: [res?.data?.message || 'Error'] };
            }
            return null;
        } finally {
            this.loading = false;
        }
    },

    async submitLogin() {
        const data = await this.post(endpoints.login, {
            email: this.form.email,
            password: this.form.password,
            remember: this.form.remember,
        });
        if (data?.ok) window.location.assign(data.redirect || window.location.href);
    },

    async submitRegister() {
        const data = await this.post(endpoints.register, {
            name: this.form.name,
            email: this.form.email,
            password: this.form.password,
            password_confirmation: this.form.password_confirmation,
            marketing_consent: this.form.marketing_consent,
        });
        if (data?.ok) window.location.assign(data.redirect || window.location.href);
    },

    async submitForgot() {
        const data = await this.post(endpoints.forgot, { email: this.form.email });
        if (data?.ok) this.notice = data.message || '';
    },
});
