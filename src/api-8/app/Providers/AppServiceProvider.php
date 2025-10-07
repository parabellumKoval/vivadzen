<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use \Backpack\Store\app\Models\Order;
use \Backpack\Reviews\app\Models\Review;
use \Backpack\Feedback\app\Models\Feedback;
use \Backpack\Transactions\app\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

      // Relation::enforceMorphMap([
      //     'Backpack\Store\app\Models\Product' => 'App\Models\Override\Product',
      // ]);

      \View::composer(['backpack::inc.topbar_left_content', 'backpack::inc.sidebar_content'], function ($view) {
		    $orders = Order::where('status','new')->count();
		    $reviews = Review::where('is_moderated', 0)->count();
		    $feedback = Feedback::where('status', 'new')->count();
		    
        $view->with('orders', $orders)->with('reviews', $reviews)->with('feedback', $feedback);
      });
        
      \View::composer('backpack::inc.sidebar_content', function ($view) {
        $transactions = Transaction::where('status', 'complited')->count();
        $view->with('transactions', $transactions);
      });
	    
    }
}
