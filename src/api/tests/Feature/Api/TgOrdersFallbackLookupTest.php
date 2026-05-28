<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\TgProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TgOrdersFallbackLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-bot-token',
            'services.telegram.init_data_max_age' => 604800,
        ]);

        Schema::dropIfExists('ak_orders');
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

        Schema::create('ak_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('code')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount_total', 10, 2)->nullable();
            $table->decimal('campaign_discount_total', 10, 2)->nullable();
            $table->decimal('promocode_discount_total', 10, 2)->nullable();
            $table->decimal('bonus_discount_total', 10, 2)->nullable();
            $table->decimal('personal_discount_total', 10, 2)->nullable();
            $table->decimal('shipping_total', 10, 2)->nullable();
            $table->decimal('tax_total', 10, 2)->nullable();
            $table->decimal('grand_total', 10, 2)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('status', 64)->nullable();
            $table->string('pay_status', 64)->nullable();
            $table->string('delivery_status', 64)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('storefront_code', 64)->nullable();
            $table->text('info')->nullable();
            $table->timestamps();
        });
    }

    public function test_orders_fall_back_to_profile_contacts_when_telegram_identity_is_missing_in_order(): void
    {
        DB::table('ak_tg_profiles')->insert([
            'telegram_user_id' => 123456,
            'username' => 'telegram-user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+420111222333',
            'email' => 'test@example.com',
            'addresses' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ak_orders')->insert([
            'id' => 1,
            'code' => 427136,
            'price' => 490,
            'currency_code' => 'CZK',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'user' => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'phone' => '+420111222333',
                    'email' => 'test@example.com',
                ],
                'delivery' => [
                    'method' => 'default_pickup',
                ],
                'payment' => [
                    'method' => 'default_cash',
                ],
                'products' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ak_orders')->insert([
            'id' => 2,
            'code' => 111111,
            'price' => 490,
            'currency_code' => 'CZK',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'user' => [
                    'phone' => '+420999888777',
                    'email' => 'another@example.com',
                ],
                'products' => [],
            ]),
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $controller = app(TgProfileController::class);
        $request = Request::create('/api/tg/orders', 'GET');
        $request->headers->set('X-Telegram-Init-Data', $this->telegramInitData([
            'id' => 123456,
            'username' => 'telegram-user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'language_code' => 'cs',
        ]));

        $response = $controller->orders($request)->response()->getData(true);

        $this->assertCount(1, $response['data']);
        $this->assertSame(1, $response['data'][0]['id']);
        $this->assertSame(427136, $response['data'][0]['code']);
    }

    protected function telegramInitData(array $user): string
    {
        $payload = [
            'auth_date' => (string) now()->timestamp,
            'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
            'user' => json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        ksort($payload);

        $dataCheckString = collect($payload)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', (string) config('services.telegram.bot_token'), 'WebAppData', true);
        $payload['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        return http_build_query($payload);
    }
}
