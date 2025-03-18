<?php

namespace App\Models\Admin;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

use Illuminate\Support\Facades\Log;
use App\Models\Bunny;
use Laravel\Scout\Searchable;

use Backpack\Tag\app\Traits\Taggable;
use App\Models\CategoryFeed;

use Backpack\Store\app\Models\Admin\Product as BaseAdminProduct;

class Product extends BaseAdminProduct
{
  // use Searchable;
  use Taggable;
 
  private $bunny = null;
  public $modificationsToSave = [];

  protected $fillable = [
    'code',
    'name',
    'short_name',
    'slug',
    'content',
    'excerpt',
    'images',
    'parent_id',
    'brand_id',
    'price',
    'old_price',
    'in_stock',
    'is_active',
    'seo',
    'extras',
    'extras_trans',
    'specs',
    'specsvirtual',
    'modifications',
    'duplicate_of',
    'duplicate',
    'suppliersData',
    'props',
    'defaultSupplier',
    'defaultSupplierVirtual',
    'category_feed_id',
    'is_images_generated_virtual',
    'is_ai_content_virtual'
  ];

  protected $casts = [
    'extras' => 'array',
    'images' => 'array',
  ];

  protected $fakeColumns = ['meta_description', 'meta_title', 'seo', 'extras_trans', 'extras', 'images'];

  public function __construct() {
    parent::__construct();
    $this->bunny = new Bunny('products');  
  }
  
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
         
    public function getMorphClass()
    {
        return 'Backpack\Store\app\Models\Admin\Product';
    } 
    
    /**
     * toSearchableArray
     *
     * @return void
     */
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
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with('sp');
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

      // dd($this->simpleInStock);
      // return $this->active();
      return $this->is_active && $this->simpleInStock;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * duplicate
     *
     * Return duplicate
     * 
     * @return Product
     */
    public function duplicate()
    {
      return $this->belongsTo(self::class, 'duplicate_of');
    }

    /**
     * categories
     *
     * @return void
     */
    public function prom_category()
    {
      return $this->belongsTo(CategoryFeed::class, 'category_feed_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    
    public function getSpecsAttribute() {
      return $this->extras['specs'] ?? null;
    }

    public function getIsAiContentVirtualAttribute() {
      return $this->extras['is_ai_content'] ?? null;
    }

    public function getIsImagesGeneratedVirtualAttribute() {
      return $this->extras['is_images_generated'] ?? null;
    }
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    
    
    /**
     * setSpecsAttribute
     *
     * @param  mixed $value
     * @return void
     */
    // public function setSpecsAttribute($value) {
    //   $old_extras = $this->extras ?? [];
    //   $old_extras['specs'] = $value;
    //   $this->extras = $old_extras;
    // }


    /**
     * setSpecsAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function setSpecsvirtualAttribute($value) {
      // dd($this->attributes, $this->specs, Request::input('specs'));
      // $specs = Request::input('specs', []);
      // dd($specs);

      $old_extras = $this->extras ?? [];
      $old_extras['specs'] = Request::input('specs', []);
      $this->extras = $old_extras;
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
    
    public function setIsAiContentVirtualAttribute($value) {
      $old_extras = $this->extras ?? [];
      $old_extras['is_ai_content'] = $value;
      $this->extras = $old_extras;
    }

    public function setIsImagesGeneratedVirtualAttribute($value) {
      $old_extras = $this->extras ?? [];
      $old_extras['is_images_generated'] = $value;
      $this->extras = $old_extras;
    }

    /**
     * setModificationsAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function setModificationsAttribute($value) {
      $this->modificationsToSave = $value;
    }
}