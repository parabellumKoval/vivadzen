@component('mail::message', ['contacts' => $mailContacts ?? true])
  @component('mail::title')
    <table width="100%">
      <tr>
        <td class="title-message">
          <span>💸 {{ $title }}</span>
          <hr class="title-line" />
        </td>
      </tr>
    </table>
  @endcomponent

  <p>{{ __('mail.withdrawal.paid_intro', ['id' => $withdrawal->id]) }}</p>

  <table class="order">
    <tr>
      <td class="cell-label">💰&nbsp;&nbsp;{{ __('mail.withdrawal.details_title') }}:</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $details ?? []])
      </td>
    </tr>
  </table>

  @component('mail::button', ['url' => $ctaUrl])
    {{ __('mail.withdrawal.button') }}
  @endcomponent
@endcomponent
