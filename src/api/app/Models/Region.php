<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

// SLUGS
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

// TRANSLATIONS
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;

use App\Events\RegionSaving;

class Region extends Model
{
    use CrudTrait;
    use Sluggable;
    use SluggableScopeHelpers;
    use HasTranslations;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'regions';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $fakeColumns = ['seo', 'extras', 'extras_trans', 'params'];
    protected $casts = [
	    'params' => 'array',
	    'extras' => 'array',
    ];

    protected $translatable = ['name', 'content', 'seo', 'extras_trans'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
      'saving' => RegionSaving::class,
  ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    

 
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
      return CategoryFactory::new();
    }

    public function toArray(){
      return [
        'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'children' => $this->children
      ];    
    }
    
    public function clearGlobalScopes()
    {
        static::$globalScopes = [];
    }
    
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'slug_or_name',
            ],
        ];
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function category()
    {
      $class_name = config('backpack.store.category.class');
      return $this->belongsTo($class_name, 'category_id');
    }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query){
      return $query->where('is_active', 1);
    }
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */ 

    public function getAdminColumnSeo() {
      $html = "";
      // $html .= "T: <span>🔴</span>";
      // $html .= "D: <span>🟢</span>";
      // $html .= "H1: <span>🔴</span>";

      $common_style = "display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; margin-right: 3px; border-radius: 9px; color: #fff; font-size: 12px; font-weight: bold;";

      // $arr = $this->seoToArray;
      // foreach($arr as $k => $v) {
      //   \Log::info($k . ' - ' . $v);
      // }

      if(!$this->seoToArray || !isset($this->seoToArray['meta_title']) || empty($this->seoToArray['meta_title'])) {
        $html .= "<div style='" . $common_style ." background: red;'>T</div>";
      }else {
        $html .= "<div style='" . $common_style ." background: #00a65a;'>T</div>";
      }

      if(!$this->seoToArray || !isset($this->seoToArray['meta_description']) ||empty($this->seoToArray['meta_description'])) {
        $html .= "<div style='" . $common_style ." background: red;'>D</div>";
      }else {
        $html .= "<div style='" . $common_style ." background: #00a65a;'>D</div>";
      }

      if(!$this->seoToArray || !isset($this->seoToArray['h1']) || empty($this->seoToArray['h1'])) {
        $html .= "<div style='" . $common_style ." background: red;'>H1</div>";
      }else {
        $html .= "<div style='" . $common_style ." background: #00a65a;'>H1</div>";
      }

      return $html;
    }

    /**
     * getSeoToArrayAttribute
     *
     * @return void
     */
    public function getSeoToArrayAttribute() {
      return !empty($this->seo)? json_decode($this->seo, true): null;
    }
    
    /**
     * getExtrasToArrayAttribute
     *
     * @return void
     */
    public function getExtrasToArrayAttribute() {
      return !empty($this->extras)? json_decode($this->extras): null;
    }
    
        
    /**
     * getSlugOrNameAttribute
     *
     * @return void
     */
    // public function getSlugOrNameAttribute()
    // {
    //     if ($this->slug != '') {
    //         return $this->slug;
    //     }

    //     return $this->name;
    // }

    public function getSlugOrNameAttribute()
    {
        if ($this->slug != '') {
            return $this->slug;
        }

        return $this->name;
    }
    
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
