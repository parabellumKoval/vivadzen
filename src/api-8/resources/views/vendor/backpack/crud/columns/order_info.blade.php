@php
  $products = $entry->info['products'] ?? [];
  $bonusesUsed = $entry->info['bonusesUsed'] ?? 0;
  $info = \Illuminate\Support\Arr::except($entry->info, ['products']);

  $user = isset($info['user'])? array_filter($info['user']): null;

  //$payment = isset($info['payment'])? array_filter($info['payment']): null;
  if(isset($info['payment'])) {
    if(is_array($info['payment'])) {
      $payment_items = array_filter($info['payment']);
      $payment = implode(', ', $payment_items);
    }else {
      $payment = $info['payment'];
    }
    //$payment = is_array($info['payment'])? array_filter($info['payment']): $info['payment'];
  }

  
  if(isset($info['delivery'])) {
    //dd($info['delivery']);
    if(is_array($info['delivery'])) {
      $keys = array("zip", "area", "room", "type", "house", "method", "region", "street", "warehouse", "settlement");
      $delivery_items = array_filter($info['delivery']);
      $delivery_items = array_intersect_key($delivery_items, array_flip($keys));
      $delivery = implode(', ', $delivery_items);
    }else {
      $delivery = $info['delivery'];
    }
    //$delivery = is_array($info['delivery'])? array_filter($info['delivery']): $info['delivery'];
  }

  //$delivery = isset($info['delivery'])? array_filter($info['delivery']): null;
@endphp

<span>
  @if($user && !empty($user))
    <p>Покупатель: <strong>{{ implode(', ', $user) }}</strong></p>
  @endif
  
  @if($payment && !empty($payment))
    <p>Оплата: <strong>{{ $payment }}</strong></p>
  @endif

  @if($delivery && !empty($delivery))
    <p>Доставка: <strong>{{ $delivery }}</strong></p>
  @endif
  
  @if(isset($info['comment']))
    <p>Комментарий: <strong>{{ $info['comment'] }}</strong></p>
  @endif
  
  <hr>

  <h5>Товары:</h5>
  <br>

  @foreach($products as $product)
    <div>
      @if(isset($product['image']['src']))
        <p>
          <img src="{{ config('backpack.store.product.image.base_path') . $product['image']['src'] }}" width="auto" height="100" />
        </p>
      @endif

      <a href="{{ config('app.client_url') . '/' . $product['slug'] }}" target="_blank">
        <strong>{{ $product['name'] ?? '' }}</strong> {{ $product['short_name'] ?? '' }}
      </a>
      <a href="{{ url('/admin/product/' . $product['id'] . '/edit') }}" target="_blank">
        (Редактировать)
      </a>
      @if($product['old_price'])
      <p>Старая цена: <s>{{ config('backpack.store.currency.symbol') . ' ' . $product['old_price'] }}</s></p>
      @endif
      <p>Цена: {{ config('backpack.store.currency.symbol') . ' ' . $product['price'] }}</p>
      <p>Количество: {{ $product['amount'] }} шт</p>
      <p>Сумма: <strong>{{ config('backpack.store.currency.symbol') . ' ' . ($product['price'] * $product['amount']) }}</strong></p>
    </div>
    <br>
  @endforeach

  @if(config('backpack.store.order.enable_bonus', false))
    <h4>Использовано бонусов: <strong>{{ config('backpack.store.currency.symbol') . $bonusesUsed }}</strong></h4>
  @endif

  @if($entry->promocode)
    <hr>
    <div>Промокод: 
      <strong><a href='{{ url("/admin/promocode/{$entry->promocode['id']}/edit") }}'>{{ $entry->promocode['code'] }}</a></strong>
      <strong>({{ $entry->promocodeSaleString }})</strong>
    </div>
  @endif

  <hr>
  <h4>Сумма заказа: <strong>{{ config('backpack.store.currency.symbol') . ($entry->price - $bonusesUsed) }}</strong></h4>
</span>