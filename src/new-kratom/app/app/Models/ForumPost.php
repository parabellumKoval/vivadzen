<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    protected $fillable = [
        'forum_topic_id',
        'user_id',
        'parent_id',
        'body',
        'status',
        'moderation_note',
        'score',
        'published_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'published_at' => 'datetime',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumPostVote::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ForumPostReaction::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function recalculateScore(): void
    {
        $this->forceFill([
            'score' => (int) $this->votes()->sum('value'),
        ])->save();
    }
}
