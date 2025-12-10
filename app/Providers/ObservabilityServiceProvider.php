<?php

declare(strict_types=1);

namespace App\Providers;

use App\Metrics\Collectors\AdminActivityCollector;
use App\Metrics\Collectors\CampaignFlowCollector;
use App\Metrics\Collectors\FrontendCollector;
use App\Metrics\Collectors\HttpRequestCollector;
use App\Metrics\Collectors\QueueJobCollector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for observability features.
 *
 * Bootstraps Prometheus metrics collection, structured logging,
 * and graceful degradation when observability infrastructure is unavailable.
 */
class ObservabilityServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/prometheus.php',
            'prometheus'
        );

        // Register collectors as singletons
        $this->app->singleton(AdminActivityCollector::class);
        $this->app->singleton(CampaignFlowCollector::class);
        $this->app->singleton(FrontendCollector::class);
        $this->app->singleton(HttpRequestCollector::class);
        $this->app->singleton(QueueJobCollector::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! config('prometheus.enabled', true)) {
            return;
        }

        $this->registerMetricsRoute();
        $this->registerCollectors();
        $this->registerDatabaseQueryListener();
    }

    /**
     * Register the /metrics endpoint for Prometheus scraping.
     */
    protected function registerMetricsRoute(): void
    {
        Route::get(config('prometheus.route.path', '/metrics'), function () {
            try {
                return $this->renderMetrics();
            } catch (\Throwable $e) {
                Log::warning('Failed to render Prometheus metrics', [
                    'error' => $e->getMessage(),
                ]);

                return response('# Metrics unavailable', 500)
                    ->header('Content-Type', 'text/plain');
            }
        })->middleware(config('prometheus.route.middleware', []));
    }

    /**
     * Register metric collectors.
     */
    protected function registerCollectors(): void
    {
        // Collectors are registered as singletons in register()
        // This method can be used for any additional bootstrap logic
    }

    /**
     * Register database query listener for query metrics.
     */
    protected function registerDatabaseQueryListener(): void
    {
        DB::listen(function ($query) {
            try {
                $operation = $this->extractQueryOperation($query->sql);
                $duration = $query->time / 1000; // Convert ms to seconds

                $prefix = config('prometheus.redis.prefix', 'groundwork_metrics_');

                // Increment query count
                Redis::incr($prefix.'counter:db_queries_total:operation='.$operation);

                // Record query duration
                $buckets = config('prometheus.buckets.db_query_duration', [0.001, 0.005, 0.01, 0.05, 0.1, 0.5, 1]);
                Redis::incr($prefix.'histogram:db_query_duration_seconds_count:operation='.$operation);
                Redis::incrbyfloat($prefix.'histogram:db_query_duration_seconds_sum:operation='.$operation, $duration);

                foreach ($buckets as $bucket) {
                    if ($duration <= $bucket) {
                        Redis::incr($prefix.'histogram:db_query_duration_seconds_bucket:le='.$bucket.',operation='.$operation);
                    }
                }
                Redis::incr($prefix.'histogram:db_query_duration_seconds_bucket:le=+Inf,operation='.$operation);
            } catch (\Throwable $e) {
                // Silently fail to avoid impacting database operations
            }
        });
    }

    /**
     * Extract the operation type from a SQL query.
     */
    protected function extractQueryOperation(string $sql): string
    {
        $sql = trim(strtolower($sql));

        if (str_starts_with($sql, 'select')) {
            return 'select';
        }
        if (str_starts_with($sql, 'insert')) {
            return 'insert';
        }
        if (str_starts_with($sql, 'update')) {
            return 'update';
        }
        if (str_starts_with($sql, 'delete')) {
            return 'delete';
        }

        return 'other';
    }

    /**
     * Render all registered metrics in Prometheus format.
     */
    protected function renderMetrics(): \Illuminate\Http\Response
    {
        $output = [];

        // App info metric
        $output[] = '# HELP groundwork_info Application information';
        $output[] = '# TYPE groundwork_info gauge';
        $output[] = sprintf(
            'groundwork_info{version="%s",environment="%s"} 1',
            config('app.version', '1.0.0'),
            config('app.env', 'local')
        );
        $output[] = '';

        // Collect metrics from all registered collectors
        try {
            $campaignFlowCollector = app(CampaignFlowCollector::class);
            $campaignMetrics = $campaignFlowCollector->collect();
            if (! empty($campaignMetrics)) {
                $output[] = $campaignMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect campaign flow metrics', ['error' => $e->getMessage()]);
        }

        try {
            $httpRequestCollector = app(HttpRequestCollector::class);
            $httpMetrics = $httpRequestCollector->collect();
            if (! empty($httpMetrics)) {
                $output[] = $httpMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect HTTP request metrics', ['error' => $e->getMessage()]);
        }

        try {
            $queueJobCollector = app(QueueJobCollector::class);
            $queueMetrics = $queueJobCollector->collect();
            if (! empty($queueMetrics)) {
                $output[] = $queueMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect queue job metrics', ['error' => $e->getMessage()]);
        }

        try {
            $frontendCollector = app(FrontendCollector::class);
            $frontendMetrics = $frontendCollector->collect();
            if (! empty($frontendMetrics)) {
                $output[] = $frontendMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect frontend metrics', ['error' => $e->getMessage()]);
        }

        try {
            $adminActivityCollector = app(AdminActivityCollector::class);
            $adminMetrics = $adminActivityCollector->collect();
            if (! empty($adminMetrics)) {
                $output[] = $adminMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect admin activity metrics', ['error' => $e->getMessage()]);
        }

        // Collect database metrics
        try {
            $dbMetrics = $this->collectDatabaseMetrics();
            if (! empty($dbMetrics)) {
                $output[] = $dbMetrics;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect database metrics', ['error' => $e->getMessage()]);
        }

        return response(implode("\n", $output)."\n")
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }

    /**
     * Collect database metrics from Redis.
     */
    protected function collectDatabaseMetrics(): string
    {
        $output = [];
        $prefix = config('prometheus.redis.prefix', 'groundwork_metrics_');
        $redisPrefix = config('database.redis.options.prefix', '');

        // Helper to strip Redis prefix for Redis::get()
        $stripPrefix = function (string $key) use ($redisPrefix): string {
            if ($redisPrefix && str_starts_with($key, $redisPrefix)) {
                return substr($key, strlen($redisPrefix));
            }

            return $key;
        };

        // DB Queries Total
        $output[] = '# HELP groundwork_db_queries_total Database queries';
        $output[] = '# TYPE groundwork_db_queries_total counter';

        $queryCountKeys = Redis::keys($prefix.'counter:db_queries_total*');
        foreach ($queryCountKeys as $key) {
            // Strip Redis prefix for both get() and label extraction
            $keyWithoutPrefix = $stripPrefix($key);
            $value = Redis::get($keyWithoutPrefix) ?? 0;
            if (preg_match('/operation=(\w+)/', $keyWithoutPrefix, $matches)) {
                $operation = $matches[1];
                $output[] = sprintf('groundwork_db_queries_total{operation="%s"} %s', $operation, $value);
            }
        }

        // DB Query Duration
        $output[] = '# HELP groundwork_db_query_duration_seconds Query execution time';
        $output[] = '# TYPE groundwork_db_query_duration_seconds histogram';

        $bucketKeys = Redis::keys($prefix.'histogram:db_query_duration_seconds_bucket*');
        foreach ($bucketKeys as $key) {
            // Strip Redis prefix for both get() and label extraction
            $keyWithoutPrefix = $stripPrefix($key);
            $value = Redis::get($keyWithoutPrefix) ?? 0;
            if (preg_match('/le=([^,]+),operation=(\w+)/', $keyWithoutPrefix, $matches)) {
                $le = $matches[1];
                $operation = $matches[2];
                $output[] = sprintf('groundwork_db_query_duration_seconds_bucket{le="%s",operation="%s"} %s', $le, $operation, $value);
            }
        }

        $countKeys = Redis::keys($prefix.'histogram:db_query_duration_seconds_count*');
        foreach ($countKeys as $key) {
            // Strip Redis prefix for both get() and label extraction
            $keyWithoutPrefix = $stripPrefix($key);
            $value = Redis::get($keyWithoutPrefix) ?? 0;
            if (preg_match('/operation=(\w+)/', $keyWithoutPrefix, $matches)) {
                $operation = $matches[1];
                $output[] = sprintf('groundwork_db_query_duration_seconds_count{operation="%s"} %s', $operation, $value);
            }
        }

        $sumKeys = Redis::keys($prefix.'histogram:db_query_duration_seconds_sum*');
        foreach ($sumKeys as $key) {
            // Strip Redis prefix for both get() and label extraction
            $keyWithoutPrefix = $stripPrefix($key);
            $value = Redis::get($keyWithoutPrefix) ?? 0;
            if (preg_match('/operation=(\w+)/', $keyWithoutPrefix, $matches)) {
                $operation = $matches[1];
                $output[] = sprintf('groundwork_db_query_duration_seconds_sum{operation="%s"} %s', $operation, $value);
            }
        }

        return implode("\n", array_filter($output));
    }
}
