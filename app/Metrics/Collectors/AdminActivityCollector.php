<?php

declare(strict_types=1);

namespace App\Metrics\Collectors;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Collector for admin activity metrics.
 *
 * Tracks administrative actions including:
 * - Admin actions (impersonation, role changes, etc.)
 * - Action counts by type
 * - Impersonation duration
 */
class AdminActivityCollector
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
     * Record an admin action.
     */
    public function incrementAdminAction(string $action, ?int $adminId = null): void
    {
        try {
            $labels = 'action='.$action;
            if ($adminId !== null) {
                $labels .= ',admin_id='.$adminId;
            }

            Redis::incr($this->prefix.'counter:admin_actions_total:'.$labels);
        } catch (\Throwable $e) {
            Log::debug('Failed to increment admin action metric', [
                'error' => $e->getMessage(),
                'action' => $action,
            ]);
        }
    }

    /**
     * Record the start of an impersonation session.
     */
    public function incrementImpersonationStart(int $adminId, int $targetUserId): void
    {
        try {
            $labels = 'admin_id='.$adminId.',target_user_id='.$targetUserId;
            Redis::incr($this->prefix.'counter:impersonation_sessions_total:'.$labels);
            Redis::incr($this->prefix.'gauge:active_impersonations');
        } catch (\Throwable $e) {
            Log::debug('Failed to record impersonation start metric', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record the end of an impersonation session with duration.
     */
    public function recordImpersonationEnd(int $adminId, int $targetUserId, float $durationSeconds): void
    {
        try {
            // Decrement active impersonations
            Redis::decr($this->prefix.'gauge:active_impersonations');

            // Record impersonation duration histogram
            $labels = 'admin_id='.$adminId.',target_user_id='.$targetUserId;
            $buckets = config('prometheus.buckets.impersonation_duration', [60, 300, 600, 1800, 3600, 7200]);

            Redis::incr($this->prefix.'histogram:impersonation_duration_seconds_count:'.$labels);
            Redis::incrbyfloat($this->prefix.'histogram:impersonation_duration_seconds_sum:'.$labels, $durationSeconds);

            foreach ($buckets as $bucket) {
                if ($durationSeconds <= $bucket) {
                    Redis::incr($this->prefix.'histogram:impersonation_duration_seconds_bucket:le='.$bucket.','.$labels);
                }
            }
            Redis::incr($this->prefix.'histogram:impersonation_duration_seconds_bucket:le=+Inf,'.$labels);
        } catch (\Throwable $e) {
            Log::debug('Failed to record impersonation end metric', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record a role change action.
     */
    public function incrementRoleChange(string $fromRole, string $toRole): void
    {
        try {
            $labels = 'from_role='.$fromRole.',to_role='.$toRole;
            Redis::incr($this->prefix.'counter:role_changes_total:'.$labels);
        } catch (\Throwable $e) {
            Log::debug('Failed to record role change metric', [
                'error' => $e->getMessage(),
            ]);
        }
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
     * Collect all admin activity metrics in Prometheus format.
     */
    public function collect(): string
    {
        $output = [];

        try {
            // Admin Actions Total
            $actionKeys = Redis::keys($this->prefix.'counter:admin_actions_total*');
            if (! empty($actionKeys)) {
                $output[] = '# HELP groundwork_admin_actions_total Total admin actions performed';
                $output[] = '# TYPE groundwork_admin_actions_total counter';

                foreach ($actionKeys as $key) {
                    $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
                    $labels = $this->extractLabels($key, 'admin_actions_total');
                    $output[] = sprintf('groundwork_admin_actions_total{%s} %s', $labels, $value);
                }
                $output[] = '';
            }

            // Impersonation Sessions Total
            $impersonationKeys = Redis::keys($this->prefix.'counter:impersonation_sessions_total*');
            if (! empty($impersonationKeys)) {
                $output[] = '# HELP groundwork_impersonation_sessions_total Total impersonation sessions started';
                $output[] = '# TYPE groundwork_impersonation_sessions_total counter';

                foreach ($impersonationKeys as $key) {
                    $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
                    $labels = $this->extractLabels($key, 'impersonation_sessions_total');
                    $output[] = sprintf('groundwork_impersonation_sessions_total{%s} %s', $labels, $value);
                }
                $output[] = '';
            }

            // Active Impersonations Gauge
            $activeImpersonations = Redis::get($this->prefix.'gauge:active_impersonations') ?? 0;
            $output[] = '# HELP groundwork_active_impersonations Number of currently active impersonation sessions';
            $output[] = '# TYPE groundwork_active_impersonations gauge';
            $output[] = sprintf('groundwork_active_impersonations %s', $activeImpersonations);
            $output[] = '';

            // Impersonation Duration Histogram
            $this->collectHistogramMetrics($output, 'impersonation_duration_seconds', 'Time spent in impersonation sessions');

            // Role Changes Total
            $roleChangeKeys = Redis::keys($this->prefix.'counter:role_changes_total*');
            if (! empty($roleChangeKeys)) {
                $output[] = '# HELP groundwork_role_changes_total Total role changes performed';
                $output[] = '# TYPE groundwork_role_changes_total counter';

                foreach ($roleChangeKeys as $key) {
                    $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
                    $labels = $this->extractLabels($key, 'role_changes_total');
                    $output[] = sprintf('groundwork_role_changes_total{%s} %s', $labels, $value);
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to collect admin activity metrics', [
                'error' => $e->getMessage(),
            ]);
        }

        return implode("\n", array_filter($output));
    }

    /**
     * Extract labels from a Redis key.
     */
    protected function extractLabels(string $key, string $metricName): string
    {
        // Keys returned by Redis::keys() include the Laravel Redis prefix
        // We need to strip it to find our metric prefix
        $keyWithoutRedisPrefix = $key;
        if ($this->redisPrefix && str_starts_with($key, $this->redisPrefix)) {
            $keyWithoutRedisPrefix = substr($key, strlen($this->redisPrefix));
        }

        // Remove prefix and metric name to get labels
        $pattern = '/.*'.preg_quote($metricName, '/').':(.*)$/';
        if (preg_match($pattern, $keyWithoutRedisPrefix, $matches)) {
            $labelStr = $matches[1];

            // Convert key=value format to key="value" format
            return preg_replace('/(\w+)=([^,]+)/', '$1="$2"', $labelStr);
        }

        return '';
    }

    /**
     * Collect histogram metrics.
     */
    protected function collectHistogramMetrics(array &$output, string $metricName, string $help): void
    {
        $bucketKeys = Redis::keys($this->prefix.'histogram:'.$metricName.'_bucket*');
        $countKeys = Redis::keys($this->prefix.'histogram:'.$metricName.'_count*');
        $sumKeys = Redis::keys($this->prefix.'histogram:'.$metricName.'_sum*');

        if (empty($bucketKeys) && empty($countKeys) && empty($sumKeys)) {
            return;
        }

        $output[] = '# HELP groundwork_'.$metricName.' '.$help;
        $output[] = '# TYPE groundwork_'.$metricName.' histogram';

        foreach ($bucketKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, $metricName.'_bucket');
            $output[] = sprintf('groundwork_%s_bucket{%s} %s', $metricName, $labels, $value);
        }

        foreach ($countKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, $metricName.'_count');
            $output[] = sprintf('groundwork_%s_count{%s} %s', $metricName, $labels, $value);
        }

        foreach ($sumKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, $metricName.'_sum');
            $output[] = sprintf('groundwork_%s_sum{%s} %s', $metricName, $labels, $value);
        }

        $output[] = '';
    }
}
