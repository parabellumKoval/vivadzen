<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class WikiArticle extends Model
{
    protected $fillable = [
        'wiki_category_id',
        'slug', 'title', 'excerpt', 'body',
        'cover_path', 'cover_url', 'cover_alt',
        'seo_keyword', 'seo_secondary_keywords', 'seo_search_intent',
        'seo_volume_estimate', 'seo_meta_title', 'seo_meta_description',
        'reading_time_minutes', 'position', 'status', 'published_at',
    ];

    protected $casts = [
        'seo_secondary_keywords' => 'array',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WikiCategory::class, 'wiki_category_id');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            WikiArticle::class,
            'wiki_article_related',
            'wiki_article_id',
            'related_id',
        )->withPivot('position')->orderBy('wiki_article_related.position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function coverDisplayUrl(): ?string
    {
        if ($this->cover_path) {
            return Storage::disk('public')->url($this->cover_path);
        }
        return $this->cover_url;
    }

    public function metaTitle(): string
    {
        return $this->seo_meta_title ?: $this->title . ' | Vivadzen Průvodce';
    }

    public function metaDescription(): ?string
    {
        return $this->seo_meta_description ?: $this->excerpt;
    }
}
