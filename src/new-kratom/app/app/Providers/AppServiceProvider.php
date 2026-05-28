<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use App\Models\Taxonomy;
use App\Observers\ProductObserver;
use App\Observers\ProductQuestionObserver;
use App\Observers\ProductReviewObserver;
use App\Observers\TaxonomyObserver;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TaxonomyRepositoryInterface;
use App\Repositories\RedisProductRepository;
use App\Repositories\RedisTaxonomyRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Контракт → Redis-имплементация. Фронт-контроллеры получают
        // только интерфейс, MySQL-имплементацию подменять не нужно.
        $this->app->singleton(ProductRepositoryInterface::class, RedisProductRepository::class);
        $this->app->singleton(TaxonomyRepositoryInterface::class, RedisTaxonomyRepository::class);
    }

    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Taxonomy::observe(TaxonomyObserver::class);
        ProductReview::observe(ProductReviewObserver::class);
        ProductQuestion::observe(ProductQuestionObserver::class);
    }
}
