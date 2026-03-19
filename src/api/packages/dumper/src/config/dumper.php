<?php

return [
    'connection' => env('DUMPER_CONNECTION', env('DB_CONNECTION', 'mysql')),

    'manual' => [
        'disk' => env('DUMPER_MANUAL_DISK', env('DUMPER_DISK', 'local')),
        'directory' => env('DUMPER_MANUAL_DIRECTORY', 'dumps/manual'),
        'filename_prefix' => env('DUMPER_MANUAL_PREFIX', 'manual'),
        'sync_to_remote' => env('DUMPER_MANUAL_SYNC_TO_REMOTE', false),
    ],

    'auto' => [
        'disk' => env('DUMPER_AUTO_DISK', env('DUMPER_DISK', 'local')),
        'base_directory' => env('DUMPER_AUTO_DIRECTORY', 'dumps/auto'),
        'filename_prefix' => env('DUMPER_AUTO_PREFIX', 'auto'),
        'cases' => [
            'monthly_full' => [
                'label' => 'Ежемесячный дамп',
                'tables' => '*',
                'schedule' => 'monthly',
                'directory' => 'monthly-full',
                'filename_prefix' => 'monthly',
                'sync_to_remote' => true,
                'retention' => [
                    'keep_last' => null,
                    'keep_days' => null,
                ],
                'description' => 'Раз в месяц создаётся полный дамп без автоудаления.',
            ],
            'daily_weekly_rotation' => [
                'label' => 'Ежедневный дамп',
                'tables' => '*',
                'schedule' => 'daily',
                'directory' => 'daily-week',
                'filename_prefix' => 'daily',
                'sync_to_remote' => true,
                'retention' => [
                    'keep_last' => 7,
                    'keep_days' => 7,
                ],
                'description' => 'Автодампы раз в день, храним только недельную историю.',
            ],
        ],
    ],

    'remote' => [
        'enabled' => env('DUMPER_REMOTE_ENABLED', false),
        'enabled_providers' => array_values(array_filter(array_map(
            static fn (string $provider): string => trim($provider),
            explode(',', (string) env('DUMPER_REMOTE_ENABLED_PROVIDERS', ''))
        ))),
        'base_directory' => env('DUMPER_REMOTE_BASE_DIRECTORY', 'database-backups'),
        'providers' => [
            'bunny' => [
                'api_key' => env('DUMPER_BUNNY_KEY', env('BUNNY_KEY')),
                'storage_zone' => env('DUMPER_BUNNY_STORAGE_ZONE', env('BUNNY_ZONE')),
                'region' => env('DUMPER_BUNNY_REGION', 'de'),
                'base_directory' => env('DUMPER_BUNNY_BASE_DIRECTORY', ''),
            ],
        ],
    ],

    'binaries' => [
        'mysqldump' => env('DUMPER_MYSQLDUMP_PATH', ''),
        'mysql' => env('DUMPER_MYSQL_PATH', 'mysql'),
    ],

    'options' => [
        'disable_column_statistics' => env('DUMPER_DISABLE_COLUMN_STATISTICS'),
        'disable_ssl' => env('DUMPER_DISABLE_SSL'),
        'no_tablespaces' => env('DUMPER_NO_TABLESPACES', true),
    ],

    'metadata_extension' => '.meta.json',
];
