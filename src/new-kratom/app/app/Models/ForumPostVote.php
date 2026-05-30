<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumPostVote extends Model
{
    protected $fillable = [
        'forum_post_id',
        'user_id',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
