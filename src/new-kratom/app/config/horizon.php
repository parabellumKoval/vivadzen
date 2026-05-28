<?php

return [
    'domain' => env('HORIZON_DOMAIN'),
    'path' => env('HORIZON_PATH', 'horizon'),
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', 'horizon:'),
    'middleware' => ['web'],
    'waits' => ['redis:default' => 60, 'redis:orders' => 30, 'redis:images' => 120],
    'trim' => [
        'recent' => 60, 'pending' => 60, 'completed' => 60,
        'recent_failed' => 10080, 'failed' => 10080, 'monitored' => 10080,
    ],
    'silenced' => [],
    'metrics' => [
        'trim_snapshots' => ['job' => 24, 'queue' => 24],
    ],
    'fast_termination' => false,
    'memory_limit' => 64,
    'defaults' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 2,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 64,
            'tries' => 1,
            'timeout' => 60,
            'nice' => 0,
        ],
        'supervisor-orders' => [
            'connection' => 'redis',
            'queue' => ['orders'],
            'balance' => 'simple',
            'minProcesses' => 1,
            'maxProcesses' => 5,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 30,
        ],
        'supervisor-images' => [
            'connection' => 'redis',
            'queue' => ['images'],
            'balance' => 'simple',
            'minProcesses' => 1,
            'maxProcesses' => 3,
            'memory' => 256,
            'tries' => 2,
            'timeout' => 120,
        ],
    ],
    'environments' => [
        'production' => [
            'supervisor-default' => ['maxProcesses' => 4],
            'supervisor-orders' => ['maxProcesses' => 8],
            'supervisor-images' => ['maxProcesses' => 4],
        ],
        'local' => [
            'supervisor-default' => ['maxProcesses' => 2],
            'supervisor-orders' => ['maxProcesses' => 2],
            'supervisor-images' => ['maxProcesses' => 1],
        ],
    ],
];
