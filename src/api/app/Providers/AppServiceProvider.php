<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use \Backpack\Store\app\Models\Order;
use \Backpack\Reviews\app\Models\Review;
use \Backpack\Feedback\app\Models\Feedback;
use \Backpack\Transactions\app\Models\Transaction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
           \App\Services\Referral\Triggers\OrderPaid::alias(),
           \App\Services\Referral\Triggers\OrderPaid::class
        );

        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
            \App\Services\Referral\Triggers\ReviewPublished::alias(),
            \App\Services\Referral\Triggers\ReviewPublished::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      \View::composer(['backpack::inc.topbar_left_content', 'backpack::inc.sidebar_content'], function ($view) {
		    $orders = Order::where('status','new')->count();
		    $reviews = Review::where('is_moderated', 0)->count();
		    $feedback = Feedback::where('status', 'new')->count();
		    
        $view->with('orders', $orders)->with('reviews', $reviews)->with('feedback', $feedback);
      });
        
    //   \View::composer('backpack::inc.sidebar_content', function ($view) {
    //     $transactions = Transaction::where('status', 'complited')->count();
    //     $view->with('transactions', $transactions);
    //   });
    }
}
