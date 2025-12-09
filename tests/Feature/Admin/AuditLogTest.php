<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_displays_entries(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_IMPERSONATE,
            'changes' => ['started_at' => now()->toIso8601String()],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $response = $this->actingAs($admin)->get('/admin/audit-log');

        $response->assertStatus(200);
        $response->assertSee('Started impersonating user');
    }

    public function test_audit_log_can_filter_by_action(): void
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

        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_ROLE_CHANGE,
            'changes' => ['from' => 'user', 'to' => 'admin'],
            'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\AuditLog::class)
            ->set('actionFilter', AdminAuditLog::ACTION_ROLE_CHANGE)
            ->assertSee('Changed user role')
            ->assertDontSee('Started impersonating');
    }

    public function test_audit_log_shows_admin_and_target_user(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin User',
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Target User',
        ]);

        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_ROLE_CHANGE,
            'changes' => ['from' => 'user', 'to' => 'admin'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get('/admin/audit-log');

        $response->assertStatus(200);
        $response->assertSee('Admin User');
        $response->assertSee('Target User');
    }

    public function test_audit_log_entries_are_ordered_by_most_recent(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Create older log first
        $olderLog = new AdminAuditLog([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_IMPERSONATE,
            'changes' => null,
            'ip_address' => '127.0.0.1',
        ]);
        $olderLog->created_at = now()->subDay();
        $olderLog->save();

        // Create newer log second
        $newerLog = AdminAuditLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_ROLE_CHANGE,
            'changes' => ['from' => 'user', 'to' => 'admin'],
            'ip_address' => '127.0.0.1',
        ]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\AuditLog::class);

        $logs = $component->instance()->logs;
        $this->assertEquals($newerLog->id, $logs->first()->id);
    }
}
