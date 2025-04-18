<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Bunny;

use Backpack\Store\app\Models\Brand as BaseBrand;

class Brand extends BaseBrand
{
  use Searchable;

  private $bunny = null;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
    $this->bunny = new Bunny('brands');  
  }
  
  /**
   * toSearchableArray
   *
   * @return void
   */
  public function toSearchableArray()
  {
      $array = [
        'name' => $this->name
      ];

      return $array;
  }

  /**
   * Method getAvailableBrandsArray
   *
   * @return array
   */
  static function getAvailableBrandsArray() {
    $brands = [];
    $brands_list = Brand::where('is_active', 1)->select('id', 'name')->get();

    foreach ($brands_list as $brand) {
      if(empty($brand->name)) {
        continue;
      }
      
      $brands[] = [
        'id' => $brand->id,
        'name' => $brand->name,
      ];
    }
    return $brands;
  }
  
  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
  
  /**
   * Method getWebsiteAttribute
   *
   * @return void
   */
  public function getWebsiteAttribute() {
    return $this->extras['website'] ?? null;
  }
  
  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */
  
  /**
   * setImagesAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setImagesAttribute($values) {
    $images_array = $this->bunny->storeImages($values, $this->images);
    $this->attributes['images'] = json_encode($images_array);
  }
  
  /**
   * Method setWebsiteAttribute
   *
   * @param $value $value [explicite description]
   *
   * @return void
   */
  public function setWebsiteAttribute($value) {
    $extras = $this->extras;
    $extras['website'] = $value;
    $this->extras = $extras;
  }
}