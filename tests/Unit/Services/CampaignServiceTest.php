<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\CampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignServiceTest extends TestCase
{
    use RefreshDatabase;

    private CampaignService $service;

    private User $user;

    private Mailbox $mailbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CampaignService::class);
        $this->user = User::factory()->create();
        $this->mailbox = Mailbox::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_calculate_metrics_returns_zero_for_empty_campaign(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $metrics = $this->service->calculateMetrics($campaign);

        $this->assertEquals(0, $metrics['total_leads']);
        $this->assertEquals(0, $metrics['emails_sent']);
        $this->assertEquals(0, $metrics['responses']);
        $this->assertEquals(0, $metrics['response_rate']);
    }

    public function test_calculate_metrics_returns_correct_total_leads(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        Lead::factory()->count(10)->create(['campaign_id' => $campaign->id]);

        $metrics = $this->service->calculateMetrics($campaign);

        $this->assertEquals(10, $metrics['total_leads']);
    }

    public function test_calculate_decision_score_returns_array_with_score(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $result = $this->service->calculateDecisionScore($campaign);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('recommendation', $result);
        $this->assertArrayHasKey('factors', $result);
        $this->assertEquals(0, $result['score']);
    }

    public function test_duplicate_copies_campaign_settings(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'hypothesis' => 'Test hypothesis',
            'target_persona' => 'Test persona',
            'industry' => 'SaaS',
        ]);

        $duplicated = $this->service->duplicate($campaign, $campaign->name.' (Copy)');

        $this->assertNotEquals($campaign->id, $duplicated->id);
        $this->assertEquals($campaign->hypothesis, $duplicated->hypothesis);
        $this->assertEquals($campaign->target_persona, $duplicated->target_persona);
        $this->assertEquals($campaign->industry, $duplicated->industry);
        $this->assertEquals(Campaign::STATUS_DRAFT, $duplicated->status);
        $this->assertStringContainsString('(Copy)', $duplicated->name);
    }

    public function test_duplicate_copies_templates(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        EmailTemplate::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
        ]);

        $duplicated = $this->service->duplicate($campaign, 'New Campaign');

        $this->assertEquals(3, $duplicated->emailTemplates()->count());
    }

    public function test_duplicate_does_not_copy_leads(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        Lead::factory()->count(5)->create(['campaign_id' => $campaign->id]);

        $duplicated = $this->service->duplicate($campaign, 'New Campaign');

        $this->assertEquals(0, $duplicated->leads()->count());
    }
}
