<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    protected $fillable = [
        'product_id', 'author_name', 'author_email',
        'question', 'answer', 'answered_by', 'answered_at',
        'helpful_count', 'status', 'published_at', 'created_by_admin_id',
    ];

    protected $casts = [
        'answered_at'   => 'datetime',
        'published_at'  => 'datetime',
        'helpful_count' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
