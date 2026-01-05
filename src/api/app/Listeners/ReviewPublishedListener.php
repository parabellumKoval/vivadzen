<?php

namespace App\Listeners;

use App\Services\Referral\Triggers\ReviewTextPublished;
use App\Services\Referral\Triggers\ReviewVideoPublished;
use App\Support\ReviewRewardContext;
use Backpack\Reviews\app\Events\ReviewPublished;

class ReviewPublishedListener
{
    public function __construct(
        protected ReviewRewardContext $rewardContext
    ) {
    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function handle(ReviewPublished $event)
    {
        if ($this->rewardContext->shouldSkipRewards()) {
            return;
        }

        $review = $event->review;

        // Skip reward if marked in extras (for bot-generated reviews)
        if (data_get($review->extras, 'skip_reward') === true) {
            return;
        }

        // Skip reward for bot-generated reviews
        if (data_get($review->extras, 'generated_by_bot') === true) {
            return;
        }

        if (!$review->is_video) {
            $link = data_get($review->extras, 'link');
            if (blank($link)) {
                return;
            }
        }

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
