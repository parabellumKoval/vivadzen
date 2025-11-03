<?php

namespace App\Listeners;

use App\Mail\RewardLedgerEntryNotification;
use App\Support\MailRecipientResolver;
use Backpack\Profile\app\Events\RewardLedgerEntryCreated;
use Backpack\Profile\app\Models\WalletBalance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRewardLedgerEntryNotification
{
    public function handle(RewardLedgerEntryCreated $event): void
    {
        $reward = $event->reward->loadMissing('beneficiary.profile');
        $ledger = $event->ledger;
        $recipient = $reward->beneficiary;
        $email = MailRecipientResolver::email($recipient);

        if (! $email) {
            Log::warning('Reward email skipped: recipient email missing or invalid', [
                'reward_id' => $reward->getKey(),
                'beneficiary_user_id' => $reward->beneficiary_user_id,
            ]);
            return;
        }

        $balance = WalletBalance::query()
            ->where('user_id', $recipient->getAuthIdentifier())
            ->where('currency', $ledger->currency)
            ->value('balance');

        $balanceValue = $balance !== null ? (string) $balance : null;

        Mail::to($email)->queue(
            new RewardLedgerEntryNotification(
                $recipient,
                $event->event,
                $event->reward,
                $ledger,
                $balanceValue
            )
        );
    }
}
