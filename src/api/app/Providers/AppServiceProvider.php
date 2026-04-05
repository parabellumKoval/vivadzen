<?php

namespace App\Providers;

use App\Http\Controllers\Api\OrderController as AppOrderController;
use App\Services\AgeVerification\AdultoClient;
use App\Services\AgeVerification\AgeVerificationService;
use App\Support\RegionalContext;
use App\Support\ReviewRewardContext;
use App\Support\StorefrontSettings;
use Illuminate\Support\ServiceProvider;

use \Backpack\Store\app\Models\Order;
use \Backpack\Store\app\Http\Controllers\Api\OrderController as StoreOrderController;
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
        $this->app->singleton(RegionalContext::class, fn () => new RegionalContext());
        $this->app->singleton(ReviewRewardContext::class, fn () => new ReviewRewardContext());
        $this->app->singleton(StorefrontSettings::class, fn () => new StorefrontSettings());
        $this->app->scoped(AgeVerificationService::class);
        $this->app->scoped(AdultoClient::class);
        $this->app->bind(StoreOrderController::class, AppOrderController::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\MigrateProductRegionalContent::class,
                \App\Console\Commands\GenerateProductRedirects::class,
                \App\Console\Commands\GenerateUaProductRedirects::class,
            ]);
        }

        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
           \App\Services\Referral\Triggers\OrderPaid::alias(),
           \App\Services\Referral\Triggers\OrderPaid::class
        );

        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
            \App\Services\Referral\Triggers\ReviewTextPublished::alias(),
            \App\Services\Referral\Triggers\ReviewTextPublished::class
        );

        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
            \App\Services\Referral\Triggers\ReviewVideoPublished::alias(),
            \App\Services\Referral\Triggers\ReviewVideoPublished::class
        );

        app(\Backpack\Profile\app\Services\TriggerRegistry::class)->register(
            \App\Services\Referral\Triggers\ReviewPhotoPublished::alias(),
            \App\Services\Referral\Triggers\ReviewPhotoPublished::class
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
