<?php

namespace Tests\Feature\Api;

use App\Models\TgProfile;
use App\Http\Controllers\Api\OrderController;
use Backpack\Store\app\Models\Order as StoreOrder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class TelegramOrderFieldFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_tg_profiles');
        Schema::create('ak_tg_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->unique();
            $table->string('username', 255)->nullable();
            $table->string('first_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('avatar_url', 1024)->nullable();
            $table->string('language_code', 16)->nullable();
            $table->string('payment_method', 120)->nullable();
            $table->text('addresses')->nullable();
            $table->timestamps();
        });
    }

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

    public function test_set_user_data_links_telegram_orders_to_tg_profile_model(): void
    {
        /** @var OrderController $controller */
        $controller = app(OrderController::class);
        $order = new StoreOrder();

        $result = $this->invokeSetUserData($controller, $order, [
            'provider' => 'data',
            'storefront' => 'telegram',
            'storefront_code' => 'telegram',
            'telegram_user_id' => 463516676,
            'telegram_user' => [
                'id' => 463516676,
                'username' => 'doubleSilver',
                'first_name' => 'Andrei',
                'last_name' => '',
                'photo_url' => 'https://t.me/avatar.svg',
                'language_code' => 'ru',
            ],
        ]);

        $profile = TgProfile::query()->where('telegram_user_id', 463516676)->first();

        $this->assertNotNull($profile);
        $this->assertSame(TgProfile::class, $result->orderable_type);
        $this->assertSame((string) $profile->getKey(), (string) $result->orderable_id);
    }

    public function test_set_user_data_does_not_fail_when_tg_profiles_table_is_missing(): void
    {
        Schema::dropIfExists('ak_tg_profiles');

        /** @var OrderController $controller */
        $controller = app(OrderController::class);
        $order = new StoreOrder();

        $result = $this->invokeSetUserData($controller, $order, [
            'provider' => 'data',
            'storefront' => 'telegram',
            'storefront_code' => 'telegram',
            'telegram_user_id' => 463516676,
            'telegram_user' => [
                'id' => 463516676,
                'username' => 'doubleSilver',
            ],
        ]);

        $this->assertNull($result->orderable_type);
        $this->assertNull($result->orderable_id);
    }

    protected function invokeSetRequestFields(OrderController $controller, StoreOrder $order, array $data): StoreOrder
    {
        $method = new ReflectionMethod($controller, 'setRequestFields');
        $method->setAccessible(true);

        /** @var StoreOrder $result */
        $result = $method->invoke($controller, $order, $data);

        return $result;
    }

    protected function invokeSetUserData(OrderController $controller, StoreOrder $order, array $data): StoreOrder
    {
        $method = new ReflectionMethod($controller, 'setUserData');
        $method->setAccessible(true);

        /** @var StoreOrder $result */
        $result = $method->invoke($controller, $order, $data, null);

        return $result;
    }
}
