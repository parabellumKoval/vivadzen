// Newsletter — простая инлайн-валидация, имитация submit.
export default () => ({
    email: '',
    state: 'idle', // idle | submitting | success | error
    error: '',

    async submit() {
        this.error = '';

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
            this.state = 'error';
            this.error = 'Zadejte platnou emailovou adresu.';
            return;
        }

        this.state = 'submitting';
        // TODO: реальный POST на /newsletter/subscribe
        await new Promise((r) => setTimeout(r, 400));
        this.state = 'success';
    },
});
