<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
        ],

        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => env('RABBITMQ_PORT', 5672),
            'user' => env('RABBITMQ_USER', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'exchange' => env('RABBITMQ_EXCHANGE_NAME', 'laravel'),
            'exchange_type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
            'exchange_routing_key' => '',
            
            // Configurações avançadas
            'login' => env('RABBITMQ_USER', 'guest'),
            'options' => [
                'queue' => [
                    'job' => \VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob::class,
                    'durable' => true,
                    'auto_delete' => false,
                    'passive' => false,
                    'exchange_routing_key' => '',
                ],
                'exchange' => [
                    'name' => env('RABBITMQ_EXCHANGE_NAME', 'laravel'),
                    'type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],

];
