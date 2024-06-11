<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Backpack\Store\app\Models\Product as BaseProduct;

// REVIEWS
use Backpack\Reviews\app\Traits\Reviewable;

class Product extends BaseProduct
{
  use Searchable;
  use Reviewable;

  public function getMorphClass()
  {
      return 'Backpack\Store\app\Models\Product';
  }

  public function toSearchableArray()
  {
      $array = [
        'name' => $this->name,
      ];

      return $array;
  }

  public function shouldBeSearchable()
  {
      return $this->active()->inStock();
  }

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */

  /**
   * getImageSrcAttribute
   *
   * Get src url address from getImageAttribute method
   * 
   * @return string|null string is image src url
   */
  public function getImageSrcAttribute() {
    return '/public/images/products/' . $this->image['src'] ?? null;
  }
}