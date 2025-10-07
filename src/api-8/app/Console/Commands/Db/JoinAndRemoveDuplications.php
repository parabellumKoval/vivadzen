<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use App\Models\Admin\Product;
use Backpack\Store\app\Models\SupplierProduct;
use Backpack\Store\app\Models\OrderProduct;

class JoinAndRemoveDuplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:join-and-remove-duplications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If it finds products marked using the duplicate_of field as duplicates of some products. 
    Transfers all SupplierProduct values to the main product and deletes the product (duplicate) card. 
    The launch must be scheduled.';

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
      $this->joinSps();
    }
    
    /**
     * joinSps
     *
     * @return void
     */
    private function joinSps() {

      $products = Product::whereNotNull('duplicate_of')->get();

      $bar = $this->output->createProgressBar($products->count());
      $bar->start();

      foreach($products as $product) {
        $base_product = $product->duplicate;

        if(!$base_product) {
          $this->error('Skip product (id = ' . $product->id . ') because base product (id = ' . $product->duplicate_of . ') not exists');
          continue;
        }

        //set new product_id for all SupplierProduct
        foreach($product->sp as $sp){
          $same_supplier = $base_product->sp()->where('supplier_id', $sp->supplier_id)->first();

          //skip if same supplier exists 
          // this SupplierProduct will be deleted automatically throw foreign_key when Product will be deleted
          if($same_supplier) {
            continue;
          }else {
            // else switch old product id for new 
            $sp->product_id = $base_product->id;
            $sp->save();
          }
        }
        
        // Delete duplication product
        $has_own_dupls = Product::where('duplicate_of', $product->id)->count();
        if($has_own_dupls) {
          $this->info('Product id = ' . $product->id . ' has own dupls = ' . $has_own_dupls);
        }
        
        $this->switchOrderProductToBase($product, $base_product);

        $product->delete();
        
        $bar->advance();
      }

      $bar->finish();
    }

    
    /**
     * switchOrderProductToBase
     *
     * @param  mixed $product
     * @param  mixed $base_product
     * @return void
     */
    public function switchOrderProductToBase($product, $base_product) {
      $ops = OrderProduct::where('product_id', $product->id)->get();
      
      if(!$ops) {
        return;
      }

      foreach($ops as $op) {
        $op->update([
          'product_id' => $base_product->id
        ]);
      }

    }
}