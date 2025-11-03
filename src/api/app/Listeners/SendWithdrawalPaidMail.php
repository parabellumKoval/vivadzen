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

        Mail::to($email)->queue(
            new WithdrawalPaidNotification($withdrawal)
        );
    }
}
