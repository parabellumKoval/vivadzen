<?php

namespace App\Models;

// use Laravel\Scout\Searchable;
use App\Models\Bunny;

use App\Models\Region;
use Backpack\Store\app\Models\Category as BaseCategory;

use Illuminate\Database\Eloquent\Builder;

use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;

class Category extends BaseCategory implements TranslatableInterface
{
  use TranslatableTrait;

  protected $translatable = ['name', 'content', 'seo', 'extras_trans', 'extras_trans->caption', 'extras_trans->full_description', 'extras_trans->short_description'];

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
            // Устанавливаем slug явно из имени категории
            $category->slug = \Str::slug($categoryName);
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



  // TRANSLATOR
  public static function setupTranslatableSettings(): void
  {
      static::addCommonSettings([
          'title' => 'Категории',
          'key' => 'category',
          'identifier' => 'name',
          'backpack_title' => 'Категории',
          'backpack_settings' => [
              [
                  'type' => 'checkbox',
                  'name' => 'is_active_only',
                  'label' => 'Только активные категории',
                  'hint' => ''
              ]
          ],
          // 'translation_type' => 'auto',
          // 'languages' => ['ru', 'uk']
      ]);

      static::addTranslatableCase([
          'fields' => [
              'name' => 'Название категории'
          ],
          'driver' => 'deepl',
          // 'scopeName' => 'namesQuery'
      ]);

      static::addTranslatableCase([
          'fields' => [
              'extras_trans->caption' => 'Промо заголовок',
              'extras_trans->full_description' => 'Полное описание',
              'extras_trans->short_description' => 'Короткое описание',
          ],
          'driver' => 'deepl',
          'scopeName' => 'extrasTransQuery'
      ]);
  }


  public function scopeTranslatableQuery(Builder $query, $settings) {
    $is_active_only = isset($settings['query']['is_active_only']) 
        ? (bool)$settings['query']['is_active_only'] 
        : false;

    $query->when($is_active_only, function ($query) {
        $query->where('is_active', 1);
    });
  }

  public function scopeExtrasTransQuery(Builder $query, $settings) {
    $query->whereNotNull('extras_trans');
  }


  public function getBackpackEditLinkAttribute(): string 
  {
      return backpack_url('category/' . $this->id . '/edit');
  }

  // public function scopeNamesQuery(Builder $query, $settings) {
  //     $from_langs = $settings['from_languages'];
  //     $min_symbols = 10;
      
  //     $query->where(function ($query) use ($from_langs, $min_symbols) {
  //         $conditions = [];
  //         foreach ($from_langs as $lang_key) {
  //             $json_key = str_replace('.', '\\.', $lang_key);
  //             $conditions[] = '(COALESCE(CHAR_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . $json_key . '"))) >= ' . $min_symbols . ', 0))';
  //         }
  //         $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
  //     });
  // }
}