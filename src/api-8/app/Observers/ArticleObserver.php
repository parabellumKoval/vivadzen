<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Bunny;

class ArticleObserver
{
    /**
     * Handle the Article "deleting" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function deleting(Article $article) {
      if(empty($article->images)) {
        return;
      }
      
      $bunny = new Bunny('brands');
      $bunny->removeAllImages($article->images);
    }
}
