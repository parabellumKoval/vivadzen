<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class Prompt extends Model
{
  use CrudTrait;
  
  /*
  |--------------------------------------------------------------------------
  | GLOBAL VARIABLES
  |--------------------------------------------------------------------------
  */

  protected $table = 'prompts';
  // protected $primaryKey = 'id';
  public $timestamps = false;
  protected $guarded = ['id'];
  // protected $fillable = ['name', 'content', 'categories', 'is_active'];
  // protected $hidden = [];
  // protected $dates = [];

  protected $casts = [];

  protected $fakeColumns = [];

  /*
  |--------------------------------------------------------------------------
  | FUNCTIONS
  |--------------------------------------------------------------------------
  */
  

  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */
  public function categories()
  {
    return $this->belongsToMany(Category::class, 'category_prompt');
  }

  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */
  
}