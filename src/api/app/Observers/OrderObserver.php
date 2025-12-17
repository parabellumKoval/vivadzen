<?php

namespace App\Observers;

use App\Mail\OrderCreated;
use App\Mail\OrderCreatedAdmin;
use App\Support\AdminNotificationResolver;
use App\Support\RegionalContext;
use Illuminate\Support\Facades\Mail;

use \Backpack\Store\app\Models\Order;

class OrderObserver
{
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
}
