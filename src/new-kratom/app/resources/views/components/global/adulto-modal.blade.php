@php use App\Support\Locale; @endphp

{{-- Compact age-verification guide modal opened from checkout / cart with the
     Alpine global event: window.dispatchEvent(new CustomEvent('adulto:open')).
     The full long-form version lives at /overeni-veku. --}}
<div
    x-data="adultoModal"
    x-cloak
    class="adulto-modal"
    :class="open && 'is-open'"
    @keydown.escape.window="close()"
>
    <div class="adulto-modal__backdrop" x-show="open" x-transition.opacity @click="close()"></div>

    <div
        class="adulto-modal__panel"
        x-show="open"
        x-transition:enter="adulto-modal__panel--enter"
        x-transition:enter-start="adulto-modal__panel--enter-start"
        x-transition:enter-end="adulto-modal__panel--enter-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adulto-modal-title"
    >
        <header class="adulto-modal__head">
            <p class="adulto-modal__eyebrow">{{ __('site.adulto.eyebrow') }}</p>
            <h2 id="adulto-modal-title" class="adulto-modal__title">{{ __('site.adulto.modal_title') }}</h2>
            <button type="button" class="adulto-modal__close" @click="close()" aria-label="{{ __('site.header.close') }}">
                <x-ui.icon name="x" />
            </button>
        </header>

        <div class="adulto-modal__body">
            <x-static.adulto-guide version="short" />
        </div>

        <footer class="adulto-modal__foot">
            <a href="{{ Locale::url('/overeni-veku') }}" class="btn btn--ghost btn--md">
                {{ __('site.adulto.open_full_guide') }}
            </a>
            <button type="button" class="btn btn--primary btn--md" @click="close()">
                {{ __('site.adulto.modal_close') }}
            </button>
        </footer>
    </div>
</div>
