<?php

namespace App\Models;

// use Laravel\Scout\Searchable;
use App\Models\Bunny;

use App\Models\Region;
use Backpack\Store\app\Models\Category as BaseCategory;

class Category extends BaseCategory
{
  // use Searchable;

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
  
public static function createOrUpdateCategoryChain(array $chain, string $locale)
{
    $parentId = null;
    $category = null;

    foreach ($chain as $categoryName) {
        // ищем по JSON ключу name->locale
        $category = self::where('parent_id', $parentId)
            ->where("name->{$locale}", $categoryName)
            ->first();

        if (!$category) {
            $category = new self();
            $category->parent_id = $parentId;
            $category->setTranslation('name', $locale, $categoryName);
            $category->save();
        } else {
            if (!$category->hasTranslation('name', $locale)) {
                $category->setTranslation('name', $locale, $categoryName);
                $category->save();
            }
        }
        $parentId = $category->id;
    }

    return $category;
}

  /**
   * Method getAvailableCategoriesArray
   *
   * @param $lang $lang [explicite description]
   *
   * @return array
   */
  static function getAvailableCategoriesArray($lang = 'ru'): array {
    $categories = self::where('is_active', 1)->get();
    $result = [];

    foreach ($categories as $category) {
        $result[] = [
            'id' => $category->id,
            'name' => $category->getTranslation('name', $lang, false),
            'parent_id' => $category->parent_id,
        ];
    }

    return $result;

  }
  

  /**
   * Method getHasNotMerchantCategoriesArray
   *
   * @param $lang $lang [explicite description]
   *
   * @return array
   */
  static function getHasNotMerchantCategoriesArray($lang = 'ru'): array {
    $categories = self::where('is_active', 1)->where('merchant_id', null)->get();
    $result = [];

    foreach ($categories as $category) {
        $result[] = [
            'id' => $category->id,
            'name' => $category->getTranslation('name', $lang, false),
            'parent_id' => $category->parent_id,
        ];
    }

    return $result;

  }

  /**
   * getNoMedicineAttribute
   *
   * @return void
   */
  public function getNoMedicineAttribute() {
    return $this->extras['no_medicine'] ?? 1;
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */
  public function regions()
  {
    return $this->hasMany(Region::class, 'category_id');
  }


  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
  
  /**
   * getIsAiContentAttribute
   *
   * @return void
   */
  public function getIsAiContentAttribute() {
    return $this->extras['is_ai_content'] ?? null;
  }

  
  /**
   * getIsImagesGeneratedAttribute
   *
   * @return void
   */
  public function getIsImagesGeneratedAttribute() {
    return $this->extras['is_images_generated'] ?? null;
  }

  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */

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
   * setImagesAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setImagesAttribute($values) {
    $images_array = $this->bunny->storeImages($values, $this->images);

    if($images_array !== -1) {
      $this->attributes['images'] = json_encode($images_array);
    }
  }
}