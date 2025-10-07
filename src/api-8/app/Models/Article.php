<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Bunny;

use Backpack\Articles\app\Models\Article as ArticleBrand;

class Article extends ArticleBrand
{

  private $bunny = null;

  // protected $fakeColumns = ['meta_description', 'meta_title', 'extras_trans', 'seo', 'extras'];
  // protected $translatable = ['excerpt', 'content', 'extras_trans', 'seo'];
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
    $this->bunny = new Bunny('blog');  
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

    if($images_array !== -1) {
      $this->attributes['images'] = json_encode($images_array);
    }
  }
}