<?php

namespace App\Models;

use App\Support\Locale;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'type', 'name', 'description', 'fee',
        'is_active', 'position', 'delivery_method_codes', 'settings',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'delivery_method_codes' => 'array',
        'settings' => 'array',
        'fee' => 'integer',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position');
    }

    public function isCompatibleWith(?string $deliveryCode): bool
    {
        $whitelist = $this->delivery_method_codes ?? [];
        return empty($whitelist) || in_array($deliveryCode, $whitelist, true);
    }

    public function localized(?string $field, ?string $locale = null): string
    {
        $value = $this->{$field};
        if (! is_array($value)) {
            return (string) ($value ?? '');
        }
        $locale ??= Locale::current();
        return $value[$locale] ?? $value['cs'] ?? reset($value) ?: '';
    }
}
