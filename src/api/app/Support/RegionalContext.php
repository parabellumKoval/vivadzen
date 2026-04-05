<?php

namespace App\Support;

use Backpack\Store\app\Services\Store;
use Illuminate\Http\Request;

class RegionalContext
{
    protected ?string $locale = null;
    protected ?string $region = null;
    protected ?string $storefront = null;
    protected ?string $acceptLanguage = null;

    public function hydrateFromRequest(Request $request, ?string $preferredLocale = null): void
    {
        $headerLocale = $request->header('Accept-Language');
        if ($headerLocale) {
            $this->acceptLanguage = $headerLocale;
        } elseif ($preferredLocale && ! $this->acceptLanguage) {
            $this->acceptLanguage = $preferredLocale;
        }

        $locale = $preferredLocale ?? $this->extractPrimaryLocale($headerLocale);
        $this->setLocale($locale ?? app()->getLocale());

        $region = $request->get('country')
            ?? $request->get('region')
            ?? $request->header('X-Region')
            ?? $request->header('X-Country');

        if ($region !== null) {
            $this->setRegion($region);
        }

        $storefront = $request->get('storefront')
            ?? $request->header('X-Storefront');

        if ($storefront !== null) {
            $this->setStorefront($storefront);
        }
    }

    public function setLocale(?string $locale): void
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized) {
            $this->locale = $normalized;

            if (! $this->acceptLanguage) {
                $this->acceptLanguage = $normalized;
            }
        }
    }

    public function setRegion(?string $region): void
    {
        $this->region = $this->normalizeRegion($region) ?? $this->region;
    }

    public function setStorefront(?string $storefront): void
    {
        $this->storefront = $this->normalizeStorefront($storefront) ?? $this->storefront;
    }

    public function setAcceptLanguage(?string $value): void
    {
        $this->acceptLanguage = $value ?: $this->acceptLanguage;
    }

    public function snapshot(array $overrides = []): array
    {
        return [
            'locale' => $this->normalizeLocale($overrides['locale'] ?? $this->locale ?? app()->getLocale()),
            'region' => $this->normalizeRegion($overrides['region'] ?? $this->region),
            'storefront' => $this->normalizeStorefront(
                $overrides['storefront']
                ?? $this->storefront
                ?? ((class_exists(Store::class) && Store::isStorefrontEnabled()) ? Store::defaultStorefront() : null)
            ),
            'accept_language' => $overrides['accept_language'] ?? $this->acceptLanguage,
        ];
    }

    public function settingsContext(array $overrides = []): array
    {
        $snapshot = $this->snapshot($overrides);
        $context = [];

        if ($snapshot['locale']) {
            $context['locale'] = $snapshot['locale'];
            $context['language'] = $snapshot['locale'];
        }

        if ($snapshot['region']) {
            $context['region'] = $snapshot['region'];
            $context['country'] = $snapshot['region'];
        }

        if ($snapshot['storefront']) {
            $context['storefront'] = $snapshot['storefront'];
        }

        if ($snapshot['accept_language']) {
            $context['accept_language'] = $snapshot['accept_language'];
        }

        return $context;
    }

    protected function extractPrimaryLocale(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        $parts = explode(',', $header);
        $primary = trim($parts[0] ?? '');
        if ($primary === '') {
            return null;
        }

        if (str_contains($primary, ';')) {
            $primary = substr($primary, 0, strpos($primary, ';'));
        }

        return $primary ?: null;
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

    protected function normalizeRegion(?string $region): ?string
    {
        if ($region === null || $region === '') {
            return null;
        }

        $cleaned = preg_replace('/[^a-zA-Z]/', '', $region);
        $code = strtolower(substr((string) $cleaned, 0, 2));

        return strlen($code) === 2 ? $code : null;
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
