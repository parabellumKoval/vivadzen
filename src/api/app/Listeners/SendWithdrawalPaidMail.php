<?php

namespace App\Listeners;

use App\Mail\WithdrawalPaidNotification;
use App\Support\MailRecipientResolver;
use Backpack\Profile\app\Events\WithdrawalPaid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWithdrawalPaidMail
{
    public function handle(WithdrawalPaid $event): void
    {
        $withdrawal = $event->withdrawal->loadMissing('user.profile');
        $user = $withdrawal->user;

        $email = MailRecipientResolver::email($user);

        if (! $email) {
            Log::warning('Withdrawal paid email skipped: recipient email missing or invalid', [
                'withdrawal_id' => $withdrawal->getKey(),
                'user_id' => $withdrawal->user_id,
            ]);
            return;
        }

        // Save current regional context to restore it after queueing
        // This prevents the user's locale from affecting other queued emails
        $previousContext = null;
        if (class_exists(\App\Support\RegionalContext::class) && app()->bound(\App\Support\RegionalContext::class)) {
            $previousContext = app(\App\Support\RegionalContext::class)->snapshot();
        }

        $regionalContext = [
            'locale' => $user->profile?->locale ?? $user->locale ?? null,
        ];

        Mail::to($email)->queue(
            new WithdrawalPaidNotification($withdrawal, $regionalContext)
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
