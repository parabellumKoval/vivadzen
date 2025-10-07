<?php

namespace App\Models;

use Backpack\Store\app\Models\Product as BaseProduct;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

use Backpack\Store\app\Models\Supplier;
use Backpack\Store\app\Models\SupplierProduct;

// use Backpack\Store\app\Models\Brand;

use App\Models\Category;
use App\Models\Brand;

ini_set('memory_limit', '2024M');

class StoreProduct extends BaseProduct
{


    public function getForeignKey()
    {
        return 'product_id';
    }
  /*
  |--------------------------------------------------------------------------
  | GLOBAL VARIABLES
  |--------------------------------------------------------------------------
  */
  
  protected $translatable = ['name', 'short_name', 'content', 'excerpt', 'merchant_content', 'extras_trans', 'seo'];

  public function getMorphClass()
  {
      return 'Backpack\Store\app\Models\Product';
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */

  // public function suppliers()
  // {
  //     return $this->belongsToMany(
  //         Supplier::class,
  //         'ak_supplier_product',
  //         'product_id',
  //         'supplier_id'
  //     )
  //     ->withTimestamps()
  //     ->withPivot('in_stock', 'barcode', 'code', 'price', 'old_price', 'updated_at');
  // }

  /*
  |--------------------------------------------------------------------------
  | METHODS
  |--------------------------------------------------------------------------
  */

  public function setProductSupplier($supplier_id, $in_stock = 0, $price = null, $old_price = null, $code = null, $barcode = null, ) {
    
    $pivotData[$supplier_id] = [
      'in_stock' => $in_stock,
      'code' => $code,
      'barcode' => $barcode,
      'price' => $price,
      'old_price' => $old_price
    ];

    $this->suppliers()->attach($pivotData);
  }

  
  /**
   * setImages
   *
   * @param  mixed $value
   * @return void
   */
  public function setImages($values) {
    if(empty($values) || !is_array($values)) {
      $this->images = null;
      return;
    }
    
    $this->images = array_map(function($item) {
      return [
        'src' => $item,
        'alt' => null,
        'title' => null,
      ];
    }, $values);
 
  }
  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
  

    
  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */





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


}