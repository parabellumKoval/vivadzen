<?php

namespace App\Mail;

use \App\Mail\Order;

class OrderCreatedAdmin extends Order
{

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      $this->prepareData();

      return $this->subject(__('mail.new_order_admin') . ' / DJINI')
                  ->markdown('mail.order_created_admin')
                  ->with([
                    'order' => $this->order,
                    'user' => $this->user,
                    'common' => $this->common,
                    'products' => $this->products,
                    'payment' => $this->payment,
                    'delivery' => $this->delivery
                  ]);
    }
}
