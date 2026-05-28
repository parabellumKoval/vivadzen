@php use App\Support\Locale; @endphp

<x-layouts.app title="{{ __('site.order.error_title') }} | Vivadzen" :announcement="false">
    @push('head')
        <meta name="robots" content="noindex,nofollow" />
    @endpush

    <section class="order-status order-status--error">
        <div class="container container--narrow">
            <div class="order-status__hero">
                <span class="order-status__alert" aria-hidden="true">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </span>
                <h1 class="order-status__title">{{ __('site.order.error_title') }}</h1>
                <p class="order-status__intro">{{ __('site.order.error_text') }}</p>
            </div>

            <div class="order-status__actions">
                <a href="{{ Locale::url('/pokladna/platba') }}" class="btn btn--primary btn--lg">
                    {{ __('site.order.try_again') }}
                </a>
                <a href="{{ Locale::url('/kontakt') }}" class="btn btn--ghost btn--md">
                    {{ __('site.order.contact_support') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
