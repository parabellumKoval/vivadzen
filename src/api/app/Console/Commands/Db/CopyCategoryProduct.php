<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product as NewProduct;
use Backpack\Store\app\Models\Category as NewCategory;

class CopyCategoryProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:category-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $this->updateCategoryProduct();
        return 0;
    }

    public function updateCategoryProduct() {
      $category_product = \DB::table('category_product')
        ->select('category_product.*')
        ->get();

      $bar = $this->output->createProgressBar(count($category_product));
      $bar->start();
  
      foreach($category_product as $cp) {
        $product = NewProduct::where('old_id', $cp->product_id)->first();
        $category = NewCategory::where('old_id', $cp->category_id)->first();
  
        if($product && $category) {
          \DB::table('ak_category_product')
            ->insert([
              'category_id' => $category->id,
              'product_id' => $product->id
            ]);
        }else {
          if(!$product) {
            $this->line('no product');
          }
  
          if(!$category) {
            $this->line('no category');
          }
        }

        $bar->advance();
      }

      $bar->finish();
    }
}
