<table class="products" width="100%" cellpadding="0" cellspacing="0">
  @foreach($products as $key => $product)
    <tr class="product">
      <td class="product-cell-image">
        <img src="{{ config('backpack.store.product.image.base_path') . $product['image']['src'] }}" class="product-image" />
      </td>
      <td class="product-cell-value">
        {{ $product['name'] }}
      </td>
      <td class="product-cell-value product-price">
        {{ $product['price'] }}
      </td>
      <td class="product-cell-value product-amount">
        x{{ $product['amount'] }}
      </td>
      <td class="product-cell-value product-price">
        {{ $product['amount'] * $product['price'] }} грн.
      </td>
    </tr>
  @endforeach
</table>