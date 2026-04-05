<?php

namespace App\Support;

use Backpack\Store\app\Services\Store;
use Backpack\Settings\Facades\Settings;

class StorefrontSettings
{
    public function current(): ?string
    {
        if (class_exists(Store::class) && Store::isStorefrontEnabled()) {
            return Store::storefront();
        }

        if (app()->bound(RegionalContext::class)) {
            return app(RegionalContext::class)->snapshot()['storefront'] ?? null;
        }

        return $this->normalizeStorefront(
            request()->header(Store::storefrontHeaderName()) ?? request()->get(Store::storefrontRequestKey())
        );
    }

    public function get(string $key, $default = null, array $context = [], ?string $storefront = null)
    {
        $resolvedStorefront = $this->normalizeStorefront($storefront ?? $this->current());
        $overrideKey = $this->overrideKey($key, $resolvedStorefront);

        if ($overrideKey !== null) {
            $missing = '__STOREFRONT_SETTINGS_MISSING__:' . uniqid('', true);
            $overrideValue = Settings::get($overrideKey, $missing, $context);

            if ($overrideValue !== $missing) {
                return $overrideValue;
            }
        }

        return Settings::get($key, $default, $context);
    }

    public function overrideKey(string $key, ?string $storefront = null): ?string
    {
        $resolvedStorefront = $this->normalizeStorefront($storefront ?? $this->current());
        if ($resolvedStorefront === null) {
            return null;
        }

        $map = class_exists(Store::class) ? Store::storefrontSettingsOverrides() : [];

        return $map[$resolvedStorefront][$key] ?? null;
    }

    protected function normalizeStorefront(?string $storefront): ?string
    {
        if ($storefront === null || $storefront === '') {
            return null;
        }

        $cleaned = strtolower(trim((string) $storefront));
        $cleaned = preg_replace('/[^a-z0-9_-]/', '', $cleaned);

        return $cleaned !== '' ? $cleaned : null;
    }
}
