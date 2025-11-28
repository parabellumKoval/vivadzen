<?php

namespace App\Listeners;

use App\Services\Referral\Triggers\ReviewPublished as LegacyReviewPublishedTrigger;
use App\Services\Referral\Triggers\ReviewTextPublished;
use App\Services\Referral\Triggers\ReviewVideoPublished;
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

        $triggerAlias = $this->resolveTriggerAlias($review->is_video);

        // Reverse bonus money for review
        \Profile::reverseLatestForSubject($triggerAlias, $review->getMorphClass(), $review->id, 'review_deleted');

        // Backward compatibility for legacy trigger entries
        if ($triggerAlias !== LegacyReviewPublishedTrigger::alias()) {
            \Profile::reverseLatestForSubject(LegacyReviewPublishedTrigger::alias(), $review->getMorphClass(), $review->id, 'review_deleted');
        }
    }

    private function resolveTriggerAlias($isVideo): string
    {
        return $isVideo
            ? ReviewVideoPublished::alias()
            : ReviewTextPublished::alias();
    }
}
