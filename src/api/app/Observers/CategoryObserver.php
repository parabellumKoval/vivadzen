<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Bunny;

class CategoryObserver
{
    /**
     * Handle the Category "deleting" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleting(Category $category) {
      if(empty($category->images)) {
        return;
      }
      
      $bunny = new Bunny('categories');
      $bunny->removeAllImages($category->images);
    }
}
