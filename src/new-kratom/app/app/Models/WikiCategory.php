<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiCategory extends Model
{
    protected $fillable = [
        'slug', 'title', 'eyebrow', 'description',
        'icon', 'accent', 'position', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(WikiArticle::class)->orderBy('position');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->published();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position');
    }
}
