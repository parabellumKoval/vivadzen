<?php

namespace Tests\Feature\Api;

use Backpack\Store\app\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Tests\TestCase;

class MessengerOrderValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Settings::set('shipping.methods', ['messenger_address'], ['cast' => 'json']);
        \Settings::set('shipping.messenger.express.enabled', true, ['cast' => 'bool']);
    }

    public function test_validate_data_accepts_messenger_delivery_with_cod(): void
    {
        $controller = new OrderController();

        $data = $controller->validateData(
            Request::create('/api/order', 'POST', $this->validPayload())
        );

        $this->assertSame('messenger_address', data_get($data, 'delivery.method'));
        $this->assertSame('messenger_cod', data_get($data, 'payment.method'));
        $this->assertSame('Praha', data_get($data, 'delivery.settlement'));
        $this->assertNull(data_get($data, 'delivery.house'));
    }

    public function test_validate_data_accepts_messenger_express_delivery_with_cod(): void
    {
        $controller = new OrderController();

        $data = $controller->validateData(
            Request::create('/api/order', 'POST', $this->validPayload('messenger_express'))
        );

        $this->assertSame('messenger_express', data_get($data, 'delivery.method'));
        $this->assertSame('messenger_cod', data_get($data, 'payment.method'));
        $this->assertSame('Praha', data_get($data, 'delivery.settlement'));
        $this->assertNull(data_get($data, 'delivery.house'));
    }

    public function test_validate_data_accepts_messenger_delivery_without_house_and_zip(): void
    {
        $controller = new OrderController();
        $payload = $this->validPayload();

        unset($payload['delivery']['house']);
        unset($payload['delivery']['zip']);

        $data = $controller->validateData(Request::create('/api/order', 'POST', $payload));

        $this->assertSame('messenger_address', data_get($data, 'delivery.method'));
        $this->assertNull(data_get($data, 'delivery.house'));
        $this->assertNull(data_get($data, 'delivery.zip'));
    }

    protected function validPayload(string $method = 'messenger_address'): array
    {
        return [
            'provider' => 'data',
            'destinationCountry' => 'CZ',
            'payment' => [
                'method' => 'messenger_cod',
            ],
            'delivery' => [
                'method' => $method,
                'settlement' => 'Praha',
                'street' => 'Libinska 1',
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
