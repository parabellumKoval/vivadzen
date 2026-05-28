<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\TgProfileController;
use App\Models\TgProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            $table->char('country_code', 2)->nullable();
            $table->char('orderable_id', 36)->nullable();
            $table->string('orderable_type', 255)->nullable();
            $table->string('code', 6)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('pay_status', 30)->nullable();
            $table->string('delivery_status', 30)->nullable();
            $table->float('price')->default(0);
            $table->char('currency_code', 3)->nullable();
            $table->decimal('fx_rate', 16, 8)->nullable();
            $table->decimal('subtotal', 14, 2)->nullable();
            $table->decimal('discount_total', 14, 2)->nullable();
            $table->decimal('shipping_total', 14, 2)->nullable();
            $table->decimal('tax_total', 14, 2)->nullable();
            $table->decimal('grand_total', 14, 2)->nullable();
            $table->json('info')->nullable();
            $table->string('storefront_code', 64)->nullable();
            $table->timestamps();
        });

        DB::table('ak_tg_profiles')->insert([
            'telegram_user_id' => 463516676,
            'username' => 'doubleSilver',
            'first_name' => 'Andrei',
            'last_name' => null,
            'addresses' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_orders_bind_legacy_telegram_orders_and_then_read_them_by_orderable_fields(): void
    {
        DB::table('ak_orders')->insert([
            'id' => 1,
            'code' => '427136',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'price' => 490,
            'currency_code' => 'CZK',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'telegram_user_id' => 463516676,
                'telegram_user' => [
                    'id' => 463516676,
                    'username' => 'doubleSilver',
                ],
                'products' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ak_orders')->insert([
            'id' => 2,
            'code' => '111111',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'price' => 490,
            'currency_code' => 'CZK',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'telegram_user_id' => 999999999,
                'telegram_user' => [
                    'id' => 999999999,
                ],
                'products' => [],
            ]),
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $response = $this->fetchOrders();
        $profileId = DB::table('ak_tg_profiles')->where('telegram_user_id', 463516676)->value('id');
        $updatedOrder = DB::table('ak_orders')->where('id', 1)->first();

        $this->assertCount(1, $response['data']);
        $this->assertSame(1, $response['data'][0]['id']);
        $this->assertSame('427136', $response['data'][0]['code']);
        $this->assertSame(TgProfile::class, $updatedOrder->orderable_type);
        $this->assertSame((string) $profileId, (string) $updatedOrder->orderable_id);
    }

    public function test_orders_find_new_telegram_orders_by_orderable_fields(): void
    {
        $profileId = DB::table('ak_tg_profiles')
            ->where('telegram_user_id', 463516676)
            ->value('id');

        DB::table('ak_orders')->insert([
            'id' => 3,
            'orderable_type' => TgProfile::class,
            'orderable_id' => (string) $profileId,
            'code' => '222222',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'price' => 590,
            'currency_code' => 'CZK',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'products' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->fetchOrders();

        $this->assertCount(1, $response['data']);
        $this->assertSame(3, $response['data'][0]['id']);
        $this->assertSame('222222', $response['data'][0]['code']);
    }

    public function test_orders_work_without_tg_profiles_table_via_legacy_compatibility_path(): void
    {
        Schema::dropIfExists('ak_tg_profiles');

        DB::table('ak_orders')->insert([
            'id' => 4,
            'code' => '333333',
            'status' => 'new',
            'pay_status' => 'waiting',
            'delivery_status' => 'waiting',
            'price' => 490,
            'currency_code' => 'CZK',
            'country_code' => 'cz',
            'storefront_code' => 'telegram',
            'info' => json_encode([
                'storefront' => 'telegram',
                'telegram_user_id' => 463516676,
                'telegram_user' => [
                    'id' => 463516676,
                ],
                'products' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->fetchOrders();

        $this->assertCount(1, $response['data']);
        $this->assertSame(4, $response['data'][0]['id']);
        $this->assertSame('333333', $response['data'][0]['code']);
    }

    protected function fetchOrders(): array
    {
        $controller = app(TgProfileController::class);
        $request = Request::create('/api/tg/orders', 'GET');
        $request->headers->set('X-Telegram-Init-Data', $this->telegramInitData([
            'id' => 463516676,
            'username' => 'doubleSilver',
            'first_name' => 'Andrei',
            'last_name' => '',
            'language_code' => 'ru',
        ]));

        return $controller->orders($request)->response()->getData(true);
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
