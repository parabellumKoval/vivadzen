<?php

use Illuminate\Support\Facades\Route;


Route::redirect('/', '/admin', 301);

Route::get('/mailable/order/{id}', function ($id) {
  // $feedback = Backpack\Feedback\app\Models\Feedback::find(40);
  // return new App\Mail\Buy1ClickCreatedAdmin($feedback);

  // $order = \Backpack\Store\app\Models\Order::find(28);
  // return new App\Mail\OrderCreatedAdmin($order);

  $order = \Backpack\Store\app\Models\Order::find($id);
  return new App\Mail\OrderCreated($order);

});

Route::feeds();