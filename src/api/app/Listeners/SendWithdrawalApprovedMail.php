<?php

namespace App\Listeners;

use App\Mail\WithdrawalApprovedNotification;
use App\Support\MailRecipientResolver;
use Backpack\Profile\app\Events\WithdrawalApproved;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWithdrawalApprovedMail
{
    public function handle(WithdrawalApproved $event): void
    {
        $withdrawal = $event->withdrawal->loadMissing('user.profile');
        $user = $withdrawal->user;

        $email = MailRecipientResolver::email($user);

        if (! $email) {
            Log::warning('Withdrawal approved email skipped: recipient email missing or invalid', [
                'withdrawal_id' => $withdrawal->getKey(),
                'user_id' => $withdrawal->user_id,
            ]);
            return;
        }

        Mail::to($email)->queue(
            new WithdrawalApprovedNotification($withdrawal)
        );
    }
}
