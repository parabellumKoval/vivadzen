<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Services\CacheWarmer;

class ProductQuestionObserver
{
    public function __construct(private readonly CacheWarmer $warmer)
    {
    }

    public function saved(ProductQuestion $question): void
    {
        $this->refresh($question->product_id);
    }

    public function deleted(ProductQuestion $question): void
    {
        $this->refresh($question->product_id);
    }

    private function refresh(int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->warmer->warmProduct($product);
        }
    }
}
