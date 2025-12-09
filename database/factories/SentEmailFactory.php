<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\SentEmail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SentEmail>
 */
class SentEmailFactory extends Factory
{
    protected $model = SentEmail::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'lead_id' => Lead::factory(),
            'mailbox_id' => Mailbox::factory(),
            'template_id' => null,
            'message_id' => '<'.Str::uuid().'@example.com>',
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'status' => SentEmail::STATUS_SENT,
            'sequence_step' => 1,
            'scheduled_for' => now(),
            'sent_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SentEmail::STATUS_PENDING,
            'sent_at' => null,
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SentEmail::STATUS_BOUNCED,
            'bounced_at' => now(),
        ]);
    }
}
