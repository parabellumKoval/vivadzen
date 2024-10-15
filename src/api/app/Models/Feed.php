<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

use App\Models\CategoryFeed;

class Feed extends Model
{
  use CrudTrait;

  /*
  |--------------------------------------------------------------------------
  | GLOBAL VARIABLES
  |--------------------------------------------------------------------------
  */

  protected $table = 'feeds';
  // protected $primaryKey = 'id';
  // public $timestamps = false;
  protected $guarded = ['id'];
  protected $fillable = [
    'name',
    'key',
    'is_active',
    'settings',
    'categoriesData'
  ];

  // protected $hidden = [];
  // protected $dates = [];
  // protected $with = [];
  protected $casts = [
    'settings' => 'array'
  ];

  protected $fakeColumns = ['settings'];
    

  /**
   * categories
   *
   * @return void
   */
  public function categories()
  {
    $category_class = config('backpack.store.category.class', 'Backpack\Store\app\Models\Category');
    return $this->belongsToMany($category_class, 'category_feed')->withPivot('name');
  }

  /**
   * CategorySource
   *
   * @return void
   */
  public function cf()
  {
    return $this->hasMany(CategoryFeed::class);
  }
  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
    
  /**
   * getCategoriesDataAttribute
   *
   * @return void
   */
  public function getCategoriesDataAttribute() {
    $arr = [];

    foreach($this->cf as $cf) {
      $arr[] = [
        'prom_name' => $cf->prom_name,
        'prom_id' => $cf->prom_id,
        'prom_parent_id' => $cf->prom_parent_id,
        'category_id' => $cf->category_id,
      ];
    }

    return $arr;
  }
  

  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */
      
  /**
   * setCategoriesDataAttribute
   *
   * @param  mixed $value
   * @return void
   */
  public function setCategoriesDataAttribute($value) {
    // Dettach all existed 
    $this->cf()->delete();

    $value_arr = json_decode($value, true);

    if(!$value_arr) {
      return;
    }

    foreach($value_arr as $item) {
      CategoryFeed::create([
        'category_id' => $item['category_id'] ?? null,
        'feed_id' => $this->id,
        'prom_name' => $item['prom_name'],
        'prom_id' => $item['prom_id'],
        'prom_parent_id' => $item['prom_parent_id']
      ]);
    } 
  }
}