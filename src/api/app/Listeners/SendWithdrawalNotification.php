<?php

namespace App\Listeners;

use App\Support\NotificationEventRegistry;
use Backpack\Profile\app\Events\WithdrawalApproved;
use Backpack\Profile\app\Events\WithdrawalPaid;
use Backpack\Profile\app\Services\NotificationService;

class SendWithdrawalNotification
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationEventRegistry $events
    ) {
    }

    public function handle(WithdrawalApproved|WithdrawalPaid $event): void
    {
        $withdrawal = $event->withdrawal->loadMissing('user');
        $user = $withdrawal->user;

        if (! $user) {
            return;
        }

        $eventKey = $event instanceof WithdrawalApproved
            ? 'withdrawal.approved'
            : 'withdrawal.paid';

        $notificationEvent = $this->events->ensure($eventKey);

        $context = [
            'withdrawal_id' => (string) $withdrawal->getKey(),
            'amount' => $this->formatAmount($withdrawal->amount),
            'currency' => (string) $withdrawal->currency,
            'status' => (string) $withdrawal->status,
        ];

        $this->notifications->createFromEvent($notificationEvent, $context, [], $user);
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
