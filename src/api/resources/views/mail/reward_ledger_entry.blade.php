@component('mail::message', ['contacts' => true])
  @component('mail::title')
    <table width="100%">
      <tr>
        <td class="title-message">
          <span>
            {{ $direction === 'credit'
              ? __('mail.reward.title.credit', ['amount' => $amountLabel])
              : __('mail.reward.title.debit', ['amount' => $amountLabel]) }}
          </span>
          <hr class="title-line" />
        </td>
      </tr>
    </table>
  @endcomponent

  <p>{{ $intro }}</p>

  @if($isReversal)
    <p>{{ __('mail.reward.reversal_notice') }}</p>
  @endif

  <table class="order">
    <tr>
      <td class="cell-label">📋&nbsp;&nbsp;{{ __('mail.reward.details_title') }}:</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $details ?? []])
      </td>
    </tr>
  </table>

  @if(!empty($balance))
    <p>{{ __('mail.reward.balance_line', ['balance' => $balance]) }}</p>
  @endif

  <p>{{ __('mail.reward.footer_note') }}</p>

  @component('mail::button', ['url' => $ctaUrl])
    {{ __('mail.reward.button') }}
  @endcomponent
@endcomponent
