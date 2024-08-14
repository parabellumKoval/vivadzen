<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class JoinProductDublicates extends Command
{
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
    protected $description = 'Command description';

    private $dublicates_count = 0;
    private $processed_ids = [];

    private $prosecced_ids = [];
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

      $this->joinDublicates();

      return 0;
    }

    public function joinDublicates() {
      $products = Product::where('id', '>', 0)->where('is_active', 1);
      $products_cursor = $products->cursor();
      $products_count = $products->count();
      
      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {
        $bar->advance();

        if(in_array($product->id, $this->prosecced_ids)) {
          continue;
        }

        $dublicates_by_code = Product::
            where('id', '!=', $product->id)
          ->where('code', $product->code)
          ->whereNotNull('code')
          ->where('code', '!=', "")
          ->get();

        $dublicates_by_name = Product::
          where('id', '!=', $product->id)
        ->where('name->ru', 'LIKE', '%' . $product->name . '%')
        ->get();

        if(!$dublicates_by_code->count() && !$dublicates_by_name->count()) {
          $this->comment("\n". 'SKIP, no dublicates for product id ' . $product->id . "\n");
          continue;
        }

        $this->dublicates_count += $dublicates_by_code->count();
        $this->dublicates_count += $dublicates_by_name->count();

        if($dublicates_by_code->count()) {
          $this->info('Dublicates by CODE found for id: ' . $product->id . ', code: ' . $product->code . "\n");

          foreach($dublicates_by_code as $key => $dublicate) {
            $this->line(($key + 1) . ') id: ' . $dublicate->id . ', code: ' . $dublicate->code . "\n");
            $this->prosecced_ids[] = $dublicate->id;
          }
        }

        if($dublicates_by_name->count()) {
          $this->info('Dublicates by NAME found for id: ' . $product->id . ', code: ' . $product->code . "\n");
  
          foreach($dublicates_by_name as $key => $dublicate) {
            $this->line(($key + 1) . ') id: ' . $dublicate->id . ', code: ' . $dublicate->code . "\n");
            $this->prosecced_ids[] = $dublicate->id;
          }
        }

        $this->prosecced_ids[] = $product->id;
      }

      $this->info('TOTAL DUBLICATES = ' . $this->dublicates_count);

      $bar->finish();
    }
    
    /**
     * joinProductModifications
     *
     * @param  mixed $product
     * @param  mixed $modifications
     * @param  mixed $name_index
     * @return void
     */
    private function joinProductModifications($product, $modifications, $name_index) {
      $names = explode(',', $product->name);
      $product->short_name = $names[$name_index];
      $product->save();
      // $this->info("\n" . 'SHORT NAME = ' . $product->short_name . "\n");

      foreach($modifications as $modification) {
        $modification_names = explode(',', $modification->name);
        $modification->short_name = $modification_names[$name_index];
        $modification->parent_id = $product->id;
        $modification->save();
        // $this->info("\n" . 'MODIFICATION SHORT NAME = ' . $modification->short_name . "\n");
      }
    }
}