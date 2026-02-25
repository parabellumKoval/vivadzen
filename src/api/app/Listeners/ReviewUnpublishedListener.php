<?php

namespace App\Listeners;

use App\Services\Referral\Triggers\ReviewPublished as LegacyReviewPublishedTrigger;
use App\Services\Referral\Triggers\ReviewPhotoPublished;
use App\Services\Referral\Triggers\ReviewTextPublished;
use App\Services\Referral\Triggers\ReviewVideoPublished;
use Backpack\Reviews\app\Events\ReviewUnpublished;

class ReviewUnpublishedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ReviewUnpublished $event)
    {

        $review = $event->review;

        $reviewType = method_exists($review, 'resolveReviewType')
            ? $review->resolveReviewType()
            : ($review->is_video ? 'video' : 'text');
        $triggerAlias = $this->resolveTriggerAlias($reviewType);

        // Reverse bonus money for review
        \Profile::reverseLatestForSubject($triggerAlias, $review->getMorphClass(), $review->id, 'review_unpublished');

        // Backward compatibility for legacy trigger entries
        if ($triggerAlias !== LegacyReviewPublishedTrigger::alias()) {
            \Profile::reverseLatestForSubject(LegacyReviewPublishedTrigger::alias(), $review->getMorphClass(), $review->id, 'review_unpublished');
        }
    }

    private function resolveTriggerAlias(string $reviewType): string
    {
        return match ($reviewType) {
            'video' => ReviewVideoPublished::alias(),
            'photo' => ReviewPhotoPublished::alias(),
            default => ReviewTextPublished::alias(),
        };
    }
}
