<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use \Cviebrock\EloquentSluggable\Services\SlugService;

use Backpack\Store\app\Events\SupplierProductSaved;
use App\Models\Product;

class SupplierProductSavedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SupplierProductSaved $event)
    {
      $product = $event->sp->product;

      if($product->shouldBeSearchable()) {
        Product::where('id', $product->id)->searchable();
      }else {
        Product::where('id', $product->id)->unsearchable();
      }
    }
}
