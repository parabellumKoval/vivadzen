<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use \Backpack\Store\app\Models\Order;
use \App\Observers\OrderObserver;

use App\Models\Admin\Product;
use \App\Observers\ProductObserver;

use App\Models\Category;
use \App\Observers\CategoryObserver;

use App\Models\Brand;
use \App\Observers\BrandObserver;

use App\Models\Article;
use \App\Observers\ArticleObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    // protected $listen = [
    //     Registered::class => [
    //         SendEmailVerificationNotification::class,
    //     ],
    // ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
      Article::observe(ArticleObserver::class);
      Category::observe(CategoryObserver::class);
      Brand::observe(BrandObserver::class);
      Product::observe(ProductObserver::class);
      Order::observe(OrderObserver::class);
    }
}
