<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://api.example.com/api/auth/oauth/google/callback'),
    ],
    'facebook' => [
        'client_id' => env('FB_CLIENT_ID'),
        'client_secret' => env('FB_CLIENT_SECRET'),
        'redirect' => env('FB_REDIRECT_URI', 'https://api.example.com/api/auth/oauth/facebook/callback'),
    ],

    'adulto' => [
        'public_key' => env('ADULTO_PUBLIC_KEY'),
        'private_key' => env('ADULTO_PRIVATE_KEY'),
        'verify_url' => env('ADULTO_VERIFY_URL', 'https://api.result.adulto.cz'),
        'timeout' => (int) env('ADULTO_TIMEOUT', 10),
    ],

    'messenger' => [
        'delivery_reporting_api_key' => env('MESSENGER_DELIVERY_REPORTING_API_KEY'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'init_data_max_age' => (int) env('TELEGRAM_INIT_DATA_MAX_AGE', 604800),
    ],
];
