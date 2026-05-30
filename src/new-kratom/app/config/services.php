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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
        // OAuth (Socialite) — «Вход через Google».
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/').'/auth/google/callback'),

        // Ссылка «написать отзыв в Google» (Google Business write-review link).
        'review_url' => env('GOOGLE_REVIEW_URL', 'https://search.google.com/local/writereview?placeid=YOUR_PLACE_ID'),
        // Ссылка «смотреть все отзывы в Google».
        'reviews_url' => env('GOOGLE_REVIEWS_URL', 'https://www.google.com/maps/search/Vivadzen+Praha'),
        'rating' => env('GOOGLE_RATING', '4,8'),
        'reviews_count' => env('GOOGLE_REVIEWS_COUNT', '240+'),
    ],

    'facebook' => [
        // OAuth (Socialite) — «Вход через Facebook».
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/').'/auth/facebook/callback'),
    ],

];
