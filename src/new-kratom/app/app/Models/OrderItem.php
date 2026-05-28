<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_slug', 'product_name',
        'size', 'unit', 'qty', 'unit_price', 'line_total', 'snapshot',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'size' => 'integer',
        'qty' => 'integer',
        'unit_price' => 'integer',
        'line_total' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
