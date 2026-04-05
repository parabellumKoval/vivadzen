<?php

namespace Tests\Feature\Api;

use App\Models\DeliveryReport;
use Backpack\Store\app\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
            'customer_signature' => 'data:image/png;base64,' . base64_encode('customer-signature'),
            'seller_signature' => 'data:image/png;base64,' . base64_encode('seller-signature'),
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
