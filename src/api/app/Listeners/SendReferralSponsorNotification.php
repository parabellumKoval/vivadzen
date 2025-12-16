<?php

namespace App\Listeners;

use App\Mail\ReferralSponsorNotification;
use App\Support\MailRecipientResolver;
use Backpack\Profile\app\Events\ReferralAttached;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReferralSponsorNotification
{
    public function handle(ReferralAttached $event): void
    {
        $event->sponsor->loadMissing('user.profile');
        $event->referral->loadMissing('user');

        $recipient = $event->sponsor->user;

        $email = MailRecipientResolver::email($recipient);

        if (! $email) {
            Log::warning('Referral email skipped: sponsor email missing or invalid', [
                'sponsor_profile_id' => $event->sponsor->getKey(),
                'sponsor_user_id' => $event->sponsor->user?->getKey(),
                'referral_profile_id' => $event->referral->getKey(),
            ]);
            return;
        }

        // Save current regional context to restore it after queueing
        // This prevents the sponsor's locale from affecting other queued emails (e.g., verification)
        $previousContext = null;
        if (class_exists(\App\Support\RegionalContext::class) && app()->bound(\App\Support\RegionalContext::class)) {
            $previousContext = app(\App\Support\RegionalContext::class)->snapshot();
        }

        $regionalContext = [
            'locale' => $event->sponsor->locale ?? $event->sponsor->user?->locale ?? null,
        ];

        Mail::to($email)->queue(
            new ReferralSponsorNotification($event->sponsor, $event->referral, $regionalContext)
        );

        // Restore the previous regional context to prevent interference with other queued notifications
        if ($previousContext && class_exists(\App\Support\RegionalContext::class) && app()->bound(\App\Support\RegionalContext::class)) {
            $service = app(\App\Support\RegionalContext::class);
            $service->setLocale($previousContext['locale'] ?? null);
            $service->setRegion($previousContext['region'] ?? null);
            $service->setAcceptLanguage($previousContext['accept_language'] ?? null);
        }
    }
}
