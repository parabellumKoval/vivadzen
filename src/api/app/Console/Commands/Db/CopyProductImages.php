<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class CopyProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:copy-product-images';

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
        $this->copyImages();
        return 0;
    }

    public function copyImages() {
      $products_images = \DB::table('products_images')
        ->select('products_images.*')
        ->get();

      $bar = $this->output->createProgressBar(count($products_images));
      $bar->start();
  
      foreach($products_images as $item) {
        $product = Product::where('old_id', $item->item_id)->first();
  
        if(!$product)
          continue;

        $images_array = $product->images;
        
        if(!is_array($images_array)){
          $images_array = array();
        }

        $images_array[] = [
          'src' => $item->name,
          'alt' => null,
          'title' => null
        ];

        $product->images = $images_array;
        $product->save();
        
        $bar->advance();
      }

      $bar->finish();
    }
}