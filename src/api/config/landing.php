<?php

return [
    'promo_subscribe' => [
        'feedback_type' => env('LANDING_PROMO_FEEDBACK_TYPE', 'landing_kratom_local_sale'),
        'promo_code' => env('LANDING_PROMO_CODE', ''),
        'cta_url' => env('LANDING_PROMO_CTA_URL', env('CLIENT_URL', env('FRONT_URL', env('APP_URL')))),
    ],
];
