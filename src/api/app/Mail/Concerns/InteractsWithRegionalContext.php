<?php

namespace App\Mail\Concerns;

use App\Support\RegionalContext;

trait InteractsWithRegionalContext
{
    protected array $regionalContext = [
        'locale' => null,
        'region' => null,
        'accept_language' => null,
    ];

    protected function initializeRegionalContext(?array $overrides = null): void
    {
        $context = $overrides ?? $this->resolveCurrentContext();
        $this->regionalContext = array_merge($this->regionalContext, array_filter($context, fn ($value) => $value !== null && $value !== ''));

        $this->syncRegionalContext();
    }

    protected function resolveCurrentContext(): array
    {
        if (app()->bound(RegionalContext::class)) {
            return app(RegionalContext::class)->snapshot();
        }

        return [
            'locale' => app()->getLocale(),
            'region' => null,
            'accept_language' => null,
        ];
    }

    protected function hasRegionalLocale(): bool
    {
        return (bool) $this->regionalContext['locale'];
    }

    protected function setRegionalLocale(?string $locale): void
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized) {
            $this->regionalContext['locale'] = $normalized;
            if (empty($this->regionalContext['accept_language'])) {
                $this->regionalContext['accept_language'] = $normalized;
            }
            $this->syncRegionalContext();
        }
    }

    protected function regionalLocale(): ?string
    {
        return $this->regionalContext['locale'] ?: null;
    }

    protected function setRegionalRegion(?string $region): void
    {
        $normalized = $this->normalizeRegion($region);

        if ($normalized) {
            $this->regionalContext['region'] = $normalized;
            $this->syncRegionalContext();
        }
    }

    protected function regionalRegion(): ?string
    {
        return $this->regionalContext['region'] ?: null;
    }

    protected function regionalSettingsContext(): array
    {
        $context = [];

        if ($this->regionalContext['locale']) {
            $context['locale'] = $this->regionalContext['locale'];
            $context['language'] = $this->regionalContext['locale'];
        }

        if ($this->regionalContext['region']) {
            $context['region'] = $this->regionalContext['region'];
            $context['country'] = $this->regionalContext['region'];
        }

        if ($this->regionalContext['accept_language']) {
            $context['accept_language'] = $this->regionalContext['accept_language'];
        }

        return $context;
    }

    protected function regionalViewData(array $data = []): array
    {
        return array_merge(['mailContacts' => $this->resolveMailContacts()], $data);
    }

    public function __wakeup(): void
    {
        $this->syncRegionalContext();
    }

    protected function resolveMailContacts(): array
    {
        $context = $this->regionalSettingsContext();

        $value = function (string $key, ?string $fallback = null) use ($context) {
            $resolved = \Settings::get($key, null, $context);

            if (is_array($resolved)) {
                $resolved = reset($resolved) ?: null;
            }

            $resolved = is_string($resolved) || is_numeric($resolved)
                ? trim((string) $resolved)
                : '';

            return $resolved !== '' ? $resolved : $fallback;
        };

        $address = $value('site.contacts.address', __('contacts.address'));
        $phone = $value('site.contacts.phone', __('contacts.phone'));
        $email = $value('site.contacts.email', __('contacts.email'));

        $social = array_filter([
            'instagram' => $value('site.contacts.social.instagram', __('contacts.instagram')),
            'viber' => $value('site.contacts.social.viber', __('contacts.viber')),
            'telegram' => $value('site.contacts.social.telegram', __('contacts.telegram')),
            'whatsapp' => $value('site.contacts.social.whatsapp', __('contacts.whatsapp')),
        ]);

        return [
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'social' => $social,
        ];
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
        $code = substr($cleaned, 0, 2);

        return strlen($code) === 2 ? strtolower($code) : null;
    }

    protected function syncRegionalContext(): void
    {
        if ($this->regionalContext['locale']) {
            app()->setLocale($this->regionalContext['locale']);
        }

        if (class_exists(RegionalContext::class)) {
            $service = app(RegionalContext::class);
            $service->setLocale($this->regionalContext['locale']);
            $service->setRegion($this->regionalContext['region']);
            $service->setAcceptLanguage($this->regionalContext['accept_language']);
        }

        $this->applyMailableLocale($this->regionalContext['locale']);
    }

    protected function applyMailableLocale(?string $locale): void
    {
        if ($locale && property_exists($this, 'locale')) {
            $this->locale = $locale;
        }
    }
}
