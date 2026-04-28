<?php

namespace Tests\Unit;

use Backpack\Store\app\Models\Order;
use Tests\TestCase;

class OrderProductsRelatedTest extends TestCase
{
    public function test_products_to_synk_defaults_to_empty_array(): void
    {
        $order = new Order();

        $this->assertSame([], $order->products_to_synk);
    }

    public function test_products_related_attribute_normalizes_null_and_iterables(): void
    {
        $order = new Order();
        $items = [
            (object) ['id' => 1],
            (object) ['id' => 2],
        ];

        $order->productsRelated = null;
        $this->assertSame([], $order->products_to_synk);

        $order->productsRelated = collect($items);
        $this->assertSame($items, $order->products_to_synk);
    }
}
