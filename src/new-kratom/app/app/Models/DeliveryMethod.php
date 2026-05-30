<?php

namespace App\Models;

use App\Support\Locale;
use Illuminate\Database\Eloquent\Model;

class DeliveryMethod extends Model
{
    protected $fillable = [
        'code', 'type', 'name', 'description', 'eta', 'address',
        'price', 'free_above', 'is_active', 'position',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'eta' => 'array',
        'address' => 'array',
        'price' => 'integer',
        'free_above' => 'integer',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position');
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
