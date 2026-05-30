@php
    use App\Support\Locale;
@endphp

<div
    x-data="authModal"
    x-cloak
    class="auth-modal"
    :class="open && 'is-open'"
    @keydown.escape.window="open = false"
>
    <div class="auth-modal__backdrop" x-show="open" x-transition.opacity @click="close()"></div>

    <div
        class="auth-modal__panel"
        x-show="open"
        x-transition:enter="auth-modal__panel--enter"
        x-transition:enter-start="auth-modal__panel--enter-start"
        x-transition:enter-end="auth-modal__panel--enter-end"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('site.auth.login.title') }} / {{ __('site.auth.register.title') }}"
    >
        <button type="button" class="auth-modal__close" @click="close()" aria-label="{{ __('site.header.close') }}">
            <x-ui.icon name="x" />
        </button>

        {{-- Tabs (login / register) --}}
        <div class="auth-modal__tabs" x-show="view !== 'forgot'">
            <button type="button" class="auth-modal__tab" :class="view === 'login' && 'is-active'" @click="switchView('login')">
                {{ __('site.auth.login.title') }}
            </button>
            <button type="button" class="auth-modal__tab" :class="view === 'register' && 'is-active'" @click="switchView('register')">
                {{ __('site.auth.register.title') }}
            </button>
        </div>

        {{-- LOGIN --}}
        <form class="auth-modal__form" x-show="view === 'login'" @submit.prevent="submitLogin()">
            <p class="auth-modal__subtitle">{{ __('site.auth.login.subtitle') }}</p>

            <div class="field">
                <label class="field__label" for="auth-login-email">{{ __('site.auth.login.email') }}</label>
                <input id="auth-login-email" type="email" class="input" autocomplete="email"
                       x-model="form.email" :class="fieldError('email') && 'input--error'" required />
                <span class="field__error" x-show="fieldError('email')" x-text="fieldError('email')"></span>
            </div>

            <div class="field">
                <label class="field__label" for="auth-login-password">{{ __('site.auth.login.password') }}</label>
                <div x-data="{ show: false }" class="field__password">
                    <input id="auth-login-password" :type="show ? 'text' : 'password'" class="input" autocomplete="current-password"
                           x-model="form.password" :class="fieldError('password') && 'input--error'" required />
                    <button type="button" class="field__password-toggle" @click="show = !show" tabindex="-1"
                            :aria-label="show ? @js(__('site.account.security.hide_password')) : @js(__('site.account.security.show_password'))">
                        <template x-if="!show"><x-ui.icon name="eye" :size="18" /></template>
                        <template x-if="show"><x-ui.icon name="eye-off" :size="18" /></template>
                    </button>
                </div>
                <span class="field__error" x-show="fieldError('password')" x-text="fieldError('password')"></span>
            </div>

            <div class="auth-modal__row">
                <label class="auth-modal__check">
                    <input type="checkbox" x-model="form.remember" />
                    <span>{{ __('site.auth.login.remember') }}</span>
                </label>
                <button type="button" class="auth-modal__link" @click="switchView('forgot')">
                    {{ __('site.auth.login.forgot') }}
                </button>
            </div>

            <button type="submit" class="btn btn--primary btn--lg btn--block" :disabled="loading">
                <span x-show="!loading">{{ __('site.auth.login.submit') }}</span>
                <span x-show="loading" x-cloak>…</span>
            </button>

            @include('components.global._auth-social')

            <p class="auth-modal__foot">
                {{ __('site.auth.login.no_account') }}
                <button type="button" class="auth-modal__link" @click="switchView('register')">{{ __('site.auth.login.register_link') }}</button>
            </p>
        </form>

        {{-- REGISTER --}}
        <form class="auth-modal__form" x-show="view === 'register'" @submit.prevent="submitRegister()">
            <p class="auth-modal__subtitle">{{ __('site.auth.register.subtitle') }}</p>

            <div class="field">
                <label class="field__label" for="auth-reg-name">{{ __('site.auth.register.name') }}</label>
                <input id="auth-reg-name" type="text" class="input" autocomplete="name"
                       x-model="form.name" :class="fieldError('name') && 'input--error'" required />
                <span class="field__error" x-show="fieldError('name')" x-text="fieldError('name')"></span>
            </div>

            <div class="field">
                <label class="field__label" for="auth-reg-email">{{ __('site.auth.register.email') }}</label>
                <input id="auth-reg-email" type="email" class="input" autocomplete="email"
                       x-model="form.email" :class="fieldError('email') && 'input--error'" required />
                <span class="field__error" x-show="fieldError('email')" x-text="fieldError('email')"></span>
            </div>

            <div class="field">
                <label class="field__label" for="auth-reg-password">{{ __('site.auth.register.password') }}</label>
                <div x-data="{ show: false }" class="field__password">
                    <input id="auth-reg-password" :type="show ? 'text' : 'password'" class="input" autocomplete="new-password"
                           x-model="form.password" :class="fieldError('password') && 'input--error'" required />
                    <button type="button" class="field__password-toggle" @click="show = !show" tabindex="-1"
                            :aria-label="show ? @js(__('site.account.security.hide_password')) : @js(__('site.account.security.show_password'))">
                        <template x-if="!show"><x-ui.icon name="eye" :size="18" /></template>
                        <template x-if="show"><x-ui.icon name="eye-off" :size="18" /></template>
                    </button>
                </div>
                <span class="field__error" x-show="fieldError('password')" x-text="fieldError('password')"></span>
            </div>

            <div class="field">
                <label class="field__label" for="auth-reg-password2">{{ __('site.auth.register.password_confirm') }}</label>
                <div x-data="{ show: false }" class="field__password">
                    <input id="auth-reg-password2" :type="show ? 'text' : 'password'" class="input" autocomplete="new-password"
                           x-model="form.password_confirmation" required />
                    <button type="button" class="field__password-toggle" @click="show = !show" tabindex="-1"
                            :aria-label="show ? @js(__('site.account.security.hide_password')) : @js(__('site.account.security.show_password'))">
                        <template x-if="!show"><x-ui.icon name="eye" :size="18" /></template>
                        <template x-if="show"><x-ui.icon name="eye-off" :size="18" /></template>
                    </button>
                </div>
            </div>

            <label class="auth-modal__check auth-modal__check--block">
                <input type="checkbox" x-model="form.marketing_consent" />
                <span>{{ __('site.auth.register.marketing') }}</span>
            </label>

            <button type="submit" class="btn btn--primary btn--lg btn--block" :disabled="loading">
                <span x-show="!loading">{{ __('site.auth.register.submit') }}</span>
                <span x-show="loading" x-cloak>…</span>
            </button>

            <p class="auth-modal__terms">{{ __('site.auth.register.terms_note') }}</p>

            @include('components.global._auth-social')

            <p class="auth-modal__foot">
                {{ __('site.auth.register.have_account') }}
                <button type="button" class="auth-modal__link" @click="switchView('login')">{{ __('site.auth.register.login_link') }}</button>
            </p>
        </form>

        {{-- FORGOT --}}
        <form class="auth-modal__form" x-show="view === 'forgot'" @submit.prevent="submitForgot()">
            <h2 class="auth-modal__title">{{ __('site.auth.forgot.title') }}</h2>
            <p class="auth-modal__subtitle">{{ __('site.auth.forgot.subtitle') }}</p>

            <div class="auth-modal__notice" x-show="notice" x-cloak x-text="notice"></div>

            <div class="field" x-show="!notice">
                <label class="field__label" for="auth-forgot-email">{{ __('site.auth.forgot.email') }}</label>
                <input id="auth-forgot-email" type="email" class="input" autocomplete="email"
                       x-model="form.email" :class="fieldError('email') && 'input--error'" required />
                <span class="field__error" x-show="fieldError('email')" x-text="fieldError('email')"></span>
            </div>

            <button type="submit" class="btn btn--primary btn--lg btn--block" x-show="!notice" :disabled="loading">
                <span x-show="!loading">{{ __('site.auth.forgot.submit') }}</span>
                <span x-show="loading" x-cloak>…</span>
            </button>

            <p class="auth-modal__foot">
                <button type="button" class="auth-modal__link" @click="switchView('login')">{{ __('site.auth.forgot.back_to_login') }}</button>
            </p>
        </form>
    </div>
</div>
