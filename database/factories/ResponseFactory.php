<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Response;
use App\Models\SentEmail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Response>
 */
class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'lead_id' => Lead::factory(),
            'sent_email_id' => SentEmail::factory(),
            'message_id' => '<'.Str::uuid().'@example.com>',
            'in_reply_to' => null,
            'subject' => 'Re: '.fake()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'body_plain' => fake()->paragraph(),
            'received_at' => now(),
            'is_auto_reply' => false,
            'analysis_status' => Response::ANALYSIS_STATUS_PENDING,
            'review_status' => Response::REVIEW_UNREVIEWED,
        ];
    }

    public function analyzed(): static
    {
        return $this->state(fn (array $attributes) => [
            'analysis_status' => Response::ANALYSIS_STATUS_COMPLETED,
            'interest_level' => fake()->randomElement(['hot', 'warm', 'cold']),
            'problem_confirmation' => fake()->randomElement(['yes', 'no', 'unclear']),
            'summary' => fake()->paragraph(),
            'analyzed_at' => now(),
        ]);
    }

    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'interest_level' => Response::INTEREST_HOT,
            'analysis_status' => Response::ANALYSIS_STATUS_COMPLETED,
        ]);
    }

    public function autoReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_auto_reply' => true,
        ]);
    }
}
