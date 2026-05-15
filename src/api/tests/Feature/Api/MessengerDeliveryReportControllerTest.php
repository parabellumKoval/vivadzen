<?php

namespace Tests\Feature\Api;

use App\Models\DeliveryReport;
use Backpack\Store\app\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessengerDeliveryReportControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('ak_settings', 'group')) {
            Schema::table('ak_settings', function (Blueprint $table): void {
                $table->string('group')->nullable();
            });
        }

        Schema::dropIfExists('ak_delivery_reports');
        Schema::dropIfExists('ak_orders');

        Schema::create('ak_orders', function (Blueprint $table): void {
            $table->id();
            $table->char('country_code', 2)->index();
            $table->nullableUuidMorphs('orderable');
            $table->string('code', 64)->index();
            $table->string('status', 30)->default('new');
            $table->string('pay_status', 30)->default('waiting');
            $table->string('delivery_status', 30)->default('waiting');
            $table->float('price')->default(0);
            $table->char('currency_code', 3)->index();
            $table->decimal('fx_rate', 16, 8);
            $table->decimal('subtotal', 14, 2)->nullable();
            $table->decimal('discount_total', 14, 2)->nullable();
            $table->decimal('shipping_total', 14, 2)->nullable();
            $table->decimal('tax_total', 14, 2)->nullable();
            $table->decimal('grand_total', 14, 2)->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_delivery_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 32)->default('messenger');
            $table->string('order_number', 64);
            $table->string('recipient_fullname');
            $table->string('recipient_actual_fullname')->nullable();
            $table->string('id_card_number', 100);
            $table->string('id_card_type', 32);
            $table->string('handover_place');
            $table->timestamp('handover_datetime');
            $table->string('sender_fullname');
            $table->longText('customer_signature')->nullable();
            $table->longText('seller_signature')->nullable();
            $table->json('payload')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('order_found')->default(false);
            $table->boolean('delivery_status_applied')->default(false);
            $table->boolean('pay_status_applied')->default(false);
            $table->boolean('order_status_applied')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'order_number'], 'ak_delivery_reports_provider_order_number_unique');
        });

        \Settings::set('shipping.messenger.reporting.api_key', 'test-messenger-key', ['cast' => 'string']);
        \Settings::set('shipping.messenger.reporting.apply_delivery_status', true, ['cast' => 'bool']);
        \Settings::set('shipping.messenger.reporting.apply_pay_status', true, ['cast' => 'bool']);
        \Settings::set('shipping.messenger.reporting.apply_order_status', true, ['cast' => 'bool']);

        Storage::fake('uploads');
    }

    public function test_webhook_stores_report_and_updates_order_statuses(): void
    {
        $order = $this->createOrder('202600005');

        $response = $this->postMessengerPayload([
            $this->payload('202600005'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('accepted', 1);

        $report = DeliveryReport::query()->first();

        $this->assertNotNull($report);
        $this->assertSame('202600005', $report->order_number);
        $this->assertStringStartsWith('/uploads/delivery-reports/messenger/signatures/', $report->customer_signature);
        $this->assertStringStartsWith('/uploads/delivery-reports/messenger/signatures/', $report->seller_signature);
        $this->assertSame($report->customer_signature, $report->payload['customer_signature']);
        $this->assertSame($report->seller_signature, $report->payload['seller_signature']);
        Storage::disk('uploads')->assertExists(ltrim(str_replace('/uploads/', '', $report->customer_signature), '/'));
        Storage::disk('uploads')->assertExists(ltrim(str_replace('/uploads/', '', $report->seller_signature), '/'));
        $this->assertTrue($report->order_found);
        $this->assertTrue($report->delivery_status_applied);
        $this->assertTrue($report->pay_status_applied);
        $this->assertTrue($report->order_status_applied);
        $this->assertNotNull($report->order);
        $this->assertTrue($report->order->is($order));

        $order->refresh();
        $this->assertSame('delivered', $order->delivery_status);
        $this->assertSame('paied', $order->pay_status);
        $this->assertSame('completed', $order->status);
    }

    public function test_webhook_can_skip_order_status_updates_via_settings(): void
    {
        \Settings::set('shipping.messenger.reporting.apply_delivery_status', false, ['cast' => 'bool']);
        \Settings::set('shipping.messenger.reporting.apply_pay_status', false, ['cast' => 'bool']);
        \Settings::set('shipping.messenger.reporting.apply_order_status', false, ['cast' => 'bool']);

        $order = $this->createOrder('202600006');

        $response = $this->postMessengerPayload([
            $this->payload('202600006'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok');

        $report = DeliveryReport::query()->firstOrFail();
        $this->assertTrue($report->order_found);
        $this->assertFalse($report->delivery_status_applied);
        $this->assertFalse($report->pay_status_applied);
        $this->assertFalse($report->order_status_applied);

        $order->refresh();
        $this->assertSame('waiting', $order->delivery_status);
        $this->assertSame('waiting', $order->pay_status);
        $this->assertSame('new', $order->status);
    }

    public function test_webhook_returns_409_for_duplicate_order_number(): void
    {
        $this->postMessengerPayload([
            $this->payload('202600007'),
        ])->assertOk();

        $duplicateResponse = $this->postMessengerPayload([
            $this->payload('202600007'),
        ]);

        $duplicateResponse->assertStatus(409)
            ->assertJsonPath('status', 'skipped')
            ->assertJsonPath('skipped', 1);

        $this->assertSame(1, DeliveryReport::query()->count());
    }

    public function test_webhook_accepts_documented_json_array_payload_and_trims_configured_key(): void
    {
        \Settings::set('shipping.messenger.reporting.api_key', '  test-messenger-key  ', ['cast' => 'string']);

        $response = $this->withHeaders([
            'X-API-KEY' => 'test-messenger-key',
        ])->postJson('/api/delivery-reports/messenger', [
            $this->payload('202600008'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('accepted', 1);

        $this->assertDatabaseHas('ak_delivery_reports', [
            'provider' => 'messenger',
            'order_number' => '202600008',
        ]);
    }

    public function test_webhook_accepts_env_config_key_when_settings_key_is_stale(): void
    {
        \Settings::set('shipping.messenger.reporting.api_key', 'stale-db-key', ['cast' => 'string']);
        config()->set('services.messenger.delivery_reporting_api_key', 'env-config-key');

        $response = $this->withHeaders([
            'X-API-KEY' => 'env-config-key',
        ])->postJson('/api/delivery-reports/messenger', [
            $this->payload('202600009'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('accepted', 1);
    }

    public function test_delivery_report_model_converts_data_uri_signatures_to_files(): void
    {
        $report = DeliveryReport::query()->create([
            'provider' => 'messenger',
            'order_number' => '202600010',
            'recipient_fullname' => 'Frantisek Klika',
            'id_card_number' => 'OP123456789',
            'id_card_type' => 'op',
            'handover_place' => 'Praha, Libinska 1',
            'handover_datetime' => '2026-03-18 15:45:00',
            'sender_fullname' => 'Tomas Kral',
            'customer_signature' => 'data:image/png;base64,'.base64_encode('customer-signature'),
            'seller_signature' => 'data:image/png;base64,'.base64_encode('seller-signature'),
        ]);

        $this->assertStringStartsWith('/uploads/delivery-reports/messenger/signatures/', $report->customer_signature);
        $this->assertStringStartsWith('/uploads/delivery-reports/messenger/signatures/', $report->seller_signature);
        Storage::disk('uploads')->assertExists(ltrim(str_replace('/uploads/', '', $report->customer_signature), '/'));
        Storage::disk('uploads')->assertExists(ltrim(str_replace('/uploads/', '', $report->seller_signature), '/'));
    }

    public function test_delivery_report_model_keeps_admin_image_field_values_as_upload_paths(): void
    {
        $report = DeliveryReport::query()->create([
            'provider' => 'messenger',
            'order_number' => '202600011',
            'recipient_fullname' => 'Frantisek Klika',
            'id_card_number' => 'OP123456789',
            'id_card_type' => 'op',
            'handover_place' => 'Praha, Libinska 1',
            'handover_datetime' => '2026-03-18 15:45:00',
            'sender_fullname' => 'Tomas Kral',
            'customer_signature' => 'http://localhost:8000/uploads/delivery-reports/messenger/signatures/2026/05/customer.png',
            'seller_signature' => 'https://dashboard.vivadzen.com/uploads/delivery-reports/messenger/signatures/2026/05/seller.png',
        ]);

        $this->assertSame('/uploads/delivery-reports/messenger/signatures/2026/05/customer.png', $report->customer_signature);
        $this->assertSame('/uploads/delivery-reports/messenger/signatures/2026/05/seller.png', $report->seller_signature);
    }

    protected function createOrder(string $code): Order
    {
        return Order::withoutEvents(function () use ($code) {
            return Order::query()->forceCreate([
                'country_code' => 'cz',
                'code' => $code,
                'status' => 'new',
                'pay_status' => 'waiting',
                'delivery_status' => 'waiting',
                'price' => 199.99,
                'currency_code' => 'CZK',
                'fx_rate' => 1,
                'subtotal' => 199.99,
                'discount_total' => 0,
                'shipping_total' => 0,
                'tax_total' => 0,
                'grand_total' => 199.99,
                'info' => [
                    'user' => [
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'phone' => '+420111222333',
                        'email' => 'test@example.com',
                    ],
                ],
            ]);
        });
    }

    protected function payload(string $orderNumber): array
    {
        return [
            'order_number' => $orderNumber,
            'recipient_fullname' => 'Frantisek Klika',
            'recipient_actual_fullname' => 'Klikova',
            'id_card_number' => 'OP123456789',
            'id_card_type' => 'op',
            'handover_place' => 'Praha, Libinska 1',
            'handover_datetime' => '2026-03-18 15:45:00',
            'sender_fullname' => 'Tomas Kral',
            'customer_signature' => 'data:image/png;base64,'.base64_encode('customer-signature'),
            'seller_signature' => 'data:image/png;base64,'.base64_encode('seller-signature'),
        ];
    }

    protected function postMessengerPayload(array $payload)
    {
        return $this->withHeaders([
            'X-API-KEY' => 'test-messenger-key',
        ])->postJson('/api/delivery-reports/messenger', [
            'reports' => $payload,
        ]);
    }
}
