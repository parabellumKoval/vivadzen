<?php

namespace App\Models;

use Laravel\Scout\Searchable;

use Backpack\Store\app\Models\Category as BaseCategory;

class Category extends BaseCategory
{
  use Searchable;

  public $children_list = [];

  public function addChildrenList($category) {
    $this->children_list[] = $category;
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