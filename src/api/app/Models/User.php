<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Backpack\Profile\app\Models\Traits\HasProfile;
use Backpack\Profile\app\Models\Profile as ProfileModel;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable, CanResetPassword, HasProfile;

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
}
