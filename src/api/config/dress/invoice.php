<?php

return [
    'default_template' => env('STORE_INVOICE_TEMPLATE', 'cz_default'),
    'locale' => env('STORE_INVOICE_LOCALE', 'cs_CZ'),
    'currency' => env('STORE_INVOICE_CURRENCY', 'CZK'),
    
    'auto_generate_payment_methods' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('STORE_INVOICE_PAYMENT_METHODS', 'bank_transfer'))
    ))),

    'templates' => [
        'cz_default' => [
            'name' => 'Český maket (default)',
            'view' => 'backpack-store::invoices.templates.cz',
            'paper' => 'A4',
            'orientation' => 'portrait',
            'options' => [
                'defaultFont' => 'dejavusans',
                'enable_remote' => true,
            ],
            'margins' => [
                'top' => 0,
                'right' => 12,
                'bottom' => 0,
                'left' => 18,
            ],
        ],
    ],

    'numbering' => [
        'pattern' => env('STORE_INVOICE_NUMBER_PATTERN', 'F{Y}{m}{order_id}'),
        'due_days' => env('STORE_INVOICE_DUE_DAYS', 14),
        'tax_date_offset' => env('STORE_INVOICE_TAX_DATE_OFFSET', 0),
    ],

    'formatting' => [
        'date' => 'd. m. Y',
        'number_decimals' => 2,
        'quantity_decimals' => 2,
        'thousands_separator' => ' ',
        'decimal_separator' => ',',
    ],

    'defaults' => [
        'vat_rate' => (float) env('STORE_INVOICE_DEFAULT_VAT', 0),
        'unit' => env('STORE_INVOICE_DEFAULT_UNIT', 'ks'),
    ],

    'storage' => [
        'disk' => env('STORE_INVOICE_DISK', 'public'),
        'path_mask' => env('STORE_INVOICE_PATH_MASK', 'invoices/{Y}/{m}/{invoice_number}.pdf'),
    ],

    'qr' => [
        'default_format' => env('STORE_INVOICE_QR_FORMAT', 'svg'),
        'cache_disk' => env('STORE_INVOICE_QR_DISK', 'output'),
        'cache_path' => env('STORE_INVOICE_QR_PATH', 'invoices/qr/{order_id}-{hash}.{format}'),
        'cache_ttl' => env('STORE_INVOICE_QR_TTL', 7 * 24 * 60 * 60), // seconds
        'error_correction' => env('STORE_INVOICE_QR_ECL', 'M'),
        'size' => env('STORE_INVOICE_QR_SIZE', 320),
        'message_pattern' => env('STORE_INVOICE_QR_MESSAGE', 'Faktura {invoice_number}'),
    ],

    'signed_url' => [
        'ttl' => env('STORE_INVOICE_SIGNED_TTL', 'P7D'), // ISO8601 duration
        'route' => 'backpack.store.invoices.download-signed',
    ],

    'assets' => [
        'logo_url' => env('STORE_INVOICE_LOGO', 'http://127.0.0.1:8000/sys/company.png'),
        'stamp_url' => env('STORE_INVOICE_STAMP', 'http://127.0.0.1:8000/sys/sign.png'),
        'signature_url' => env('STORE_INVOICE_SIGNATURE', null),
    ],

    'bank_accounts' => [
        'cz' => [
            'iban' => env('STORE_INVOICE_CZ_IBAN'),
            'bic' => env('STORE_INVOICE_CZ_BIC'),
            'account_display' => env('STORE_INVOICE_CZ_ACCOUNT', null),
            'bank_name' => env('STORE_INVOICE_CZ_BANK', null),
        ],
    ],

    'seller' => [
        'name' => 'Vivadzen s.r.o.',
        'ico' => '14396238',
        'dic' => 'CZ14396238',
        'vat_number' => null,
        'address' => [
            'street' => 'Freyova 82/27',
            'city' => 'Praha - Vysočany',
            'zip' => '19000',
            'country' => 'Česká republika',
        ],
        'contacts' => [
            'email' => config('mail.from.address'),
            'phone' => null,
            'website' => config('app.url'),
        ],
    ]
];
