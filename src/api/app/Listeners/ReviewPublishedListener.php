<?php

namespace App\Listeners;

use App\Services\Referral\Triggers\ReviewTextPublished;
use App\Services\Referral\Triggers\ReviewVideoPublished;
use App\Services\Referral\Triggers\ReviewPhotoPublished;
use App\Support\ReviewRewardContext;
use Backpack\Reviews\app\Events\ReviewPublished;
use Backpack\Store\app\Services\Store;

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

        $reviewType = method_exists($review, 'resolveReviewType')
            ? $review->resolveReviewType()
            : ($review->is_video ? 'video' : 'text');

        if ($reviewType === 'text') {
            $link = data_get($review->extras, 'link');
            if (blank($link)) {
                return;
            }
        }

        $triggerAlias = $this->resolveTriggerAlias($reviewType);

        // Give bonus money for review
        \Profile::trigger($triggerAlias, null, [
            'storefront' => Store::storefront(),
            'storefront_code' => Store::storefront(),
        ], $review->owner_id, [
            'subject_type' => $review->getMorphClass(),
            'subject_id' => $review->id,
        ]);
    }

    protected function resolveTriggerAlias(string $reviewType): string
    {
        return match ($reviewType) {
            'video' => ReviewVideoPublished::alias(),
            'photo' => ReviewPhotoPublished::alias(),
            default => ReviewTextPublished::alias(),
        };
    }
}
