<?php
namespace App\Traits;

// use App\Models\Product;
use Spatie\Feed\FeedItem;

trait ProductGoogleMerchantsTrait {

  public static function getMerchantsFeedItems() {
    $products = self::where('is_active', 1);
    $products_cursore = $products->cursor();

    $feed_products = collect();

    foreach($products_cursore as $product) {
      $feed_item = new FeedItem([ 
        'id' => $product->id,
        'title' => $product->name? $product->name: '–',
        'link' => $product->webLink,
        'updated' => $product->updated_at,
        'availability' => $product->merchantsAvailability,
        'summary' => $product->merchantsDescription,
        'price' => $product->merchantsPrice,
        'sale_price' => $product->merchantsSalePrice,
        'image' => $product->merchantsImage,
        'second_image' => $product->merchantsSecondImage,
        'brand' => $product->merchantsBrand,
        'google_product_category' => $product->merchantsGoogleProductCategory,
        'product_type' => $product->merchantsCategory,
        // 'shipping' => $product->merchantsShipping,
        'gtin' => $product->merchantsGtin,
        'mpn' => $product->merchantsMpn,
        'authorName' => 'djini.com.ua'
      ]);
      $feed_products->push($feed_item);
    }

    return $feed_products;
  }
  
  
  public function getMerchantsShippingAttribute() {
    return '';
  }

  public function getMerchantsGtinAttribute() {
    $barcodes_array = $this->sp->pluck('barcode')->toArray();
    return !empty($barcodes_array)? $barcodes_array[0]: '';
  }

  public function getMerchantsMpnAttribute() {
    $codes_array = $this->sp->pluck('code')->toArray();
    return !empty($codes_array)? $codes_array[0]: '';
  }
  
  public function getMerchantsSecondImageAttribute() {
    return isset($this->imageSrcs[1]) && !empty($this->imageSrcs[1])? $this->imageSrcs[1]: '';

  }

  public function getMerchantsImageAttribute() {
    return isset($this->imageSrcs[0]) && !empty($this->imageSrcs[0])? $this->imageSrcs[0]: '';
  }

  public function getMerchantsGoogleProductCategoryAttribute() {
    if(!$this->category) {
      return '';
    }

    return $this->category->merchant? $this->category->merchant->key: 469;
  }

  public function getMerchantsCategoryAttribute() {
    if(!$this->category) {
      return '';
    }

    $items_array = $this->category->getParentNode()->pluck('name')->toArray();

    if(empty($items_array)) {
      return '';
    }

    $categories = array_reverse($items_array);
    return implode(' > ', $categories);
  }

  public function getMerchantsBrandAttribute() {
    return $this->brand? $this->brand->name: '';
  }

  public function getMerchantsDescriptionAttribute() {
    $content = $this->getTranslation('merchant_content', 'uk', false);
    
    if(empty($content)) {
      $content = $this->getTranslation('content', 'uk', false);
    }

    if(empty($content)) {
      return '';
    }

    $trimmed_content = mb_substr($content, 0, 1000);

    return $trimmed_content; 
  }

  public function getMerchantsSalePriceAttribute() {
    return $this->simpleOldPrice? $this->simpleOldPrice . ' UAH': '';
  }

  public function getMerchantsPriceAttribute() {
    return $this->simplePrice . ' UAH';
  }

  public function getMerchantsAvailabilityAttribute() {
    if($this->simpleInStock > 0) {
      return 'in_stock';
    }else {
      return 'out_of_stock';
    }
  }

  public function getWebLinkAttribute() {
    return "https://djini.com.ua/{$this->slug}";
  }
}