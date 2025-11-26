<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Octane Configuration
    |--------------------------------------------------------------------------
    */

    'server' => env('OCTANE_SERVER', 'swoole'),

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    */

    'listeners' => [
        \Laravel\Octane\Events\WorkerStarting::class => [
            \Laravel\Octane\Listeners\EnsureUploadedFilesAreValid::class,
            \Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved::class,
        ],

        \Laravel\Octane\Events\RequestReceived::class => [
            ...config('octane.garbage_collection', [
                \Laravel\Octane\Listeners\DisconnectFromDatabases::class,
                \Laravel\Octane\Listeners\FlushStrAccumulation::class,
            ]),
        ],

        \Laravel\Octane\Events\RequestHandled::class => [
            \Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
        ],

        \Laravel\Octane\Events\RequestTerminated::class => [
            // Limpar uploads temporários
            \Laravel\Octane\Listeners\FlushUploadedFiles::class,
        ],

        \Laravel\Octane\Events\TickReceived::class => [
            \Laravel\Octane\Listeners\FlushStaleSessions::class,
        ],

        \Laravel\Octane\Events\TickTerminated::class => [
            \Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
        ],

        \Laravel\Octane\Events\WorkerErrorOccurred::class => [],
        \Laravel\Octane\Events\WorkerStopping::class => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    */

    'warm' => [
        'auth',
        'cache',
        'cache.store',
        'config',
        'db',
        'encrypter',
        'log',
        'redis',
        'redis.connection',
        'router',
        'session',
        'session.store',
        'view',
    ],

    'flush' => [
        // Bindings que devem ser limpos após cada requisição
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'rows' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Swoole Configuration
    |--------------------------------------------------------------------------
    */

    'swoole' => [
        'options' => [
            // Configurações de log
            'log_file' => storage_path('logs/swoole.log'),
            'log_level' => env('APP_DEBUG') ? 0 : 4,

            // Configurações de workers
            'worker_num' => env('OCTANE_WORKERS', 4),
            'task_worker_num' => env('OCTANE_TASK_WORKERS', 6),
            'max_request' => env('SWOOLE_MAX_REQUEST', 1000),
            'reload_async' => true,
            'enable_coroutine' => env('SWOOLE_ENABLE_COROUTINE', true),
            'max_coroutine' => env('SWOOLE_MAX_COROUTINE', 10000),

            // Configurações para uploads grandes - EVITAR 413 ERROR
            'package_max_length' => env('SWOOLE_PACKAGE_MAX_LENGTH', 536870912), // 512MB
            'buffer_output_size' => env('SWOOLE_BUFFER_OUTPUT_SIZE', 4194304), // 4MB
            
            // Timeouts
            'max_wait_time' => 60,

            // Compressão HTTP
            'http_compression' => env('SWOOLE_HTTP_COMPRESSION', true),
            'http_compression_level' => env('SWOOLE_HTTP_COMPRESSION_LEVEL', 6),
            
            // Performance
            'open_tcp_nodelay' => true,
            'tcp_fastopen' => true,
            
            // Socket settings
            'socket_buffer_size' => 8 * 1024 * 1024, // 8MB
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */

    'tables' => [
        'example:1000' => [
            'name' => 'string:1000',
            'votes' => 'int',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Watch Options
    |--------------------------------------------------------------------------
    */

    'watch' => [
        'directories' => [
            'app',
            'bootstrap',
            'config',
            'database',
            'modules',
            'public/**/*.php',
            'resources/**/*.php',
            'routes',
            'storage/framework/views',
        ],

        'exclude' => [
            'node_modules',
            'vendor',
            'storage',
            'public/storage',
            'public/hot',
        ],

        'poll' => env('OCTANE_WATCH_POLL', false),
        'interval' => env('OCTANE_WATCH_INTERVAL', 1000),
    ],

    'garbage_collection' => [
        \Laravel\Octane\Listeners\DisconnectFromDatabases::class,
        \Laravel\Octane\Listeners\FlushStrAccumulation::class,
    ],

    'max_execution_time' => 30,
];
