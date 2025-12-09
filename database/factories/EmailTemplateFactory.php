<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'campaign_id' => Campaign::factory(),
            'name' => fake()->words(3, true).' Template',
            'subject' => 'Question about {{company}}',
            'body' => "Hi {{first_name}},\n\n".fake()->paragraph()."\n\nBest regards",
            'sequence_order' => 1,
            'delay_days' => 0,
            'delay_type' => 'business',
            'is_library_template' => false,
        ];
    }

    public function followUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Follow-up Template',
            'subject' => 'Re: Question about {{company}}',
            'body' => "Hi {{first_name}},\n\nJust following up on my previous message.\n\nBest regards",
            'sequence_order' => 2,
            'delay_days' => 3,
        ]);
    }

    public function libraryTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_library_template' => true,
            'campaign_id' => null,
        ]);
    }
}
