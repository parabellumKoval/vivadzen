<?php

namespace App\Models;


use App\Support\RegionalContext;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Backpack\Profile\app\Models\Traits\HasProfile;
use Backpack\Profile\app\Models\Profile as ProfileModel;
use Backpack\Helpers\Traits\FormatsUniqAttribute;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable, CanResetPassword, HasProfile, FormatsUniqAttribute;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $regionalContext = $this->resolveRegionalContext();
        $this->notify(new VerifyEmailNotification($regionalContext));
    }

    protected function resolveRegionalContext(): ?array
    {
        if (class_exists(RegionalContext::class) && app()->bound(RegionalContext::class)) {
            return app(RegionalContext::class)->snapshot();
        }

        return null;
    }

    protected function preferredVerificationLocale(): ?string
    {
        foreach ($this->verificationLocaleCandidates() as $candidate) {
            $normalized = $this->normalizeLocale($candidate);

            if ($normalized) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @return array<int, string|null>
     */
    protected function verificationLocaleCandidates(): array
    {
        $candidates = [];

        // Priority 1: Locale from current request context (RegionalContext from Accept-Language header)
        if (class_exists(RegionalContext::class) && app()->bound(RegionalContext::class)) {
            $snapshot = app(RegionalContext::class)->snapshot();
            if (!empty($snapshot['locale'])) {
                $candidates[] = $snapshot['locale'];
            }
        }

        // Priority 2: Application locale
        $candidates[] = app()->getLocale();
        
        // Priority 3: User's own locale preference
        $candidates[] = $this->locale ?? null;
        
        // Priority 4: Default application locale
        $candidates[] = config('app.locale');
        
        // Priority 5 (LOWEST): Profile locale - should rarely be used
        // Note: profile locale might be set from sponsor during referral signup,
        // so we keep it as a very last resort
        $candidates[] = $this->profile?->locale;

        return array_filter($candidates, static fn ($value) => $value !== null && $value !== '');
    }

    protected function normalizeLocale(?string $locale): ?string
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        $normalized = strtolower(str_replace('_', '-', $locale));

        if (str_contains($normalized, '-')) {
            $normalized = explode('-', $normalized)[0];
        }

        $supported = (array) config('app.supported_locales', []);

        if (!empty($supported) && !in_array($normalized, $supported, true)) {
            return null;
        }

        return $normalized;
    }

    public function toArray(): array
    {
        $profile = $this->profile;

        $avatar = $profile?->avatarUrl();
        $billing = $profile
            ? ProfileModel::fillAddress($profile->getMetaSection('billing'))
            : ProfileModel::fillAddress([]);
        $shipping = $profile
            ? ProfileModel::fillAddress($profile->getMetaSection('shipping'))
            : ProfileModel::fillAddress([]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'first_name' => $profile?->first_name,
            'last_name' => $profile?->last_name,
            'phone' => $profile?->phone,
            'avatar' => $avatar,
            'avatar_url' => $avatar,
            'referral_code' => $profile?->referral_code,
            'balance' => $this->walletBalance->balance ?? 0,
            'discount_percent' => $this->personal_discount_percent,
            'personal_discount_percent' => $this->personal_discount_percent,
            'billing' => $billing,
            'shipping' => $shipping,
            'meta' => $profile?->metaWithoutOther() ?? [],
        ];
    }

    public function toReviewArray() {
        $profile = $this->profile;

        $avatar = $profile?->avatarUrl();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->name,
            'last_name' => $this->name, 
            'photo' => $avatar, 
            'email' => $this->email,
            'phone' => $profile?->phone
        ];
    }

    public function toOrderArray() {
        return [
            'first_name' => $this->name,
            'last_name' => $this->name, 
            'phone' => null, 
            'email' => $this->email
        ];
    }

    protected function personalDiscountPercent(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (method_exists($this, 'relationLoaded') && method_exists($this, 'loadMissing') && !$this->relationLoaded('profile')) {
                    $this->loadMissing('profile');
                }

                $percent = $this->profile->discount_percent ?? 0.0;

                return (float) $percent;
            }
        );
    }

    public function getAvatarAttribute() {
        if(!$this->profile)
            return null;

        return $this->profile->avatarUrl();
    }

    public function getUniqStringAttribute(): string
    {
        $profile = $this->profile;

        return $this->formatUniqString([
            '#'.$this->id,
            $this->name,
            $this->email,
            $profile?->phone,
            $profile?->country_code,
            $this->email_verified_at ? 'verified' : 'not verified',
        ]);
    }

    public function getUniqHtmlAttribute(): string
    {
        $profile = $this->profile;

        $headline = $this->formatUniqString([
            '#'.$this->id,
            $this->name ?: $this->email,
        ]);

        return $this->formatUniqHtml($headline, [
            $this->email,
            $profile?->phone,
            $profile?->country_code,
            $this->email_verified_at ? 'verified' : 'not verified',
        ]);
    }
}
