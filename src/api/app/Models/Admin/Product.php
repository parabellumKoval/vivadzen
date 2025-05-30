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
    // 'is_images_generated_virtual',
    // 'is_ai_content_virtual',
    'brand_ai_generated',
    'brand_ai_generated_moderated',
    'category_ai_generated_moderated',
    'category_ai_generated',
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

    public function getCategoryLinksAdminAttribute() {
      if(!$this->categories || !$this->categories->count())
        return '-';
        
      $cat_links = $this->categories->map(function($item) {
        return "<a href='/admin/product?category={$item->id}'>{$item->name}</a>";
      });

      return implode(', ', $cat_links->toArray());
    }


    public function getBrandLinkAdminAttribute() {
      if(!$this->brand)
        return '-';
        
        return "<a href='/admin/product?brand={$this->brand->id}'>{$this->brand->name}</a>";
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
     * getIsAiContentAttribute
     *
     * @return void
     */
    public function getIsAiContentAttribute() {
      return $this->extras['is_ai_content'] ?? null;
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
     * Method getSpecsAttribute
     *
     * @return void
     */
    public function getSpecsAttribute() {
      return $this->extras['specs'] ?? null;
    }

    public function getIsAnyAiAttribute() {
      return $this->isAiContent 
        || $this->isImagesGenerated 
        || $this->isAttributesGenerated 
        || $this->brandAiGenerated 
        || $this->categoryAiGenerated
        || $this->nameAiGenerated
        || $this->merchantAiGenerated;
    }

    // public function getIsImagesGeneratedVirtualAttribute() {
    //   return $this->extras['is_images_generated'] ?? null;
    // }
    

    
    /**
     * Method getAdminPropsAttribute
     *
     * @return void
     */
    public function getAdminPropsAttribute() {
        return view('admin.product.props', [
            'specs' => $this->specs,
            'customProperties' => $this->customProperties,
            'properties' => $this->properties,
            'countAvailableProperties' => $this->countAvailableProperties
        ])->render();
    }

    /**
     * Method getAdminNameAttribute
     *
     * @return void
     */
    public function getAdminNameAttribute() {
        return view('admin.product.name', [
            'needModeration' => $this->needModeration,
            // moderated
            // 'aiContentModerated' => $this->aiContentModerated,
            // 'aiAttributesModerated' => $this->aiAttributesModerated,
            // 'aiBrandModerated' => $this->aiBrandModerated,
            // 'aiCategoryModerated' => $this->aiCategoryModerated,
            // 'aiImagesModerated' => $this->aiImagesModerated,
            // common
            'isTrans' => $this->is_trans,
            'isAnyAi' => $this->isAnyAi,
            'isImagesGenerated' => $this->isImagesGenerated,
            // product
            'name' => $this->name,
            'brand' => $this->brand,
            'category' => $this->category,
            'brandLinkAdmin' => $this->brandLinkAdmin,
            'categoryLinksAdmin' => $this->categoryLinksAdmin
        ])->render();
    }

    public function getNeedModerationAttribute() {
      return !$this->aiBrandModerated 
        || !$this->aiCategoryModerated 
        || !$this->aiContentModerated 
        || !$this->aiImagesModerated 
        || !$this->aiAttributesModerated 
        || !$this->aiNameModerated 
        || !$this->aiMerchantModerated;
    }

    public function getAiNameModeratedAttribute() {
      return $this->nameAiGenerated? ($this->extras['name_ai_moderated'] ?? false): true;
    }
    public function getAiMerchantModeratedAttribute() {
      return $this->merchantAiGenerated? ($this->extras['ai_merchant_content_moderated'] ?? false): true;
    }
    public function getAiBrandModeratedAttribute() {
      return $this->brandAiGenerated? ($this->extras['brand_ai_generated_moderated'] ?? false): true;
    }
    public function getAiCategoryModeratedAttribute() {
      return $this->categoryAiGenerated? ($this->extras['category_ai_generated_moderated'] ?? false): true;
    }
    public function getAiContentModeratedAttribute() {
      return $this->isAiContent? ($this->extras['ai_content_moderated'] ?? false): true;
    }
    public function getAiImagesModeratedAttribute() {
      return $this->isImagesGenerated? ($this->extras['images_moderated'] ?? false): true;
    }
    public function getAiAttributesModeratedAttribute() {
      return $this->isAttributesGenerated? ($this->extras['attributes_ai_moderated'] ?? false): true;
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
    //   // $old_extras['specs'] = Request::input('specs', []);
    //   $this->extras = $old_extras;
    // }

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
        
    /**
     * Method setIsAiContentVirtualAttribute
     *
     * @param $value $value [explicite description]
     *
     * @return void
     */
    // public function setIsAiContentVirtualAttribute($value) {
    //   $old_extras = $this->extras ?? [];
    //   $old_extras['is_ai_content'] = $value;
    //   $this->extras = $old_extras;
    // }
    
    /**
     * Method setIsImagesGeneratedVirtualAttribute
     *
     * @param $value $value [explicite description]
     *
     * @return void
     */
    // public function setIsImagesGeneratedVirtualAttribute($value) {
    //   $old_extras = $this->extras ?? [];
    //   $old_extras['is_images_generated'] = $value;
    //   $this->extras = $old_extras;
    // }

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