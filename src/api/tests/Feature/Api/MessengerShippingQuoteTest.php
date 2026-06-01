<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class MessengerShippingQuoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Settings::set('shipping.messenger.currency', 'CZK', ['cast' => 'string', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.address_rates', [
            ['shipments_count' => 1, 'price' => 100],
        ], ['cast' => 'json', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.fuel_surcharge_percent', 0, ['cast' => 'float', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.vat_rate', 21, ['cast' => 'float', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.vat_included', false, ['cast' => 'bool', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.cod.enabled', true, ['cast' => 'bool', 'region' => 'CZ']);
        \Settings::set('shipping.messenger.cod.cash_tiers', [
            ['max_amount' => 1000, 'fee' => 30],
            ['max_amount' => 999999, 'fee' => 60],
        ], ['cast' => 'json', 'region' => 'CZ']);
    }

    public function test_messenger_cod_uses_first_cash_tier_for_orders_up_to_1000_czk(): void
    {
        $response = $this->postJson('/api/shipping/quote', [
            'methodKey' => 'messenger_address',
            'destinationCountry' => 'CZ',
            'weightG' => 1000,
            'codEnabled' => true,
            'codAmount' => 900,
            'meta' => [
                'cod_payment_type' => 'cash',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('breakdown.cod_base', 30)
            ->assertJsonPath('breakdown.cod_gross', 30);
    }

    public function test_messenger_cod_uses_second_cash_tier_for_orders_above_1000_czk(): void
    {
        $response = $this->postJson('/api/shipping/quote', [
            'methodKey' => 'messenger_address',
            'destinationCountry' => 'CZ',
            'weightG' => 1000,
            'codEnabled' => true,
            'codAmount' => 1100,
            'meta' => [
                'cod_payment_type' => 'cash',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('breakdown.cod_base', 60)
            ->assertJsonPath('breakdown.cod_gross', 60);
    }
}
