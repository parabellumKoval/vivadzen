<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'author_name', 'author_email', 'rating',
        'body', 'verified_purchase', 'helpful_count',
        'status', 'published_at', 'created_by_admin_id',
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
        'helpful_count'     => 'integer',
        'rating'            => 'integer',
        'published_at'      => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductReviewImage::class)->orderBy('position');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
