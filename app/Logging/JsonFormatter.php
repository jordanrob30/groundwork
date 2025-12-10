<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;
use Monolog\LogRecord;

/**
 * Custom JSON formatter for structured logging.
 *
 * Formats log records as JSON with a consistent structure suitable
 * for ingestion by Grafana Loki and other log aggregation systems.
 */
class JsonFormatter extends MonologJsonFormatter
{
    /**
     * Format a log record into a JSON string.
     *
     * @param  LogRecord  $record  The log record to format
     * @return string The formatted JSON string
     */
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalizeRecord($record);

        $output = [
            'timestamp' => $record->datetime->format('c'),
            'level' => strtolower($record->level->name),
            'channel' => $record->channel,
            'message' => $record->message,
            'context' => $this->normalizeContext($normalized['context'] ?? []),
        ];

        // Add exception details if present
        if (isset($normalized['context']['exception'])) {
            $output['exception'] = $this->formatException($normalized['context']['exception']);
            unset($output['context']['exception']);
        }

        // Add extra fields from processors
        if (! empty($normalized['extra'])) {
            $output['extra'] = $normalized['extra'];
        }

        return $this->toJson($output)."\n";
    }

    /**
     * Normalize context data for consistent output.
     *
     * @param  array<string, mixed>  $context  The context array
     * @return array<string, mixed> The normalized context
     */
    protected function normalizeContext(array $context): array
    {
        // Remove null values and empty arrays
        return array_filter($context, function ($value) {
            return $value !== null && $value !== [] && $value !== '';
        });
    }

    /**
     * Format exception data for logging.
     *
     * @param  array<string, mixed>  $exception  The exception data
     * @return array<string, mixed> The formatted exception
     */
    protected function formatException(array $exception): array
    {
        return [
            'class' => $exception['class'] ?? 'Unknown',
            'message' => $exception['message'] ?? '',
            'code' => $exception['code'] ?? 0,
            'file' => $exception['file'] ?? '',
            'line' => $exception['line'] ?? 0,
            'trace' => array_slice($exception['trace'] ?? [], 0, 10),
        ];
    }
}
