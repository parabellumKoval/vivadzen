<?php

namespace App\Observers;

use App\Mail\OrderCreated;
use App\Mail\OrderCreatedAdmin;
use App\Support\AdminNotificationResolver;
use App\Support\NotificationEventRegistry;
use App\Support\RegionalContext;
use Backpack\Profile\app\Services\NotificationService;
use Illuminate\Support\Facades\Mail;

use \Backpack\Store\app\Models\Order;

class OrderObserver
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationEventRegistry $events
    ) {
    }

    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
      $regionalContext = $this->resolveRegionalContextSnapshot();
      $adminRecipients = AdminNotificationResolver::resolve($order->country_code ?? null);

      // SEND NOTY TO ADMIN EMAIL
      if (!empty($adminRecipients)) {
          Mail::to($adminRecipients)->queue(new OrderCreatedAdmin($order));
      }

      // SEND NOTY TO CUSTOMER
      $email = $order->info['user']['email'] ?? null;

      if($email)
        Mail::to($email)->queue(new OrderCreated($order, $regionalContext));
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        $userId = $this->resolveOrderUserId($order);

        if (! $userId) {
            return;
        }

        $changes = [
            ['field' => 'status', 'event' => 'order.status.changed'],
            ['field' => 'pay_status', 'event' => 'order.payment.changed'],
            ['field' => 'delivery_status', 'event' => 'order.delivery.changed'],
        ];

        foreach ($changes as $change) {
            if (! $order->wasChanged($change['field'])) {
                continue;
            }

            $this->notifyOrderStatusChange($order, $userId, $change['event'], $change['field']);
        }
    } 

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }
    protected function resolveRegionalContextSnapshot(): ?array
    {
        if (class_exists(RegionalContext::class)) {
            return app(RegionalContext::class)->snapshot();
        }

        return null;
    }

    protected function resolveOrderUserId(Order $order): ?int
    {
        $order->loadMissing('orderable');
        $orderable = $order->orderable;

        if (! $orderable) {
            return null;
        }

        $userModel = config('backpack.profile.user_model', \App\Models\User::class);

        // dd($userModel, $orderable->getKey(), $orderable instanceof $userModel);
        if ($orderable instanceof $userModel) {
            return (int) $orderable->getKey();
        }

        if (! empty($orderable->user_id)) {
            return (int) $orderable->user_id;
        }

        if (method_exists($orderable, 'user')) {
            $orderable->loadMissing('user');
            $userId = $orderable->user?->getKey();

            if ($userId) {
                return (int) $userId;
            }
        }

        return null;
    }

    protected function notifyOrderStatusChange(Order $order, int $userId, string $eventKey, string $field): void
    {
        $notificationEvent = $this->events->ensure($eventKey);

        $context = [
            'order_id' => (string) $order->getKey(),
            'order_code' => (string) ($order->code ?? $order->getKey()),
            'status' => (string) $order->{$field},
            'previous_status' => (string) $order->getOriginal($field),
            'status_field' => (string) $field,
        ];

        $this->notifications->createFromEvent($notificationEvent, $context, [], $userId);
    }
}
