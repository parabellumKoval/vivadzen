@php
    use App\Support\Locale;
    $allLocales = Locale::all();
    $current = $currentLocale ?? app()->getLocale();
    $htmlLang = $allLocales[$current]['htmlLang'] ?? 'cs-CZ';
    $currentPath = '/' . ltrim(request()->path(), '/');
    $basePath = Locale::stripPrefix($currentPath === '/' ? '/' : $currentPath);
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#1B3A2D" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @foreach($allLocales as $code => $meta)
        <link rel="alternate" hreflang="{{ $meta['htmlLang'] }}" href="{{ url(Locale::url($basePath, $code)) }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url(Locale::url($basePath, 'cs')) }}" />

    <title>{{ $title ?? 'Kratom s licencí MZ ČR — laboratorně testovaný | Vivadzen' }}</title>
    <meta name="description"
          content="{{ $description ?? 'Specializovaný e-shop kratomu pod licencí PML. Každá šarže testovaná VŠCHT Praha. 2 prodejny v Praze. Doručení do 180 min v Praze a Ostravě.' }}" />

    {{-- Preconnect / fonts. Загружаем CSS асинхронно — это вытащит ~200ms
         render-blocking из waterfall и не даст fonts.googleapis.com быть
         bottleneck-ом. Браузер уже знает о connect-е к fonts.gstatic.com,
         поэтому когда CSS загрузится — woff2 пойдут параллельно. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        rel="preload"
        as="style"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" />
    <link
        rel="stylesheet"
        media="print" onload="this.media='all'"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" />
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" />
    </noscript>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />

    @stack('head')

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body @class(['has-transparent-header' => $transparentHeader ?? false])>
    <a href="#main" class="skip-link">Přeskočit na obsah</a>

    @if($announcement ?? true)
        <x-global.announcement-bar />
    @endif

    <x-global.header :transparent="$transparentHeader ?? false" />

    @auth
        @if(! auth()->user()->hasVerifiedEmail())
            <div class="verify-banner" x-data="{ sent: false }">
                <span>{{ __('site.auth.verify.banner') }}</span>
                <button
                    type="button"
                    class="verify-banner__btn"
                    x-show="!sent"
                    @click="
                        fetch('{{ url('/email/verification-notification') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                        }).then(() => sent = true)
                    "
                >{{ __('site.auth.verify.resend') }}</button>
                <span x-show="sent" x-cloak>✓ {{ __('site.auth.verify.resent') }}</span>
            </div>
        @endif
    @endauth

    <x-global.age-strip />

    <main id="main" tabindex="-1">
        {{ $slot }}
    </main>

    <x-global.footer />

    <x-global.cart-modal />

    @guest
        <x-global.auth-modal />
    @endguest

    <script id="cart-bootstrap" type="application/json">@json(\App\Support\Cart::snapshot())</script>
    <script>
        window.__cartStrings = {
            added: @json(__('site.cart.added_toast')),
            add_to_cart: @json(__('site.add_to_cart')),
        };
    </script>

    @stack('schema')
</body>
</html>
