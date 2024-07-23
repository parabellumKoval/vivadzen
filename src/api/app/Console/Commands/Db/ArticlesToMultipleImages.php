<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Articles\app\Models\Article;

class ArticlesToMultipleImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:articles-multiple-images';

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
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {

      $this->moveImages();

      return 0;
    }

    public function moveImages() {
      $articles = Article::all();
      
      $bar = $this->output->createProgressBar(count($articles));
      $bar->start();

      foreach($articles as $article) {
        
        if($article->images) {
          $src = $article->images['src'];

          $article->images = [
            [
              'src' => $src,
              'alt' => null,
              'title' => null,
            ]
          ];
        }
  
        $article->save();

        $bar->advance();
      }

      $bar->finish();
    }
}