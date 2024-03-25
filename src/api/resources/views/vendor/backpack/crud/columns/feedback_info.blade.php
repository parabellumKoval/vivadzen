@php
  $product = $entry && isset($entry->extras) && isset($entry->extras['product'])? $entry->extras['product']: null;
@endphp

@if(isset($product) && !empty($product))
<div>
  <h3>Товар</h3>
  <div>
    @if(isset($product['image']['src']))
      <p><img src="{{ url($product['image']['src']) }}" width="100" height="100" /></p>
    @endif

    @if(isset($product['name']) && isset($product['short_name']))
      <p><strong>{{ $product['name'] ?? '' }}</strong> {{ $product['short_name'] ?? '' }}</p>
    @endif

    @if(isset($product['price']))
      <p>Цена: <strong>{{ $product['price'] }} $</strong></p>
    @endif

    @if(isset($product['amount']))
      <p>Количество: <strong>{{ $product['amount'] }} шт</strong></p>
    @endif

    @if(isset($product['price']) && isset($product['amount']))
      <p>Сумма: <strong>{{ $product['price'] * $product['amount'] }} $</strong></p>
    @endif
  </div>
  <br>
</div>
@endif