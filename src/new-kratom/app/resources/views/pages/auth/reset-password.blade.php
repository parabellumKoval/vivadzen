<x-layouts.app :title="__('site.auth.reset.title')" :announcement="false">
    <section class="auth-page">
        <div class="auth-page__card">
            <h1 class="auth-page__title">{{ __('site.auth.reset.title') }}</h1>
            <p class="auth-page__subtitle">{{ __('site.auth.reset.subtitle') }}</p>

            <form method="POST" action="{{ url('/obnoveni-hesla') }}" class="account__form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <div class="field">
                    <label class="field__label" for="reset-email">{{ __('site.auth.login.email') }}</label>
                    <input id="reset-email" name="email" type="email" class="input @error('email') input--error @enderror"
                           value="{{ old('email', $email) }}" required autocomplete="email" />
                    @error('email')<span class="field__error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label class="field__label" for="reset-pass">{{ __('site.auth.reset.password') }}</label>
                    <x-form.password id="reset-pass" name="password" :required="true"
                        autocomplete="new-password" :error="$errors->first('password')" />
                </div>

                <div class="field">
                    <label class="field__label" for="reset-pass2">{{ __('site.auth.reset.password_confirm') }}</label>
                    <x-form.password id="reset-pass2" name="password_confirmation" :required="true"
                        autocomplete="new-password" />
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--block">{{ __('site.auth.reset.submit') }}</button>
            </form>
        </div>
    </section>
</x-layouts.app>
