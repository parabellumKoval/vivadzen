<?php

return [
    'connection' => env('DUMPER_CONNECTION', env('DB_CONNECTION', 'mysql')),

    'manual' => [
        'disk' => env('DUMPER_MANUAL_DISK', env('DUMPER_DISK', 'local')),
        'directory' => env('DUMPER_MANUAL_DIRECTORY', 'dumps/manual'),
        'filename_prefix' => env('DUMPER_MANUAL_PREFIX', 'manual'),
    ],

    'auto' => [
        'disk' => env('DUMPER_AUTO_DISK', env('DUMPER_DISK', 'local')),
        'base_directory' => env('DUMPER_AUTO_DIRECTORY', 'dumps/auto'),
        'filename_prefix' => env('DUMPER_AUTO_PREFIX', 'auto'),
        'cases' => [
            'monthly_full' => [
                'label' => 'Полная копия раз в месяц',
                'tables' => '*',
                'cron' => '0 4 1 * *',
                'directory' => 'monthly-full',
                'retention' => [
                    'keep_last' => null,
                    'keep_days' => null,
                ],
                'description' => 'Раз в месяц создаётся полный дамп без автоудаления.',
            ],
            'daily_weekly_rotation' => [
                'label' => 'Ежедневная копия c недельным retention',
                'tables' => '*',
                'cron' => '0 2 * * *',
                'directory' => 'daily-week',
                'retention' => [
                    'keep_last' => 7,
                    'keep_days' => 7,
                ],
                'description' => 'Автодампы раз в день, храним только недельную историю.',
            ],
        ],
    ],

    'binaries' => [
        'mysqldump' => env('DUMPER_MYSQLDUMP_PATH', ''),
        'mysql' => env('DUMPER_MYSQL_PATH', 'mysql'),
    ],

    'options' => [
        'disable_column_statistics' => env('DUMPER_DISABLE_COLUMN_STATISTICS'),
    ],

    'metadata_extension' => '.meta.json',
];
