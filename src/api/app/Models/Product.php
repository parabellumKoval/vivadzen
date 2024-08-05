<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

use Backpack\Store\app\Models\Product as BaseProduct;

// REVIEWS
use Backpack\Reviews\app\Traits\Reviewable;

ini_set('memory_limit', '2024M');

class Product extends BaseProduct implements Feedable
{
  use Searchable;
  use Reviewable;

  public function getMorphClass()
  {
      return 'Backpack\Store\app\Models\Product';
  }

  public function toSearchableArray()
  {
      $array = [
        'name' => $this->name,
      ];

      return $array;
  }

  public function shouldBeSearchable()
  {
      return $this->active()->inStock();
  }

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
    
  public function getSpecsAttribute() {
    return $this->extras['specs'] ?? null;
  }

  public function getImageSrcAttribute() {
    $base_path = config('backpack.store.product.image.base_path', '/');

    if(isset($this->image['src'])) {
      return $base_path . $this->image['src'] . "?width=50&height=50";
    }else {
      return null;
    }
  }

  
  /**
   * toFeedItem
   *
   * @return void
   */
  public function toFeedItem(): FeedItem {
    $properties = [];
    $properties_string = '';

    // if($this->properties) {
    //   foreach($this->properties as $property){
    //     $value_object = $property['value'];
    //     $value = null;

    //     if(is_array($value_object)) {
    //       $values = [];
    //       foreach($value_object as $vo) {
    //         $values[] = $vo->value;
    //       }
    //       $value = implode(', ', $values);
    //     }else {
    //       $value = $value_object;
    //     }

    //     $properties[] = $property['name'] . ': ' . $value;
    //   }

    //   $properties_string = implode('; ', $properties);
    // }
    
    //
    $description = $this->content;
    $short_desc = strlen($description) > 10? 
        strip_tags( substr($description, strpos($description, "<p"), strpos($description, "</p>")+4) ): '';
    
    return FeedItem::create([
      'id' => $this->id,
      'title' => $this->name,
      'summary' => mb_convert_encoding( $short_desc, 'UTF-8', 'UTF-8' ),
      'description' => $description,
      'authorName' => 'author',
      'quantity_in_stock' => $this->in_stock,
      'presence' => $this->in_stock > 0?  'true': 'false',
      'availability' => $this->in_stock > 0? 'in stock': '0',
      'link' => url($this->slug),
      'vendorCode' => $this->code,
      'price' => $this->price,
      'sale_price' => $this->price,
      'image' => $this->image['src'] ?? '',
      'second_image' => 'second_image',
      'brand' => $this->brand->name ?? null,
      'condition' => 'новый',
      'mpn' => '4234', //код товара для тех у которых нет кода GTIN
      'gtin' => '1234', //для всех товаров, у которых есть код GTIN
      'base_measure' => 'ct', //единица, за которую рассчитывается цена товара
      'google_product_category' => 525, //категория товара в соответствии с классификацией гугл
      'categoryName' => $this->category->name ?? null,
      'updated' => $this->updated_at,
    ]);
  }
    
	public static function getFeedItems() {
	   return self::where('in_stock', '>', 0)
        ->whereNotIn('old_id', [960,957,563,924,571,958,341,966,578,570,814,575,954,959,584,561,572,499,508,731,343,919,953,923,581,830,985,707,918])
        ->get();
	}
	
	
    
/*
	public static function getPromBelokFeedItems()
	{	
		return self::where('parsed_from', 'belok.ua')->whereNotNull('image_large')->get();
	} 
*/ 
    
	public static function getPromFeedItems()
	{
		// supplier_id 22,10 - iHerb и Склад
		return self::whereIn('parsed_from', ['dobavki.ua', 'belok.ua', 'proteinplus.pro'])
      ->whereNotIn('supplier_id', [22, 10])
      ->where('images', '!=', null)
      // ->whereJsonContains('images[0].src', null)
      // ->where('images->0->src', 'not like', 'null')
      ->where('is_active', 1)
      ->limit(10000)
      ->get();

    // return $items->filter(function($item) {
    //   if($item->image['src']) {
    //     return $item;
    //   }
    // }); 
		//return self::whereIn('parsed_from', ['dobavki.ua', 'belok.ua'])->whereNotNull('image_large')->get();
	   //return self::where('amount', '>=', 5)->where('parsed_from', 'dobavki.ua')->get();
	}    
	
}