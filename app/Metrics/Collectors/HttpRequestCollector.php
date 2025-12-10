<?php

declare(strict_types=1);

namespace App\Metrics\Collectors;

use Illuminate\Support\Facades\Redis;

/**
 * Collector for HTTP request metrics.
 *
 * Tracks request counts, durations, and sizes for all HTTP requests.
 */
class HttpRequestCollector
{
    protected string $prefix;

    protected string $redisPrefix;

    public function __construct()
    {
        $this->prefix = config('prometheus.redis.prefix', 'groundwork_metrics_');
        // Get the Laravel Redis prefix (used by Redis facade)
        $this->redisPrefix = config('database.redis.options.prefix', '');
    }

    /**
     * Increment the HTTP requests counter.
     */
    public function incrementRequests(string $method, string $route, string $statusCode): void
    {
        $this->incrementCounter('http_requests_total', [
            'method' => $method,
            'route' => $route,
            'status_code' => $statusCode,
        ]);
    }

    /**
     * Record HTTP request duration.
     */
    public function recordDuration(string $method, string $route, float $seconds): void
    {
        $this->observeHistogram('http_request_duration_seconds', $seconds, [
            'method' => $method,
            'route' => $route,
        ], config('prometheus.buckets.http_request_duration', [0.01, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]));
    }

    /**
     * Record HTTP request size.
     */
    public function recordRequestSize(string $method, string $route, int $bytes): void
    {
        $this->observeHistogram('http_request_size_bytes', $bytes, [
            'method' => $method,
            'route' => $route,
        ], [100, 1000, 10000, 100000, 1000000]);
    }

    /**
     * Record HTTP response size.
     */
    public function recordResponseSize(string $method, string $route, int $bytes): void
    {
        $this->observeHistogram('http_response_size_bytes', $bytes, [
            'method' => $method,
            'route' => $route,
        ], [100, 1000, 10000, 100000, 1000000]);
    }

    /**
     * Collect all metrics in Prometheus format.
     */
    public function collect(): string
    {
        $output = [];

        // HTTP Requests Total
        $output[] = '# HELP groundwork_http_requests_total Total HTTP requests';
        $output[] = '# TYPE groundwork_http_requests_total counter';
        $output = array_merge($output, $this->collectCounter('http_requests_total'));

        // HTTP Request Duration
        $output[] = '# HELP groundwork_http_request_duration_seconds Request processing time';
        $output[] = '# TYPE groundwork_http_request_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('http_request_duration_seconds'));

        // HTTP Request Size
        $output[] = '# HELP groundwork_http_request_size_bytes Request body size';
        $output[] = '# TYPE groundwork_http_request_size_bytes histogram';
        $output = array_merge($output, $this->collectHistogram('http_request_size_bytes'));

        // HTTP Response Size
        $output[] = '# HELP groundwork_http_response_size_bytes Response body size';
        $output[] = '# TYPE groundwork_http_response_size_bytes histogram';
        $output = array_merge($output, $this->collectHistogram('http_response_size_bytes'));

        return implode("\n", array_filter($output));
    }

    /**
     * Increment a counter metric.
     */
    protected function incrementCounter(string $name, array $labels, int $value = 1): void
    {
        $key = $this->buildKey('counter', $name, $labels);
        Redis::incrby($key, $value);
    }

    /**
     * Observe a histogram value.
     */
    protected function observeHistogram(string $name, float $value, array $labels, array $buckets): void
    {
        // Increment count
        $countKey = $this->buildKey('histogram', $name.'_count', $labels);
        Redis::incr($countKey);

        // Add to sum
        $sumKey = $this->buildKey('histogram', $name.'_sum', $labels);
        Redis::incrbyfloat($sumKey, $value);

        // Increment bucket counters
        foreach ($buckets as $bucket) {
            if ($value <= $bucket) {
                $bucketLabels = array_merge($labels, ['le' => (string) $bucket]);
                $bucketKey = $this->buildKey('histogram', $name.'_bucket', $bucketLabels);
                Redis::incr($bucketKey);
            }
        }

        // +Inf bucket always gets incremented
        $infLabels = array_merge($labels, ['le' => '+Inf']);
        $infKey = $this->buildKey('histogram', $name.'_bucket', $infLabels);
        Redis::incr($infKey);
    }

