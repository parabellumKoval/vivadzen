<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Backpack\Store\app\Models\Brand as BaseBrand;

class Brand extends BaseBrand
{
  use Searchable;

  public function toSearchableArray()
  {
      $array = [
        'name' => $this->name
      ];

      return $array;
  }
}