<?php

declare(strict_types=1);

namespace App\Metrics\Collectors;

use Illuminate\Support\Facades\Redis;

/**
 * Collector for queue job metrics.
 *
 * Tracks job processing counts, durations, and queue depths.
 */
class QueueJobCollector
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
     * Increment the job processed counter.
     */
    public function incrementJobProcessed(string $job, string $queue, string $status = 'processed'): void
    {
        $this->incrementCounter('queue_jobs_total', [
            'job' => $this->normalizeJobName($job),
            'queue' => $queue,
            'status' => $status,
        ]);
    }

    /**
     * Record job execution duration.
     */
    public function recordJobDuration(string $job, string $queue, float $seconds): void
    {
        $this->observeHistogram('queue_job_duration_seconds', $seconds, [
            'job' => $this->normalizeJobName($job),
            'queue' => $queue,
        ], config('prometheus.buckets.queue_job_duration', [0.1, 0.5, 1, 5, 10, 30, 60, 300]));
    }

    /**
     * Set the queue depth gauge.
     */
    public function setQueueDepth(string $queue, int $depth): void
    {
        $this->setGauge('queue_depth', $depth, [
            'queue' => $queue,
        ]);
    }

    /**
     * Record queue wait time.
     */
    public function recordQueueWait(string $queue, float $seconds): void
    {
        $this->observeHistogram('queue_wait_seconds', $seconds, [
            'queue' => $queue,
        ], [1, 5, 10, 30, 60, 120, 300, 600]);
    }

    /**
     * Normalize job class name to short form.
     */
    protected function normalizeJobName(string $job): string
    {
        $parts = explode('\\', $job);

        return end($parts);
    }

    /**
     * Collect all metrics in Prometheus format.
     */
    public function collect(): string
    {
        $output = [];

        // Queue Jobs Total
        $output[] = '# HELP groundwork_queue_jobs_total Jobs processed';
        $output[] = '# TYPE groundwork_queue_jobs_total counter';
        $output = array_merge($output, $this->collectCounter('queue_jobs_total'));

        // Queue Job Duration
        $output[] = '# HELP groundwork_queue_job_duration_seconds Job execution time';
        $output[] = '# TYPE groundwork_queue_job_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('queue_job_duration_seconds'));

        // Queue Depth
        $output[] = '# HELP groundwork_queue_depth Current queue depth';
        $output[] = '# TYPE groundwork_queue_depth gauge';
        $output = array_merge($output, $this->collectGauge('queue_depth'));

        // Queue Wait
        $output[] = '# HELP groundwork_queue_wait_seconds Time job waited in queue';
        $output[] = '# TYPE groundwork_queue_wait_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('queue_wait_seconds'));

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
     * Set a gauge metric.
     */
    protected function setGauge(string $name, float $value, array $labels): void
    {
        $key = $this->buildKey('gauge', $name, $labels);
        Redis::set($key, $value);
    }

    /**
     * Observe a histogram value.
     */
    protected function observeHistogram(string $name, float $value, array $labels, array $buckets): void
    {
        $countKey = $this->buildKey('histogram', $name.'_count', $labels);
        Redis::incr($countKey);

        $sumKey = $this->buildKey('histogram', $name.'_sum', $labels);
        Redis::incrbyfloat($sumKey, $value);

        foreach ($buckets as $bucket) {
            if ($value <= $bucket) {
                $bucketLabels = array_merge($labels, ['le' => (string) $bucket]);
                $bucketKey = $this->buildKey('histogram', $name.'_bucket', $bucketLabels);
                Redis::incr($bucketKey);
            }
        }

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
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'counter', $name);
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.$labelStr.' '.$value;
        }

        return $output;
    }

    /**
     * Collect gauge metrics from Redis.
     */
    protected function collectGauge(string $name): array
    {
        $pattern = $this->prefix.'gauge:'.$name.'*';
        $keys = Redis::keys($pattern);
        $output = [];

        foreach ($keys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'gauge', $name);
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

        $bucketPattern = $this->prefix.'histogram:'.$name.'_bucket*';
        $bucketKeys = Redis::keys($bucketPattern);

        foreach ($bucketKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_bucket');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_bucket'.$labelStr.' '.$value;
        }

        $countPattern = $this->prefix.'histogram:'.$name.'_count*';
        $countKeys = Redis::keys($countPattern);

        foreach ($countKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_count');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_count'.$labelStr.' '.$value;
        }

        $sumPattern = $this->prefix.'histogram:'.$name.'_sum*';
        $sumKeys = Redis::keys($sumPattern);

        foreach ($sumKeys as $key) {
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
