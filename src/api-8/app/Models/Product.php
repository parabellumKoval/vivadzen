<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

use App\Models\Category;
use App\Models\CategoryFeed;

use App\Models\Bunny;

use Backpack\Store\app\Models\Product as BaseProduct;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

// use Backpack\Store\app\Models\Brand;
use App\Models\Brand;

// REVIEWS
use Backpack\Reviews\app\Traits\Reviewable;

// Traits
use App\Traits\ProductGoogleMerchantsTrait;

ini_set('memory_limit', '2024M');

class Product extends BaseProduct implements Feedable
{
  use Searchable;
  use Reviewable;
  use ProductGoogleMerchantsTrait;

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
   * brand
   *
   * @return void
   */
  public function brand()
  {
    return $this->belongsTo(Brand::class, 'brand_id');
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

  public function aiGenerationHistory()
  {
      return $this->morphMany(AiGenerationHistory::class, 'generatable');
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
	
  
  /**
   * Method getAvailablePropertiesArray
   *
   * @return void
   */
  public function getAvailablePropertiesArray($lang = 'ru') {
    $props = $this->getAvailableProperties();

    if(!$props) {
      return [];
    }

    $data = [];

    foreach($props as $prop) {
      $data[] = [
        'id' => $prop->id,
        'name' => $prop->getTranslation('name', $lang),
        'type' => $prop->type,
        'si' => $prop->si,
        'value' => $prop->values->map(function ($item) use ($lang) {
          return $item->getTranslation('value', $lang);
        })->toArray()
      ];
    }

    return $data;
  }
  
  /**
   * Method getAvailableProperties
   *
   * @return void
   */
  public function getAvailableProperties() {
    // create empty collection
    $attrs = collect();

    // if categories have not been set go out
    if(!$this->categories || !$this->categories->count())
      return;
    
    // 
    foreach($this->categories as $category) {
      
      $category_parent_node = $category->getParentNode();

      foreach($category_parent_node as $category) {
        // Take all active attributes for this category 
        $cat_attrs = $category->attributes()->active()->get();

        // If isset active attributes for this category merge with common list
        if($cat_attrs && $cat_attrs->count()) {
          $attrs = $attrs->merge($cat_attrs);
        }
      }
    }

    return $attrs;
  }
  
  /**
   * Method getCountAvailablePropertiesAttribute
   *
   * @return void
   */
  public function getCountAvailablePropertiesAttribute() {
    $props = $this->getAvailableProperties();

    if(!$props) {
      return 0;
    }

    return $props->count();
  }
  
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
   * getNameAiGeneratedAttribute
   *
   * @return void
   */
  public function getNameAiGeneratedAttribute() {
    return $this->extras['name_ai_generated'] ?? null;
  }

  /**
   * getMerchantAiGeneratedAttribute
   *
   * @return void
   */
  public function getMerchantAiGeneratedAttribute() {
    return $this->extras['is_ai_merchant_content'] ?? null;
  }

  /**
   * getBrandAiGeneratedAttribute
   *
   * @return void
   */
  public function getBrandAiGeneratedAttribute() {
    return $this->extras['brand_ai_generated'] ?? null;
  }

  /**
   * getCategoryAiGeneratedAttribute
   *
   * @return void
   */
  public function getCategoryAiGeneratedAttribute() {
    return $this->extras['category_ai_generated'] ?? null;
  }
  
  /**
   * getIsImagesGeneratedAttribute
   *
   * @return void
   */
  public function getIsImagesGeneratedAttribute() {
    return $this->extras['is_images_generated'] ?? null;
  }
  
  /**
   * getIsAttributesGeneratedAttribute
   *
   * @return void
   */
  public function getIsAttributesGeneratedAttribute() {
    return $this->extras['attributes_ai_generated'] ?? null;
  }

  /**
   * Method setAttributeToProduct
   *
   * @param $attribute_id $attribute_id [explicite description]
   * @param $value $value [explicite description]
   * @param $lang $lang [explicite description]
   *
   * @return void
   */
  public function setAttributeToProduct($attribute_id, $value, $lang = 'ru') {
    $attribute = Attribute::findOrFail($attribute_id);

    if($attribute->type === 'checkbox') {
      $values = explode(';', $value);

      foreach($values as $value) {
        $this->createAttributeProductSelect($attribute, $value, $lang);
      }
    }else if($attribute->type === 'radio') {
      $this->createAttributeProductSelect($attribute, $value, $lang);
    }else if($attribute->type === 'string') {
      $this->createAttributeProductString($attribute, $value, $lang);
    }else if($attribute->type === 'number') {
      $this->createAttributeProductNumber($attribute, $value);
    }
  }
    
  /**
   * Method createAttributeProductNumber
   *
   * @param $attribute $attribute [explicite description]
   * @param $value $value [explicite description]
   *
   * @return void
   */
  public function createAttributeProductNumber($attribute, $value){

    $ap = AttributeProduct::firstOrCreate([
      'product_id' => $this->id,
      'attribute_id' => $attribute->id,
      'value' => $value
    ]);

    return $ap;
  }
  
  /**
   * Method createAttributeProductString
   *
   * @param $attribute $attribute [explicite description]
   * @param $value $value [explicite description]
   * @param $lang $lang [explicite description]
   *
   * @return void
   */
  public function createAttributeProductString($attribute, $value, $lang){

    $ap = AttributeProduct::firstOrNew([
      'product_id' => $this->id,
      'attribute_id' => $attribute->id
    ]);

    $ap->setTranslation('value_trans', $lang, $value);
    $ap->save();

    return $ap;
  }

  /**
   * Method createAttributeProductSelect
   *
   * @param $attribute $attribute [explicite description]
   * @param $value $value [explicite description]
   * @param $lang $lang [explicite description]
   *
   * @return void
   */
  public function createAttributeProductSelect($attribute, $value, $lang){

    $attribute_value = AttributeValue::firstOrCreate([
      'attribute_id' => $attribute->id,
      'value->' . $lang => $value
    ]);

    $ap = AttributeProduct::firstOrCreate([
      'product_id' => $this->id,
      'attribute_id' => $attribute->id,
      'attribute_value_id' => $attribute_value->id
    ]);

    return $ap;
  }

  
  /**
   * Method setAllPropertiesAi
   *
   * @param array $props [explicite description]
   * @param string $lang [explicite description]
   *
   * @return void
   */
  public function setAllPropertiesAi(array $props, string $lang = 'ru') {
    if(empty($props)) {
      return;
    }

    // Fill specs
    if(isset($props['specs']) && is_array($props['specs']) && !empty($props['specs'])) {
      $this->setSpecs($props['specs']);
      $this->save();
    }

    // Fill custom properties
    if(isset($props['custom_attr']) && is_array($props['custom_attr']) && !empty($props['custom_attr'])) {
      $this->setCustomProperties($props['custom_attr'], $lang);
      $this->save();
    }

    // Fill attributes
    if(isset($props['attrs']) && is_array($props['attrs']) && !empty($props['attrs'])) {
      $this->setFilterAttributes($props['attrs'], $lang);
    }
  }
  
  /**
   * Method setFilterAttributes
   *
   * @param array $attrs [explicite description]
   *
   * @return void
   */
  public function setFilterAttributes(array $attrs = null, string $lang = 'ru') {
    if(empty($attrs)) {
      \Log::error("Error setting attributes: empty attributes");
      return;
    }

    foreach($attrs as $attr) {
      // Check if the property is valid
      try {
        $this->checkProperty($attr);
      }catch (\Exception $e) {
        \Log::error("Error attribute validation {$attr['id']}: " . $e->getMessage());
        continue;
      }

      try {
        $this->setAttributeToProduct($attr['id'], $attr['value'], $lang);
      }catch (\Exception $e) {
        \Log::error("Error setting attribute {$prop['id']}: " . $e->getMessage());
        continue;
      }
    }
  }


  /**
   * Method setCustomProperties
   *
   * @param array $props [explicite description]
   *
   * @return void
   */
  public function setCustomProperties(array $props, string $lang = 'ru') {
    $valid_props = [];

    foreach($props as $prop) {
      if(!isset($prop['name']) || !isset($prop['value'])) {
        \Log::error("Invalid custom property: 'name' and 'value' are required.");
        continue;
      }

      if(!is_string($prop['name'])) {
        \Log::error("Invalid custom property: 'name' must be strings.");
        continue;
      }

      if(!is_string($prop['value']) && !is_numeric($prop['value'])) {
        \Log::error("Invalid custom property: 'value' must be a string or number.");
        continue;
      }

      $valid_props[] = [
        'name' => mb_trim($prop['name']),
        'value' => mb_trim($prop['value']),
      ];
    }

    $extras_trans = $this->getTranslation('extras_trans', $lang, false);
    if (!is_array($extras_trans)) {
        $extras_trans = [];
    }
    
    $extras_trans['custom_attrs'] = $valid_props;
    $this->setTranslation('extras_trans', $lang, $extras_trans);
  }
  

  /**
   * Method setSpecs
   *
   * @param array $specs [explicite description]
   *
   * @return void
   */
  public function setSpecs(array $specs) {
    $allowed_keys = ["natural", "vegetarian", "lactose", "gluten", "gmo", "milk"];
    
    $specs = array_filter($specs, function($key) use ($allowed_keys) {
      return in_array($key, $allowed_keys);
    }, ARRAY_FILTER_USE_KEY);
    
    $specs = array_map(function($value) {
      return (bool)$value;
    }, $specs);

    $extras = $this->extras ?? [];
    $extras['specs'] = $specs;
    $this->extras = $extras;
    // $this->attributes['extras'] = json_encode($extras);
  }
  
  /**
   * Method checkProperty
   *
   * @param $prop $prop [explicite description]
   *
   * @return void
   */
  private function checkProperty($prop) {
    if (!is_array($prop)) {
        throw new \InvalidArgumentException("Invalid property: must be an array.");
    }

    if (!isset($prop['id']) || !is_int($prop['id'])) {
      throw new \InvalidArgumentException("Invalid property: 'id' must be an integer.");
    }

    if (!isset($prop['value']) || is_null($prop['value'])) {
        throw new \InvalidArgumentException("Invalid property: 'value' is required.");
    }
  }


  // public function setOldNameAttribute($value) {
  //   $extras_trans = $this->extras_trans;
  //   $extras_trans['o'] = $value;
  //   $this->extras_trans = $extras_trans;
  // }

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
	  
  /**
   * setIsAiMerchantContentAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setIsAiMerchantContentAttribute($value) {
    $extras = $this->extras;
    $extras['is_ai_merchant_content'] = $value;
    $this->extras = $extras;
  }

  /**
   * Method setBrandAiGeneratedAttribute
   *
   * @param $value $value [explicite description]
   *
   * @return void
   */
  public function setBrandAiGeneratedAttribute($value) {
    $extras = $this->extras;
    $extras['brand_ai_generated'] = $value;
    $this->extras = $extras;
  }
  
  /**
   * Method setNameAiGeneratedAttribute
   *
   * @param $value $value [explicite description]
   *
   * @return void
   */
  public function setNameAiGeneratedAttribute($value) {
    $extras = $this->extras;
    $extras['name_ai_generated'] = $value;
    $this->extras = $extras;
  }

  /**
   * Method setCategoryAiGeneratedAttribute
   *
   * @param $value $value [explicite description]
   *
   * @return void
   */
  public function setCategoryAiGeneratedAttribute($value) {
    $extras = $this->extras;
    $extras['category_ai_generated'] = $value;
    $this->extras = $extras;
  }

  /**
   * setIsAiContentAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setIsImagesGeneratedAttribute($value) {
    $extras = $this->extras;
    $extras['is_images_generated'] = $value;
    $this->extras = $extras;
  }

  /**
   * setAttributesAiGeneratedAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setAttributesAiGeneratedAttribute($value) {
    $extras = $this->extras;
    $extras['attributes_ai_generated'] = $value;
    $this->extras = $extras;
  }

  /**
   * setImagesAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setImagesAttribute($values) {
    // $images_array = $this->bunny->storeImages($values, $this->images);
    // $this->attributes['images'] = json_encode($images_array);
    // $enter_images = json_decode($values);
    // \Log::info(print_r($enter_images, true));
    // dd($enter_images);

    if(empty($values)) {
      $this->attributes['images'] = $values;
      return;
    }
    

    $bunny = new Bunny('products');

    $images_array = $bunny->storeImages($values);

    if($images_array !== -1) {
      $this->attributes['images'] = json_encode($images_array);
    }
  }


  public static function getCategoryCacheItemsQuery() {
      $category_query = Category::where('is_active', 1)->get()->map(function ($category) {
          return ['category_slug' => $category->slug];
      });

      return $category_query;
  }

  public static function getBrandCacheItemsQuery() {
      $brand_query = Brand::where('is_active', 1)->get()->map(function ($category) {
          return ['brand_slug' => $category->slug];
      });
      
      return $brand_query;
  }

}