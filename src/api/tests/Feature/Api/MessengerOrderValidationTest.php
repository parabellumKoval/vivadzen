<?php

namespace Tests\Feature\Api;

use Backpack\Store\app\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Rd\app\Exceptions\DetailedException;
use Tests\TestCase;

class MessengerOrderValidationTest extends TestCase
{
    public function test_validate_data_accepts_messenger_delivery_with_cod(): void
    {
        $controller = new OrderController();

        $data = $controller->validateData(
            Request::create('/api/order', 'POST', $this->validPayload())
        );

        $this->assertSame('messenger_address', data_get($data, 'delivery.method'));
        $this->assertSame('messenger_cod', data_get($data, 'payment.method'));
        $this->assertSame('Praha', data_get($data, 'delivery.settlement'));
        $this->assertSame('1', data_get($data, 'delivery.house'));
    }

    public function test_validate_data_requires_house_for_messenger_delivery(): void
    {
        $controller = new OrderController();
        $payload = $this->validPayload();

        unset($payload['delivery']['house']);

        try {
            $controller->validateData(Request::create('/api/order', 'POST', $payload));
            $this->fail('Expected messenger delivery validation to fail without house.');
        } catch (DetailedException $exception) {
            $this->assertSame(403, $exception->getCode());
            $errors = data_get($exception->getOptions(), 'delivery.house');

            $this->assertIsArray($errors);
            $this->assertNotEmpty($errors);
        }
    }

    protected function validPayload(): array
    {
        return [
            'provider' => 'data',
            'payment' => [
                'method' => 'messenger_cod',
            ],
            'delivery' => [
                'method' => 'messenger_address',
                'settlement' => 'Praha',
                'street' => 'Libinska',
                'house' => '1',
                'room' => '5',
                'zip' => '18000',
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
