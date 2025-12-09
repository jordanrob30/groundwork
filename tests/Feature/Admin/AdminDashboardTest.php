<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_total_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(5)->create(['role' => User::ROLE_USER]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Dashboard::class);

        // Access the computed property via the component instance
        $this->assertEquals(6, $component->instance()->totalUsers);
    }

    public function test_dashboard_displays_active_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Active user (updated recently)
        User::factory()->create([
            'role' => User::ROLE_USER,
            'updated_at' => now()->subDay(),
        ]);

        // Inactive user (not updated in 7+ days)
        User::factory()->create([
            'role' => User::ROLE_USER,
            'updated_at' => now()->subDays(10),
        ]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Dashboard::class);

        $this->assertEquals(2, $component->instance()->activeUsers);
    }

    public function test_dashboard_displays_campaign_counts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Need a mailbox for campaigns
        $mailbox = \App\Models\Mailbox::factory()->create(['user_id' => $user->id]);

        Campaign::factory()->create([
            'user_id' => $user->id,
            'mailbox_id' => $mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        Campaign::factory()->create([
            'user_id' => $user->id,
            'mailbox_id' => $mailbox->id,
            'status' => Campaign::STATUS_PAUSED,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Dashboard::class);

        $this->assertEquals(2, $component->instance()->totalCampaigns);
        $this->assertEquals(1, $component->instance()->activeCampaigns);
    }

    public function test_dashboard_displays_recent_activity(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_IMPERSONATE,
            'changes' => null,
            'ip_address' => '127.0.0.1',
        ]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Dashboard::class);

        $recentActivity = $component->instance()->recentActivity;
        $this->assertCount(1, $recentActivity);
        $this->assertEquals(AdminAuditLog::ACTION_IMPERSONATE, $recentActivity->first()->action);
    }

    public function test_dashboard_limits_recent_activity_to_10(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        for ($i = 0; $i < 15; $i++) {
            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'target_user_id' => $user->id,
                'action' => AdminAuditLog::ACTION_IMPERSONATE,
                'changes' => null,
                'ip_address' => '127.0.0.1',
            ]);
        }

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Dashboard::class);

        $recentActivity = $component->instance()->recentActivity;
        $this->assertCount(10, $recentActivity);
    }
}
