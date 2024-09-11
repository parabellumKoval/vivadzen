<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\SupplierProduct;

use App\Traits\ProductProcessing;

class JoinProductDublicates extends Command
{

    use ProductProcessing;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:join-product-dublicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'We go through the entire database and try to find duplicates by name/code/barcode and combine products and their SupplierProduct';

    private $dublicates_count = 0;
    private $processed_ids = [];
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

      // Find duplications by code and barcode and join
      $this->joinDublicatesByCode();

      // Find duplications by name and join
      $this->processed_ids = [];
      $this->joinDublicatesByName();

      return 0;
    }
        
    
    /**
     * joinDublicatesByName
     *
     * @return void
     */
    private function joinDublicatesByName() {
      $products = Product::where('id', '>', 0);
      $products_cursor = $products->cursor();
      $products_count = $products->count();
      
      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      $all_supplier_products = collect();
      foreach($products_cursor as $product) {
        $bar->advance();

        if(in_array($product->id, $this->processed_ids)) {
          continue;
        }

        $dublicates_by_name = Product::
            where('id', '!=', $product->id)
          ->where(function($query) use($product) {
            $query->where('name->ru', 'LIKE', '%' . $product->name . '%')
                  ->orWhere('name->uk', 'LIKE', '%' . $product->name . '%');
          })
          ->get();

        if(!$dublicates_by_name->count()) {
          // $this->comment("\n". 'SKIP, no dublicates for product id ' . $product->id . "\n");
          continue;
        }

        // Get all base product SupplierProducts
        $all_supplier_products = $product->sp;

        // Sum dublicates for statistics
        $this->dublicates_count += $dublicates_by_name->count();
        $this->info('Dublicates by NAME found for id: ' . $product->id . ', code: ' . $product->code . "\n");

        foreach($dublicates_by_name as $key => $dublicate) {
          $this->line(($key + 1) . ') id: ' . $dublicate->id . ', code: ' . $dublicate->code . "\n");
          // Set this duplication as processed
          $this->processed_ids[] = $dublicate->id;
          // Add all SupplierProduct from this product duplication
          $all_supplier_products = $all_supplier_products->merge($dublicate->sp);
        }

        // Merge all SupplierProduct
        $this->mergeSupplierProductsTrait($all_supplier_products);

        // set base product as processed
        $this->processed_ids[] = $product->id;
      }

      $this->info('TOTAL DUBLICATES = ' . $this->dublicates_count);

      $bar->finish();
    }

    /**
     * joinDublicatesByCode
     *
     * @return void
     */
    private function joinDublicatesByCode() {
      $sps = SupplierProduct::where('supplier_id', '!=', 44);
      $sps_cursor = $sps->cursor();
      $sps_count = $sps->count();

      $bar = $this->output->createProgressBar($sps_count);
      $bar->start();

      foreach($sps_cursor as $sp) {
        $bar->advance();

        if(in_array($sp->id, $this->processed_ids)) {
          continue;
        }

        $dubls = SupplierProduct::
            where('id', '!=', $sp->id)
          ->where('supplier_id', '!=', 44);
        
        $this->processed_ids[] = $sp->id;

        // CODE
        if(!empty($sp->code)){ 
          $dubls_by_code = $dubls
              ->where(function($query) use($sp) {
                $query->where('code', $sp->code);
                $query->orWhere('barcode', $sp->code);
              })
              ->get();
        
          if($dubls_by_code->count()) {
            $this->mergeSupplierProductsTrait($dubls_by_code->push($sp));
            $this->processed_ids = $this->processed_ids + $dubls_by_code->pluck('product_id')->toArray();
            $this->dublicates_count += $dubls_by_code->count();
          }
        }

        // BARCODE
        if(!empty($sp->barcode)){ 
          $dubls_by_barcode = $dubls
              ->where(function($query) use($sp) {
                $query->where('code', $sp->barcode);
                $query->orWhere('barcode', $sp->barcode);
              })
              ->get();
          
            if($dubls_by_barcode->count()) {
              $this->mergeSupplierProductsTrait($dubls_by_barcode->push($sp));
              $this->processed_ids = $this->processed_ids + $dubls_by_barcode->pluck('product_id')->toArray();
              $this->dublicates_count += $dubls_by_barcode->count();
            }
        }
      }

      $this->info('TOTAL DUBLICATES = ' . $this->dublicates_count);

      $bar->finish();
    }
    
}