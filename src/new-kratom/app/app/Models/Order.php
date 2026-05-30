<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending', 'received', 'paid', 'packed',
        'shipped', 'delivered', 'cancelled', 'refunded',
    ];

    protected $fillable = [
        'user_id', 'public_id', 'status',
        'email', 'phone', 'first_name', 'last_name',
        'street', 'city', 'zip', 'country',
        'delivery_method', 'payment_method', 'payment_status',
        'promo_code', 'subtotal', 'discount', 'shipping', 'total', 'items_count',
        'locale', 'marketing_consent', 'note', 'ip',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'shipping' => 'integer',
        'total' => 'integer',
        'items_count' => 'integer',
        'marketing_consent' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
