@component('mail::message')
  @component('mail::title')
  <table width="100%">
    <tr class="title-inner">
      <td class="title-number">Новый заказ: {{ $order->code }}</td>
      <td class="title-data">{{ $order->created_at->format('d.m.Y H:i') }}</td>
    </tr>
  </table>
  @endcomponent

  <table class="order">
    <tr>
      <td class="cell-label">⚡&nbsp;&nbsp;Общее:</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $common !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🙋‍♀️&nbsp;&nbsp;Покупатель:</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $user !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🚕&nbsp;&nbsp;Доставка:</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $delivery !!}</td>
    </tr>
    <tr>
      <td class="cell-label">💳&nbsp;&nbsp;Оплата:</td>
    </tr>
    <tr>
      <td class="cell-value">{!! $payment !!}</td>
    </tr>
    <tr>
      <td class="cell-label">🛍&nbsp;&nbsp;Товары:</td>
    </tr>
    <tr>
      <td class="">
        @component('mail::cart', ['products' => $products ])
        @endcomponent
      </td>
    </tr>
  </table>

  @component('mail::button', ['url' => url('/admin/order/'.$order->id.'/show') ])
    Подробнее
  @endcomponent
@endcomponent
