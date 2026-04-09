<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Backpack\Store\app\Events\OrderСompleted;

class OrderСompletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderСompleted $event)
    {
        $order = $event->order;

        if($order->orderable_id) {
            $payload = [
                'order_id' => $order->id,
                'user_id'  => $order->orderable_id,
                'total'    => $order->price,
                'currency' => $order->currency,
                'storefront' => $order->storefront_code,
                'storefront_code' => $order->storefront_code,
            ];

            \Profile::trigger('store.order_paid', null, $payload, $order->orderable_id, ['subject_type' => $order->getMorphClass(), 'subject_id' => $order->id]);
        }
    }
}
