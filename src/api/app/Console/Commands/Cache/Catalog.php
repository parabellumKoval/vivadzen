<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use App\Models\Region;

ini_set('memory_limit', '500M');

class Catalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:catalog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';


    public $client = null;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    $categories = Category::where('is_active', 1);
      $this->cacheData($categories, 'category');

	    $regions = Region::where('is_active', 1);
      $this->cacheData($regions, 'region');
    }
        
    /**
     * cacheData
     *
     * @param  mixed $query
     * @param  mixed $type
     * @return void
     */
    protected function cacheData($query, $type) {
	    $categories = Category::where('is_active', 1);

      $data_cursor = $query->cursor();
      $data_count = $query->count();

      $bar = $this->output->createProgressBar($data_count);
      $bar->start();

      $category_controller = new \App\Http\Controllers\Api\CategoryController;

      foreach($data_cursor as $item) {
        if($type === 'region') {
          $all_data = $category_controller->catalogData(null, null, $item);
        }elseif($type === 'category') {
          $all_data = $category_controller->catalogData(null, $item);
        }

        Cache::put('category-data-' . $item->slug, $all_data);
        $bar->advance();
      }

      $bar->finish();
    }
}
