<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'short', 'description',
        'color_slug', 'strain_slug', 'form_slug', 'origin', 'origin_i18n',
        'mitragynin', 'h7mg', 'purity', 'batch', 'tested_at',
        'grind', 'grind_i18n', 'rating', 'reviews_count', 'questions_count',
        'in_stock', 'badge', 'main_image', 'position', 'published_at',
    ];

    protected $casts = [
        'name' => 'array',
        'short' => 'array',
        'description' => 'array',
        'origin_i18n' => 'array',
        'grind_i18n' => 'array',
        'badge' => 'array',
        'in_stock' => 'boolean',
        'mitragynin' => 'decimal:2',
        'h7mg' => 'decimal:3',
        'purity' => 'decimal:2',
        'rating' => 'decimal:1',
        'tested_at' => 'date',
        'published_at' => 'datetime',
        'position' => 'integer',
        'reviews_count' => 'integer',
        'questions_count' => 'integer',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('size');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function coa(): HasMany
    {
        return $this->hasMany(CoaFile::class)->orderByDesc('tested_at');
    }

    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class, 'product_taxonomy');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
