<?php

use Illuminate\Support\Str;

$queueNames = [
    'default' => trim((string) env('QUEUE_DEFAULT_NAME', 'default')) ?: 'default',
    'ak_catalog' => trim((string) env('QUEUE_AK_CATALOG', 'ak_catalog')) ?: 'ak_catalog',
    'frontend_cache' => trim((string) env('QUEUE_FRONTEND_CACHE', env('WEBHOOK_QUEUE', 'frontend_cache'))) ?: 'frontend_cache',
    'emails' => trim((string) env('QUEUE_EMAILS', 'emails')) ?: 'emails',
];

$supervisor = static function (array $queues, array $overrides = []): array {
    return array_merge([
        'connection' => env('HORIZON_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'redis')),
        'queue' => $queues,
        'balance' => 'auto',
        'autoScalingStrategy' => 'time',
        'maxProcesses' => (int) env('HORIZON_MAX_PROCESSES', 1),
        'maxTime' => 0,
        'maxJobs' => 0,
        'memory' => (int) env('HORIZON_WORKER_MEMORY', 256),
        'tries' => (int) env('HORIZON_JOB_TRIES', 3),
        'timeout' => (int) env('HORIZON_JOB_TIMEOUT', 1800),
        'nice' => 0,
    ], $overrides);
};

$environmentOverrides = static function (string $name, int $fallbackMaxProcesses): array {
    $envKey = strtoupper(str_replace('-', '_', $name));

    return [
        'maxProcesses' => (int) env("HORIZON_{$envKey}_MAX_PROCESSES", env('HORIZON_MAX_PROCESSES', $fallbackMaxProcesses)),
        'balanceMaxShift' => (int) env('HORIZON_BALANCE_MAX_SHIFT', 1),
        'balanceCooldown' => (int) env('HORIZON_BALANCE_COOLDOWN', 3),
    ];
};

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Name
    |--------------------------------------------------------------------------
    |
    | This name appears in notifications and in the Horizon UI. Unique names
    | can be useful while running multiple instances of Horizon within an
    | application, allowing you to identify the Horizon you're viewing.
    |
    */

    'name' => env('HORIZON_NAME', env('APP_NAME', 'laravel')),

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('HORIZON_PATH', 'admin/horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:'.$queueNames['default'] => 60,
        'redis:'.$queueNames['ak_catalog'] => 60,
        'redis:'.$queueNames['frontend_cache'] => 60,
        'redis:'.$queueNames['emails'] => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    |
    | Silencing a job will instruct Horizon to not place the job in the list
    | of completed jobs within the Horizon dashboard. This setting may be
    | used to fully remove any noisy jobs from the completed jobs list.
    |
    */

    'silenced' => [
        // App\Jobs\ExampleJob::class,
    ],

    'silenced_tags' => [
        // 'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Here you can configure how many snapshots should be kept to display in
    | the metrics graph. This will get used in combination with Horizon's
    | `horizon:snapshot` schedule to define how long to retain metrics.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon master
    | supervisor may consume before it is terminated and restarted. For
    | configuring these limits on your workers, see the next section.
    |
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'defaults' => [
        'supervisor-default' => $supervisor([$queueNames['default']]),
        'supervisor-ak-catalog' => $supervisor(
            [$queueNames['ak_catalog']],
            ['timeout' => (int) env('HORIZON_AK_CATALOG_TIMEOUT', 0)]
        ),
        'supervisor-frontend-cache' => $supervisor([$queueNames['frontend_cache']]),
        'supervisor-emails' => $supervisor([$queueNames['emails']]),
    ],

    'environments' => [
        'production' => [
            'supervisor-default' => $environmentOverrides('default', 10),
            'supervisor-ak-catalog' => $environmentOverrides('ak-catalog', 10),
            'supervisor-frontend-cache' => $environmentOverrides('frontend-cache', 10),
            'supervisor-emails' => $environmentOverrides('emails', 10),
        ],

        'local' => [
            'supervisor-default' => $environmentOverrides('default', 3),
            'supervisor-ak-catalog' => $environmentOverrides('ak-catalog', 3),
            'supervisor-frontend-cache' => $environmentOverrides('frontend-cache', 3),
            'supervisor-emails' => $environmentOverrides('emails', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watcher Configuration
    |--------------------------------------------------------------------------
    |
    | The following list of directories and files will be watched when using
    | the `horizon:listen` command. Whenever any directories or files are
    | changed, Horizon will automatically restart to apply all changes.
    |
    */

    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'public/**/*.php',
        'resources/**/*.php',
        'routes',
        'composer.lock',
        'composer.json',
        '.env',
    ],
];
