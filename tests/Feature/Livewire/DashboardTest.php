<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard\Dashboard;
use App\Models\Campaign;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
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
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_component_shows_active_campaigns(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'name' => 'Active Test Campaign',
        ]);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Active Test Campaign');
    }

    public function test_component_shows_weekly_metrics(): void
    {
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Emails Sent (This Week)')
            ->assertSee('Responses')
            ->assertSee('Response Rate')
            ->assertSee('Hot Leads');
    }

    public function test_component_shows_mailbox_health(): void
    {
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Mailbox Health');
    }

    public function test_component_can_be_refreshed(): void
    {
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->call('refresh')
            ->assertStatus(200);
    }
}
