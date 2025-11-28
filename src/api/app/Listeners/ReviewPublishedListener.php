<?php

namespace App\Listeners;

use App\Services\Referral\Triggers\ReviewTextPublished;
use App\Services\Referral\Triggers\ReviewVideoPublished;
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

        $triggerAlias = $review->is_video
            ? ReviewVideoPublished::alias()
            : ReviewTextPublished::alias();

        // Give bonus money for review
        \Profile::trigger($triggerAlias, null, [], $review->owner_id, [
            'subject_type' => $review->getMorphClass(),
            'subject_id' => $review->id,
        ]);
    }
}
