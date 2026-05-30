<?php

return [

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
    | This is the path where Horizon will be accessible from. Feel free to
    | change this path to anything you like. Note that the URI will not
    | affect the path of the API that this dashboard communicates with.
    |
    */

    'path' => 'horizon',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection that Horizon uses to store
    | all of its data. It includes processed, failed, and other types of
    | data that needs to be stored for history in your application.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Environment
    |--------------------------------------------------------------------------
    |
    | This is the environment that Horizon will run under. It can be set to
    | anything, but only these two ought to be used. By default, the value
    | is auto-detected as the app environment.
    |
    */

    'environment' => env('HORIZON_ENVIRONMENT', env('APP_ENV')),

    /*
    |--------------------------------------------------------------------------
    | Horizon Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing and retrieving data from Redis
    | so that multiple Horizon instances can run on the same Redis server
    | so you don't have any conflicts. It includes the trailing slash.
    |
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can just stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Waiter - Limit
    |--------------------------------------------------------------------------
    |
    | This value represents the maximum amount of jobs to process via the
    | Horizon daemon "worker" before it is automatically restarted. This is
    | useful to ensure the worker doesn't accumulate too much memory over
    | time by always staying fresh when it is auto-restarted by Supervisor.
    |
    */

    'waiter' => env('HORIZON_WAITER_MAX_PROCESSES', 10),

    /*
    |--------------------------------------------------------------------------
    | Job Monitoring
    |--------------------------------------------------------------------------
    |
    | Horizon can monitor the performance and runtime of your jobs. When
    | enabled, Horizon records all of the metrics about your jobs so you
    | can inspect the performance of everything happening in the queue.
    |
    */

    'monitor' => [
        'enabled' => env('HORIZON_MONITOR_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    |
    | Horizon allows you to configure your workers on a per environment
    | basis. Here you may specify a fresh list of processes that should
    | be spawned by Supervisor in each available Horizon environment.
    |
    */

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'transcribe'],
                'balance' => 'auto',
                'maxProcesses' => 3,
                'tries' => 3,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'transcribe'],
                'balance' => 'auto',
                'maxProcesses' => 3,
                'tries' => 3,
            ],
        ],
    ],

];
