@props(['step' => 1])

@php
    $steps = [
        ['n' => 1, 'label' => __('site.checkout.steps.delivery')],
        ['n' => 2, 'label' => __('site.checkout.steps.payment')],
        ['n' => 3, 'label' => __('site.checkout.steps.confirm')],
    ];
@endphp

<ol class="checkout-stepper" aria-label="Checkout progress">
    @foreach($steps as $s)
        @php
            $state = $step > $s['n'] ? 'done' : ($step === $s['n'] ? 'current' : 'pending');
        @endphp
        <li class="checkout-stepper__item is-{{ $state }}">
            <span class="checkout-stepper__dot">
                @if($state === 'done')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                @else
                    {{ $s['n'] }}
                @endif
            </span>
            <span class="checkout-stepper__label">{{ $s['label'] }}</span>
            @if(!$loop->last)
                <span class="checkout-stepper__line"></span>
            @endif
        </li>
    @endforeach
</ol>
