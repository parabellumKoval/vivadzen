<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use \Cviebrock\EloquentSluggable\Services\SlugService;

use App\Models\Region;
use App\Events\RegionSaving;

class RegionSavingListener
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
    public function handle(RegionSaving $event)
    {
      if(empty($event->region->slug)) {
        $category_name = $event->region->category->name;
        $region_name = $event->region->name;
        $sluggable = $category_name . ' ' . $region_name;
  
        $event->region->slug = SlugService::createSlug(Region::class, 'slug', $sluggable);
      }
    }
}
