<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

/**
 * Создание заказа в БД асинхронно.
 *
 * Фронт диспатчит job сразу с публичным ID и кладёт snapshot в Redis
 * (orders:pending:{publicId} TTL 1ч), мгновенно возвращая клиенту
 * страницу "успех" с этим ID. Job в Horizon-воркере записывает заказ
 * в MySQL, обновляет статус в Redis, опционально шлёт email/webhook.
 *
 * Фронт-страница /objednavka/uspech опрашивает /api/order/{id}/status
 * раз в 2 секунды до status=received. Обычно job отрабатывает <100ms.
 */
class CreateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $publicId,
        public readonly array $cart,        // snapshot из App\Support\Cart::snapshot()
        public readonly array $delivery,    // form-data из checkout
        public readonly array $payment,
        public readonly string $locale = 'cs',
        public readonly ?string $ip = null,
    ) {
    }

    public function handle(): void
    {
        $statusKey = "nk:v1:order:pending:{$this->publicId}";

        $order = Order::create([
            'public_id' => $this->publicId,
            'status' => 'received',
            'email' => $this->delivery['email'],
            'phone' => $this->delivery['phone'],
            'first_name' => $this->delivery['first_name'],
            'last_name' => $this->delivery['last_name'],
            'street' => $this->delivery['street'],
            'city' => $this->delivery['city'],
            'zip' => $this->delivery['zip'],
            'country' => $this->delivery['country'] ?? 'CZ',
            'delivery_method' => $this->delivery['delivery_method'],
            'payment_method' => $this->payment['payment_method'],
            'payment_status' => 'unpaid',
            'promo_code' => $this->cart['promo']['code'] ?? null,
            'subtotal' => $this->cart['subtotal'],
            'discount' => $this->cart['discount'],
            'shipping' => 0,
            'total' => $this->cart['total'],
            'items_count' => $this->cart['count'],
            'locale' => $this->locale,
            'marketing_consent' => (bool) ($this->delivery['marketing_consent'] ?? false),
            'ip' => $this->ip,
        ]);

        foreach ($this->cart['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_slug' => $item['slug'],
                'product_name' => $item['name'],
                'size' => $item['size'],
                'unit' => $item['unit'] ?? 'g',
                'qty' => $item['qty'],
                'unit_price' => $item['price'],
                'line_total' => $item['price'] * $item['qty'],
                'snapshot' => $item,
            ]);
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'received',
            'note' => 'Заказ принят из чекаута',
        ]);

        // Обновляем Redis-статус, чтобы фронт мог опросить и убрать loader
        Redis::setex($statusKey, 3600, json_encode([
            'status' => 'received',
            'order_id' => $order->id,
            'public_id' => $order->public_id,
        ]));

        // TODO: dispatch SendOrderConfirmationMail, NotifyAdmin webhook
    }

    public function failed(\Throwable $e): void
    {
        Redis::setex("nk:v1:order:pending:{$this->publicId}", 3600, json_encode([
            'status' => 'failed',
            'error' => $e->getMessage(),
        ]));
    }
}
