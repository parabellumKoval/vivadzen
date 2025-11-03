<?php
namespace App\Mail;

use \App\Mail\Order;

class OrderCreated extends Order
{

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      
      $this->prepareData();
      
      return $this->subject(__('mail.new_order') . ' / DJINI')
                  ->markdown('mail.order_created')
                  ->with([
                    'order' => $this->order,
                    'summary' => $this->summary,
                    'pricing' => $this->pricing,
                    'adjustments' => $this->adjustments,
                    'customer' => $this->customer,
                    'products' => $this->products,
                    'payment' => $this->payment,
                    'delivery' => $this->delivery,
                    'invoice' => $this->invoice,
                    'currency' => $this->currency,
                  ]);
    }
}
