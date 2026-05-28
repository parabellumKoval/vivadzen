<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Services\CacheWarmer;

class ProductReviewObserver
{
    public function __construct(private readonly CacheWarmer $warmer)
    {
    }

    public function saved(ProductReview $review): void
    {
        $this->refresh($review->product_id);
    }

    public function deleted(ProductReview $review): void
    {
        $this->refresh($review->product_id);
    }

    private function refresh(int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->warmer->warmProduct($product);
        }
    }
}
