<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use \Cviebrock\EloquentSluggable\Services\SlugService;

use Backpack\Reviews\app\Events\ReviewPublished;

class ReviewPublishedListener
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
    public function handle(ReviewPublished $event)
    {
        $review = $event->review;
        
        // Give bonus money for review
        \Profile::trigger('review.published', null, [], $review->owner_id, ['subject_type' => $review->getMorphClass(), 'subject_id' => $review->id]);
    }
}
