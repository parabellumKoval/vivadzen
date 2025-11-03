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

        Mail::to($email)->queue(
            new ReferralSponsorNotification($event->sponsor, $event->referral)
        );
    }
}
