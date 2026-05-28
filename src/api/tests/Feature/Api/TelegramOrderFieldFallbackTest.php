<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use Backpack\Store\app\Models\Order as StoreOrder;
use ReflectionMethod;
use Tests\TestCase;

class TelegramOrderFieldFallbackTest extends TestCase
{
    public function test_set_request_fields_keeps_telegram_identity_when_runtime_order_config_is_stale(): void
    {
        $fields = config('dress.order.fields');
        unset($fields['telegram_user_id'], $fields['telegram_user']);
        config(['dress.order.fields' => $fields]);

        /** @var OrderController $controller */
        $controller = app(OrderController::class);
        $order = new StoreOrder();

        $result = $this->invokeSetRequestFields($controller, $order, [
            'provider' => 'data',
            'storefront' => 'telegram',
            'storefront_code' => 'telegram',
            'telegram_user_id' => 123456,
            'telegram_user' => [
                'id' => 123456,
                'username' => 'telegram-user',
            ],
        ]);

        $this->assertSame('data', data_get($result->info, 'provider'));
        $this->assertSame('telegram', data_get($result->info, 'storefront'));
        $this->assertSame(123456, data_get($result->info, 'telegram_user_id'));
        $this->assertSame('telegram-user', data_get($result->info, 'telegram_user.username'));
    }

    protected function invokeSetRequestFields(OrderController $controller, StoreOrder $order, array $data): StoreOrder
    {
        $method = new ReflectionMethod($controller, 'setRequestFields');
        $method->setAccessible(true);

        /** @var StoreOrder $result */
        $result = $method->invoke($controller, $order, $data);

        return $result;
    }
}
