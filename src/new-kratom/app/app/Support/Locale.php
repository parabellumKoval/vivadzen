<?php

namespace App\Support;

class Locale
{
    public const DEFAULT = 'cs';
    public const SUPPORTED = ['cs', 'en', 'ru', 'uk'];

    /** @var array<string, array{label:string, native:string, flag:string, htmlLang:string}> */
    public static function all(): array
    {
        return [
            'cs' => ['label' => 'Čeština',    'native' => 'CS', 'flag' => '🇨🇿', 'htmlLang' => 'cs-CZ'],
            'en' => ['label' => 'English',    'native' => 'EN', 'flag' => '🇬🇧', 'htmlLang' => 'en'],
            'ru' => ['label' => 'Русский',    'native' => 'RU', 'flag' => '🇷🇺', 'htmlLang' => 'ru'],
            'uk' => ['label' => 'Українська', 'native' => 'UK', 'flag' => '🇺🇦', 'htmlLang' => 'uk'],
        ];
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public static function current(): string
    {
        $locale = (string) app()->getLocale();

        return self::isSupported($locale) ? $locale : self::DEFAULT;
    }

    public static function translate(array|string|null $value, ?string $locale = null): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        $locale ??= self::current();
        $candidates = array_values(array_unique([
            $locale,
            self::DEFAULT,
            ...array_keys($value),
        ]));

        foreach ($candidates as $candidate) {
            $translated = $value[$candidate] ?? null;

            if (is_string($translated) && trim($translated) !== '') {
                return trim($translated);
            }
        }

        return null;
    }

    /**
     * Build a URL for a given path under given locale.
     * Czech (cs) has no prefix; others use /{locale}/...
     */
    public static function url(string $path = '/', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $path = '/' . ltrim($path, '/');

        if ($locale === self::DEFAULT) {
            return $path === '/' ? '/' : $path;
        }

        return '/' . $locale . ($path === '/' ? '' : $path);
    }

    /**
     * Strip the locale prefix from the current request path (used by switcher).
     */
    public static function stripPrefix(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        foreach (self::SUPPORTED as $code) {
            if ($code === self::DEFAULT) {
                continue;
            }
            if ($path === '/' . $code) {
                return '/';
            }
            if (str_starts_with($path, '/' . $code . '/')) {
                return substr($path, strlen($code) + 1);
            }
        }
        return $path;
    }
}
