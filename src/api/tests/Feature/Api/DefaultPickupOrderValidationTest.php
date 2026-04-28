<?php

namespace Tests\Feature\Api;

use Backpack\Store\app\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Rd\app\Exceptions\DetailedException;
use Tests\TestCase;

class DefaultPickupOrderValidationTest extends TestCase
{
    public function test_validate_data_accepts_default_pickup_with_selected_location(): void
    {
        $controller = new OrderController();

        $data = $controller->validateData(
            Request::create('/api/order', 'POST', $this->validPayload())
        );

        $this->assertSame('default_pickup', data_get($data, 'delivery.method'));
        $this->assertSame('default_cash', data_get($data, 'payment.method'));
        $this->assertSame('Prague Store, Vaclavske namesti 1', data_get($data, 'delivery.warehouse'));
    }

    public function test_validate_data_requires_selected_pickup_location_for_default_pickup(): void
    {
        $controller = new OrderController();
        $payload = $this->validPayload();

        unset($payload['delivery']['warehouse']);

        try {
            $controller->validateData(Request::create('/api/order', 'POST', $payload));
            $this->fail('Expected default pickup validation to fail without warehouse.');
        } catch (DetailedException $exception) {
            $this->assertSame(403, $exception->getCode());
            $errors = data_get($exception->getOptions(), 'delivery.warehouse');

            $this->assertIsArray($errors);
            $this->assertNotEmpty($errors);
        }
    }

    protected function validPayload(): array
    {
        return [
            'provider' => 'data',
            'payment' => [
                'method' => 'default_cash',
            ],
            'delivery' => [
                'method' => 'default_pickup',
                'warehouse' => 'Prague Store, Vaclavske namesti 1',
                'warehouseRef' => 'pickup-1',
                'street' => 'Vaclavske namesti 1',
            ],
            'products' => [
                1 => 1,
            ],
            'user' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '+420111222333',
                'email' => 'test@example.com',
            ],
        ];
    }
}
