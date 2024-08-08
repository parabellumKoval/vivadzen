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
      'image' => '',
      'second_image' => 'second_image',
      'brand' => $this->brand ?? null,
      'condition' => 'новый',
      'mpn' => '4234', //код товара для тех у которых нет кода GTIN
      'gtin' => '1234', //для всех товаров, у которых есть код GTIN
      'base_measure' => 'ct', //единица, за которую рассчитывается цена товара
      'google_product_category' => 525, //категория товара в соответствии с классификацией гугл
      // 'categoryName' => $this->category->name ?? null,
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
    
  private static function pretty_bytes($bytes, $precision = 2){
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 
   
    return round($bytes, $precision) . $units[$pow]; 
  }

  private static function echoMemoryUsage() {

    $text = '';
    $text .= "Memory usage: " . self::pretty_bytes(memory_get_usage()) . PHP_EOL . "\n";
    $text .= "Peak memory usage: " . self::pretty_bytes(memory_get_peak_usage()) . PHP_EOL . "\n";
    $text .= "'Actual' memory usage: " . self::pretty_bytes(memory_get_usage(true)) . PHP_EOL . "\n";
    $text .= "'Actual' peak memory usage: " . self::pretty_bytes(memory_get_peak_usage(true)) . PHP_EOL . "\n";
    
    $ps_output = exec("ps --pid " . getmypid() . " --no-headers -o rss");
    
    $text .= "'Memory usage according to ps: " . self::pretty_bytes(intval($ps_output) * 1000) . "\n";

    \Log::info($text);

  }

	public static function getPromFeedItems()
	{

    $limit = \Request::get('limit');
    $skip = \Request::get('skip', 0);

		// supplier_id 22,10 - iHerb и Склад
		// $items = self::whereIn('parsed_from', ['dobavki.ua', 'belok.ua', 'proteinplus.pro'])
    //   ->whereNotIn('supplier_id', [22, 10])
    //   ->where('images', '!=', null)
    //   ->where('is_active', 1)
    //   ->limit($limit)
    //   ->skip($skip)
    //   ->get();

    $items = \DB::table('ak_products as p')
      ->select(['p.id', 'p.old_id', 'p.name->ru as name', 'p.slug', 'p.code', 'p.content->ru as content', 'p.images', 'p.in_stock', 'p.price', 'p.updated_at', 'b.name->ru as brand'])
      ->join('ak_brands as b', 'p.brand_id', '=', 'b.id')
      ->whereIn('p.parsed_from', ['dobavki.ua', 'belok.ua', 'proteinplus.pro'])
      ->whereNotIn('p.supplier_id', [22, 10])
      ->where('p.images', '!=', null)
      ->where('p.is_active', 1)
      ->get();

    // \Log::info('Items - ' . $items->count() . "\n" );
    // self::echoMemoryUsage();

    $array = $items->map(function($item) {

      $description = $item->content;
      $short_desc = strlen($description) > 10? 
          strip_tags( substr($description, strpos($description, "<p"), strpos($description, "</p>")+4) ): '';
  
      $images_array = json_decode($item->images, true);
      $image = isset($images_array[0]['src']) && !empty($images_array[0]['src'])? config('backpack.store.product.image.base_path').$images_array[0]['src']: '';
  
      return new FeedItem([
        'id' => $item->old_id? $item->old_id: $item->id,
        'title' => $item->name,
        'summary' => mb_convert_encoding( $short_desc, 'UTF-8', 'UTF-8' ),
        'description' => $description,
        'authorName' => 'author',
        'quantity_in_stock' => $item->in_stock,
        'presence' => $item->in_stock > 0?  'true': 'false',
        'availability' => $item->in_stock > 0? 'in stock': '0',
        'link' => url($item->slug),
        'vendorCode' => $item->code,
        'price' => $item->price,
        'sale_price' => $item->price,
        'image' => $image,
        'second_image' => 'second_image',
        'brand' => $item->brand ?? null,
        'condition' => 'новый',
        'mpn' => '4234', //код товара для тех у которых нет кода GTIN
        'gtin' => '1234', //для всех товаров, у которых есть код GTIN
        'base_measure' => 'ct', //единица, за которую рассчитывается цена товара
        'google_product_category' => 525, //категория товара в соответствии с классификацией гугл
        'updated' => new \Carbon\Carbon($item->updated_at),
      ]);
    }, $items);

    // dd($array);
    return $array;
	}  
	
}