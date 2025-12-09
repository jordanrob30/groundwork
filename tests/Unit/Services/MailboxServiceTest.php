<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\MailboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxServiceTest extends TestCase
{
    use RefreshDatabase;

    private MailboxService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MailboxService::class);
        $this->user = User::factory()->create();
    }

    public function test_get_current_daily_limit_returns_full_limit_when_warmup_disabled(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => false,
            'daily_limit' => 100,
        ]);

        $limit = $this->service->getCurrentDailyLimit($mailbox);

        $this->assertEquals(100, $limit);
    }

    public function test_get_current_daily_limit_returns_warmup_limit_when_enabled(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => true,
            'warmup_day' => 1,
            'daily_limit' => 100,
        ]);

        $limit = $this->service->getCurrentDailyLimit($mailbox);

        // Day 1 of warmup schedule is 10 emails
        $this->assertEquals(10, $limit);
    }

    public function test_get_current_daily_limit_follows_warmup_schedule(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => true,
            'warmup_day' => 7,
            'daily_limit' => 100,
        ]);

        $limit = $this->service->getCurrentDailyLimit($mailbox);

        // Day 7 of warmup schedule is 32 emails
        $this->assertEquals(32, $limit);
    }

    public function test_get_current_daily_limit_returns_full_limit_after_warmup_period(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => true,
            'warmup_day' => 20, // Past the 14-day warmup
            'daily_limit' => 100,
        ]);

        $limit = $this->service->getCurrentDailyLimit($mailbox);

        $this->assertEquals(100, $limit);
    }

    public function test_has_reached_daily_limit_returns_false_when_under_limit(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => false,
            'daily_limit' => 100,
        ]);

        $result = $this->service->hasReachedDailyLimit($mailbox);

        $this->assertFalse($result);
    }
}
