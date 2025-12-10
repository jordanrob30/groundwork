<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prometheus Metrics Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Prometheus metrics collection for the application.
    |
    */

    'enabled' => env('PROMETHEUS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Storage Adapter
    |--------------------------------------------------------------------------
    |
    | The storage adapter for metrics. Using Redis to persist metrics across
    | PHP's share-nothing request lifecycle.
    |
    | Supported: "redis", "apc", "in_memory"
    |
    */

    'storage_adapter' => env('PROMETHEUS_STORAGE_ADAPTER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Redis connection settings for metric storage.
    |
    */

    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => env('PROMETHEUS_REDIS_DATABASE', 1),
        'prefix' => env('PROMETHEUS_REDIS_PREFIX', 'groundwork_metrics_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Labels
    |--------------------------------------------------------------------------
    |
    | Labels that will be added to all metrics.
    |
    */

    'default_labels' => [
        'app' => 'groundwork',
        'environment' => env('APP_ENV', 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metric Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace prefix for all metrics. All metrics will be prefixed
    | with this value (e.g., groundwork_http_requests_total).
    |
    */

    'namespace' => 'groundwork',

    /*
    |--------------------------------------------------------------------------
    | Histogram Buckets
    |--------------------------------------------------------------------------
    |
    | Default histogram bucket configurations for different metric types.
    |
    */

    'buckets' => [
        'http_request_duration' => [0.01, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10],
        'email_send_duration' => [0.1, 0.5, 1, 2, 5, 10, 30],
        'polling_duration' => [1, 5, 10, 30, 60, 120],
        'analysis_duration' => [1, 5, 10, 30, 60],
        'queue_job_duration' => [0.1, 0.5, 1, 5, 10, 30, 60, 300],
        'db_query_duration' => [0.001, 0.005, 0.01, 0.05, 0.1, 0.5, 1],
        'web_vitals_lcp' => [0.5, 1, 1.5, 2, 2.5, 3, 4, 5],
        'web_vitals_fid' => [0.01, 0.05, 0.1, 0.2, 0.3, 0.5],
        'web_vitals_cls' => [0.01, 0.05, 0.1, 0.15, 0.2, 0.25],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Endpoint
    |--------------------------------------------------------------------------
    |
    | Configure the metrics endpoint path.
    |
    */

    'route' => [
        'path' => '/metrics',
        'middleware' => [],
    ],
];
