<?php

namespace App\Listeners;

use App\Support\NotificationEventRegistry;
use Backpack\Profile\app\Events\ReferralAttached;
use Backpack\Profile\app\Services\NotificationService;

class SendReferralNotification
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationEventRegistry $events
    ) {
    }

    public function handle(ReferralAttached $event): void
    {
        $event->sponsor->loadMissing('user');
        $event->referral->loadMissing('user');

        $user = $event->sponsor->user;

        if (! $user) {
            return;
        }

        $notificationEvent = $this->events->ensure('referral.attached');

        $referralName = $event->referral->fullname
            ?? $event->referral->user?->name
            ?? 'Referral';

        $context = [
            'referral_name' => $referralName,
            'referral_id' => (string) $event->referral->getKey(),
            'sponsor_id' => (string) $event->sponsor->getKey(),
        ];

        $this->notifications->createFromEvent($notificationEvent, $context, [], $user);
    }
}
