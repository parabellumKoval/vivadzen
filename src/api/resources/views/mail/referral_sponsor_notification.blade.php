@component('mail::message', ['contacts' => true])
  @component('mail::title')
    <table width="100%">
      <tr>
        <td class="title-message">
          <span>🤝 {{ __('mail.referral.new_sponsor_title') }}</span>
          <hr class="title-line" />
        </td>
      </tr>
    </table>
  @endcomponent

  <p>{{ __('mail.referral.new_sponsor_intro', ['name' => $sponsorName ?: __('mail.referral.sponsor_fallback')]) }}</p>

  <table class="order">
    <tr>
      <td class="cell-label">👤&nbsp;&nbsp;{{ __('mail.referral.details_title') }}:</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $details ?? []])
      </td>
    </tr>
  </table>

  @component('mail::button', ['url' => $ctaUrl])
    {{ __('mail.referral.new_sponsor_button') }}
  @endcomponent
@endcomponent
