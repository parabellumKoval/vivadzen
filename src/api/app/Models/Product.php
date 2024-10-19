<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

use App\Models\CategoryFeed;

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
      
  /**
   * getSpecsAttribute
   *
   * @return void
   */
  public function getSpecsAttribute() {
    return $this->extras['specs'] ?? null;
  }
  
  /**
   * getImageSrcAttribute
   *
   * @return void
   */
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
    	
	/**
	 * getFeedItems
	 *
	 * @return void
	 */
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
  
  /**
   * echoMemoryUsage
   *
   * @return void
   */
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
	
  private static function mergeProductData($groups, $prom_groups) {

    // Get values
    $groups = $groups->values()->toArray();

    $products = collect();
    for($i = 0; $i < count($groups); $i++){
      $product = array_reduce($groups[$i], function($carry, $item) use ($prom_groups){
        
        if(empty($carry)) {
          // Get Images Array
          $image_names_array = !empty($item->images)? json_decode($item->images): [];
  
          if(!empty($image_names_array)) {
            $image_urls = array_map(function($filename){
              return config('backpack.store.product.image.base_path') . $filename->src;
            }, $image_names_array);
          }else {
            $image_urls = [];
          }

          // Get prom category
          $promCategoryId = $prom_groups[(int)$item->categoryId]->prom_id ?? null;

          $product = new FeedItem([
            'id' => $item->old_id? $item->old_id: $item->id,
            'title' => !empty($item->name_ru)? $item->name_ru: '',
            'title_uk' => $item->name_uk,
            'link' => $item->slug,
            'vendorCode' => $item->simpleCode ?? $item->simpleBarcode ?? $item->code, // code, barcode
            'summary' => !empty($item->content_ru)? $item->content_ru: '',
            'summary_uk' => !empty($item->content_uk)? $item->content_uk: '',
            'images' => $image_urls,
            'vendor' => $item->brand, // brand
            'inStock' => $item->simpleInStock,
            'price' => $item->simplePrice,
            'oldprice' => $item->simpleOldPrice,
            'attributes' => [],
            'promCategoryId' => $promCategoryId,
            'presence' => $item->simpleInStock > 0?  'true': 'false',
            'mpn' => '4234',
            'updated' => \Carbon\Carbon::parse($item->updated_at),
            'authorName' => 'Djini'
          ]);
        }else {
          $product = $carry;
        }

        // Fill params
        // if(isset($product->attributes[$item->a_id])) {
        //   $product->attributes[$item->a_id]['value'] = $product->attributes[$item->a_id]['value'] . '|' . $item->av_value;
        // }else {
        //   $product->attributes[$item->a_id] = [
        //     'name' => $item->a_name ?? '',
        //     'si' => $item->a_si ?? '',
        //     'value' => $item->av_value ?? '',
        //   ];
        // }
 
        return $product;
      }, []);

      $products->push($product);
    }

    return $products;
  }

	/**
	 * getPromFeedItems
	 *
	 * @return void
	 */
	public static function getPromFeedItems()
	{

    $sps = \DB::table('ak_supplier_product as sp')
              ->select('sp.id', 'sp.price', 'sp.old_price', 'sp.in_stock', 'sp.code', 'sp.barcode', 'sp.product_id')
              ->where('sp.in_stock', '>', 0)
              ->orderByRaw('IF(sp.in_stock > ?, ?, ?) DESC', [0, 1, 0])
              ->orderBy('sp.price');


    $items = \DB::table('ak_products as p')
      ->select([
        'sp.id as spId',
        'p.id',
        'p.old_id',
        'p.name->ru as name_ru',
        'p.name->uk as name_uk',
        'p.slug',
        'p.code',
        'p.content->ru as content_ru',
        'p.content->uk as content_uk',
        'p.images',
        'sp.in_stock as simpleInStock',
        'sp.price as simplePrice',
        'sp.old_price as simpleOldPrice',
        'sp.code as simpleCode',
        'sp.barcode as simpleBarcode',
        'p.updated_at',
        'b.name->ru as brand',
        // 'a.id as a_id',
        // 'a.name->ru as a_name',
        // 'a.extras_trans->ru->si as a_si',
        // 'av.value->ru as av_value',
        // 'ap.value as ap_value',
        // 'ap.value_trans->ru as ap_value_trans',
        'cp.category_id as categoryId',
      ])
      ->join('ak_category_product as cp', 'cp.product_id', '=', 'p.id')
      // ->join('category_feed as cf', 'cf.category_id', '=', 'cp.category_id')
      ->join('ak_brands as b', 'p.brand_id', '=', 'b.id')
      ->joinSub($sps, 'sp', function ($join) {
        $join->on('p.id', '=', 'sp.product_id');
      })
      ->join('ak_attribute_product as ap', 'p.id', '=', 'ap.product_id')
      ->join('ak_attributes as a', 'a.id', '=', 'ap.attribute_id')
      ->join('ak_attribute_values as av', 'av.id', '=', 'ap.attribute_value_id')
      ->where('p.images', '!=', null)
      ->where('p.is_active', 1)
      ->groupBy('sp.id', 'a.id', 'av.id', 'ap.id', 'cp.id')
      ->get()
      ->groupBy('id');

    dd($items->count());

    // <categoryId><![CDATA[{{ 110341818 }}]]></categoryId>
    // <categoryId>72879515</categoryId>
    // <categoryName>БАДы</categoryName>

    $categories = self::getPromCategories();
    $products = self::mergeProductData($items, $categories);

    $collection = collect([
      'products' => self::getFakeFeedItem($products),
      'categories' => self::getFakeFeedItem($categories)
    ]);

    return $collection;
	}
  
  /**
   * getPromCategories
   *
   * @param  mixed $items
   * @return FeedItem
   */
  public static function getPromCategories() {

    // Get prom groups keied by site category id
    $prom_groups = CategoryFeed::whereHas('feed', function($query) {
      $query->where('key', 'prom');
    })->get()->keyBy('category_id');
    
    return $prom_groups;
  }
  
  /**
   * getFakeFeedItem
   *
   * @param  mixed $items
   * @return FeedItem
   */
  public static function getFakeFeedItem($items): FeedItem {
    return FeedItem::create([
      'id' => 1,
      'title' => 'fake',
      'summary' => 'fake',
      'link' => 'fake',
      'items' => $items->all(),
      'updated' => \Carbon\Carbon::now(),
      'authorName' => ''
    ]);
  }
	
}