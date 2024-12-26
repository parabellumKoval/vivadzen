<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Http;

use App\Models\Product;
use App\Models\Bunny;

class SearchImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    protected $client = null;

    private $available_languages = [];

    private $langs_list = [];

    private $api_key = null;

    const MIN_IMAGE_WIDTH = 500;
    const MIN_IMAGE_HEIGHT = 500;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();

      // available languages
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);

      $this->api_key = config('serper.api_key');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

	    $products = Product::whereHas('sp', function($query){
        $query->where('in_stock', '>', 0);
      })->where(function($query) {
        $query->where('images', null);
        $query->orWhereRaw('JSON_LENGTH(images) = ? ', 0);
      });

      $products_count = $products->count();
      $products_cursor = $products->cursor();

      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {
        $images = $this->findImages($product->name);

        if(!$images) {
          continue;
        }

        $images_array = [];
        
        foreach($images as $image) {
          $images_array[] = [
            'src' => $this->saveImage($image['imageUrl']),
            'alt' => null,
            'title' => null,
          ];
        }

        $product->images = $images_array;
        $product->is_images_generated = 1;
        $product->save();

        $this->info('Product ' . $product->id . ' - https://djini.com.ua/' . $product->slug  . ' processed');
        $bar->advance();
      }

      $bar->finish();
    }
        
    /**
     * saveImage
     *
     * @param  mixed $url
     * @return void
     */
    private function saveImage($url) {

      // Store image from remote url to local machine
      $response = $this->storeImageLocaly($url);

      try {
        $bunny = new Bunny('products');

        // Store image on bunny cdn
        $bunny->storeBunny($response['path'], $response['filename']);

        // Remove image from local machine
        $bunny->tempImageDelete($response['path']);
      }catch(\Exception $e) {
        \Log::error($e);
        return null;
      }

      return $response['filename'];
    }
    
    /**
     * storeImageLocaly
     *
     * @param  mixed $url
     * @return array
     */
    private function storeImageLocaly($url) {

      try {
        $contents = file_get_contents($url);

        $info = pathinfo($url);
        $filename = md5(time()) . '.' . $info['extension'];
        $path = 'products/' . $filename;
      
        \Storage::disk('temp')->put('/' . $path, $contents);

      }catch(\Exception $e) {
       \Log::error($e);
       return null;
      }

      $image_local_path = \Storage::disk('temp')->path($path);

      return ['path' => $image_local_path, 'filename' => $filename];
    }

    /**
     * findImages
     *
     * @param  mixed $product_name
     * @return void
     */
    private function findImages($product_name) {
      $url = 'https://google.serper.dev/images';
      
      $headers = [
        'X-API-KEY' => $this->api_key,
        'Content-Type' => 'application/json'
      ];

      $body = [
        "q" => $product_name,
        "gl" => "ua",
        "hl" => "uk"
      ];

      try {
        $response = Http::withHeaders($headers)->post($url, $body);
        $data = $response->json();

        if(!isset($data['images']) || !is_array($data['images'])) {
          throw new Exception('No serper search results');
        }

        $filtered_images = array_filter($data['images'], function($item) {
          if($item['imageWidth'] >= self::MIN_IMAGE_WIDTH && $item['imageHeight'] >= self::MIN_IMAGE_HEIGHT) {
            return true;
          }else {
            return false;
          }
        });

        $images = !empty($filtered_images)? array_slice($filtered_images, 0, 2): null;
      }catch(\Exception $e) {
        \Log::error($e->getMessage());
        return null;
      }
      
      return $images;
    }

}
