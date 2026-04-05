<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\Store\app\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReport extends Model
{
    use CrudTrait;

    protected $table = 'ak_delivery_reports';

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'is_test' => 'bool',
        'order_found' => 'bool',
        'delivery_status_applied' => 'bool',
        'pay_status_applied' => 'bool',
        'order_status_applied' => 'bool',
        'handover_datetime' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_number', 'code');
    }

    public function resolveOrder(): ?Order
    {
        return Order::query()
            ->where('code', $this->order_number)
            ->latest('id')
            ->first();
    }

    public function orderLink(): string
    {
        $order = $this->resolveOrder();

        if (! $order) {
            return '<span class="badge badge-secondary">Не найден</span>';
        }

        $label = e(sprintf('#%d / %s', $order->id, $order->code ?? ''));
        $url = backpack_url("order/{$order->id}/show");

        return sprintf('<a href="%s" target="_blank">%s</a>', e($url), $label);
    }

    public function customerSignaturePreview(): string
    {
        return $this->renderSignaturePreview($this->customer_signature);
    }

    public function sellerSignaturePreview(): string
    {
        return $this->renderSignaturePreview($this->seller_signature);
    }

    public function payloadPreview(): string
    {
        $payload = $this->payload;

        if (! is_array($payload) || $payload === []) {
            return '<span class="text-muted">Пусто</span>';
        }

        return sprintf(
            '<pre style="white-space:pre-wrap;max-width:100%%;margin:0;">%s</pre>',
            e(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        );
    }

    protected function renderSignaturePreview(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '<span class="text-muted">Нет подписи</span>';
        }

        return sprintf(
            '<img src="%s" alt="signature" style="max-width:320px;max-height:180px;border:1px solid #d8dbe0;border-radius:6px;background:#fff;">',
            e($value)
        );
    }
}
