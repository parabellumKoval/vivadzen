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
      <td class="cell-label">⚡&nbsp;&nbsp;@lang('email.common'):</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $common !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🙋‍♀️&nbsp;&nbsp;@lang('email.customer'):</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $user !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🚕&nbsp;&nbsp;@lang('email.delivery'):</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $delivery !!}</td>
    </tr>
    <tr>
      <td class="cell-label">💳&nbsp;&nbsp;@lang('email.payment'):</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $payment !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🛍&nbsp;&nbsp;@lang('email.products'):</td>
    </tr>
    <tr>
      <td class="">
        @component('mail::cart', ['products' => $products ])
        @endcomponent
      </td>
    </tr>
  </table>
@endcomponent