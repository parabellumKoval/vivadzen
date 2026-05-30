<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ForumTopic extends Model
{
    protected $fillable = [
        'user_id',
        'forum_category_id',
        'title',
        'slug',
        'emoji',
        'body',
        'cover_path',
        'cover_url',
        'cover_source_url',
        'cover_credit',
        'status',
        'moderation_note',
        'is_pinned',
        'is_locked',
        'is_featured',
        'views_count',
        'score',
        'published_at',
        'last_post_at',
        'last_post_user_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'score' => 'integer',
        'published_at' => 'datetime',
        'last_post_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class)->oldest();
    }

    public function approvedPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class)
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function lastPostUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_post_user_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_path) {
            return Storage::disk('public')->url($this->cover_path);
        }

        return $this->cover_url;
    }
}
