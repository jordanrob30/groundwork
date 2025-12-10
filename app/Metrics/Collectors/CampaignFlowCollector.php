<?php

declare(strict_types=1);

namespace App\Metrics\Collectors;

use Illuminate\Support\Facades\Redis;

/**
 * Collector for campaign flow metrics.
 *
 * Tracks email campaign pipeline metrics: queue → send → poll → analyze
 */
class CampaignFlowCollector
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
     * Increment the email queued counter.
     */
    public function incrementEmailQueued(int $campaignId, int $mailboxId): void
    {
        $this->incrementCounter('email_queued_total', [
            'campaign_id' => (string) $campaignId,
            'mailbox_id' => (string) $mailboxId,
        ]);
    }

    /**
     * Increment the email sent counter.
     */
    public function incrementEmailSent(int $campaignId, int $mailboxId, string $status = 'success'): void
    {
        $this->incrementCounter('email_sent_total', [
            'campaign_id' => (string) $campaignId,
            'mailbox_id' => (string) $mailboxId,
            'status' => $status,
        ]);
    }

    /**
     * Record email send duration.
     */
    public function recordSendDuration(int $campaignId, int $mailboxId, float $seconds): void
    {
        $this->observeHistogram('email_send_duration_seconds', $seconds, [
            'campaign_id' => (string) $campaignId,
            'mailbox_id' => (string) $mailboxId,
        ], config('prometheus.buckets.email_send_duration', [0.1, 0.5, 1, 2, 5, 10, 30]));
    }

    /**
     * Increment the replies detected counter.
     */
    public function incrementRepliesDetected(int $campaignId, bool $matched): void
    {
        $this->incrementCounter('replies_detected_total', [
            'campaign_id' => (string) $campaignId,
            'matched' => $matched ? 'true' : 'false',
        ]);
    }

    /**
     * Record polling duration.
     */
    public function recordPollingDuration(int $mailboxId, float $seconds): void
    {
        $this->observeHistogram('polling_duration_seconds', $seconds, [
            'mailbox_id' => (string) $mailboxId,
        ], config('prometheus.buckets.polling_duration', [1, 5, 10, 30, 60, 120]));
    }

    /**
     * Increment the polling messages counter.
     */
    public function incrementPollingMessages(int $mailboxId, int $count = 1): void
    {
        $this->incrementCounter('polling_messages_total', [
            'mailbox_id' => (string) $mailboxId,
        ], $count);
    }

    /**
     * Increment the analysis counter.
     */
    public function incrementAnalysis(int $campaignId, string $status = 'completed'): void
    {
        $this->incrementCounter('analysis_total', [
            'campaign_id' => (string) $campaignId,
            'status' => $status,
        ]);
    }

    /**
     * Record analysis duration.
     */
    public function recordAnalysisDuration(int $campaignId, float $seconds): void
    {
        $this->observeHistogram('analysis_duration_seconds', $seconds, [
            'campaign_id' => (string) $campaignId,
        ], config('prometheus.buckets.analysis_duration', [1, 5, 10, 30, 60]));
    }

    /**
     * Set the queue depth gauge.
     */
    public function setQueueDepth(int $campaignId, int $mailboxId, int $depth): void
    {
        $this->setGauge('email_queue_depth', $depth, [
            'campaign_id' => (string) $campaignId,
            'mailbox_id' => (string) $mailboxId,
        ]);
    }

    /**
     * Set the analysis queue depth gauge.
     */
    public function setAnalysisQueueDepth(int $depth): void
    {
        $this->setGauge('analysis_queue_depth', $depth, []);
    }

    /**
     * Collect all metrics in Prometheus format.
     */
    public function collect(): string
    {
        $output = [];

        // Email Queue Metrics
        $output[] = '# HELP groundwork_email_queue_depth Current emails waiting in queue';
        $output[] = '# TYPE groundwork_email_queue_depth gauge';
        $output = array_merge($output, $this->collectGauge('email_queue_depth'));

        $output[] = '# HELP groundwork_email_queued_total Total emails added to queue';
        $output[] = '# TYPE groundwork_email_queued_total counter';
        $output = array_merge($output, $this->collectCounter('email_queued_total'));

        // Email Sending Metrics
        $output[] = '# HELP groundwork_email_sent_total Emails sent';
        $output[] = '# TYPE groundwork_email_sent_total counter';
        $output = array_merge($output, $this->collectCounter('email_sent_total'));

        $output[] = '# HELP groundwork_email_send_duration_seconds Send operation duration';
        $output[] = '# TYPE groundwork_email_send_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('email_send_duration_seconds'));

        // Reply Detection Metrics
        $output[] = '# HELP groundwork_replies_detected_total Replies found';
        $output[] = '# TYPE groundwork_replies_detected_total counter';
        $output = array_merge($output, $this->collectCounter('replies_detected_total'));

        $output[] = '# HELP groundwork_polling_duration_seconds Mailbox polling duration';
        $output[] = '# TYPE groundwork_polling_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('polling_duration_seconds'));

        $output[] = '# HELP groundwork_polling_messages_total Total messages retrieved from mailboxes';
        $output[] = '# TYPE groundwork_polling_messages_total counter';
        $output = array_merge($output, $this->collectCounter('polling_messages_total'));

        // AI Analysis Metrics
        $output[] = '# HELP groundwork_analysis_total Analysis jobs';
        $output[] = '# TYPE groundwork_analysis_total counter';
        $output = array_merge($output, $this->collectCounter('analysis_total'));

        $output[] = '# HELP groundwork_analysis_duration_seconds AI analysis duration';
        $output[] = '# TYPE groundwork_analysis_duration_seconds histogram';
        $output = array_merge($output, $this->collectHistogram('analysis_duration_seconds'));

        $output[] = '# HELP groundwork_analysis_queue_depth Responses pending analysis';
        $output[] = '# TYPE groundwork_analysis_queue_depth gauge';
        $output = array_merge($output, $this->collectGauge('analysis_queue_depth'));

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

        // Collect buckets
        $bucketPattern = $this->prefix.'histogram:'.$name.'_bucket*';
        $bucketKeys = Redis::keys($bucketPattern);

        foreach ($bucketKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_bucket');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_bucket'.$labelStr.' '.$value;
        }

        // Collect counts
        $countPattern = $this->prefix.'histogram:'.$name.'_count*';
        $countKeys = Redis::keys($countPattern);

        foreach ($countKeys as $key) {
            $value = Redis::get($this->stripRedisPrefix($key)) ?? 0;
            $labels = $this->extractLabels($key, 'histogram', $name.'_count');
            $labelStr = $this->formatPrometheusLabels($labels);
            $output[] = 'groundwork_'.$name.'_count'.$labelStr.' '.$value;
        }

        // Collect sums
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

        // Remove leading colon
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
