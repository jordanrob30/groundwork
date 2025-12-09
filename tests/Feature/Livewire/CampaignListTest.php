<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Campaign\CampaignList;
use App\Models\Campaign;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignListTest extends TestCase
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

    public function test_component_renders(): void
    {
        Livewire::actingAs($this->user)
            ->test(CampaignList::class)
            ->assertStatus(200);
    }

    public function test_component_shows_user_campaigns(): void
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Test Campaign',
        ]);

        Livewire::actingAs($this->user)
            ->test(CampaignList::class)
            ->assertSee('Test Campaign');
    }

    public function test_component_filters_by_status(): void
    {
        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Active Campaign',
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Draft Campaign',
            'status' => Campaign::STATUS_DRAFT,
        ]);

        Livewire::actingAs($this->user)
            ->test(CampaignList::class)
            ->set('statusFilter', Campaign::STATUS_ACTIVE)
            ->assertSee('Active Campaign')
            ->assertDontSee('Draft Campaign');
    }

    public function test_component_searches_by_name(): void
    {
        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Alpha Campaign',
        ]);

        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Beta Campaign',
        ]);

        Livewire::actingAs($this->user)
            ->test(CampaignList::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Campaign')
            ->assertDontSee('Beta Campaign');
    }

    public function test_component_can_archive_campaign(): void
    {
        $campaign = Campaign::factory()->completed()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(CampaignList::class)
            ->call('archive', $campaign->id);

        $this->assertEquals(Campaign::STATUS_ARCHIVED, $campaign->fresh()->status);
    }
}
