@component('mail::message', ['contacts' => $mailContacts ?? true])
  @component('mail::title')
    <table width="100%">
      <tr>
        <td class="title-message">
          <span>🎁 {{ __('mail.landing_promo.title') }}</span>
          <hr class="title-line" />
        </td>
      </tr>
    </table>
  @endcomponent

  <p>{{ __('mail.landing_promo.intro') }}</p>

  <table class="order">
    <tr>
      <td class="cell-label">🏷️&nbsp;&nbsp;{{ __('mail.landing_promo.code_label') }}:</td>
    </tr>
    <tr>
      <td class="cell-value"><strong>{{ $promoCode }}</strong></td>
    </tr>
    <tr>
      <td class="cell-value">{{ __('mail.landing_promo.code_hint') }}</td>
    </tr>
  </table>

  @if(!empty($ctaUrl))
    @component('mail::button', ['url' => $ctaUrl])
      {{ __('mail.landing_promo.button') }}
    @endcomponent
  @endif
@endcomponent
