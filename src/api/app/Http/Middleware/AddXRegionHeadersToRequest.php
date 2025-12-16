<?php

namespace App\Http\Middleware;

use App\Support\RegionalContext;
use Closure;
use Illuminate\Http\Request;

class AddXRegionHeadersToRequest
{
    public function handle(Request $request, Closure $next)
    {
        $country = $this->normalizeCountry($request->header('X-Region') ?? $request->header('X-Country'));
        $lang = $this->preferredLanguage($request->header('Accept-Language'));

        $payload = [];

        if ($country) {
            $payload['country'] = $country;
        }

        if ($lang) {
            $payload['lang'] = $lang;
            app()->setLocale($lang);
        }

        if (!empty($payload)) {
            $request->merge($payload);
        }

        if (app()->bound(RegionalContext::class)) {
            app(RegionalContext::class)->hydrateFromRequest($request, $lang);
        }

        return $next($request);
    }

    protected function preferredLanguage(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        $parts = explode(',', $header);
        $primary = trim($parts[0] ?? '');

        if ($primary === '') {
            return null;
        }

        $primary = strtolower($primary);

        if (str_contains($primary, ';')) {
            $primary = substr($primary, 0, strpos($primary, ';'));
        }

        $segments = preg_split('/[-_]/', $primary);
        $language = $segments[0] ?? null;

        if (!$language) {
            return null;
        }

        $supported = (array) config('app.supported_locales', []);

        if (!empty($supported) && !in_array($language, $supported, true)) {
            return null;
        }

        return $language;
    }

    protected function normalizeCountry(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $cleaned = preg_replace('/[^a-zA-Z]/', '', $value);
        // $code = strtoupper(substr($cleaned, 0, 2));
        $code = substr($cleaned, 0, 2);

        return strlen($code) === 2 ? $code : null;
    }
}
