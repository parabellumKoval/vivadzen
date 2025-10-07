<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Category;

use \DeepL\Translator;

class TranslateCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:categories';

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
      $per_page = 50;

      do{
        $skip = $page * $per_page;

        $categories = Category::where('is_trans', '!==', 1)->skip($skip)->take($per_page)->get();

        $this->translate($categories);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($categories->count());
      
      return 0;
    }
    
    /**
     * translate
     *
     * @param  mixed $articles
     * @return void
     */
    private function translate($categories) {

      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $authKey = config('deepl.key');
      $translator = new \DeepL\Translator($authKey);
        
      $bar = $this->output->createProgressBar($categories->count());
      $bar->start();

      foreach($categories as $category) {

        try {
          $result = $translator->translateText([
            $category->name,
            $category->content,
          ], 'ru', 'uk', ['tag_handling' => 'html']);
        }catch(\Exception $e) {
          $out->writeln($e->getMessage());
          continue;
        }

        $category->setTranslation('name', 'uk', $result[0]->text)
                ->setTranslation('content', 'uk', $result[1]->text);

        $category->is_trans = 1;
        $category->save();

        $bar->advance();
      }

      $bar->finish();

    }


}
