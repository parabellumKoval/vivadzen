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
      // $this->joinDublicatesByCode();

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
            $query->whereRaw('LOWER(JSON_EXTRACT(name, "$.ru")) LIKE ? ',['"' . trim(strtolower($product->name)) . '"'])
                  ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.uk")) LIKE ? ',['"' . trim(strtolower($product->name)) . '"']);

            // $query->where('name->ru', 'LIKE', '%' . $product->name . '%')
            //       ->orWhere('name->uk', 'LIKE', '%' . $product->name . '%');
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
      $sps = SupplierProduct::
        where(function($query) {
          $query->whereNotNull('code')
                ->where('code', '!=', '');
        })
        ->orWhere(function($query) {
          $query->whereNotNull('barcode')
                ->where('barcode', '!=', '');
        });

      $sps_cursor = $sps->cursor();
      $sps_count = $sps->count();

      $bar = $this->output->createProgressBar($sps_count);
      $bar->start();

      foreach($sps_cursor as $sp) {
        $bar->advance();

        // $prose = implode(', ', $this->processed_ids);
        // $this->info('procesed ' . $prose);

        if(in_array($sp->id, $this->processed_ids)) {
          $this->error('skip ' . $sp->id);
          continue;
        }

        // if($sp->id === 1596 || $sp->id === 1595 || $sp->id === 2223) {
        //   $this->info($sp->id);
        // }

        $this->processed_ids[] = $sp->id;

        $dupls = SupplierProduct::
            where('id', '!=', $sp->id)
          // ->where('supplier_id', '!=', 44)
          // Thats means that it is another product (not other supplier of same product)
          ->where('product_id', '!=', $sp->product_id);

        // CODE
        if(!empty($sp->code)){ 
          $dupls = $dupls
              // ->where('barcode', $sp->code)->get();
              ->where(function($query) use($sp) {
                $query->where('code', $sp->code)
                      ->orWhere('barcode', $sp->code);
              })->get();
        }else if(!empty($sp->barcode)){ 
          $dupls = $dupls
            // ->where('code', $sp->barcode)->get();
              ->where(function($query) use($sp) {
                $query->where('code', $sp->barcode)
                      ->orWhere('barcode', $sp->barcode);
              })->get();
        }else {
          continue;
        }

        // dd($dupls->toSql());
        // if($dupls->count() !== $dupls->unique('product_id')->count()) {
        //   dd($dupls);
        // }
        
        if($dupls->count()) {
          $this->processed_ids = $this->processed_ids + $dupls->pluck('id')->toArray();
          $this->dublicates_count += $dupls->count();

          $this->mergeSupplierProductsTrait($dupls->push($sp));
        }
      }

      $this->info('TOTAL DUBLICATES = ' . $this->dublicates_count);

      $bar->finish();
    }
    
}