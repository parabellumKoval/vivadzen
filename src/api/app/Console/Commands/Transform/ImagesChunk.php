<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;
use File;

use Backpack\Store\app\Models\Product;

class ImagesChunk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:images-chunk';

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

      $this->moveImages();
      return 0;
    }

    private function moveImages() {

      $page = 0;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;
        $products = Product::whereNotNull('images')->skip($skip)->take($per_page)->get();
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach($products as $product) {
          $this->updateProduct($product);
          $bar->advance();
        }

        $bar->finish();

        $page += 1;
      }while($products->count());
      
    }

    private function updateProduct($product) {

      foreach($product->images as $image) {
        $src = $image['src'];

        if(!$src)
          continue;

        $products_dir = 'uploads/images/products';
        $to_directory = $products_dir . '/1/';
        $to_path = public_path($to_directory);

        $from_file = public_path($products_dir . $src);
        $to_file = public_path($to_directory . $src);


        $files = File::files($to_path);
        $dirs = File::directories(public_path($products_dir));
        dd($dirs);

        dd(count($files));

        if(!File::isDirectory($to_path)){
          File::makeDirectory($to_path, 0755, true, true);
        }

        File::move($from_file, $to_file);

        dd($src);

      }

      // $product->save();

    }


}
