<?php

namespace Tests\Unit;

use Tests\TestCase;

class StoreDeliveryHelperTest extends TestCase
{
    public function test_store_delivery_lines_hide_technical_refs_and_keep_logical_order(): void
    {
        $lines = store_delivery_lines([
            'area' => 'Черкаська область',
            'district' => 'Канівський район',
            'type' => 'місто',
            'settlement' => 'Канів',
            'warehouse' => 'Поштомат "Нова Пошта" №33624: вул. Гетьмана Михайла Дорошенка, 60',
            'settlementRef' => '4a054f6f-2750-11f0-9ad7-d4f5ef0df2b8',
            'warehouseRef' => 'e7187c05-4b33-11e4-ab6d-005056801329',
            'price' => 80,
            'priceCurrency' => 'UAH',
        ]);

        $this->assertSame([
            'Черкаська область, Канівський район, місто Канів',
            'Поштомат "Нова Пошта" №33624: вул. Гетьмана Михайла Дорошенка, 60',
        ], $lines);
    }

    public function test_store_delivery_lines_accept_nested_provider_payloads_without_array_to_string_errors(): void
    {
        $lines = store_delivery_lines([
            'deliveryMethod' => [
                'code' => 'packeta_warehouse',
                'label' => 'Packeta',
            ],
            'address' => [
                'country' => 'Czechia',
                'city' => 'Brno',
                'street' => 'Masarykova',
                'house' => '12',
                'zip' => '60200',
            ],
            'pickupPoint' => [
                'title' => 'Z-BOX Brno Centrum',
                'id' => 'point-123',
            ],
            'providerPayload' => [
                'branchId' => 'abc',
                'tracking' => [
                    'number' => 'ZX-123',
                ],
            ],
        ]);

        $this->assertSame([
            'Пункт выдачи Packeta',
            'Czechia, Brno',
            'Z-BOX Brno Centrum',
            'ZX-123',
        ], $lines);
    }
}
