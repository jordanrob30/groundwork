<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->company(),
            'role' => fake()->jobTitle(),
            'status' => Lead::STATUS_PENDING,
        ];
    }

    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_CONTACTED,
            'last_contacted_at' => now(),
        ]);
    }

    public function responded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_REPLIED,
            'last_contacted_at' => now()->subDays(2),
            'replied_at' => now(),
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_BOUNCED,
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_UNSUBSCRIBED,
        ]);
    }
}
