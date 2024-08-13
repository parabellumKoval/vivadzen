<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Articles\app\Models\Article;

class UpdateBlogImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:articles-update-images';

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

      $this->articlesImage();

      return 0;
    }

    public function articlesImage() {
      $articles = Article::all();
      
      $bar = $this->output->createProgressBar(count($articles));
      $bar->start();

      foreach($articles as $article) {
        $ru_content = $article->getTranslation('content', 'ru');
        $uk_content = $article->getTranslation('content', 'uk');
        
        $ru_content_mod = str_replace('https://djini.b-cdn.net/', 'https://djini-v2.b-cdn.net/', $ru_content);
        $uk_content_mod = str_replace('https://djini.b-cdn.net/', 'https://djini-v2.b-cdn.net/', $uk_content);

        $article->setTranslation('content', 'ru', $ru_content_mod);
        $article->setTranslation('content', 'uk', $uk_content_mod);

        $article->save();

        $bar->advance();
      }

      $bar->finish();
    }
}