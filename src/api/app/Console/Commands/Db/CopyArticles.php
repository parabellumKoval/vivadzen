<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Articles\app\Models\Article as NewArticle;

class CopyArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:articles';

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

      $this->articlesToMultilangs();

      return 0;
    }

    public function articlesToMultilangs() {
      $old_articles = \DB::table('blog_posts')->select('blog_posts.*')->get();
      
      $bar = $this->output->createProgressBar(count($old_articles));
      $bar->start();

      foreach($old_articles as $article) {
        $lang = 'ru';
  
        $n_article = new NewArticle;
  
        $n_article->setTranslation('title', $lang, $article->title);
        $n_article->slug = $article->slug;
        $n_article->setTranslation('content', $lang, $article->description);
        $n_article->setTranslation('excerpt', $lang, $article->introtext);
        $n_article->image = $article->image_large;
        $n_article->date = $article->publicated_at;
        $n_article->status = 'PUBLISHED';
        $n_article->extras = ['time' => $article->duration];
        $n_article->setTranslation('seo', $lang, [
          'meta_title' => $article->seo_title,
          'meta_description' => $article->seo_description
        ]);
  
        $n_article->save();

        $bar->advance();
      }

      $bar->finish();
    }
}