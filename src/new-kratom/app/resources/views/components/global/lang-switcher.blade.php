@php
    use App\Support\Locale;
    $locales = Locale::all();
    $current = $currentLocale ?? app()->getLocale();
    $currentMeta = $locales[$current] ?? $locales[Locale::DEFAULT];
    $basePath = Locale::stripPrefix(request()->path() === '/' ? '/' : '/' . request()->path());
@endphp

<div class="lang-switcher" x-data="{ open: false }" @click.outside="open = false">
    <button
        type="button"
        class="lang-switcher__trigger"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        aria-label="{{ __('site.header.language') }}"
    >
        <span class="lang-switcher__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M2 12h20"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
        </span>
        <span class="lang-switcher__code">{{ $currentMeta['native'] }}</span>
        <svg class="lang-switcher__caret" :class="open && 'is-open'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    <ul
        class="lang-switcher__menu"
        x-show="open"
        x-transition.opacity.duration.150ms
        x-cloak
        role="listbox"
        aria-label="{{ __('site.header.language') }}"
    >
        @foreach($locales as $code => $meta)
            @php
                $href = Locale::url($basePath, $code);
                $isCurrent = $code === $current;
            @endphp
            <li role="option" aria-selected="{{ $isCurrent ? 'true' : 'false' }}">
                <a
                    href="{{ $href }}"
                    class="lang-switcher__item @if($isCurrent) is-current @endif"
                    hreflang="{{ $meta['htmlLang'] }}"
                >
                    <span class="lang-switcher__item-code">{{ $meta['native'] }}</span>
                    <span class="lang-switcher__item-label">{{ $meta['label'] }}</span>
                    @if($isCurrent)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
