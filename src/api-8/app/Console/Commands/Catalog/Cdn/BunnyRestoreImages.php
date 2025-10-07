<?php

namespace App\Console\Commands\Catalog\Cdn;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

use Corbpie\BunnyCdn\BunnyAPIStorage;

use App\Models\Product;

ini_set('memory_limit', '500M');

class BunnyRestoreImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:cdn-bunny-restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore product images';


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

        // dd($this->client->listFiles('products'));
    }

    private function imageExists($url)
    {
      try {
        $bunny = new BunnyAPIStorage();
        $bunny->zoneConnect(config('bunny.zone'), config('bunny.key'));

        if($bunny->getFileSize('/' . $url) === -1) {
          return false;
        }else {
          return true;
        }
      }catch(\Exception $e) {
        return false;
      }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    // $products = Product::where('images', '!=', null);
	    $products = Product::where('images', '!=', null)->where('is_bunny', 1)->where('bunny_restore', 0);
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

          // Remote path
          $remote_path = 'products/' . $image['src'];
          // $remote_url = 'https://djini-v2.b-cdn.net/products/' . $image['src'];

          // Local path
          $image_url = public_path('/uploads/images/products/' . $image['src']);
  
          // Remote file
          $exists = $this->imageExists($remote_path);
          
          if($exists) {
            continue;
          }

          if(!File::exists($image_url)) {
            $this->error('Local file not exists: ' . $image_url . "\n");
            continue;
          }
    
          try {
            $uploads_result = $this->client->upload($image_url, $remote_path);
            $results = json_decode($uploads_result, true);
          }catch(\Exception $e) {
            $this->error($e->getMessage() . "\n");
            continue;
          }
  
          if($results['HttpCode'] === 201) {
          }else {
            $this->error($results['Message'] . "\n");
          }
        }

        $product->bunny_restore = 1;
        $product->save();

        $bar->advance();
      }

      $bar->finish();
    }
    
}
