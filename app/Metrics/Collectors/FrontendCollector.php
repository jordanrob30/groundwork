<?php

declare(strict_types=1);

namespace App\Metrics\Collectors;

use Illuminate\Support\Facades\Redis;

/**
 * Collector for frontend performance metrics.
 *
 * Tracks Core Web Vitals, page load times, JS errors, and Livewire metrics.
 */
class FrontendCollector
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
     * Record a Core Web Vital metric.
     */
    public function recordWebVital(string $name, float $value, string $page, string $deviceType): void
    {
        $metricName = match ($name) {
            'LCP' => 'web_vitals_lcp_seconds',
            'FID' => 'web_vitals_fid_seconds',
            'CLS' => 'web_vitals_cls',
            'INP' => 'web_vitals_inp_seconds',
            'TTFB' => 'web_vitals_ttfb_seconds',
            default => null,
        };

        if (! $metricName) {
            return;
        }

        // Convert milliseconds to seconds for time-based metrics
        if (in_array($name, ['LCP', 'FID', 'INP', 'TTFB'])) {
            $value = $value / 1000;
        }

        $buckets = match ($name) {
            'LCP' => config('prometheus.buckets.web_vitals_lcp', [0.5, 1, 1.5, 2, 2.5, 3, 4, 5]),
            'FID', 'INP' => config('prometheus.buckets.web_vitals_fid', [0.01, 0.05, 0.1, 0.2, 0.3, 0.5]),
            'CLS' => config('prometheus.buckets.web_vitals_cls', [0.01, 0.05, 0.1, 0.15, 0.2, 0.25]),
            'TTFB' => [0.1, 0.2, 0.4, 0.6, 0.8, 1, 2],
            default => [0.1, 0.5, 1, 2, 5],
        };

        $this->observeHistogram($metricName, $value, [
            'page' => $this->normalizePage($page),
            'device_type' => $deviceType,
        ], $buckets);
    }

    /**
     * Record a JavaScript error.
     */
    public function recordJsError(string $page, string $errorType, string $message): void
    {
        $this->incrementCounter('js_errors_total', [
            'page' => $this->normalizePage($page),
            'error_type' => $errorType,
        ]);
    }

    /**
     * Record Livewire component render time.
     */
    public function recordLivewireRender(string $component, float $seconds): void
    {
        $this->observeHistogram('livewire_render_duration_seconds', $seconds, [
            'component' => $component,
        ], [0.01, 0.05, 0.1, 0.25, 0.5, 1]);
    }

    /**
     * Record Livewire component update time.
     */
    public function recordLivewireUpdate(string $component, float $seconds): void
    {
        $this->observeHistogram('livewire_update_duration_seconds', $seconds, [
            'component' => $component,
        ], [0.01, 0.05, 0.1, 0.25, 0.5, 1]);
    }

    /**
     * Record page load duration.
     */
    public function recordPageLoad(string $page, float $loadDuration, ?float $domReadyDuration = null): void
    {
        $this->observeHistogram('page_load_duration_seconds', $loadDuration, [
            'page' => $this->normalizePage($page),
        ], [0.5, 1, 2, 3, 5, 10]);

        if ($domReadyDuration !== null) {
            $this->observeHistogram('page_dom_ready_seconds', $domReadyDuration, [
                'page' => $this->normalizePage($page),
            ], [0.5, 1, 2, 3, 5, 10]);
        }
    }

    /**
     * Increment Livewire error counter.
     */
    public function incrementLivewireError(string $component): void
    {
        $this->incrementCounter('livewire_errors_total', [
            'component' => $component,
        ]);
    }

    /**
     * Normalize page path for metric labeling.
     */
    protected function normalizePage(string $page): string
    {
        // Remove query strings and fragments
        $page = strtok($page, '?');
        $page = strtok($page, '#');

        // Replace numeric IDs with placeholder
        $page = preg_replace('/\/\d+/', '/{id}', $page);

        // Replace UUIDs with placeholder
        $page = preg_replace(
            '/\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i',
            '/{uuid}',
            $page
        );

        return $page ?: '/';
    }

    /**
     * Collect all metrics in Prometheus format.
     */
    public function collect(): string
    {
        $output = [];

        // Core Web Vitals
        $webVitals = ['lcp', 'fid', 'cls', 'inp', 'ttfb'];
        foreach ($webVitals as $vital) {
            $name = 'web_vitals_'.$vital.($vital === 'cls' ? '' : '_seconds');
            $output[] = '# HELP groundwork_'.$name.' '.strtoupper($vital).' metric';
            $output[] = '# TYPE groundwork_'.$name.' histogram';
            $output = array_merge($output, $this->collectHistogram($name));
        }

        // Page Load
        $output[] = '# HELP groundwork_page_load_duration_seconds Full page load time';
        $output[] = '# TYPE groundwork_page_load_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('page_load_duration_seconds'));

        $output[] = '# HELP groundwork_page_dom_ready_seconds DOM ready time';
        $output[] = '# TYPE groundwork_page_dom_ready_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('page_dom_ready_seconds'));

        // JS Errors
        $output[] = '# HELP groundwork_js_errors_total JavaScript errors captured';
        $output[] = '# TYPE groundwork_js_errors_total counter';
        $output = array_merge($output, $this->collectCounter('js_errors_total'));

        // Livewire
        $output[] = '# HELP groundwork_livewire_render_duration_seconds Component render time';
        $output[] = '# TYPE groundwork_livewire_render_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('livewire_render_duration_seconds'));

        $output[] = '# HELP groundwork_livewire_update_duration_seconds Component update time';
        $output[] = '# TYPE groundwork_livewire_update_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('livewire_update_duration_seconds'));

        $output[] = '# HELP groundwork_livewire_errors_total Livewire component errors';
        $output[] = '# TYPE groundwork_livewire_errors_total counter';
        $output = array_merge($output, $this->collectCounter('livewire_errors_total'));

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
