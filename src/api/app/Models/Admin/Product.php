<?php

namespace App\Models\Admin;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

use Illuminate\Support\Facades\Log;
use App\Models\Bunny;

use Backpack\Tag\app\Traits\Taggable;
use App\Models\CategoryFeed;

use Backpack\Store\app\Models\Admin\Product as BaseAdminProduct;

class Product extends BaseAdminProduct
{
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
    'category_feed_id'
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
      $this->attributes['images'] = json_encode($images_array);
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