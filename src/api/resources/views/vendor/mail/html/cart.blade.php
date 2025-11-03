<table class="products" width="100%" cellpadding="0" cellspacing="0">
  @foreach($products as $key => $product)
    @php
    //config('backpack.store.product.image.base_path')
      $image = $product['image'] ?? null;
      $src = $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : url($image)) : null;
      $price = $product['price_formatted'] ?? null;
      $total = $product['total_formatted'] ?? null;
      $currencyCode = $product['currency'] ?? ($currency ?? null);

      if(!$price) {
        $rawPrice = (float) ($product['price'] ?? 0);
        $price = number_format($rawPrice, 2, '.', ' ');
        if($currencyCode) {
          $price .= ' ' . $currencyCode;
        }
      }

      if(!$total) {
        $rawTotal = (float) ($product['price'] ?? 0) * (float) ($product['amount'] ?? 0);
        $total = number_format($rawTotal, 2, '.', ' ');
        if($currencyCode) {
          $total .= ' ' . $currencyCode;
        }
      }
    @endphp
    <tr class="product">
      <td class="product-cell-image">
        <img src="{{ $src }}" class="product-image" />
      </td>
      <td class="product-cell-value">
        {{ $product['name'] }}
      </td>
      <td class="product-cell-value product-price">
        {{ $price }}
      </td>
      <td class="product-cell-value product-amount">
        x{{ $product['amount'] }}
      </td>
      <td class="product-cell-value product-price">
        {{ $total }}
      </td>
    </tr>
  @endforeach
</table>
