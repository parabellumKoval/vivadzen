<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use App\Models\Product;

ini_set('memory_limit', '500M');

class NormalizeProductsPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:normalize-products';

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

      $this->normalizeAll();
      
      return 0;
    }
        
    /**
     * normalizeAll
     *
     * @return void
     */
    private function normalizeAll(){
      $products = Product::where('old_price', '!=', null)->get();

      foreach($products as $product) {
        if($product->old_price <= $product->price) {
          $product->old_price = null;
          $product->save();
        }
      }
    }

}
