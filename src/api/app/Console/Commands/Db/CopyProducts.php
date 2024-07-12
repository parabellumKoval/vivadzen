<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

// use Backpack\Store\app\Models\Product;

use Backpack\Store\app\Models\Product as NewProduct;

class CopyProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:products';

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

      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $page = 25;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;

        $old_products_collection = \DB::table('products')
          ->select('products.*')
          ->where('deleted_at', null)
          ->skip($skip)
          ->take($per_page)
          ->get();

        $this->createProducts($old_products_collection);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($old_products_collection->count());

      return 0;
    }

    public function createProducts($old_products) {
  
      if(!$old_products) {
        print 'no data';
        return;
      }
  
      $bar = $this->output->createProgressBar(count($old_products));
      $bar->start();

      foreach($old_products as $old_product) {
        
        $bar->advance();

        $product = NewProduct::where('old_id', $old_product->id)->first();

        if(!$product) {
          $product = new NewProduct;
        }

        $product->old_id = $old_product->id;
        $product->code = $old_product->vendor_code;
        // $product->code = $old_product->vendor_code? substr($old_product->vendor_code, 0, 30): null;
        $product->setTranslation('name', 'ru', $old_product->title);
        $product->slug = $old_product->slug;
        $product->setTranslation('content', 'ru', $old_product->description);
        $product->images = [
          [
            'src' => $old_product->image_large,
            'alt' => null,
            'title' => null
          ]
        ];
        $product->in_stock = $old_product->amount? $old_product->amount: 0;

        if($old_product->price_discount) {
          $product->old_price = $old_product->price;
          $product->price = $old_product->price_discount;
        }else {
          $product->price = $old_product->price;
        }

        if($old_product->seo_title || $old_product->seo_description) {
          $product->setTranslation('seo', 'ru', [
            'meta_title' => $old_product->seo_title,
            'meta_description' => $old_product->seo_description
          ]);
        }

        if($old_product->parsed_from) {
          $product->extras = [
            'parsed_from' => $old_product->parsed_from
          ];
          $product->parsed_from = $old_product->parsed_from;
        }

        $product->supplier_id = $old_product->supplier_id;
        $product->import_id = $old_product->import_id;

        $product->save();
      }

      $bar->finish();
  
    }
}