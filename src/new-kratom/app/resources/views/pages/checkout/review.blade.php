@php
    use App\Support\Locale;
    use App\Models\DeliveryMethod;
    use App\Models\PaymentMethod;
    $d = $checkout['delivery'];
    $p = $checkout['payment'];
    $age = $ageVerification ?? ['enabled' => false, 'required' => false, 'skipped' => false, 'public_key' => '', 'script_url' => ''];

    $deliveryModel = DeliveryMethod::where('code', $d['delivery_method'] ?? '')->first();
    $paymentModel = PaymentMethod::where('code', $p['payment_method'] ?? '')->first();
    $deliveryLabel = $deliveryModel?->localized('name') ?? ($d['delivery_method'] ?? '');
    $paymentLabel = $paymentModel?->localized('name') ?? ($p['payment_method'] ?? '');
@endphp

<x-checkout.layout :step="3">
    <script id="cart-bootstrap" type="application/json">@json($cart)</script>

    <div class="container checkout-page">
        <div class="checkout-page__grid">
            <form
                method="POST"
                action="{{ Locale::url('/pokladna/dokoncit') }}"
                class="checkout-form"
                x-data="{
                    age: false,
                    terms: false,
                    safety: false,
                    adultoRequired: {{ $age['required'] ? 'true' : 'false' }},
                    adultoUid: '',
                    init() {
                        const hidden = document.getElementById('age_verification_uid');
                        if (hidden) {
                            this.adultoUid = hidden.value || '';
                            hidden.addEventListener('input', () => { this.adultoUid = hidden.value || ''; });
                            hidden.addEventListener('change', () => { this.adultoUid = hidden.value || ''; });
                        }
                    },
                    get canSubmit() {
                        if (!this.age || !this.terms || !this.safety) return false;
                        if (this.adultoRequired && !this.adultoUid) return false;
                        return true;
                    },
                }"
            >
                @csrf

                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.review_title') }}</h2>

                    {{-- Delivery summary --}}
                    <div class="review-card">
                        <div class="review-card__head">
                            <x-ui.icon name="truck" :size="20" />
                            <strong>{{ $deliveryLabel }}</strong>
                            <a href="{{ Locale::url('/pokladna') }}" class="review-card__edit">{{ __('site.checkout.edit') }}</a>
                        </div>
                        <div class="review-card__body">
                            <p>{{ $d['first_name'] }} {{ $d['last_name'] }}</p>
                            <p>{{ $d['street'] }}, {{ $d['city'] }}, {{ $d['zip'] }}</p>
                            <p>{{ $d['phone'] }} · {{ $d['email'] }}</p>
                        </div>
                    </div>

                    {{-- Payment summary --}}
                    <div class="review-card">
                        <div class="review-card__head">
                            <x-ui.icon name="shield-check" :size="20" />
                            <strong>{{ $paymentLabel }}</strong>
                            <a href="{{ Locale::url('/pokladna/platba') }}" class="review-card__edit">{{ __('site.checkout.edit') }}</a>
                        </div>
                    </div>

                    {{-- Items review --}}
                    <div class="review-items">
                        @foreach($cart['items'] as $it)
                            <div class="review-item">
                                <span class="review-item__media">
                                    @if($it['image'])
                                        <img src="{{ $it['image'] }}" alt="{{ $it['name'] }}" />
                                    @endif
                                </span>
                                <div class="review-item__body">
                                    <strong>{{ $it['name'] }}</strong>
                                    <small>{{ $it['size'] }} {{ $it['unit'] ?? 'g' }} · {{ $it['qty'] }} ks</small>
                                </div>
                                <span class="review-item__price">
                                    {{ number_format($it['price'] * $it['qty'], 0, ',', ' ') }} {{ __('site.currency') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ADULTO age verification --}}
                @if($age['enabled'])
                    @if($age['skipped'])
                        <section class="checkout-section">
                            <div class="adulto-verification adulto-verification--skipped">
                                <div class="adulto-verification__head">
                                    <span class="adulto-verification__icon"><x-ui.icon name="badge-check" :size="22" /></span>
                                    <div>
                                        <h3 class="adulto-verification__title">{{ __('site.adulto.checkout_title') }}</h3>
                                        <p class="adulto-verification__desc">{{ __('site.adulto.skipped_notice') }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @elseif($age['required'])
                        <section class="checkout-section">
                            <x-checkout.adulto-verification
                                :public-key="$age['public_key']"
                                :script-url="$age['script_url']"
                            />
                        </section>
                    @endif
                @endif

                {{-- Consents --}}
                <section class="checkout-section">
                    <h2 class="checkout-section__title">{{ __('site.checkout.consents_title') }}</h2>

                    <label class="checkbox">
                        <input type="checkbox" name="consent_age" value="1" required x-model="age" />
                        <span>
                            {{ __('site.checkout.consent_age') }} *
                            <button type="button" class="checkbox__link" @click="window.dispatchEvent(new CustomEvent('adulto:open'))">
                                {{ __('site.adulto.checkout_open_guide') }}
                            </button>
                        </span>
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="consent_terms" value="1" required x-model="terms" />
                        <span>{!! str_replace(
                            ['obchodními podmínkami', 'zpracováním osobních údajů', 'terms and conditions', 'processing of personal data', 'условиями', 'обработкой персональных данных', 'умовами', 'обробкою персональних даних'],
                            ['<a href="' . Locale::url('/obchodni-podminky') . '" target="_blank">obchodními podmínkami</a>', '<a href="' . Locale::url('/ochrana-osobnich-udaju') . '" target="_blank">zpracováním osobních údajů</a>', '<a href="' . Locale::url('/obchodni-podminky') . '" target="_blank">terms and conditions</a>', '<a href="' . Locale::url('/ochrana-osobnich-udaju') . '" target="_blank">processing of personal data</a>', '<a href="' . Locale::url('/obchodni-podminky') . '" target="_blank">условиями</a>', '<a href="' . Locale::url('/ochrana-osobnich-udaju') . '" target="_blank">обработкой персональных данных</a>', '<a href="' . Locale::url('/obchodni-podminky') . '" target="_blank">умовами</a>', '<a href="' . Locale::url('/ochrana-osobnich-udaju') . '" target="_blank">обробкою персональних даних</a>'],
                            e(__('site.checkout.consent_terms'))
                        ) !!} *</span>
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="consent_safety" value="1" required x-model="safety" />
                        <span>{{ __('site.checkout.consent_safety') }} *</span>
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="consent_marketing" value="1" />
                        <span>{{ __('site.checkout.consent_marketing') }}</span>
                    </label>
                </section>

                <div class="checkout-actions">
                    <a href="{{ Locale::url('/pokladna/platba') }}" class="btn btn--ghost btn--md">
                        ← {{ __('site.checkout.back_to_payment') }}
                    </a>
                    <button type="submit" class="btn btn--primary btn--lg" :disabled="!canSubmit">
                        {{ __('site.checkout.confirm_and_pay') }} {{ number_format($cart['total'], 0, ',', ' ') }} {{ __('site.currency') }}
                        <span class="btn__icon"><x-ui.icon name="arrow-right" /></span>
                    </button>
                </div>
            </form>

            <x-checkout.order-summary :cart="$cart" />
        </div>
    </div>
</x-checkout.layout>
