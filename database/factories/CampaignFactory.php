<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mailbox_id' => Mailbox::factory(),
            'name' => fake()->words(3, true).' Campaign',
            'status' => Campaign::STATUS_DRAFT,
            'hypothesis' => fake()->paragraph(),
            'industry' => fake()->randomElement(['SaaS', 'Fintech', 'Healthcare', 'E-commerce']),
            'target_persona' => fake()->sentence(),
            'success_criteria' => fake()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_ACTIVE,
            'activated_at' => now(),
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_PAUSED,
            'activated_at' => now()->subDays(5),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_COMPLETED,
            'activated_at' => now()->subDays(14),
            'completed_at' => now(),
        ]);
    }
}
