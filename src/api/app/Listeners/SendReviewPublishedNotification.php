<?php

namespace App\Listeners;

use App\Support\NotificationEventRegistry;
use Backpack\Profile\app\Services\NotificationService;
use Backpack\Reviews\app\Events\ReviewPublished;

class SendReviewPublishedNotification
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationEventRegistry $events
    ) {
    }

    public function handle(ReviewPublished $event): void
    {
        $review = $event->review->loadMissing(['user', 'reviewable']);
        $user = $review->user;

        if (! $user) {
            return;
        }

        $notificationEvent = $this->events->ensure('review.published');
        $reviewable = $review->reviewable;
        $reviewableType = $reviewable ? class_basename($reviewable) : class_basename((string) $review->reviewable_type);
        $reviewableName = $reviewable?->title
            ?? $reviewable?->name
            ?? $reviewable?->getAttribute('title')
            ?? $reviewable?->getAttribute('name');

        $context = [
            'review_id' => (string) $review->getKey(),
            'rating' => (string) ($review->rating ?? ''),
            'reviewable_type' => $reviewableType ?: 'item',
            'reviewable_name' => $reviewableName ?: (string) ($review->reviewable_id ?? ''),
            'is_video' => $review->is_video ? '1' : '0',
        ];

        $this->notifications->createFromEvent($notificationEvent, $context, [], $user);
    }
}
