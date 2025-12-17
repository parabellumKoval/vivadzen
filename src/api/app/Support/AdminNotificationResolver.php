<?php

namespace App\Support;

class AdminNotificationResolver
{
    public static function resolve(?string $countryCode = null): array
    {
        $emails = [];
        $country = strtolower(trim((string) ($countryCode ?? '')));

        if ($country !== '') {
            $mapping = collect((array) \Settings::get('core.order_emails.per_country', []));

            $emails = $mapping->filter(function ($row) use ($country) {
                $rowCountry = strtolower(trim((string) ($row['country'] ?? '')));
                $email = trim((string) ($row['email'] ?? ''));

                return $rowCountry === $country && $email !== '';
            })->map(function ($row) {
                return trim((string) ($row['email'] ?? ''));
            })->filter()->unique()->values()->all();
        }

        if (empty($emails)) {
            $fallback = self::fallbackEmail();
            if ($fallback !== '') {
                $emails = [$fallback];
            }
        }

        return $emails;
    }

    protected static function fallbackEmail(): string
    {
        $candidates = [
            \Settings::get('core.order_emails.default'),
            config('app.admin_email'),
            env('ADMIN_MAIL'),
            'shop@vivadzen.com',
        ];

        foreach ($candidates as $email) {
            $value = trim((string) ($email ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
