<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;
use App\Models\Product;

use Illuminate\Support\Facades\Http;

class ClearBrokenImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-broken-images';

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

      $this->clearProductImages();

      return 0;
    }

    public function clearProductImages() {
      
        $products = Product::where('images', '!=', null)->whereHas('sp', function($query) {
            $query->where('supplier_id', '=', 6);
        })->orderBy('updated_at', 'desc');

        $products_count = $products->count();
        $products_cursor = $products->cursor();

        $bar = $this->output->createProgressBar($products_count);
        $bar->start();

        foreach($products_cursor as $product) {
            if(!is_array($product->images)) {
                $product->images = null;
                $this->error('images is not array: ' . $product->id);
                // $product->save();
                continue;
            }

            $good_images = array_filter($product->images, function($item) {
                if(isset($item['src']) && !empty($item['src'])) {
                    return $this->checkRemoteImage($item['src']);
                }else {
                    return false;
                }
            });

            if(!count($good_images)) {
                $this->line('NO VALID IMAGES for product: ' . $product->id);
            }

            // $product->save();

            $bar->advance();
        }

        $bar->finish();
    }

    public function checkRemoteImage($url) {
      $base_path = config('backpack.store.product.image.base_path', '/');
      $image_url = $base_path . $url;

      $response = Http::get($image_url);

      if($response->ok()) {
        $this->info('file exist: ' . $image_url);
        return true;
      }else {
        $this->error('file is broken: ' . $image_url);
        return false;
      }
    }
}