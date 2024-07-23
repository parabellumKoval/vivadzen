<?php

namespace App\Console\Commands\Catalog\Cdn;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use App\Models\Product;

ini_set('memory_limit', '500M');

class Bunny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:cdn-bunny';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products from belok.ua pricelist (XML)';


    public $client = null;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->client = new \Bunny\Storage\Client(
          config('bunny.key'),
          config('bunny.zone')
        );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    // $products = Product::where('images', '!=', null);
	    $products = Product::where('images', '!=', null)->where('is_bunny', 0);
      $product_cursor = $products->cursor();
      $product_count = $products->count();

      $bar = $this->output->createProgressBar($product_count);
      $bar->start();

      foreach($product_cursor as $product) {
        // sleep(0.5);

        if(!$product->images) {
          continue;
        }

        foreach($product->images as $image) {
          if(!isset($image['src']) || empty($image['src'])) {
            continue;
          }

          $image_url = public_path('/uploads/images/products/' . $image['src']);
          $remote_path = 'products/' . $image['src'];
  
          if(!File::exists($image_url)) {
            $this->error('File not exists: ' . $image_url . "\n");
            continue;
          }
    
          $uploads_result = $this->client->upload($image_url, $remote_path);
          $results = json_decode($uploads_result, true);
  
          if($results['HttpCode'] === 201) {
            // $this->info($results['Message']);
          }else {
            $this->error($results['Message'] . "\n");
          }
        }

        $product->is_bunny = 1;
        $product->save();

        $bar->advance();
      }

      $bar->finish();
    }
    
}
