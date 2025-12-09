<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mailbox>
 */
class MailboxFactory extends Factory
{
    protected $model = Mailbox::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' Mailbox',
            'email_address' => fake()->unique()->safeEmail(),
            'status' => Mailbox::STATUS_ACTIVE,
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => fake()->userName(),
            'smtp_password' => 'test-password',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => fake()->userName(),
            'imap_password' => 'test-password',
            'uses_oauth' => false,
            'daily_limit' => 100,
            'warmup_enabled' => false,
            'warmup_day' => 0,
            'send_window_start' => '09:00',
            'send_window_end' => '17:00',
            'skip_weekends' => true,
            'timezone' => 'America/New_York',
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Mailbox::STATUS_PAUSED,
        ]);
    }

    public function warmup(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Mailbox::STATUS_WARMUP,
            'warmup_enabled' => true,
            'warmup_started_at' => now()->subDays(3),
            'warmup_day' => 3,
        ]);
    }

    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Mailbox::STATUS_ERROR,
            'error_message' => 'Authentication failed',
            'last_error_at' => now(),
        ]);
    }

    /**
     * Configure mailbox to use local Greenmail test server.
     */
    public function greenmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_address' => 'test@localhost',
            'smtp_host' => env('MAIL_HOST', 'mailserver'),
            'smtp_port' => (int) env('MAIL_PORT', 3025),
            'smtp_encryption' => 'none',
            'smtp_username' => env('TEST_MAILBOX_USERNAME', 'test'),
            'smtp_password' => env('TEST_MAILBOX_PASSWORD', 'password'),
            'imap_host' => env('TEST_MAILBOX_IMAP_HOST', 'mailserver'),
            'imap_port' => (int) env('TEST_MAILBOX_IMAP_PORT', 3143),
            'imap_encryption' => 'none',
            'imap_username' => env('TEST_MAILBOX_USERNAME', 'test'),
            'imap_password' => env('TEST_MAILBOX_PASSWORD', 'password'),
        ]);
    }
}
