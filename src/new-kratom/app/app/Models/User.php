<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar_path',
        'marketing_consent',
        'locale',
        'forum_slug',
        'forum_signature',
        'forum_reputation',
        'forum_avatar_color',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url', 'has_password'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'blocked_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->orderBy('id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function forumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class)->latest('last_post_at');
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class)->latest();
    }

    public function forumPostVotes(): HasMany
    {
        return $this->hasMany(ForumPostVote::class);
    }

    public function forumPostReactions(): HasMany
    {
        return $this->hasMany(ForumPostReaction::class);
    }

    public function hasPassword(): bool
    {
        return ! empty($this->password);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function getHasPasswordAttribute(): bool
    {
        return $this->hasPassword();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        // Remote avatars from social providers are stored as absolute URLs.
        if (str_starts_with($this->avatar_path, 'http')) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }
}
