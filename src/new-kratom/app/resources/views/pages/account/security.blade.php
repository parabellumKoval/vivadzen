@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.security.title')" :announcement="false">
    <x-account.shell active="security">
        <header class="account__head">
            <div class="account__head-text">
                <h1 class="account__title">{{ __('site.account.security.title') }}</h1>
                <p class="account__head-hint">{{ __('site.account.security.head_hint') }}</p>
            </div>
        </header>

        {{-- Password --}}
        <section class="account__card">
            <h2 class="account__subtitle">{{ __('site.account.security.password_title') }}</h2>

            @unless($user->hasPassword())
                <p class="account__hint account__hint--inline">{{ __('site.account.security.set_hint') }}</p>
            @endunless

            <form method="POST" action="{{ Locale::url('/ucet/heslo') }}" class="account__form">
                @csrf

                @if($user->hasPassword())
                    <div class="field">
                        <label class="field__label" for="cur-pass">{{ __('site.account.security.current_password') }}</label>
                        <x-form.password
                            id="cur-pass"
                            name="current_password"
                            autocomplete="current-password"
                            :required="true"
                            :error="$errors->first('current_password')"
                        />
                    </div>
                @endif

                <div class="field">
                    <label class="field__label" for="new-pass">{{ __('site.account.security.new_password') }}</label>
                    <x-form.password
                        id="new-pass"
                        name="password"
                        autocomplete="new-password"
                        :required="true"
                        :error="$errors->first('password')"
                    />
                    <p class="field__helper">{{ __('site.account.security.password_helper') }}</p>
                </div>

                <div class="field">
                    <label class="field__label" for="new-pass2">{{ __('site.account.security.new_password_confirm') }}</label>
                    <x-form.password
                        id="new-pass2"
                        name="password_confirmation"
                        autocomplete="new-password"
                        :required="true"
                    />
                </div>

                <div class="account__form-actions">
                    <button type="submit" class="btn btn--primary btn--md">
                        {{ $user->hasPassword() ? __('site.account.security.change_password') : __('site.account.security.set_password') }}
                    </button>
                </div>
            </form>
        </section>

        {{-- Email --}}
        <section class="account__card">
            <h2 class="account__subtitle">{{ __('site.account.security.email_title') }}</h2>

            <form method="POST" action="{{ Locale::url('/ucet/email') }}" class="account__form">
                @csrf

                <div class="field">
                    <label class="field__label" for="acc-email">{{ __('site.account.security.email') }}</label>
                    <input id="acc-email" name="email" type="email" autocomplete="email"
                           class="input @error('email') input--error @enderror"
                           value="{{ old('email', $user->email) }}" required />
                    @error('email')<span class="field__error">{{ $message }}</span>@enderror

                    @if($user->hasVerifiedEmail())
                        <p class="account__verify-note account__verify-note--ok">
                            <x-ui.icon name="badge-check" :size="16" />
                            <span>{{ __('site.account.security.verified_note') }}</span>
                        </p>
                    @else
                        <div class="account__verify-note account__verify-note--warn">
                            <x-ui.icon name="alert-circle" :size="16" />
                            <span class="account__verify-text">{{ __('site.account.security.unverified_note') }}</span>
                            <form method="POST" action="{{ route('verification.send') }}" class="account__verify-resend">
                                @csrf
                                <button type="submit" class="btn btn--text btn--sm">
                                    {{ __('site.account.security.resend_verification') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="account__form-actions">
                    <button type="submit" class="btn btn--primary btn--md">{{ __('site.account.security.change_email') }}</button>
                </div>
            </form>
        </section>
    </x-account.shell>
</x-layouts.app>
