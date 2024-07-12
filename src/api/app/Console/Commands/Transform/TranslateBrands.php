<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Brand;

use \DeepL\Translator;

class TranslateBrands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:brands';

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
      $per_page = 100;

      do{
        $skip = $page * $per_page;
        $brands = Brand::whereNotNull('content')
                        ->where('is_active', 1)
                        ->where('is_trans', 0)
                        ->skip($skip)
                        ->take($per_page)
                        ->get();
      
        $this->translate($brands);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($brands->count());

      return 0;
    }

    private function translate($brands) {

      $authKey = config('deepl.key');
      $translator = new \DeepL\Translator($authKey);

      $bar = $this->output->createProgressBar($brands->count());
      $bar->start();

      foreach($brands as $brand) {

        if(!$brand->content) {
          continue;
        }

        $result = $translator->translateText([
          $brand->content
        ], 'ru', 'uk', ['tag_handling' => 'html']);

        $brand->setTranslation('content', 'uk', $result[0]->text);

        $brand->is_trans = 1;
        $brand->save();

        $bar->advance();
      }

      $bar->finish();
      
    }

}
