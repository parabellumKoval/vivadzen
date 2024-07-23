<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Bunny;

use Backpack\Store\app\Models\Category as BaseCategory;

class Category extends BaseCategory
{
  use Searchable;

  public $children_list = [];
  private $bunny = null;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
    $this->bunny = new Bunny('categories');  
  }
  
  
  /**
   * addChildrenList
   *
   * @param  mixed $category
   * @return void
   */
  public function addChildrenList($category) {
    $this->children_list[] = $category;
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

      // Customize the data array...

      return $array;
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
}