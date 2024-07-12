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

      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $page = 0;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;
        $products = Product::where('is_active', 1)
                            ->where('is_trans', 0)
                            ->where('in_stock', '>', 0)
                            ->where('price', '>=', 300)
                            ->skip($skip)
                            ->take($per_page)
                            ->get();
      
        $this->translate($products);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($products->count());

      return 0;
    }

    private function translate($products) {

      $authKey = config('deepl.key');
      $translator = new \DeepL\Translator($authKey);

      $bar = $this->output->createProgressBar($products->count());
      $bar->start();

      foreach($products as $product) {

        $result = $translator->translateText([
          $product->name,
          $product->content
        ], 'ru', 'uk', ['tag_handling' => 'html']);

        $product->setTranslation('name', 'uk', $result[0]->text)
                ->setTranslation('content', 'uk', $result[1]->text);

        $product->is_trans = 1;
        $product->save();

        $bar->advance();
      }

      $bar->finish();
      
    }

}
