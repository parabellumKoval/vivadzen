<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;

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
    protected $description = 'Update products from belok.ua pricelist (XML)';


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
      $categories_cursor = $categories->cursor();
      $categories_count = $categories->count();

      $bar = $this->output->createProgressBar($categories_count);
      $bar->start();

      $category_controller = new \App\Http\Controllers\Api\CategoryController;

      foreach($categories_cursor as $category) {
        $all_data = $category_controller->catalogData(null, $category, $category->slug);
        Cache::put('category-data-' . $category->slug, $all_data);
        $bar->advance();
      }

      $bar->finish();
    }
    
}
