<?php

namespace App\Listeners;

use App\Support\NotificationEventRegistry;
use Backpack\Profile\app\Events\RewardLedgerEntryCreated;
use Backpack\Profile\app\Services\NotificationService;

class SendRewardLedgerNotification
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationEventRegistry $events
    ) {
    }

    public function handle(RewardLedgerEntryCreated $event): void
    {
        $userId = $event->reward->beneficiary_user_id ?? $event->ledger->user_id;

        if (! $userId) {
            return;
        }

        $notificationEvent = $this->events->ensure('wallet.reward.created');
        $trigger = $event->event->trigger ?? null;

        $context = [
            'reward_id' => (string) $event->reward->getKey(),
            'event_id' => (string) $event->event->getKey(),
            'amount' => $this->formatAmount($event->reward->amount ?? $event->ledger->amount),
            'currency' => (string) ($event->reward->currency ?? $event->ledger->currency),
            'trigger' => $trigger ?? 'unknown',
            'trigger_label' => $this->resolveTriggerLabel($trigger),
            'reference_type' => (string) $event->ledger->reference_type,
            'reference_id' => (string) $event->ledger->reference_id,
            'level' => $event->reward->level,
            'beneficiary_type' => $event->reward->beneficiary_type,
        ];

        $this->notifications->createFromEvent($notificationEvent, $context, [], (int) $userId);
    }

    protected function resolveTriggerLabel(?string $trigger): string
    {
        $labels = [
            'review.published.text' => 'Published review (text)',
            'review.published.video' => 'Published review (video)',
            'review.published' => 'Published review',
            'store.order_paid' => 'Paid order',
            'referral.signup' => 'Referral signup',
            'referral.purchase' => 'Referral purchase',
        ];

        if ($trigger && isset($labels[$trigger])) {
            return $labels[$trigger];
        }

        if (! $trigger) {
            return 'Reward event';
        }

        return ucwords(str_replace(['.', '_'], ' ', $trigger));
    }

    protected function formatAmount($value): string
    {
        if ($value === null) {
            return '0';
        }

        if (is_numeric($value)) {
            $formatted = number_format((float) $value, 6, '.', '');
            return rtrim(rtrim($formatted, '0'), '.');
        }

        return (string) $value;
    }
}
