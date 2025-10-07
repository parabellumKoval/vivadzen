<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Articles\app\Models\Article;

use \DeepL\Translator;

class TranslateArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:articles';

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
      $per_page = 20;

      do{
        $skip = $page * $per_page;

        $articles = Article::whereNotNull('content')->where('is_trans', '!==', 1)->skip($skip)->take($per_page)->get();

        $this->translate($articles);

        $page += 1;
        $out->writeln("\nPage " . $page . " finished \n");
      }while($articles->count());
      
      return 0;
    }
    
    /**
     * translate
     *
     * @param  mixed $articles
     * @return void
     */
    private function translate($articles) {

      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $authKey = config('deepl.key');
      $translator = new \DeepL\Translator($authKey);
        
      $bar = $this->output->createProgressBar($articles->count());
      $bar->start();

      foreach($articles as $article) {

        try {
          $result = $translator->translateText([
            $article->title,
            $article->content,
            $article->excerpt
          ], 'ru', 'uk', ['tag_handling' => 'html']);
        }catch(\Exception $e) {
          $out->writeln($e->getMessage());
          continue;
        }

        $article->setTranslation('title', 'uk', $result[0]->text)
                ->setTranslation('content', 'uk', $result[1]->text)
                ->setTranslation('excerpt', 'uk', $result[2]->text);

        $article->is_trans = 1;
        $article->save();

        $bar->advance();
      }

      $bar->finish();

    }


}
