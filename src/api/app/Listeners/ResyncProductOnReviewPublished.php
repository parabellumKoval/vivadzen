<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Backpack\Reviews\app\Events\ReviewPublished;
use Backpack\Store\app\Job\SyncCatalogProductJob;

class ResyncProductOnReviewPublished implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReviewPublished $event): void
    {
        $review = $event->review;

        // обнавляем кеш только для отзывов верхнего уровня, так как только они влияют на рейтинг товара
        if ((int)$review->parent_id !== 0) {
          return;
        }

        $reviewable = $review->reviewable; // MorphTo

        if (!$reviewable) {
            return;
        }


        $products = $reviewable->children;

        if(!$products) {
          return;
        }

        // dd($reviewable->children, $reviewable->parent_id, $reviewable->id);
        // $reviewableKey = $reviewable->parent_id ?? $reviewable->id;

        /**
         * Делаем проверку гибко, опираясь на конфиг reviewable_types_list из Settings.
         * В нём у вас:
         *   'product' => ['model' => 'Backpack\Store\app\Models\Product', ...]
         */
        $types = \Settings::get('backpack.reviews.reviewable_types_list') ?? [];
        $productModel = data_get($types, 'product.model');

        // если модель из конфига задана — проверяем через instanceof на лету
        // if ($productModel && $reviewable instanceof $productModel) {
            foreach($products as $product) {
              // Вызвать job пересборки кэша товара
              SyncCatalogProductJob::dispatch($product->id);
            }
        //     return;
        // }

        // На всякий случай подхватим возможный Admin-класс, если так сохранён morph
        // $fallbacks = [
        //     \Backpack\Store\app\Models\Product::class,
        //     \Backpack\Store\app\Models\Admin\Product::class,
        // ];

        // foreach ($fallbacks as $cls) {
        //     if (class_exists($cls) && $reviewable instanceof $cls) {
        //         SyncCatalogProductJob::dispatch($reviewableKey);
        //         return;
        //     }
        // }
    }
}
