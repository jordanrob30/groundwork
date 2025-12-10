<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Logging\PiiRedactionProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the PiiRedactionProcessor.
 */
class PiiRedactionProcessorTest extends TestCase
{
    private PiiRedactionProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new PiiRedactionProcessor;
    }

    /**
     * Test that password fields are redacted.
     */
    public function test_redacts_password_field(): void
    {
        $record = $this->createLogRecord([
            'password' => 'secret123',
            'user_password' => 'anothersecret',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('[REDACTED]', $processed->context['password']);
        $this->assertEquals('[REDACTED]', $processed->context['user_password']);
    }

    /**
     * Test that API key fields are redacted.
     */
    public function test_redacts_api_key_fields(): void
    {
        $record = $this->createLogRecord([
            'api_key' => 'key123',
            'apikey' => 'key456',
            'api-key' => 'key789',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('[REDACTED]', $processed->context['api_key']);
        $this->assertEquals('[REDACTED]', $processed->context['apikey']);
        $this->assertEquals('[REDACTED]', $processed->context['api-key']);
    }

    /**
     * Test that token fields are redacted.
     */
    public function test_redacts_token_fields(): void
    {
        $record = $this->createLogRecord([
            'token' => 'abc123',
            'access_token' => 'token456',
            'refresh_token' => 'token789',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('[REDACTED]', $processed->context['token']);
        $this->assertEquals('[REDACTED]', $processed->context['access_token']);
        $this->assertEquals('[REDACTED]', $processed->context['refresh_token']);
    }

    /**
     * Test that email addresses are masked.
     */
    public function test_masks_email_addresses(): void
    {
        $record = $this->createLogRecord([
            'email' => 'john.doe@example.com',
            'user_email' => 'jane.smith@company.org',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('j***@example.com', $processed->context['email']);
        $this->assertEquals('j***@company.org', $processed->context['user_email']);
    }

    /**
     * Test that email addresses in messages are masked.
     */
    public function test_masks_email_addresses_in_message(): void
    {
        $record = $this->createLogRecord(
            [],
            'User john.doe@example.com logged in'
        );

        $processed = ($this->processor)($record);

        $this->assertEquals('User j***@example.com logged in', $processed->message);
    }

    /**
     * Test that nested sensitive data is redacted.
     */
    public function test_redacts_nested_sensitive_data(): void
    {
        $record = $this->createLogRecord([
            'user' => [
                'name' => 'John',
                'password' => 'secret',
                'credentials' => [
                    'api_key' => 'nested_key',
                ],
            ],
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('John', $processed->context['user']['name']);
        $this->assertEquals('[REDACTED]', $processed->context['user']['password']);
        $this->assertEquals('[REDACTED]', $processed->context['user']['credentials']);
    }

    /**
     * Test that non-sensitive data is preserved.
     */
    public function test_preserves_non_sensitive_data(): void
    {
        $record = $this->createLogRecord([
            'user_id' => 123,
            'action' => 'login',
            'ip_address' => '192.168.1.1',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals(123, $processed->context['user_id']);
        $this->assertEquals('login', $processed->context['action']);
        $this->assertEquals('192.168.1.1', $processed->context['ip_address']);
    }

    /**
     * Test that SMTP credentials are redacted.
     */
    public function test_redacts_smtp_credentials(): void
    {
        $record = $this->createLogRecord([
            'smtp_password' => 'smtp_secret',
            'imap_password' => 'imap_secret',
        ]);

        $processed = ($this->processor)($record);

        $this->assertEquals('[REDACTED]', $processed->context['smtp_password']);
        $this->assertEquals('[REDACTED]', $processed->context['imap_password']);
    }

    /**
     * Create a log record for testing.
     *
     * @param  array<string, mixed>  $context  The context data
     * @param  string  $message  The log message
     * @return LogRecord The created log record
     */
    private function createLogRecord(array $context = [], string $message = 'Test message'): LogRecord
    {
        return new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: $message,
            context: $context,
            extra: []
        );
    }
}
