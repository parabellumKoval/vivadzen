@props([
    'transparent' => false,
])

@php
    use App\Support\Locale;
    $nav = [
        ['label' => __('site.nav.kratom'),     'href' => Locale::url('/kratom')],
        ['label' => __('site.nav.maeng_da'),   'href' => Locale::url('/kratom/maeng-da')],
        ['label' => __('site.nav.extrakt'),    'href' => Locale::url('/kratom/extrakt')],
        ['label' => __('site.nav.predplatne'), 'href' => Locale::url('/predplatne')],
        ['label' => __('site.nav.lab'),        'href' => Locale::url('/laboratorni-testy')],
        ['label' => __('site.nav.prodejny'),   'href' => Locale::url('/prodejny')],
        ['label' => __('site.nav.pruvodce'),   'href' => Locale::url('/pruvodce')],
    ];
    $cartCount = (int) collect(session('cart.items', []))->sum('qty');
@endphp

<header
    class="header @if(!$transparent) header--solid @endif"
    x-data="header"
    :class="scrolled && 'is-scrolled'"
>
    <div class="container header__inner">
        <a href="{{ Locale::url('/') }}" class="header__logo" aria-label="Vivadzen">
            <img
                class="header__logo-image"
                src="{{ asset('assets/brand/logo-vivadzen-primary-light.avif') }}"
                alt="Vivadzen"
                loading="eager"
            />
        </a>

        <nav class="header__nav" aria-label="Hlavní navigace">
            @foreach($nav as $item)
                <a href="{{ $item['href'] }}" class="header__nav-link">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="header__actions">
            <x-global.lang-switcher />

            <a href="{{ Locale::url('/ucet') }}" class="header__icon-btn" aria-label="{{ __('site.header.account') }}">
                <x-ui.icon name="user" />
            </a>

            <a
                href="{{ Locale::url('/kosik') }}"
                class="header__icon-btn"
                aria-label="{{ __('site.header.cart') }}"
                data-cart-link
            >
                <x-ui.icon name="shopping-bag" />
                <span
                    class="header__badge-count"
                    data-cart-count
                    @if($cartCount === 0) hidden @endif
                    aria-hidden="true"
                >{{ $cartCount }}</span>
            </a>

            <button
                type="button"
                class="header__icon-btn header__menu-btn"
                @click="toggleDrawer()"
                aria-label="{{ __('site.header.menu') }}"
                :aria-expanded="drawerOpen.toString()"
            >
                <x-ui.icon name="menu" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div class="header__drawer" :class="drawerOpen && 'is-open'" x-show="drawerOpen" x-cloak>
        <div class="header__drawer-backdrop" @click="closeDrawer()"></div>
        <div class="header__drawer-panel">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ Locale::url('/') }}" class="header__logo">
                    <img
                        class="header__logo-image"
                        src="{{ asset('assets/brand/logo-vivadzen-primary-light.avif') }}"
                        alt="Vivadzen"
                        loading="eager"
                    />
                </a>
                <button type="button" class="header__icon-btn" @click="closeDrawer()" aria-label="{{ __('site.header.close') }}">
                    <x-ui.icon name="x" />
                </button>
            </div>

            <nav class="flex flex-col gap-4 mt-8" aria-label="Mobilní navigace">
                @foreach($nav as $item)
                    <a href="{{ $item['href'] }}" class="header__nav-link">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="header__drawer-lang">
                <x-global.lang-switcher />
            </div>
        </div>
    </div>
</header>
