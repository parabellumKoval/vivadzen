<?php

use Laravel\Sanctum\Sanctum;

return [
    // Admin API использует personal access tokens в Authorization header,
    // поэтому stateful SPA-cookie домены здесь не нужны по умолчанию.
    'stateful' => array_values(array_filter(array_map(
        static fn (string $domain) => trim($domain),
        explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1'))
    ))),
    'guard' => ['web'],
    'expiration' => 60 * 24 * 7, // токены живут неделю
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
