@php
    use App\Support\Locale;
    $email = $order['delivery']['email'] ?? '';
@endphp

<x-layouts.app
    title="{{ __('site.order.success_title') }} · {{ $orderId }} | Vivadzen"
    :announcement="false"
>
    @push('head')
        <meta name="robots" content="noindex,nofollow" />
    @endpush

    <section class="order-status">
        <div class="container container--narrow">
            <div class="order-status__hero">
                <span class="order-status__check" aria-hidden="true">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </span>
                <h1 class="order-status__title">{{ __('site.order.success_title') }}</h1>
                <p class="order-status__intro">{{ __('site.order.success_intro') }}</p>

                @if($orderId)
                    <p class="order-status__id">
                        <span>{{ __('site.order.order_number') }}</span>
                        <strong>#{{ $orderId }}</strong>
                    </p>
                @endif

                @if($email)
                    <p class="order-status__email">
                        {{ str_replace(':email', $email, __('site.order.success_email')) }}
                    </p>
                @endif
            </div>

            @if($order)
                <div class="order-status__cards">
                    <div class="order-status__card">
                        <p class="order-status__card-eyebrow">{{ __('site.checkout.delivery_title') }}</p>
                        <p><strong>{{ $order['delivery']['first_name'] }} {{ $order['delivery']['last_name'] }}</strong></p>
                        <p>{{ $order['delivery']['street'] }}, {{ $order['delivery']['city'] }}, {{ $order['delivery']['zip'] }}</p>
                        <p>{{ $order['delivery']['phone'] }}</p>
                    </div>
                    <div class="order-status__card">
                        <p class="order-status__card-eyebrow">{{ __('site.checkout.payment_title') }}</p>
                        <p><strong>
                            @switch($order['payment']['payment_method'])
                                @case('card') {{ __('site.checkout.payment_card') }} @break
                                @case('qr')   {{ __('site.checkout.payment_qr') }} @break
                                @case('bank') {{ __('site.checkout.payment_bank') }} @break
                                @case('cod')  {{ __('site.checkout.payment_cod') }} @break
                                @case('store') {{ __('site.checkout.payment_store') }} @break
                            @endswitch
                        </strong></p>
                        <p>{{ number_format($order['cart']['total'], 0, ',', ' ') }} {{ __('site.currency') }}</p>
                    </div>
                </div>
            @endif

            <div class="order-status__next">
                <h2>{{ __('site.order.whats_next') }}</h2>
                <ol>
                    <li>{{ __('site.order.next_1') }}</li>
                    <li>{{ __('site.order.next_2') }}</li>
                    <li>{{ __('site.order.next_3') }}</li>
                </ol>
            </div>

            <div class="order-status__actions">
                <a href="{{ Locale::url('/kratom') }}" class="btn btn--primary btn--lg">
                    {{ __('site.order.continue_shopping') }}
                    <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
