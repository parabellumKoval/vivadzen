<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

use App\Models\Category;
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
        'code' => $this->simpleCode,
        'brand' => $this->brand? $this->brand->name: null,
        'price' => $this->simplePrice,
        'ru' => [
          'name' => $this->getTranslation('name', 'ru', false),
          'category' => null,
        ],
        'uk' => [
          'name' => $this->getTranslation('name', 'uk', false),
          'category' => null,
        ]
      ];

      // add category
      if($this->category) {
        $array['ru']['category'] = $this->category->getTranslation('name', 'ru', false);
        $array['uk']['category'] = $this->category->getTranslation('name', 'uk', false);
      }

      return $array;
  }
  
  /**
   * shouldBeSearchable
   *
   * @return void
   */
  public function shouldBeSearchable()
  {
    // return $this->active()->whereHas('sp', function($query)  {
    //   return $query->where('in_stock', '>', 0);
    // });

    // return $this->active();
    return $this->is_active && $this->simpleInStock;
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */
   
  /**
   * categories
   *
   * @return void
   */
  public function categories()
  {
    return $this->belongsToMany(Category::class, 'ak_category_product');
  }

  /**
   * prom categories
   *
   * @return void
   */
  public function prom_category()
  {
    return $this->belongsTo(CategoryFeed::class, 'category_feed_id');
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
   * getImageSrcsAttribute
   *
   * @return void
   */
  public function getImageSrcsAttribute() {
    $base_path = config('backpack.store.product.image.base_path', '/');
    $data = [];

    if(!$this->images) {
      return [];
    }

    foreach($this->images as $image) {
      if(isset($image['src']) && !empty($image['src'])) {
        $data[] = $base_path . $image['src'];
      }
    }
    
    return $data;
  }
  
	
	/**
	 * getAllAttributesAttribute
	 *
	 * @return void
	 */
	public function getAllAttributesAttribute() {
    $data = [];


    if($this->properties) {
      foreach($this->properties as $property) {
        $value = !empty($property['value'])? $property['value']: null;


        if(is_array($property['value'])) {
          $raw_values = array_filter($property['value']);

          if($raw_values) {
            $values = [];

            foreach($raw_values as $value) {
              $values[] = $value['value'];
            }

            $result_value = implode(' | ', $values);
          }else {
            $result_value = null;
          }
        }else {
          $result_value = $value;
        }

        if(!empty($result_value)) {
          $data[] = [
            'name' => $property['name'],
            'si' => $property['si'],
            'value' => $result_value,
          ];
        }
      }
    }


    if($this->customProperties) {
      foreach($this->customProperties as $cp) {
        $data[] = $cp;
      }
    }

    return $data;
  }
	

  // public function getPromCategoryAttribute() {

  // }
  /**
   * mergeProductData
   *
   * @param  mixed $groups
   * @param  mixed $prom_groups
   * @return void
   */
  // private static function mergeProductData($groups, $prom_groups) {

  //   // Get values
  //   $groups = $groups->values()->toArray();

  //   $products = collect();
  //   for($i = 0; $i < count($groups); $i++){
  //     $product = array_reduce($groups[$i], function($carry, $item) use ($prom_groups){
        
  //       if(empty($carry)) {
  //         // Get Images Array
  //         $image_names_array = !empty($item->images)? json_decode($item->images): [];
  
  //         if(!empty($image_names_array)) {
  //           $image_urls = array_map(function($filename){
  //             return config('backpack.store.product.image.base_path') . $filename->src;
  //           }, $image_names_array);
  //         }else {
  //           $image_urls = [];
  //         }

  //         // Get prom category
  //         $promCategoryId = $prom_groups[(int)$item->categoryId]->prom_id ?? null;

  //          // code, barcode
  //         if(!empty($item->simpleCode)) {
  //           $code = $item->simpleCode;
  //         }else if(!empty($item->simpleBarcode)) {
  //           $code = $item->simpleBarcode;
  //         }else {
  //           $code = null;
  //         }

  //         $product = new FeedItem([
  //           'id' => $item->old_id? $item->old_id: $item->id,
  //           'title' => !empty($item->name_ru)? $item->name_ru: '',
  //           'title_uk' => $item->name_uk,
  //           'link' => $item->slug,
  //           'vendorCode' => $code,
  //           'summary' => !empty($item->content_ru)? $item->content_ru: '',
  //           'summary_uk' => !empty($item->content_uk)? $item->content_uk: '',
  //           'images' => $image_urls,
  //           'vendor' => $item->brand, // brand
  //           'inStock' => $item->simpleInStock,
  //           'price' => $item->simplePrice,
  //           'oldprice' => $item->simpleOldPrice,
  //           'attributes' => [],
  //           'promCategoryId' => $promCategoryId,
  //           'presence' => $item->simpleInStock > 0?  'true': 'false',
  //           'mpn' => '4234',
  //           'updated' => \Carbon\Carbon::parse($item->updated_at),
  //           'authorName' => 'Djini'
  //         ]);
  //       }else {
  //         $product = $carry;
  //       }

  //       // Fill params
  //       if(isset($product->attributes[$item->a_id])) {
  //         $product->attributes[$item->a_id]['value'] = $product->attributes[$item->a_id]['value'] . '|' . $item->av_value;
  //       }else {
  //         $product->attributes[$item->a_id] = [
  //           'name' => $item->a_name ?? '',
  //           'si' => $item->a_si ?? '',
  //           'value' => $item->av_value ?? '',
  //         ];
  //       }
 
  //       return $product;
  //     }, []);

  //     $products->push($product);
  //   }

  //   return $products;
  // }
  
  /**
   * getPromProducts
   *
   * @param  mixed $products
   * @param  mixed $prom_categories
   * @return void
   */
  public static function getPromProducts($products, $prom_groups) {
    $output_data = collect();

    foreach($products as $product) {
      // Get prom category
      if($product->prom_category) {
        $promCategoryId = $product->prom_category->prom_id;
      }

      if(empty($promCategoryId)) {
        if($product->category) {
          $promCategoryId = $prom_groups[(int)$product->category->id]->prom_id ?? null;
        }else {
          $promCategoryId = null;
        }
      }
  
      $product = new FeedItem([
        'id' => $product->old_id? $product->old_id: $product->id,
        'title' => $product->getTranslation('name', 'ru', false),
        'title_uk' => $product->getTranslation('name', 'uk', false),
        'link' => $product->slug,
        'vendorCode' => $product->simpleCode,
        'summary' => $product->getTranslation('content', 'ru', false),
        'summary_uk' => $product->getTranslation('content', 'uk', false),
        'images' => $product->imageSrcs,
        'vendor' => $product->brand? $product->brand->name: null,
        'inStock' => $product->simpleInStock,
        'price' => $product->simplePrice,
        'oldprice' => $product->simpleOldPrice,
        'attributes' => $product->allAttributes,
        'promCategoryId' => $promCategoryId,
        'presence' => $product->simpleInStock > 0?  'true': 'false',
        'mpn' => '4234',
        'updated' => $product->updated_at,
        'authorName' => 'Djini'
      ]);

      $output_data->push($product);
    }

    return $output_data;
  }
	
	/**
	 * getPromFeedItems
	 *
	 * @return void
	 */
	public static function getPromFeedItems()
	{
  
    $items = self::
        without(['orders', 'children', 'parent', 'suppliers'])
        ->whereHas('sp', function($query){
          $query->where('in_stock', '>', 0);
        })
        ->where('is_active', 1)
        ->where('images', '!=', null)
        // ->whereIn('id', [4478])
        // ->limit(100)
        ->cursor();

    $categories = self::getPromCategories();
    $products = self::getPromProducts($items, $categories->keyBy('category_id'));

    $collection = collect([
      'products' => self::getFakeFeedItem($products),
      'categories' => self::getFakeFeedItem($categories)
    ]);

    return $collection;
  }

	/**
	 * getPromFeedItems
	 *
	 * @return void
	 */
	// public static function getPromFeedItems()
	// {

  //   $sps = \DB::table('ak_supplier_product as sp')
  //             ->select('sp.id', 'sp.price', 'sp.old_price', 'sp.in_stock', 'sp.code', 'sp.barcode', 'sp.product_id')
  //             ->where('sp.in_stock', '>', 0)
  //             ->orderByRaw('IF(sp.in_stock > ?, ?, ?) DESC', [0, 1, 0])
  //             ->orderBy('sp.price');


  //   $items = \DB::table('ak_products as p')
  //     ->select([
  //       'sp.id as spId',
  //       'p.id',
  //       'p.old_id',
  //       'p.name->ru as name_ru',
  //       'p.name->uk as name_uk',
  //       'p.slug',
  //       'p.code',
  //       'p.content->ru as content_ru',
  //       'p.content->uk as content_uk',
  //       'p.images',
  //       'sp.in_stock as simpleInStock',
  //       'sp.price as simplePrice',
  //       'sp.old_price as simpleOldPrice',
  //       'sp.code as simpleCode',
  //       'sp.barcode as simpleBarcode',
  //       'p.updated_at',
  //       'b.name->ru as brand',
  //       'a.id as a_id',
  //       'a.name->ru as a_name',
  //       'a.extras_trans->ru->si as a_si',
  //       'av.value->ru as av_value',
  //       'ap.value as ap_value',
  //       'ap.value_trans->ru as ap_value_trans',
  //       'cp.category_id as categoryId',
  //     ])
  //     ->join('ak_category_product as cp', 'cp.product_id', '=', 'p.id')
  //     // ->join('category_feed as cf', 'cf.category_id', '=', 'cp.category_id')
  //     ->join('ak_brands as b', 'p.brand_id', '=', 'b.id')
  //     ->joinSub($sps, 'sp', function ($join) {
  //       $join->on('p.id', '=', 'sp.product_id');
  //     })
  //     ->join('ak_attribute_product as ap', 'p.id', '=', 'ap.product_id')
  //     ->join('ak_attributes as a', 'a.id', '=', 'ap.attribute_id')
  //     ->join('ak_attribute_values as av', 'av.id', '=', 'ap.attribute_value_id')
  //     ->where('p.images', '!=', null)
  //     ->where('p.is_active', 1)
  //     ->groupBy('sp.id', 'a.id', 'av.id', 'ap.id', 'cp.id')
  //     ->limit(100)
  //     ->get()
  //     ->groupBy('id');

  //   // <categoryId><![CDATA[{{ 110341818 }}]]></categoryId>
  //   // <categoryId>72879515</categoryId>
  //   // <categoryName>БАДы</categoryName>

  //   $categories = self::getPromCategories();
  //   $products = self::mergeProductData($items, $categories->keyBy('category_id'));

  //   $collection = collect([
  //     'products' => self::getFakeFeedItem($products),
  //     'categories' => self::getFakeFeedItem($categories)
  //   ]);

  //   return $collection;
	// }
  
  public function toFeedItem(): FeedItem {
    return [];
  }

  public static function getFeedItems() {
    return [];
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
    })->get();
    
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
    
  /**
   * getIsAiContentAttribute
   *
   * @return void
   */
  public function getIsAiContentAttribute() {
    return $this->extras['is_ai_content'] ?? null;
  }

  /**
   * setIsAiContentAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setIsAiContentAttribute($value) {
    $extras = $this->extras;
    $extras['is_ai_content'] = $value;
    $this->extras = $extras;
  }
	
}