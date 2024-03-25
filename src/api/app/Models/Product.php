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
        'name' => $this->name
      ];

      // Customize the data array...

      return $array;
  }
}