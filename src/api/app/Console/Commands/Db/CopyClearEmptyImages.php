<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class CopyClearEmptyImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:clear-images';

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
      $page = 0;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;
        $products = Product::skip($skip)->take($per_page)->get();
        $this->clearImages($products);
        $page += 1;
      }while($products->count());
      
      return 0;
    }

    public function clearImages($products) {

      $bar = $this->output->createProgressBar(count($products));
      $bar->start();
  
      foreach($products as $product) {
        
        if($product->images && isset($product->images[0]) && isset($product->images[0]['src']) && !empty($product->images[0]['src']))
          continue;

        $product->images = null;

        $product->save();
        
        $bar->advance();
      }

      $bar->finish();
    }
}
