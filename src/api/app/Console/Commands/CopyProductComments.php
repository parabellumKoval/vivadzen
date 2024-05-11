<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Reviews\app\Models\Review;

class CopyProductComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:product-comment';

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
      $this->copyReviews();
      return 0;
    }


  public function copyReviews() {
    $product_comments = \DB::table('product_comments')
        ->select('product_comments.*')
        ->get();

      $bar = $this->output->createProgressBar(count($product_comments));
      $bar->start();


    foreach($product_comments as $comment) {

      $product = Product::where('old_id', $comment->product_id)->first();

      if(!$product)
        continue;

      $extras = [
        "owner" =>  [
            "id" => $comment->user_id,
            "email" => $comment->email,
            "name" => $comment->name,
            "photo" => null
        ],
        "advantages" => $comment->pluses,
        "flaws" => $comment->minuses
      ];

      $review = new Review();

      $review->old_id = $comment->id;
      $review->old_parent_id = $comment->parent_id;

      $review->is_moderated = $comment->is_confirmed;

      $review->text = $comment->text;
      $review->rating = $comment->rating;
      $review->likes = $comment->likes;

      $review->extras = $extras;

      $review->reviewable_id = $product->id;
      $review->reviewable_type = 'Backpack\Store\app\Models\Product';

      $review->save();

      $bar->advance();
    }

    $reviews = Review::all();

    foreach($reviews as $review) {
      $review->parent_id = $review->old_parent_id;
      $review->save();
    }

    $bar->finish();
  }
}
