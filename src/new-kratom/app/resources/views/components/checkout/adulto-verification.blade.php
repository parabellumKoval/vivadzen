@props([
    'publicKey' => '',
    'scriptUrl' => 'https://api.js.m2a.cz/api.js',
    'initialUid' => '',
])

@php
    // When the public key is missing we still render the section as a hint —
    // the checkout form's `requires_age_verification` flag will keep the submit
    // button disabled until a key is configured. The fallback checkbox in
    // review.blade.php is still required, so customers can never bypass the
    // declaration entirely.
@endphp

<section
    class="adulto-verification"
    x-data="adultoWidget({
        publicKey: @js($publicKey),
        scriptUrl: @js($scriptUrl),
        initialUid: @js($initialUid),
    })"
    data-error-label="{{ __('site.adulto.widget_error') }}"
    data-unavailable-label="{{ __('site.adulto.widget_unavailable') }}"
>
    <header class="adulto-verification__head">
        <span class="adulto-verification__icon"><x-ui.icon name="shield-check" :size="22" /></span>
        <div>
            <h3 class="adulto-verification__title">{{ __('site.adulto.checkout_title') }}</h3>
            <p class="adulto-verification__desc">{{ __('site.adulto.checkout_desc') }}</p>
        </div>
    </header>

    <div class="adulto-verification__guide">
        <p>{{ __('site.adulto.checkout_guide_hint') }}</p>
        <button type="button" class="adulto-verification__guide-btn" @click="openGuide">
            {{ __('site.adulto.checkout_open_guide') }}
        </button>
    </div>

    <div class="adulto-verification__widget" id="adulto-widget" x-ref="container">
        <div class="adulto-cz" data-sitekey="{{ $publicKey }}"></div>
    </div>

    <input
        type="hidden"
        name="age_verification_uid"
        id="age_verification_uid"
        value="{{ $initialUid }}"
        :value="uid"
    />

    <p class="adulto-verification__status adulto-verification__status--loading" x-show="loading" x-cloak>
        {{ __('site.adulto.widget_loading') }}
    </p>
    <p class="adulto-verification__status adulto-verification__status--success" x-show="verified" x-cloak>
        ✓ {{ __('site.adulto.widget_verified') }}
    </p>
    <p class="adulto-verification__status adulto-verification__status--error" x-show="error" x-text="error" x-cloak></p>
</section>
