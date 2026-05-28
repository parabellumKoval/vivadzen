<?php

use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CloseMonologHandlers;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushOnce;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\FlushUploadedFiles;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Octane;

return [
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    'https' => env('OCTANE_HTTPS', false),

    'listeners' => [
        // pre-warm — выполняется один раз при boot воркера
        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        // на каждый запрос/операцию используем актуальный для Octane 2.17 набор reset-listener'ов
        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
        ],
        RequestHandled::class => [
        ],
        RequestTerminated::class => [
            // Для FrankenPHP достаточно cleanup на завершении операции.
        ],

        TickReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
        ],
        TaskReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
        ],
        TickTerminated::class => [
        ],
        TaskTerminated::class => [
        ],
        OperationTerminated::class => [
            FlushOnce::class,
            FlushTemporaryContainerInstances::class,
            // Можно включить при необходимости жёсткий cleanup после каждой операции.
            // DisconnectFromDatabases::class,
            // CollectGarbage::class,
            // FlushUploadedFiles::class,
        ],

        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],
        WorkerStopping::class => [
            CloseMonologHandlers::class,
        ],
    ],

    'warm' => [
        ...\Laravel\Octane\Octane::defaultServicesToWarm(),
    ],

    // Перезагрузка воркера каждые N запросов — страховка от утечек памяти
    // в долгоживущем процессе. 500 — золотая середина (FrankenPHP официальная рекомендация).
    'max_execution_time' => 30,
    'garbage' => 50,

    // Если в коде где-то были state-ful singletons — переподключаем БД/Redis после tick-а
    'flush' => [
        'auth',
    ],

    'tables' => [
        'example:1000' => [
            'name' => 'string:1000',
            'votes' => 'int',
        ],
    ],

    'cache' => [
        'rows' => 1000,
        'bytes' => 10000,
    ],
];
