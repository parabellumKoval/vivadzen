<?php

return [
    'paths' => ['admin-api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map(
        static fn (string $origin) => trim($origin),
        explode(',', (string) env('ADMIN_FRONT_URLS', env('ADMIN_FRONT_URL', 'http://localhost:3002,http://127.0.0.1:3002')))
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
