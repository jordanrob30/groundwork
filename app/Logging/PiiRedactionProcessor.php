<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Monolog processor that redacts sensitive/PII data from log records.
 *
 * This processor scans log context for sensitive keys and redacts their
 * values before the log is written. It also masks email addresses to
 * comply with privacy requirements.
 */
class PiiRedactionProcessor implements ProcessorInterface
{
    /**
     * Keys that should have their values completely redacted.
     *
     * @var array<string>
     */
    protected array $sensitiveKeys = [
        'password',
        'api_key',
        'apikey',
        'api-key',
        'secret',
        'token',
        'access_token',
        'refresh_token',
        'bearer',
        'authorization',
        'smtp_password',
        'imap_password',
        'credential',
        'credentials',
        'private_key',
        'privatekey',
        'session_id',
        'sessionid',
        'csrf',
        'credit_card',
        'creditcard',
        'card_number',
        'cvv',
        'ssn',
    ];

    /**
     * The redaction placeholder.
     */
    protected string $redactionPlaceholder = '[REDACTED]';

    /**
     * Process a log record and redact sensitive data.
     *
     * @param  LogRecord  $record  The log record to process
     * @return LogRecord The processed log record with redacted data
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->redactArray($record->context);
        $extra = $this->redactArray($record->extra);

        // Also redact the message if it contains sensitive patterns
        $message = $this->redactMessage($record->message);

        return $record->with(
            context: $context,
            extra: $extra,
            message: $message
        );
    }

    /**
     * Recursively redact sensitive values from an array.
     *
     * @param  array<string, mixed>  $data  The data to redact
     * @return array<string, mixed> The redacted data
     */
    protected function redactArray(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $lowercaseKey = strtolower((string) $key);

            // Check if this key should be redacted
            if ($this->isSensitiveKey($lowercaseKey)) {
                $result[$key] = $this->redactionPlaceholder;

                continue;
            }

            // Recursively process nested arrays
            if (is_array($value)) {
                $result[$key] = $this->redactArray($value);

                continue;
            }

            // Mask email addresses
            if (is_string($value)) {
                $result[$key] = $this->maskEmailAddresses($value);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Check if a key is in the sensitive keys list.
     *
     * @param  string  $key  The key to check (lowercase)
     * @return bool True if the key is sensitive
     */
    protected function isSensitiveKey(string $key): bool
    {
        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask email addresses in a string.
     *
     * Converts "john.doe@example.com" to "j***@example.com"
     *
     * @param  string  $value  The string to process
     * @return string The string with masked emails
     */
    protected function maskEmailAddresses(string $value): string
    {
        return preg_replace_callback(
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            function (array $matches): string {
                $email = $matches[0];
                $parts = explode('@', $email);
                if (count($parts) !== 2) {
                    return $email;
                }

                $local = $parts[0];
                $domain = $parts[1];

                // Keep first character, mask the rest
                $maskedLocal = strlen($local) > 1
                    ? $local[0].'***'
                    : $local.'***';

                return $maskedLocal.'@'.$domain;
            },
            $value
        ) ?? $value;
    }

    /**
     * Redact sensitive patterns from the log message.
     *
     * @param  string  $message  The log message
     * @return string The message with redacted patterns
     */
    protected function redactMessage(string $message): string
    {
        // Mask email addresses in the message
        $message = $this->maskEmailAddresses($message);

        // Redact common sensitive patterns
        $patterns = [
            // API keys (common formats)
            '/(?:api[_-]?key|apikey|token)[=:]\s*[\'"]?([a-zA-Z0-9_-]{20,})[\'"]?/i' => '$0'.$this->redactionPlaceholder,
            // Bearer tokens
            '/Bearer\s+[a-zA-Z0-9._-]+/i' => 'Bearer '.$this->redactionPlaceholder,
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message) ?? $message;
        }

        return $message;
    }
}
