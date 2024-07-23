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
    protected $signature = 'db:copy-category-product';

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
      // $this->setAllDefaultCategoryProductDisabled();

        $this->updateCategoryProduct();
        return 0;
    }
    
    /**
     * setAllDefaultCategoryProductDisabled
     *
     * @return void
     */
    private function setAllDefaultCategoryProductDisabled() {
      $default_category = NewCategory::find(476);
      $products = $default_category->products()->cursor();

      foreach($products as $product) {
        $product->is_active = 0;
        $product->save();
        $product->categories()->detach(476);
      }
    }
        
    /**
     * updateCategoryProduct
     *
     * @return void
     */
    public function updateCategoryProduct() {
      $default_category_id = 546;

      $category_product = \DB::table('category_product')
        ->select('category_product.*')
        ->where('category_product.category_id', '!=', $default_category_id)
        ->get();

      // dd($category_product->count());

      $bar = $this->output->createProgressBar(count($category_product));
      $bar->start();
  
      foreach($category_product as $cp) {
        $product = NewProduct::where('old_id', $cp->product_id)->first();
        $category = NewCategory::where('old_id2', $cp->category_id)->first();
  
        if($product && $category) {

          $product->is_active = 1;
          $product->save();

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
