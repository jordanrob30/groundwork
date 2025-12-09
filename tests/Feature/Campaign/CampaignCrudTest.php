<?php

declare(strict_types=1);

namespace Tests\Feature\Campaign;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\CampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Mailbox $mailbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->mailbox = Mailbox::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_view_campaign_list(): void
    {
        Campaign::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('campaigns.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_create_campaign_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('campaigns.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_edit_campaign_form(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('campaigns.edit', $campaign));

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_other_users_campaign(): void
    {
        $otherUser = User::factory()->create();
        $otherMailbox = Mailbox::factory()->create(['user_id' => $otherUser->id]);
        $campaign = Campaign::factory()->create([
            'user_id' => $otherUser->id,
            'mailbox_id' => $otherMailbox->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('campaigns.edit', $campaign));

        $response->assertStatus(403);
    }

    public function test_campaign_can_be_activated_with_leads_and_templates(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        Lead::factory()->count(5)->create(['campaign_id' => $campaign->id]);
        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
        ]);

        $service = app(CampaignService::class);
        $result = $service->activate($campaign);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals(Campaign::STATUS_ACTIVE, $campaign->fresh()->status);
    }

    public function test_campaign_cannot_be_activated_without_leads(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
        ]);

        $service = app(CampaignService::class);

        $this->expectException(\DomainException::class);
        $service->activate($campaign);
    }

    public function test_campaign_cannot_be_activated_without_templates(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        Lead::factory()->count(5)->create(['campaign_id' => $campaign->id]);

        $service = app(CampaignService::class);

        $this->expectException(\DomainException::class);
        $service->activate($campaign);
    }

    public function test_campaign_can_be_paused(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $service = app(CampaignService::class);
        $result = $service->pause($campaign);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals(Campaign::STATUS_PAUSED, $campaign->fresh()->status);
    }

    public function test_campaign_can_be_completed(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $service = app(CampaignService::class);
        $result = $service->complete($campaign);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals(Campaign::STATUS_COMPLETED, $campaign->fresh()->status);
        $this->assertNotNull($campaign->fresh()->completed_at);
    }

    public function test_campaign_can_be_duplicated(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        EmailTemplate::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
        ]);

        Lead::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $service = app(CampaignService::class);
        $duplicated = $service->duplicate($campaign, $campaign->name.' (Copy)');

        $this->assertNotEquals($campaign->id, $duplicated->id);
        $this->assertEquals($campaign->hypothesis, $duplicated->hypothesis);
        $this->assertEquals(Campaign::STATUS_DRAFT, $duplicated->status);
        // Templates should be duplicated
        $this->assertEquals(2, $duplicated->emailTemplates()->count());
        // Leads should NOT be duplicated
        $this->assertEquals(0, $duplicated->leads()->count());
    }

    public function test_campaign_status_helper_methods(): void
    {
        $draftCampaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        $activeCampaign = Campaign::factory()->active()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        $this->assertTrue($draftCampaign->isDraft());
        $this->assertFalse($draftCampaign->isActive());

        $this->assertTrue($activeCampaign->isActive());
        $this->assertFalse($activeCampaign->isDraft());
    }

    public function test_campaign_scopes(): void
    {
        Campaign::factory()->active()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);
        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        $activeCampaigns = Campaign::active()->forUser($this->user->id)->get();

        $this->assertCount(1, $activeCampaigns);
    }
}