    /**
     * Build a Redis key for a metric.
     */
    protected function buildKey(string $type, string $name, array $labels): string
    {
        $labelStr = $this->formatLabels($labels);

        return $this->prefix.$type.':'.$name.$labelStr;
    }

    /**
     * Format labels for Redis key.
     */
    protected function formatLabels(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        ksort($labels);
        $parts = [];
        foreach ($labels as $key => $value) {
            $parts[] = $key.'='.$value;
        }

        return ':'.implode(',', $parts);
    }

    /**
     * Format labels for Prometheus output.
     */
    protected function formatPrometheusLabels(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        $parts = [];
        foreach ($labels as $key => $value) {
            $parts[] = $key.'="'.addslashes((string) $value).'"';
        }

        return '{'.implode(',', $parts).'}';
    }

    /**
     * Strip Redis prefix from key returned by Redis::keys().
     */
    protected function stripRedisPrefix(string $key): string
    {
        if ($this->redisPrefix && str_starts_with($key, $this->redisPrefix)) {
            return substr($key, strlen($this->redisPrefix));
        }

        return $key;
    }

    /**
     * Collect counter metrics from Redis.
     */
    protected function collectCounter(string $name): array
    {
        $pattern = $this->prefix.'counter:'.$name.'*';
        $keys = Redis::keys($pattern);
        $output = [];

        foreach ($keys as $key) {
            // Strip Redis prefix before getting value (Redis::get adds prefix automatically)
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'counter', $name);
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.$labelStr.' '.$value;
        }

        return $output;
    }

    /**
     * Collect histogram metrics from Redis.
     */
    protected function collectHistogram(string $name): array
    {
        $output = [];

        // Collect buckets
        $bucketPattern = $this->prefix.'histogram:'.$name.'_bucket*';
        $bucketKeys = Redis::keys($bucketPattern);

        foreach ($bucketKeys as $key) {
            // Strip Redis prefix before getting value
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_bucket');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_bucket'.$labelStr.' '.$value;
        }

        // Collect counts
        $countPattern = $this->prefix.'histogram:'.$name.'_count*';
        $countKeys = Redis::keys($countPattern);

        foreach ($countKeys as $key) {
            // Strip Redis prefix before getting value
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_count');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_count'.$labelStr.' '.$value;
        }

        // Collect sums
        $sumPattern = $this->prefix.'histogram:'.$name.'_sum*';
        $sumKeys = Redis::keys($sumPattern);

        foreach ($sumKeys as $key) {
            // Strip Redis prefix before getting value
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_sum');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_sum'.$labelStr.' '.$value;
        }

        return $output;
    }

    /**
     * Extract labels from a Redis key.
     */
    protected function extractLabels(string $key, string $type, string $name): array
    {
        // Keys returned by Redis::keys() include the Laravel Redis prefix
        // We need to strip it to find our metric prefix
        $keyWithoutRedisPrefix = $key;
        if ($this->redisPrefix && str_starts_with($key, $this->redisPrefix)) {
            $keyWithoutRedisPrefix = substr($key, strlen($this->redisPrefix));
        }

        $prefix = $this->prefix.$type.':'.$name;

        if (str_contains($keyWithoutRedisPrefix, $prefix)) {
            $labelPart = substr($keyWithoutRedisPrefix, strpos($keyWithoutRedisPrefix, $prefix) + strlen($prefix));
        } else {
            return [];
        }

        if (empty($labelPart) || $labelPart === '') {
            return [];
        }

        $labelPart = ltrim($labelPart, ':');

        if (empty($labelPart)) {
            return [];
        }

        $labels = [];
        $pairs = explode(',', $labelPart);

        foreach ($pairs as $pair) {
            if (str_contains($pair, '=')) {
                [$k, $value] = explode('=', $pair, 2);
                $labels[$k] = $value;
            }
        }

        return $labels;
    }
}
