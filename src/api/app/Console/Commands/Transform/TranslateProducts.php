<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

use \DeepL\Translator;

class TranslateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:products';

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

      // $products = Product::where('is_active', 1)
      //                       ->where('is_trans', 0)
      //                       ->whereHas('sp', function($query) {
      //                         $query->where('in_stock', '>', 0)
      //                                ->where('price', '>=', 300);
      //                       })
      //                       ->take(5)
      //                       ->get();

      // $this->translate($products);


      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $page = 0;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;
        $products = Product::where('is_active', 1)
                            ->where('is_trans', 0)
                            ->whereHas('sp', function($query) {
                              $query->where('in_stock', '>', 0)
                                     ->where('price', '>=', 300);
                            })
                            ->skip($skip)
                            ->take($per_page)
                            ->get();
      
        $this->translate($products);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($products->count());

      return 0;
    }
    
    /**
     * translate
     *
     * @param  mixed $products
     * @return void
     */
    private function translate($products) {

      $authKey = config('deepl.key');
      $translator = new \DeepL\Translator($authKey);

      $bar = $this->output->createProgressBar($products->count());
      $bar->start();

      foreach($products as $product) {
        $ru_name = $product->getTranslation('name', 'ru', false);
        $uk_name = $product->getTranslation('name', 'uk', false);

        $ru_content = $product->getTranslation('content', 'ru', false);
        $uk_content = $product->getTranslation('content', 'uk', false);


        if(empty($ru_content) && empty($uk_content)) {
          $this->line('skip product id ' . $product->id);
          continue;
        }


        if(!empty($ru_content)) {

          $result = $translator->translateText([
            $ru_name,
            $ru_content
          ], 'ru', 'uk', ['tag_handling' => 'html']);
  
          $product->setTranslation('name', 'uk', $result[0]->text)
                  ->setTranslation('content', 'uk', $result[1]->text);

          $product->is_trans = 1;
          $product->save();

          $this->info('Translate product id ' . $product->id);
        }else if(!empty($uk_content)) {

          $result = $translator->translateText([
            $uk_name,
            $uk_content
          ], 'uk', 'ru', ['tag_handling' => 'html']);
  
          $product->setTranslation('name', 'ru', $result[0]->text)
                  ->setTranslation('content', 'ru', $result[1]->text);

          $product->is_trans = 1;
          $product->save();

          $this->info('Translate product id ' . $product->id);
        }

        $bar->advance();
      }

      $bar->finish();
      
    }

}
