<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Order extends Mailable
{
    use Queueable, SerializesModels;
	
	  public $order;
    public $user;
    public $products;
    public $payment;
    public $delivery;
    public $common;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
        app()->setLocale('uk');
    }

    
    /**
     * prepareData
     *
     * @return void
     */
    public function prepareData() {
      if($this->order->info['payment'] && count($this->order->info['payment'])) {
        $payment = array_filter($this->order->info['payment']);
        $this->payment = __('email.status') . ": " . __('status.pay_status.'.$this->order->pay_status);
        $this->payment .= count($payment)? '&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;' . implode(', ', $payment): null;
      }

      if($this->order->info['delivery'] && count($this->order->info['delivery'])) {
        $delivery = array_filter($this->order->info['delivery']);

        $type = isset($delivery['type']) && !empty($delivery['type'])? $delivery['type'] . ' ': '';

        $delivery_arr = [
          $delivery['area'] ?? null,
          isset($delivery['settlement']) && !empty($delivery['settlement'])? $type . $delivery['settlement']: null,
          $delivery['warehouse'] ?? null,
          isset($delivery['house']) && !empty($delivery['house'])? __('email.house') . ' ' . $delivery['house']: null,
          isset($delivery['room']) && !empty($delivery['room'])? __('email.room') . ' ' . $delivery['room']: null,
          isset($delivery['zip']) && !empty($delivery['zip'])? __('email.zip') . ' ' . $delivery['zip']: null
        ];

        $delivery_arr  = array_filter($delivery_arr);
        
        $this->delivery = __('email.status') . ": " . __('status.delivery_status.'.$this->order->delivery_status);
        $this->delivery .= ' / ';
        $this->delivery .= __('email.method') . ": " . __('validation.values.delivery.method.' . $delivery['method']);
        $this->delivery .= count($delivery_arr)? '&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;' . implode(', ', $delivery_arr): null;
      }

      if($this->order->info['user'] && count($this->order->info['user'])) {
        $user = $this->order->info['user'];
        $this->user = count($user)? implode(', ', $user): null;
      }

      if($this->order->status && $this->order->price) {
        $this->common = __('email.status') . ": " . __('status.status.'.$this->order->status);
        $this->common .= "&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp; <b>" . $this->order->price ." грн.</b>";
      }

      if($this->order->info['products'] && count($this->order->info['products'])) {
        $this->products = $this->order->info['products'];
      }
    }
}