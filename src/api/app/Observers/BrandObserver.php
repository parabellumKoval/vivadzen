<?php

namespace App\Observers;

use App\Models\Brand;
use App\Models\Bunny;

class BrandObserver
{
    /**
     * Handle the Brand "deleting" event.
     *
     * @param  \App\Models\Brand  $brand
     * @return void
     */
    public function deleting(Brand $brand) {
      if(empty($brand->images)) {
        return;
      }
      
      $bunny = new Bunny('brands');
      $bunny->removeAllImages($brand->images);
    }
}
