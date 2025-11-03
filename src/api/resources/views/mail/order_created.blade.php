@component('mail::message', ["contacts" => true])
  @component('mail::title')
  <table width="100%">
    <tr>
      <td class="title-message" colspan="2">
        <span>😍 {{ __('mail.thank_for_order') }}</span>
        <hr class="title-line" />
      </td>
    </tr>
    <tr class="title-inner">
      <td class="title-number">@lang('email.order_code'): {{ $order->code }}</td>
      <td class="title-data">{{ $order->created_at->format('d.m.Y H:i') }}</td>
    </tr>
  </table>
  @endcomponent

  <table class="order">
    <tr>
      <td class="cell-label">⚡&nbsp;&nbsp;@lang('email.summary'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $summary ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">💰&nbsp;&nbsp;@lang('email.totals'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $pricing ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">🎯&nbsp;&nbsp;@lang('email.adjustments'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $adjustments ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">🙋‍♀️&nbsp;&nbsp;@lang('email.customer'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $customer ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">🚕&nbsp;&nbsp;@lang('email.delivery'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $delivery ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">💳&nbsp;&nbsp;@lang('email.payment'):</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $payment ?? []])

        @if(!empty($invoice['download_url']) || !empty($invoice['qr_url']))
          <div class="invoice-block">
            @if(!empty($invoice['download_url']))
              <div class="invoice-link">
                <a href="{{ $invoice['download_url'] }}">@lang('email.invoice_download_link')</a>
              </div>
            @endif
            @if(!empty($invoice['qr_url']))
              <div class="invoice-qr">
                <img src="{{ $invoice['qr_url'] }}" alt="@lang('email.invoice_qr_alt')" class="invoice-qr-image" />
              </div>
            @endif
          </div>
        @endif
      </td>
    </tr>
    <tr>
      <td class="cell-label">🛍&nbsp;&nbsp;@lang('email.products'):</td>
    </tr>
    <tr>
      <td class="">
        @component('mail::cart', ['products' => $products ?? [], 'currency' => $currency ?? null])
        @endcomponent
      </td>
    </tr>
  </table>
@endcomponent
