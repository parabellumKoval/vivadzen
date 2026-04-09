<?php

namespace Tests\Unit;

use Backpack\Profile\app\Services\CurrencyConverter;
use Backpack\Profile\app\Services\LoyaltyDiscountService;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;
use Tests\TestCase;

class LoyaltyDiscountServiceTest extends TestCase
{
    public function test_levels_are_normalized_sorted_and_highest_matching_level_is_selected(): void
    {
        config([
            'backpack-settings.drivers' => ['config'],
            'profile.loyalty.levels' => [
                [
                    'name' => 'Gold',
                    'amount_from' => 3000,
                    'discount_percent' => 10,
                ],
                [
                    'name' => 'Starter',
                    'amount_from' => -50,
                    'discount_percent' => -5,
                ],
                [
                    'name' => 'Silver',
                    'amount_from' => 1000,
                    'discount_percent' => 5,
                ],
            ],
        ]);

        $service = new LoyaltyDiscountService($this->fakeConverter());

        $this->assertSame([
            [
                'name' => 'Starter',
                'amount_from' => 0.0,
                'discount_percent' => 0.0,
            ],
            [
                'name' => 'Silver',
                'amount_from' => 1000.0,
                'discount_percent' => 5.0,
            ],
            [
                'name' => 'Gold',
                'amount_from' => 3000.0,
                'discount_percent' => 10.0,
            ],
        ], $service->levels());

        $this->assertSame([
            'name' => 'Silver',
            'amount_from' => 1000.0,
            'discount_percent' => 5.0,
        ], $service->resolveLevel(2500));
    }

    public function test_resolve_order_spent_amount_uses_fx_rate_for_store_base_thresholds(): void
    {
        config([
            'backpack-settings.drivers' => ['config'],
            'dress.store.base_currency' => 'USD',
            'profile.loyalty.base_currency' => 'USD',
        ]);

        $service = new LoyaltyDiscountService($this->fakeConverter());

        $order = new class extends Model {};
        $order->grand_total = 2400;
        $order->shipping_total = 200;
        $order->tax_total = 200;
        $order->currency_code = 'EUR';
        $order->fx_rate = 2;

        $method = new ReflectionMethod(LoyaltyDiscountService::class, 'resolveOrderSpentAmount');
        $method->setAccessible(true);

        $this->assertSame(1000.0, $method->invoke($service, $order));
    }

    protected function fakeConverter(): CurrencyConverter
    {
        return new class extends CurrencyConverter {
            public function convert(float $amount, string $from, string $to, int $fixTo = 2): float
            {
                return round($amount, $fixTo);
            }
        };
    }
}
