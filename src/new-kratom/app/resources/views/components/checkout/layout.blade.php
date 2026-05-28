@props(['step' => 1])

@php
    use App\Support\Locale;
    $allLocales = Locale::all();
    $current = $currentLocale ?? app()->getLocale();
    $htmlLang = $allLocales[$current]['htmlLang'] ?? 'cs-CZ';
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#1B3A2D" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="robots" content="noindex,nofollow" />

    <title>{{ __('site.checkout.title') }} | Vivadzen</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="checkout-body">
    <a href="#main" class="skip-link">Přeskočit na obsah</a>

    <header class="checkout-header">
        <div class="container checkout-header__inner">
            <a href="{{ Locale::url('/') }}" class="checkout-header__logo" aria-label="Vivadzen">
                <img src="{{ asset('assets/brand/logo-vivadzen-primary-light.avif') }}" alt="Vivadzen" />
            </a>

            <x-checkout.stepper :step="$step" />

            <a href="{{ Locale::url('/kontakt') }}" class="checkout-header__help">
                <x-ui.icon name="mail" :size="18" />
                <span class="checkout-header__help-text">{{ __('site.checkout.help') }}</span>
            </a>
        </div>
    </header>

    <main id="main" class="checkout-main">
        {{ $slot }}
    </main>

    <footer class="checkout-foot">
        <div class="container checkout-foot__inner">
            <span>© {{ date('Y') }} Vivadzen s.r.o.</span>
            <nav class="checkout-foot__links">
                <a href="{{ Locale::url('/obchodni-podminky') }}">{{ __('site.pages.podmínky') }}</a>
                <a href="{{ Locale::url('/ochrana-osobnich-udaju') }}">{{ __('site.pages.gdpr') }}</a>
                <a href="{{ Locale::url('/kontakt') }}">{{ __('site.pages.kontakt') }}</a>
            </nav>
        </div>
    </footer>
</body>
</html>
