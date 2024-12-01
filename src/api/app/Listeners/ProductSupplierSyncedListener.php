<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use \Cviebrock\EloquentSluggable\Services\SlugService;

use App\Models\Product;
use Backpack\Store\app\Events\ProductSupplierSynced;

class ProductSupplierSyncedListener
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
    public function handle(ProductSupplierSynced $event)
    {
      if($event->product->shouldBeSearchable()) {
        Product::where('id', $event->product->id)->searchable();
      }else {
        Product::where('id', $event->product->id)->unsearchable();
      }
    }
}
