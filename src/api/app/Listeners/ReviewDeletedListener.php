<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use \Cviebrock\EloquentSluggable\Services\SlugService;

use Backpack\Reviews\app\Events\ReviewDeleted;

class ReviewDeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ReviewDeleted $event)
    {
        $review = $event->review;
        
        // Give bonus money for review
        \Profile::reverseLatestForSubject('review.published', $review->getMorphClass(), $review->id, 'review_deleted');
    }
}
