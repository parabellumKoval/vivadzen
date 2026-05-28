<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheWarmer;

/**
 * Write-through: любое изменение продукта в админке прогревает Redis-проекцию
 * до того, как admin-API ответит клиенту. Дополнительный страховой rebuild —
 * раз в сутки через scheduler (см. routes/console.php).
 */
class ProductObserver
{
    public function __construct(private readonly CacheWarmer $warmer)
    {
    }

    public function saved(Product $product): void
    {
        $this->warmer->warmProduct($product);
    }

    public function deleted(Product $product): void
    {
        $this->warmer->evictProduct($product->slug);
    }
}
